-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Oct 12, 2014 at 02:03 AM
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
(5, '{"ALL":{"B_FIX":"4","C_BW":"4.3","E_ESS":"5","REFRA":"42"},"VAR":{"F_FR":[{"val":"","ind":false,"an":false,"ab":false},{"val":"3","ind":true,"an":false,"ab":true},{"val":"3","ind":true,"an":true,"ab":true}],"G_MI":[{"val":"","ind":false,"an":false,"ab":false},{"val":"","ind":false,"an":false,"ab":false},{"val":"","ind":false,"an":false,"ab":false}],"H_AB":[{"val":"4","ind":true,"an":true,"ab":false},{"val":"4.5","ind":true,"an":true,"ab":false},{"val":"","ind":false,"an":false,"ab":false}],"D_UE":[{"val":"12.5","ind":true,"an":true,"ab":false},{"val":"12.5","ind":true,"an":true,"ab":false},{"val":"","ind":false,"an":false,"ab":false}],"A_FAHRT":[{"val":"2.5","ind":false,"an":true,"ab":false},{"val":"","ind":false,"an":false,"ab":false},{"val":"2.5","ind":false,"an":false,"ab":true}]}}', '[{"pos":"Limonade","cnt":"76","price":"1.43"},{"pos":"Brause","cnt":"42","price":"0.83"},{"pos":"Lutscher","cnt":"1","price":"0.5"}]', '[{"pos":"Bettwäsche","cnt":"1","mul":"40","price":"1.43"},{"pos":"Grillnutzung","cnt":"1","mul":"40","price":"0.3"},{"pos":"Halbpension","cnt":"2","mul":"40","price":"12.30"},{"pos":"Klodeckel","cnt":1,"mul":1,"price":"35"}]', '{"in":[{"pos":"Förderung","val":"1200"},{"pos":"Pfand","val":"31"},{"pos":"Kaution (Rückzahlung)","val":"100"}],"out":[{"pos":"Einkaufen","val":"354"},{"pos":"Busfahrt Hin","val":"35"},{"pos":"Busfahrt Rück","val":"40"},{"pos":"Kaution","val":"100"}]}', 60);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cost`
--
ALTER TABLE `cost`
 ADD PRIMARY KEY (`fahrt_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
