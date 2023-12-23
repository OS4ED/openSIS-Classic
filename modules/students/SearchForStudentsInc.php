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
            PopTable('header',  _findAStudent);
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




            # --------   Advanced Search Start   -------- #

            $ethnicity = DBGet(DBQuery('SELECT * FROM ethnicity'));

            $ethnic_option = array($ethnicity[1]['ETHNICITY_ID'] => $ethnicity[1]['ETHNICITY_NAME'], $ethnicity[2]['ETHNICITY_ID'] => $ethnicity[2]['ETHNICITY_NAME'], $ethnicity[3]['ETHNICITY_ID'] => $ethnicity[3]['ETHNICITY_NAME'], $ethnicity[4]['ETHNICITY_ID'] => $ethnicity[4]['ETHNICITY_NAME'], $ethnicity[5]['ETHNICITY_ID'] => $ethnicity[5]['ETHNICITY_NAME'], $ethnicity[6]['ETHNICITY_ID'] => $ethnicity[6]['ETHNICITY_NAME'], $ethnicity[7]['ETHNICITY_ID'] => $ethnicity[7]['ETHNICITY_NAME'], $ethnicity[8]['ETHNICITY_ID'] => $ethnicity[8]['ETHNICITY_NAME'], $ethnicity[9]['ETHNICITY_ID'] => $ethnicity[9]['ETHNICITY_NAME'], $ethnicity[10]['ETHNICITY_ID'] => $ethnicity[10]['ETHNICITY_NAME'], $ethnicity[11]['ETHNICITY_ID'] => $ethnicity[11]['ETHNICITY_NAME']);

            $language = DBGet(DBQuery('SELECT * FROM language'));

            $language_option = array($language[1]['LANGUAGE_ID'] => $language[1]['LANGUAGE_NAME'], $language[2]['LANGUAGE_ID'] => $language[2]['LANGUAGE_NAME'], $language[3]['LANGUAGE_ID'] => $language[3]['LANGUAGE_NAME'], $language[4]['LANGUAGE_ID'] => $language[4]['LANGUAGE_NAME'], $language[5]['LANGUAGE_ID'] => $language[5]['LANGUAGE_NAME'], $language[6]['LANGUAGE_ID'] => $language[6]['LANGUAGE_NAME'], $language[7]['LANGUAGE_ID'] => $language[7]['LANGUAGE_NAME'], $language[8]['LANGUAGE_ID'] => $language[8]['LANGUAGE_NAME'], $language[9]['LANGUAGE_ID'] => $language[9]['LANGUAGE_NAME'], $language[10]['LANGUAGE_ID'] => $language[10]['LANGUAGE_NAME'], $language[11]['LANGUAGE_ID'] => $language[11]['LANGUAGE_NAME'], $language[12]['LANGUAGE_ID'] => $language[12]['LANGUAGE_NAME'], $language[13]['LANGUAGE_ID'] => $language[13]['LANGUAGE_NAME'], $language[14]['LANGUAGE_ID'] => $language[14]['LANGUAGE_NAME'], $language[15]['LANGUAGE_ID'] => $language[15]['LANGUAGE_NAME'], $language[16]['LANGUAGE_ID'] => $language[16]['LANGUAGE_NAME'], $language[17]['LANGUAGE_ID'] => $language[17]['LANGUAGE_NAME'], $language[18]['LANGUAGE_ID'] => $language[18]['LANGUAGE_NAME'], $language[19]['LANGUAGE_ID'] => $language[19]['LANGUAGE_NAME'], $language[20]['LANGUAGE_ID'] => $language[20]['LANGUAGE_NAME']);



            echo '<div style="height:10px;"></div>';
            echo '<input type=hidden name=sql_save_session value=true />';


            echo '<div id="searchdiv" style="display:none;" class="well">';
            echo '<div><a href="javascript:void(0);" class="text-pink" onclick="hide_search_div();"><i class="icon-cancel-square"></i> ' . _closeAdvancedSearch . '</a></div>';
            echo '<br/>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . ' </label><div class="col-lg-8"><input type=text name="mp_comment" size=30 placeholder="' . _comments . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            Search('student_advanced_fields');

            //////////////////////// extra search field start ///////////////////////////

            echo '<h5 class="text-primary">' . _generalInformation . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _middleName . ' </label><div class="col-lg-8"><input type=text name="middle_name" size=30 placeholder="' . _middleName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _commonName . ' </label><div class="col-lg-8"><input type=text name="common_name" size=30 placeholder="' . _commonName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _gender . ' </label><div class="col-lg-8">' . SelectInput('', 'GENDER', '', array('Male' => 'Male', 'Female' => 'Female'), 'N/A', '') . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _ethnicity . ' </label><div class="col-lg-8">' . SelectInput('', 'ETHNICITY_ID', '', $ethnic_option, 'N/A', '') . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _language . ' </label><div class="col-lg-8">' . SelectInput('', 'LANGUAGE_ID', '', $language_option, 'N/A', '') . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _email . ' </label><div class="col-lg-8"><input type=text name="email" size=30 placeholder="' . _email . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _phone . ' </label><div class="col-lg-8"><input type=text name="phone" size=30 placeholder="' . _phone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6

            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _accessInformation . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _username . ' </label><div class="col-lg-8"><input type=text name="username" size=30 placeholder="' . _username . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            //////////////////////// extra search field end ///////////////////////////

            echo '<h5 class="text-primary">' . _birthdaySearch . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _from . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _to . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _dob . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right"> </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_dob_birthdate', 'month_dob_birthdate', 'year_dob_birthdate', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';

            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _estimatedGradDate . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _from . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_est', 'month_from_est', 'year_from_est', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _to . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_est', 'month_to_est', 'year_to_est', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row            



            echo '<h5 class="text-primary">' . _enrollmentStartDate . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _from . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_st', 'month_from_st', 'year_from_st', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _to . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_st', 'month_to_st', 'year_to_st', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _enrollmentEndDate . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _from . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_from_en', 'month_from_en', 'year_from_en', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _to . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_to_en', 'month_to_en', 'year_to_en', 'Y', 'Y', '') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row



            echo '<h5 class="text-primary">' . _homeAddressInformation . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _addressLine_1 . ' </label><div class="col-lg-8"><input type=text name="home_address_1" size=30 placeholder="' . _addressLine_1 . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _addressLine_2 . ' </label><div class="col-lg-8"><input type=text name="home_address_2" size=30 placeholder="' . _addressLine_2 . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _city . ' </label><div class="col-lg-8"><input type=text name="home_city" size=30 placeholder="' . _city . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _state . ' </label><div class="col-lg-8"><input type=text name="home_state" size=30 placeholder="' . _state . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _zipPostalCode . ' </label><div class="col-lg-8"><input type=text name="home_zip" size=30 placeholder="' . _zipPostalCode . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _busNo . ' </label><div class="col-lg-8"><input type=text name="home_busno" size=30 placeholder="' . _busNo . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _schoolBusPickUp . '</label><div class="col-lg-8"><label class="checkbox-inline"><input class="styled" type=checkbox name="home_bus_pickup"></label></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _schoolBusDropOff . ' </label><div class="col-lg-8"><label class="checkbox-inline"><input class="styled" type=checkbox name="home_bus_droppoff"></label></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<h5 class="text-primary">' . _mailAddressInformation . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _addressLine_1 . ' </label><div class="col-lg-8"><input type=text name="mail_address_1" size=30 placeholder="' . _addressLine_1 . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _addressLine_2 . ' </label><div class="col-lg-8"><input type=text name="mail_address_2" size=30 placeholder="' . _addressLine_2 . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _city . ' </label><div class="col-lg-8"><input type=text name="mail_city" size=30 placeholder="' . _city . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _state . ' </label><div class="col-lg-8"><input type=text name="mail_state" size=30 placeholder="' . _state . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _zipPostalCode . ' </label><div class="col-lg-8"><input type=text name="mail_zip" size=30 placeholder="' . _zipPostalCode . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row



            echo '<h5 class="text-primary">' . _primaryContact . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _relationshipToStudent . ' </label><div class="col-lg-8">' . SelectInput(
                '',
                'primary_realtionship',
                '',
                array(
                    'Father' => _father,
                    'Mother' => _mother,
                    'Step Mother' => _mother,
                    'Step Father' => _stepFather,
                    'Step Mother' => _stepMother,
                    'Grandmother' => _grandmother,
                    'Grandfather' => _grandfather,
                    'Legal Guardian' => _legalGuardian,
                    'Other Family Member' => _otherFamilyMember,
                ),
                'N/A',
                ''
            ) . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _firstName . ' </label><div class="col-lg-8"><input type=text name="primary_first_name" size=30 placeholder="' . _firstName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _secondName . ' </label><div class="col-lg-8"><input type=text name="primary_second_name" size=30 placeholder="' . _secondName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _homePhone . ' </label><div class="col-lg-8"><input type=text name="primary_home_phone" size=30 placeholder="' . _homePhone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _workPhone . ' </label><div class="col-lg-8"><input type=text name="primary_work_phone" size=30 placeholder="' . _workPhone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _cellMobilePhone . ' </label><div class="col-lg-8"><input type=text name="primary_mobile_phone" size=30 placeholder="' . _cellMobilePhone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _email . ' </label><div class="col-lg-8"><input type=text name="primary_email" size=30 placeholder="' . _email . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row



            echo '<h5 class="text-primary">' . _secondaryContact . '</h5>';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _relationshipToStudent . ' </label><div class="col-lg-8">' . SelectInput(
                '',
                'secondary_realtionship',
                '',
                array(
                    'Father' => _father,
                    'Mother' => _mother,
                    'Step Mother' => _mother,
                    'Step Father' => _stepFather,
                    'Step Mother' => _stepMother,
                    'Grandmother' => _grandmother,
                    'Grandfather' => _grandfather,
                    'Legal Guardian' => _legalGuardian,
                    'Other Family Member' => _otherFamilyMember,
                ),
                'N/A',
                ''
            ) . '</div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _firstName . ' </label><div class="col-lg-8"><input type=text name="secondary_first_name" size=30 placeholder="' . _firstName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _secondName . ' </label><div class="col-lg-8"><input type=text name="secondary_second_name" size=30 placeholder="' . _secondName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _homePhone . ' </label><div class="col-lg-8"><input type=text name="secondary_home_phone" size=30 placeholder="' . _homePhone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _workPhone . ' </label><div class="col-lg-8"><input type=text name="secondary_work_phone" size=30 placeholder="' . _workPhone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _cellMobilePhone . ' </label><div class="col-lg-8"><input type=text name="secondary_mobile_phone" size=30 placeholder="' . _cellMobilePhone . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _email . ' </label><div class="col-lg-8"><input type=text name="secondary_email" size=30 placeholder="' . _email . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<h5 class="text-primary">' . _goalAndProgress . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _goalTitle . ' </label><div class="col-lg-8"><input type=text name="goal_title" placeholder="' . _goalTitle . '" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _goalDescription . ' </label><div class="col-lg-8"><input type=text name="goal_description" placeholder="' . _goalDescription . '" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _progressPeriod . ' </label><div class="col-lg-8"><input type=text name="progress_name" placeholder="' . _progressPeriod . '" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _progressAssessment . ' </label><div class="col-lg-8"><input type=text name="progress_description" size=30 placeholder="' . _progressAssessment . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _medical . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _doctorSNote . '</label><div class="col-lg-8"><input type=text name="doctors_note_comments" placeholder="' . _doctorSNote . '" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _immunization . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _type . '</label><div class="col-lg-8"><input type=text name="type" placeholder="' . _type . '" size=30 class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . '</label><div class="col-lg-8"><input type=text name="imm_comments" placeholder="' . _comments . '" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _medicalAlert . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _alert . '</label><div class="col-lg-8"><input type=text name="med_alrt_title" placeholder="' . _alert . '" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<h5 class="text-primary">' . _nurseVisit . '</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _reason . '</label><div class="col-lg-8"><input type=text name="reason" size=30 placeholder="' . _reason . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _result . '</label><div class="col-lg-8"><input type=text name="result" size=30 placeholder="' . _result . '" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . '</label><div class="col-lg-8"><input type=text name="med_vist_comments" placeholder="' . _comments . '" size=30 class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '</div>';



            # ---   Advanced Search End ----------------------------------------------------------- #


            echo '<div class="row">';
            echo '<div class="col-md-12">';
            if (User('PROFILE') == 'admin') {
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=address_group value=Y' . (Preferences('DEFAULT_FAMILIES') == 'Y' ? ' CHECKED' : '') . '> ' . _groupByFamily . '</label>';
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '> ' . _searchAllSchools . '</label>';
            }
            if ($_REQUEST['modname'] != 'students/StudentReenroll.php')
                echo '<label class="checkbox-inline"><INPUT class="styled" type=checkbox name=include_inactive value=Y> ' . _includeInactiveStudents . '</label>';
            echo '</div>'; //.col-md-12
            echo '</div>'; //.row

            echo '<hr/>';
            echo '<div class="text-right">';
            echo '<a id="advancedSearchDivForStudents" href="javascript:void(0);" class="text-pink m-r-15" onclick="show_search_div();"><i class="icon-cog"></i> ' . _advancedSearch . '</a>';
            if ($extra['pdf'] != true)
                echo "<INPUT id=\"searchStuBtn\" type=SUBMIT class=\"btn btn-primary m-r-10\" value='" . _submit . "' onclick='return formcheck_student_advnc_srch(this);formload_ajax(\"search\");'><INPUT type=RESET class=\"btn btn-default\" value='" . _reset . "'>&nbsp; &nbsp; ";
            else
                echo "<INPUT id=\"searchStuBtn\" type=SUBMIT class=\"btn btn-primary m-r-10\" value='" . _submit . "' onclick='return formcheck_student_advnc_srch(this);'><INPUT type=RESET class=\"btn btn-default\" value='" . _reset . "'>&nbsp; &nbsp; ";

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
            PopTable('header',  _search);
            if ($extra['pdf'] != true)
                echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
            else
                echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST target=_blank>";
            echo '<TABLE border=0>';
            if ($extra['search'])
                echo $extra['search'];
            echo '<TR><TD colspan=2 align=center>';
            echo '<BR>';
            echo Buttons(_submit, _reset);
            echo '</TD></TR>';
            echo '</TABLE>';
            echo '</FORM>';
            PopTable('footer');
            break;
    }
} else {


    if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['filter_name'] != '') {
        // $filter_id = DBGet(DBQuery("SHOW TABLE STATUS LIKE 'filters'"));
        // $filter_id = $filter_id[1]['AUTO_INCREMENT'];

        DBQuery('INSERT INTO filters (FILTER_NAME' . ($_REQUEST['filter_all_school'] == 'Y' ? '' : ',SCHOOL_ID') . ($_REQUEST['filter_public'] == 'Y' ? '' : ',SHOW_TO') . ') VALUES (\'' . singleQuoteReplace("", "", $_REQUEST['filter_name']) . '\'' . ($_REQUEST['filter_all_school'] == 'Y' ? '' : ',' . UserSchool()) . ($_REQUEST['filter_public'] == 'Y' ? '' : ',' . UserID()) . ')');
        // $filter_id = mysqli_insert_id($connection);

        $filter_id = DBGet(DBQuery("SELECT MAX(FILTER_ID) AS FILTER_ID FROM filters"))[1]['FILTER_ID'];

        $filters = array("last", "first", "stuid", "altid", "addr", "grade", "section", "address_group", "GENDER", "ETHNICITY_ID", "LANGUAGE_ID", "age_from", "age_to", "_search_all_schools", "include_inactive", "mp_comment", "goal_title", "goal_description", "progress_name", "progress_description", "doctors_note_comments", "type", "imm_comments", "med_alrt_title", "reason", "result", "med_vist_comments");

        $cust_filters = array();

        if (isset($_REQUEST['each_custom_fields_ids']) && $_REQUEST['each_custom_fields_ids'] != '') {
            $res_cfs = explode(", ", $_REQUEST['each_custom_fields_ids']);

            foreach ($res_cfs as $one_cf) {
                array_push($cust_filters, $one_cf);
            }
        }

        // echo "<pre>";print_r($_REQUEST);echo "</pre>";
        // die;

        foreach ($filters as $filter_columns) {
            if ($_REQUEST[$filter_columns] != '')
                DBQuery('INSERT INTO filter_fields (FILTER_ID,FILTER_COLUMN,FILTER_VALUE) VALUES (' . $filter_id . ',\'' . $filter_columns . '\',\'' . $_REQUEST[$filter_columns] . '\')');
        }

        if (!empty($cust_filters)) {
            foreach ($cust_filters as $cust_filters_columns) {
                if (isset($_REQUEST['cust']) && $_REQUEST['cust'][$cust_filters_columns] != '') {
                    // $cf_column = 'cust['.$cust_filters_columns.']';

                    DBQuery('INSERT INTO filter_fields (FILTER_ID,FILTER_COLUMN,FILTER_VALUE) VALUES (' . $filter_id . ',\'' . $cust_filters_columns . '\',\'' . $_REQUEST['cust'][$cust_filters_columns] . '\')');
                }
            }
        }

        $_REQUEST['filter'] = $filter_id;
    }

    // echo "<pre>";print_r($_REQUEST);echo "</pre>";

    if ($_REQUEST['filter'] != '') {
        $filters = array("last", "first", "stuid", "altid", "addr", "grade", "section", "address_group", "GENDER", "ETHNICITY_ID", "LANGUAGE_ID", "age_from", "age_to", "_search_all_schools", "include_inactive", "mp_comment", "goal_title", "goal_description", "progress_name", "progress_description", "doctors_note_comments", "type", "imm_comments", "med_alrt_title", "reason", "result", "med_vist_comments");

        foreach ($filters as $one_f) {
            $_REQUEST[$one_f] = '';
        }

        if (isset($_REQUEST['cust']) && !empty($_REQUEST['cust'])) {
            foreach ($_REQUEST['cust'] as $one_cfk => $one_cfv) {
                $_REQUEST['cust'][$one_cfk] = '';
            }
        }

        $get_filters = DBGet(DBQuery('SELECT * FROM filter_fields WHERE FILTER_ID=' . $_REQUEST['filter']));
        foreach ($get_filters as $get_results) {
            $_REQUEST[$get_results['FILTER_COLUMN']] = $get_results['FILTER_VALUE'];

            if (strpos($get_results['FILTER_COLUMN'], 'CUSTOM') !== false) {
                $_REQUEST['cust'][$get_results['FILTER_COLUMN']] = $get_results['FILTER_VALUE'];
            }
        }
    }

    // echo "<pre>";print_r($_REQUEST);echo "</pre>";

    if (!$_REQUEST['next_modname'])
        $_REQUEST['next_modname'] = 'students/Student.php';

    if ($_REQUEST['address_group']) {
        $extra['SELECT'] = $extra['SELECT'] . ',ssm.student_id AS CHILD';
        if (is_countable($extra['functions']) && !empty($extra['functions']) > 0)
            $extra['functions'] += array('CHILD' => '_make_Parents');
        else
            $extra['functions'] = array('CHILD' => '_make_Parents');

        if (!($_REQUEST['expanded_view'] == 'true' || $_REQUEST['addr'] || $extra['addr'])) {

            $extra['FROM'] = ' INNER JOIN students_join_people sam ON (sam.STUDENT_ID=ssm.STUDENT_ID) ';

            $extra['ORDER_BY'] = 'FULL_NAME';
            $extra['DISTINCT'] = 'DISTINCT';
        }
    }
    $extra['SELECT'] .= ' ,ssm.SECTION_ID';
    if (is_countable($extra['functions']) && count($extra['functions']) > 0)
        $extra['functions'] += array('SECTION_ID' => '_make_sections');
    else
        $extra['functions'] = array('SECTION_ID' => '_make_sections');


    if ($_REQUEST['section'] != '')
        $extra['WHERE'] .= ' AND ssm.SECTION_ID=' . $_REQUEST['section'];


    if (isset($_REQUEST['LO_sort']) && $_REQUEST['LO_sort'] != '' && $_REQUEST['LO_sort'] != NULL && isset($_REQUEST['LO_direction'])) {
        $extra['ORDER_BY'] = $_REQUEST['LO_sort'];

        if ($_REQUEST['LO_direction'] == '1') {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'] . ' ASC';
        }
        if ($_REQUEST['LO_direction'] == '-1') {
            $extra['ORDER_BY'] = $_REQUEST['LO_sort'] . ' DESC';
        }
    }

    # Set pagination params
    keepRequestParams($_REQUEST);
    keepExtraParams($extra);

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

    $LO_columns = array(
        'FULL_NAME' => _student,
        'STUDENT_ID' => _studentId,
        'ALT_ID' => _alternateId,
        'GRADE_ID' => _grade,
        'SECTION_ID' => _section,
        'PHONE' => _phone,
    );
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
        // Form for Filter Students Start
        echo "<FORM name=search class=\"form-horizontal m-b-0\" id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
        echo '<input type=hidden name=filter_form value=Y />';
        echo '<div class="panel">';
        echo '<div class="panel-heading p-0 clearfix">';
        echo '<div class="collapse-icon"><ul class="icons-list"><li><a data-action="collapse" class=""></a></li></ul></div>';
        echo '<div><ul class="nav nav-tabs nav-tabs-bottom no-margin-bottom"><li class="active"><a>' . _filterStudent . '</a></li></ul></div>';

        echo '</div>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-bordered table-xxs">';
        echo '<tbody>';
        echo '<tr>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleLastName\');">' . _lastName . '</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleFirstName\');">' . _firstName . '</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleStudentId\');">' . _studentId . '</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleAltId\');">' . _altId . '</th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleAddress\');">' . _address . '</th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleGrade\');">' . _grade . '</th>';
        echo '</tr>';
        echo '<tr>';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['last'] != '')
            echo '<td><div  id="toggleLastName_element"><input type="text" id="last" name="last" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _lastName . '" value="' . $_REQUEST['last'] . '"/></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleLastName\');" id="toggleLastName">' . _any . '</div><div style="display:none;" id="toggleLastName_element" class="hide-element"><input type="text" name="last" id="last" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _lastName . '" /></div></td>';

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['first'] != '')
            echo '<td><div id="toggleFirstName_element"><input type="text" id="first" name="first" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _firstName . '" value="' . $_REQUEST['first'] . '"/></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleFirstName\');" id="toggleFirstName">' . _any . '</div><div style="display:none;" id="toggleFirstName_element" class="hide-element"><input type="text" id="first" name="first" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _firstName . '" /></div></td>';


        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['stuid'] != '')
            echo '<td><div id="toggleStudentId_element"><input type="text" id="stuid" name="stuid" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _studentId . '" value="' . $_REQUEST['stuid'] . '"/></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleStudentId\');" id="toggleStudentId">' . _any . '</div><div style="display:none;" id="toggleStudentId_element" class="hide-element"><input type="text" id="stuid" name="stuid" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _studentId . '" /></div></td>';

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['altid'] != '')
            echo '<td><div id="toggleAltId_element"><input type="text" id="altid" name="altid" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _altId . '" value="' . $_REQUEST['altid'] . '"/></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleAltId\');" id="toggleAltId">' . _any . '</div><div style="display:none;" id="toggleAltId_element" class="hide-element"><input type="text" id="altid" name="altid" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _altId . '" /></div></td>';


        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['addr'] != '')
            echo '<td><div id="toggleAddress_element" class="hide-element"><input type="text" id="addr" name="addr" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _address . '" value="' . $_REQUEST['addr'] . '"/></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleAddress\');" id="toggleAddress">' . _any . '</div><div style="display:none;" id="toggleAddress_element" class="hide-element"><input type="text" id="addr" name="addr" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _address . '" /></div></td>';


        $list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['grade'] != '') {
            echo '<td><div id="toggleGrade_element"><select id="grade" name=grade class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
            foreach ($list as $value)
                echo '<option value="' . $value['ID'] . '" ' . ($value['ID'] == $_REQUEST['grade'] ? 'selected' : '') . '>' . $value['TITLE'] . '</option>';
            echo '</select></div></td>';
            echo '</tr>';
        } else {
            echo '<td><div onclick="divToggle(\'#toggleGrade\');" id="toggleGrade">' . _any . '</div><div style="display:none;" id="toggleGrade_element" class="hide-element"><select id="grade" name=grade class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
            foreach ($list as $value)
                echo '<option value="' . $value['ID'] . '">' . $value['TITLE'] . '</option>';
            echo '</select></div></td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleSection\');">' . _section . '</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleGrpByFamily\');">' . _groupByFamily . '</a></th>';
        echo '<th><a href="javascript:void(0);" onclick="divToggle(\'#toggleSearchAllSchool\');">' . _searchAllSchools . '</a></th>';
        echo '<th colspan="3"><a href="javascript:void(0);" onclick="divToggle(\'#toggleIncludeInactive\');">' . _includeInactiveStudents . '</a></th>';
        echo '</tr>';


        $list = DBGet(DBQuery("SELECT DISTINCT NAME,ID,SORT_ORDER FROM school_gradelevel_sections WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
        echo '<tr>';

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['section'] != '') {
            echo '<td><div id="toggleSection_element">';
            echo '<select id="section" name=section class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
            foreach ($list as $value)
                echo '<option value="' . $value['ID'] . '" ' . ($value['ID'] == $_REQUEST['section'] ? 'selected' : '') . '>' . $value['NAME'] . '</option>';
            echo '</select></div></td>';
        } else {
            echo '<td><div onclick="divToggle(\'#toggleSection\');" id="toggleSection">' . _any . '</div><div style="display:none;" id="toggleSection_element" class="hide-element">';
            echo '<select id="section" name=section class="form-control p-t-0 p-b-0 input-xs"><option value="">-- Select --</option>';
            foreach ($list as $value)
                echo '<option value=' . $value['ID'] . '>' . $value['NAME'] . '</option>';
            echo '</select></div></td>';
        }

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['address_group'] != '')
            echo '<td><div id="toggleGrpByFamily_element"><div class="checkbox m-b-0"><label><input id="address_group" type="checkbox" name="address_group" value="Y" checked/></label></div></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleGrpByFamily\');" id="toggleGrpByFamily">' . _no . '</div><div style="display:none;" id="toggleGrpByFamily_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="address_group" name="address_group" value="Y"/></label></div></div></td>';

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['_search_all_schools'] != '')
            echo '<td><div id="toggleSearchAllSchool_element"><div class="checkbox m-b-0"><label><input type="checkbox" id="_search_all_schools" name="_search_all_schools" value="Y" checked/></label></div></div></td>';
        else
            echo '<td><div onclick="divToggle(\'#toggleSearchAllSchool\');" id="toggleSearchAllSchool">' . _no . '</div><div style="display:none;" id="toggleSearchAllSchool_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="_search_all_schools" name="_search_all_schools" value="Y"/></label></div></div></td>';

        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['include_inactive'] != '')
            echo '<td colspan="3"><div id="toggleIncludeInactive_element"><div class="checkbox m-b-0"><label><input type="checkbox" id="include_inactive" name="include_inactive" value="Y" checked/></label></div></div></td>';
        else
            echo '<td colspan="3"><div onclick="divToggle(\'#toggleIncludeInactive\');" id="toggleIncludeInactive">' . _no . '</div><div style="display:none;" id="toggleIncludeInactive_element" class="hide-element"><div class="checkbox m-b-0"><label><input type="checkbox" id="include_inactive" name="include_inactive" value="Y"/></label></div></div></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; //.table-responsive

        # ---   Advanced Filter Start ---------------------------------------------------------- #
        $ethnicity = DBGet(DBQuery('SELECT * FROM ethnicity'));
        $ethnic_option = array($ethnicity[1]['ETHNICITY_ID'] => $ethnicity[1]['ETHNICITY_NAME'], $ethnicity[2]['ETHNICITY_ID'] => $ethnicity[2]['ETHNICITY_NAME'], $ethnicity[3]['ETHNICITY_ID'] => $ethnicity[3]['ETHNICITY_NAME'], $ethnicity[4]['ETHNICITY_ID'] => $ethnicity[4]['ETHNICITY_NAME'], $ethnicity[5]['ETHNICITY_ID'] => $ethnicity[5]['ETHNICITY_NAME'], $ethnicity[6]['ETHNICITY_ID'] => $ethnicity[6]['ETHNICITY_NAME'], $ethnicity[7]['ETHNICITY_ID'] => $ethnicity[7]['ETHNICITY_NAME'], $ethnicity[8]['ETHNICITY_ID'] => $ethnicity[8]['ETHNICITY_NAME'], $ethnicity[9]['ETHNICITY_ID'] => $ethnicity[9]['ETHNICITY_NAME'], $ethnicity[10]['ETHNICITY_ID'] => $ethnicity[10]['ETHNICITY_NAME'], $ethnicity[11]['ETHNICITY_ID'] => $ethnicity[11]['ETHNICITY_NAME']);
        $language = DBGet(DBQuery('SELECT * FROM language'));
        $language_option = array($language[1]['LANGUAGE_ID'] => $language[1]['LANGUAGE_NAME'], $language[2]['LANGUAGE_ID'] => $language[2]['LANGUAGE_NAME'], $language[3]['LANGUAGE_ID'] => $language[3]['LANGUAGE_NAME'], $language[4]['LANGUAGE_ID'] => $language[4]['LANGUAGE_NAME'], $language[5]['LANGUAGE_ID'] => $language[5]['LANGUAGE_NAME'], $language[6]['LANGUAGE_ID'] => $language[6]['LANGUAGE_NAME'], $language[7]['LANGUAGE_ID'] => $language[7]['LANGUAGE_NAME'], $language[8]['LANGUAGE_ID'] => $language[8]['LANGUAGE_NAME'], $language[9]['LANGUAGE_ID'] => $language[9]['LANGUAGE_NAME'], $language[10]['LANGUAGE_ID'] => $language[10]['LANGUAGE_NAME'], $language[11]['LANGUAGE_ID'] => $language[11]['LANGUAGE_NAME'], $language[12]['LANGUAGE_ID'] => $language[12]['LANGUAGE_NAME'], $language[13]['LANGUAGE_ID'] => $language[13]['LANGUAGE_NAME'], $language[14]['LANGUAGE_ID'] => $language[14]['LANGUAGE_NAME'], $language[15]['LANGUAGE_ID'] => $language[15]['LANGUAGE_NAME'], $language[16]['LANGUAGE_ID'] => $language[16]['LANGUAGE_NAME'], $language[17]['LANGUAGE_ID'] => $language[17]['LANGUAGE_NAME'], $language[18]['LANGUAGE_ID'] => $language[18]['LANGUAGE_NAME'], $language[19]['LANGUAGE_ID'] => $language[19]['LANGUAGE_NAME'], $language[20]['LANGUAGE_ID'] => $language[20]['LANGUAGE_NAME']);

        echo '<div style="height:10px;"></div>';
        echo '<input type=hidden name=sql_save_session value=true />';


        echo '<div id="searchdiv1" style="display:none;" class="well">';
        echo '<div><a href="javascript:void(0);" class="text-pink" onclick="hide_search_div1();"><i class="icon-cancel-square"></i> ' . _closeAdvancedFilter . '</a></div>';
        echo '<br/>';

        echo '<div class="row">';
        echo '<div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['mp_comment'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . ' </label><div class="col-lg-8"><input type="text" id="mp_comment" name="mp_comment" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _comments . '" value="' . $_REQUEST['mp_comment'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . ' </label><div class="col-lg-8"><input type=text id="mp_comment" name="mp_comment" size=30 placeholder="' . _comments . '" class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        Search('student_fields');

        // Search('student_advanced_fields');

        if (isset($_REQUEST['cust']) && !empty($_REQUEST['cust'])) {
            foreach ($_REQUEST['cust'] as $single_cfk => $single_cfv) {
                $keep_elem = '';

                $keep_elem = 'cust[' . $single_cfk . ']';

                echo '<script>document.getElementsByName("' . $keep_elem . '")[0].value = "' . $single_cfv . '";</script>';
            }
        }


        echo '<h5 class="text-primary">' . _generalInformation . '</h5>';

        // echo '<div class="row">';
        // echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._middleName.' </label><div class="col-lg-8"><input type=text name="middle_name" size=30 placeholder="'._middleName.'" class="form-control"></div></div>';
        // echo '</div>'; //.col-md-6
        // echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._commonName.' </label><div class="col-lg-8"><input type=text name="common_name" size=30 placeholder="'._commonName.'" class="form-control"></div></div>';
        // echo '</div>'; //.col-md-6
        // echo '</div>'; //.row

        echo '<div class="row">';
        echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._gender.' </label><div class="col-lg-8">'.SelectInput($_REQUEST['GENDER'], 'GENDER', '', array('Male' => 'Male', 'Female' => 'Female'), 'N/A', '') .'</div></div>';
        $gender_array = array('Male' => 'Male', 'Female' => 'Female');
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _gender . ' </label><div class="col-lg-8"><select class="form-control" id="GENDER" name="GENDER"><option value="">N/A</option>';
        foreach ($gender_array as $one_gender) {
            $gend_selected = '';
            if ($_REQUEST['GENDER'] == $one_gender) {
                $gend_selected = 'selected';
            }
            echo '<option ' . $gend_selected . ' value="' . $one_gender . '">' . $one_gender . '</option>';
        }
        echo '</select></div></div>';
        echo '</div>'; //.col-md-6


        echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._ethnicity.' </label><div class="col-lg-8">' . SelectInput($_REQUEST['ETHNICITY_ID'], 'ETHNICITY_ID', '', $ethnic_option, 'N/A', '') . '</div></div>';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _ethnicity . ' </label><div class="col-lg-8"><select class="form-control" id="ETHNICITY_ID" name="ETHNICITY_ID"><option value="">N/A</option>';
        foreach ($ethnic_option as $ethn_key => $one_ethnicity) {
            $ethn_selected = '';
            if ($_REQUEST['ETHNICITY_ID'] == $ethn_key) {
                $ethn_selected = 'selected';
            }
            echo '<option ' . $ethn_selected . ' value="' . $ethn_key . '">' . $one_ethnicity . '</option>';
        }
        echo '</select></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row


        echo '<div class="row">';
        echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._language.' </label><div class="col-lg-8">' . SelectInput($_REQUEST['LANGUAGE_ID'], 'LANGUAGE_ID', '', $language_option, 'N/A', '') . '</div></div>';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _language . ' </label><div class="col-lg-8"><select class="form-control" id="LANGUAGE_ID" name="LANGUAGE_ID"><option value="">N/A</option>';
        foreach ($language_option as $lang_key => $one_language) {
            $lang_selected = '';
            if ($_REQUEST['LANGUAGE_ID'] == $lang_key) {
                $lang_selected = 'selected';
            }
            echo '<option ' . $lang_selected . ' value="' . $lang_key . '">' . $one_language . '</option>';
        }
        echo '</select></div></div>';
        echo '</div>'; //.col-md-6
        // echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._email.' </label><div class="col-lg-8"><input type=text name="email" size=30 placeholder="'._email.'" class="form-control"></div></div>';
        // echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        // echo '<div class="row">';
        // echo '<div class="col-md-6">';
        // echo '<div class="form-group"><label class="control-label col-lg-4 text-right">'._phone.' </label><div class="col-lg-8"><input type=text name="phone" size=30 placeholder="'._phone.'" class="form-control"></div></div>';
        // echo '</div>'; //.col-md-6

        // echo '</div>'; //.row





        echo '<h5 class="text-primary">' . _birthdaySearch . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _from . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_from_birthdate', 'month_from_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
        echo '</div><div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _to . ': </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInputDob('day_to_birthdate', 'month_to_birthdate', '', 'Y', 'Y', '') . '</div></div></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<h5 class="text-primary">' . _dob . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right"> </label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('day_dob_birthdate', 'month_dob_birthdate', 'year_dob_birthdate', 'Y', 'Y', 'Y') . '</div></div></div></div>';
        echo '</div><div class="col-md-6">';

        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<h5 class="text-primary">' . _goalAndProgress . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['goal_title'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _goalTitle . ' </label><div class="col-lg-8"><input type="text" id="goal_title" name="goal_title" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _goalTitle . '" value="' . $_REQUEST['goal_title'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _goalTitle . ' </label><div class="col-lg-8"><input type=text id="goal_title" name="goal_title" placeholder="' . _goalTitle . '" size=30 class="form-control"></div></div>';
        echo '</div><div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['goal_description'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _goalDescription . ' </label><div class="col-lg-8"><input type="text" id="goal_description" name="goal_description" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _goalDescription . '" value="' . $_REQUEST['goal_description'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _goalDescription . ' </label><div class="col-lg-8"><input type=text id="goal_description" name="goal_description" placeholder="' . _goalDescription . '" size=30 class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<div class="row">';
        echo '<div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['progress_name'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _progressPeriod . ' </label><div class="col-lg-8"><input type="text" id="progress_name" name="progress_name" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _progressPeriod . '" value="' . $_REQUEST['progress_name'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _progressPeriod . ' </label><div class="col-lg-8"><input type=text id="progress_name" name="progress_name" placeholder="' . _progressPeriod . '" size=30 class="form-control"></div></div>';
        echo '</div><div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['progress_description'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _progressAssessment . ' </label><div class="col-lg-8"><input type="text" id="progress_description" name="progress_description" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _progressAssessment . '" value="' . $_REQUEST['progress_description'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _progressAssessment . ' </label><div class="col-lg-8"><input type=text id="progress_description" name="progress_description" size=30 placeholder="' . _progressAssessment . '" class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<h5 class="text-primary">' . _medical . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('med_day', 'med_month', 'med_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
        echo '</div><div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['doctors_note_comments'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _doctorSNote . ' </label><div class="col-lg-8"><input type="text" id="doctors_note_comments" name="doctors_note_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _doctorSNote . '" value="' . $_REQUEST['doctors_note_comments'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _doctorSNote . '</label><div class="col-lg-8"><input type=text id="doctors_note_comments" name="doctors_note_comments" placeholder="' . _doctorSNote . '" size=30 class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<h5 class="text-primary">' . _immunization . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['type'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _type . ' </label><div class="col-lg-8"><input type="text" id="type" name="type" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _immunizationType . '" value="' . $_REQUEST['type'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _type . '</label><div class="col-lg-8"><input type=text id="type" name="type" placeholder="' . _immunizationType . '" size=30 class="form-control"></div></div>';
        echo '</div><div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('imm_day', 'imm_month', 'imm_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<div class="row">';
        echo '<div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['imm_comments'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . ' </label><div class="col-lg-8"><input type="text" id="imm_comments" name="imm_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _immunizationComments . '" value="' . $_REQUEST['imm_comments'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . '</label><div class="col-lg-8"><input type=text id="imm_comments" name="imm_comments" placeholder="' . _immunizationComments . '" size=30 class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<h5 class="text-primary">' . _medicalAlert . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('ma_day', 'ma_month', 'ma_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
        echo '</div><div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['med_alrt_title'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _alert . ' </label><div class="col-lg-8"><input type="text" id="med_alrt_title" name="med_alrt_title" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _medicalAlerts . '" value="' . $_REQUEST['med_alrt_title'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _alert . '</label><div class="col-lg-8"><input type=text id="med_alrt_title" name="med_alrt_title" placeholder="' . _medicalAlerts . '" size=30 class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<h5 class="text-primary">' . _nurseVisit . '</h5>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _date . '</label><div class="col-lg-8"><div class="form-horizontal"><div class="row">' . SearchDateInput('nv_day', 'nv_month', 'nv_year', 'Y', 'Y', 'Y') . '</div></div></div></div>';
        echo '</div><div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['reason'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _reason . ' </label><div class="col-lg-8"><input type="text" id="reason" name="reason" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _nurseVisitReason . '" value="' . $_REQUEST['reason'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _reason . '</label><div class="col-lg-8"><input type=text id="reason" name="reason" size=30 placeholder="' . _nurseVisitReason . '" class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<div class="row">';
        echo '<div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['result'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _result . ' </label><div class="col-lg-8"><input type="text" id="result" name="result" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _nurseVisitResult . '" value="' . $_REQUEST['result'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _result . '</label><div class="col-lg-8"><input type=text id="result" name="result" size=30 placeholder="' . _nurseVisitResult . '" class="form-control"></div></div>';
        echo '</div><div class="col-md-6">';
        if ($_REQUEST['filter_form'] == 'Y' && $_REQUEST['med_vist_comments'] != '')
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . ' </label><div class="col-lg-8"><input type="text" id="med_vist_comments" name="med_vist_comments" class="form-control p-t-0 p-b-0 input-xs" placeholder="' . _nurseVisitComments . '" value="' . $_REQUEST['med_vist_comments'] . '"/></div></div>';
        else
            echo '<div class="form-group"><label class="control-label col-lg-4 text-right">' . _comments . '</label><div class="col-lg-8"><input type=text id="med_vist_comments" name="med_vist_comments" placeholder="' . _nurseVisitComments . '" size=30 class="form-control"></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '</div>';



        # ---   Advanced Filter End ----------------------------------------------------------- #

        echo '<div class="panel-footer p-l-15 p-r-15">';

        echo '<div class="row">';
        echo '<div class="col-sm-6 col-md-6 col-lg-6">';
        echo '<input type="submit" class="btn btn-primary" value="' . _applyFilter . '" onclick="self_disable(this);" /> &nbsp; <input class="btn btn-default" value="' . _reset . '" type="button" onclick="clearSearching();">';
        echo '<a id="advancedFilterDivForStudents" href="javascript:void(0);" class="text-pink" onclick="show_search_div1();">  &nbsp;<i class="icon-cog"></i> ' . _advancedFilter . '</a>';
        echo '</div>';
        echo '<div class="col-sm-6 col-md-6 col-lg-6 text-lg-right text-md-right text-sm-right">';
        echo '<a HREF=javascript:void(0) data-toggle="modal" data-target="#modal_default_filter" class="btn btn-primary display-inline-block" onClick="setFilterValues();">' . _saveFilter . '</a>';
        $filters = DBGet(DBQuery('SELECT * FROM filters WHERE SCHOOL_ID IN (' . UserSchool() . ',0) AND SHOW_TO IN (' . UserID() . ',0)'));
        echo '<div class="m-l-10 display-inline-block"><select name="filter" class="form-control form-control-bordered width-auto"  onchange="this.form.submit();"><option value="">-- ' . _loadFilter . ' --</option>';
        foreach ($filters as $value)
            echo '<option value=' . $value['FILTER_ID'] . ' ' . ($_REQUEST['filter'] == $value['FILTER_ID'] ? 'SELECTED' : '') . ' >' . $value['FILTER_NAME'] . '</option>';
        echo '</select></div>';

        if (!empty($filters)) {
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a HREF=javascript:void(0) data-toggle="modal" data-target="#modal_filter_edit" class="text-pink display-inline-block" onClick="loadFilterList();"><i class="fa fa-filter"></i> ' . _editFilters . '</a>';
        }

        echo '</div>';
        echo '</div>';

        echo '</div>'; //.panel-footer
        echo '</div>'; //.panel
        echo '</form>';



        ##### Modal For Editing Filter #####
        echo '<div id="modal_filter_edit" class="modal fade">';
        echo '<div class="modal-dialog modal-md">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<button type="button" class="close" data-dismiss="modal">×</button>';
        echo '<h5 class="modal-title">' . _editFilter . '</h5>';
        echo '</div>';

        echo "<form class='form-horizontal m-b-0' method=POST>";

        echo '<div class="modal-body p-0">';
        echo '<div id="conf_div"></div>';

        echo '<div id="stuf_loader" class="ajax-loading"><img src="assets/search-loader.gif"></div>';
        echo '<div id="view_resp" class=""></div>';

        echo '</div>'; //.modal-body
        echo '</form>';

        echo '</div>'; //.modal-content
        echo '</div>'; //.modal-dialog
        echo '</div>'; //.modal
        ##### End of Modal #####


        ##### Modal For Filter Save #####
        echo '<div id="modal_default_filter" class="modal fade">';
        echo '<div class="modal-dialog modal-sm">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<button type="button" class="close" data-dismiss="modal">×</button>';
        echo '<h5 class="modal-title">' . _saveCurrentFilter . '</h5>';
        echo '</div>';

        $filter_modal = DBGet(DBQuery('SELECT FILTER_NAME FROM filters WHERE SCHOOL_ID IN (' . UserSchool() . ',0) AND SHOW_TO IN (' . UserID() . ',0)'));
        $filter_name = json_encode(array_column($filter_modal, 'FILTER_NAME'));

        echo "<form onSubmit='return validate_filter($filter_name);' class='form-horizontal m-b-0' action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]" . $extra['action'] . " method=POST>";
        // echo '<form class="form-horizontal m-b-0"  method="post" action="Modules.php?modname='.$_REQUEST[modname].'&modfunc='.$_REQUEST[modfunc].'&search_modfunc=list&next_modname='.$_REQUEST[next_modname]. $extra['action'].">';
        echo '<div class="modal-body">';
        echo '<div id="conf_div"></div>';

        echo '<div class="form-group">';
        echo '<label class="control-label text-right col-lg-4">' . _filterName . '</label>';
        echo '<div class="col-lg-8">';
        echo '<input type="text" id="filter_name" name="filter_name" size="30" placeholder="' . _filterName . '" class="form-control">';
        echo '<div id="error_modal_filter"></div></div>'; //.col-lg-8
        echo '</div>'; //.form-group

        echo  '<input type="hidden" id="last_hidden" name="last"/>';
        echo  '<input type="hidden" id="first_hidden" name="first"/>';
        echo  '<input type="hidden" id="stuid_hidden" name="stuid"/>';
        echo  '<input type="hidden" id="altid_hidden" name="altid"/>';
        echo  '<input type="hidden" id="addr_hidden" name="addr"/>';
        echo  '<input type="hidden" id="grade_hidden" name="grade"/>';
        echo  '<input type="hidden" id="section_hidden" name="section"/>';

        echo  '<input type="hidden" id="GENDER_hidden" name="GENDER"/>';
        echo  '<input type="hidden" id="ETHNICITY_ID_hidden" name="ETHNICITY_ID"/>';
        echo  '<input type="hidden" id="LANGUAGE_ID_hidden" name="LANGUAGE_ID"/>';

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

        $searchable_fields = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM='StudentFieldsSearchable' AND puc.USER_ID='" . User('STAFF_ID') . "' AND puc.VALUE='Y' ORDER BY cf.SORT_ORDER,cf.TITLE"));

        $nonsearchable_fields = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME, cf.TYPE, cf.TITLE, cf.SELECT_OPTIONS FROM custom_fields cf LEFT JOIN program_user_config puc ON puc.TITLE = cf.ID WHERE puc.TITLE IS NULL"));

        // echo "<pre>";print_r($searchable_fields);echo "</pre>";
        // echo "<pre>";print_r($nonsearchable_fields);echo "</pre>";

        $each_custom_fields = '';

        foreach ($searchable_fields as $one_searchable) {
            if ($one_searchable['TYPE'] != 'textarea') {
                echo '<input type="hidden" id="custom_' . $one_searchable['COLUMN_NAME'] . '_hidden" name="cust[' . $one_searchable['COLUMN_NAME'] . ']"/>';

                if ($each_custom_fields == '') {
                    $each_custom_fields .= $one_searchable['COLUMN_NAME'];
                } else {
                    $each_custom_fields .= ', ' . $one_searchable['COLUMN_NAME'];
                }
            }
        }

        echo '<input id="each_custom_fields_ids" name="each_custom_fields_ids" type="hidden" value="' . $each_custom_fields . '">';

        echo '<div class="form-group">';
        echo '<label class="control-label text-right col-lg-4">' . _makePublic . '</label>';
        echo '<div class="col-lg-8">';
        echo '<div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_public" value="Y"><span></span></label></div>';
        echo '</div>'; //.col-lg-8
        echo '</div>'; //.form-group

        echo '<div class="form-group">';
        echo '<label class="control-label text-right col-lg-4">' . _allSchool . '</label>';
        echo '<div class="col-lg-8">';
        echo '<div class="checkbox checkbox-switch switch-success"><label><input type="checkbox" name="filter_all_school" value="Y"><span></span></label></div>';
        echo '</div>'; //.col-lg-8
        echo '</div>'; //.form-group

        echo '</div>'; //.modal-body
        echo '<div class="modal-footer text-center">';
        echo '<input type="submit" class="btn btn-primary display-inline-block" value="' . _save . '">';
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
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true><i class=\"icon-square-down-right\"></i> " . _expandedView . "</A>", $extra['header_right']);
            DrawHeader(str_replace('', '', substr($_openSIS['SearchTerms'], 0, -4)));
        } elseif (!UserStudentID() && count($students_RET) != 0) {
            DrawHeader("<A HREF=" . PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false><i class=\"icon-square-up-left\"></i> " . _originalView . "</A>", $extra['header_right']);
            DrawHeader(str_replace('', '', substr($_openSIS['Search'], 0, -4)));
        }
        DrawHeader($extra['extra_header_left'], $extra['extra_header_right']);
        if ($_REQUEST['LO_save'] != '1' && !$extra['suppress_save']) {
            $_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
            //echo '<script language=JavaScript>parent.help.location.reload();</script>';
        }
        if (!$extra['singular'] || !$extra['plural'])
            $extra['singular'] = _student;
        $extra['plural'] = _students;

        if (isset($_SESSION['ALL_RETURN']) && (is_countable($_SESSION['ALL_RETURN']) && count($_SESSION['ALL_RETURN']) > 0))
            foreach ($_SESSION['ALL_RETURN'] as $si => $sd)
                $_SESSION['students_order'][$si] = $sd['STUDENT_ID'];
        else
            foreach ($students_RET as $si => $sd)
                $_SESSION['students_order'][$si] = $sd['STUDENT_ID'];

        # Set pagination params
        setPaginationRequisites($_REQUEST['modname'], $_REQUEST['search_modfunc'], $_REQUEST['next_modname'], $columns, $extra['singular'], $extra['plural'], $link, $extra['LO_group'], $extra['options'], 'ListOutputCustomDT', ProgramTitle());

        echo "<div id='tabs_resp'><div id='students' class=\"table-responsive\">";

        ListOutputCustomDT($students_RET, $columns, $extra['singular'], $extra['plural'], $link, '', $extra['LO_group'], $extra['options']);
        echo "</div></div>"; //.table-responsive
        echo "</div>"; //.panel.panel-default

        // form for filter student ends
    } elseif (count($students_RET) == 1) {
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
        BackPrompt(_noStudentsWereFound . '.');
}

// function _make_sections($value)
// {
//     if ($value != '') {
//         $get = DBGet(DBQuery('SELECT NAME FROM school_gradelevel_sections WHERE ID=' . $value));
//         return $get[1]['NAME'];
//     } else
//         return '';
// }
