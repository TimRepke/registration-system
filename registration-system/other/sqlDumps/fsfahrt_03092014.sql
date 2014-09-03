-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Sep 03, 2014 at 07:42 PM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.14

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
  `anm_time` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `forname` varchar(50) NOT NULL,
  `sirname` varchar(50) NOT NULL,
  `anday` int(11) NOT NULL,
  `abday` int(11) NOT NULL,
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
  `backstepped` int(10) DEFAULT NULL COMMENT 'rücktritt als unix timestamp'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bachelor`
--

INSERT INTO `bachelor` (`bachelor_id`, `fahrt_id`, `anm_time`, `version`, `forname`, `sirname`, `anday`, `abday`, `antyp`, `abtyp`, `pseudo`, `mehl`, `essen`, `public`, `virgin`, `studityp`, `comment`, `paid`, `repaid`, `backstepped`) VALUES
('5b61b92044983e1', 2, 0, 1, 'asd', 'ad', 2012, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'ffas', 'asdasd@asd.de', 'Alles', 1, 0, '0', 'dasd', NULL, NULL, NULL),
('f35f12ca7c55462', 2, 0, 1, 'fcacs', 'ads', 2012, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'fas', 'asd@asd.de', 'Alles', 0, 0, '0', 'adasdasda', NULL, NULL, NULL),
('068e4198f255a1e', 2, 0, 1, 'göll', 'asd', 2012, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'adsad', 'adskd@asdl.de', 'Alles', 1, 0, '0', 'adasd', NULL, NULL, NULL),
('d748d40c0d7e475', 2, 0, 1, 'ad', 'adsd', 2012, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'asdadl', 'asdas@asd.de', 'Vegan', 1, 0, '0', 'ad', NULL, NULL, NULL),
('ec2cac23f915bf9', 2, 0, 1, 'gbhg', 'ncvbx', 2012, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'cvxcvxsdfs', 'ads@asdl.de', 'Alles', 1, 0, '0', 'ycyxc', NULL, NULL, 1408205076),
('78a322842b66657', 2, 0, 1, 'lkblka', 'kbvnfj', 2012, 2014, 'individuell', 'gemeinsam mit Rad', 'kmkm', 'sdkk@ksad.de', 'Vegan', 1, 0, 'MasterErsti', 'asda', NULL, NULL, NULL),
('8d70b435d61c302', 2, 0, 1, 'gm', 'sdlkjflkj', 2013, 2014, 'gemeinsam mit Rad', 'individuell', 'skldfmlk', 'sfjdkl@dfjklj.de', 'Grießbrei', 1, 0, 'Hoersti', 'asddsa', NULL, NULL, NULL),
('c8c1d8a327fd88f', 2, 0, 1, 'lkdnl', 'sdkjfhnk', 2013, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'adhsj', 'fsfahrt@byom.de', 'Frutarisch', 1, 0, 'Hoersti', 'adas', 1408205076, NULL, NULL),
('7f5609769cce5f1', 2, 0, 1, 'lkdnl', 'sdkjfhnk', 2013, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'adhsj', 'fsfahrt@byom.de', 'Frutarisch', 1, 0, 'Hoersti', 'adas', 1408105076, 1408205076, NULL),
('61fd805b3bbe4b5', 2, 0, 1, 'rcsa', 'adas', 1203, 2014, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'gdsfa', 'asdas@deda-de.de', 'Extrawurst', 1, 0, 'Wechsli', '', NULL, NULL, NULL),
('9593abed7ec0b79', 2, 0, 1, 'adolf', 'hitler', 1203, 2014, 'mit Kamel', 'mit Kamel', 'ah', 'reichskanzlei@web.dr', 'Vegetarisch', 1, 0, 'Tutti', 'Mit Kamel!', NULL, NULL, NULL),
('4eb203cf14c7a4e', 2, 1409761827, 1, 'dlklödsa', 'adlökl', 1203, 1403, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'dsaoif', 'daskdj@asdkj.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('810789efb42264d', 2, 1409763812, 1, 'klalsd', 'ladköl', 1394647412, 1394820212, 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'sdkfölk', 'alksd@aslkdj.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fahrten`
--

DROP TABLE IF EXISTS `fahrten`;
CREATE TABLE IF NOT EXISTS `fahrten` (
`fahrt_id` int(11) NOT NULL,
  `titel` varchar(200) NOT NULL,
  `ziel` varchar(100) NOT NULL,
  `von` date NOT NULL,
  `bis` date NOT NULL,
  `regopen` int(1) NOT NULL,
  `beschreibung` text NOT NULL,
  `leiter` varchar(100) NOT NULL,
  `kontakt` varchar(100) NOT NULL
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
`note_id` int(11) NOT NULL,
  `fahrt_id` int(11) NOT NULL,
  `note` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`note_id`, `fahrt_id`, `note`) VALUES
(1, 1, 'fahrt1 note'),
(2, 2, '<h1><b>Testnotiz 123<br></b></h1>hier kann man <i>notizen </i>hinterlassen test<br><br><h2><b>Dumme Bemerkung</b></h2>Notiz = no&nbsp;<span class="wysiwyg-color-red">tits!<br></span><b>aoisdkd<br>asdlji<br><br></b><b><br></b>');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bachelor`
--
ALTER TABLE `bachelor`
 ADD PRIMARY KEY (`bachelor_id`,`fahrt_id`);

--
-- Indexes for table `fahrten`
--
ALTER TABLE `fahrten`
 ADD PRIMARY KEY (`fahrt_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
 ADD PRIMARY KEY (`note_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fahrten`
--
ALTER TABLE `fahrten`
MODIFY `fahrt_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
