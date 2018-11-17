-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 28, 2018 at 05:19 PM
-- Server version: 5.7.22-0ubuntu0.16.04.1
-- PHP Version: 7.0.30-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s253054`
--

-- --------------------------------------------------------

--
-- Table structure for table `busstop`
--

CREATE TABLE `busstop` (
  `BusStopId` varchar(30) NOT NULL,
  `Arrived` int(11) NOT NULL DEFAULT '0',
  `Starts` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `busstop`
--

INSERT INTO `busstop` (`BusStopId`, `Arrived`, `Starts`) VALUES
('AL', 0, 1),
('BB', 1, 2),
('DD', 2, 2),
('EE', 2, 0),
('FF', 0, 4),
('KK', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `Email` varchar(50) NOT NULL,
  `Password` varchar(1024) NOT NULL,
  `Src` varchar(30) DEFAULT NULL,
  `Dest` varchar(30) DEFAULT NULL,
  `NSeats` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`Email`, `Password`, `Src`, `Dest`, `NSeats`) VALUES
('u1@p.it', 'ec6ef230f1828039ee794566b9c58adc', 'FF', 'KK', 4),
('u2@p.it', '1d665b9b1467944c128a5575119d1cfd', 'BB', 'EE', 1),
('u3@p.it', '7bc3ca68769437ce986455407dab2a1f', 'DD', 'EE', 1),
('u4@p.it', '13207e3d5722030f6c97d69b4904d39d', 'AL', 'DD', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `busstop`
--
ALTER TABLE `busstop`
  ADD PRIMARY KEY (`BusStopId`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`Email`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
