<?php

// Prevent direct access
defined( '_CF_EXEC' ) or die( 'Restricted access' );

class CF_Cocktail {
	
	// All of these properties are "protected" to allow for child class derivations in the future
	protected $id;
	protected $name;
	protected $category;
	protected $categoryName;
	protected $picture;
	protected $hits;
	protected $points;
	protected $numVotes;
	protected $ingredients;
	protected $directions;
	protected $tagged_bars;
	
	// Initializes a new cocktail object with the specified user's information
	public function __construct( $id = null ) {
		$dbo =& CF_Factory::getDBO();
		
		if ( !is_null( $id ) ) {
		// Load cocktail information
			$query	=	'
				SELECT		c.*, cat.name AS category_name
				FROM		' . CF_COCKTAILS_TABLE . ' c, ' . CF_CATEGORIES_TABLE . ' cat
				WHERE		c.id = ' . $dbo->sqlsafe( $id ) . '
				AND			c.category = cat.id'
			;
			$cocktailresult = $dbo->query( $query );
			
			if ( $dbo->hasError( $cocktailresult ) ) {
				$dbo->submitErrorLog( $cocktailresult, 'CF_Cocktail::__construct()' );
				throw new Exception( 'Could not load the cocktail information!', CF_MSG_ERROR );
			}
			if ( $dbo->num_rows( $cocktailresult ) != 1 ) {
				throw new Exception( 'The specified cocktail does not exist!', CF_MSG_ERROR );
			}
			
			$row = $dbo->getResultObject( $cocktailresult )->fetch_object();
			$this->id			=	$row->id;
			$this->name			=	$row->name;
			$this->category		=	$row->category;
			$this->categoryName	=	$row->category_name;
			$this->picture		=	$row->picture;
			$this->hits			=	$row->hits;
			$this->points		=	$row->points;
			$this->numVotes		=	$row->numVotes;
			$this->tagged_bars	=	$row->tagged_bars;
			
			
			// Load ingredient information
			$query	=	'
				SELECT	`order`
				FROM		' . CF_INGREDIENTS_TABLE . '
				WHERE		recipe_id = "' . $dbo->sqlsafe( $id ) . '"
				ORDER BY	`order` ASC'
			;
			$ingredientresult = $dbo->query( $query );
			
			if ( $dbo->hasError( $ingredientresult ) ) {
				$dbo->submitErrorLog( $ingredientresult, 'CF_Cocktail::__construct()' );
				throw new Exception( 'Could not load the cocktail\'s ingredient information!', CF_MSG_ERROR );
			}
			if ( $dbo->num_rows( $ingredientresult ) < 1 ) {
				throw new Exception( 'The specified cocktail does not contain any ingredients!', CF_MSG_ERROR );
			} else {
				while ( $row = $dbo->getResultObject( $ingredientresult )->fetch_object() ) {
					$this->ingredients[] = new CF_Ingredient( $id, $row->order );
				}
			}
			
			// Load direction information
			$query	=	'
				SELECT	`order`
				FROM		' . CF_DIRECTIONS_TABLE . '
				WHERE		recipe_id = "' . $dbo->sqlsafe( $id ) . '"
				ORDER BY	`order` ASC'
			;
			$directionresult = $dbo->query( $query );
			
			if ( $dbo->hasError( $directionresult ) ) {
				$dbo->submitErrorLog( $directionresult, 'CF_Cocktail::__construct()' );
				throw new Exception( 'Could not load the cocktail\'s direction information!', CF_MSG_ERROR );
			}
			if ( $dbo->num_rows( $directionresult ) < 1 ) {
				throw new Exception( 'The specified cocktail does not contain any directions!', CF_MSG_ERROR );
			} else {
				while ( $row = $dbo->getResultObject( $directionresult )->fetch_object() ) {
					$this->directions[] = new CF_Direction( $id, $row->order );
				}
			}
		} else {
			$this->id = null;
		}
	}
	
	// Getters and Setters
	public function getID() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getCategory() {
		return array( $this->category, $this->categoryName );
	}
	
	public function getPicture() {
		return $this->picture;
	}
	
	public function getRating() {
		if ( $this->numVotes == 0 )
			return (float) 0;
		return array( ((float) $this->points) / $this->numVotes, $this->numVotes );
	}
	
