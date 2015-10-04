-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Aug 22, 2014 at 08:31 PM
-- Server version: 5.1.73-0ubuntu0.10.04.1
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fsfahrt`
--
CREATE DATABASE IF NOT EXISTS `fsfahrt` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `fsfahrt`;

-- --------------------------------------------------------

--
-- Table structure for table `bachelor`
--

DROP TABLE IF EXISTS `bachelor`;
CREATE TABLE IF NOT EXISTS `bachelor` (
  `bachelor_id` varchar(15) NOT NULL,
  `fahrt_id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `forname` varchar(50) NOT NULL,
  `sirname` varchar(50) NOT NULL,
  `anday` varchar(10) NOT NULL,
  `abday` date NOT NULL,
  `antyp` varchar(100) NOT NULL,
  `abtyp` varchar(100) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `mehl` varchar(100) NOT NULL,
  `essen` varchar(50) NOT NULL,
  `public` int(11) NOT NULL,
  `virgin` int(11) NOT NULL,
  `studityp` varchar(11) NOT NULL,
  `comment` text NOT NULL,
  `paid` int(10) DEFAULT NULL COMMENT 'zahlung erhalten am unix timestamp',
  `repaid` int(10) DEFAULT NULL COMMENT 'rückzahlung abgeschickt am unix timestamp',
  `backstepped` int(10) DEFAULT NULL COMMENT 'rücktritt als unix timestamp',
  PRIMARY KEY (`bachelor_id`,`fahrt_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bachelor`
--

INSERT INTO `bachelor` (`bachelor_id`, `fahrt_id`, `version`, `forname`, `sirname`, `anday`, `abday`, `antyp`, `abtyp`, `pseudo`, `mehl`, `essen`, `public`, `virgin`, `studityp`, `comment`, `paid`, `repaid`, `backstepped`) VALUES
('5b61b92044983e1', 2, 1, 'asd', 'ad', '2012-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'ffas', 'asdasd@asd.de', 'Alles', 1, 0, '0', 'dasd', NULL, NULL, NULL),
('f35f12ca7c55462', 2, 1, 'fcacs', 'ads', '2012-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'fas', 'asd@asd.de', 'Alles', 0, 0, '0', 'adasdasda', NULL, NULL, NULL),
('068e4198f255a1e', 2, 1, 'göll', 'asd', '2012-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'adsad', 'adskd@asdl.de', 'Alles', 1, 0, '0', 'adasd', NULL, NULL, NULL),
('d748d40c0d7e475', 2, 1, 'ad', 'adsd', '2012-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'asdadl', 'asdas@asd.de', 'Vegan', 1, 0, '0', 'ad', NULL, NULL, NULL),
('ec2cac23f915bf9', 2, 1, 'gbhg', 'ncvbx', '2012-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'cvxcvxsdfs', 'ads@asdl.de', 'Alles', 1, 0, '0', 'ycyxc', NULL, NULL, 1408205076),
('78a322842b66657', 2, 1, 'lkblka', 'kbvnfj', '2012-03-20', '2014-03-20', 'individuell', 'gemeinsam mit Rad', 'kmkm', 'sdkk@ksad.de', 'Vegan', 1, 0, 'MasterErsti', 'asda', NULL, NULL, NULL),
('8d70b435d61c302', 2, 1, 'gm', 'sdlkjflkj', '2013-03-20', '2014-03-20', 'gemeinsam mit Rad', 'individuell', 'skldfmlk', 'sfjdkl@dfjklj.de', 'Grießbrei', 1, 0, 'Hoersti', 'asddsa', NULL, NULL, NULL),
('c8c1d8a327fd88f', 2, 1, 'lkdnl', 'sdkjfhnk', '2013-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'adhsj', 'fsfahrt@byom.de', 'Frutarisch', 1, 0, 'Hoersti', 'adas', 1408205076, NULL, NULL),
('7f5609769cce5f1', 2, 1, 'lkdnl', 'sdkjfhnk', '2013-03-20', '2014-03-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'adhsj', 'fsfahrt@byom.de', 'Frutarisch', 1, 0, 'Hoersti', 'adas', 1408105076, 1408205076, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fahrten`
--

DROP TABLE IF EXISTS `fahrten`;
CREATE TABLE IF NOT EXISTS `fahrten` (
  `fahrt_id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(200) NOT NULL,
  `ziel` varchar(100) NOT NULL,
  `von` date NOT NULL,
  `bis` date NOT NULL,
  `regopen` int(1) NOT NULL,
  `beschreibung` text NOT NULL,
  `leiter` varchar(100) NOT NULL,
  `kontakt` varchar(100) NOT NULL,
  PRIMARY KEY (`fahrt_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `fahrten`
--

INSERT INTO `fahrten` (`fahrt_id`, `titel`, `ziel`, `von`, `bis`, `regopen`, `beschreibung`, `leiter`, `kontakt`) VALUES
(1, 'Porno laut im Flur Fahrt', 'Irgendwo', '2012-10-17', '2012-10-19', 0, 'irgendein Text', 'Willi', 'hans@wurst.de'),
(2, 'Vodka in Hand Fahrt', 'Halbinsel', '2013-10-18', '2013-10-20', 1, 'Mehr Text passt nicht!', 'Tim', 'wahr@gi.na');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `fahrt_id` int(11) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`note_id`, `fahrt_id`, `note`) VALUES
(1, 1, 'fahrt1 note'),
(2, 2, '<h1><b><marquee>Testnotiz 123</marquee><br></b></h1>hier kann man <i>notizen </i>hinterlassen test<br><br><h2><b>Dumme Bemerkung</b></h2>Notiz =<span class="wysiwyg-color-red"> unlimited tits!</span><b><br></b><b><br></b>');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
