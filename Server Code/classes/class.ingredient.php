<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );


class CF_Ingredient {
	
	private $recipe_id;
	private $name;
	private $quantity;
	private $measure;
	private $measureText;
	private $order;
	
	// Initializes a new ingredient object
	public function __construct( $recipe_id, $order = null ) {
		if ( !is_null( $order ) ) {
			$dbo =& CF_Factory::getDBO();
			
			$query	=	'
				SELECT	i.*, m.abbreviation
				FROM		' . CF_INGREDIENTS_TABLE . ' i, ' . CF_MEASURES_TABLE . ' m
				WHERE		i.recipe_id = ' . $dbo->sqlsafe( $recipe_id ) . '
				AND		i.`order` = ' . $dbo->sqlsafe( $order ) . '
				AND		i.measure = m.id'
			;
			$ingredientresult = $dbo->query( $query );
			
			if ( $dbo->hasError( $ingredientresult ) ) {
				$dbo->submitErrorLog( $ingredientresult, 'CF_Ingredient::__construct()' );
				throw new Exception( 'Could not load the ingredient information!', CF_MSG_ERROR );
			}
			if ( $dbo->num_rows( $ingredientresult ) < 1 ) {
				throw new Exception( 'The specified ingredient does not exist!', CF_MSG_ERROR );
			}
			
			$row = $dbo->getResultObject( $ingredientresult )->fetch_object();
			$this->recipe_id	=	$row->recipe_id;
			$this->name			=	$row->name;
			$this->quantity		=	$row->quantity;
			$this->measure		=	$row->measure;
			$this->measureText	=	$row->abbreviation;
			$this->order		=	$row->order;
		} else {
			$this->recipe_id		=	$recipe_id;
			$this->order		=	null;
		}
	}
	
	// Getters and Setters
	public function getName() {
		return $this->name;
	}
	
	public function getQuantity() {
		return ( (round( $this->quantity, 2 ) == round( $this->quantity )) ? (int) $this->quantity : $this->quantity );
	}
	
	public function getOrder() {
		return $this->order;
	}
	
	public function getMeasure() {
		return $this->measure;
	}
	
	public function setID( $recipe_id ) {
		$this->recipe_id = $recipe_id;
	}
	
	public function setParameters( $name, $quantity, $measure ) {
		$this->name = $name;
		$this->quantity = $quantity;
		$this->measure = $measure;
	}
	
	public function setName( $name ) {
		$this->name = $name;
	}
	
	public function setQuantity( $quantity ) {
		$this->quantity = $quantity;
	}
	
	public function setMeasure( $measure ) {
		$this->measure = $measure;
	}
	
	// Utility Functions
	public function getScaledQuantity( $factor ) {
		return $this->quantity * $factor;
	}
	
	public function commit() {
		$dbo =& CF_Factory::getDBO();
		
		if ( empty( $this->recipe_id ) || empty( $this->name ) || empty( $this->quantity ) || empty( $this->measure ) ) {
			throw new Exception( 'Required information not provided!', 1 );
		} else {
			/*if ( preg_match( '/[^A-Za-z0-9\, \-\(\)]/', $this->name ) > 0 ) {
				throw new Exception( 'Invalid name', 2 );
			} else*/if ( !is_numeric( $this->quantity ) || ceil( $this->quantity ) < 1.0 ) {
				throw new Exception( 'Invalid quantity', 3 );
			} elseif ( !is_numeric( $this->measure ) || $this->measure < 1 ) {
				throw new Exception( 'Invalid measure', 4 );
			} elseif ( !is_null( $this->order ) && (!is_numeric( $this->order ) || $this->order < 1) ) {
				throw new Exception( 'Invalid ingredient order', 5 );
			}
		}
		
		if ( !is_null( $this->order ) ) {
			$query	=	'
				UPDATE	' . CF_INGREDIENTS_TABLE . '
				SET		name = "' . $dbo->sqlsafe( $this->name ) . '",
						quantity = ' . $dbo->sqlsafe( $this->quantity ) . ',
						measure = ' . $dbo->sqlsafe( $this->measure ) . '
				WHERE		recipe_id = ' . $dbo->sqlsafe( $this->recipe_id ) . '
				AND		`order` = ' . $dbo->sqlsafe( $this->order )
			;
			$updatequery = $dbo->query( $query );
			
			if ( $dbo->hasError( $updatequery ) ) {
				$dbo->submitErrorLog( $updatequery, 'CF_Ingredient::commit()' );
				throw new Exception( 'Failed committing the ingredient properties to the database!', -1 );
			}
		} else {
			$query	=	'
				SELECT
					IFNULL( t2.o + 1, 1 ) AS maxorder
				FROM 	( SELECT 1 ) AS t1
				LEFT JOIN (
					SELECT		MAX( `order` ) AS o
					FROM		' . CF_INGREDIENTS_TABLE . '
					GROUP BY	recipe_id
					HAVING		recipe_id = ' . $dbo->sqlsafe( $this->recipe_id ) . '
				)
				AS t2
				ON 1
				LIMIT 1'
			;
			$maxorderquery = $dbo->query( $query );
			
			if ( $dbo->hasError( $maxorderquery ) ) {
				$dbo->submitErrorLog( $maxorderquery, 'CF_Ingredient::commit()' );
				throw new Exception( 'Failed committing the ingredient properties to the database!', -1 );
			} elseif ( $dbo->num_rows( $maxorderquery ) == 1 ) {
				$maxorder = $dbo->getResultObject( $maxorderquery )->fetch_row()[0];
			}
			
			$query	=	'
				INSERT INTO	' . CF_INGREDIENTS_TABLE . '
				(
					recipe_id,
					name,
					quantity,
					measure,
					`order`
				)
				VALUES(
					' . $dbo->sqlsafe( $this->recipe_id ) . ',
					"' . $dbo->sqlsafe( $this->name ) . '",
					' . $dbo->sqlsafe( $this->quantity ) . ',
					' . $dbo->sqlsafe( $this->measure ) . ',
					' . $dbo->sqlsafe( $maxorder ) . '
				)'
			;
			$insertquery = $dbo->query( $query );
			
			if ( $dbo->hasError( $insertquery ) ) {
				$dbo->submitErrorLog( $insertquery, 'CF_Ingredient::commit()' );
				throw new Exception( 'Failed committing the ingredient properties to the database!', -1 );
			}
			
			$this->order = $maxorder;
			
		}
		
		return true;
	}
	
	// Removes ingredient from database
	// Note: After running this function, all references to this object should be deleted.
	public function remove() {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			DELETE FROM	' . CF_INGREDIENTS_TABLE . '
			WHERE		recipe_id = ' . $dbo->sqlsafe( $this->recipe_id ) . '
			AND			`order` = ' . $dbo->sqlsafe( $this->order );
		;
		$deletequery = $dbo->query( $query );
		
		if ( $dbo->hasError( $deletequery ) ) {
			$dbo->submitErrorLog( $deletequery, 'CF_Ingredient::remove()' );
			throw new Exception( 'Failed removing the ingredient from the database!', -1 );
		}
		
		return true;
	}
	
	// Miscellaneous Functions
	
	// Note: We could implement a decimal to fraction converter in the future if we're feeling ambitious.
	public function __toString() {
		return ( (round( $this->quantity, 2 ) == round( $this->quantity )) ? (int) $this->quantity : round( $this->quantity, 2 ) ) . ( (!empty( $this->measure )) ? ' ' . $this->measureText : '' ) . ' ' . $this->name;
	}
	
}

?>