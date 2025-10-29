<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that
#  include student demographic info, scheduling, grade book, attendance,
#  report cards, eligibility, transcripts, parent portal,
#  student portal and more.
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as
#  published by the Free Software Foundation, version 2 of the License.
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************
require_once("../functions/PragRepFnc.php");
$text = "
-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 08:41 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `opensis`
--

--
-- Dumping data for table `app`
--

INSERT INTO `app` (`name`, `value`) VALUES
('version', '9.2'),
('date', 'October 27, 2025'),
('build', '20250827001'),
('update', '0'),
('last_updated', 'October 27, 2025');


--
-- Dumping data for table `attendance_calendar`
--

INSERT INTO `attendance_calendar` (`syear`, `school_id`, `school_date`, `minutes`, `block`, `calendar_id`, `last_updated`, `updated_by`) VALUES
(2025, 1, '2025-08-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-07', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-14', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-21', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-28', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-08-29', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-01', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-29', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-09-30', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-01', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-07', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-14', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-21', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-28', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-29', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-30', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-10-31', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-07', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-14', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-21', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-11-28', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-01', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-29', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-30', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2025-12-31', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-01', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-07', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-14', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-21', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-28', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-29', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-01-30', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-02-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-30', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-03-31', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-01', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-02', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-03', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-07', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-09', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-10', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-14', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-16', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-17', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-21', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-23', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-24', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-28', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-29', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-04-30', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-01', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-04', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-05', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-06', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-07', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-08', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-11', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-12', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-13', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-14', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-15', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-18', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-19', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-20', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-21', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-22', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-25', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-26', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-27', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-28', 999, NULL, 1, '2025-10-25 08:07:56', '1'),
(2025, 1, '2026-05-29', 999, NULL, 1, '2025-10-25 08:07:56', '1');

--
-- Dumping data for table `attendance_codes`
--

INSERT INTO `attendance_codes` (`id`, `syear`, `school_id`, `title`, `short_name`, `type`, `state_code`, `default_code`, `table_name`, `sort_order`, `last_updated`, `updated_by`) VALUES
(1, 2025, 1, 'Present', 'P', 'teacher', 'P', 'Y', 0, 1, '2025-12-22 00:41:21', NULL),
(2, 2025, 1, 'Absent', 'A', 'teacher', 'A', NULL, 0, 2, '2025-12-22 00:41:21', NULL),
(3, 2025, 1, 'Tardy', 'T', 'teacher', 'H', NULL, 0, 3, '2025-12-22 00:41:21', NULL),
(4, 2025, 1, 'Late', 'L', 'teacher', 'P', NULL, 0, 4, '2025-12-22 00:41:21', NULL);

--
-- Dumping data for table `attendance_completed`
--

INSERT INTO `attendance_completed` (`staff_id`, `school_date`, `period_id`, `course_period_id`, `cpv_id`, `substitute_staff_id`, `is_taken_by_substitute_staff`, `last_updated`, `updated_by`) VALUES
(5, '2025-10-22', 1, 1, 1, NULL, NULL, '2025-10-27 06:56:32', '5'),
(5, '2025-10-13', 1, 1, 1, NULL, NULL, '2025-10-27 06:56:43', '5'),
(5, '2025-10-27', 1, 1, 1, NULL, NULL, '2025-10-27 08:10:09', '5'),
(5, '2025-08-11', 1, 1, 1, NULL, NULL, '2025-10-27 08:10:28', '5'),
(5, '2025-08-20', 1, 1, 1, NULL, NULL, '2025-10-27 08:10:35', '5'),
(5, '2025-08-18', 1, 1, 1, NULL, NULL, '2025-10-27 08:10:41', '5'),
(5, '2025-09-01', 1, 1, 1, NULL, NULL, '2025-10-27 08:10:48', '5');


--
-- Dumping data for table `attendance_day`
--

INSERT INTO `attendance_day` (`student_id`, `school_date`, `minutes_present`, `state_value`, `syear`, `marking_period_id`, `comment`, `last_updated`, `updated_by`) VALUES
(1, '2025-08-11', 15, 1.0, 2025, 12, NULL, '2025-10-27 08:10:28', '5'),
(1, '2025-08-18', 8, 1.0, 2025, 12, NULL, '2025-10-27 08:10:41', '5'),
(1, '2025-08-20', 0, 0.0, 2025, 12, NULL, '2025-10-27 08:10:35', '5'),
(1, '2025-09-01', 15, 1.0, 2025, 12, NULL, '2025-10-27 08:10:48', '5'),
(1, '2025-10-13', 0, 0.0, 2025, 12, NULL, '2025-10-27 06:56:43', '5'),
(1, '2025-10-22', 15, 1.0, 2025, 12, NULL, '2025-10-27 06:56:32', '5'),
(1, '2025-10-27', 15, 1.0, 2025, 12, NULL, '2025-10-27 08:10:09', '5');

--
-- Dumping data for table `attendance_period`
--

INSERT INTO `attendance_period` (`student_id`, `school_date`, `period_id`, `attendance_code`, `attendance_teacher_code`, `attendance_reason`, `admin`, `course_period_id`, `marking_period_id`, `comment`, `last_updated`, `updated_by`) VALUES
(1, '2025-08-11', 1, 1, 1, NULL, NULL, 1, 12, NULL, '2025-10-27 08:10:28', '5'),
(1, '2025-08-18', 1, 3, 3, NULL, NULL, 1, 12, NULL, '2025-10-27 08:10:41', '5'),
(1, '2025-08-20', 1, 2, 2, NULL, NULL, 1, 12, NULL, '2025-10-27 08:10:35', '5'),
(1, '2025-09-01', 1, 1, 1, NULL, NULL, 1, 12, NULL, '2025-10-27 08:10:48', '5'),
(1, '2025-10-13', 1, 2, 2, NULL, NULL, 1, 12, NULL, '2025-10-27 06:56:43', '5'),
(1, '2025-10-22', 1, 1, 1, NULL, NULL, 1, 12, NULL, '2025-10-27 06:56:32', '5'),
(1, '2025-10-27', 1, 4, 4, NULL, NULL, 1, 12, NULL, '2025-10-27 08:10:09', '5');
--
-- Dumping data for table `calendar_events_visibility`
--

INSERT INTO `calendar_events_visibility` (`calendar_id`, `profile_id`, `profile`, `last_updated`, `updated_by`) VALUES
(1, NULL, 'admin', '2025-10-25 08:08:09', '1'),
(1, NULL, 'teacher', '2025-10-25 08:08:09', '1'),
(1, NULL, 'parent', '2025-10-25 08:08:09', '1'),
(1, 0, NULL, '2025-10-25 08:08:09', '1'),
(1, 1, NULL, '2025-10-25 08:08:09', '1'),
(1, 2, NULL, '2025-10-25 08:08:09', '1'),
(1, 3, NULL, '2025-10-25 08:08:09', '1'),
(1, 4, NULL, '2025-10-25 08:08:09', '1'),
(1, 5, NULL, '2025-10-25 08:08:09', '1');


--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`syear`, `course_id`, `subject_id`, `school_id`, `grade_level`, `title`, `short_name`, `rollover_id`, `last_updated`, `updated_by`) VALUES
(2025, 1, 1, 1, NULL, 'Attendance All', 'ATTN001', NULL, '2025-10-24 18:00:00', NULL),
(2025, 2, 2, 1, NULL, 'Reading', 'READ001', NULL, '2025-10-24 18:00:00', NULL),
(2025, 3, 2, 1, NULL, 'Writing', 'WRT002', NULL, '2025-10-24 18:00:00', NULL),
(2025, 4, 3, 1, NULL, 'Algebra ll', 'ALG02', NULL, '2025-10-24 18:00:00', NULL),
(2025, 5, 3, 1, NULL, 'Geometry', 'GEOM1', NULL, '2025-10-24 18:00:00', NULL),
(2025, 6, 4, 1, NULL, 'Biology', 'BIO 101', NULL, '2025-10-24 18:00:00', NULL),
(2025, 7, 4, 1, NULL, 'Chemistry', 'CHEM 101', NULL, '2025-10-24 18:00:00', NULL),
(2025, 8, 4, 1, NULL, 'Physics', 'PHY 101', NULL, '2025-10-24 18:00:00', NULL),
(2025, 9, 5, 1, NULL, 'Geography', 'GEOG001', NULL, '2025-10-24 18:00:00', NULL),
(2025, 10, 5, 1, NULL, 'History', 'HIST001', NULL, '2025-10-24 18:00:00', NULL);


--
-- Dumping data for table `course_periods`
--

