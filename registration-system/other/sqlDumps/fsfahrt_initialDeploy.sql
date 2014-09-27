-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Sep 27, 2014 at 06:29 PM
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
  `backstepped` int(10) DEFAULT NULL COMMENT 'rücktritt als unix timestamp'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bachelor`
--

INSERT INTO `bachelor` (`bachelor_id`, `fahrt_id`, `anm_time`, `version`, `forname`, `sirname`, `anday`, `abday`, `antyp`, `abtyp`, `pseudo`, `mehl`, `essen`, `public`, `virgin`, `studityp`, `comment`, `paid`, `repaid`, `backstepped`) VALUES
('5b61b92044983e1', 2, 0, 1, 'asd', 'ad', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'ffas', 'asdasd@asd.de', 'Alles', 1, 0, '0', 'dasd', NULL, NULL, 1411059051),
('f35f12ca7c55462', 2, 0, 1, 'fcacs', 'ads', '2013-10-18', '2013-10-19', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'fas', 'asd@asd.de', 'Alles', 0, 0, '0', 'adasdasda', NULL, NULL, NULL),
('068e4198f255a1e', 2, 0, 1, 'göll', 'asd', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'adsad', 'adskd@asdl.de', 'Alles', 1, 0, '0', 'adasd', 1409779206, NULL, NULL),
('d748d40c0d7e475', 2, 0, 1, 'ad', 'adsd', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'asdadl', 'asdas@asd.de', 'Vegan', 1, 0, '0', 'ad', NULL, NULL, NULL),
('ec2cac23f915bf9', 2, 0, 1, 'gbhg', 'ncvbx', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'cvxcvxsdfs', 'ads@asdl.de', 'Alles', 1, 0, '0', 'ycyxc', NULL, NULL, 1408205076),
('78a322842b66657', 2, 0, 1, 'lkblka', 'kbvnfj', '2013-10-18', '2013-10-20', 'individuell', 'gemeinsam mit Rad', 'kmkm', 'sdkk@ksad.de', 'Vegan', 1, 0, 'MasterErsti', 'asda', NULL, NULL, NULL),
('8d70b435d61c302', 2, 0, 1, 'gm', 'sdlkjflkj', '2013-10-18', '2013-10-20', 'gemeinsam mit Rad', 'individuell', 'skldfmlk', 'sfjdkl@dfjklj.de', 'Grießbrei', 1, 0, 'Hoersti', 'asddsa', NULL, NULL, NULL),
('c8c1d8a327fd88f', 2, 0, 1, 'lkdnl', 'sdkjfhnk', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'adhsj', 'fsfahrt@byom.de', 'Frutarisch', 1, 0, 'Hoersti', 'adas', 1408205076, NULL, NULL),
('7f5609769cce5f1', 2, 0, 1, 'lkdnl', 'sdkjfhnk', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'adhsj', 'fsfahrt@byom.de', 'Frutarisch', 1, 0, 'Hoersti', 'adas', 1408105076, 1408205076, NULL),
('61fd805b3bbe4b5', 2, 0, 1, 'rcsa', 'adas', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'gdsfa', 'asdas@deda-de.de', 'Extrawurst', 1, 0, 'Wechsli', '', NULL, NULL, 1411251707),
('9593abed7ec0b79', 2, 0, 1, 'bla', 'blubb', '2013-10-18', '2013-10-20', 'mit Kamel', 'mit Kamel', 'ah', 'reichskanzlei@web.dr', 'Vegetarisch', 1, 0, 'Tutor', 'Mit Kamel!', NULL, NULL, NULL),
('4eb203cf14c7a4e', 2, 1409761827, 1, 'dlklödsa', 'adlökl', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'dsaoif', 'daskdj@asdkj.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('810789efb42264d', 2, 1409763812, 1, 'klalsd', 'ladköl', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'sdkfölk', 'alksd@aslkdj.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('09ca2d98ea68524', 2, 1411224273, 1, 'ölmm', 'ölkö', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'lkkldslk', 'aksdllk@asdjk.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, 1411251706),
('9de69c5684a4c28', 2, 1411249881, 1, 'random', 'so random', '2013-10-18', '2013-10-20', 'individuell', 'individuell', 'superrandom', 'ran@om.de', 'Alles', 1, 0, 'Ersti', 'randomtest', NULL, NULL, NULL),
('0baff8036ee698b', 2, 1411432206, 1, 'as', 'das', '0000-00-00', '0000-00-00', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'ffas', 'asdsa@asd.de', 'Vegan', 1, 0, 'Hoersti', '', NULL, NULL, NULL),
('54cdf371a2f56b9', 2, 1411432331, 1, 'klau', 'asdk', '0000-00-00', '0000-00-00', 'Kamel/Individuell', 'gemeinsam mit Bus/Bahn', 'mutter', 'back@web.de', 'Vegan', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('a62d6883688789f', 2, 1411682781, 1, 'asd', 'asdd', '0000-00-00', '0000-00-00', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'asdas', 'asdas@sad.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('61ed33f03d0de0b', 2, 1411686132, 1, 'adsd', 'adss', '2013-10-19', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'date', 'adslk@asldk.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('bb4dc6d782c98c0', 2, 1411767105, 1, 'letzter', 'adkslj', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Rad', 'letzter', 'adslkj@askd.de', 'Vegetarisch', 1, 0, 'Tutor', 'asdads', NULL, NULL, NULL),
('bfdefb03deb3bcb', 2, 1411767151, 1, 'nkjkl', 'kjlk', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'kljlk', 'alkdsj@ads.de', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL),
('6ec42e12e2368c0', 2, 1411767427, 1, 'qwe', 'qwe', '2013-10-18', '2013-10-20', 'gemeinsam mit Bus/Bahn', 'gemeinsam mit Bus/Bahn', 'asd', 'qwe@qw.qw', 'Alles', 1, 0, 'Ersti', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cost`
--

