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
//print_r($_REQUEST);
include('../../RedirectModulesInc.php');
if ($_openSIS['modules_search'] && $extra['force_search'])
    $_REQUEST['search_modfunc'] = '';

if (Preferences('SEARCH') != 'Y' && !$extra['force_search'])
    $_REQUEST['search_modfunc'] = 'list';
if ($extra['skip_search'] == 'Y')
    $_REQUEST['search_modfunc'] = 'list';

if ($_REQUEST['search_modfunc'] == 'search_fnc' || !$_REQUEST['search_modfunc']) {
    if ($_SESSION['student_id'] && User('PROFILE') == 'admin' && $_REQUEST['student_id'] == 'new') {
        unset($_SESSION['student_id']);
        //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
    }

    switch (User('PROFILE')) {
        case 'admin':
        case 'teacher':
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['smc']);
                unset($_SESSION['g']);
                unset($_SESSION['p']);
                unset($_SESSION['smn']);
                unset($_SESSION['sm']);
                unset($_SESSION['sma']);
                unset($_SESSION['smv']);
                unset($_SESSION['s']);
                unset($_SESSION['_search_all']);
            }

            $_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
            if (isset($_SESSION['stu_search']['sql']) && $search_from_grade != 'true') {
                unset($_SESSION['stu_search']);
            } else if ($search_from_grade == 'true') {
                $_SESSION['stu_search']['search_from_grade'] = 'true';
            }

            echo '<div class="row">';
            echo '<div class="col-md-12">';
            PopTable('header', 'Find a Student');
            unset($_SESSION['students_order']);
            // echo 'test';
            // echo  encode_url("Modules.php?="); 
            if ($extra['pdf'] != true)
                echo "<FORM name=search class=\"form-horizontal m-b-0\" id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM name=search class=\"form-horizontal m-b-0\" id=search action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";

            Search('general_info');
            if ($extra['search'])
                echo $extra['search'];
            Search('student_fields');




            # ---   Advanced Search Start ---------------------------------------------------------- #
          
            $qr = DBQuery("SELECT ethnicity_name FROM ethnicity where ethnicity_id>15");
$res = DBGet($qr);
$ethnic_option = array('White, Non-Hispanic' => 'White, Non-Hispanic', 'Black, Non-Hispanic' => 'Black, Non-Hispanic', 'Hispanic' => 'Hispanic', 'American Indian or Native Alaskan' => 'American Indian or Native Alaskan', 'Pacific Islander' => 'Pacific Islander', 'Asian' => 'Asian', 'Indian' => 'Indian', 'Middle Eastern' => 'Middle Eastern', 'African' => 'African', 'Mixed Race' => 'Mixed Race', 'White British' => 'White British', 'Asian' => 'Asian', 'Black' => 'Black', 'Chinese' => 'Chinese', 'Other' => 'Other');
foreach ($res as $v) {
    $ethnic_option[$v['ETHNICITY_NAME']] = $v['ETHNICITY_NAME'];
}
          $language_option = array('English' => 'English', 'Arabic' => 'Arabic', 'Bengali' => 'Bengali', 'Chinese' => 'Chinese', 'French' => 'French', 'German' => 'German', 'Haitian Creole' => 'Haitian Creole', 'Hindi' => 'Hindi', 'Italian' => 'Italian', 'Japanese' => 'Japanese', 'Korean' => 'Korean', 'Malay' => 'Malay', 'Polish' => 'Polish', 'Portuguese' => 'Portuguese', 'Russian' => 'Russian', 'Somali' => 'Somali', 'Spanish' => 'Spanish', 'Thai' => 'Thai', 'Turkish' => 'Turkish', 'Urdu' => 'Urdu', 'Vietnamese' => 'Vietnamese');
  
            echo '<div style="height:10px;"></div>';
            echo '<input type=hidden name=sql_save_session value=true />';


            echo '<div id="searchdiv" style="display:none;" class="well">';
            echo '<div><a href="javascript:void(0);" class="text-pink" onclick="hide_search_div();"><i class="icon-cancel-square"></i> Close Advanced Search</a></div>';
            echo '<br/>';
            
                        echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments </label><div class="col-lg-8"><input type=text name="mp_comment" size=30 placeholder="Comments" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
             echo '</div>'; //.row
