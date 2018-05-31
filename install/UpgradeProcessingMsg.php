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
error_reporting(0);
session_start();
$_SESSION['db'] = $_POST['sdb'];
?>
<html>
<head>
<META http-equiv="refresh" content="5; URL=Upgrade.php">
<link rel="stylesheet" type="text/css" href="../styles/Installer.css" />
</head>
<body>
<div class="heading2">Upgrade Processing
<center>
<div style="height:280px; width:90%;">
<br />
<br />
Your system is being upgraded to the new version. This will take a few minutes. 

Please be patient and do not click the Refresh or browser Back buttons.

<div class="loading_bar" style="margin-top:40px;"></div>
</div>
</center>
</div>
</body>
</html>