INSERT INTO `course_periods` (`syear`, `school_id`, `course_period_id`, `course_id`, `course_weight`, `title`, `short_name`, `mp`, `marking_period_id`, `begin_date`, `end_date`, `teacher_id`, `secondary_teacher_id`, `total_seats`, `filled_seats`, `does_honor_roll`, `does_class_rank`, `gender_restriction`, `house_restriction`, `availability`, `parent_id`, `calendar_id`, `half_day`, `does_breakoff`, `rollover_id`, `grade_scale_id`, `credits`, `schedule_type`, `last_updated`, `modified_by`, `updated_by`) VALUES
(2025, 1, 1, 1, NULL, 'Attendance - 001 - Rudyard  Kipling', 'Attendance - 001', 'FY', 1, '2025-08-04', '2026-05-29', 5, NULL, 15, 3, NULL, NULL, 'N', NULL, NULL, 1, 1, NULL, NULL, NULL, 1, 3.000, 'FIXED', '2025-10-27 08:53:42', 1, '1'),
(2025, 1, 2, 2, NULL, 'SEM1 - English-001 - Rudyard  Kipling', 'English-001', 'SEM', 12, '2025-08-04', '2025-12-19', 5, NULL, 15, 1, NULL, NULL, 'N', NULL, NULL, 2, 1, NULL, NULL, NULL, 1, NULL, 'FIXED', '2025-10-27 06:57:15', 1, '1'),
(2025, 1, 3, 3, NULL, 'Essay - Charles  Dickens', 'Essay', 'FY', 1, '2025-08-04', '2026-05-29', 6, NULL, 15, 0, NULL, NULL, 'N', NULL, NULL, 3, 1, NULL, NULL, NULL, 1, NULL, 'FIXED', '2025-10-27 06:44:31', 1, '1'),
(2025, 1, 4, 4, NULL, 'Linear Equations - Charles  Dickens', 'Linear Equations', 'FY', 1, '2025-08-04', '2026-05-29', 6, NULL, 15, 2, NULL, NULL, 'N', NULL, NULL, 4, 1, NULL, NULL, NULL, 1, NULL, 'VARIABLE', '2025-10-27 08:52:54', 1, '1');

--
-- Dumping data for table `course_period_var`
--

INSERT INTO `course_period_var` (`id`, `course_period_id`, `days`, `course_period_date`, `period_id`, `start_time`, `end_time`, `room_id`, `does_attendance`, `last_updated`, `updated_by`) VALUES
(1, 1, 'MW', NULL, 1, '08:00:00', '08:15:00', 7, 'Y', '2025-10-25 14:42:12', '1'),
(2, 2, 'MH', NULL, 3, '09:30:00', '10:20:00', 1, NULL, '2025-10-27 06:43:30', '1'),
(3, 3, 'TW', NULL, 4, '10:30:00', '11:20:00', 2, NULL, '2025-10-27 06:44:31', '1'),
(4, 4, 'W', NULL, 6, '12:00:00', '12:50:00', 5, NULL, '2025-10-27 06:46:00', '1');

--
-- Dumping data for table `course_subjects`
--

INSERT INTO `course_subjects` (`syear`, `school_id`, `subject_id`, `title`, `short_name`, `rollover_id`, `last_updated`, `updated_by`) VALUES
(2025, 1, 1, 'Attendance Tracking', NULL, NULL, '2025-10-24 18:00:00', NULL),
(2025, 1, 2, 'Language Arts', NULL, NULL, '2025-10-24 18:00:00', NULL),
(2025, 1, 3, 'Mathematics', NULL, NULL, '2025-10-24 18:00:00', NULL),
(2025, 1, 4, 'Science', NULL, NULL, '2025-10-24 18:00:00', NULL),
(2025, 1, 5, 'Social Studies', NULL, NULL, '2025-10-24 18:00:00', NULL);

--
-- Dumping data for table `ethnicity`
--

INSERT INTO `ethnicity` (`ethnicity_id`, `ethnicity_name`, `sort_order`, `last_updated`, `updated_by`) VALUES
(1, 'White, Non-Hispanic', 1, '0000-00-00 00:00:00', NULL),
(2, 'Black, Non-Hispanic', 2, '0000-00-00 00:00:00', NULL),
(3, 'Hispanic', 3, '0000-00-00 00:00:00', NULL),
(4, 'American Indian or Native Alaskan', 4, '0000-00-00 00:00:00', NULL),
(5, 'Pacific Islander', 5, '0000-00-00 00:00:00', NULL),
(6, 'Asian', 6, '0000-00-00 00:00:00', NULL),
(7, 'Indian', 7, '0000-00-00 00:00:00', NULL),
(8, 'Middle Eastern', 8, '0000-00-00 00:00:00', NULL),
(9, 'African', 9, '0000-00-00 00:00:00', NULL),
(10, 'Mixed Race', 10, '0000-00-00 00:00:00', NULL),
(11, 'Other', 11, '0000-00-00 00:00:00', NULL);

--
-- Dumping data for table `gradebook_assignment_types`
--

INSERT INTO `gradebook_assignment_types` (`assignment_type_id`, `staff_id`, `course_id`, `title`, `final_grade_percent`, `course_period_id`, `last_updated`, `updated_by`) VALUES
(1, 5, 1, 'Homework', NULL, 1, '2025-10-27 07:20:48', '5'),
(2, 5, 1, 'Classwork', NULL, 1, '2025-10-27 08:07:49', '5');

--
-- Dumping data for table `gradebook_grades`
--

--
-- Dumping data for table `grades_completed`
--

INSERT INTO `grades_completed` (`staff_id`, `marking_period_id`, `period_id`, `last_updated`, `updated_by`) VALUES
(5, 12, 1, '2025-10-27 08:31:42', '1'),
(5, 12, 3, '2025-10-27 08:53:57', '1'),
(6, 12, 6, '2025-10-27 08:53:24', '1');

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`language_id`, `language_name`, `sort_order`, `last_updated`, `updated_by`) VALUES
(1, 'English', 1, '2025-10-25 00:00:00', NULL),
(2, 'Arabic', 2, '2025-10-25 00:00:00', NULL),
(3, 'Bengali', 3, '2025-10-25 00:00:00', NULL),
(4, 'Chinese', 4, '2025-10-25 00:00:00', NULL),
(5, 'French', 5, '2025-10-25 00:00:00', NULL),
(6, 'German', 6, '2025-10-25 00:00:00', NULL),
(7, 'Haitian Creole', 7, '2025-10-25 00:00:00', NULL),
(8, 'Hindi', 8, '2025-10-25 00:00:00', NULL),
(9, 'Italian', 9, '2025-10-25 00:00:00', NULL),
(10, 'Japanese', 10, '2025-10-25 00:00:00', NULL),
(11, 'Korean', 11, '2025-10-25 00:00:00', NULL),
(12, 'Malay', 12, '2025-10-25 00:00:00', NULL),
(13, 'Polish', 13, '2025-10-25 00:00:00', NULL),
(14, 'Portuguese', 14, '2025-10-25 00:00:00', NULL),
(15, 'Russian', 15, '2025-10-25 00:00:00', NULL),
(16, 'Spanish', 16, '2025-10-25 00:00:00', NULL),
(17, 'Thai', 17, '2025-10-25 00:00:00', NULL),
(18, 'Turkish', 18, '2025-10-25 00:00:00', NULL),
(19, 'Urdu', 19, '2025-10-25 00:00:00', NULL),
(20, 'Vietnamese', 20, '2025-10-25 00:00:00', NULL);

--
-- Dumping data for table `login_message`
--

INSERT INTO `login_message` (`id`, `message`, `display`) VALUES
(1, 'This is a restricted network. Use of this network, its equipment, and resources is monitored at all times and requires explicit permission from the network administrator. If you do not have this permission in writing, you are violating the regulations of this network and can and will be prosecuted to the fullest extent of law. By continuing into this system, you are acknowledging that you are aware of and agree to these terms.', 'Y');

--
-- Dumping data for table `log_maintain`
--


--
-- Dumping data for table `mail_group`
--


--
-- Dumping data for table `mail_groupmembers`
--


--
-- Dumping data for table `marking_period_id_generator`
--

INSERT INTO `marking_period_id_generator` (`id`) VALUES
(1),
(12),
(13),
(14),
(15),
(16),
(17),
(18),
(19),
(20),
(21),
(22),
(23),
(24);

--
-- Dumping data for table `medical_info`
--

