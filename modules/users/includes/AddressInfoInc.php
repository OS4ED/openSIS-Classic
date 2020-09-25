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

include('../../../RedirectIncludes.php');

$_SESSION['staff_selected'] = $staff['STAFF_ID'];
$addr = DBGet(DBQuery('SELECT STREET_ADDRESS_1 as ADDRESS,STREET_ADDRESS_2 as STREET,CITY,STATE,ZIPCODE FROM student_address WHERE PEOPLE_ID=' . $_SESSION['staff_selected']));
$addr = $addr[1];

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4 text-right">'._address.' <span class="text-danger">*</span></label>';
echo '<div class="col-lg-8">'.TextInput($addr['ADDRESS'], 'student_addres[STREET_ADDRESS_1]', '', 'placeholder='._address.' size=25 maxlength=100 id=email').'</div>';
echo '</div>'; //.form-group
echo '</div>'; //.col-md-6

echo '<div class="col-md-6">';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4 text-right">'._street.'</label>';
echo '<div class="col-lg-8">'.TextInput($addr['STREET'], 'student_addres[STREET_ADDRESS_2]', '', 'size=25 maxlength=100 id=email').'</div>';
echo '</div>'; //.form-group
echo '</div>'; //.col-md-6

echo '<div class="col-md-6">';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4 text-right">'._city.' <span class=text-danger>*</span></label>';
echo '<div class="col-lg-8">'.TextInput($addr['CITY'], 'student_addres[CITY]', '', 'size=25 maxlength=100 id=email').'</div>';
echo '</div>'; //.form-group
echo '</div>'; //.col-md-6

echo '<div class="col-md-6">';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4 text-right">'._state.' <span class=text-danger>*</span></label>';
echo '<div class="col-lg-8">'.TextInput($addr['STATE'], 'student_addres[STATE]', '', 'size=25 maxlength=100 id=email').'</div>';
echo '</div>'; //.form-group
echo '</div>'; //.col-md-6

echo '<div class="col-md-6">';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4 text-right">'._zipCode.' <span class=text-danger>*</span></label>';
echo '<div class="col-lg-8">'.TextInput($addr['ZIPCODE'], 'student_addres[ZIPCODE]', '', 'size=25 maxlength=100 id=email').'</div>';
echo '</div>'; //.form-group
echo '</div>'; //.col-md-6
include('modules/users/includes/OtherInfoUserInc.php');
echo '</div>'; //.row

$_REQUEST['category_id'] = 3;


?>
