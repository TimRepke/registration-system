-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Oct 20, 2016 at 08:20 PM
-- Server version: 5.5.52-MariaDB-1ubuntu0.14.04.1
-- PHP Version: 5.6.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `fsfahrt`
--

-- --------------------------------------------------------

--
-- Table structure for table `bachelor`
--

CREATE TABLE `bachelor` (
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
  `repaid` int(10) DEFAULT NULL COMMENT 'rÃ¼ckzahlung abgeschickt am unix timestamp',
  `backstepped` int(10) DEFAULT NULL COMMENT 'rÃ¼cktritt als unix timestamp',
  `on_waitlist` int(11) NOT NULL DEFAULT '0',
  `transferred` int(11) DEFAULT NULL,
  `signupstats` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bachelor`
--

INSERT INTO `bachelor` (`bachelor_id`, `fahrt_id`, `anm_time`, `version`, `forname`, `sirname`, `anday`, `abday`, `antyp`, `abtyp`, `pseudo`, `mehl`, `essen`, `public`, `virgin`, `studityp`, `comment`, `paid`, `repaid`, `backstepped`, `on_waitlist`, `transferred`, `signupstats`) VALUES
  ('5b61b92044983e1', 2, 1411767105, 3, 'John', 'Doe', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'Backstepped', 'some@mail.com', 'ALLES', 1, 1, 'ERSTI', 'Test comment', NULL, NULL, 1476889935, 0, NULL, '{"method":"game1", "methodinfo":{"achievedAchievements":[1,2,3,4,5,3,3,3,3,3,3]}}'),
  ('06030f06b6e9194', 2, 1476889983, 2, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'Paid', 'test@test.de', 'ALLES', 1, 0, 'ERSTI', 'huii', 1476890174, NULL, NULL, 0, NULL, '{"method":"game1", "methodinfo":{"achievedAchievements":[1,2,3,4,5,3,3,3,3,3,3]}}'),
  ('06030f06b6e9195', 2, 1476889983, 2, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'RePaid', 'test@test.de', 'ALLES', 1, 0, 'ERSTI', 'huii', NULL, 1476890179, NULL, 0, NULL, '{"method":"game1", "methodinfo":{"achievedAchievements":["first_step", "some_water", "saw_devs1", "spotted_gorilla", "hydrant", "muell","randomwalk", "rettich_pick", "kohl", "mais", "rasenmeh", "moneyboy", "batteries", "bierball", "bild","hu", "holz", "karriereleiter", "wrong_board", "hugo_water", "laptop2", "laptop1", "marathon", "ffa","stolper", "fs_chair", "laser", "speedrun", "woman", "plumber", "princess", "stroh", "blumen", "maske","gentzen", "kacke", "antler", "flowers", "wine", "chair", "started_game", "gameDone", "achievement42"]}}'),
  ('06030f06b6e9196', 2, 1476889983, 3, 'TestA', 'Test', '2013-10-26', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'Paid+repaid', 'test@test.de', 'ALLES', 0, 0, 'ERSTI', 'huii', 1476890176, 1476890177, NULL, 0, NULL, NULL),
  ('06030f06b6e9197', 2, 1476889983, 5, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'Paid+backstepped', 'test@test.de', 'ALLES', 1, 0, 'ERSTI', 'huii', 1476890169, NULL, 1476890168, 0, NULL, NULL),
  ('06030f06b6e9198', 2, 1476889983, 1, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'tutti', 'test@test.de', 'ALLES', 0, 0, 'TUTTI', 'huii', NULL, NULL, NULL, 0, NULL, NULL),
  ('06030f06b6e9193', 2, 1476889983, 1, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'hoersti', 'test@test.de', 'ALLES', 1, 0, 'HOERS', 'huii', NULL, NULL, NULL, 0, NULL, '{"method":"game1", "methodinfo":{"achievedAchievements":["first_step", "some_water", "saw_devs1", "spotted_gorilla", "hydrant", "muell","randomwalk", "rettich_pick", "kohl", "mais", "rasenmeh", "moneyboy", "batteries", "bierball", "bild","hu", "holz", "karriereleiter", "wrong_board", "hugo_water", "laptop2", "laptop1", "marathon", "ffa","stolper", "fs_chair", "laser", "speedrun", "woman", "plumber", "princess", "stroh", "blumen", "maske", "achievement42"]}}'),
  ('06030f06b6e919', 2, 1476889983, 3, 'TestA', 'Test', '2013-10-25', '2013-10-26', 'INDIVIDUELL', 'INDIVIDUELL', 'individuell', 'test@test.de', 'VEGA', 1, 0, 'ERSTI', 'huii', NULL, NULL, NULL, 1, NULL, NULL),
  ('06030f06b6e929', 2, 1476889983, 3, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'RAD', 'BUSBAHN', 'rad', 'test@test.de', 'VEGE', 1, 0, 'ERSTI', 'huii', NULL, NULL, NULL, 0, NULL, '{"method":"game1", "methodinfo":{"achievedAchievements":["first_step", "some_water", "saw_devs1", "spotted_gorilla", "hydrant", "muell","randomwalk", "rettich_pick", "kohl", "mais", "rasenmeh", "moneyboy", "batteries", "bierball", "bild","hu", "holz", "karriereleiter", "wrong_board", "achievement42"]}}'),
  ('06030f06b6e939', 2, 1476889983, 3, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'wait', 'test@test.de', 'VEGE', 0, 0, 'ERSTI', 'huii', NULL, NULL, NULL, 1, NULL, NULL),
  ('06030f06b6e949', 2, 1476889983, 3, 'TestA', 'Test', '2013-10-25', '2013-10-27', 'INDIVIDUELL', 'BUSBAHN', 'bla', 'test@test.de', 'ALLES', 1, 0, 'ERSTI', 'huii', NULL, NULL, NULL, 0, NULL, NULL),
  ('06030f06b6e959', 2, 1476889983, 4, 'TestA', 'Test', '2013-10-26', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'wait+trans', 'test@test.de', 'ALLES', 1, 0, 'ERSTI', 'huii', NULL, NULL, NULL, 1, 1476890563, NULL),
  ('dc29d62c30756a3', 2, 1476992412, 2, 'asd', 'asd', '2013-10-26', '2013-10-27', 'BUSBAHN', 'BUSBAHN', 'asd', 'asd@asd.de', 'VEGA', 1, 1, 'ERSTI', '', NULL, NULL, NULL, 1, 1476992435, '{"method":"story","methodinfo":{"achievements":["stein","elch2","park"]}}');

-- --------------------------------------------------------

--
-- Table structure for table `cost`
--

CREATE TABLE `cost` (
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
  (2, '', '', '[{"pos":"BettwÃ¤sche","cnt":"1","mul":"40","price":"1.43"},{"pos":"Grillnutzung","cnt":"1","mul":"40","price":"0.3"},{"pos":"Halbpension","cnt":"2","mul":"40","price":"12.30"},{"pos":"Klodeckel","cnt":1,"mul":1,"price":"33"},{"pos":"blubb","cnt":1,"mul":1,"price":"03"}]', '', 60);

-- --------------------------------------------------------

--
-- Table structure for table `fahrten`
--

CREATE TABLE `fahrten` (
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
  `max_bachelor` int(4) NOT NULL,
  `wikilink` varchar(255) NOT NULL DEFAULT 'https://wiki.fachschaft.informatik.hu-berlin.de/wiki/Erstsemesterfahrt',
  `paydeadline` date NOT NULL,
  `payinfo` text NOT NULL,
  `opentime` int(11) NOT NULL DEFAULT '0',
  `disclaimlink` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `fahrten`
--

INSERT INTO `fahrten` (`fahrt_id`, `titel`, `ziel`, `von`, `bis`, `regopen`, `beschreibung`, `leiter`, `kontakt`, `map_pin`, `max_bachelor`, `wikilink`, `paydeadline`, `payinfo`, `opentime`, `disclaimlink`) VALUES
  (2, 'Fachschaftsfahrt Winter 2013', 'KiEZ Frauensee bei GrÃ¤bendorf', '2013-10-25', '2013-10-27', 1, 'Erstsemester und Fachschaftsfahrt im Wintersemester 13/14<br>Alle Informationen im <a rel="nofollow" target="_blank" href="http://wiki.fachschaft.informatik.hu-berlin.de/wiki/Fachschaftsfahrt_Winter_2013">Wiki</a>', 'Orga Name', 'organame@mail.com', '52.43893109993363 13.648079039184609', 10, 'https://wiki.fachschaft.informatik.hu-berlin.de/wiki/Erstsemesterfahrt', '2015-09-24', 'Some\r\nPayment\r\nInfo\r\nHere', 1443639637, 'https://fachschaft.informatik.hu-berlin.de/index.php?title=FachschaftsfahrtDisclaimer&oldid=428');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `note_id` int(11) NOT NULL,
  `fahrt_id` int(11) NOT NULL,
  `note` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`note_id`, `fahrt_id`, `note`) VALUES
  (2, 2, '<h1><b>Testnotiz 123<br></b></h1>hier kann man <i>notizen </i>hinterlassen test<br><br><h2><b>Dumme Bemerkung</b></h2>Notiz = no&nbsp;<span class="wysiwyg-color-red">tits!<br></span><b><br><br>aoisdkd<br>asdlji<br><br></b><b><br></b>');

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
MODIFY `fahrt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;