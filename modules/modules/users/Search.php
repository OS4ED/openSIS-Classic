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
include('../../RedirectModulesInc.php');
$modcat = 'users';
$_REQUEST['modname'] = '';
if (AllowUse('users/User.php')) {
    if (User('PROFILE_ID') == 4) {
        $_REQUEST['modname'] = 'users/User.php';
        $_REQUEST['next_modname'] = 'users/User.php';
    } else {
        $_REQUEST['modname'] = 'users/Staff.php';
        $_REQUEST['next_modname'] = 'users/Staff.php';
    }
} elseif (AllowUse('users/Preferences.php')) {
    $_REQUEST['modname'] = 'users/Preferences.php';
}
if ($_REQUEST['modname']) {
    //echo "<SCRIPT language=javascript>parent.help.location=\"Bottom.php?modcat=$modcat&modname=$_REQUEST[modname]\";</SCRIPT>";
    include("modules/$_REQUEST[modname]");
}
?>