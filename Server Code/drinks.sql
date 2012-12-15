-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 14, 2012 at 05:14 PM
-- Server version: 5.5.28-0ubuntu0.12.04.2
-- PHP Version: 5.4.9-4~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `drinks`
--

-- --------------------------------------------------------

--
-- Table structure for table `bars`
--

CREATE TABLE IF NOT EXISTS `bars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `location` text NOT NULL,
  `lat` double(20,15) NOT NULL,
  `lng` double(20,15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='This table contains all the bars.' AUTO_INCREMENT=7 ;

--
-- Dumping data for table `bars`
--

INSERT INTO `bars` (`id`, `name`, `location`, `lat`, `lng`) VALUES
(1, 'Absinthe House', '1109 Walnut St, Boulder, CO 80302', 40.016808300000000, -105.280620800000000),
(2, 'Walrus Saloon', '1911 11th St, Boulder, CO 80302', 40.016854000000000, -105.281269000000000),
(3, 'Rio Grande Boulder', '1101 Walnut St, Boulder, CO 80302', 40.016806500000000, -105.280776300000000),
(4, 'Conor O''Neills', '1922 13th St, Boulder, CO 80302', 40.017641600000000, -105.278348700000000),
(5, 'Catacombs', '2115 13th St, Boulder, CO 80302', 40.019392000000000, -105.279428900000000),
(6, 'Sundown Saloon', '1136 Pearl St, Pearl Street Mall, Boulder, CO 80302', 40.017508000000000, -105.280272900000000);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='This table stores all the different recipe categories.' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Cocktail'),
(2, 'Spirit');

-- --------------------------------------------------------

--
-- Table structure for table `cocktails`
--

CREATE TABLE IF NOT EXISTS `cocktails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `category` tinyint(3) unsigned NOT NULL,
  `picture` text NOT NULL,
  `hits` bigint(20) unsigned NOT NULL DEFAULT '0',
  `points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `numVotes` bigint(20) unsigned NOT NULL DEFAULT '0',
  `tagged_bars` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='This table contains all the cocktails.' AUTO_INCREMENT=19 ;

--
-- Dumping data for table `cocktails`
--

INSERT INTO `cocktails` (`id`, `name`, `category`, `picture`, `hits`, `points`, `numVotes`, `tagged_bars`) VALUES
(1, 'J&B on the Rocks', 2, '1.jpg', 0, 29, 8, '1,2,3'),
(4, 'Margarita', 1, '4.jpg', 0, 7, 2, '1,1,1,3'),
(5, 'The Big Tex Margarita', 1, '5.jpg', 0, 3, 1, '6,1,3'),
(6, 'Ricky', 1, '6.jpg', 0, 7, 2, '3'),
(7, 'Long Island Ice Tea', 1, '7.jpg', 0, 14, 4, '2,3,5,5,5'),
(8, 'Adios Motherfucker', 1, '8.jpg', 0, 15, 3, '1'),
(9, 'Irish Coffee', 1, '9.jpg', 0, 3, 1, '4'),
(10, 'Washington Apple', 1, '10.jpg', 0, 0, 0, ''),
(11, 'Fat Albert', 1, '11.jpg', 0, 0, 0, ''),
(12, 'Rum and Coke', 1, '12.jpg', 0, 4, 1, ''),
(13, 'Jack and Coke', 1, '13.jpg', 0, 4, 1, ''),
(16, 'Three Wise Men', 1, '16.jpg', 0, 7, 2, ''),
(17, 'Fireball Whiskey', 2, '17.jpg', 0, 6, 2, '2,5,1,1,1,4,3'),
(18, 'Horrible Drink', 2, '18.png', 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `directions`
--

CREATE TABLE IF NOT EXISTS `directions` (
  `recipe_id` bigint(20) unsigned NOT NULL COMMENT 'Refers to the recipes.id field.',
  `direction` text NOT NULL,
  `order` tinyint(3) unsigned NOT NULL COMMENT 'Determines the position of this direction in the recipe''s set of directions.',
  KEY `recipe_id` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains all the directions for recipes.';

--
-- Dumping data for table `directions`
--

INSERT INTO `directions` (`recipe_id`, `direction`, `order`) VALUES
(1, 'Pour the J&B into a chilled glass filled with ice cubes.', 1),
(1, 'Enjoy.', 2),
(4, '(Optional) Run the rim of a cocktail glass with lime juice, and dip in salt.', 1),
(4, 'Shake all ingredients with ice, strain into the glass.', 2),
(4, 'Serve', 3),
(5, '(Optional) Run the rim of a cocktail glass with lime juice, and dip in salt.', 1),
(5, 'Shake together with ice and strain into a margarita glass.', 2),
(5, 'Add a lime wedge to the rim of the glass as a garnish.', 3),
(6, '(Optional) Run the rim of a cocktail glass with lime juice, and dip in salt.', 1),
(6, 'Pour tequila, triple sec, lime juice, and crushed ice into a blender. Blend well at high speed.', 2),
(6, 'Pour the contents of the blender into a glass.', 3),
(6, 'Place the opened mini Corona in the glass upside down.', 4),
(6, '(Optional) Add a lime wedge as a garnish.', 5),
(7, 'Add gin, vodka, silver spiced rum, tequila, triple sec, and sweet and sour mix in ice-filled Collins glass and stir.', 1),
(7, 'Top off the glass with cola.', 2),
(7, '(Optional) Garnish with a lemon wedge.', 3),
(8, 'Pour all ingredients except the 7-Up in a chilled glass filled with ice cubes.', 1),
(8, 'Top off with 7-Up.', 2),
(8, 'Stir gently.', 3),
(8, '(Optional) Garnish with a lime wedge.', 4),
(9, 'Combine whiskey, sugar and coffee in a mug and stir to dissolve.', 1),
(9, 'Float cold cream gently on top. Do not mix.', 2),
(10, 'Pour Crown Royal Canadian whiskey, sour apple pucker, and cranberry juice into a cocktail shaker.', 1),
(10, 'Shake and strain into a shot glass.', 2),
(10, 'Add a splash of 7-Up if desired.', 3),
(10, 'Serve', 4),
(11, 'Pour all ingredients into a chilled glass filled with ice cubes.', 1),
(11, 'Stir and enjoy.', 2),
(12, 'Pour ingredients into a chilled glass with ice cubes. ', 1),
(12, 'Stir and enjoy.', 2),
(13, 'Pour ingredients into a chilled glass with ice cubes. ', 1),
(13, 'Stir and enjoy.', 2),
(16, 'Pour into a shot glass.', 1),
(16, 'Bottoms up.', 2),
(17, 'Pour cold fireball into a shot glass.', 1),
(17, 'Take the shot.', 2),
(17, 'Prepare for a warming sensation.', 3),
(18, 'Mix in bucket, guzzle', 1);

-- --------------------------------------------------------

--
-- Table structure for table `error_log`
--

CREATE TABLE IF NOT EXISTS `error_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Internal Error ID',
  `description` text NOT NULL COMMENT 'Custom Error Description and Associated Query',
  `error` text COMMENT 'MySQL Generated Error Message',
  `datelogged` datetime NOT NULL COMMENT 'Timestamp of Error',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Dumping data for table `error_log`