	public function setParameters( $name, $category, $picture ) {
		$this->name = $name;
		$this->category = $category;
		$this->picture = $picture;
		$this->points = 0;
		$this->numVotes = 0;
	}
	
	public function getIngredients() {
		return $this->ingredients;
	}
	
	public function getDirections() {
		return $this->directions;
	}
	
	public function getReviews() {
		$dbo =& CF_Factory::getDBO();
		
		// Load review information
		$query	=	'
			SELECT		message, UNIX_TIMESTAMP( date_time ) AS date_time
			FROM		' . CF_REVIEWS_TABLE . '
			WHERE		recipe_id = ' . $dbo->sqlsafe( $this->id ) . '
			ORDER BY	date_time DESC'
		;
		$reviewresult = $dbo->query( $query );
		
		if ( $dbo->hasError( $reviewresult ) ) {
			$dbo->submitErrorLog( $reviewresult, 'CF_Recipe::getReviews()' );
			throw new Exception( 'Could not load the review information!', CF_MSG_ERROR );
		}
		
		$reviews = array();
		while ( $row = $dbo->getResultObject( $reviewresult )->fetch_object() ) {
			$reviews[] = array( $row->message, $row->date_time );
		}
		
		return $reviews;
	}
	
	public function addReview( $message ) {
		$dbo =& CF_Factory::getDBO();
		
		// Insert review information
		$query	=	'
			INSERT INTO	' . CF_REVIEWS_TABLE . '
			( recipe_id, message, date_time )
			VALUES(
				' . $dbo->sqlsafe( $this->id ) . ',
				"' . $dbo->sqlsafe( $message ) . '",
				NOW()
			)'
		;
		$reviewresult = $dbo->query( $query );
		
		if ( $dbo->hasError( $reviewresult ) ) {
			$dbo->submitErrorLog( $reviewresult, 'CF_Recipe::addReview()' );
			throw new Exception( 'Could not add the review!', CF_MSG_ERROR );
		}
		
		return true;
	}
	
	// Commits the state of the object to the database
	public function commit() {
		$dbo =& CF_Factory::getDBO();
		
		if ( empty( $this->name ) || empty( $this->ingredients ) || empty( $this->directions ) ) {
			throw new Exception( 'Required information not provided!', 1 );
		} else {
			/*if ( preg_match( '/[^A-Za-z0-9\, \'\-\(\)]/', $this->name ) > 0 ) {
				throw new Exception( 'Invalid name', 2 );
			} else*/if ( !empty( $this->picture ) && !pathinfo( $this->picture, PATHINFO_EXTENSION ) ) {
				throw new Exception( 'Invalid picture', 3 );
			} elseif ( $this->points < 0 || $this->points < $this->numVotes ) {
				throw new Exception( 'Invalid rating', 4 );
			} elseif ( $this->numVotes < 0 ) {
				throw new Exception( 'Invalid vote count', 5 );
			} elseif ( count( $this->ingredients ) < 1 ) {
				throw new Exception( 'Invalid or not enough ingredients', 6 );
			} elseif ( count( $this->directions ) < 1 ) {
				throw new Exception( 'Invalid or not enough directions', 7 );
			}
		}
		
		if ( !is_null( $this->id ) ) {
			$query	=	'
				UPDATE	' . CF_COCKTAILS_TABLE . '
				SET		name = "' . $dbo->sqlsafe( $this->name ) . '",
						category = ' . $dbo->sqlsafe( $this->category ) . ',
						picture = "' . $dbo->sqlsafe( $this->picture ) . '",
						points = ' . $dbo->sqlsafe( $this->points ) . ',
						numVotes = ' . $dbo->sqlsafe( $this->numVotes ) . ',
						tagged_bars = "' . $dbo->sqlsafe( $this->tagged_bars ) . '"
				WHERE		id = ' . $dbo->sqlsafe( $this->id )
			;
			$updatequery = $dbo->query( $query );
			
			if ( $dbo->hasError( $updatequery ) ) {
				$dbo->submitErrorLog( $updatequery, 'CF_Cocktail::commit()' );
				throw new Exception( 'Failed committing the cocktail properties to the database!', -1 );
			}
		} else {
			$query	=	'
				INSERT INTO	' . CF_COCKTAILS_TABLE . '
				(
					name,
					category,
					picture,
					points,
					numVotes,
					tagged_bars
				)
				VALUES(
					"' . $dbo->sqlsafe( $this->name ) . '",
					' . $dbo->sqlsafe( $this->category ) . ',
					"' . $dbo->sqlsafe( $this->picture ) . '",
					0,
					0,
					""
				)'
			;
			$insertquery = $dbo->query( $query );
			
			if ( $dbo->hasError( $insertquery ) ) {
				$dbo->submitErrorLog( $insertquery, 'CF_Cocktail::commit()' );
				throw new Exception( 'Failed committing the cocktail properties to the database!', -1 );
			} else {
				// Get the auto-incremented ID of the insert query
				$this->id = $dbo->insert_id;
			}
			
			// Update all the cocktail IDs in ingredients
			foreach ( $this->ingredients as $ingredient ) {
				$ingredient->setID( $this->id );
			}
			
			// Commit all the cocktail IDs in directions
			foreach ( $this->directions as $direction ) {
				$direction->setID( $this->id );
			}
			
		}
		
		// Commit all ingredients
		foreach ( $this->ingredients as $ingredient ) {
			$ingredient->commit();
		}
		
		// Commit all directions
		foreach ( $this->directions as $direction ) {
			$direction->commit();
		}
		
		// Retrieve the new category name
		$query	=	'
			SELECT	name
			FROM	' . CF_CATEGORIES_TABLE . '
			WHERE	id = ' . $dbo->sqlsafe( $this->category )
		;
		$categoryquery = $dbo->query( $query );
		
		if ( $dbo->hasError( $categoryquery ) ) {
			$dbo->submitErrorLog( $categoryquery, 'CF_Recipe::commit()' );
			throw new Exception( 'Failed retrieving the new category name from the database!', -1 );
		} elseif ( $dbo->num_rows( $categoryquery ) == 1 ) {
			$this->categoryName = $dbo->getResultObject( $categoryquery )->fetch_row()[0];
		}
		
		return true;
	}
	