////////////////////////extra search field start///////////////////////////
            echo '<h5 class="text-primary">General Information</h5>';     
             
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Middle Name </label><div class="col-lg-8"><input type=text name="middle_name" size=30 placeholder="Middle Name" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Common Name </label><div class="col-lg-8"><input type=text name="common_name" size=30 placeholder="Common Name" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Gender </label><div class="col-lg-8">'.SelectInput('', 'GENDER', '', array('Male' => 'Male', 'Female' => 'Female'), 'N/A', '') .'</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Ethnicity </label><div class="col-lg-8">' . SelectInput('', 'ETHNICITY', '', $ethnic_option, 'N/A', '') . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
                        
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Language </label><div class="col-lg-8">' . SelectInput('', 'LANGUAGE', '', $language_option, 'N/A', '') . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Email </label><div class="col-lg-8"><input type=text name="email" size=30 placeholder="Email" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Phone </label><div class="col-lg-8"><input type=text name="phone" size=30 placeholder="phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6

            echo '</div>'; //.row
            
            echo '<h5 class="text-primary">Access Information</h5>';
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Username </label><div class="col-lg-8"><input type=text name="username" size=30 placeholder="Username" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            ////////////////////////extra search field end///////////////////////////

            echo '<h5 class="text-primary">Birthday Search</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">From: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">To: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<h5 class="text-primary">DOB</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right"> </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_dob_birthdate', 'month_dob_birthdate', 'year_dob_birthdate', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<h5 class="text-primary">Estimated Grad. Date</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">From: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_est', 'month_from_est', 'year_from_est', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">To: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_est', 'month_to_est', 'year_to_est', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            
            echo '<h5 class="text-primary">Enrollment Start Date</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">From: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_st', 'month_from_st', 'year_from_st', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">To: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_st', 'month_to_st', 'year_to_st', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<h5 class="text-primary">Enrollment End Date</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">From: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_en', 'month_from_en', 'year_from_en', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">To: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_en', 'month_to_en', 'year_to_en', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            
            echo '<h5 class="text-primary">Home Address Information</h5>';     
             
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Address Line 1 </label><div class="col-lg-8"><input type=text name="home_address_1" size=30 placeholder="Address Line 1" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Address Line 2 </label><div class="col-lg-8"><input type=text name="home_address_2" size=30 placeholder="Address Line 2" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">City </label><div class="col-lg-8"><input type=text name="home_city" size=30 placeholder="City" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">State </label><div class="col-lg-8"><input type=text name="home_state" size=30 placeholder="State" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
                        
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Zip/Postal Code </label><div class="col-lg-8"><input type=text name="home_zip" size=30 placeholder="Zip" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Bus No </label><div class="col-lg-8"><input type=text name="home_busno" size=30 placeholder="Bus No." class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">School Bus Pick-up</label><div class="col-lg-8"><label class="checkbox-inline"><input class="styled" type=checkbox name="home_bus_pickup"></label></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">School Bus Drop-off </label><div class="col-lg-8"><label class="checkbox-inline"><input class="styled" type=checkbox name="home_bus_droppoff"></label></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            echo '<h5 class="text-primary">Mail Address Information</h5>';     
             
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Address Line 1 </label><div class="col-lg-8"><input type=text name="mail_address_1" size=30 placeholder="Address Line 1" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Address Line 2 </label><div class="col-lg-8"><input type=text name="mail_address_2" size=30 placeholder="Address Line 2" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">City </label><div class="col-lg-8"><input type=text name="mail_city" size=30 placeholder="City" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">State </label><div class="col-lg-8"><input type=text name="mail_state" size=30 placeholder="State" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
                        
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Zip/Postal Code </label><div class="col-lg-8"><input type=text name="mail_zip" size=30 placeholder="Zip" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            
            echo '<h5 class="text-primary">Primary Contact</h5>';     
             
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Relationship to student </label><div class="col-lg-8">'.SelectInput('', 'primary_realtionship', '', array('Father' => 'Father', 'Mother' => 'Mother','Step Mother' => 'Mother','Step Father' => 'Step Father','Step Mother' => 'Step Mother','Grandmother' => 'Grandmother','Grandfather' => 'Grandfather','Legal Guardian' => 'Legal Guardian','Other Family Member' => 'Other Family Member'), 'N/A', '').'</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">First Name </label><div class="col-lg-8"><input type=text name="primary_first_name" size=30 placeholder="First Name" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Second Name </label><div class="col-lg-8"><input type=text name="primary_second_name" size=30 placeholder="Second Name" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Home Phone </label><div class="col-lg-8"><input type=text name="primary_home_phone" size=30 placeholder="Home Phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Work Phone </label><div class="col-lg-8"><input type=text name="primary_work_phone" size=30 placeholder="Work Phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Cell/Mobile Phone </label><div class="col-lg-8"><input type=text name="primary_mobile_phone" size=30 placeholder="Cell/Mobile Phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
                        
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Email </label><div class="col-lg-8"><input type=text name="primary_email" size=30 placeholder="Email" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            
            echo '<h5 class="text-primary">Secondary Contact</h5>';     
             
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Relationship to student </label><div class="col-lg-8">'.SelectInput('', 'secondary_realtionship', '', array('Father' => 'Father', 'Mother' => 'Mother','Step Mother' => 'Mother','Step Father' => 'Step Father','Step Mother' => 'Step Mother','Grandmother' => 'Grandmother','Grandfather' => 'Grandfather','Legal Guardian' => 'Legal Guardian','Other Family Member' => 'Other Family Member'), 'N/A', '').'</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">First Name </label><div class="col-lg-8"><input type=text name="secondary_first_name" size=30 placeholder="First Name" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Second Name </label><div class="col-lg-8"><input type=text name="secondary_second_name" size=30 placeholder="Second Name" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Home Phone </label><div class="col-lg-8"><input type=text name="secondary_home_phone" size=30 placeholder="Home Phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Work Phone </label><div class="col-lg-8"><input type=text name="secondary_work_phone" size=30 placeholder="Work Phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Cell/Mobile Phone </label><div class="col-lg-8"><input type=text name="secondary_mobile_phone" size=30 placeholder="Cell/Mobile Phone" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
                        
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Email </label><div class="col-lg-8"><input type=text name="secondary_email" size=30 placeholder="Email" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            echo '<h5 class="text-primary">Goal and Progress</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Title </label><div class="col-lg-8"><input type=text name="goal_title" placeholder="Goal Title" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Description </label><div class="col-lg-8"><input type=text name="goal_description" placeholder="Goal Description" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Period </label><div class="col-lg-8"><input type=text name="progress_name" placeholder="Progress Period" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Assessment </label><div class="col-lg-8"><input type=text name="progress_description" size=30 placeholder="Progress Assessment" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('med_month', 'med_day', 'med_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Doctor\'s Note</label><div class="col-lg-8"><input type=text name="doctors_note_comments" placeholder="Doctor\'s Note" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Immunization</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Type</label><div class="col-lg-8"><input type=text name="type" placeholder="Immunization Type" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('imm_month', 'imm_day', 'imm_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments</label><div class="col-lg-8"><input type=text name="imm_comments" placeholder="Immunization Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical Alert</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('ma_month', 'ma_day', 'ma_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Alert</label><div class="col-lg-8"><input type=text name="med_alrt_title" placeholder="Medical Alert" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Nurse Visit</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('nv_month', 'nv_day', 'nv_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Reason</label><div class="col-lg-8"><input type=text name="reason" size=30 placeholder="Nurse Visit Reason" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Result</label><div class="col-lg-8"><input type=text name="result" size=30 placeholder="Nurse Visit Result" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments</label><div class="col-lg-8"><input type=text name="med_vist_comments" placeholder="Nurse Visit Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '</div>';



            # ---   Advanced Search End ----------------------------------------------------------- #


            echo '<div class="row">';
            echo '<div class="col-md-12">';
            if (User('PROFILE') == 'admin') {
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=address_group value=Y' . (Preferences('DEFAULT_FAMILIES') == 'Y' ? ' CHECKED' : '') . '> Group by Family</label>';
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '> Search All Schools</label>';
            }
            if ($_REQUEST['modname'] != 'students/StudentReenroll.php')
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=include_inactive value=Y> Include Inactive Students</label>';
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row

            echo '<hr/>';
            echo '<div class="text-right">';
            echo '<a id="addiv" href="javascript:void(0);" class="text-pink m-r-15" onclick="show_search_div();"><i class="icon-cog"></i> Advanced Search</a>';
            if ($extra['pdf'] != true)
                echo "<INPUT type=SUBMIT class=\"btn btn-primary m-r-10\" value='Submit' onclick='return formcheck_student_advnc_srch();formload_ajax(\"search\");'><INPUT type=RESET class=\"btn btn-default\" value='Reset'>&nbsp; &nbsp; ";
            else
                echo "<INPUT type=SUBMIT class=\"btn btn-primary m-r-10\" value='Submit' onclick='return formcheck_student_advnc_srch();'><INPUT type=RESET class=\"btn btn-default\" value='Reset'>&nbsp; &nbsp; ";

            echo '</div>';

            echo '</FORM>';
            // set focus to last name text box
            echo '<script type="text/javascript"><!--
				document.search.last.focus();
				--></script>';
            PopTable('footer');
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row
            break;

        case 'parent':
        case 'student':
            echo '<BR>';
            PopTable('header', 'Search');
            if ($extra['pdf'] != true)
                echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";
            echo '<TABLE border=0>';
            if ($extra['search'])
                echo $extra['search'];
            echo '<TR><TD colspan=2 align=center>';
            echo '<BR>';
            echo Buttons('Submit', 'Reset');
            echo '</TD></TR>';
            echo '</TABLE>';
            echo '</FORM>';
            PopTable('footer');
            break;
    }
}
else {
    
    
    if($_REQUEST['filter_form']=='Y' && $_REQUEST['filter_name']!='')
        {

            $filter_id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'filters'"));
            $filter_id= $filter_id[1]['AUTO_INCREMENT'];
            DBQuery('INSERT INTO filters (FILTER_NAME'.($_REQUEST['filter_all_school']=='Y'?'':',SCHOOL_ID').($_REQUEST['filter_public']=='Y'?'':',SHOW_TO').') VALUES (\''.singleQuoteReplace("","",$_REQUEST['filter_name']).'\''.($_REQUEST['filter_all_school']=='Y'?'':','.UserSchool()).($_REQUEST['filter_public']=='Y'?'':','.UserID()).')');


            $filters = array("last", "first", "stuid","altid","addr","grade","section","address_group","_search_all_schools","include_inactive","mp_comment","goal_title","goal_description","progress_name","progress_description","doctors_note_comments","type","imm_comments","med_alrt_title","reason","result","med_vist_comments");
            foreach($filters as $filter_columns)
            {
                if($_REQUEST[$filter_columns]!='')
                DBQuery('INSERT INTO filter_fields (FILTER_ID,FILTER_COLUMN,FILTER_VALUE) VALUES ('.$filter_id.',\''.$filter_columns.'\',\''.$_REQUEST[$filter_columns].'\')');
            }
            $_REQUEST['filter']=$filter_id;
        }
        if($_REQUEST['filter']!='')
        {
            $get_filters=DBGet(DBQuery('SELECT * FROM filter_fields WHERE FILTER_ID='.$_REQUEST['filter']));
            foreach($get_filters as $get_results)
            {
                $_REQUEST[$get_results['FILTER_COLUMN']]=$get_results['FILTER_VALUE'];
            }
        }
    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'students/Student.php';

    if ($_REQUEST['address_group']) {
        $extra['SELECT'] = $extra['SELECT'] . ',ssm.student_id AS CHILD';
        if (count($extra['functions']) > 0)
            $extra['functions']+=array('CHILD' => '_make_Parents');
        else
            $extra['functions'] = array('CHILD' => '_make_Parents');

        if (!($_REQUEST['expanded_view'] == 'true' || $_REQUEST['addr'] || $extra['addr'])) {

            $extra['FROM'] = ' INNER JOIN students_join_people sam ON (sam.STUDENT_ID=ssm.STUDENT_ID) ';

            $extra['ORDER_BY'] = 'FULL_NAME';
            $extra['DISTINCT'] = 'DISTINCT';
        }
    }
    $extra['SELECT'].=' ,ssm.SECTION_ID';
    if (count($extra['functions']) > 0)
        $extra['functions']+=array('SECTION_ID' => '_make_sections');
    else
        $extra['functions'] = array('SECTION_ID' => '_make_sections');


    if ($_REQUEST['section'] != '')
        $extra['WHERE'].=' AND ssm.SECTION_ID=' . $_REQUEST['section'];
    

    $students_RET = GetStuList($extra);
    if ($_REQUEST['modname'] == 'grades/HonorRoll.php') {
        $i = 1;
        foreach ($students_RET as $key => $stuRET) {
            if ($stuRET['HONOR_ROLL'] != '') {
                $stu[$i] = $stuRET;
                $i++;
            }
        }
        $students_RET = $stu;
    }
    if ($_REQUEST['address_group']) {
        
    }

    if ($extra['array_function'] && function_exists($extra['array_function']))
        $students_RET = $extra['array_function']($students_RET);

    $LO_columns = array('FULL_NAME' => 'Student', 'STUDENT_ID' => 'Student ID', 'ALT_ID' => 'Alternate ID', 'GRADE_ID' => 'Grade', 'SECTION_ID' => 'Section', 'PHONE' => 'Phone');
    $name_link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]";
    $name_link['FULL_NAME']['variables'] = array('student_id' => 'STUDENT_ID');
    if ($_REQUEST['_search_all_schools'])
        $name_link['FULL_NAME']['variables'] += array('school_id' => 'SCHOOL_ID');

    if (is_array($extra['link']))
        $link = $extra['link'] + $name_link;
    else
        $link = $name_link;
    if (is_array($extra['columns_before'])) {
        $columns = $extra['columns_before'] + $LO_columns;
        $LO_columns = $columns;
    }

    if (is_array($extra['columns_after']))
        $columns = $LO_columns + $extra['columns_after'];
    if (!$extra['columns_before'] && !$extra['columns_after'])
        $columns = $LO_columns;

    if (count($students_RET) > 1 || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt'] == false && count($students_RET) == 0) || ($extra['Redirect'] === false && count($students_RET) == 1)) {
        echo "<FORM name=search class=\"form-horizontal m-b-0\" id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
        echo '<input type=hidden name=filter_form value=Y />';
        echo '<div class="panel">';
        echo '<div class="panel-heading p-0 clearfix">';
        echo '<div class="collapse-icon"><ul class="icons-list"><li><a data-action="collapse" class=""></a></li></ul></div>';
        echo '<div><ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active"><a>Filter Student</a></li></ul></div>';
         
        echo '</div>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-bordered table-xxs">';
        echo '<tbody>';
        echo '<tr>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleLastName\');">Last Name</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleFirstName\');">First Name</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleStudentId\');">Student ID</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleAltId\');">Alt ID</th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleAddress\');">Address</th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleGrade\');">Grade</th>';
        echo '</tr>';
        echo '<tr>';
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['last']!='')
        echo '<td><div  id="toggleLastName_element"><input type="text" id="last" name="last" class="form-control p-t-0 p-b-0 input-xs" placeholder="Last Name" value="'.$_REQUEST['last'].'"/></div></td>';
        else
        echo '<td><div onclick="divToggle(\'#toggleLastName\');" id="toggleLastName">Any</div><div style="display:none;" id="toggleLastName_element" class="hide-element"><input type="text" name="last" id="last" class="form-control p-t-0 p-b-0 input-xs" placeholder="Last Name" /></div></td>';
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['first']!='')
        echo '<td><div id="toggleFirstName_element"><input type="text" id="first" name="first" class="form-control p-t-0 p-b-0 input-xs" placeholder="First Name" value="'.$_REQUEST['first'].'"/></div></td>';
        else
        echo '<td><div onclick="divToggle(\'#toggleFirstName\');" id="toggleFirstName">Any</div><div style="display:none;" id="toggleFirstName_element" class="hide-element"><input type="text" id="first" name="first" class="form-control p-t-0 p-b-0 input-xs" placeholder="First Name" /></div></td>';
        
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['stuid']!='')
        echo '<td><div id="toggleStudentId_element"><input type="text" id="stuid" name="stuid" class="form-control p-t-0 p-b-0 input-xs" placeholder="Student ID" value="'.$_REQUEST['stuid'].'"/></div></td>';
        else
        echo '<td><div onclick="divToggle(\'#toggleStudentId\');" id="toggleStudentId">Any</div><div style="display:none;" id="toggleStudentId_element" class="hide-element"><input type="text" id="stuid" name="stuid" class="form-control p-t-0 p-b-0 input-xs" placeholder="Student ID" /></div></td>';
       
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['altid']!='')
        echo '<td><div id="toggleAltId_element"><input type="text" id="altid" name="altid" class="form-control p-t-0 p-b-0 input-xs" placeholder="Alt ID" value="'.$_REQUEST['altid'].'"/></div></td>';
        else
        echo '<td><div onclick="divToggle(\'#toggleAltId\');" id="toggleAltId">Any</div><div style="display:none;" id="toggleAltId_element" class="hide-element"><input type="text" id="altid" name="altid" class="form-control p-t-0 p-b-0 input-xs" placeholder="Alt ID" /></div></td>';
        
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['addr']!='')
        echo '<td><div id="toggleAddress_element" class="hide-element"><input type="text" id="addr" name="addr" class="form-control p-t-0 p-b-0 input-xs" placeholder="Address" value="'.$_REQUEST['addr'].'"/></div></td>';    
        else
        echo '<td><div onclick="divToggle(\'#toggleAddress\');" id="toggleAddress">Any</div><div style="display:none;" id="toggleAddress_element" class="hide-element"><input type="text" id="addr" name="addr" class="form-control p-t-0 p-b-0 input-xs" placeholder="Address" /></div></td>';
       
        
        $list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['grade']!='')
        {
        echo '<td><div id="toggleGrade_element"><select id="grade" name=grade class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
        echo '<option value="'. $value['TITLE'].'" '.($value['TITLE']==$_REQUEST['grade']?'selected':'').'>'.$value['TITLE'].'</option>';
        echo '</select></div></td>';
        echo '</tr>';  
        }
        else
        {
        echo '<td><div onclick="divToggle(\'#toggleGrade\');" id="toggleGrade">Any</div><div style="display:none;" id="toggleGrade_element" class="hide-element"><select id="grade" name=grade class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
        echo '<option value="'. $value['TITLE'].'">'.$value['TITLE'].'</option>';
        echo '</select></div></td>';
        echo '</tr>';
        }
        echo '<tr>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleSection\');">Section</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleGrpByFamily\');">Group by Family</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleSearchAllSchool\');">Search All Schools</a></th>';
        echo '<th colspan="3"><a href="javascript:void(0);" onclick="divToggle(\'#toggleIncludeInactive\');">Include Inactive Students</a></th>';
        echo '</tr>';
        
        
        $list = DBGet(DBQuery("SELECT DISTINCT NAME,ID,SORT_ORDER FROM school_gradelevel_sections WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
        echo '<tr>';
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['section']!='')
        {
        echo '<td><div id="toggleSection_element">';
        echo '<select id="section" name=section class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
        echo '<option value="'.$value['ID'].'" '.($value['ID']==$_REQUEST['section']?'selected':'').'>'.$value['NAME'].'</option>';
        echo '</select></div></td>';
        }
        else
        {
        echo '<td><div onclick="divToggle(\'#toggleSection\');" id="toggleSection">Any</div><div style="display:none;" id="toggleSection_element" class="hide-element">';
        echo '<select id="section" name=section class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
        foreach ($list as $value)
        echo '<option value='.$value['ID'].'>'.$value['NAME'].'</option>';
        echo '</select></div></td>';
        }
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['address_group']!='')
        echo '<td><div id="toggleGrpByFamily_element"><div class="checkbox m-b-0"><label><input id="address_group" type="checkbox" name="address_group" value="Y" checked/></label></div></div></td>';
        else
        echo '<td><div onclick="divToggle(\'#toggleGrpByFamily\');" id="toggleGrpByFamily">No</div><div style="display:none;" id="toggleGrpByFamily_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="address_group" name="address_group" value="Y"/></label></div></div></td>';
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['_search_all_schools']!='')
        echo '<td><div id="toggleSearchAllSchool_element"><div class="checkbox m-b-0"><label><input type="checkbox" id="_search_all_schools" name="_search_all_schools" value="Y" checked/></label></div></div></td>';
        else    
        echo '<td><div onclick="divToggle(\'#toggleSearchAllSchool\');" id="toggleSearchAllSchool">No</div><div style="display:none;" id="toggleSearchAllSchool_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="_search_all_schools" name="_search_all_schools" value="Y"/></label></div></div></td>';
        
        if($_REQUEST['filter_form']=='Y' && $_REQUEST['include_inactive']!='')
        echo '<td colspan="3"><div id="toggleIncludeInactive_element"><div class="checkbox m-b-0"><label><input type="checkbox" id="include_inactive" name="include_inactive" value="Y" checked/></label></div></div></td>';
        else
        echo '<td colspan="3"><div onclick="divToggle(\'#toggleIncludeInactive\');" id="toggleIncludeInactive">No</div><div style="display:none;" id="toggleIncludeInactive_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="include_inactive" name="include_inactive" value="Y"/></label></div></div></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; //.table-responsive

            # ---   Advanced Filter Start ---------------------------------------------------------- #
            echo '<div style="height:10px;"></div>';
            echo '<input type=hidden name=sql_save_session value=true />';


            echo '<div id="searchdiv1" style="display:none;" class="well">';
            echo '<div><a href="javascript:void(0);" class="text-pink" onclick="hide_search_div1();"><i class="icon-cancel-square"></i> Close Advanced Filter</a></div>';
            echo '<br/>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['mp_comment']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments </label><div class="col-lg-8"><input type="text" id="mp_comment" name="mp_comment" class="form-control p-t-0 p-b-0 input-xs" placeholder="Comments" value="'.$_REQUEST['mp_comment'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments </label><div class="col-lg-8"><input type=text id="mp_comment" name="mp_comment" size=30 placeholder="Comments" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Birthday Search</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">From: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">To: </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<h5 class="text-primary">DOB</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right"> </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_dob_birthdate', 'month_dob_birthdate', 'year_dob_birthdate', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Goal and Progress</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['goal_title']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Title </label><div class="col-lg-8"><input type="text" id="goal_title" name="goal_title" class="form-control p-t-0 p-b-0 input-xs" placeholder="Goal Title" value="'.$_REQUEST['goal_title'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Title </label><div class="col-lg-8"><input type=text id="goal_title" name="goal_title" placeholder="Goal Title" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['goal_description']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Description </label><div class="col-lg-8"><input type="text" id="goal_description" name="goal_description" class="form-control p-t-0 p-b-0 input-xs" placeholder="Goal Description" value="'.$_REQUEST['goal_description'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Goal Description </label><div class="col-lg-8"><input type=text id="goal_description" name="goal_description" placeholder="Goal Description" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['progress_name']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Period </label><div class="col-lg-8"><input type="text" id="progress_name" name="progress_name" class="form-control p-t-0 p-b-0 input-xs" placeholder="Progress Period" value="'.$_REQUEST['progress_name'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Period </label><div class="col-lg-8"><input type=text id="progress_name" name="progress_name" placeholder="Progress Period" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['progress_description']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Assessment </label><div class="col-lg-8"><input type="text" id="progress_description" name="progress_description" class="form-control p-t-0 p-b-0 input-xs" placeholder="Progress Assessment" value="'.$_REQUEST['progress_description'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Progress Assessment </label><div class="col-lg-8"><input type=text id="progress_description" name="progress_description" size=30 placeholder="Progress Assessment" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['doctors_note_comments']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Doctor\'s Note </label><div class="col-lg-8"><input type="text" id="doctors_note_comments" name="doctors_note_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="Doctor\'s Note" value="'.$_REQUEST['doctors_note_comments'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Doctor\'s Note</label><div class="col-lg-8"><input type=text id="doctors_note_comments" name="doctors_note_comments" placeholder="Doctor\'s Note" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Immunization</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['type']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Type </label><div class="col-lg-8"><input type="text" id="type" name="type" class="form-control p-t-0 p-b-0 input-xs" placeholder="Immunization Type" value="'.$_REQUEST['type'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Type</label><div class="col-lg-8"><input type=text id="type" name="type" placeholder="Immunization Type" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['imm_comments']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments </label><div class="col-lg-8"><input type="text" id="imm_comments" name="imm_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="Immunization Comments" value="'.$_REQUEST['imm_comments'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments</label><div class="col-lg-8"><input type=text id="imm_comments" name="imm_comments" placeholder="Immunization Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Medical Alert</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['med_alrt_title']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Alert </label><div class="col-lg-8"><input type="text" id="med_alrt_title" name="med_alrt_title" class="form-control p-t-0 p-b-0 input-xs" placeholder="Medical Alert" value="'.$_REQUEST['med_alrt_title'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Alert</label><div class="col-lg-8"><input type=text id="med_alrt_title" name="med_alrt_title" placeholder="Medical Alert" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">Nurse Visit</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Date</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['reason']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Reason </label><div class="col-lg-8"><input type="text" id="reason" name="reason" class="form-control p-t-0 p-b-0 input-xs" placeholder="Nurse Visit Reason" value="'.$_REQUEST['reason'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Reason</label><div class="col-lg-8"><input type=text id="reason" name="reason" size=30 placeholder="Nurse Visit Reason" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['result']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Result </label><div class="col-lg-8"><input type="text" id="result" name="result" class="form-control p-t-0 p-b-0 input-xs" placeholder="Nurse Visit Result" value="'.$_REQUEST['result'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Result</label><div class="col-lg-8"><input type=text id="result" name="result" size=30 placeholder="Nurse Visit Result" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            if($_REQUEST['filter_form']=='Y' && $_REQUEST['med_vist_comments']!='')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments </label><div class="col-lg-8"><input type="text" id="med_vist_comments" name="med_vist_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="Nurse Visit Comments" value="'.$_REQUEST['med_vist_comments'].'"/></div></div>';
            else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">Comments</label><div class="col-lg-8"><input type=text id="med_vist_comments" name="med_vist_comments" placeholder="Nurse Visit Comments" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '</div>';



            # ---   Advanced Filter End ----------------------------------------------------------- #

        echo '<div class="panel-footer p-l-15 p-r-15">';
        
        echo '<div class="row">';
        echo '<div class="col-sm-6 col-md-6 col-lg-6">';
        echo '<input type="submit" class="btn btn-primary" value="Apply Filter" /> &nbsp; <input class="btn btn-default" value="Reset" type="RESET">';
        echo '<a id="addiv1" href="javascript:void(0);" class="text-pink" onclick="show_search_div1();">  &nbsp;<i class="icon-cog"></i> Advanced Filter</a>';
        echo '</div>';
        echo '<div class="col-sm-6 col-md-6 col-lg-6 text-lg-right text-md-right text-sm-right">';
        echo '<a HREF=javascript:void(0) data-toggle="modal" data-target="#modal_default_filter" class="btn btn-primary display-inline-block" onClick="setFilterValues();">Save Filter</a>';
        $filters=DBGet(DBQuery('SELECT * FROM filters WHERE SCHOOL_ID IN ('.UserSchool().',0) AND SHOW_TO IN ('. UserID().',0)'));
        echo '<div class="m-l-10 display-inline-block"><select name="filter" class="form-control form-control-bordered width-auto"  onchange="this.form.submit();"><option value="">-- Load Filter --</option>';
        foreach ($filters as $value)
        echo '<option value='.$value['FILTER_ID'].' '.($_REQUEST['filter']==$value['FILTER_ID']?'SELECTED':'').' >'.$value['FILTER_NAME'].'</option>';
        echo '</select></div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; //.panel-footer
        echo '</div>'; //.panel
        echo '</form>';
        
        
        
        //////////////Modal For Filter Save////////////////////
        echo '<div id="modal_default_filter" class="modal fade">';
        echo '<div class="modal-dialog modal-sm">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<button type="button" class="close" data-dismiss="modal">×</button>';
        echo '<h5 class="modal-title">Save Current Filter</h5>';
        echo '</div>';
 
        echo "<form onSubmit='return validate_filter();' class='form-horizontal m-b-0' action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
//        echo '<form class="form-horizontal m-b-0"  method="post" action="Modules.php?modname='.$_REQUEST[modname].'&modfunc='.$_REQUEST[modfunc].'&search_modfunc=list&next_modname='.$_REQUEST[next_modname]. $extra['action'].">';
        echo '<div class="modal-body">';
        echo '<div id="conf_div"></div>';
        
        echo '<div class="form-group">';        
        echo '<label class="control-label text-right col-lg-4">Filter Name</label>';
        echo '<div class="col-lg-8">';
        echo '<input type="text" id="filter_name" name="filter_name" size="30" placeholder="Filter Name" class="form-control">';
        echo '<div id="error_modal_filter"></div></div>'; //.col-lg-8
        echo '</div>'; //.form-group
        
        echo  '<input type="hidden" id="last_hidden" name="last"/>';
        echo  '<input type="hidden" id="first_hidden" name="first"/>';
        echo  '<input type="hidden" id="stuid_hidden" name="stuid"/>';
        echo  '<input type="hidden" id="altid_hidden" name="altid"/>';
        echo  '<input type="hidden" id="addr_hidden" name="addr"/>';
        echo  '<input type="hidden" id="grade_hidden" name="grade"/>';
        echo  '<input type="hidden" id="section_hidden" name="section"/>';
        
        echo '<div id="address_group_hidden"></div>';
        echo '<div id="_search_all_schools_hidden"></div>';
        echo '<div id="include_inactive_hidden"></div>';

        echo  '<input type="hidden" id="mp_comment_hidden" name="mp_comment"/>';
        echo  '<input type="hidden" id="goal_title_hidden" name="goal_title"/>';
        echo  '<input type="hidden" id="goal_description_hidden" name="goal_description"/>';
        echo  '<input type="hidden" id="progress_name_hidden" name="progress_name"/>';
        echo  '<input type="hidden" id="progress_description_hidden" name="progress_description"/>';
        echo  '<input type="hidden" id="doctors_note_comments_hidden" name="doctors_note_comments"/>';
        echo  '<input type="hidden" id="type_hidden" name="type"/>';
        echo  '<input type="hidden" id="imm_comments_hidden" name="imm_comments"/>';
        echo  '<input type="hidden" id="med_alrt_title_hidden" name="med_alrt_title"/>';
        echo  '<input type="hidden" id="reason_hidden" name="reason"/>';
        echo  '<input type="hidden" id="result_hidden" name="result"/>';
        echo  '<input type="hidden" id="med_vist_comments_hidden" name="med_vist_comments"/>';
        
        echo '<input type="hidden" name="filter_form" value="Y" />';
        
        echo '<div class="form-group">';        
        echo '<label class="control-label text-right col-lg-4">Make Public</label>';
        echo '<div class="col-lg-8">';
        echo '<div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_public" value="Y"><span></span></label></div>';
        echo '</div>'; //.col-lg-8
        echo '</div>'; //.form-group
        
        echo '<div class="form-group">';        
        echo '<label class="control-label text-right col-lg-4">All School</label>';
        echo '<div class="col-lg-8">';
        echo '<div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_all_school" value="Y"><span></span></label></div>';
        echo '</div>'; //.col-lg-8
        echo '</div>'; //.form-group
        
        echo '</div>'; //.modal-body
        echo '<div class="modal-footer text-center">';
        echo '<input type="submit" class="btn btn-primary display-inline-block" value="Save">';
        echo '</div>'; //.modal-footer
        echo '</form>';
        
        echo '</div>'; //.modal-content
        echo '</div>'; //.modal-dialog
        echo '</div>'; //.modal
        ///////////////////////////////////////////////////////////////////
        echo '<div class="panel panel-default">';
        $tmp_REQUEST = $_REQUEST;
        unset($tmp_REQUEST['expanded_view']);
        if ($_REQUEST['expanded_view'] != 'true' && !UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true><i class=\"icon-square-down-right\"></i> Expanded View</A>", $extra['header_right']);
            DrawHeader(str_replace('', '', substr($_openSIS['SearchTerms'], 0, -4)));
        } elseif (!UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false><i class=\"icon-square-up-left\"></i> Original View</A>", $extra['header_right']);
            DrawHeader(str_replace('', '', substr($_openSIS['Search'], 0, -4)));
        }
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }
        if (!$extra['singular'] || !$extra['plural'])
            $extra['singular'] = 'Student';
        $extra['plural'] = 'Students';

        foreach ($students_RET as $si => $sd)
            $_SESSION['students_order'][$si] = $sd['STUDENT_ID'];


        echo "<div id='students' class=\"table-responsive\">";

        ListOutput($students_RET, $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options']);
        echo "</div>"; //.table-responsive
        echo "</div>"; //.panel.panel-default
    }
    elseif (count($students_RET) == 1) {
        if (count($link['FULL_NAME']['variables'])) {
            foreach ($link['FULL_NAME']['variables'] as $var => $val)
                $_REQUEST[$var] = $students_RET['1'][$val];
        }
        if (!is_array($students_RET[1]['STUDENT_ID'])) {
            $_SESSION['student_id'] = $students_RET[1]['STUDENT_ID'];



            if (User('PROFILE') == 'admin')
                $_SESSION['UserSchool'] = $students_RET[1]['LIST_SCHOOL_ID'];
            if (User('PROFILE') == 'teacher')
                $_SESSION['UserSchool'] = $students_RET[1]['SCHOOL_ID'];


            //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            unset($_REQUEST['search_modfunc']);
        }
        if ($_REQUEST['modname'] != $_REQUEST['next_modname']) {
            $modname = $_REQUEST['next_modname'];
            if (strpos($modname, '?'))
                $modname = substr($_REQUEST['next_modname'], 0, strpos($_REQUEST['next_modname'], '?'));
            if (strpos($modname, '&'))
                $modname = substr($_REQUEST['next_modname'], 0, strpos($_REQUEST['next_modname'], '&'));
            if ($_REQUEST['modname'])
                $_REQUEST['modname'] = $modname;
            include('modules/' . $modname);
        }
    } else
        BackPrompt('No Students were found.');
}

function _make_sections($value) {
    if ($value != '') {
        $get = DBGet(DBQuery('SELECT NAME FROM school_gradelevel_sections WHERE ID=' . $value));
        return $get[1]['NAME'];
    } else
        return '';
}

?>