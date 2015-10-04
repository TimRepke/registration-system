-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 04, 2015 at 06:28 PM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `fsfahrt`
--

-- --------------------------------------------------------

--
-- Table structure for table `bachelor`
--

CREATE TABLE IF NOT EXISTS `bachelor` (
  `bachelor_id` varchar(15) NOT NULL,
  `fahrt_id` int(11) NOT NULL,
  `anm_time` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `forname` varchar(50) NOT NULL,
  `sirname` varchar(50) NOT NULL,
  `anday` date NOT NULL,
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

INSERT INTO `bachelor` (`bachelor_id`, `fahrt_id`, `anm_time`, `version`, `forname`, `sirname`, `anday`, `abday`, `antyp`, `abtyp`, `pseudo`, `mehl`, `essen`, `public`, `virgin`, `studityp`, `comment`, `paid`, `repaid`, `backstepped`) VALUES
('5b61b92044983e1', 2, 1411767105, 1, 'John', 'Doe', '2013-10-25', '2013-10-27', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'Nickname', 'some@mail.com', 'Alles', 1, 0, '0', 'Test comment', NULL, NULL, 1411059051);
-- --------------------------------------------------------

--
-- Table structure for table `cost`
--

CREATE TABLE IF NOT EXISTS `cost` (
  `fahrt_id` int(11) NOT NULL COMMENT 'trip associated to calculation',
  `tab1` text NOT NULL COMMENT 'JSON dump of tab1',
  `tab2` text NOT NULL COMMENT 'JSON dump of tab2',
  `tab3` text NOT NULL COMMENT 'JSON dump of tab3',
  `moneyIO` text NOT NULL COMMENT 'JSON dump of money IO',
  `collected` int(11) NOT NULL COMMENT 'amount collected per person before trip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cost`
--

INSERT INTO `cost` (`fahrt_id`, `tab1`, `tab2`, `tab3`, `moneyIO`, `collected`) VALUES
(2, '', '', '', '', 60);

-- --------------------------------------------------------

--
-- Table structure for table `fahrten`
--

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
  `map_pin` text NOT NULL,
  `max_bachelor` int(4) NOT NULL,
  `wikilink` varchar(255) NOT NULL DEFAULT 'https://wiki.fachschaft.informatik.hu-berlin.de/wiki/Erstsemesterfahrt',
  `paydeadline` date NOT NULL,
  `payinfo` text NOT NULL,
  `opentime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fahrt_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `fahrten`
--

INSERT INTO `fahrten` (`fahrt_id`, `titel`, `ziel`, `von`, `bis`, `regopen`, `beschreibung`, `leiter`, `kontakt`, `map_pin`, `max_bachelor`, `wikilink`, `paydeadline`, `payinfo`, `opentime`) VALUES
(2, 'Fachschaftsfahrt Winter 2013', 'KiEZ Frauensee bei Gräbendorf', '2013-10-25', '2013-10-27', 1, 'Erstsemester und Fachschaftsfahrt im Wintersemester 13/14<br>Alle Informationen im <a rel="nofollow" target="_blank" href="http://wiki.fachschaft.informatik.hu-berlin.de/wiki/Fachschaftsfahrt_Winter_2013">Wiki</a>', 'Orga Name', 'organame@mail.com', '52.43893109993363 13.648079039184609', 40, 'https://wiki.fachschaft.informatik.hu-berlin.de/wiki/Erstsemesterfahrt', '2015-09-24', 'Some\r\nPayment\r\nInfo\r\nHere', 1443639637);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

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
(2, 2, '<h1><b>Testnotiz 123<br></b></h1>hier kann man <i>notizen </i>hinterlassen test<br><br><h2><b>Dumme Bemerkung</b></h2>Notiz = no&nbsp;<span class="wysiwyg-color-red">tits!<br></span><b><br><br>aoisdkd<br>asdlji<br><br></b><b><br></b>');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE IF NOT EXISTS `waitlist` (
  `waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `fahrt_id` int(11) NOT NULL,
  `anm_time` int(11) NOT NULL,
  `forname` varchar(50) NOT NULL,
  `sirname` varchar(50) NOT NULL,
  `anday` date NOT NULL,
  `abday` date NOT NULL,
  `antyp` varchar(100) NOT NULL,
  `abtyp` varchar(100) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `mehl` varchar(100) NOT NULL,
  `essen` varchar(50) NOT NULL,
  `public` int(11) NOT NULL,
  `virgin` int(11) NOT NULL,
  `studityp` varchar(11) NOT NULL,
  `comment` text NOT NULL DEFAULT '',
  `transferred` int(11) DEFAULT NULL,
  PRIMARY KEY (`waitlist_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