	public static function add( array $parameters ) {
		// Basic error checking
		if ( !(count( $parameters['ingredients'] ) == count( $parameters['quantities'] ) && count( $parameters['quantities'] ) == count( $parameters['measures'] )) ) {
			throw new Exception( 'Stop trying to hack my web app!', CF_MSG_ERROR );
		}
		
		// Create new cocktail object and assign all values
		$cocktail = new CF_Cocktail();
		// Set cocktail parameters
		$cocktail->setParameters( $parameters['name'], $parameters['category'], '' );
		// Add ingredients
		for ( $i = 0; $i < count( $parameters['ingredients'] ); ++$i ) {
			$cocktail->addIngredient( $parameters['ingredients'][$i], $parameters['quantities'][$i], $parameters['measures'][$i] );
		}
		// Add directions
		for ( $i = 0; $i < count( $parameters['directions'] ); ++$i ) {
			$cocktail->addDirection( $parameters['directions'][$i] );
		}
		
		// Commit changes to database
		$cocktail->commit();
		
		// If a picture was uploaded, save it to the database.
		// Note: We have to do this here because our image naming convention depends on the cocktail's ID,
		//       which does not exist until we commit once already.
		if (
			!empty( $parameters['picture'] ) &&
			move_uploaded_file( $parameters['picture']['tmp_name'], CF_DIR_THUMBNAILS . $cocktail->getID() . '.' . pathinfo( $parameters['picture']['name'], PATHINFO_EXTENSION ) )
		) {
			$cocktail->setParameters( $parameters['name'], $parameters['category'], $cocktail->getID() . '.' . pathinfo( $parameters['picture']['name'], PATHINFO_EXTENSION ) );
			$cocktail->commit();
		}
		
		return $cocktail;
	}
	
