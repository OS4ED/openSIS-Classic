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
include "lang/language.php";
function Search($type, $extra = array(), $search_from_grade = '')
{

    global $_openSIS;
    switch ($type) {
        case 'student_id':
            if ($_REQUEST['bottom_back']) {
                unset($_SESSION['student_id']);
                //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }
            if ($_SESSION['unset_student']) {
                unset($_REQUEST['student_id']);
                unset($_SESSION['unset_student']);
            }

            if ($_REQUEST['student_id']) {
                if ($_REQUEST['student_id'] != 'new') {
                    $_SESSION['student_id'] = $_REQUEST['student_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['student_id']);
                //                if (!$_REQUEST['_openSIS_PDF'])
                //                    echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }

            if (!UserStudentID() && $_REQUEST['student_id'] != 'new' || $extra['new'] == true) {
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                include('modules/students/SearchInc.php');
            }
            break;

        case 'student_id_from_student':
            if ($_REQUEST['bottom_back']) {
                unset($_SESSION['student_id']);
                //echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }
            if ($_SESSION['unset_student']) {
                unset($_REQUEST['student_id']);
                unset($_SESSION['unset_student']);
            }

            if ($_REQUEST['student_id']) {
                if ($_REQUEST['student_id'] != 'new') {
                    $_SESSION['student_id'] = $_REQUEST['student_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['student_id']);
                //                if (!$_REQUEST['_openSIS_PDF'])
                //                    echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }

            if (!UserStudentID() && $_REQUEST['student_id'] != 'new' || $extra['new'] == true) {
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                include('modules/students/SearchForStudentsInc.php');
            }
            break;
        case 'staff_id':
            // convert profile string to array for legacy compatibility
            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {
                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['staff_id']);
                //                if (!$_REQUEST['_openSIS_PDF'])
                //                    echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {
                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/users/SearchInc.php');
            }
            break;

        case 'teacher_id':
            // convert profile string to array for legacy compatibility
            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {
                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['staff_id']);
                //                if (!$_REQUEST['_openSIS_PDF'])
                //                    echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {
                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/users/SearchStaffInc.php');
            }
            break;

        case 'general_info':
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _lastName . '</label><div class="col-lg-8"><input type=text name="last" size=30 placeholder="' . _lastName . '" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _firstName . '</label><div class="col-lg-8"><input type=text name="first" size=30 placeholder="' . _firstName . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _studentId . '</label><div class="col-lg-8"><input type=text name="stuid" size=30 placeholder="' . _studentId . '" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _altId . '</label><div class="col-lg-8"><input type=text name="altid" size=30 placeholder="' . _altId . '" class="form-control"></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group clearfix"><label class="control-label text-right col-lg-4">' . _address . '</label><div class="col-lg-8"><input type=text name="addr" size=30 placeholder="' . _address . '" class="form-control"></div></div>';
            echo '</div><div class="col-md-6">';
            $list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _grade . '</label><div class="col-lg-8"><SELECT name=grade class="form-control"><OPTION value="">' . _grade . '</OPTION>';

            foreach ($list as $value)
                echo '<OPTION value="' . $value['ID'] . '">' . $value['TITLE'] . '</OPTION>';
            echo '</SELECT></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            echo '<div class="row">';
            echo '<div class="col-md-6">';
            $list = DBGet(DBQuery("SELECT DISTINCT NAME,ID,SORT_ORDER FROM school_gradelevel_sections WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
            // echo '<div class="form-group"><label class="control-label col-lg-4">'._section.'</label><div class="col-lg-8"><SELECT name=section class="form-control"><OPTION value="">'._section.'</OPTION>';
            // echo '</div><div class="col-md-6">';
            echo '<div class="form-group"><label class="control-label text-right col-lg-4">' . _section . '</label><div class="col-lg-8"><SELECT name=section class="form-control"><OPTION value="">' . _section . '</OPTION>';

            foreach ($list as $value)
                echo '<OPTION value="' . $value['ID'] . '">' . $value['NAME'] . '</OPTION>';
            echo '</SELECT></div></div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row

            break;

        case 'student_fields':

            $search_fields_RET = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM='StudentFieldsSearchable' AND puc.USER_ID='" . User('STAFF_ID') . "' AND puc.VALUE='Y' ORDER BY cf.SORT_ORDER,cf.TITLE"), array(), array('TYPE'));

            $field_count = 0;
            echo '<div class="row">';
            // edit needed
            if (!empty($search_fields_RET['text'])) {
                foreach ($search_fields_RET['text'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label><div class=\"col-md-8\"><INPUT type=text name=cust[{$column['COLUMN_NAME']}] size=30 class=\"form-control\"></div></div>";
                    echo '</div>';
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['numeric'])) {
                foreach ($search_fields_RET['numeric'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo "<div class=\"col-md-8\"><div class=\"input-group\"><span class=\"input-group-addon\">Between</span><INPUT type=text name=cust_begin[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"><span class=\"input-group-addon\">&amp;</span><INPUT type=text name=cust_end[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"></div></div>";
                    echo '</div>'; //.form-group
                    echo '</div>';
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }

            if (!empty($search_fields_RET['codeds'])) {
                foreach ($search_fields_RET['codeds'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option) {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            echo "<OPTION value=\"$option[0]\">$option[1]</OPTION>";
                    }
                    echo '</SELECT>';
                    echo "</div>";
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['select'])) {
                foreach ($search_fields_RET['select'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo '</SELECT>';
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['autos'])) {
                foreach ($search_fields_RET['autos'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else {
                        $options_RET = array();
                    }

                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option) {
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                        $options[$option] = true;
                    }
                    echo "<OPTION value=\"---\">---</OPTION>";
                    $options['---'] = true;
                    // add values found in current and previous year
                    $options_RET = DBGet(DBQuery("SELECT DISTINCT s.$column[COLUMN_NAME],upper(s.$column[COLUMN_NAME]) AS KEEY FROM students s,student_enrollment sse WHERE sse.STUDENT_ID=s.STUDENT_ID AND (sse.SYEAR='" . UserSyear() . "' OR sse.SYEAR='" . (UserSyear() - 1) . "') AND $column[COLUMN_NAME] IS NOT NULL ORDER BY KEEY"));
                    foreach ($options_RET as $option)
                        if ($option[$column['COLUMN_NAME']] != '' && !$options[$option[$column['COLUMN_NAME']]]) {
                            echo "<OPTION value=\"" . $option[$column['COLUMN_NAME']] . "\">" . $option[$column['COLUMN_NAME']] . "</OPTION>";
                            $options[$option[$column['COLUMN_NAME']]] = true;
                        }
                    echo '</SELECT>';
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['edits'])) {
                foreach ($search_fields_RET['edits'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo "<OPTION value=\"---\">---</OPTION>";
                    echo "<OPTION value=\"~\">Other Value</OPTION>";
                    echo '</SELECT>';
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }

            if (!empty($search_fields_RET['date'])) {
                $data_counter = 1;
                foreach ($search_fields_RET['date'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label text-right col-md-4\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<div class=\"input-group\">" . DateInputAY('', '_cust_begin[' . $column['COLUMN_NAME'] . ']', $data_counter) . "<span class=\"input-group-addon\">&amp;</span>";
                    $data_counter++;
                    echo DateInputAY('', '_cust_end[' . $column['COLUMN_NAME'] . ']', $data_counter);
                    echo '</div>'; //.input-group
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $data_counter++;
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }

            if (!empty($search_fields_RET['radio'])) {
                echo '<div class="row">';
                echo '<label class="col-xs-3 col-md-2"></label>';
                echo '<label class="col-xs-3 col-md-1">All</label>';
                echo '<label class="col-xs-3 col-md-1">Yes</label>';
                echo '<label class="col-xs-3 col-md-1">No</label>';
                echo '</div>';
                // if (!empty($search_fields_RET['radio']) > 1){
                //     echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table>";
                // }

                $side = 1;
                foreach ($search_fields_RET['radio'] as $cust) {
                    echo '<div class="row">';
                    echo "<label class=\"col-xs-3 col-md-2 text-right control-label\">$cust[TITLE]</label>";
                    echo "<div class=\"col-xs-3 col-md-1\"><label class=\"radio-inline\"><input class=\"styled\" name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='' checked='checked' /></label></div>";
                    echo "<div class=\"col-xs-3 col-md-1\"><label class=\"radio-inline\"><input class=\"styled\" name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='Y' /></label></div>";
                    echo "<div class=\"col-xs-3 col-md-1\"><label class=\"radio-inline\"><input class=\"styled\" name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='N' /></label></div>";
                    echo '</div>';
                    $side++;
                }
            }
            echo '</div>'; //.row
            break;


        case 'student_advanced_fields';

            $search_fields_RET = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME, cf.TYPE, cf.TITLE, cf.SELECT_OPTIONS FROM custom_fields cf LEFT JOIN program_user_config puc ON puc.TITLE = cf.ID WHERE puc.TITLE IS NULL"), array(), array('TYPE'));

            // echo "<pre>";print_r($search_fields_RET);echo "</pre>";

            $field_count = 0;
            echo '<div class="row">';
            // edit needed
            if (!empty($search_fields_RET['text'])) {
                foreach ($search_fields_RET['text'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label><div class=\"col-md-8\"><INPUT type=text name=cust[{$column['COLUMN_NAME']}] size=30 class=\"form-control\"></div></div>";
                    echo '</div>';
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['numeric'])) {
                foreach ($search_fields_RET['numeric'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo "<div class=\"col-md-8\"><div class=\"input-group\"><span class=\"input-group-addon\">Between</span><INPUT type=text name=cust_begin[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"><span class=\"input-group-addon\">&amp;</span><INPUT type=text name=cust_end[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"></div></div>";
                    echo '</div>'; //.form-group
                    echo '</div>';
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }

            if (!empty($search_fields_RET['codeds'])) {
                foreach ($search_fields_RET['codeds'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option) {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            echo "<OPTION value=\"$option[0]\">$option[1]</OPTION>";
                    }
                    echo '</SELECT>';
                    echo "</div>";
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['select'])) {
                foreach ($search_fields_RET['select'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo '</SELECT>';
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['autos'])) {
                foreach ($search_fields_RET['autos'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else {
                        $options_RET = array();
                    }

                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option) {
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                        $options[$option] = true;
                    }
                    echo "<OPTION value=\"---\">---</OPTION>";
                    $options['---'] = true;
                    // add values found in current and previous year
                    $options_RET = DBGet(DBQuery("SELECT DISTINCT s.$column[COLUMN_NAME],upper(s.$column[COLUMN_NAME]) AS KEEY FROM students s,student_enrollment sse WHERE sse.STUDENT_ID=s.STUDENT_ID AND (sse.SYEAR='" . UserSyear() . "' OR sse.SYEAR='" . (UserSyear() - 1) . "') AND $column[COLUMN_NAME] IS NOT NULL ORDER BY KEEY"));
                    foreach ($options_RET as $option)
                        if ($option[$column['COLUMN_NAME']] != '' && !$options[$option[$column['COLUMN_NAME']]]) {
                            echo "<OPTION value=\"" . $option[$column['COLUMN_NAME']] . "\">" . $option[$column['COLUMN_NAME']] . "</OPTION>";
                            $options[$option[$column['COLUMN_NAME']]] = true;
                        }
                    echo '</SELECT>';
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }
            if (!empty($search_fields_RET['edits'])) {
                foreach ($search_fields_RET['edits'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label col-md-4 text-right\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo "<OPTION value=\"---\">---</OPTION>";
                    echo "<OPTION value=\"~\">Other Value</OPTION>";
                    echo '</SELECT>';
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }

            if (!empty($search_fields_RET['date'])) {
                $data_counter = 1;
                foreach ($search_fields_RET['date'] as $column) {
                    if ($field_count == 0) {
                        echo '</div><div class="row">';
                    }
                    echo '<div class="col-md-6">';
                    echo "<div class=\"form-group\"><label class=\"control-label text-right col-md-4\">$column[TITLE]</label>";
                    echo '<div class="col-md-8">';
                    echo "<div class=\"input-group\">" . DateInputAY('', '_cust_begin[' . $column['COLUMN_NAME'] . ']', $data_counter) . "<span class=\"input-group-addon\">&amp;</span>";
                    $data_counter++;
                    echo DateInputAY('', '_cust_end[' . $column['COLUMN_NAME'] . ']', $data_counter);
                    echo '</div>'; //.input-group
                    echo '</div>'; //.col-md-8
                    echo '</div>'; //.form-group
                    echo '</div>'; //.col-md-6
                    $data_counter++;
                    $field_count++;
                    if ($field_count == 2) {
                        $field_count = 0;
                    }
                }
            }

            if (!empty($search_fields_RET['radio'])) {
                echo '<div class="row">';
                echo '<label class="col-xs-3 col-md-2"></label>';
                echo '<label class="col-xs-3 col-md-1">All</label>';
                echo '<label class="col-xs-3 col-md-1">Yes</label>';
                echo '<label class="col-xs-3 col-md-1">No</label>';
                echo '</div>';
                // if (!empty($search_fields_RET['radio']) > 1){
                //     echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table>";
                // }

                $side = 1;
                foreach ($search_fields_RET['radio'] as $cust) {
                    echo '<div class="row">';
                    echo "<label class=\"col-xs-3 col-md-2 text-right control-label\">$cust[TITLE]</label>";
                    echo "<div class=\"col-xs-3 col-md-1\"><label class=\"radio-inline\"><input class=\"styled\" name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='' checked='checked' /></label></div>";
                    echo "<div class=\"col-xs-3 col-md-1\"><label class=\"radio-inline\"><input class=\"styled\" name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='Y' /></label></div>";
                    echo "<div class=\"col-xs-3 col-md-1\"><label class=\"radio-inline\"><input class=\"styled\" name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='N' /></label></div>";
                    echo '</div>';
                    $side++;
                }
            }
            echo '</div>'; //.row
            break;
    }
}

# -------------------------------- SEARCH FOR MISSING ATTENDANCE START ----------------------------------------- #

function Search_Miss_Attn($type, $extra = array())
{
    global $_openSIS;

    switch ($type) {

        case 'staff_id':
            // convert profile string to array for legacy compatibility
            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {
                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['staff_id']);
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {
                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/users/SearchMissAttnInc.php');
            }
            break;
    }
}

# ---------------------------------------- SEARCH FOR MISSING ATTENDANCE END ----------------------------------------- #
#---------------------------------SEARCH FOR GROUP SCHEDULING ---------------------------------------------------------------#

function Search_GroupSchedule($type, $extra = array())
{
    unset($_SESSION['student_id']);
    switch ($type) {
        case 'student_id':
            if ($_REQUEST['bottom_back']) {
                unset($_SESSION['student_id']);
            }
            if ($_SESSION['unset_student']) {
                unset($_REQUEST['student_id']);
                unset($_SESSION['unset_student']);
            }

            if ($_REQUEST['student_id']) {
                if ($_REQUEST['student_id'] != 'new') {
                    $_SESSION['student_id'] = $_REQUEST['student_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['student_id']);
            }

            if (!UserStudentID() && $_REQUEST['student_id'] != 'new' || $extra['new'] == true) {
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                include('modules/scheduling/SearchInc.php');
            }
            break;

        case 'staff_id':
            // convert profile string to array for legacy compatibility
            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {
                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['staff_id']);
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {
                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/users/SearchInc.php');
            }
            break;

        case 'general_info':
            echo '<div class="form-group"><label class="control-label">' . _lastName . '</label><input type=text name="last" size=30 class="form-control"></div>';
            echo '<div class="form-group"><label class="control-label">' . _firstName . '</label><input type=text name="first" size=30 class="form-control"></div>';
            echo '<div class="form-group"><label class="control-label">' . _studentId . '</label><input type=text name="stuid" size=30 class="form-control"></div>';
            echo '<div class="form-group"><label class="control-label">' . _altId . '</label><input type=text name="altid" size=30 class="form-control"></div>';
            echo '<div class="form-group"><label class="control-label">' . _address . '</label><input type=text name="addr" size=30 class="form-control"></div>';

            $list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
            echo '<div class="form-group"><label class="control-label">' . _grade . '</label><SELECT class="form-control" name=grade><OPTION value="" class="cell_floating">' . _notSpecified . '</OPTION>';
            foreach ($list as $value)
                echo "<OPTION value=$value[ID]>$value[TITLE]</OPTION>";
            echo '</SELECT></div>';
            break;

        case 'student_fields':
            $search_fields_RET = DBGet(DBQuery('SELECT CONCAT(\'CUSTOM_\',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM=\'StudentFieldsSearch\' AND puc.USER_ID=\'' . User('STAFF_ID') . '\' AND puc.VALUE=\'Y\' ORDER BY cf.SORT_ORDER,cf.TITLE'), array(), array('TYPE'));
            if (!$search_fields_RET)
                $search_fields_RET = DBGet(DBQuery('SELECT CONCAT(\'CUSTOM_\',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM custom_fields cf WHERE cf.ID IN (\'200000000\',\'200000001\')'), array(), array('TYPE'));
            // edit needed
            if (!empty($search_fields_RET['text'])) {
                foreach ($search_fields_RET['text'] as $column)
                    echo "<div class=\"form-group\"><label class=\"control-label\">$column[TITLE]</label><INPUT type=text name=cust[{$column['COLUMN_NAME']}] size=30 class=\"form-control\"></div>";
            }
            if (!empty($search_fields_RET['numeric'])) {
                foreach ($search_fields_RET['numeric'] as $column)
                    echo "<h5>$column[TITLE]</h5><div class=\"form-horizontal\"><div class=\"form-group\"><label class=\"control-label col-xs-2\">" . _between . "</label><div class=\"col-xs-3\"><INPUT type=text name=cust_begin[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"></div><div class=\"col-xs-3\"><INPUT type=text name=cust_end[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"></div></div></div>";
            }

            if (!empty($search_fields_RET['codeds'])) {
                foreach ($search_fields_RET['codeds'] as $column) {
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"form-group\"><label class=\"control-label\">$column[TITLE]</label>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option) {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            echo "<OPTION value=\"$option[0]\">$option[1]</OPTION>";
                    }
                    echo '</SELECT>';
                    echo "</div>";
                }
            }
            if (!empty($search_fields_RET['select'])) {
                foreach ($search_fields_RET['select'] as $column) {
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"form-group\"><label class=\"control-label\">$column[TITLE]</label>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo '</SELECT>';
                    echo "</div>";
                }
            }
            if (!empty($search_fields_RET['autos'])) {
                foreach ($search_fields_RET['autos'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    echo "<div class=\"form-group\"><label class=\"control-label\">$column[TITLE]</label>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option) {
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                        $options[$option] = true;
                    }
                    echo "<OPTION value=\"---\">---</OPTION>";
                    $options['---'] = true;
                    // add values found in current and previous year
                    $options_RET = DBGet(DBQuery('SELECT DISTINCT s.' . $column['COLUMN_NAME'] . ',upper(s.' . $column['COLUMN_NAME'] . ') AS KEEY FROM students s,student_enrollment sse WHERE sse.STUDENT_ID=s.STUDENT_ID AND (sse.SYEAR=\'' . UserSyear() . '\' OR sse.SYEAR=\'' . (UserSyear() - 1) . '\') AND ' . $column['COLUMN_NAME'] . ' IS NOT NULL ORDER BY KEEY'));
                    foreach ($options_RET as $option)
                        if ($option[$column['COLUMN_NAME']] != '' && !$options[$option[$column['COLUMN_NAME']]]) {
                            echo "<OPTION value=\"" . $option[$column['COLUMN_NAME']] . "\">" . $option[$column['COLUMN_NAME']] . "</OPTION>";
                            $options[$option[$column['COLUMN_NAME']]] = true;
                        }
                    echo '</SELECT>';
                    echo "</div>";
                }
            }
            if (!empty($search_fields_RET['edits'])) {
                foreach ($search_fields_RET['edits'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    echo "<div class=\"form-group\"><label class=\"control-label\">$column[TITLE]</label>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo "<OPTION value=\"---\">---</OPTION>";
                    echo "<OPTION value=\"~\">Other Value</OPTION>";
                    echo '</SELECT>';
                    echo "</div>";
                }
            }

            if (!empty($search_fields_RET['date'])) {


                $data_counter = 1;
                foreach ($search_fields_RET['date'] as $column) {
                    echo "<h5>$column[TITLE]</h5><div class=\"form-horizontal\"><div class=\"form-group\"><label class=\"control-label col-xs-2\">" . _between . "</label><div class=\"col-xs-3\">" . DateInputAY('', '_cust_begin[' . $column['COLUMN_NAME'] . ']', $data_counter) . '</div><div class="col-xs-1"></div>';
                    $data_counter++;
                    echo "<div class=\"col-xs-3\">" . DateInputAY('', '_cust_end[' . $column['COLUMN_NAME'] . ']', $data_counter) . "</div></div></div>";
                    $data_counter++;
                }
            }
            if (!empty($search_fields_RET['radio'])) {
                echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table></TD><TD></TD><TD></TD><TD>";
                if (!empty($search_fields_RET['radio']) > 1)
                    echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table>";
                echo "</TD></TR>";

                $side = 1;
                foreach ($search_fields_RET['radio'] as $cust) {
                    if ($side % 2 != 0)
                        echo '<TR>';
                    echo "<TD ALIGN=RIGHT>$cust[TITLE]</TD><TD>
						<table border=0 cellpadding=0 cellspacing=0><tr><td width=25 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='' checked='checked' />
						</td><td width=30 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='Y' />
						</td><td width=25 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='N' />
						</td></tr></table>
						</TD><TD>&nbsp; &nbsp; &nbsp; &nbsp;</TD>";
                    if ($side % 2 == 0)
                        echo '</TR>';
                    $side++;
                }
                echo "</TABLE>";
            }
            break;
    }
}

#----------------------------SEARCH FOR GROUP SCHEDULING ENDS HERE -----------------------------------------------------------#

function Search_absence_summary($type, $extra = array(), $search_from_grade = '')
{
    global $_openSIS;

    switch ($type) {
        case 'student_id':
            if ($_REQUEST['bottom_back']) {
                unset($_SESSION['student_id']);
            }
            if ($_SESSION['unset_student']) {
                unset($_REQUEST['student_id']);
                unset($_SESSION['unset_student']);
            }

            if ($_REQUEST['student_id']) {
                if ($_REQUEST['student_id'] != 'new') {
                    $_SESSION['student_id'] = $_REQUEST['student_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['student_id']);
            }

            if (!UserStudentID() && $_REQUEST['student_id'] != 'new' || $extra['new'] == true) {
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                include('modules/attendance/SearchInc.php');
            }
            break;

        case 'staff_id':
            // convert profile string to array for legacy compatibility
            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {
                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else
                    unset($_SESSION['staff_id']);
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {
                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/users/SearchInc.php');
            }
            break;

        case 'general_info':
            echo '<div class="row">';
            echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _lastName . '</label><div class="col-lg-8"><input type=text name="last" placeholder="' . _lastName . '" size=30 class="form-control"></div></div></div>';
            echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _firstName . '</label><div class="col-lg-8"><input type=text name="first" placeholder="' . _firstName . '" size=30 class="form-control"></div></div></div>';
            echo '</div>';
            echo '<div class="row">';
            echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _studentId . '</label><div class="col-lg-8"><input type=text name="stuid" placeholder="' . _studentId . '" size=30 class="form-control"></div></div></div>';
            echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _altId . '</label><div class="col-lg-8"><input type=text name="altid" placeholder="' . _altId . '" size=30 class="form-control"></div></div></div>';
            echo '</div>';
            echo '<div class="row">';
            echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _address . '</label><div class="col-lg-8"><input type=text name="addr" placeholder="' . _address . '" size=30 class="form-control"></div></div></div>';

            $list = DBGet(DBQuery('SELECT DISTINCT TITLE,ID,SORT_ORDER FROM school_gradelevels WHERE SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY SORT_ORDER'));
            echo '<div class="col-md-6"><div class="form-group"><label class="control-label col-lg-4 text-right">' . _grade . '</label><div class="col-lg-8"><SELECT name=grade class="form-control"><OPTION value="">' . _notSpecified . '</OPTION>';
            foreach ($list as $value)
                echo "<OPTION value=$value[ID]>$value[TITLE]</OPTION>";
            echo '</SELECT></div></div></div>';
            echo '</div>';
            break;

        case 'student_fields':
            echo '<div class="row">';
            $search_fields_RET = DBGet(DBQuery('SELECT CONCAT(\'CUSTOM_\',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM=\'StudentFieldsSearch\' AND puc.USER_ID=\'' . User('STAFF_ID') . '\' AND puc.VALUE=\'Y\' ORDER BY cf.SORT_ORDER,cf.TITLE'), array(), array('TYPE'));
            if (!$search_fields_RET)
                $search_fields_RET = DBGet(DBQuery('SELECT CONCAT(\'CUSTOM_\',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM custom_fields cf WHERE cf.ID IN (\'200000000\',\'200000001\')'), array(), array('TYPE'));
            // edit needed
            if (!empty($search_fields_RET['text'])) {
                foreach ($search_fields_RET['text'] as $column)
                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]</label><div class=\"col-lg-8\"><INPUT type=text name=cust[{$column['COLUMN_NAME']}] size=30 class=\"form-control\"></div></div></div>";
            }
            if (!empty($search_fields_RET['numeric'])) {
                foreach ($search_fields_RET['numeric'] as $column)
                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]</label><div class=\"col-sm-8\"><div class=\"input-group\"><span class=\"input-group-addon\">Between</span><INPUT type=text name=cust_begin[{$column['COLUMN_NAME']}] maxlength=11 class=\"form-control\"><span class=\"input-group-addon\"> &amp; </span><INPUT type=text name=cust_end[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"form-control\"></div></div></div></div>";
            }
            echo '</div>';
            echo '<div class="row">';
            if (!empty($search_fields_RET['codeds'])) {
                foreach ($search_fields_RET['codeds'] as $column) {
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]</label><div class=\"col-md-8\">";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option) {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            echo "<OPTION value=\"$option[0]\">$option[1]</OPTION>";
                    }
                    echo '</SELECT>';
                    echo "</div></div></div>";
                }
            }
            if (!empty($search_fields_RET['select'])) {
                foreach ($search_fields_RET['select'] as $column) {
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]</label><div class=\"col-md-8\">";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo '</SELECT>';
                    echo "</div></div></div>";
                }
            }
            if (!empty($search_fields_RET['autos'])) {
                foreach ($search_fields_RET['autos'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]</label><div class=\"col-md-8\">";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option) {
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                        $options[$option] = true;
                    }
                    echo "<OPTION value=\"---\">---</OPTION>";
                    $options['---'] = true;
                    // add values found in current and previous year
                    $options_RET = DBGet(DBQuery('SELECT DISTINCT s.' . $column['COLUMN_NAME'] . ',upper(s.' . $column['COLUMN_NAME'] . ') AS KEEY FROM students s,student_enrollment sse WHERE sse.STUDENT_ID=s.STUDENT_ID AND (sse.SYEAR=\'' . UserSyear() . '\' OR sse.SYEAR=\'' . (UserSyear() - 1) . '\') AND ' . $column['COLUMN_NAME'] . ' IS NOT NULL ORDER BY KEEY'));
                    foreach ($options_RET as $option)
                        if ($option[$column['COLUMN_NAME']] != '' && !$options[$option[$column['COLUMN_NAME']]]) {
                            echo "<OPTION value=\"" . $option[$column['COLUMN_NAME']] . "\">" . $option[$column['COLUMN_NAME']] . "</OPTION>";
                            $options[$option[$column['COLUMN_NAME']]] = true;
                        }
                    echo '</SELECT>';
                    echo "</div></div></div>";
                }
            }
            if (!empty($search_fields_RET['edits'])) {
                foreach ($search_fields_RET['edits'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]</label><div class=\"col-md-8\">";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] class=\"form-control\"><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo "<OPTION value=\"---\">---</OPTION>";
                    echo "<OPTION value=\"~\">Other Value</OPTION>";
                    echo '</SELECT>';
                    echo "</div></div></div>";
                }
            }
            echo '</div>';
            echo '<div class="row">';
            if (!empty($search_fields_RET['date'])) {

                $data_counter = 1;
                foreach ($search_fields_RET['date'] as $column) {
                    echo "<div class=\"col-md-6\"><div class=\"form-group\"><label class=\"control-label text-right col-lg-4\">$column[TITLE]<label><div class=\"col-lg-8\"><div class=\"input-group\"><span class=\"input-group-addon\">Between</span>" . DateInputAY('', '_cust_begin[' . $column['COLUMN_NAME'] . ']', $data_counter) . '<span class="input-group-addon"> & </span>';
                    $data_counter++;
                    echo DateInputAY('', '_cust_end[' . $column['COLUMN_NAME'] . ']', $data_counter) . "</div></div></div></div>";
                    $data_counter++;
                }
            }
            if (!empty($search_fields_RET['radio'])) {
                echo "<TABLE>";

                echo "<TR><TD></TD><TD><table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table></TD><TD></TD><TD></TD><TD>";
                if (!empty($search_fields_RET['radio']) > 1)
                    echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table>";
                echo "</TD></TR>";

                $side = 1;
                foreach ($search_fields_RET['radio'] as $cust) {
                    if ($side % 2 != 0)
                        echo '<TR>';
                    echo "<TD ALIGN=RIGHT>$cust[TITLE]</TD><TD>
						<table border=0 cellpadding=0 cellspacing=0><tr><td width=25 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='' checked='checked' />
						</td><td width=30 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='Y' />
						</td><td width=25 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='N' />
						</td></tr></table>
						</TD><TD>&nbsp; &nbsp; &nbsp; &nbsp;</TD>";
                    if ($side % 2 == 0)
                        echo '</TR>';
                    $side++;
                }
                echo "</TABLE>";
            }
            echo '</div>';
            break;
    }
}

#------------------------------ this function is for user_staff----------------------

function SearchStaff($type, $extra = array())
{
    global $_openSIS;

    switch ($type) {
        case 'student_id':
            if ($_REQUEST['bottom_back']) {
                unset($_SESSION['student_id']);
            }
            if ($_SESSION['unset_student']) {
                unset($_REQUEST['student_id']);
                unset($_SESSION['unset_student']);
            }

            if ($_REQUEST['student_id']) {
                if ($_REQUEST['student_id'] != 'new') {
                    $_SESSION['student_id'] = $_REQUEST['student_id'];
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else {
                    unset($_SESSION['student_id']);
                }
            }

            if (!UserStudentID() && $_REQUEST['student_id'] != 'new' || $extra['new'] == true) {
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                include('modules/students/SearchInc.php');
            }
            break;

        case 'staff_id':
            // convert profile string to array for legacy compatibility
            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {

                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    unset($_SESSION['fn']);
                    $pro = DBGet(DBQuery("SELECT PROFILE_ID FROM staff  WHERE STAFF_ID='" . $_SESSION['staff_id'] . "'"));
                    if ($pro[1]['PROFILE_ID'] == 4)
                        $_SESSION['fn'] = 'user';
                    elseif ($pro[1]['PROFILE_ID'] == '' && $_REQUEST['staff_id'] == '')
                        $_SESSION['fn'] = '';
                    elseif ($pro[1]['PROFILE_ID'] == '' && $_REQUEST['staff_id'] != '')
                        $_SESSION['fn'] = 'staff';
                    else
                        $_SESSION['fn'] = 'staff';


                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                } else {
                    unset($_SESSION['staff_id']);
                }
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {
                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/users/SearchStaffInc.php');
            } else {
                if ($_SESSION['fn'] == 'user') {
                    if (!$_REQUEST['modfunc'])
                        $_REQUEST['modfunc'] = 'search_fnc';
                    $_REQUEST['next_modname'] = $_REQUEST['modname'];
                    if (!$_REQUEST['modname'])
                        $_REQUEST['modname'] = 'users/Search.php';
                    include('modules/users/SearchStaffInc.php');
                }
            }

            break;

        case 'general_info':
            echo '<tr><td align=right width=120>' . _lastName . '</td><td><input type=text name="last" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>' . _firstName . '</td><td><input type=text name="first" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>' . _studentId . '</td><td><input type=text name="stuid" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>National ID</td><td><input type=text name="altid" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>' . _address . '</td><td><input type=text name="addr" size=30 class="cell_floating"></td></tr>';

            $list = DBGet(DBQuery("SELECT DISTINCT TITLE,ID,SORT_ORDER FROM SCHOOL_GRADELEVELS WHERE SCHOOL_ID='" . UserSchool() . "' ORDER BY SORT_ORDER"));
            echo '<TR><TD align=right width=120>' . _grade . '</TD><TD><SELECT name=grade><OPTION value="" class="cell_floating">' . _notSpecified . '</OPTION>';
            foreach ($list as $value)
                echo "<OPTION value=$value[ID]>$value[TITLE]</OPTION>";
            echo '</SELECT></TD></TR>';
            break;

        case 'student_fields':

            $search_fields_RET = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM program_user_config puc,CUSTOM_FIELDS cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM='StudentFieldsSearch' AND puc.USER_ID='" . User('STAFF_ID') . "' AND puc.VALUE='Y' ORDER BY cf.SORT_ORDER,cf.TITLE"), array(), array('TYPE'));
            if (!$search_fields_RET)
                $search_fields_RET = DBGet(DBQuery("SELECT CONCAT('CUSTOM_',cf.ID) AS COLUMN_NAME,cf.TYPE,cf.TITLE,cf.SELECT_OPTIONS FROM custom_fields cf WHERE cf.ID IN ('200000000','200000001')"), array(), array('TYPE'));
            // edit needed
            if (!empty($search_fields_RET['text'])) {
                foreach ($search_fields_RET['text'] as $column)
                    echo "<TR><TD align=right width=120>$column[TITLE]</TD><TD><INPUT type=text name=cust[{$column['COLUMN_NAME']}] size=30 class=\"cell_floating\"></TD></TR>";
            }
            if (!empty($search_fields_RET['numeric'])) {
                foreach ($search_fields_RET['numeric'] as $column)
                    echo "<TR><TD align=right width=120>$column[TITLE]</TD><TD>Between <INPUT type=text name=cust_begin[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"cell_floating\"> &amp; <INPUT type=text name=cust_end[{$column['COLUMN_NAME']}] size=3 maxlength=11 class=\"cell_small\"></TD></TR>";
            }
            echo '</TABLE><TABLE>';
            if (!empty($search_fields_RET['codeds'])) {
                foreach ($search_fields_RET['codeds'] as $column) {
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<TR><TD align=right width=120>$column[TITLE]</TD><TD>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] style='max-width:250;'><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option) {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            echo "<OPTION value=\"$option[0]\">$option[1]</OPTION>";
                    }
                    echo '</SELECT>';
                    echo "</TD></TR>";
                }
            }
            if (!empty($search_fields_RET['select'])) {
                foreach ($search_fields_RET['select'] as $column) {
                    $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                    $options = explode("\r", $column['SELECT_OPTIONS']);

                    echo "<TR><TD align=right width=120>$column[TITLE]</TD><TD>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] style='max-width:250;'><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    foreach ($options as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo '</SELECT>';
                    echo "</TD></TR>";
                }
            }
            if (!empty($search_fields_RET['autos'])) {
                foreach ($search_fields_RET['autos'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    echo "<TR><TD align=right width=120>$column[TITLE]</TD><TD>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] style='max-width:250;'><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option) {
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                        $options[$option] = true;
                    }
                    echo "<OPTION value=\"---\">---</OPTION>";
                    $options['---'] = true;
                    // add values found in current and previous year
                    $options_RET = DBGet(DBQuery("SELECT DISTINCT s.$column[COLUMN_NAME],upper(s.$column[COLUMN_NAME]) AS KEEY FROM students s,student_enrollment sse WHERE sse.STUDENT_ID=s.STUDENT_ID AND (sse.SYEAR='" . UserSyear() . "' OR sse.SYEAR='" . (UserSyear() - 1) . "') AND $column[COLUMN_NAME] IS NOT NULL ORDER BY KEEY"));
                    foreach ($options_RET as $option)
                        if ($option[$column['COLUMN_NAME']] != '' && !$options[$option[$column['COLUMN_NAME']]]) {
                            echo "<OPTION value=\"" . $option[$column['COLUMN_NAME']] . "\">" . $option[$column['COLUMN_NAME']] . "</OPTION>";
                            $options[$option[$column['COLUMN_NAME']]] = true;
                        }
                    echo '</SELECT>';
                    echo "</TD></TR>";
                }
            }
            if (!empty($search_fields_RET['edits'])) {
                foreach ($search_fields_RET['edits'] as $column) {
                    if ($column['SELECT_OPTIONS']) {
                        $column['SELECT_OPTIONS'] = str_replace("\n", "\r", str_replace("\r\n", "\r", $column['SELECT_OPTIONS']));
                        $options_RET = explode("\r", $column['SELECT_OPTIONS']);
                    } else
                        $options_RET = array();

                    echo "<TR><TD align=right width=120>$column[TITLE]</TD><TD>";
                    echo "<SELECT name=cust[{$column['COLUMN_NAME']}] style='max-width:250;'><OPTION value=''>N/A</OPTION><OPTION value='!'>No Value</OPTION>";
                    $options = array();
                    foreach ($options_RET as $option)
                        echo "<OPTION value=\"$option\">$option</OPTION>";
                    echo "<OPTION value=\"---\">---</OPTION>";
                    echo "<OPTION value=\"~\">Other Value</OPTION>";
                    echo '</SELECT>';
                    echo "</TD></TR>";
                }
            }
            echo '</TABLE><TABLE>';
            if (!empty($search_fields_RET['date'])) {
                $data_counter = 1;
                foreach ($search_fields_RET['date'] as $column) {
                    echo "<TR><TD colspan=2>$column[TITLE]<BR> &nbsp; &nbsp; Between " . DateInputAY('', '_cust_begin[' . $column['COLUMN_NAME'] . ']', $data_counter) . ' & ';
                    $data_counter++;
                    echo DateInputAY('', '_cust_end[' . $column['COLUMN_NAME'] . ']', $data_counter) . "</TD></TR>";
                    $data_counter++;
                }
            }
            if (!empty($search_fields_RET['radio'])) {
                echo '<TR><TD colspan=2><BR></TD></TR>';
                echo "<TR><TD colspan=2><TABLE>";

                echo "<TR><TD></TD><TD><table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table></TD><TD></TD><TD></TD><TD>";
                if (!empty($search_fields_RET['radio']) > 1)
                    echo "<table border=0 cellpadding=0 cellspacing=0><tr><td width=25><b>All</b></td><td width=30><b>Yes</b></td><td width=25><b>No</b></td></tr></table>";
                echo "</TD></TR>";

                $side = 1;
                foreach ($search_fields_RET['radio'] as $cust) {
                    if ($side % 2 != 0)
                        echo '<TR>';
                    echo "<TD ALIGN=RIGHT>$cust[TITLE]</TD><TD>
						<table border=0 cellpadding=0 cellspacing=0><tr><td width=25 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='' checked='checked' />
						</td><td width=30 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='Y' />
						</td><td width=25 align=center>
						<input name='cust[{$cust['COLUMN_NAME']}]' type='radio' value='N' />
						</td></tr></table>
						</TD><TD>&nbsp; &nbsp; &nbsp; &nbsp;</TD>";
                    if ($side % 2 == 0)
                        echo '</TR>';
                    $side++;
                }
                echo "</TABLE></TD></TR>";
            }
            echo '</TABLE>';
            break;
    }
}

////////////staff list////////////////////////////
function stafflist($type, $extra = array())
{

    global $_openSIS;

    switch ($type) {
        case 'staff_id':
            // convert profile string to array for legacy compatibility

            if (!is_array($extra))
                $extra = array('profile' => $extra);
            if (!$_REQUEST['staff_id'] && User('PROFILE') != 'admin')
                $_REQUEST['staff_id'] = User('STAFF_ID');

            if ($_REQUEST['staff_id']) {

                if ($_REQUEST['staff_id'] != 'new') {
                    $_SESSION['staff_id'] = $_REQUEST['staff_id'];
                    unset($_SESSION['fn']);
                    $pro = DBGet(DBQuery("SELECT PROFILE_ID FROM staff S WHERE STAFF_ID='" . $_SESSION['staff_id'] . "'"));

                    if ($pro[1]['PROFILE_ID'] == 4)
                        $_SESSION['fn'] = 'user';
                    else
                        $_SESSION['fn'] = 'staff';
                    if ($_REQUEST['school_id'])
                        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
                }

                //                if (!$_REQUEST['_openSIS_PDF'])
                //                    echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
            }

            if (!UserStaffID() && $_REQUEST['staff_id'] != 'new' || $extra['new'] == true) {

                if (!$_REQUEST['modfunc'])
                    $_REQUEST['modfunc'] = 'search_fnc';
                $_REQUEST['next_modname'] = $_REQUEST['modname'];
                if (!$_REQUEST['modname'])
                    $_REQUEST['modname'] = 'users/Search.php';
                include('modules/attendance/StaffSearch.inc.php');
            }


            break;
        case 'general_info':
            $ethnicity_RET = DBGet(DBQuery("SELECT ETHNICITY_ID, ETHNICITY_NAME FROM ethnicity"));

            echo '<tr><td align=right width=120>' . _lastName . '</td><td><input type=text name="last" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>' . _firstName . '</td><td><input type=text name="first" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>Staff ID</td><td><input type=text name="staffid" size=30 class="cell_floating"></td></tr>';
            echo '<tr><td align=right width=120>Gender</td><td><SELECT name=gender style="max-width:1750;"><OPTION value="">N/A</OPTION><OPTION value="Male">Male</OPTION><OPTION value="Female">Female</OPTION></SELECT></td></tr>';
            echo '<tr><td align=right width=120>Ethnicity</td><td><SELECT name=ethnicity style="max-width:1750;"><OPTION value="">N/A</OPTION>';

            foreach ($ethnicity_RET as $ethnicity)
                echo "<OPTION value='" . $ethnicity['ETHNICITY_ID'] . "'>'" . $ethnicity['ETHNICITY_NAME'] . "'</OPTION>";

            echo '</SELECT></td></tr>';
            echo '<tr><td align=right width=120>Date of Birth</td><td>' . DateInputAY('', 'search[BIRTHDATE]', 1) . '</td></tr>';



            break;
    }
}
