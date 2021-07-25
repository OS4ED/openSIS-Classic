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
// config variables for include/AddressInc.php
// set this to false to disable auto-pull-downs for the contact info Description field
include('../../RedirectModulesInc.php');
$info_apd = true;
// set this to false to disable mailing address display
$use_mailing = true;
// set this to false to disable bus pickoff/dropoff defaulting checked
$use_bus = true;
// set this to false to disable legacy contact info
$use_contact = true;
// these are the static items for the dynamic select lists in the format

$city_options = array('Kokomo'=>'Kokomo');
$state_options = array('IN'=>'IN');
$zip_options = array('46901'=>'46901','46902'=>'46902');

$relation_options = array('Father'=>_father,
'Mother'=>_mother,
'Step Mother'=>_stepMother,
'Step Father'=>_stepFather,
'Grandmother'=>_grandmother,
'Grandfather'=>_grandfather,
'Legal Guardian'=>_legalGuardian,
'Other Family Member'=>_otherFamilyMember,
);
if($info_apd)
	$info_options_x = array('Phone'=>_phone,
	'Cell Phone'=>_cellPhone,
	'Work Phone'=>_workPhone,
	'Employer'=>_employer,
);

?>