	public function edit( array $parameters ) {
		$this->name = $parameters['name'];
		$this->category = $parameters['category'];
		$this->picture = $parameters['picture'];
		
		if ( !(count( $parameters['ingredients'] ) == count( $parameters['quantities'] ) && count( $parameters['quantities'] ) == count( $parameters['measures'] )) ) {
			throw new Exception( 'Stop trying to hack my web app!', CF_MSG_ERROR );
		}
		
		// Update ingredients
		for ( $i = 0; $i < min( count( $this->ingredients ), count( $parameters['ingredients'] ) ); ++$i ) {
			$this->ingredients[$i]->setParameters( $parameters['ingredients'][$i], $parameters['quantities'][$i], $parameters['measures'][$i] );
		}
		
		// Update directions
		for ( $i = 0; $i < min( count( $this->directions ), count( $parameters['directions'] ) ); ++$i ) {
			$this->directions[$i]->setDirection( $parameters['directions'][$i] );
		}
		
		// Add/remove ingredients
		$delta = count( $parameters['quantities'] ) - count( $this->ingredients );
		if ( $delta > 0 ) {
			$i_count = count( $this->ingredients );
			for ( $i = 0; $i < $delta; ++$i ) {
				$this->addIngredient( $parameters['ingredients'][$i_count + $i], $parameters['quantities'][$i_count + $i], $parameters['measures'][$i_count + $i] );
			}
		} elseif ( $delta < 0 ) {
			for ( $i = 0; $i < abs( $delta ); ++$i ) {
				$this->removeIngredient( count( $this->ingredients ) );
			}
		}
		
		// Add/remove directions
		$delta = count( $parameters['directions'] ) - count( $this->directions );
		if ( $delta > 0 ) {
			$d_count = count( $this->directions );
			for ( $i = 0; $i < $delta; ++$i ) {
				$this->addDirection( $parameters['directions'][$d_count + $i] );
			}
		} elseif ( $delta < 0 ) {
			for ( $i = 0; $i < abs( $delta ); ++$i ) {
				$this->removeDirection( count( $this->directions ) );
			}
		}
		
		$this->commit();
	}
	
	// Removes cocktail from database
	// Note: After running this function, all references to this object should be deleted.
	public function remove() {
		$dbo =& CF_Factory::getDBO();
		
		// Note: We only have to delete from this table because the entries from
		//       the other tables get deleted due to foreign key constraints.
		$query	=	'
			DELETE FROM		' . CF_COCKTAILS_TABLE . '
			WHERE			id = ' . $dbo->sqlsafe( $this->id )
		;
		$deleteresult = $dbo->query( $query );
		
		if ( $dbo->hasError( $deleteresult ) ) {
			$dbo->submitErrorLog( $deleteresult, 'CF_Cocktail::remove()' );
			throw new Exception( 'Error while deleting the cocktail.', CF_MSG_ERROR );
		}
		if ( $dbo->affected_rows < 1 ) {
			throw new Exception( 'The specified cocktail does not exist!', CF_MSG_ERROR );
		}
	}
	
	public function addIngredient( $name, $quantity, $measure ) {
		$ingredient = new CF_Ingredient( $this->id );
		$ingredient->setParameters( $name, $quantity, $measure );
		$this->ingredients[] = $ingredient;
	}
	
	public function addDirection( $message ) {
		$direction = new CF_Direction( $this->id );
		$direction->setDirection( $message );
		$this->directions[] = $direction;
	}
	
	public function removeIngredient( $order ) {
		if ( $order > 0 && $order <= count( $this->ingredients ) ) {
			$this->ingredients[$order - 1]->remove();
			array_splice( $this->ingredients, $order - 1, 1 );
		}
	}
	
	public function removeDirection( $order ) {
		if ( $order > 0 && $order <= count( $this->directions ) ) {
			$this->directions[$order - 1]->remove();
			array_splice( $this->directions, $order - 1, 1 );
		}
	}
	
	public function touch() {
		$this->hits++;
	}
	
	public function rate( $rating ) {
		if ( $rating < 0 || $rating > 5 ) {
			throw new Exception( 'Invalid rating', CF_MSG_ERROR );
		}
		
		$this->points += $rating;
		$this->numVotes++;
	}
	
	public function __toString() {
		$string = '';
		$string .= 'Name: ' . $this->name . "\n" . 'Rating: ' . $this->getRating()[0] . ' (' . $this->numVotes . " ratings)\n\nIngredients:\n";
		foreach ( $this->ingredients as $ingredient ) {
			$string .= $ingredient . "\n";
		}
		$string .= "\n\nDirections:\n";
		foreach ( $this->directions as $direction ) {
			$string .= $direction;
		}
		return $string;
	}
	
}

?>