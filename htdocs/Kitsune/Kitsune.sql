-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2016 at 08:21 PM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kitsune`
--

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
`ID` int(11) NOT NULL,
  `Moderator` char(12) NOT NULL,
  `Player` int(11) unsigned NOT NULL,
  `Comment` text NOT NULL,
  `Expiration` int(8) NOT NULL,
  `Time` int(8) NOT NULL,
  `Type` smallint(3) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `igloos`
--

CREATE TABLE IF NOT EXISTS `igloos` (
`ID` int(10) unsigned NOT NULL,
  `Owner` int(10) unsigned NOT NULL,
  `Type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `Floor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Music` smallint(6) NOT NULL DEFAULT '0',
  `Furniture` text NOT NULL,
  `Locked` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `igloos`
--

INSERT INTO `igloos` (`ID`, `Owner`, `Type`, `Floor`, `Music`, `Furniture`, `Locked`) VALUES
(1, 101, 23, 0, 0, '224|407|277|1|1,225|560|306|1|1,208|264|374|1|1,210|485|371|1|1,207|299|387|1|1,221|367|298|1|1,211|454|342|1|1', 1),
(2, 102, 1, 0, 0, '61|508|298|2|1,61|335|265|8|1,63|224|351|6|1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `penguins`
--

CREATE TABLE IF NOT EXISTS `penguins` (
`ID` int(10) unsigned NOT NULL,
  `Username` char(12) NOT NULL,
  `Nickname` char(16) NOT NULL,
  `Password` char(32) NOT NULL,
  `LoginKey` char(32) NOT NULL,
  `Email` char(254) NOT NULL,
  `RegistrationDate` int(8) NOT NULL,
  `Moderator` tinyint(1) NOT NULL DEFAULT '0',
  `Inventory` text NOT NULL,
  `Coins` mediumint(7) unsigned NOT NULL DEFAULT '500',
  `Igloo` int(10) unsigned NOT NULL COMMENT 'Current active igloo',
  `Igloos` text NOT NULL COMMENT 'Owned igloo types',
  `Furniture` text NOT NULL COMMENT 'Furniture inventory',
  `Color` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `Head` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Face` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Neck` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Body` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Hand` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Feet` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Photo` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Flag` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Walking` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Puffle ID',
  `Banned` varchar(20) NOT NULL DEFAULT '0' COMMENT 'Timestamp of ban',
  `Stamps` text NOT NULL,
  `StampBook` varchar(150) NOT NULL DEFAULT '1%1%-1%1',
  `EPF` varchar(9) NOT NULL DEFAULT '0,0,0',
  `Buddies` text NOT NULL,
  `Ignores` text NOT NULL,
  `MinutesPlayed` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `penguins`
--

INSERT INTO `penguins` (`ID`, `Username`, `Nickname`, `Password`, `LoginKey`, `Email`, `RegistrationDate`, `Moderator`, `Inventory`, `Coins`, `Igloo`, `Igloos`, `Furniture`, `Color`, `Head`, `Face`, `Neck`, `Body`, `Hand`, `Feet`, `Photo`, `Flag`, `Walking`, `Banned`, `Stamps`, `StampBook`, `EPF`, `Buddies`, `Ignores`, `MinutesPlayed`) VALUES
(101, 'Ben_', 'Ben', '5F4DCC3B5AA765D61D8327DEB882CF99', '', 'solerian.godess@solero.me', 1419245107, 1, '1%712%301%7%15%234%340%6%293%5014%180%729%428%821', 184470, 1, '1,23', '210|1,224|1,208|1,207|1,225|1,221|1,218|1,211|1,200|1', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0', '332,10,13,14,20,', '2%4%6%3%0|10|212|162|0|1%0|14|475|219|0|3%0|20|292|255|0|5%0|13|434|329|0|7', '0,0,0', '103|Nickname1%102|Nickname', '', 1150),
(102, 'Nickname', 'Nickname', '5F4DCC3B5AA765D61D8327DEB882CF99', '', '', 1450645107, 0, '1%821', 200168, 2, '', '61|2,63|1', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0', '', '1%1%1%1', '0,0,0', '101|Ben_', '', 552),
(103, 'Nickname1', 'Nickname1', '5F4DCC3B5AA765D61D8327DEB882CF99', '', '', 1450645107, 0, '%821', 200000, 0, '', '', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, '0', '', '1%1%1%1', '0,0,0', '101|Ben_', '', 64);

-- --------------------------------------------------------

--
-- Table structure for table `postcards`
--

CREATE TABLE IF NOT EXISTS `postcards` (
`ID` int(10) unsigned NOT NULL,
  `Recipient` int(10) unsigned NOT NULL,
  `SenderName` char(12) NOT NULL,
  `SenderID` int(10) unsigned NOT NULL,
  `Details` varchar(12) NOT NULL,
  `Date` int(8) NOT NULL,
  `Type` smallint(5) unsigned NOT NULL,
  `HasRead` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `postcards`
--

INSERT INTO `postcards` (`ID`, `Recipient`, `SenderName`, `SenderID`, `Details`, `Date`, `Type`, `HasRead`) VALUES
(2, 102, 'Ben_', 101, '', 1453826828, 37, 1),
(4, 102, 'Ben_', 101, '', 1454107091, 217, 1);

-- --------------------------------------------------------

--
-- Table structure for table `puffles`
--

CREATE TABLE IF NOT EXISTS `puffles` (
`ID` int(10) unsigned NOT NULL,
  `Owner` int(10) unsigned NOT NULL,
  `Name` char(12) NOT NULL,
  `AdoptionDate` int(8) NOT NULL,
  `Type` tinyint(3) unsigned NOT NULL,
  `Food` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `Play` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `Rest` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `Walking` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `puffles`
--

INSERT INTO `puffles` (`ID`, `Owner`, `Name`, `AdoptionDate`, `Type`, `Food`, `Play`, `Rest`, `Walking`) VALUES
(3, 101, 'Blue', 1453750614, 0, 100, 100, 100, 0),
(4, 101, 'Blue', 1453752421, 0, 100, 100, 100, 0),
(5, 101, 'Red', 1453753127, 5, 100, 100, 100, 1),
(6, 101, 'Yellow', 1453753887, 6, 100, 100, 100, 0),
(7, 101, 'Pink', 1453829330, 1, 100, 100, 100, 0),
(8, 101, 'Purple', 1454159945, 4, 100, 100, 100, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bans`
--
ALTER TABLE `bans`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `Time` (`Time`);

--
-- Indexes for table `igloos`
--
ALTER TABLE `igloos`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `penguins`
--
ALTER TABLE `penguins`
 ADD PRIMARY KEY (`ID`), ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `postcards`
--
ALTER TABLE `postcards`
 ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `puffles`
--
ALTER TABLE `puffles`
 ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bans`
--
ALTER TABLE `bans`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `igloos`
--
ALTER TABLE `igloos`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `penguins`
--
ALTER TABLE `penguins`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=104;
--
-- AUTO_INCREMENT for table `postcards`
--
ALTER TABLE `postcards`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `puffles`
--
ALTER TABLE `puffles`
MODIFY `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