DROP TABLE IF EXISTS `cost`;
CREATE TABLE IF NOT EXISTS `cost` (
  `fahrt_id` int(11) NOT NULL COMMENT 'trip associated to calculation',
  `tab1` text NOT NULL COMMENT 'JSON dump of tab1',
  `tab2` text NOT NULL COMMENT 'JSON dump of tab2',
  `tab3` text NOT NULL COMMENT 'JSON dump of tab3',
  `moneyIO` text NOT NULL COMMENT 'JSON dump of money IO',
  `collected` int(11) NOT NULL COMMENT 'amount collected per person before trip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `kontakt` varchar(100) NOT NULL,
  `map_pin` text NOT NULL,
  `max_bachelor` int(4) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `fahrten`
--

INSERT INTO `fahrten` (`fahrt_id`, `titel`, `ziel`, `von`, `bis`, `regopen`, `beschreibung`, `leiter`, `kontakt`, `map_pin`, `max_bachelor`) VALUES
(1, 'Fachschaftsfahrt Winter 2012', 'KiEZ Inselparadies am Schwielowsee bei Werder', '2012-10-26', '2012-10-28', 0, 'Alle Informationen zur Fahrt im <a rel="nofollow" target="_blank" href="http://wiki.fachschaft.informatik.hu-berlin.de/wiki/Fachschaftsfahrt_Winter_2012">Wiki</a>', 'Tim Repke', 'nein@nein.de', ' ', 2),
(2, 'Fachschaftsfahrt Winter 2013', 'KiEZ Frauensee bei Gräbendorf', '2013-10-25', '2013-10-27', 1, '<pre>Erstsemester und Fachschaftsfahrt im Wintersemester 13/14\r\nAlle Informationen im <a rel="nofollow" target="_blank" href="http://wiki.fachschaft.informatik.hu-berlin.de/wiki/Fachschaftsfahrt_Winter_2013">Wiki</a>\r\n\r\nAdresse:\r\nKiEZ Frauensee\r\nWeg zum Frauensee 1\r\n15754 Heidesee\r\nWWW: <a rel="nofollow" target="_blank" href="http://www.frauensee.de/">http://www.frauensee.de</a></pre>', 'Tim Repke', 'nein@nein.de', '52.50514830899912 13.4540158525391', 19),
(5, 'Fachschaftsfahrt Winter 2014', ' Jugendherberge Münchehofe', '2014-10-24', '2014-10-26', 1, 'Dies ist die Anmeldung zur Erstsemester-/Fachschaftsfahrt im Wintersemester 2014/15. <br>Weitere Infos sind im <a rel="nofollow" target="_blank" href="http://wiki.fachschaft.informatik.hu-berlin.de/wiki/Fachschaftsfahrt_Winter_2014">Wiki</a>', 'Georg Gentzen', 'gentzeng@informatik.hu-berlin.de', '52.55853564453215 14.14091885241703', 60);

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
(2, 2, '<h1><b>Testnotiz 123<br></b></h1>hier kann man <i>notizen </i>hinterlassen test<br><br><h2><b>Dumme Bemerkung</b></h2>Notiz = no&nbsp;<span class="wysiwyg-color-red">tits!<br></span><b><br><br>aoisdkd<br>asdlji<br><br></b><b><br></b>');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

DROP TABLE IF EXISTS `waitlist`;
CREATE TABLE IF NOT EXISTS `waitlist` (
`waitlist_id` int(11) NOT NULL,
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
  `comment` text NOT NULL,
  `transferred` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
 ADD PRIMARY KEY (`waitlist_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fahrten`
--
ALTER TABLE `fahrten`
MODIFY `fahrt_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
MODIFY `waitlist_id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
