<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );

class CF_CocktailManager {
	
	private $cocktails;
	
	public function __construct() {
		$this->cocktails = null;
	}
	
	// Populates cocktails
	public function populate() {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		*
			FROM		' . CF_COCKTAILS_TABLE
		;
		$cocktailquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $cocktailquery ) ) {
			$dbo->submitErrorLog( $cocktailquery, 'CF_CocktailManager::populate()' );
			throw new Exception( 'Could not load the cocktail information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $cocktailquery ) > 0 ) {
			$this->cocktails = $cocktailquery;
			return $cocktailquery;
		}
		
	}
	
	/*** TO DO ***/
	public function search( $criteria, $isAjaxRequest = false, $limit = 0, $offset = 0, $order_by = 1 ) {
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
		
		// Sort the results in the specified order
		switch ( $order_by ) {
			case 1:
				$ordering = 'r.name';
				break;
			case 2:
				$ordering = '( points / numVotes ) DESC';
				break;
			case 3:
				$ordering = 'r.hits DESC';
				break;
			case 4:
				$ordering = '(LENGTH(r.tagged_bars) - LENGTH(REPLACE(r.tagged_bars, ",", ""))) DESC';
				break;
			default:
				$ordering = 'r.name';
				break;
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
			ORDER BY	' . $ordering . '
			' . ( ($limit > 0) ? 'LIMIT ' . $offset . ', ' . $limit : '' )
		;
		
		$searchquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $searchquery ) ) {
			$dbo->submitErrorLog( $searchquery, 'CF_CocktailManager::search()' );
			throw new Exception( 'An error occurred while searching.', -2 );
			return false;
		} else {
			//return array( $searchquery, $total );
			return $searchquery;
		}
	}
	
	// Populates the different recipe categories
	public static function getCategories() {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		*
			FROM		' . CF_CATEGORIES_TABLE
		;
		$categoryquery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $categoryquery ) ) {
			$dbo->submitErrorLog( $categoryquery, 'CF_CocktailManager::getCategories()' );
			throw new Exception( 'Could not load the category information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $categoryquery ) > 0 ) {
			$categories = array();
			while ( $row = $dbo->getResultObject( $categoryquery )->fetch_object() ) {
				$categories[] = array( $row->id, $row->name );
			}
			return $categories;
		}
	}
	
	// Populates the different measuring units
	public static function getMeasures() {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			SELECT		id, abbreviation
			FROM		' . CF_MEASURES_TABLE
		;
		$measurequery = $dbo->query( $query );
		
		// If an error occurred, append to the error log and return with an error code
		if ( $dbo->hasError( $measurequery ) ) {
			$dbo->submitErrorLog( $measurequery, 'CF_CocktailManager::getMeasures()' );
			throw new Exception( 'Could not load the measure information!', CF_MSG_ERROR );
		} elseif ( $dbo->num_rows( $measurequery ) > 0 ) {
			$measures = array();
			while ( $row = $dbo->getResultObject( $measurequery )->fetch_object() ) {
				$measures[] = array( $row->id, $row->abbreviation );
			}
			return $measures;
		}
	}
	
	public function add() {}
	
}

?>