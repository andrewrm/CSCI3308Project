<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );

class CF_BarManager {
	
	private $bars;
	
	public function __construct() {
		$this->bars = null;
	}
	
	// Populates and returns bars
	public function populate() {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		id, name, location
			FROM		' . CF_BARS_TABLE
		;
		$barquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $barquery ) ) {
			$dbo->submitErrorLog( $barquery, 'CF_BarManager::populate()' );
			throw new Exception( 'Could not load the bar information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $barquery ) > 0 ) {
			$this->bars = $barquery;
			return $barquery;
		}
		
	}
	
	// Finds bars that offer a certain drink
	public function findBarsWithDrink( $recipe_id ) {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		tagged_bars
			FROM		' . CF_COCKTAILS_TABLE . '
			WHERE		id = ' . $dbo->sqlsafe( $recipe_id )
		;
		$cocktailquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $cocktailquery ) ) {
			$dbo->submitErrorLog( $cocktailquery, 'CF_BarManager::findBarsWithDrink() - Retrieve tagged bars' );
			throw new Exception( 'Could not find the bar information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $cocktailquery ) > 0 ) {
			$tagged_bars = $dbo->getResultObject( $cocktailquery )->fetch_row()[0];
		}
		
		// Split the serialized string into separater bar IDs
		$tagged_bars = explode( ',', $tagged_bars );
		if ( count( $tagged_bars ) == 1 && empty( $tagged_bars[0] ) ) {
			$tagged_bars = array();
		}
		
		// Build part of the query
		$squery = '';
		foreach ( $tagged_bars as $tagged_bar ) {
			$squery .= ' OR id = ' . $dbo->sqlsafe( $tagged_bar );
		}
		
		$query	=	'
			SELECT		id, name, location, lat, lng
			FROM		' . CF_BARS_TABLE . '
			WHERE		0'
			 . $squery
		;
		$barquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $barquery ) ) {
			$dbo->submitErrorLog( $barquery, 'CF_BarManager::findBarsWithDrink() - Get bar names' );
			throw new Exception( 'Could not find the bar information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $barquery ) > 0 ) {
			return $barquery;
		}
		
		
	}
	
	// Tags bars that offer a certain drink
	public function tagBarWithDrink( $recipe_id, $bar_id ) {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		tagged_bars
			FROM		' . CF_COCKTAILS_TABLE . '
			WHERE		id = ' . $dbo->sqlsafe( $recipe_id )
		;
		$cocktailquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $cocktailquery ) ) {
			$dbo->submitErrorLog( $cocktailquery, 'CF_BarManager::tagBarWithDrink() - Retrieve tagged bars' );
			throw new Exception( 'Could not find the bar information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $cocktailquery ) > 0 ) {
			$tagged_bars = $dbo->getResultObject( $cocktailquery )->fetch_row()[0];
		} else {
			throw new Exception( 'No such drink exists!', CF_MSG_ERROR );
		}
		
		// Split the serialized string into separater bar IDs
		$tagged_bars = explode( ',', $tagged_bars );
		if ( count( $tagged_bars ) == 1 && empty( $tagged_bars[0] ) ) {
			$tagged_bars = array();
		}
		
		// Add the current bar ID to the list
		$tagged_bars[] = $bar_id;
		
		// Commit changes to database
		$query	=	'
			UPDATE		' . CF_COCKTAILS_TABLE . '
			SET			tagged_bars = "' . implode( ',', $tagged_bars ) . '"
			WHERE		id = ' . $dbo->sqlsafe( $recipe_id )
		;
		$barquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $barquery ) ) {
			$dbo->submitErrorLog( $barquery, 'CF_BarManager::tagBarWithDrink() - Get bar names' );
			throw new Exception( 'Could not find the bar information!', CF_MSG_ERROR );
		}
		
		return true;
		
	}
	
	/*** TO DO ***/
	public function search( $criteria, $isAjaxRequest = false, $limit = 0, $offset = 0 ) {
		$dbo =& CF_Factory::getDBO();
		
		// Prospective AJAX feature
		if ( $isAjaxRequest )
			return false;
		
		// Clean up the search term
		$criteria = trim( $criteria );
		
		// A minimum of 3 characters is required to initiate a search
		if ( strlen( $criteria ) < 2 ) {
			throw new Exception( 'Minimum search query character limit not met.', -1 );
			return null;
		}
		
		$keywords = explode( ' ', $criteria );
		$squery = '';
		
		
		foreach( $keywords as $keyword ) {
			if ( strlen( $keyword ) >= 2 ) {
				$squery	.=	'
					OR	d.direction LIKE "%' . $dbo->sqlsafe( $keyword ) . '%"
					OR	i.name LIKE "%' . $dbo->sqlsafe( $keyword ) . '%"
					OR	r.name LIKE "%' . $dbo->sqlsafe( $keyword ) . '%"
				';
			} else {
				throw new Exception( 'Minimum search query character limit not met.', -1 );
				return null;
			}
		}
		
		$query	=	'
			SELECT		r.*
			FROM		' . CF_COCKTAILS_TABLE . ' r
			LEFT JOIN	' . CF_INGREDIENTS_TABLE . ' i
			ON			i.recipe_id = r.id
			LEFT JOIN	' . CF_DIRECTIONS_TABLE . ' d
			ON			d.recipe_id = r.id
			WHERE (
				0 
				' . $squery . '
			)
			GROUP BY	i.recipe_id, d.recipe_id
			ORDER BY	r.name
			' . ( ($limit > 0) ? 'LIMIT ' . $offset . ', ' . $limit : '' )
		;
		
		$searchquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $searchquery ) ) {
			$dbo->submitErrorLog( $searchquery, 'CF_BarManager::search()' );
			throw new Exception( 'An error occurred while searching.', -2 );
			return false;
		} else {
			//return array( $searchquery, $total );
			return $searchquery;
		}
	}
	/*** END TO DO ***/
	
	public function add() {}
	
}

?>