<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );


class CF_Direction {
	
	private $recipe_id;
	private $direction;
	private $order;
	
	// Initializes a new ingredient object
	public function __construct( $recipe_id, $order = null ) {
		if ( !is_null( $order ) ) {
			$dbo =& CF_Factory::getDBO();
			
			$query	=	'
				SELECT	*
				FROM		' . CF_DIRECTIONS_TABLE . '
				WHERE		recipe_id = ' . $dbo->sqlsafe( $recipe_id ) . '
				AND		`order` = ' . $dbo->sqlsafe( $order )
			;
			$directionresult = $dbo->query( $query );
			
			if ( $dbo->hasError( $directionresult ) ) {
				$dbo->submitErrorLog( $directionresult, 'CF_Direction::__construct()' );
				throw new Exception( 'Could not load the direction information!', CF_MSG_ERROR );
			}
			if ( $dbo->num_rows( $directionresult ) < 1 ) {
				throw new Exception( 'The specified direction does not exist!', CF_MSG_ERROR );
			}
			
			$row = $dbo->getResultObject( $directionresult )->fetch_object();
			$this->recipe_id		=	$row->recipe_id;
			$this->direction		=	$row->direction;
			$this->order			=	$row->order;
		} else {
			$this->recipe_id		=	$recipe_id;
			$this->order			=	null;
		}
	}
	
	// Getters and Setters
	public function getDirection() {
		return $this->direction;
	}
	
	public function getOrder() {
		return $this->order;
	}
	
	public function setID( $recipe_id ) {
		$this->recipe_id = $recipe_id;
	}
	
	public function setDirection( $direction ) {
		$this->direction = $direction;
	}
	
	// Utility Functions
	public function commit() {
		$dbo =& CF_Factory::getDBO();
		
		if ( empty( $this->recipe_id ) || empty( $this->direction ) ) {
			throw new Exception( 'Required information not provided!', 1 );
		} else {
			if ( !is_null( $this->order ) && (!is_numeric( $this->order ) || $this->order < 1) ) {
				throw new Exception( 'Invalid direction order', 5 );
			}
		}
		
		if ( !is_null( $this->order ) ) {
			$query	=	'
				UPDATE	' . CF_DIRECTIONS_TABLE . '
				SET		direction = "' . $dbo->sqlsafe( $this->direction ) . '"
				WHERE		recipe_id = ' . $dbo->sqlsafe( $this->recipe_id ) . '
				AND		`order` = ' . $dbo->sqlsafe( $this->order )
			;
			$updatequery = $dbo->query( $query );
			
			if ( $dbo->hasError( $updatequery ) ) {
				$dbo->submitErrorLog( $updatequery, 'CF_Direction::commit()' );
				throw new Exception( 'Failed committing the direction properties to the database!', -1 );
			}
		} else {
			$query	=	'
				SELECT
					IFNULL( t2.o + 1, 1 ) AS maxorder
				FROM 	( SELECT 1 ) AS t1
				LEFT JOIN (
					SELECT		MAX( `order` ) AS o
					FROM		' . CF_DIRECTIONS_TABLE . '
					GROUP BY	recipe_id
					HAVING		recipe_id = ' . $dbo->sqlsafe( $this->recipe_id ) . '
				)
				AS t2
				ON 1
				LIMIT 1'
			;
			$maxorderquery = $dbo->query( $query );
			
			if ( $dbo->hasError( $maxorderquery ) ) {
				$dbo->submitErrorLog( $maxorderquery, 'CF_Direction::commit()' );
				throw new Exception( 'Failed committing the direction properties to the database!', -1 );
			} elseif ( $dbo->num_rows( $maxorderquery ) == 1 ) {
				$maxorder = $dbo->getResultObject( $maxorderquery )->fetch_row()[0];
			}
			
			$query	=	'
				INSERT INTO	' . CF_DIRECTIONS_TABLE . '
				(
					recipe_id,
					direction,
					`order`
				)
				VALUES(
					' . $dbo->sqlsafe( $this->recipe_id ) . ',
					"' . $dbo->sqlsafe( $this->direction ) . '",
					' . $dbo->sqlsafe( $maxorder ) . '
				)'
			;
			
			$insertquery = $dbo->query( $query );
			
			if ( $dbo->hasError( $insertquery ) ) {
				$dbo->submitErrorLog( $insertquery, 'CF_Direction::commit()' );
				throw new Exception( 'Failed committing the direction properties to the database!', -1 );
			}
			
			$this->order = $maxorder;
			
		}
		
		return true;
	}
	
	// Removes direction from database
	// Note: After running this function, all references to this object should be deleted.
	public function remove() {
		$dbo =& CF_Factory::getDBO();
		
		$query	=	'
			DELETE FROM	' . CF_DIRECTIONS_TABLE . '
			WHERE		recipe_id = ' . $dbo->sqlsafe( $this->recipe_id ) . '
			AND			`order` = ' . $dbo->sqlsafe( $this->order );
		;
		$deletequery = $dbo->query( $query );
		
		if ( $dbo->hasError( $deletequery ) ) {
			$dbo->submitErrorLog( $deletequery, 'CF_Direction::remove()' );
			throw new Exception( 'Failed removing the direction from the database!', -1 );
		}
		
		return true;
	}
	
	// Miscellaneous Functions
	public function __toString() {
		return $this->direction;
	}
	
}

?>