INSERT INTO `medical_info` (`id`, `student_id`, `syear`, `school_id`, `physician`, `physician_phone`, `preferred_hospital`, `last_updated`, `updated_by`) VALUES
(1, 1, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(2, 2, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(3, 3, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(4, 4, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(5, 5, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(6, 6, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(7, 7, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(8, 8, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(9, 9, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(10, 10, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(11, 11, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(12, 12, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(13, 13, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(14, 14, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL),
(15, 15, 2025, 1, NULL, NULL, NULL, '2025-12-22 00:41:21', NULL);

--
-- Dumping data for table `missing_attendance`
--

INSERT INTO `missing_attendance` (`school_id`, `syear`, `school_date`, `course_period_id`, `period_id`, `teacher_id`, `secondary_teacher_id`, `last_updated`, `updated_by`) VALUES
(1, '2025', '2025-08-04', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-08-06', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-08-13', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-08-25', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-08-27', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-03', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-08', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-10', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-15', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-17', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-22', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-24', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-09-29', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-10-01', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-10-06', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-10-08', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-10-15', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1'),
(1, '2025', '2025-10-20', 1, 1, 5, NULL, '2025-10-27 08:53:42', '1');

--
-- Dumping data for table `people`
--

--
-- Dumping data for table `people_field_categories`
--

INSERT INTO `people_field_categories` (`id`, `title`, `sort_order`, `include`, `admin`, `teacher`, `parent`, `none`, `last_updated`, `updated_by`) VALUES
(1, 'General Info', 1, NULL, 'Y', 'Y', 'Y', 'Y', '2025-10-27 00:00:00', NULL),
(2, 'Address Info', 2, NULL, 'Y', 'Y', 'Y', 'Y', '2025-10-27 00:00:00', NULL);

--
-- Dumping data for table `portal_notes`
--

INSERT INTO `portal_notes` (`id`, `school_id`, `syear`, `title`, `content`, `sort_order`, `published_user`, `last_updated`, `start_date`, `end_date`, `published_profiles`, `updated_by`) VALUES
(1, NULL, 2025, 'Welcome', 'Welcome to the Greenwood International School', 1, 1, '2025-10-27 00:00:00', '2025-08-04', '2026-05-29', ',all,admin,teacher,parent,0,1,2,3,4,5,6,', NULL),
(2, 1, 2025, 'Chirstmas Week', 'Merry Christmas to all of you. Enjoy !!!', NULL, 1, '2025-10-27 00:00:00', '2025-12-25', '2025-12-31', ',0,1,2,3,4,', NULL);

--
-- Dumping data for table `profile_exceptions`
--

INSERT INTO `profile_exceptions` (`profile_id`, `modname`, `can_use`, `can_edit`, `last_updated`, `updated_by`) VALUES
(2, 'students/Student.php&category_id=6', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/Student.php&category_id=7', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'students/Student.php&category_id=6', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'students/Student.php&category_id=6', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'users/User.php&category_id=5', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'schoolsetup/Schools.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'schoolsetup/Calendar.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'students/Student.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'students/Student.php&category_id=1', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'students/Student.php&category_id=3', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'students/ChangePassword.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'scheduling/ViewSchedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'scheduling/PrintSchedules.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'scheduling/Requests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(3, 'grades/StudentGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'grades/FinalGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'grades/ReportCards.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'grades/Transcripts.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'grades/GPARankList.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'attendance/StudentSummary.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'attendance/DailySummary.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'eligibility/Student.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'eligibility/StudentList.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/Schools.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/MarkingPeriods.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/Calendar.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/Student.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/AddUsers.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/AdvancedReport.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/StudentLabels.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/Student.php&category_id=1', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/Student.php&category_id=3', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/Student.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/User.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/Rooms.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'grades/Grades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'users/Preferences.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'scheduling/Schedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'scheduling/PrintSchedules.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'scheduling/PrintClassLists.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'scheduling/PrintClassPictures.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/InputFinalGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/ReportCards.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/Grades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/Assignments.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/AnomalousGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/Configuration.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/ProgressReports.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/StudentGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/FinalGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/ReportCardGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'grades/ReportCardComments.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'attendance/TakeAttendance.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'attendance/DailySummary.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'attendance/StudentSummary.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'eligibility/EnterEligibility.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'scheduling/ViewSchedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'attendance/StudentSummary.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'attendance/DailySummary.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'eligibility/Student.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'eligibility/StudentList.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'schoolsetup/Schools.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'schoolsetup/Calendar.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'students/Student.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'students/Student.php&category_id=1', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'students/Student.php&category_id=3', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'users/User.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'users/User.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'users/Preferences.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'scheduling/ViewSchedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'scheduling/Requests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'grades/StudentGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'grades/FinalGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'grades/ReportCards.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'grades/Transcripts.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'grades/GPARankList.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'users/User.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'users/User.php&category_id=3', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/Courses.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/CourseCatalog.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/PrintCatalog.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'schoolsetup/PrintAllCourses.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'students/Student.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'students/ChangePassword.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'scheduling/StudentScheduleReport.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'grades/ParentProgressReports.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'scheduling/StudentScheduleReport.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/PortalNotes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/MarkingPeriods.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/Calendar.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/Periods.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/GradeLevels.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/Schools.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/UploadLogo.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/Schools.php?new_school=true', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/CopySchool.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/SystemPreference.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/Courses.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/CourseCatalog.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/PrintCatalog.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/PrintCatalogGradeLevel.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/PrintAllCourses.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/TeacherReassignment.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php&include=GeneralInfoInc&student_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/AssignOtherInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/AddUsers.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/AdvancedReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/AddDrop.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Letters.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/MailingLabels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/StudentLabels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/PrintStudentInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/PrintStudentContactInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/GoalReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/StudentFields.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'students/EnrollmentCodes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Upload.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Upload.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php&category_id=3', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/Student.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/User.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/User.php&staff_id=new', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/AddStudents.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/Preferences.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/Profiles.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/Exceptions.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/UserFields.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=grades/InputFinalGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=grades/Grades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=grades/ProgressReports.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=attendance/Missing_Attendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'users/User.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/User.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'scheduling/Schedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/ViewSchedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/Requests.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/MassSchedule.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/MassRequests.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/MassDrops.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/PrintSchedules.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'scheduling/PrintClassLists.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'scheduling/PrintClassPictures.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/PrintRequests.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/ScheduleReport.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/RequestsReport.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/UnfilledRequests.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/IncompleteSchedules.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/AddDrop.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'scheduling/Scheduler.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/ReportCards.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'grades/CalcGPA.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'grades/Transcripts.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'grades/TeacherCompletion.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/GradeBreakdown.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/FinalGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/GPARankList.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/AdminProgressReports.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/HonorRoll.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/ReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'grades/ReportCardComments.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'grades/HonorRollSetup.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'grades/FixGPA.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/EditReportCardGrades.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'grades/EditHistoryMarkingPeriods.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'attendance/Administration.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/AddAbsences.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/AttendanceData.php?list_by_day=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/Percent.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/Percent.php?list_by_day=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/DailySummary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/StudentSummary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/FixDailyAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/DuplicateAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'attendance/AttendanceCodes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'eligibility/Student.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'eligibility/AddActivity.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'eligibility/StudentList.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'eligibility/TeacherCompletion.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'eligibility/Activities.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'eligibility/EntryTimes.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(5, 'tools/LogDetails.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'tools/DeleteLog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'tools/Rollover.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/SchoolCustomFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=6', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=7', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/User.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/PortalNotes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Schools.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Schools.php?new_school=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/CopySchool.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/MarkingPeriods.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Calendar.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Periods.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/GradeLevels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Rollover.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Courses.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/CourseCatalog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/PrintCatalog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/PrintCatalogGradeLevel.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/PrintAllCourses.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/UploadLogo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/TeacherReassignment.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&include=GeneralInfoInc&student_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/AssignOtherInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/AddUsers.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/AdvancedReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/AddDrop.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Letters.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/MailingLabels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/StudentLabels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/PrintStudentInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/PrintStudentContactInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/GoalReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/StudentFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/AddressFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/PeopleFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/EnrollmentCodes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Upload.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Upload.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=3', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/StudentReenroll.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/EnrollmentReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/User.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/User.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/User.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/User.php&staff_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/AddStudents.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Preferences.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Profiles.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Exceptions.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/UserFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=grades/InputFinalGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=grades/Grades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=attendance/Missing_Attendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/UploadUserPhoto.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/UploadUserPhoto.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/UserAdvancedReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/UserAdvancedReportStaff.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/Schedule.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/Requests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/MassSchedule.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/MassRequests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/MassDrops.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/ScheduleReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/RequestsReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/UnfilledRequests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/IncompleteSchedules.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/AddDrop.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/PrintSchedules.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/PrintRequests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/PrintClassLists.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/PrintClassPictures.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/Courses.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/Scheduler.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'scheduling/ViewSchedule.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/ReportCards.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/CalcGPA.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/Transcripts.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/GradeBreakdown.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/FinalGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/GPARankList.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/ReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/ReportCardComments.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/FixGPA.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/EditReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/EditHistoryMarkingPeriods.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/HistoricalReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/Administration.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/AddAbsences.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/AttendanceData.php?list_by_day=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/Percent.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/Percent.php?list_by_day=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/DailySummary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/StudentSummary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/DuplicateAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/AttendanceCodes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'attendance/FixDailyAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'eligibility/Student.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'eligibility/AddActivity.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'eligibility/StudentList.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'eligibility/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'eligibility/Activities.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'eligibility/EntryTimes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'tools/LogDetails.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'tools/DeleteLog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'schoolsetup/SchoolCustomFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'tools/Rollover.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Upload.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Upload.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/SystemPreference.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'students/Student.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/HonorRoll.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=grades/ProgressReports.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/User.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/HonorRollSetup.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'grades/AdminProgressReports.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&staff_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Exceptions_staff.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/StaffFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&category_id=3', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&category_id=6', 'Y', 'Y', '2019-07-29 06:26:33', NULL),
(1, 'messaging/Inbox.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'messaging/Compose.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'messaging/SentMail.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'messaging/Trash.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'messaging/Group.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'messaging/Inbox.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'messaging/Compose.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'messaging/SentMail.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'messaging/Trash.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(4, 'messaging/Group.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'messaging/Inbox.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'messaging/Compose.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'messaging/SentMail.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'messaging/Trash.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'messaging/Group.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'messaging/Inbox.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'messaging/Compose.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'messaging/SentMail.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'messaging/Trash.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(3, 'messaging/Group.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=6', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=7', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/User.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/PortalNotes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Schools.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Schools.php?new_school=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/CopySchool.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/MarkingPeriods.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Calendar.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Periods.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/GradeLevels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Rollover.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Courses.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/CourseCatalog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/PrintCatalog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/PrintCatalogGradeLevel.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/PrintAllCourses.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/UploadLogo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/TeacherReassignment.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&include=GeneralInfoInc&student_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/AssignOtherInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/AddUsers.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/AdvancedReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/AddDrop.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Letters.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/MailingLabels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/StudentLabels.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/PrintStudentInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/PrintStudentContactInfo.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/GoalReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/StudentFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/AddressFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/PeopleFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/EnrollmentCodes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Upload.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Upload.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=3', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/StudentReenroll.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/EnrollmentReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/User.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/User.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/User.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/User.php&staff_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/AddStudents.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Preferences.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Profiles.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Exceptions.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/UserFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=grades/InputFinalGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=grades/Grades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=attendance/Missing_Attendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=eligibility/EnterEligibility.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/UploadUserPhoto.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/UploadUserPhoto.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/UserAdvancedReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/UserAdvancedReportStaff.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/Schedule.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/Requests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/MassSchedule.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/MassRequests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/MassDrops.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/ScheduleReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/RequestsReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/UnfilledRequests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/IncompleteSchedules.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/AddDrop.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/PrintSchedules.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/PrintRequests.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/PrintClassLists.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/PrintClassPictures.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/Courses.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/Scheduler.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'scheduling/ViewSchedule.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/ReportCards.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/CalcGPA.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/Transcripts.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/GradeBreakdown.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/FinalGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/GPARankList.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/ReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/ReportCardComments.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/FixGPA.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/EditReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/EditHistoryMarkingPeriods.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/HistoricalReportCardGrades.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/Administration.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/AddAbsences.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/AttendanceData.php?list_by_day=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/Percent.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/Percent.php?list_by_day=true', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/DailySummary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/StudentSummary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/DuplicateAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/AttendanceCodes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'attendance/FixDailyAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'eligibility/Student.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'eligibility/AddActivity.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'eligibility/StudentList.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'eligibility/TeacherCompletion.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'eligibility/Activities.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'eligibility/EntryTimes.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/LogDetails.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/DeleteLog.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/Backup.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/Rollover.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Upload.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Upload.php?modfunc=edit', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/SystemPreference.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'students/Student.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/HonorRoll.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=grades/ProgressReports.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/User.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/HonorRollSetup.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/AdminProgressReports.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&staff_id=new', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Exceptions_staff.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/StaffFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&category_id=3', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&category_id=6', 'Y', 'Y', '2019-07-29 06:26:33', NULL),
(0, 'schoolsetup/SchoolCustomFields.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'messaging/Inbox.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'messaging/Compose.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'messaging/SentMail.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'messaging/Trash.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'messaging/Group.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Rooms.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/school_specific_standards.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=grades/AdminProgressReports.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/Reports.php?func=Basic', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/Reports.php?func=Ins_r', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'tools/Reports.php?func=Ins_cf', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/us_common_standards.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/EffortGradeLibrary.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'grades/EffortGradeSetup.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'scheduling/PrintSchedules.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(0, 'users/TeacherPrograms.php?include=attendance/MissingAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(0, 'users/Staff.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'schoolsetup/Rooms.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/TeacherPrograms.php?include=attendance/MissingAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(1, 'users/Staff.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'students/EnrollmentReport.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'users/TeacherPrograms.php?include=attendance/MissingAttendance.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'messaging/Inbox.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'messaging/Compose.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'messaging/SentMail.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'messaging/Trash.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'messaging/Group.php', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php&category_id=1', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php&category_id=2', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php&category_id=3', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php&category_id=4', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php&category_id=5', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'users/Staff.php&category_id=6', 'Y', 'Y', '2019-07-29 06:26:33', NULL),
(4, 'grades/ParentProgressReports.php', 'Y', NULL, '2019-07-29 02:26:33', NULL),
(0, 'schoolsetup/Sections.php', 'Y', 'Y', '2019-07-26 08:53:00', NULL),
(1, 'schoolsetup/Sections.php', 'Y', 'Y', '2019-07-26 08:53:25', NULL),
(0, 'tools/DataImport.php', 'Y', 'Y', '2019-07-26 08:53:25', NULL),
(1, 'tools/DataImport.php', 'Y', 'Y', '2019-07-26 08:53:25', NULL),
(0, 'tools/GenerateApi.php', 'Y', 'Y', '2020-11-03 12:34:02', NULL),
(1, 'tools/GenerateApi.php', 'Y', 'Y', '2019-08-05 09:33:56', NULL),
(0, 'scheduling/SchoolwideScheduleReport.php', 'Y', 'Y', '2022-09-27 20:54:07', NULL),
(1, 'scheduling/SchoolwideScheduleReport.php', 'Y', 'Y', '2022-09-27 20:54:07', NULL),
(0, 'scheduling/SchoolwideScheduleReport.php', 'Y', 'Y', '2025-10-25 07:54:26', NULL),
(1, 'scheduling/SchoolwideScheduleReport.php', 'Y', 'Y', '2025-10-25 07:54:26', NULL);

--
-- Dumping data for table `program_config`
--

INSERT INTO `program_config` (`syear`, `school_id`, `program`, `title`, `value`, `last_updated`, `updated_by`) VALUES
(2025, NULL, 'Currency', 'US Dollar (USD)', '1', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'British Pound (GBP)', '2', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Euro (EUR)', '3', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Canadian Dollar (CAD)', '4', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Australian Dollar (AUD)', '5', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Brazilian Real (BRL)', '6', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Chinese Yuan Renminbi (CNY)', '7', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Danish Krone (DKK)', '8', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Japanese Yen (JPY)', '9', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Indian Rupee (INR)', '10', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Indonesian Rupiah (IDR)', '11', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Korean Won  (KRW)', '12', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Malaysian Ringit (MYR)', '13', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Mexican Peso (MXN)', '14', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'New Zealand Dollar (NZD)', '15', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Norwegian Krone  (NOK)', '16', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Pakistan Rupee  (PKR)', '17', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Philippino Peso (PHP)', '18', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Saudi Riyal (SAR)', '19', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Singapore Dollar (SGD)', '20', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'South African Rand  (ZAR)', '21', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Swedish Krona  (SEK)', '22', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Swiss Franc  (CHF)', '23', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Thai Bhat  (THB)', '24', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'Turkish Lira  (TRY)', '25', '2019-07-28 22:26:33', NULL),
(2025, NULL, 'Currency', 'United Arab Emirates Dirham (AED)', '26', '2019-07-28 22:26:33', NULL),
(2025, 1, 'MissingAttendance', 'LAST_UPDATE', '2025-10-27', '2025-10-27 07:00:00', NULL),
(2025, 1, 'eligibility', 'START_DAY', '1', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'START_HOUR', '8', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'START_MINUTE', '00', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'START_M', 'AM', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'END_DAY', '5', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'END_HOUR', '16', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'END_MINUTE', '00', '2025-10-25 07:54:26', NULL),
(2025, 1, 'eligibility', 'END_M', 'PM', '2025-10-25 07:54:26', NULL),
(2025, 1, 'UPDATENOTIFY', 'display', 'Y', '2025-10-25 07:54:26', NULL),
(2025, 1, 'UPDATENOTIFY', 'display_school', 'Y', '2025-10-25 07:54:26', NULL),
(2025, 1, 'SeatFill', 'LAST_UPDATE', '2025-10-27', '2025-10-27 06:40:04', '1');

--
-- Dumping data for table `program_user_config`
--

INSERT INTO `program_user_config` (`user_id`, `school_id`, `program`, `title`, `value`, `last_updated`, `updated_by`) VALUES
(1, NULL, 'Preferences', 'THEME', 'blue', '2019-07-28 20:56:33', NULL),
(1, NULL, 'Preferences', 'MONTH', 'M', '2019-07-28 20:56:33', NULL),
(1, NULL, 'Preferences', 'DAY', 'j', '2019-07-28 20:56:33', NULL),
(1, NULL, 'Preferences', 'YEAR', 'Y', '2019-07-28 20:56:33', NULL),
(1, NULL, 'Preferences', 'HIDDEN', 'Y', '2019-07-28 20:56:33', NULL),
(1, NULL, 'Preferences', 'CURRENCY', '1', '2019-07-28 20:56:33', NULL),
(1, NULL, 'Preferences', 'HIDE_ALERTS', 'N', '2019-07-28 20:56:33', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2020-08-12 00:58:51', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2020-08-12 00:58:51', NULL),
(2, 1, 'Gradebook', 'SEM-16', NULL, '2020-08-12 00:58:51', NULL),
(2, 1, 'Gradebook', 'SEM-17', NULL, '2020-08-12 00:58:51', NULL),
(2, 1, 'Gradebook', 'SEM-E13', NULL, '2020-08-12 00:58:51', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2020-08-12 01:27:03', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2020-08-12 01:27:03', NULL),
(2, 1, 'Gradebook', 'SEM-16', NULL, '2020-08-12 01:27:03', NULL),
(2, 1, 'Gradebook', 'SEM-17', NULL, '2020-08-12 01:27:03', NULL),
(2, 1, 'Gradebook', 'SEM-E13', NULL, '2020-08-12 01:27:03', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'ASSIGNMENT_SORTING', 'ASSIGNMENT_ID_1', '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'ANOMALOUS_MAX', '100_1', '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'LATENCY', '0_1', '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'Q-14', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'Q-15', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'Q-16', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'Q-17', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'SEM-16', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'SEM-17', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'SEM-E13', NULL, '2020-08-12 01:27:21', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'ASSIGNMENT_SORTING', 'ASSIGNMENT_ID_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'WEIGHT', 'Y_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'ANOMALOUS_MAX', '100_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'LATENCY', '0_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'Q-14', '100_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'Q-15', '100_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'Q-16', '100_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'Q-17', '100_2', '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'SEM-16', NULL, '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'SEM-17', NULL, '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'SEM-E13', NULL, '2020-08-12 01:28:25', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'ASSIGNMENT_SORTING', 'ASSIGNMENT_ID_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'WEIGHT', 'Y_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'ANOMALOUS_MAX', '100_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'LATENCY', '0_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'Q-14', '100_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'Q-15', '100_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'Q-16', '100_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'Q-17', '100_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'SEM-14', '40_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'SEM-15', '40_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'SEM-E12', '20_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'SEM-16', '40_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'SEM-17', '40_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'SEM-E13', '20_18', '2020-08-12 04:51:28', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'ASSIGNMENT_SORTING', 'ASSIGNMENT_ID_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'WEIGHT', 'Y_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'ANOMALOUS_MAX', '100_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'LATENCY', '0_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'Q-14', '100_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'Q-15', '100_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'Q-16', '100_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'Q-17', '100_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'SEM-14', '40_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'SEM-15', '40_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'SEM-E12', '20_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'SEM-16', '40_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'SEM-17', '40_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'SEM-E13', '20_19', '2020-08-12 19:08:43', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2021-08-08 04:16:52', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2021-08-08 04:16:52', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2021-08-08 04:17:17', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2021-08-08 04:17:17', NULL),
(2, 1, 'Gradebook', 'ROUNDING', NULL, '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'ASSIGNMENT_SORTING', 'ASSIGNMENT_ID_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'WEIGHT', 'Y_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'DEFAULT_ASSIGNED', 'Y_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'ANOMALOUS_MAX', '100_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'LATENCY', '0_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'COMMENT_A', NULL, '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'Q-21', '100_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'Q-22', '100_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'Q-23', '100_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'Q-24', '100_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'SEM-21', '40_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'SEM-22', '40_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'SEM-E19', '20_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'SEM-23', '40_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'SEM-24', '40_28', '2021-08-08 04:17:31', NULL),
(2, 1, 'Gradebook', 'SEM-E20', '20_28', '2021-08-08 04:17:31', NULL);


--
-- Dumping data for table `report_card_grades`
--

INSERT INTO `report_card_grades` (`id`, `syear`, `school_id`, `title`, `sort_order`, `gpa_value`, `break_off`, `comment`, `grade_scale_id`, `unweighted_gp`, `last_updated`, `updated_by`) VALUES
(1, 2025, 1, 'A', 1, 0.00, 90, NULL, 1, 4.00, '2025-12-22 00:41:21', NULL),
(2, 2025, 1, 'B', 2, 0.00, 80, NULL, 1, 3.00, '2025-12-22 00:41:21', NULL),
(3, 2025, 1, 'C', 3, 0.00, 70, NULL, 1, 2.00, '2025-12-22 00:41:21', NULL),
(4, 2025, 1, 'D', 4, 0.00, 60, NULL, 1, 1.00, '2025-12-22 00:41:21', NULL),
(5, 2025, 1, 'F', 5, 0.00, 0, NULL, 1, 0.00, '2025-12-22 00:41:21', NULL),
(6, 2025, 1, 'Inc.', 6, 0.00, 0, NULL, 1, 0.00, '2025-12-22 00:41:21', NULL);

--
-- Dumping data for table `report_card_grade_scales`
--

INSERT INTO `report_card_grade_scales` (`id`, `syear`, `school_id`, `title`, `comment`, `sort_order`, `rollover_id`, `gp_scale`, `gpa_cal`, `last_updated`, `updated_by`) VALUES
(1, 2025, 1, 'Main', NULL, 1, NULL, 4.000, 'Y', '2025-12-22 00:41:21', NULL);


--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `school_id`, `title`, `capacity`, `description`, `sort_order`, `last_updated`, `updated_by`) VALUES
(1, 1, 'Lang 1', 30, ' ', 1, '2025-10-24 21:12:29', NULL),
(2, 1, 'Lang 2', 30, ' ', 2, '2025-10-24 21:12:36', NULL),
(3, 1, 'Social Studies 1', 30, ' ', 3, '2025-10-24 21:12:43', NULL),
(4, 1, 'Social Studies 2', 30, ' ', 4, '2025-10-24 21:12:51', NULL),
(5, 1, 'Math 1', 30, ' ', 5, '2025-10-24 21:12:58', NULL),
(6, 1, 'Math 2', 30, ' ', 6, '2025-10-24 21:13:06', NULL),
(7, 1, 'Ground Hall', 60, ' ', 7, '2025-10-24 21:13:27', NULL),
(8, 1, 'Science 1', 30, ' ', 8, '2025-10-24 21:13:34', NULL);

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`syear`, `school_id`, `student_id`, `start_date`, `end_date`, `modified_date`, `modified_by`, `course_id`, `course_weight`, `course_period_id`, `mp`, `marking_period_id`, `scheduler_lock`, `dropped`, `id`, `last_updated`, `updated_by`) VALUES
(2025, 1, 1, '2025-08-04', '2026-05-29', NULL, '1', 1, NULL, 1, 'FY', 1, NULL, 'N', 1, '2025-10-27 06:46:39', '1'),
(2025, 1, 1, '2025-08-04', '2025-12-19', NULL, '1', 2, NULL, 2, 'SEM', 12, NULL, 'N', 2, '2025-10-27 06:57:15', '1'),
(2025, 1, 3, '2025-08-04', '2026-05-29', NULL, '1', 4, NULL, 4, 'FY', 1, NULL, 'N', 3, '2025-10-27 06:57:34', '1'),
(2025, 1, 2, '2025-08-04', '2026-05-29', NULL, '1', 1, NULL, 1, 'FY', 1, NULL, 'N', 4, '2025-10-27 08:31:59', '1'),
(2025, 1, 7, '2025-08-04', '2026-05-29', NULL, '1', 4, NULL, 4, 'FY', 1, NULL, 'N', 5, '2025-10-27 08:52:54', '1'),
(2025, 1, 3, '2025-08-04', '2026-05-29', NULL, '1', 1, NULL, 1, 'FY', 1, NULL, 'N', 6, '2025-10-27 08:53:42', '1');

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `syear`, `title`, `address`, `city`, `state`, `zipcode`, `area_code`, `phone`, `principal`, `www_address`, `e_mail`, `reporting_gp_scale`, `last_updated`, `updated_by`) VALUES
(1, 2025, 'Greenwood International School', '2456 Elm Street', 'Springfield', 'USA', 'IL 62704', NULL, NULL, NULL, NULL, NULL, 4.000, '2025-10-25 08:25:23', '1');


--
-- Dumping data for table `school_calendars`
--

INSERT INTO `school_calendars` (`school_id`, `title`, `syear`, `calendar_id`, `default_calendar`, `days`, `rollover_id`, `last_updated`, `updated_by`) VALUES
(1, 'Main Calendar 2025_2026', 2025, 1, NULL, 'MTWHF', NULL, '2025-10-25 08:07:56', '1');


--
-- Dumping data for table `school_gradelevels`
--

INSERT INTO `school_gradelevels` (`id`, `school_id`, `short_name`, `title`, `next_grade_id`, `sort_order`, `last_updated`, `updated_by`) VALUES
(1, 1, '9', '9th Grade', 2, 1, '2025-10-24 18:00:00', NULL),
(2, 1, '10', '10th Grade', 3, 2, '2025-10-24 18:00:00', NULL),
(3, 1, '11', '11th Grade', 4, 3, '2025-10-24 18:00:00', NULL),
(4, 1, '12', '12th Grade', NULL, 4, '2025-10-24 18:00:00', NULL);


--
-- Dumping data for table `school_gradelevel_sections`
--

INSERT INTO `school_gradelevel_sections` (`id`, `school_id`, `name`, `sort_order`, `last_updated`, `updated_by`) VALUES
(1, 1, 'Section A', 1, '2025-10-24 15:35:18', NULL),
(2, 1, 'Section B', 2, '2025-10-24 15:35:28', NULL),
(3, 1, 'Section C', 3, '2025-10-24 15:35:39', NULL);

--
-- Dumping data for table `school_periods`
--

INSERT INTO `school_periods` (`period_id`, `syear`, `school_id`, `sort_order`, `title`, `short_name`, `length`, `block`, `ignore_scheduling`, `attendance`, `rollover_id`, `start_time`, `end_time`, `last_updated`, `updated_by`) VALUES
(1, 2025, 1, 1, 'Daily Attendance', 'Attendance', 15, NULL, NULL, 'Y', NULL, '08:00:00', '08:15:00', '2025-10-24 18:00:00', NULL),
(2, 2025, 1, 2, 'Period 1', 'P1', 50, NULL, NULL, NULL, NULL, '08:30:00', '09:20:00', '2025-10-24 18:00:00', NULL),
(3, 2025, 1, 3, 'Period 2', 'P2', 50, NULL, NULL, NULL, NULL, '09:30:00', '10:20:00', '2025-10-24 18:00:00', NULL),
(4, 2025, 1, 4, 'Period 3', 'P3', 50, NULL, NULL, NULL, NULL, '10:30:00', '11:20:00', '2025-10-24 18:00:00', NULL),
(5, 2025, 1, 5, 'Lunch', 'Lunch', 38, NULL, NULL, NULL, NULL, '11:21:00', '11:59:00', '2025-10-24 18:00:00', NULL),
(6, 2025, 1, 6, 'Period 4', 'P4', 50, NULL, NULL, NULL, NULL, '12:00:00', '12:50:00', '2025-10-24 18:00:00', NULL),
(7, 2025, 1, 7, 'Period 5', 'P5', 50, NULL, NULL, NULL, NULL, '13:00:00', '13:50:00', '2025-10-24 18:00:00', NULL),
(8, 2025, 1, 8, 'Period 6', 'P6', 50, NULL, NULL, NULL, NULL, '14:00:00', '14:50:00', '2025-10-24 18:00:00', NULL);


--
-- Dumping data for table `school_quarters`
--

INSERT INTO `school_quarters` (`marking_period_id`, `syear`, `school_id`, `semester_id`, `title`, `short_name`, `sort_order`, `start_date`, `end_date`, `post_start_date`, `post_end_date`, `does_grades`, `does_exam`, `does_comments`, `rollover_id`, `last_updated`, `updated_by`) VALUES
(14, 2025, 1, 12, 'Quarter 1', 'Q1', 3, '2025-08-04', '2025-10-10', '2025-08-04', '2025-10-10', 'Y', NULL, NULL, NULL, '2025-10-27 11:44:49', '1'),
(15, 2025, 1, 12, 'Quarter 2', 'Q2', 4, '2025-10-13', '2025-12-19', '2025-10-13', '2025-12-19', 'Y', NULL, NULL, NULL, '2025-10-27 11:45:52', '1'),
(16, 2025, 1, 13, 'Quarter 3', 'Q3', 6, '2026-01-05', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-27 11:46:40', '1'),
(17, 2025, 1, 13, 'Quarter 4', 'Q4', 7, '2026-03-16', '2026-05-29', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-27 11:47:10', '1');

--
-- Dumping data for table `school_semesters`
--

INSERT INTO `school_semesters` (`marking_period_id`, `syear`, `school_id`, `year_id`, `title`, `short_name`, `sort_order`, `start_date`, `end_date`, `post_start_date`, `post_end_date`, `does_grades`, `does_exam`, `does_comments`, `rollover_id`, `last_updated`, `updated_by`) VALUES
(12, 2025, 1, 1, 'Semester 1', 'SEM1', 2, '2025-08-04', '2025-12-19', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 08:07:11', '1'),
(13, 2025, 1, 1, 'Semester 2', 'SEM2', 3, '2026-01-05', '2026-05-29', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 08:07:43', '1');


--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`marking_period_id`, `syear`, `school_id`, `title`, `short_name`, `sort_order`, `start_date`, `end_date`, `post_start_date`, `post_end_date`, `does_grades`, `does_exam`, `does_comments`, `rollover_id`, `last_updated`, `updated_by`) VALUES
(1, 2025, 1, 'Full Year', 'FY', 1, '2025-08-04', '2026-05-29', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 07:54:26', NULL);

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `current_school_id`, `title`, `first_name`, `last_name`, `middle_name`, `phone`, `email`, `profile`, `homeroom`, `profile_id`, `primary_language_id`, `gender`, `ethnicity_id`, `birthdate`, `alternate_id`, `name_suffix`, `second_language_id`, `third_language_id`, `is_disable`, `physical_disability`, `disability_desc`, `img_name`, `img_content`, `last_updated`, `updated_by`) VALUES
(1, 1, NULL, 'Bob', 'Ghosh', '', NULL, 'bob@os4ed.com', 'admin', NULL, 0, 1, 'Male', 1, NULL, NULL, NULL, 5, NULL, 'N', 'N', NULL, NULL, NULL, '2025-10-25 07:55:17', NULL),
(2, 1, 'Mrs.', 'Charlotte', 'Davis', NULL, NULL, 'charlotte@gmail.com', NULL, NULL, NULL, 1, 'Female', 1, '1983-02-02', NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '2025-10-24 18:00:00', NULL),
(3, 1, NULL, 'Tim', 'Jones', NULL, NULL, 'tim@os4ed.com', 'admin', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '2025-10-24 18:00:00', NULL),
(4, 1, NULL, 'Tim', 'Jones', NULL, NULL, 'timjones@os4ed.com', 'admin', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '2025-10-24 18:00:00', NULL),
(5, 1, 'Mr.', 'Rudyard', 'Kipling', NULL, NULL, 'kiplling@gmail.com', 'teacher', NULL, 2, 1, 'Male', 1, '1992-09-01', NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '2025-10-24 18:00:00', NULL),
(6, 1, 'Mr.', 'Charles', 'Dickens', NULL, NULL, 'charles.dickens@os4ed.com', 'teacher', NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '2025-10-24 18:00:00', NULL),
(7, 1, 'Mr.', 'David', 'Hilbert', NULL, NULL, 'david.hilbert@os4ed.com', 'teacher', NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '2025-10-24 18:00:00', NULL);

--
-- Dumping data for table `staff_address`
--

INSERT INTO `staff_address` (`staff_address_id`, `staff_id`, `staff_address1_primary`, `staff_address2_primary`, `staff_city_primary`, `staff_state_primary`, `staff_zip_primary`, `staff_address1_mail`, `staff_address2_mail`, `staff_city_mail`, `staff_state_mail`, `staff_zip_mail`, `last_update`, `staff_pobox_mail`, `last_updated`, `updated_by`) VALUES
(1, 5, '123 Main st.', 'Secaucus', 'Atlanta', 'Georgia', '12345', '123 Main st.', 'Secaucus', 'Atlanta', 'Georgia', '12345', '0000-00-00 00:00:00', NULL, '2025-10-24 18:00:00', NULL);

--
-- Dumping data for table `staff_certification`
--

--
-- Dumping data for table `staff_contact`
--

INSERT INTO `staff_contact` (`staff_phone_id`, `staff_id`, `last_update`, `staff_home_phone`, `staff_mobile_phone`, `staff_work_phone`, `staff_work_email`, `staff_personal_email`, `last_updated`, `updated_by`) VALUES
(1, 5, '0000-00-00 00:00:00', '3179138170', NULL, NULL, NULL, NULL, '2025-10-24 18:00:00', NULL);


--
-- Dumping data for table `staff_emergency_contact`
--

INSERT INTO `staff_emergency_contact` (`staff_emergency_contact_id`, `staff_id`, `staff_emergency_first_name`, `staff_emergency_last_name`, `staff_emergency_relationship`, `staff_emergency_home_phone`, `staff_emergency_mobile_phone`, `staff_emergency_work_phone`, `staff_emergency_email`, `last_updated`, `updated_by`) VALUES
(1, 5, 'Poulami', 'Bose', '', NULL, NULL, NULL, NULL, '2025-12-30 11:35:47', NULL);

--
-- Dumping data for table `staff_field_categories`
--

INSERT INTO `staff_field_categories` (`id`, `title`, `sort_order`, `include`, `admin`, `teacher`, `parent`, `none`, `last_updated`, `updated_by`) VALUES
(1, 'Demographic Info', 1, NULL, 'Y', 'Y', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(2, 'Addresses & Contacts', 2, NULL, 'Y', 'Y', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(3, 'School Information', 3, NULL, 'Y', 'Y', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(4, 'Certification Information', 4, NULL, 'Y', 'Y', 'Y', 'Y', '2019-07-29 02:26:33', NULL),
(5, 'Schedule', 5, NULL, 'Y', 'Y', NULL, NULL, '2019-07-29 02:26:33', NULL),
(6, 'Files', 6, NULL, 'Y', 'Y', NULL, NULL, '2019-07-29 06:26:33', NULL);

--
-- Dumping data for table `staff_school_info`
--

INSERT INTO `staff_school_info` (`staff_school_info_id`, `staff_id`, `category`, `job_title`, `joining_date`, `end_date`, `home_school`, `opensis_access`, `opensis_profile`, `school_access`, `last_updated`, `updated_by`) VALUES
(1, 1, 'Super Administrator', 'Super Administrator', '2019-01-01', NULL, 1, 'Y', '0', '1', '2020-01-22 16:18:03', NULL),
(2, 2, 'Administrator', NULL, '2025-08-04', NULL, 1, 'N', NULL, ',,', '2025-10-07 18:58:29', NULL),
(3, 3, 'Administrator', 'Administrator', '2025-08-04', NULL, 1, 'Y', '0', ',1,', '2025-12-05 21:31:46', NULL),
(4, 4, 'Administrator', 'Administrator', '2025-08-04', NULL, 1, 'Y', '0', ',1,', '2025-12-05 22:58:16', NULL),
(5, 5, 'Teacher', 'Teacher', '2025-08-04', NULL, 1, 'Y', '2', ',1,', '2025-12-22 01:04:30', NULL),
(6, 6, 'Teacher', 'Teacher', '2025-08-04', NULL, 1, 'Y', '2', ',1,', '2025-12-30 18:03:29', NULL),
(7, 7, 'Teacher', 'Teacher', '2025-08-04', NULL, 1, 'Y', '2', ',1,', '2025-12-30 18:05:36', NULL);

--
-- Dumping data for table `staff_school_relationship`
--

INSERT INTO `staff_school_relationship` (`staff_id`, `school_id`, `syear`, `last_updated`, `updated_by`, `start_date`, `end_date`) VALUES
(1, 1, 2025, '2025-10-25 07:54:26', NULL, '2025-08-04', '0000-00-00'),
(2, 1, 2025, '2025-12-22 00:41:13', NULL, '2025-08-04', NULL),
(3, 1, 2025, '2025-12-22 00:41:13', NULL, '2025-08-04', NULL),
(4, 1, 2025, '2025-12-22 00:41:13', NULL, '2025-08-04', NULL),
(5, 1, 2025, '2025-12-22 00:41:13', NULL, '2025-08-04', NULL),
(6, 1, 2025, '2025-12-22 00:41:13', NULL, '2025-08-04', NULL),
(7, 1, 2025, '2025-12-22 00:41:13', NULL, '2025-08-04', NULL);

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `last_name`, `first_name`, `middle_name`, `name_suffix`, `gender`, `ethnicity_id`, `common_name`, `social_security`, `birthdate`, `language_id`, `estimated_grad_date`, `alt_id`, `email`, `phone`, `is_disable`, `last_updated`, `updated_by`) VALUES
(1, 'Ahuja', 'Vihaan', NULL, NULL, 'Male', 3, NULL, NULL, '1993-06-02', 1, NULL, NULL, NULL, NULL, NULL, '2025-10-07 13:51:27', NULL),
(2, 'Boucher', 'Arthur', NULL, NULL, 'Male', 1, NULL, NULL, '2005-08-24', 5, NULL, NULL, NULL, NULL, NULL, '2025-10-07 13:53:12', NULL),
(3, 'Brown', 'Sophia', NULL, NULL, 'Female', 1, NULL, NULL, '2005-10-04', 1, NULL, NULL, NULL, NULL, NULL, '2025-10-07 13:56:34', NULL),
(4, 'Fang', 'Wang', NULL, NULL, 'Female', 6, NULL, NULL, '2004-01-08', 4, NULL, NULL, NULL, NULL, NULL, '2025-10-07 14:29:55', NULL),
(5, 'Garcia', 'Clare', NULL, NULL, 'Female', 1, NULL, NULL, '2005-05-10', NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:21:24', NULL),
(6, 'Jones', 'Amelia', NULL, NULL, 'Female', 4, NULL, NULL, '1992-06-09', 1, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:22:15', NULL),
(7, 'Keita', 'Audre', NULL, NULL, 'Female', 5, NULL, NULL, '2002-01-01', 1, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:23:15', NULL),
(8, 'Kimathi', 'Kwame', NULL, NULL, 'Male', 8, NULL, NULL, '2006-05-01', 1, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:29:04', NULL),
(9, 'Miller', 'James', NULL, NULL, 'Male', 1, NULL, NULL, '2005-05-11', 14, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:35:02', NULL),
(10, 'Sharma', 'Aarohi', NULL, NULL, 'Female', 7, NULL, NULL, '1993-02-16', 3, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:39:28', NULL),
(11, 'Silva', 'Luis', NULL, NULL, 'Male', 9, NULL, NULL, '2005-03-17', 13, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:40:43', NULL),
(12, 'Smith', 'Oliver', NULL, NULL, 'Male', 5, NULL, NULL, '1992-02-04', 11, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:41:40', NULL),
(13, 'Watanabe', 'Akari', NULL, NULL, 'Female', 6, NULL, NULL, '2005-05-03', 10, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:42:33', NULL),
(14, 'Wei', 'Li', NULL, NULL, 'Male', 8, NULL, NULL, '2004-02-11', 4, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:43:24', NULL),
(15, 'Yamamoto', 'Akio', NULL, NULL, 'Male', 10, NULL, NULL, '2005-06-01', 15, NULL, NULL, NULL, NULL, NULL, '2025-10-07 15:44:20', NULL);

--
-- Dumping data for table `students_join_people`
--

--
-- Dumping data for table `student_address`
--

--
-- Dumping data for table `student_enrollment`
--

INSERT INTO `student_enrollment` (`id`, `syear`, `school_id`, `student_id`, `grade_id`, `section_id`, `start_date`, `end_date`, `enrollment_code`, `drop_code`, `next_school`, `calendar_id`, `last_school`, `last_updated`, `updated_by`) VALUES
(1, 2025, 1, 1, 3, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(2, 2025, 1, 2, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(3, 2025, 1, 3, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(4, 2025, 1, 4, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(5, 2025, 1, 5, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(6, 2025, 1, 6, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(7, 2025, 1, 7, 4, NULL, '2025-08-04', NULL, 5, NULL, -1, 1, NULL, '2025-12-22 14:11:26', NULL),
(8, 2025, 1, 8, 4, NULL, '2025-08-04', NULL, 5, NULL, -1, 1, NULL, '2025-12-22 14:11:26', NULL),
(9, 2025, 1, 9, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(10, 2025, 1, 10, 3, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(11, 2025, 1, 11, 1, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(12, 2025, 1, 12, 3, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(13, 2025, 1, 13, 4, NULL, '2025-08-04', NULL, 5, NULL, -1, 1, NULL, '2025-12-22 14:11:26', NULL),
(14, 2025, 1, 14, 2, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL),
(15, 2025, 1, 15, 3, NULL, '2025-08-04', NULL, 5, NULL, 1, 1, NULL, '2025-12-22 00:41:21', NULL);


--
-- Dumping data for table `student_enrollment_codes`
--

INSERT INTO `student_enrollment_codes` (`id`, `syear`, `title`, `short_name`, `type`, `last_updated`, `updated_by`) VALUES
(1, 2025, 'Transferred Out', 'TRAN', 'TrnD', '2019-07-28 12:56:33', NULL),
(2, 2025, 'Transferred In', 'TRAN', 'TrnE', '2019-07-28 12:56:33', NULL),
(3, 2025, 'Rolled Over', 'ROLL', 'Roll', '2019-07-28 12:56:33', NULL),
(4, 2025, 'Dropped Out', 'DROP', 'Drop', '2019-07-28 12:56:33', NULL),
(5, 2025, 'New', 'NEW', 'Add', '2019-07-28 12:56:33', NULL);

--
-- Dumping data for table `student_field_categories`
--

INSERT INTO `student_field_categories` (`id`, `title`, `sort_order`, `include`, `last_updated`, `updated_by`) VALUES
(1, 'General Info', 1, NULL, '2019-07-28 19:26:33', NULL),
(2, 'Medical', 3, NULL, '2019-07-28 19:26:33', NULL),
(3, 'Addresses & Contacts', 2, NULL, '2019-07-28 19:26:33', NULL),
(4, 'Comments', 4, NULL, '2019-07-28 19:26:33', NULL),
(5, 'Goals', 5, NULL, '2019-07-28 19:26:33', NULL),
(6, 'Enrollment Info', 6, NULL, '2019-07-28 19:26:33', NULL),
(7, 'Files', 7, NULL, '2019-07-28 19:26:33', NULL);


--
-- Adding Schoolwide Schedule Report to `profile_exceptions`
--

INSERT INTO `profile_exceptions` (`profile_id`, `modname`, `can_use`, `can_edit`) VALUES
('0', 'scheduling/SchoolwideScheduleReport.php', 'Y', 'Y'),
('1', 'scheduling/SchoolwideScheduleReport.php', 'Y', 'Y');


--
-- Dumping data for table `student_goal`
--

--
-- Dumping data for table `student_goal_progress`
--

--
-- Dumping data for table `student_gpa_calculated`
--

INSERT INTO `student_gpa_calculated` (`student_id`, `marking_period_id`, `mp`, `gpa`, `weighted_gpa`, `unweighted_gpa`, `class_rank`, `grade_level_short`, `cgpa`, `cum_unweighted_factor`, `last_updated`, `updated_by`) VALUES
(1, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.750000, '2025-10-27 08:53:57', '1'),
(2, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:32:29', '1'),
(3, 12, NULL, 3.00, NULL, 3.00, 1, NULL, NULL, NULL, '2025-10-27 08:54:04', '1'),
(7, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-27 08:53:24', '1');

--
-- Dumping data for table `student_immunization`
--

--
-- Dumping data for table `student_medical_alerts`
--

--
-- Dumping data for table `student_medical_notes`
--

--
-- Dumping data for table `student_medical_visits`
--

--
-- Dumping data for table `student_mp_comments`
--

--
-- Dumping data for table `student_report_card_grades`
--

INSERT INTO `student_report_card_grades` (`syear`, `school_id`, `student_id`, `course_period_id`, `report_card_grade_id`, `report_card_comment_id`, `comment`, `grade_percent`, `marking_period_id`, `grade_letter`, `weighted_gp`, `unweighted_gp`, `gp_scale`, `gpa_cal`, `credit_attempted`, `credit_earned`, `credit_category`, `course_code`, `course_title`, `id`, `last_updated`, `updated_by`) VALUES
(2025, 1, 1, 1, 2, NULL, NULL, 85.00, '12', 'B', NULL, 3.000, 4.000, NULL, NULL, NULL, NULL, NULL, 'Attendance All', 1, '2025-10-27 08:31:42', '1'),
(2025, 1, 2, 1, 4, NULL, NULL, 65.00, '12', 'D', NULL, 1.000, 4.000, NULL, NULL, NULL, NULL, NULL, 'Attendance All', 2, '2025-10-27 08:32:29', '1'),
(2025, 1, 3, 4, 3, NULL, NULL, 75.00, '12', 'C', NULL, 2.000, 4.000, NULL, NULL, NULL, NULL, NULL, 'Algebra ll', 3, '2025-10-27 08:53:24', '1'),
(2025, 1, 7, 4, 1, NULL, NULL, 95.00, '12', 'A', NULL, 4.000, 4.000, NULL, NULL, NULL, NULL, NULL, 'Algebra ll', 4, '2025-10-27 08:53:24', '1'),
(2025, 1, 1, 2, 2, NULL, NULL, 85.00, '12', 'B', NULL, 3.000, 4.000, NULL, NULL, NULL, NULL, NULL, 'Reading', 5, '2025-10-27 08:53:57', '1'),
(2025, 1, 3, 1, 2, NULL, NULL, 85.00, '12', 'B', NULL, 3.000, 4.000, NULL, 1.500, 1.500, NULL, NULL, 'Attendance All', 6, '2025-10-27 08:54:04', '1');

--
-- Dumping data for table `system_preference`
--

INSERT INTO `system_preference` (`id`, `school_id`, `full_day_minute`, `half_day_minute`, `last_updated`, `updated_by`) VALUES
(1, 1, 5, 2, '2019-07-28 19:26:33', NULL);


--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `profile`, `title`, `last_updated`, `updated_by`) VALUES
(0, 'admin', 'Super Administrator', '2022-09-27 13:54:07', NULL),
(1, 'admin', 'Administrator', '2019-07-28 08:26:33', NULL),
(2, 'teacher', 'Teacher', '2019-07-28 08:26:33', NULL),
(3, 'student', 'Student', '2019-07-28 08:26:33', NULL),
(4, 'parent', 'Parent', '2019-07-28 08:26:33', NULL),
(5, 'admin', 'Admin Asst', '2019-07-28 08:26:33', NULL),
(6, 'admin', 'Test', '2022-10-13 22:08:47', NULL);";

$dbconn = new mysqli($_SESSION['host'],$_SESSION['username'],$_SESSION['password'],$_SESSION['db'],$_SESSION['port']);
$sqllines = par_spt("/[\n]/",$text);
$cmd = '';
foreach($sqllines as $l)
{
	if(par_rep_mt('/^\s*--/',$l) == 0)
	{
		$cmd .= ' ' . $l . "\n";
		if(par_rep_mt('/.+;/',$l) != 0)
		{
			$result = $dbconn->query($cmd) or die($dbconn->error);
			$cmd = '';
		}
	}
}
        
$dbconn->query("INSERT INTO `gradebook_assignments` (`assignment_id`, `staff_id`, `marking_period_id`, `course_period_id`, `course_id`, `assignment_type_id`, `title`, `assigned_date`, `due_date`, `points`, `description`, `ungraded`, `last_updated`, `updated_by`) VALUES
(1, 5, 12, 1, NULL, 1, 'Assignment 1', '2025-10-27', '2025-11-04', 100, NULL, 1, '2025-10-27 08:07:16', NULL),
(2, 5, 12, 1, NULL, 2, 'Assignment 2', '2025-10-28', '2025-12-01', 50, NULL, 1, '2025-10-27 08:08:11', NULL);");

$dbconn->query("INSERT INTO `login_authentication` (`id`, `user_id`, `profile_id`, `username`, `password`, `last_login`, `failed_login`, `last_updated`, `updated_by`) VALUES
(1, 1, 0, 'os4ed', '$2y$10$IPR.lDaKddyDTt2B3XRDfuEj2MN7gKgkcxVY4vNlGJJuf3CVM038u', '2025-10-26 23:40:04', 0, '2025-10-27 06:40:04', '1'),
(2, 1, 3, 'Vihaan', '$2y$10$j.ZSVK3ZAWUUJAUTIj8mz./qDgXFicbNr6g7cKtWO.jfpOvY8.iYq', NULL, 0, '2025-10-07 13:51:27', NULL),
(3, 2, 3, 'arthur', '$2y$10$pLVUfl7m1Daz81DSpYCwm.mZybxb198GWmiqibjzXvJpEhpIHi.ge', NULL, 0, '2025-10-07 13:53:12', NULL),
(4, 3, 3, 'sophia', '$2y$10$CgH2kSzY7xcGOdLNo3vOFe7XyPfwajqH2ocAzL12UYZoMYbGVrLnG', NULL, 0, '2025-10-07 13:56:34', NULL),
(5, 4, 3, 'wang', '$2y$10$bk3nyUjzN89yNqSC5onZv.Lq.2HB2JUJMj69S5TCpxqSi8/w6PbKS', NULL, 0, '2025-10-07 14:29:55', NULL),
(6, 5, 3, 'clare', '$2y$10$nZm3cUxdUbaxtWhZ2ILuVuT1aYg0IXo2jzJcm1nBMADP.AB.5npw.', NULL, 0, '2025-10-07 15:21:24', NULL),
(7, 6, 3, 'amelia', '$2y$10$kfUJqL9oAJktw5IkZiOJNOiHS/AgUtfbmlG8ZEzxwX3KZo6bka4Qu', NULL, 0, '2025-10-07 15:22:15', NULL),
(8, 7, 3, 'audre', '$2y$10$QJk48Vwuw7B3OFkfcbwTBOCLiETl0DZtcHuid2nONa6AMAdV0bYTO', NULL, 0, '2025-10-07 15:23:15', NULL),
(9, 8, 3, 'kwame', '$2y$10$gXv8mPROOExDjtBR23eqO.a91uoVOlp5e5S5xgDdGTluSe/HuzPHC', NULL, 0, '2025-10-07 15:29:04', NULL),
(10, 9, 3, 'james', '$2y$10$.xlDEO0J6PD0CUULOHjzYul3288OJYhw5cPy3W9MZPRELGa0ukeIW', NULL, 0, '2025-10-07 15:35:02', NULL),
(11, 10, 3, 'aarohi', '$2y$10$3iK.Pl1f5/paw9Xy.seMP.gt9aEbkaKjxfBxAj.UVz.bY0TrB0.i6', NULL, 0, '2025-10-07 15:39:28', NULL),
(12, 11, 3, 'luis', '$2y$10$HOF9O/WNZwW3pGqDixd8UeqYl5jEHzPlCbsHBV5jPKTD.QN.BHMR2', NULL, 0, '2025-10-07 15:40:43', NULL),
(13, 12, 3, 'oliver', '$2y$10$rRhBBuwlF4EKtXpSmROXBeAyXIBuW14PlwRgW5ZwWD0QbZWwlpK52', NULL, 0, '2025-10-07 15:41:40', NULL),
(14, 13, 3, 'akari', '$2y$10$g9Cpn3v2w/tDKp4ClO7LWeG0tFXLW905uGNke0VawzkUPP8RgCGMW', NULL, 0, '2025-10-07 15:42:33', NULL),
(15, 14, 3, 'liwei', '$2y$10$lxcSJ2oHSmegfdfJ5ylzGO9wAubQvF7FronwC5l5FZT1zy.QjONVy', NULL, 0, '2025-10-07 15:43:24', NULL),
(16, 15, 3, 'akio', '$2y$10$lwndEGPklntp6iOd6D89tOFBXQIcW4PLYSX7727yRk.tV6KEBRaoS', NULL, 0, '2025-10-07 15:44:20', NULL),
(17, 3, 0, 'timjones', '$2y$10$p.Nf60E5qJFj5lW2XcNs6uqfsb9JD6cLLpgTtzCg2ihD/FALvTBFa', NULL, 0, '2025-10-05 21:31:46', NULL),
(18, 4, 0, 'timejones', '$2y$10$p9OxyrKXWk9XXe2WoAGOQ..oET37ulhAb/HL8w1Y9JONtG3EkSknG', NULL, 0, '2025-10-05 22:58:16', NULL),
(19, 5, 2, 'kiplling', '$2y$10$TBh9f8whgsNEc0ft4C2ub.ie38/slOJmRBfeITsKr0PwWaOb8F.nO', '2025-10-26 23:47:26', 0, '2025-10-27 06:47:26', '5'),
(20, 6, 2, 'charles', '$2y$10$N97QZPgsIsXxqt/5PQ1PD.a23XZwiNlSeM.feOOE1FyLgvZyDJ.Bm', '2025-10-27 01:14:00', 0, '2025-10-27 08:14:00', '6'),
(21, 7, 2, 'davidhilbert', '$2y$10$fhq21olfcyZo.XkVwLgHIOfeiEgFm3R8SgeO72OawTsblb5DlXMz6', NULL, 0, '2025-10-24 17:57:45', NULL);");

$dbconn->query("CALL CALC_MISSING_ATTENDANCE();");

?>