--

INSERT INTO `error_log` (`id`, `description`, `error`, `datelogged`) VALUES
(20, 'Custom Message:\nCF_BarManager::populate()\n\nAssociated Query:\n\n\r\n			SELECT		id, name, location\r\n			FROM		drinks.bars\r\n			WHERE		0 OR id = ', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 3', '2012-12-10 03:24:40'),
(21, 'Custom Message:\nCF_BarManager::findBarsWithDrink() - Get bar names\n\nAssociated Query:\n\n\r\n			SELECT		id, name, location\r\n			FROM		drinks.bars\r\n			WHERE		0 OR id = ', 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '''' at line 3', '2012-12-10 03:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE IF NOT EXISTS `ingredients` (
  `recipe_id` bigint(20) unsigned NOT NULL COMMENT 'Refers to the recipes.id field.',
  `name` text NOT NULL,
  `quantity` float(6,3) unsigned NOT NULL COMMENT 'The number of this ingredient the recipe calls for.',
  `measure` bigint(20) unsigned NOT NULL COMMENT 'Refers to the measures.id field.',
  `order` tinyint(3) unsigned NOT NULL COMMENT 'Determines the position of this ingredient in the recipe''s ingredient list.',
  KEY `recipe_id` (`recipe_id`),
  KEY `measure` (`measure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores all of the ingredients for recipes.';

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`recipe_id`, `name`, `quantity`, `measure`, `order`) VALUES
(1, 'J&B Scotch Whiskey', 3.000, 5, 1),
(1, 'ice cubes', 1.000, 1, 2),
(4, 'tequila', 1.500, 4, 1),
(4, 'triple sec', 0.500, 4, 2),
(4, 'lime juice', 1.000, 4, 3),
(5, 'orange liquer', 0.500, 4, 1),
(5, 'silver tequila', 2.000, 4, 2),
(5, 'lemon and lime juice', 1.000, 4, 3),
(6, 'white tequila', 3.000, 4, 1),
(6, 'triple sec', 1.000, 4, 2),
(6, 'lime juice', 2.000, 4, 3),
(6, 'crushed ice', 1.000, 1, 4),
(6, 'mini Corona', 1.000, 6, 5),
(7, 'gin', 0.250, 4, 1),
(7, 'vodka', 0.250, 4, 2),
(7, 'silver spiced rum', 0.250, 4, 3),
(7, 'gold tequila', 0.250, 4, 4),
(7, 'triple sec', 0.250, 4, 5),
(7, 'sweet and sour mix', 1.000, 4, 6),
(7, 'cola', 6.000, 4, 7),
(8, 'vodka', 0.500, 4, 1),
(8, 'rum', 0.500, 4, 2),
(8, 'tequila', 0.500, 4, 3),
(8, 'gin', 0.500, 4, 4),
(8, 'Blue Curacao liqueur', 0.500, 4, 5),
(8, 'sweet and sour mix', 2.000, 4, 6),
(8, '7-Up soda', 2.000, 4, 7),
(9, 'Irish whiskey', 1.500, 4, 1),
(9, 'brown sugar', 1.000, 3, 2),
(9, 'hot coffee', 6.000, 4, 3),
(9, 'whipped cream', 1.000, 6, 4),
(10, 'Crown Royal Canadian whiskey', 0.330, 4, 1),
(10, 'sour apple pucker schnapps', 0.330, 4, 2),
(10, 'cranberry juice', 0.330, 4, 3),
(10, '7-Up soda', 2.000, 4, 4),
(11, 'vodka', 3.000, 4, 1),
(11, 'grape Koolaid', 6.000, 4, 2),
(12, 'rum', 1.500, 4, 1),
(12, 'Coke', 4.000, 4, 2),
(13, 'Jack Daniel''s Tennessee Whiskey', 1.500, 4, 1),
(13, 'Coke', 4.000, 4, 2),
(16, 'Johnnie Walker Scotch whiskey', 0.500, 4, 1),
(16, 'Jim Beam bourbon whiskey', 0.500, 4, 2),
(16, 'Jack Daniel''s Tennessee whiskey', 0.500, 4, 3),
(17, 'Fireball whiskey', 1.500, 4, 1),
(18, 'tequila', 6.000, 1, 1),
(18, 'lime juice', 1.000, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `measures`
--

CREATE TABLE IF NOT EXISTS `measures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL COMMENT 'This should be the name of the unit (singular).',
  `abbreviation` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='This table stores the different measuring units.' AUTO_INCREMENT=7 ;

--
-- Dumping data for table `measures`
--

INSERT INTO `measures` (`id`, `name`, `abbreviation`) VALUES
(1, 'cup', 'cup'),
(2, 'tablespoon', 'tbsp'),
(3, 'teaspoon', 'tsp'),
(4, 'ounce', 'oz'),
(5, 'finger', 'finger'),
(6, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL COMMENT 'Internal System Message ID',
  `message` text NOT NULL COMMENT 'Message Content',
  `type` tinyint(3) NOT NULL COMMENT 'Type of Message',
  `sessionID` char(9) NOT NULL COMMENT 'Associated Session ID for Message',
  PRIMARY KEY (`id`),
  KEY `FK_mc_messages` (`sessionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `recipe_id` bigint(20) unsigned NOT NULL,
  `date_time` datetime NOT NULL,
  `message` text NOT NULL,
  KEY `recipe_id` (`recipe_id`),
  KEY `recipe_id_2` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores all of the user reviews of recipes.';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cocktails`
--
ALTER TABLE `cocktails`
  ADD CONSTRAINT `cocktails_ibfk_1` FOREIGN KEY (`category`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `directions`
--
ALTER TABLE `directions`
  ADD CONSTRAINT `directions_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `cocktails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `cocktails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ingredients_ibfk_3` FOREIGN KEY (`measure`) REFERENCES `measures` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `cocktails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
