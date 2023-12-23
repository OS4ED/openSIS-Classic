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
function GetStuList(&$extra)
{
    global $contacts_RET, $view_other_RET, $_openSIS, $select;
    $offset = 'GRADE_ID';
    $get_rollover_id = DBGet(DBQuery('SELECT ID FROM student_enrollment_codes WHERE SYEAR=' . UserSyear() . ' AND TYPE=\'Roll\' '));
    $get_rollover_id = $get_rollover_id[1]['ID'];
    if ((!$extra['SELECT_ONLY'] || strpos($extra['SELECT_ONLY'], $offset) !== false) && !$extra['functions']['GRADE_ID'])
        $functions = array('GRADE_ID' => 'GetGrade');
    else
        $functions = array();

    if ($extra['functions'])
        $functions += $extra['functions'];

    if (!$extra['DATE']) {
        $queryMP = UserMP();
        $extra['DATE'] = DBDate();
    } else
        $queryMP = GetCurrentMP('QTR', $extra['DATE'], false);

    if ($_REQUEST['expanded_view'] == 'true') {
        if (!$extra['columns_after'])
            $extra['columns_after'] = array();
        #############################################################################################
        //Commented as it crashing for Linux due to  Blank Database tables

        $view_fields_RET = DBGet(DBQuery('SELECT cf.ID,cf.TYPE,cf.TITLE FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM=\'StudentFieldsView\' AND puc.USER_ID=\'' . User('STAFF_ID') . '\' AND puc.VALUE=\'Y\''));
        #############################################################################################
        $view_address_RET = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE=\'ADDRESS\' AND USER_ID=\'' . User('STAFF_ID') . '\''));
        $view_address_RET = $view_address_RET[1]['VALUE'];
        $view_other_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE IN (\'PHONE\',\'HOME_PHONE\',\'GUARDIANS\',\'ALL_CONTACTS\') AND USER_ID=\'' . User('STAFF_ID') . '\''), array(), array('TITLE'));

        if (!count($view_fields_RET) && !isset($view_address_RET) && !isset($view_other_RET['CONTACT_INFO'])) {
            $extra['columns_after'] = array(
                'PHONE' => _phone,
                'GENDER' => _gender,
                'ETHNICITY' => _ethnicity,
                'ADDRESS' => _mailingAddress,
                'CITY' => _city,
                'STATE' => _state,
                'ZIPCODE' => _zipcode,
            ) + $extra['columns_after'];

            $select = ',s.PHONE,s.GENDER, (SELECT ETHNICITY_NAME FROM ethnicity WHERE ETHNICITY_ID = s.ETHNICITY_ID) AS ETHNICITY, s.ETHNICITY_ID,COALESCE((SELECT STREET_ADDRESS_1 FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail" ORDER BY ID DESC LIMIT 1),sa.STREET_ADDRESS_1) AS ADDRESS,COALESCE((SELECT CITY FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail" ORDER BY ID DESC LIMIT 1),sa.CITY) AS CITY,COALESCE((SELECT STATE FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail" ORDER BY ID DESC LIMIT 1),sa.STATE) AS STATE,COALESCE((SELECT ZIPCODE FROM student_address WHERE student_id=ssm.STUDENT_ID AND TYPE="Mail" ORDER BY ID DESC LIMIT 1),sa.ZIPCODE) AS ZIPCODE ';

            $extra['FROM'] = ' LEFT OUTER JOIN student_address sa ON (ssm.STUDENT_ID=sa.STUDENT_ID AND sa.TYPE=\'Home Address\' ) ' . $extra['FROM'];
            $functions['CONTACT_INFO'] = 'makeContactInfo';

            // if gender is converted to codeds type

            $extra['singular'] = 'Student Address';
            $extra['plural'] = 'Student Addresses';
            $extra2['NoSearchTerms'] = true;
            $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,s.PHONE,sa.ID AS ADDRESS_ID ';
            $extra2['FROM'] .= ',student_address sa,students_join_people sjp,people p  ';
            $extra2['WHERE'] .= ' AND sa.STUDENT_ID=sjp.STUDENT_ID AND sa.STUDENT_ID=sjp.STUDENT_ID AND (p.CUSTODY=\'Y\' OR sjp.IS_EMERGENCY=\'Y\') AND p.STAFF_ID=sjp.PERSON_ID  AND sa.STUDENT_ID=ssm.STUDENT_ID ';
            $extra2['ORDER_BY'] .= 'COALESCE(p.CUSTODY,\'N\') DESC';
            $extra2['group'] = array('STUDENT_ID', 'PERSON_ID');

            // EXPANDED VIEW AND ADDR BREAKS THIS QUERY ... SO, TURN 'EM OFF
            if (!$_REQUEST['_openSIS_PDF']) {
                $expanded_view = $_REQUEST['expanded_view'];
                $_REQUEST['expanded_view'] = false;
                $addr = $_REQUEST['addr'];
                unset($_REQUEST['addr']);
                $contacts_RET = GetStuList($extra2);
                $_REQUEST['expanded_view'] = $expanded_view;
                $_REQUEST['addr'] = $addr;
            } else
                unset($extra2['columns_after']['CONTACT_INFO']);
        } else {
            if ($view_other_RET['CONTACT_INFO'][1]['VALUE'] == 'Y' && !$_REQUEST['_openSIS_PDF']) {
                $select .= ',NULL AS CONTACT_INFO ';
                $extra['columns_after']['CONTACT_INFO'] = '<IMG SRC=assets/down_phone_button.gif border=0>';
                $functions['CONTACT_INFO'] = 'makeContactInfo';

                $extra2 = $extra;
                $extra2['NoSearchTerms'] = true;
                $extra2['SELECT'] = '';

                $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,a.PHONE';
                $extra2['FROM'] .= ',student_address a LEFT OUTER JOIN students_join_people sjp ON (a.STUDENT_ID=sjp.STUDENT_ID AND sjp.IS_EMERGENCY=\'Y\') LEFT OUTER JOIN people p ON (p.STAFF=sjp.PERSON_ID) ';
                $extra2['WHERE'] .= ' AND a.STUDENT_ID=sjp.a.STUDENT_ID AND sjp.STUDENT_ID=ssm.STUDENT_ID ';
                $extra2['ORDER_BY'] .= 'COALESCE(p.CUSTODY,\'N\') DESC';

                $extra2['group'] = array('STUDENT_ID', 'PERSON_ID');
                $extra2['functions'] = array();
                $extra2['link'] = array();

                // EXPANDED VIEW AND ADDR BREAKS THIS QUERY ... SO, TURN 'EM OFF
                $expanded_view = $_REQUEST['expanded_view'];
                $_REQUEST['expanded_view'] = false;
                $addr = $_REQUEST['addr'];
                unset($_REQUEST['addr']);
                $contacts_RET = GetStuList($extra2);
                $_REQUEST['expanded_view'] = $expanded_view;
                $_REQUEST['addr'] = $addr;
            }
            foreach ($view_fields_RET as $field) {
                $custom = DBGet(DBQuery('SHOW COLUMNS FROM students WHERE FIELD=\'CUSTOM_' . $field['ID'] . '\''));
                $custom = $custom[1];
                if ($custom) {
                    $extra['columns_after']['CUSTOM_' . $field['ID']] = $field['TITLE'];
                    if ($field['TYPE'] == 'date')
                        $functions['CUSTOM_' . $field['ID']] = 'ProperDate';
                    elseif ($field['TYPE'] == 'numeric')
                        $functions['CUSTOM_' . $field['ID']] = 'removeDot00';
                    elseif ($field['TYPE'] == 'codeds')
                        $functions['CUSTOM_' . $field['ID']] = 'DeCodeds';
                    $select .= ',s.CUSTOM_' . $field['ID'];
                } else {
                    $custom_stu = DBGet(DBQuery('SELECT TYPE,TITLE FROM custom_fields WHERE ID=\'' . $field['ID'] . '\''));
                    $custom_stu = $custom_stu[1];
                    if ($custom_stu['TYPE'] == 'date')
                        $functions[strtolower(str_replace(" ", "_", $custom_stu['TITLE']))] = 'ProperDate';
                    elseif ($custom_stu['TYPE'] == 'numeric')
                        $functions[strtolower(str_replace(" ", "_", $custom_stu['TITLE']))] = 'removeDot00';
                    elseif ($custom_stu['TYPE'] == 'codeds')
                        $functions[strtolower(str_replace(" ", "_", $custom_stu['TITLE']))] = 'DeCodeds';
                    $select .= ',s.' . strtoupper(str_replace(" ", "_", $custom_stu['TITLE']));

                    $extra['columns_after'] += array(strtoupper(str_replace(" ", "_", $custom_stu['TITLE'])) => $custom_stu['TITLE']);
                }
            }
            if ($view_address_RET) {
                if ($view_address_RET == 'RESIDENCE')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\')  ' . $extra['FROM'];
                elseif ($view_address_RET == 'MAILING')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Mail\') ' . $extra['FROM'];
                elseif ($view_address_RET == 'BUS_PICKUP')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.BUS_PICKUP=\'Y\') ' . $extra['FROM'];
                else
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.BUS_DROPOFF=\'Y\') ' . $extra['FROM'];

                $extra['columns_after'] += array('ADDRESS' => ucwords(strtolower(str_replace('_', ' ', $view_address_RET))) . ' Address', 'CITY' => 'City', 'STATE' => 'State', 'ZIPCODE' => 'Zipcode');

                $select .= ',sam.ID as ADDRESS_ID,sam.STREET_ADDRESS_1 as ADDRESS,sam.CITY,sam.STATE,sam.ZIPCODE,s.PHONE,ssm.STUDENT_ID AS PARENTS';

                $extra['singular'] = 'Student Address';
                $extra['plural'] = 'Student Addresses';

                if ($view_other_RET['HOME_PHONE'][1]['VALUE'] == 'Y') {
                    $functions['PHONE'] = 'makePhone';
                    $extra['columns_after']['PHONE'] = 'Home Phone';
                }
                if ($view_other_RET['GUARDIANS'][1]['VALUE'] == 'Y' || $view_other_RET['ALL_CONTACTS'][1]['VALUE'] == 'Y') {
                    $functions['PARENTS'] = 'makeParents';
                    if ($view_other_RET['ALL_CONTACTS'][1]['VALUE'] == 'Y')
                        $extra['columns_after']['PARENTS'] = 'Contacts';
                    else
                        $extra['columns_after']['PARENTS'] = 'Guardians';
                }
            } elseif ($_REQUEST['addr'] || $extra['addr']) {
                $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\' ) ' . $extra['FROM'];
                $distinct = 'DISTINCT ';
            }
        }

        $extra['SELECT'] .= $select;
    } elseif ($_REQUEST['addr'] || $extra['addr']) {
        $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\' ) ' . $extra['FROM'];
        $distinct = 'DISTINCT ';
    }
    $stu_arr = array();
    if (($_REQUEST['include_inactive'] == 'Y' && $_REQUEST['_search_all_schools'] == 'Y'))
        $tot_stu = DBGet(DBQuery('SELECT STUDENT_ID , ID FROM student_enrollment WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ORDER BY START_DATE DESC'));
    if (($_REQUEST['include_inactive'] == 'Y' && $_REQUEST['_search_all_schools'] != 'Y'))
        $tot_stu = DBGet(DBQuery('SELECT STUDENT_ID , ID FROM student_enrollment WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY START_DATE DESC'));
    $tot_stu_id = '';
    if (is_countable($tot_stu) && !empty($tot_stu)) {
        foreach ($tot_stu as $key => $value) {
            if (in_array($value['STUDENT_ID'], $stu_arr) == false) {
                $stu_arr['STUDENT_ID'] = $value['STUDENT_ID'];
                $tot_stu_id .= $value['ID'] . ',';
            }
        }
        $tot_stu_id = substr($tot_stu_id, 0, -1);
    }
    if ($tot_stu_id == '')
        $tot_stu_id = 0;
    switch (User('PROFILE')) {
        case 'admin':

            $sql = 'SELECT ';
            $allSQL = 'SELECT s.STUDENT_ID ';
            if ($extra['DISTINCT'])
                $sql .= 'DISTINCT ';
            if ($extra['SELECT_ONLY'])
                $sql .= $extra['SELECT_ONLY'];
            else {
                if (Preferences('NAME') == 'Common')
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
                else
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,ssm.SCHOOL_ID,s.ALT_ID,ssm.SCHOOL_ID AS LIST_SCHOOL_ID,ssm.GRADE_ID' . $extra['SELECT'];

                if ($_REQUEST['include_inactive'] == 'Y')
                    $sql .= ',' . db_case(array('(ssm.SYEAR=\'' . UserSyear() . '\'  AND (ssm.START_DATE IS NOT NULL AND s.IS_DISABLE IS NULL AND ((\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL) ) OR ssm.DROP_CODE=\'' . $get_rollover_id . '\' )) ', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE ';
            }
            $sql .= ' FROM students s ';
            $allSQL .= ' FROM students s ';

            //////////////extra field table start////////////////////

            if ($_REQUEST['username']) {
                $sql .= ',login_authentication la ';
                $allSQL .= ',login_authentication la ';
            }
            //////////////extra field table start////////////////////
            if ($_REQUEST['mp_comment']) {
                $sql .= ',student_mp_comments smc ';
                $allSQL .= ',student_mp_comments smc ';
            }
            if ($_REQUEST['goal_title'] || $_REQUEST['goal_description']) {
                $sql .= ',student_goal g ';
                $allSQL .= ',student_goal g ';
            }
            if ($_REQUEST['progress_name'] || $_REQUEST['progress_description']) {
                $sql .= ',student_goal_progress p ';
                $allSQL .= ',student_goal_progress p ';
            }
            if ($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
                $sql .= ',student_medical_notes smn ';
                $allSQL .= ',student_medical_notes smn ';
            }
            if ($_REQUEST['type'] || $_REQUEST['imm_comments'] || $_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
                $sql .= ',student_immunization sm ';
                $allSQL .= ',student_immunization sm ';
            }
            if ($_REQUEST['med_alrt_title'] || $_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
                $sql .= ',student_medical_alerts sma ';
                $allSQL .= ',student_medical_alerts sma ';
            }
            if ($_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments'] || $_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
                $sql .= ',student_medical_visits smv ';
                $allSQL .= ',student_medical_visits smv ';
            }
            if (stripos($extra['FROM'], "student_enrollment ssm") === false) {
                $sql .= ',student_enrollment ssm ';
                $allSQL .= ',student_enrollment ssm ';
            }
            if ($_REQUEST['modname'] == 'scheduling/PrintSchedules.php' && $_REQUEST['search_modfunc'] == 'list') {
                // DDDDD
                if (isset($_SESSION['student_id']) && $_REQUEST['modfunc'] == 'save') {
                    $sql .= $extra['FROM'] . ' WHERE sr.STUDENT_ID=ssm.STUDENT_ID AND s.student_id=ssm.student_id';
                    $allSQL .= $extra['FROM'] . ' WHERE sr.STUDENT_ID=ssm.STUDENT_ID AND s.student_id=ssm.student_id';
                } else {
                    $sql .= $extra['FROM'] . ',schedule sr ' . ' WHERE sr.STUDENT_ID=ssm.STUDENT_ID AND s.student_id=ssm.student_id';
                    $allSQL .= $extra['FROM'] . ',schedule sr ' . ' WHERE sr.STUDENT_ID=ssm.STUDENT_ID AND s.student_id=ssm.student_id';
                }
            } else {
                $sql .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID  ';
                $allSQL .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID  ';
            }
            // if($_REQUEST['modname'] =='scheduling/PrintSchedules.php' && $_REQUEST['search_modfunc'] =='list')
            // $sql.=$extra['FROM'] . ' WHERE sr.STUDENT_ID=ssm.STUDENT_ID ';
            if ($_REQUEST['modname'] != 'students/StudentReenroll.php') {
                if ($_REQUEST['include_inactive'] == 'Y' || $_REQUEST['_search_all_schools'] == 'Y') {
                    if ($tot_stu_id != 0) {
                        $sql .= ' AND ssm.ID IN (' . $tot_stu_id . ')';
                        $allSQL .= ' AND ssm.ID IN (' . $tot_stu_id . ')';
                    }
                    $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
                    $allSQL .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
                }
                if (!$_REQUEST['include_inactive']) {
                    //$sql .= $_SESSION['inactive_stu_filter'] = ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ((ssm.START_DATE IS NOT NULL AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL) AND \'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'>=ssm.START_DATE) OR ssm.DROP_CODE=' . $get_rollover_id . ' ) ';
                    $sql .= $_SESSION['inactive_stu_filter'] = ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)  OR ssm.DROP_CODE=\'' . $get_rollover_id . '\' ) ';
                    $allSQL .= $_SESSION['inactive_stu_filter'] = ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)  OR ssm.DROP_CODE=' . $get_rollover_id . ' ) ';
                }
                if ($_REQUEST['address_group'])
                    $extra['columns_after']['CHILD'] = 'Parent';
                if (UserSchool() && $_REQUEST['_search_all_schools'] != 'Y') {
                    $sql .= ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
                    $allSQL .= ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
                } else {
                    $sql .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';
                    $allSQL .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';
                    $extra['columns_after']['LIST_SCHOOL_ID'] = 'School';
                    $functions['LIST_SCHOOL_ID'] = 'GetSchool';
                }

                if (!$extra['SELECT_ONLY'] && $_REQUEST['include_inactive'] == 'Y')
                    $extra['columns_after']['ACTIVE'] = 'Status';
            } else {
                if ($_REQUEST['_search_all_schools'] == 'Y') {
                    $sql .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';
                    $allSQL .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';
                } else {
                    $sql .= ' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
                    $allSQL .= ' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
                }
            }
            if ($_REQUEST['modname'] == 'scheduling/PrintSchedules.php' && $_REQUEST['search_modfunc'] == 'list' && $_REQUEST['modfunc'] != 'save') {
                $extra['GROUP'] = 's.STUDENT_ID';
            }

            break;

        case 'teacher':

            $sql = 'SELECT ';
            $allSQL = 'SELECT s.STUDENT_ID ';
            if ($extra['SELECT_ONLY'])
                $sql .= $extra['SELECT_ONLY'];
            else {
                if (Preferences('NAME') == 'Common')
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
                else
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];

                if ($_REQUEST['include_inactive'] == 'Y') {
                    if ($_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'scheduling/PrintClassLists.php' || $_REQUEST['modname'] == 'scheduling/PrintSchedules.php' || $_REQUEST['modname'] == 'grades/Grades.php' || $_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/Grades.php') {
                        $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL OR ssm.DROP_CODE=\'' . $get_rollover_id . '\' ) AND s.IS_DISABLE IS NULL )', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE';
                        $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (cp.END_DATE<=ss.END_DATE ) )', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE_SCHEDULE';
                    } else {
                        $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL) AND s.IS_DISABLE IS NULL )', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE';
                        $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE ) )', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE_SCHEDULE';
                    }
                }
            }

            $sql .= ' FROM students s,course_periods cp,schedule ss ';
            $allSQL .= ' FROM students s,course_periods cp,schedule ss ';
            if ($_REQUEST['mp_comment']) {
                $sql .= ',student_mp_comments smc ';
                $allSQL .= ',student_mp_comments smc ';
            }
            if ($_REQUEST['goal_title'] || $_REQUEST['goal_description']) {
                $sql .= ',student_goal g ';
                $allSQL .= ',student_goal g ';
            }
            if ($_REQUEST['progress_name'] || $_REQUEST['progress_description']) {
                $sql .= ',student_goal_progress p ';
                $allSQL .= ',student_goal_progress p ';
            }
            if ($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
                $sql .= ',student_medical_notes smn ';
                $allSQL .= ',student_medical_notes smn ';
            }
            if ($_REQUEST['type'] || $_REQUEST['imm_comments'] || $_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
                $sql .= ',student_immunization sm ';
                $allSQL .= ',student_immunization sm ';
            }
            if ($_REQUEST['med_alrt_title'] || $_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
                $sql .= ',student_medical_alerts sma ';
                $allSQL .= ',student_medical_alerts sma ';
            }
            if ($_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments'] || $_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
                $sql .= ',student_medical_visits smv ';
                $allSQL .= ',student_medical_visits smv ';
            }
            $sql .= ' ,student_enrollment ssm ';
            $allSQL .= ' ,student_enrollment ssm ';
            $sql .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID
					AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR
					AND (ss.MARKING_PERIOD_ID IN (' . GetAllMP_Mod('', $queryMP) . ')   OR (ss.START_DATE<=\'' . date('Y-m-d') . '\'   AND (ss.END_DATE>=\'' . date('Y-m-d') . '\'  OR ss.END_DATE IS NULL))  OR (ss.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' AND ss.MARKING_PERIOD_ID IS NULL))
					AND (cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'
					AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID';
            $allSQL .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID
                    AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR
                    AND (ss.MARKING_PERIOD_ID IN (' . GetAllMP_Mod('', $queryMP) . ')   OR (ss.START_DATE<=\'' . date('Y-m-d') . '\'   AND (ss.END_DATE>=\'' . date('Y-m-d') . '\'  OR ss.END_DATE IS NULL))  OR (ss.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' AND ss.MARKING_PERIOD_ID IS NULL))
                    AND (cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'
                    AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID';

            if ($_REQUEST['include_inactive'] == 'Y' && $_REQUEST['_search_all_schools'] != 'Y') {
                if ($tot_stu_id != 0) {
                    $sql .= ' AND ssm.ID IN (' . $tot_stu_id . ')';
                    $allSQL .= ' AND ssm.ID IN (' . $tot_stu_id . ')';
                }
                $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
                $allSQL .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
                $sql .= ' AND ss.START_DATE=(SELECT START_DATE FROM schedule WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND (MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ') OR (COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' AND MARKING_PERIOD_ID IS NULL))  AND COURSE_ID=cp.COURSE_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID ORDER BY START_DATE DESC LIMIT 1)';
                $allSQL .= ' AND ss.START_DATE=(SELECT START_DATE FROM schedule WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND (MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ') OR (COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\' AND MARKING_PERIOD_ID IS NULL))  AND COURSE_ID=cp.COURSE_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID ORDER BY START_DATE DESC LIMIT 1)';
            } else {
                if ($_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'scheduling/PrintClassLists.php' || $_REQUEST['modname'] == 'scheduling/PrintSchedules.php' || $_REQUEST['modname'] == 'grades/Grades.php' || $_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/Grades.php') {
                    $sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL OR ssm.DROP_CODE=\'' . $get_rollover_id . '\'))';
                    $allSQL .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL OR ssm.DROP_CODE=' . $get_rollover_id . '))';
                } else {
                    $sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))';
                    $allSQL .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))';
                }
                // if ($_REQUEST['modname']=='users/TeacherPrograms.php?include=grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'grades/InputFinalGrades.php' || $_REQUEST['modname'] =='scheduling/PrintClassLists.php' || $_REQUEST['modname'] =='scheduling/PrintSchedules.php')
                if ($_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'grades/InputFinalGrades.php' || $_REQUEST['modname'] == 'scheduling/PrintClassLists.php' || $_REQUEST['modname'] == 'scheduling/PrintSchedules.php' || $_REQUEST['modname'] == 'grades/Grades.php' || $_REQUEST['modname'] == 'users/TeacherPrograms.php?include=grades/Grades.php') {
                    $sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (cp.end_date<=ss.END_DATE OR ss.END_DATE IS NULL OR  ss.END_DATE > \'' . date('Y-m-d') . '\' ))';
                    $allSQL .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (cp.end_date<=ss.END_DATE OR ss.END_DATE IS NULL OR  ss.END_DATE > \'' . date('Y-m-d') . '\' ))';
                } else {
                    $sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))';
                    $allSQL .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))';
                }
            }
            if ($_REQUEST['include_inactive'] == 'Y' && $_REQUEST['_search_all_schools'] == 'Y') {
                if ($tot_stu_id != 0) {
                    $sql .= ' AND ssm.ID IN (' . $tot_stu_id . ')';
                    $allSQL .= ' AND ssm.ID IN (' . $tot_stu_id . ')';
                }
                $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
                $allSQL .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
            }
            if (!$extra['SELECT_ONLY'] && $_REQUEST['include_inactive'] == 'Y') {
                $extra['columns_after']['ACTIVE'] = 'School Status';
                $extra['columns_after']['ACTIVE_SCHEDULE'] = 'Course Status';
            }
            break;

        case 'parent':
        case 'student':

            $sql = 'SELECT ';
            $allSQL = 'SELECT s.STUDENT_ID ';
            if ($extra['SELECT_ONLY'])
                $sql .= $extra['SELECT_ONLY'];
            else {
                if (Preferences('NAME') == 'Common')
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
                else
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];
            }
            if ($_REQUEST['modname'] == 'grades/GPARankList.php') {
                $sql .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
					WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate() . '\'>=ssm.START_DATE))';
                $allSQL .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
                    WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate() . '\'>=ssm.START_DATE))';
            } else {
                $sql .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
					WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate() . '\'>=ssm.START_DATE)) AND ssm.STUDENT_ID' . ($extra['ASSOCIATED'] ? ' IN (SELECT STUDENT_ID FROM students_join_people WHERE PERSON_ID=\'' . $extra['ASSOCIATED'] . '\')' : '=\'' . UserStudentID() . '\'');
                $allSQL .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
                    WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate() . '\'>=ssm.START_DATE)) AND ssm.STUDENT_ID' . ($extra['ASSOCIATED'] ? ' IN (SELECT STUDENT_ID FROM students_join_people WHERE PERSON_ID=\'' . $extra['ASSOCIATED'] . '\')' : '=\'' . UserStudentID() . '\'');
            }
            break;
        default:
            exit('Error');
    }
    $sql_home_address = array();
    $student_contact_ids = '';
    if (isset($_REQUEST['home_address_1']) && $_REQUEST['home_address_1'] != '') {
        $sql_home_address[] = 'street_address_1=\'' . singleQuoteReplace("", "", $_REQUEST['home_address_1']) . '\'';
    }
    if (isset($_REQUEST['home_address_2']) && $_REQUEST['home_address_2'] != '') {
        $sql_home_address[] = 'street_address_2=\'' . singleQuoteReplace("", "", $_REQUEST['home_address_2']) . '\'';
    }
    if (isset($_REQUEST['home_city']) && $_REQUEST['home_city'] != '') {
        $sql_home_address[] = 'city=\'' . singleQuoteReplace("", "", $_REQUEST['home_city']) . '\'';
    }
    if (isset($_REQUEST['home_state']) && $_REQUEST['home_state'] != '') {
        $sql_home_address[] = 'state=\'' . singleQuoteReplace("", "", $_REQUEST['home_state']) . '\'';
    }
    if (isset($_REQUEST['home_zip']) && $_REQUEST['home_zip'] != '') {
        $sql_home_address[] = 'zipcode=\'' . singleQuoteReplace("", "", $_REQUEST['home_zip']) . '\'';
    }
    if (isset($_REQUEST['home_state']) && $_REQUEST['home_state'] != '') {
        $sql_home_address[] = 'state=\'' . singleQuoteReplace("", "", $_REQUEST['home_state']) . '\'';
    }
    if (isset($_REQUEST['home_busno']) && $_REQUEST['home_busno'] != '') {
        $sql_home_address[] = 'bus_no=\'' . singleQuoteReplace("", "", $_REQUEST['home_busno']) . '\'';
    }
    if (isset($_REQUEST['home_bus_pickup']) && $_REQUEST['home_bus_pickup'] == 'Y') {
        $sql_home_address[] = 'bus_pickup=\'' . singleQuoteReplace("", "", $_REQUEST['home_bus_pickup']) . '\'';
    }
    if (isset($_REQUEST['home_bus_droppoff']) && $_REQUEST['home_bus_droppoff'] == 'Y') {
        $sql_home_address[] = 'bus_dropoff=\'' . singleQuoteReplace("", "", $_REQUEST['home_bus_droppoff']) . '\'';
    }
    if (count($sql_home_address) > 0) {
        $ret_students = DBGet(DBQuery('SELECT GROUP_CONCAT(STUDENT_ID) as STUDENT_IDS FROM student_address WHERE TYPE=\'Home Address\' AND ' . implode(' AND ', $sql_home_address)));
        if ($ret_students[1]['STUDENT_IDS'] != '')
            $student_contact_ids .= $ret_students[1]['STUDENT_IDS'];
        else
            $student_contact_ids .= 0;
    }
    $sql_mail_address = array();
    if (isset($_REQUEST['mail_address_1']) && $_REQUEST['mail_address_1'] != '') {
        $sql_mail_address[] = 'street_address_1=\'' . singleQuoteReplace("", "", $_REQUEST['mail_address_1']) . '\'';
    }
    if (isset($_REQUEST['mail_address_2']) && $_REQUEST['mail_address_2'] != '') {
        $sql_mail_address[] = 'street_address_2=\'' . singleQuoteReplace("", "", $_REQUEST['mail_address_2']) . '\'';
    }
    if (isset($_REQUEST['mail_city']) && $_REQUEST['mail_city'] != '') {
        $sql_mail_address[] = 'city=\'' . singleQuoteReplace("", "", $_REQUEST['mail_city']) . '\'';
    }
    if (isset($_REQUEST['mail_state']) && $_REQUEST['mail_state'] != '') {
        $sql_mail_address[] = 'state=\'' . singleQuoteReplace("", "", $_REQUEST['mail_state']) . '\'';
    }
    if (isset($_REQUEST['home_zip']) && $_REQUEST['home_zip'] != '') {
        $sql_mail_address[] = 'zipcode=\'' . singleQuoteReplace("", "", $_REQUEST['mail_zip']) . '\'';
    }
    if (isset($_REQUEST['home_state']) && $_REQUEST['home_state'] != '') {
        $sql_mail_address[] = 'state=\'' . singleQuoteReplace("", "", $_REQUEST['mail_state']) . '\'';
    }
    if (count($sql_mail_address) > 0) {
        $ret_students = DBGet(DBQuery('SELECT GROUP_CONCAT(STUDENT_ID) as STUDENT_IDS FROM student_address WHERE TYPE=\'Mail\' AND ' . implode(' AND ', $sql_mail_address)));
        if ($ret_students[1]['STUDENT_IDS'] != '')
            $student_contact_ids .= $ret_students[1]['STUDENT_IDS'];
        else
            $student_contact_ids .= 0;
    }


    $sql_primary = array();
    if (isset($_REQUEST['primary_realtionship']) && $_REQUEST['primary_realtionship'] != '') {
        $sql_primary[] = 'relationship=\'' . singleQuoteReplace("", "", $_REQUEST['primary_realtionship']) . '\'';
    }
    if (isset($_REQUEST['primary_first_name']) && $_REQUEST['primary_first_name'] != '') {
        $sql_primary[] = 'first_name=\'' . singleQuoteReplace("", "", $_REQUEST['primary_first_name']) . '\'';
    }
    if (isset($_REQUEST['primary_last_name']) && $_REQUEST['primary_last_name'] != '') {
        $sql_primary[] = 'last_name=\'' . singleQuoteReplace("", "", $_REQUEST['primary_last_name']) . '\'';
    }
    if (isset($_REQUEST['primary_home_phone']) && $_REQUEST['primary_home_phone'] != '') {
        $sql_primary[] = 'home_phone=\'' . singleQuoteReplace("", "", $_REQUEST['primary_home_phone']) . '\'';
    }
    if (isset($_REQUEST['primary_work_phone']) && $_REQUEST['primary_work_phone'] != '') {
        $sql_primary[] = 'work_phone=\'' . singleQuoteReplace("", "", $_REQUEST['primary_work_phone']) . '\'';
    }
    if (isset($_REQUEST['primary_mobile_phone']) && $_REQUEST['primary_mobile_phone'] != '') {
        $sql_primary[] = 'cell_phone=\'' . singleQuoteReplace("", "", $_REQUEST['primary_mobile_phone']) . '\'';
    }
    if (isset($_REQUEST['primary_email']) && $_REQUEST['primary_email'] != '') {
        $sql_primary[] = 'email=\'' . singleQuoteReplace("", "", $_REQUEST['primary_email']) . '\'';
    }
    if (count($sql_primary) > 0) {
        $ret_students = DBGet(DBQuery('SELECT GROUP_CONCAT(sjp.STUDENT_ID) as STUDENT_IDS FROM students_join_people sjp,people p WHERE sjp.PERSON_ID=p.STAFF_ID AND sjp.EMERGENCY_TYPE=\'Primary\' AND ' . implode(' AND ', $sql_primary)));
        if ($ret_students[1]['STUDENT_IDS'] != '')
            $student_contact_ids .= $ret_students[1]['STUDENT_IDS'];
        else
            $student_contact_ids .= 0;
    }

    $sql_secondary = array();
    if (isset($_REQUEST['secondary_realtionship']) && $_REQUEST['secondary_realtionship'] != '') {
        $sql_secondary[] = 'relationship=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_realtionship']) . '\'';
    }
    if (isset($_REQUEST['secondary_first_name']) && $_REQUEST['secondary_first_name'] != '') {
        $sql_secondary[] = 'first_name=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_first_name']) . '\'';
    }
    if (isset($_REQUEST['secondary_last_name']) && $_REQUEST['secondary_last_name'] != '') {
        $sql_secondary[] = 'last_name=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_last_name']) . '\'';
    }
    if (isset($_REQUEST['secondary_home_phone']) && $_REQUEST['secondary_home_phone'] != '') {
        $sql_secondary[] = 'home_phone=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_home_phone']) . '\'';
    }
    if (isset($_REQUEST['secondary_work_phone']) && $_REQUEST['secondary_work_phone'] != '') {
        $sql_secondary[] = 'work_phone=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_work_phone']) . '\'';
    }
    if (isset($_REQUEST['secondary_mobile_phone']) && $_REQUEST['secondary_mobile_phone'] != '') {
        $sql_secondary[] = 'cell_phone=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_mobile_phone']) . '\'';
    }
    if (isset($_REQUEST['secondary_email']) && $_REQUEST['secondary_email'] != '') {
        $sql_secondary[] = 'email=\'' . singleQuoteReplace("", "", $_REQUEST['secondary_email']) . '\'';
    }
    if (count($sql_secondary) > 0) {
        $ret_students = DBGet(DBQuery('SELECT GROUP_CONCAT(sjp.STUDENT_ID) as STUDENT_IDS FROM students_join_people sjp,people p WHERE sjp.PERSON_ID=p.STAFF_ID AND sjp.EMERGENCY_TYPE=\'Secondary\' AND ' . implode(' AND ', $sql_secondary)));
        if ($ret_students[1]['STUDENT_IDS'] != '')
            $student_contact_ids .= $ret_students[1]['STUDENT_IDS'];
        else
            $student_contact_ids .= 0;
    }
    if ($student_contact_ids != '') {
        $sql .= ' AND s.STUDENT_ID IN (' . $student_contact_ids . ')';
        $allSQL .= ' AND s.STUDENT_ID IN (' . $student_contact_ids . ')';
    }

    if ($expanded_view == true) {
        $custom_str = CustomFields('where', '', 1);
        if ($custom_str != '')
            $_SESSION['custom_count_sql'] = $custom_str;

        $sql .= $custom_str;
        $allSQL .= $custom_str;
    } elseif ($expanded_view == false) {
        $custom_str = CustomFields('where', '', 2);
        if ($custom_str != '')
            $_SESSION['custom_count_sql'] = $custom_str;

        $sql .= $custom_str;
        $allSQL .= $custom_str;
    } else {
        $custom_str = CustomFields('where');
        if ($custom_str != '')
            $_SESSION['custom_count_sql'] = $custom_str;

        $sql .= $custom_str;
        $allSQL .= $custom_str;
    }

    $sql .= $extra['WHERE'] . ' ';
    $allSQL .= $extra['WHERE'] . ' ';

    if ($_REQUEST['include_inactive'] != 'Y') {
        $sql .= 'AND s.IS_DISABLE IS NULL';
        $allSQL .= 'AND s.IS_DISABLE IS NULL';
    }

    $sql = appendSQL($sql, $extra);
    $allSQL = appendAllSQL($allSQL, $extra);

    //        TODO               Modification Required


    if ($extra['GROUP'])
        $sql .= ' GROUP BY ' . $extra['GROUP'];

    if (!$extra['ORDER_BY'] && !$extra['SELECT_ONLY']) {
        if (Preferences('SORT') == 'Grade')
            $sql .= ' ORDER BY (SELECT SORT_ORDER FROM school_gradelevels WHERE ID=ssm.GRADE_ID),FULL_NAME';
        else
            $sql .= ' ORDER BY FULL_NAME';
        $sql .= $extra['ORDER'];
    } elseif ($extra['ORDER_BY'] && !($_SESSION['stu_search']['sql'] && $_REQUEST['return_session']))
        $sql .= ' ORDER BY ' . $extra['ORDER_BY'];

    $_SESSION['mainSQL'] = array(
        'SQL'           =>  $sql,
        'FUNCTIONS'     =>  $functions,
        'EXTRA_GROUP'   =>  $extra['group']
    );

    # (?) Initially, $allSQL was programmed to SELECT only the STUDENT_ID field 
    # to obtain the total student count. But, a bug was reported at the time of
    # navigating students using the "First", "Previous", "Next", "Last" arrows.
    # As, $allSQL doesn't do SELECT on other fields or doesn't do ORDER BY or 
    # GROUP BY, the student sequence doesn't match with $sql resulting in wrong 
    # navigation in students profiles.
    #
    # As a workaround, $all_return will hold the results from $sql, instead of 
    # $allSQL. However, the query building of $allSQL will be kept intact for 
    # any future reference.
    #
    # $all_return = DBGet(DBQuery($allSQL), $functions, $extra['GROUP']);

    $all_return = DBGet(DBQuery($sql), $functions, $extra['GROUP']);

    unset($_SESSION['ALL_RETURN']);
    $_SESSION['ALL_RETURN'] = $all_return;

    $all_stu_res = [];
    foreach (DBGet(DBQuery($sql)) as $xy) {
        $all_stu_res[] = $xy['STUDENT_ID'];
    }
    $all_stu_res = implode(',', $all_stu_res);
    echo '<input type=hidden name=all_stu_res id=all_stu_res value=\'' . $all_stu_res . '\'>';
    echo '<input type=hidden name=checked_all id=checked_all value=false>';

    if ($extra['DEBUG'] === true)
        echo '<!--' . $sql . '-->';

    if (checkPagesForPrint($_REQUEST['modname']) && $_REQUEST['modfunc'] == 'save' || ($_REQUEST['modname'] == 'scheduling/Schedule.php' && UserStudentID() != '')) {
        $_SESSION['AL_RES_COUNT'] = $_SESSION['AL_RES_COUNT'];
    } else {
        // $_SESSION['AL_RES_COUNT'] = $all_return[1]['STUDENT_COUNT'];
        $_SESSION['AL_RES_COUNT'] = count($all_return);
    }

    if ($extra['LIMIT']) {
        $sql = $sql . ' LIMIT ' . $extra['LIMIT'];
    } else {
        if ((!$_REQUEST['_openSIS_PDF'] || $_REQUEST['_openSIS_PDF'] == false) && !checkNoNeedPaging($_REQUEST['modname'])) {
            $sql = $sql . ' LIMIT 0,50';
        }
    }

    // echo "<br><br>" . $sql . "<br><br>";

    $return = DBGet(DBQuery($sql), $functions, $extra['group']);
    $_SESSION['count_stu'] = count($return);
    if ($_REQUEST['modname'] == 'students/Student.php' && $_REQUEST['search_modfunc'] == 'list')
        $_SESSION['total_stu'] = $_SESSION['count_stu'];

    return $return;
}

function makeContactInfo($student_id, $column)
{
    global $THIS_RET, $contacts_RET, $tipmessage;

    if (count($contacts_RET[$THIS_RET['STUDENT_ID']])) {
        foreach ($contacts_RET[$THIS_RET['STUDENT_ID']] as $person) {
            if ($person[1]['FIRST_NAME'] || $person[1]['LAST_NAME'])
                $tipmessage .= '' . $person[1]['STUDENT_RELATION'] . ': ' . $person[1]['FIRST_NAME'] . ' ' . $person[1]['LAST_NAME'] . ' | ';
            $tipmessage .= '';
            if ($person[1]['PHONE'])
                $tipmessage .= ' ' . $person[1]['PHONE'] . '';
            foreach ($person as $info) {
                if ($info['TITLE'] || $info['VALUE'])
                    $tipmessage .= '' . $info['TITLE'] . '' . $info['VALUE'] . '';
            }
            $tipmessage .= '';
        }
    } else
        $tipmessage = 'This student has no contact information.';
    return button('phone', '', '# alt="' . $tipmessage . '" title="' . $tipmessage . '"');
}

function removeDot00($value, $column)
{
    return str_replace('.00', '', $value);
}

function makePhone($phone, $column = '')
{
    global $THIS_RET;
    $return = '';
    if (strlen($phone) == 10)
        $return .= '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 7) . '-' . substr($phone, 7);
    if (strlen($phone) == '7')
        $return .= substr($phone, 0, 3) . '-' . substr($phone, 3);
    else
        $return .= $phone;

    return $return;
}

function makeParents($student_id, $column = '')
{
    global $THIS_RET, $view_other_RET, $_openSIS;

    if ($THIS_RET['PARENTS'] == $student_id) {
        if (!$THIS_RET['ADDRESS_ID'])
            $THIS_RET['ADDRESS_ID'] = 0;

        $THIS_RET['PARENTS'] = '';

        if ($_openSIS['makeParents'])
            $constraint = 'AND (LOWER(sjp.RELATIONSHIP) LIKE \'' . strtolower($_openSIS['makeParents']) . '%\')';
        elseif ($view_other_RET['ALL_CONTACTS'][1]['VALUE'] == 'Y')
            $constraint = 'AND (p.CUSTODY=\'Y\' OR sjp.IS_EMERGENCY=\'Y\')';
        else
            $constraint = 'AND p.CUSTODY=\'Y\'';

        $people_RET = DBGet(DBQuery('SELECT p.STAFF_ID as PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sa.ID AS ADDRESS_ID,p.CUSTODY,sjp.IS_EMERGENCY as EMERGENCY FROM students_join_people sjp,people p,student_address sa WHERE sjp.PERSON_ID=p.STAFF_ID AND sjp.STUDENT_ID=\'' . $student_id . '\' AND p.STAFF_ID=sa.PEOPLE_ID AND sa.STUDENT_ID=sjp.STUDENT_ID ' . $constraint . ' ORDER BY p.LAST_NAME,p.FIRST_NAME'));
        if (count($people_RET)) {
            foreach ($people_RET as $person) {

                if ($person['CUSTODY'] == 'Y')
                    $color = '0000FF';
                elseif ($person['EMERGENCY'] == 'Y')
                    $color = 'FFFF00';

                if ($_REQUEST['_openSIS_PDF'])
                    $THIS_RET['PARENTS'] .= '<TR><TD>' . button('dot', $color, '', 6) . '</TD><TD>' . $person['FIRST_NAME'] . ' ' . $person['LAST_NAME'] . '</TD></TR>, ';
                else
                    $THIS_RET['PARENTS'] .= '<TR><TD>' . button('dot', $color, '', 6) . '</TD><TD><A HREF=# onclick=\'window.open("Modules.php?modname=miscellaneous/ViewContact.php?person_id=' . $person['PERSON_ID'] . '","","scrollbars=yes,resizable=yes,width=400,height=200");\'>' . $person['FIRST_NAME'] . ' ' . $person['LAST_NAME'] . '</A></TD></TR>';
            }
            if ($_REQUEST['_openSIS_PDF'])
                $THIS_RET['PARENTS'] = substr($THIS_RET['PARENTS'], 0, -2);
        }
    }

    if ($THIS_RET['PARENTS'])
        return '<TABLE border=0 cellpadding=0 cellspacing=0 class=LO_field>' . $THIS_RET['PARENTS'] . '</TABLE>';
}

function appendSQL($sql, &$extra)
{
    global $_openSIS, $imm_date, $med_date, $ma_date, $nv_date;

    if ($_REQUEST['stuid']) {
        $sql .= ' AND ssm.STUDENT_ID = \'' . singleQuoteReplace("'", "\'", $_REQUEST['stuid']) . '\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Student ID: </b></font>' . $_REQUEST['stuid'] . '<BR>';
    }
    if ($_REQUEST['altid']) {

        $sql .= ' AND LOWER(s.ALT_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['altid']))) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Student ID: </b></font>' . $_REQUEST['stuid'] . '<BR>';
    }
    if ($_REQUEST['last']) {
        $sql .= ' AND LOWER(s.LAST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['last']))) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Last Name starts with: </b></font>' . stripslashes(trim($_REQUEST['last'])) . '<BR>';
    }
    if ($_REQUEST['first']) {
        $sql .= ' AND LOWER(s.FIRST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['first']))) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>First Name starts with: </b></font>' . stripslashes(trim($_REQUEST['first'])) . '<BR>';
    }
    if ($_REQUEST['grade']) {
        $sql .= ' AND ssm.GRADE_ID IN(SELECT id FROM school_gradelevels WHERE id= \'' . singleQuoteReplace("'", "\'", $_REQUEST['grade']) . '\')';
        if (!$extra['NoSearchTerms']) {
            $title = DBGet(DBQuery('SELECT title FROM school_gradelevels WHERE id= \'' . singleQuoteReplace("'", "\'", $_REQUEST['grade']) . '\''));
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Grade: </b></font>' . $title[1]['TITLE'] . '<BR>';
	    }
    }
    if ($_REQUEST['addr']) {
        $sql .= ' AND (LOWER(sam.STREET_ADDRESS_1) LIKE \'%' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(sam.CITY) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(sam.STATE)=\'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '\' OR ZIPCODE LIKE \'' . trim(singleQuoteReplace("'", "\'", $_REQUEST['addr'])) . '%\')';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Address contains: </b></font>' . trim($_REQUEST['addr']) . '<BR>';
    }
    if ($_REQUEST['preferred_hospital']) {
        $sql .= ' AND LOWER(s.PREFERRED_HOSPITAL) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['preferred_hospital'])) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Preferred Medical Facility starts with: </b></font>' . $_REQUEST['preferred_hospital'] . '<BR>';
    }

    ////////////////////////extra search field start///////////////////////////


    if ($_REQUEST['middle_name']) {
        $sql .= ' AND LOWER(s.middle_name) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['middle_name']))) . '%\' ';
    }

    if ($_REQUEST['GENDER']) {
        $sql .= ' AND LOWER(s.GENDER) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['GENDER']))) . '%\' ';
    }

    // if ($_REQUEST['ETHNICITY_ID']) {
    //     $sql .= ' AND LOWER(s.ETHNICITY_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['ETHNICITY_ID']))) . '%\' ';
    // }

    if ($_REQUEST['ETHNICITY_ID']) {
        $sql .= ' AND s.ETHNICITY_ID = \'' . trim($_REQUEST['ETHNICITY_ID']) . '\'';
    }

    if ($_REQUEST['common_name']) {
        $sql .= ' AND LOWER(s.common_name) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['common_name']))) . '%\' ';
    }

    // if ($_REQUEST['LANGUAGE_ID']) {
    //     $sql .= ' AND LOWER(s.LANGUAGE_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['LANGUAGE_ID']))) . '%\' ';
    // }

    if ($_REQUEST['LANGUAGE_ID']) {
        $sql .= ' AND s.LANGUAGE_ID = \'' . trim($_REQUEST['LANGUAGE_ID']) . '\'';
    }

    if ($_REQUEST['email']) {
        $sql .= ' AND LOWER(s.email) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['email']))) . '%\' ';
    }

    if ($_REQUEST['phone']) {
        $sql .= ' AND LOWER(s.phone) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['phone']))) . '%\' ';
    }


    ////////////////////////extra search field end///////////////////////////
    if ($_REQUEST['mp_comment']) {
        $sql .= ' AND LOWER(smc.COMMENT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['mp_comment'])) . '%\' AND s.STUDENT_ID=smc.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Comments starts with: </b></font>' . $_REQUEST['mp_comment'] . '<BR>';
    }
    if ($_REQUEST['goal_title']) {
        $sql .= ' AND LOWER(g.GOAL_TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_title'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>GoalInc Title starts with: </b></font>' . $_REQUEST['goal_title'] . '<BR>';
    }


    //////////////extra field table start2////////////////////


    if ($_REQUEST['username']) {
        $sql .= ' AND LOWER(la.username) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['username']))) . '%\' and la.user_id=s.student_id ';
    }
    //////////////extra field table end2////////////////////
    if ($_REQUEST['goal_description']) {
        $sql .= ' AND LOWER(g.GOAL_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_description'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>GoalInc Description starts with: </b></font>' . $_REQUEST['goal_description'] . '<BR>';
    }
    if ($_REQUEST['progress_name']) {
        $sql .= ' AND LOWER(p.PROGRESS_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_name'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Progress Period Name starts with: </b></font>' . $_REQUEST['progress_name'] . '<BR>';
    }
    if ($_REQUEST['progress_description']) {
        $sql .= ' AND LOWER(p.PROGRESS_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_description'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Progress Assessment starts with: </b></font>' . $_REQUEST['progress_description'] . '<BR>';
    }
    if ($_REQUEST['doctors_note_comments']) {
        $sql .= ' AND LOWER(smn.DOCTORS_NOTE_COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['doctors_note_comments'])) . '%\' AND s.STUDENT_ID=smn.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Doctor\'s Note starts with: </b></font>' . $_REQUEST['doctors_note_comments'] . '<BR>';
    }
    if ($_REQUEST['type']) {
        $sql .= ' AND LOWER(sm.TYPE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['type'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Type starts with: </b></font>' . $_REQUEST['type'] . '<BR>';
    }
    if ($_REQUEST['imm_comments']) {
        $sql .= ' AND LOWER(sm.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['imm_comments'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Comments starts with: </b></font>' . $_REQUEST['imm_comments'] . '<BR>';
    }
    if ($_REQUEST['imm_day'] && $_REQUEST['imm_month'] && $_REQUEST['imm_year']) {
        $imm_date = $_REQUEST['imm_year'] . '-' . $_REQUEST['imm_month'] . '-' . $_REQUEST['imm_day'];
        $sql .= ' AND sm.MEDICAL_DATE =\'' . date('Y-m-d', strtotime($imm_date)) . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Immunization Date: </b></font>' . $imm_date . '<BR>';
    } elseif ($_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
        if ($_REQUEST['imm_day']) {
            $sql .= ' AND SUBSTR(sm.MEDICAL_DATE,9,2) =\'' . $_REQUEST['imm_day'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Day :" . $_REQUEST['imm_day'];
        }
        if ($_REQUEST['imm_month']) {
            $sql .= ' AND SUBSTR(sm.MEDICAL_DATE,6,2) =\'' . $_REQUEST['imm_month'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Month :" . $_REQUEST['imm_month'];
        }
        if ($_REQUEST['imm_year']) {
            $sql .= ' AND SUBSTR(sm.MEDICAL_DATE,1,4) =\'' . $_REQUEST['imm_year'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Year :" . $_REQUEST['imm_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Immunization Date: </b></font>' . $imm_date . '<BR>';
    }
    if ($_REQUEST['med_day'] && $_REQUEST['med_month'] && $_REQUEST['med_year']) {
        $med_date = $_REQUEST['med_year'] . '-' . $_REQUEST['med_month'] . '-' . $_REQUEST['med_day'];
        //        $med_date = $_REQUEST['med_year'] . '-' . $_REQUEST['med_day'] . '-' . $_REQUEST['med_month'];
        //        echo $med_date;
        //        echo '<br>';
        ////        echo strtotime($med_date);
        //        echo date('Y-m-d', strtotime($med_date));
        //        echo '<br>';
        $sql .= ' AND smn.DOCTORS_NOTE_DATE =\'' . date('Y-m-d', strtotime($med_date)) . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Date: </b></font>' . $med_date . '<BR>';
    } elseif ($_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
        if ($_REQUEST['med_day']) {
            $sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,9,2) =\'' . $_REQUEST['med_day'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Day :" . $_REQUEST['med_day'];
        }
        if ($_REQUEST['med_month']) {
            $sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,6,2) =\'' . $_REQUEST['med_month'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Month :" . $_REQUEST['med_month'];
        }
        if ($_REQUEST['med_year']) {
            $sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,1,4) =\'' . $_REQUEST['med_year'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Year :" . $_REQUEST['med_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Date: </b></font>' . $med_date . '<BR>';
    }
    if ($_REQUEST['ma_day'] && $_REQUEST['ma_month'] && $_REQUEST['ma_year']) {
        $ma_date = $_REQUEST['ma_year'] . '-' . $_REQUEST['ma_month'] . '-' . $_REQUEST['ma_day'];
        $sql .= ' AND sma.ALERT_DATE =\'' . date('Y-m-d', strtotime($ma_date)) . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Alert Date: </b></font>' . $ma_date . '<BR>';
    } elseif ($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
        if ($_REQUEST['ma_day']) {
            $sql .= ' AND SUBSTR(sma.ALERT_DATE,9,2) =\'' . $_REQUEST['ma_day'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Day :" . $_REQUEST['ma_day'];
        }
        if ($_REQUEST['ma_month']) {
            $sql .= ' AND SUBSTR(sma.ALERT_DATE,6,2) =\'' . $_REQUEST['ma_month'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Month :" . $_REQUEST['ma_month'];
        }
        if ($_REQUEST['ma_year']) {
            $sql .= ' AND SUBSTR(sma.ALERT_DATE,1,4) =\'' . $_REQUEST['ma_year'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Year :" . $_REQUEST['ma_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Alert Date: </b></font>' . $ma_date . '<BR>';
    }
    if ($_REQUEST['nv_day'] && $_REQUEST['nv_month'] && $_REQUEST['nv_year']) {
        $nv_date = $_REQUEST['nv_year'] . '-' . $_REQUEST['nv_month'] . '-' . $_REQUEST['nv_day'];
        $sql .= ' AND smv.SCHOOL_DATE =\'' . date('Y-m-d', strtotime($nv_date)) . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Date: </b></font>' . $nv_date . '<BR>';
    } elseif ($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
        if ($_REQUEST['nv_day']) {
            $sql .= ' AND SUBSTR(smv.SCHOOL_DATE,9,2) =\'' . $_REQUEST['nv_day'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Day :" . $_REQUEST['nv_day'];
        }
        if ($_REQUEST['nv_month']) {
            $sql .= ' AND SUBSTR(smv.SCHOOL_DATE,6,2) =\'' . $_REQUEST['nv_month'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Month :" . $_REQUEST['nv_month'];
        }
        if ($_REQUEST['nv_year']) {
            $sql .= ' AND SUBSTR(smv.SCHOOL_DATE,1,4) =\'' . $_REQUEST['nv_year'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Year :" . $_REQUEST['nv_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Date: </b></font>' . $nv_date . '<BR>';
    }


    if ($_REQUEST['med_alrt_title']) {
        $sql .= ' AND LOWER(sma.TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_alrt_title'])) . '%\' AND s.STUDENT_ID=sma.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Alert starts with: </b></font>' . $_REQUEST['med_alrt_title'] . '<BR>';
    }
    if ($_REQUEST['reason']) {
        $sql .= ' AND LOWER(smv.REASON) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['reason'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Reason starts with: </b></font>' . $_REQUEST['reason'] . '<BR>';
    }
    if ($_REQUEST['result']) {
        $sql .= ' AND LOWER(smv.RESULT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['result'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Result starts with: </b></font>' . $_REQUEST['result'] . '<BR>';
    }
    if ($_REQUEST['med_vist_comments']) {
        $sql .= ' AND LOWER(smv.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_vist_comments'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Comments starts with: </b></font>' . $_REQUEST['med_vist_comments'] . '<BR>';
    }

    if ($_REQUEST['day_to_birthdate'] && $_REQUEST['month_to_birthdate']  && $_REQUEST['day_from_birthdate'] && $_REQUEST['month_from_birthdate']) {

        // $date_to =$_REQUEST['year_to_birthdate'].'-'.$_REQUEST['day_to_birthdate'] . '-' . $_REQUEST['month_to_birthdate'];
        //$date_from = $_REQUEST['year_from_birthdate'] . '-' .$_REQUEST['day_from_birthdate'] . '-' . $_REQUEST['month_from_birthdate'];

        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['day_from_birthdate'] . '\' AND \'' . $_REQUEST['day_to_birthdate'] . '\') ';
        //        
        //  $sql .= ' AND (s.BIRTHDATE  BETWEEN \'' .$date_from . '\' AND \'' . $date_to. '\') ';
        // $sql .= ' AND (s.BIRTHDATE  >= \'' .$date_from . '\' AND s.BIRTHDATE  <= \'' . $date_to. '\') ';
        $date_to = $_REQUEST['month_to_birthdate'] . '-' . $_REQUEST['day_to_birthdate'];
        $date_from = $_REQUEST['month_from_birthdate'] . '-' . $_REQUEST['day_from_birthdate'];
        $sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['day_from_birthdate'] . '\' AND \'' . $_REQUEST['day_to_birthdate'] . '\') ';
        $sql .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Birthday Starts from ' . $date_from . ' to ' . $date_to . '</b></font>';
    }


    if ($_REQUEST['day_dob_birthdate'] && $_REQUEST['month_dob_birthdate']  && $_REQUEST['year_dob_birthdate']) {


        $date_dob = $_REQUEST['year_dob_birthdate'] . '-' . $_REQUEST['month_dob_birthdate'] . '-' . $_REQUEST['day_dob_birthdate'];
        //$date_from = $_REQUEST['month_from_birthdate'] . '-' . $_REQUEST['day_from_birthdate'];
        $sql .= ' AND s.BIRTHDATE = \'' . $date_dob . '\'';
        //$sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Birthday is ' . $date_dob . '</b></font>';
    }


    if ($_REQUEST['day_to_est'] && $_REQUEST['month_to_est'] && $_REQUEST['day_from_est'] && $_REQUEST['month_from_est']) {
        $date_to_est = $_REQUEST['year_to_est'] . '-' . $_REQUEST['month_to_est'] . '-' . $_REQUEST['day_to_est'];
        $date_from_est = $_REQUEST['year_from_est'] . '-' . $_REQUEST['month_from_est'] . '-' . $_REQUEST['day_from_est'];

        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['day_from_birthdate'] . '\' AND \'' . $_REQUEST['day_to_birthdate'] . '\') ';
        //        
        $sql .= ' AND (s.ESTIMATED_GRAD_DATE BETWEEN \'' . $date_from_est . '\' AND \'' . $date_to_est . '\') ';

        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Estimated Grad. Date Starts from ' . $date_from_est . ' to ' . $date_to_est . '</b></font>';
    }

    if ($_REQUEST['day_to_st'] && $_REQUEST['month_to_st'] && $_REQUEST['day_from_st'] && $_REQUEST['month_from_st']) {
        $date_to_st = $_REQUEST['year_to_st'] . '-' . $_REQUEST['month_to_st'] . '-' . $_REQUEST['day_to_st'];
        $date_from_st = $_REQUEST['year_from_st'] . '-' . $_REQUEST['month_from_st'] . '-' . $_REQUEST['day_from_st'];

        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['day_from_birthdate'] . '\' AND \'' . $_REQUEST['day_to_birthdate'] . '\') ';
        //        
        $sql .= ' AND (ssm.START_DATE BETWEEN \'' . $date_from_st . '\' AND \'' . $date_to_st . '\') ';

        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Enrollment Starts from ' . $date_from_st . ' to ' . $date_to_st . '</b></font>';
    }

    if ($_REQUEST['day_to_en'] && $_REQUEST['month_to_en'] && $_REQUEST['day_from_en'] && $_REQUEST['month_from_en']) {
        $date_to_en = $_REQUEST['year_to_en'] . '-' . $_REQUEST['month_to_en'] . '-' . $_REQUEST['day_to_en'];
        $date_from_en = $_REQUEST['year_from_en'] . '-' . $_REQUEST['month_from_en'] . '-' . $_REQUEST['day_from_en'];

        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
        //        $sql .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['day_from_birthdate'] . '\' AND \'' . $_REQUEST['day_to_birthdate'] . '\') ';
        //        
        $sql .= ' AND (ssm.END_DATE BETWEEN \'' . $date_from_en . '\' AND \'' . $date_to_en . '\') ';

        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Enrollment Ends from ' . $date_from_en . ' to ' . $date_to_en . '</b></font>';
    }
    // test cases start
    // test cases end
    if ($_SESSION['stu_search']['sql'] && $_REQUEST['return_session']) {
        unset($_SESSION['inactive_stu_filter']);
        return $_SESSION['stu_search']['sql'];
    } else {
        if ($_REQUEST['sql_save_session'] && !$_SESSION['stu_search']['search_from_grade']) {
            $_SESSION['stu_search']['sql'] = $sql;
        } else if ($_SESSION['stu_search']['search_from_grade']) {
            unset($_SESSION['stu_search']['search_from_grade']);
        }
        //        echo $sql;
        return $sql;
    }
}

function appendAllSQL($allSQL, &$extra)
{
    global $_openSIS, $imm_date, $med_date, $ma_date, $nv_date;

    if ($_REQUEST['stuid']) {
        $allSQL .= ' AND ssm.STUDENT_ID = \'' . singleQuoteReplace("'", "\'", $_REQUEST['stuid']) . '\' ';
    }
    if ($_REQUEST['altid']) {

        $allSQL .= ' AND LOWER(s.ALT_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['altid']))) . '%\' ';
    }
    if ($_REQUEST['last']) {
        $allSQL .= ' AND LOWER(s.LAST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['last']))) . '%\' ';
    }
    if ($_REQUEST['first']) {
        $allSQL .= ' AND LOWER(s.FIRST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['first']))) . '%\' ';
    }

    if ($_REQUEST['grade']) {
        $grade = sqlSecurityFilter($_REQUEST['grade']);
        $allSQL .= ' AND ssm.GRADE_ID IN(SELECT id FROM school_gradelevels WHERE title= \'' . singleQuoteReplace("'", "\'", $grade) . '\')';
    }
    if ($_REQUEST['addr']) {
        $allSQL .= ' AND (LOWER(sam.STREET_ADDRESS_1) LIKE \'%' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(sam.CITY) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(sam.STATE)=\'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '\' OR ZIPCODE LIKE \'' . trim(singleQuoteReplace("'", "\'", $_REQUEST['addr'])) . '%\')';
    }
    if ($_REQUEST['preferred_hospital']) {
        $allSQL .= ' AND LOWER(s.PREFERRED_HOSPITAL) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['preferred_hospital'])) . '%\' ';
    }

    ////////////////////////extra search field start///////////////////////////

    if ($_REQUEST['middle_name']) {
        $allSQL .= ' AND LOWER(s.middle_name) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['middle_name']))) . '%\' ';
    }

    if ($_REQUEST['GENDER']) {
        $allSQL .= ' AND LOWER(s.GENDER) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['GENDER']))) . '%\' ';
    }

    // if ($_REQUEST['ETHNICITY_ID']) {
    //     $allSQL .= ' AND LOWER(s.ETHNICITY_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['ETHNICITY_ID']))) . '%\' ';
    // }

    if ($_REQUEST['ETHNICITY_ID']) {
        $allSQL .= ' AND s.ETHNICITY_ID = \'' . trim($_REQUEST['ETHNICITY_ID']) . '\'';
    }

    if ($_REQUEST['common_name']) {
        $allSQL .= ' AND LOWER(s.common_name) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['common_name']))) . '%\' ';
    }

    // if ($_REQUEST['LANGUAGE_ID']) {
    //     $allSQL .= ' AND LOWER(s.LANGUAGE_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['LANGUAGE_ID']))) . '%\' ';
    // }

    if ($_REQUEST['LANGUAGE_ID']) {
        $allSQL .= ' AND s.LANGUAGE_ID = \'' . trim($_REQUEST['LANGUAGE_ID']) . '\'';
    }

    if ($_REQUEST['email']) {
        $allSQL .= ' AND LOWER(s.email) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['email']))) . '%\' ';
    }

    if ($_REQUEST['phone']) {
        $allSQL .= ' AND LOWER(s.phone) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['phone']))) . '%\' ';
    }

    ////////////////////////extra search field end///////////////////////////

    if ($_REQUEST['mp_comment']) {
        $allSQL .= ' AND LOWER(smc.COMMENT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['mp_comment'])) . '%\' AND s.STUDENT_ID=smc.STUDENT_ID ';
    }
    if ($_REQUEST['goal_title']) {
        $allSQL .= ' AND LOWER(g.GOAL_TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_title'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
    }

    //////////////extra field table start2////////////////////       

    if ($_REQUEST['username']) {
        $allSQL .= ' AND LOWER(la.username) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['username']))) . '%\' and la.user_id=s.student_id ';
    }

    //////////////extra field table end2////////////////////

    if ($_REQUEST['goal_description']) {
        $allSQL .= ' AND LOWER(g.GOAL_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_description'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
    }
    if ($_REQUEST['progress_name']) {
        $allSQL .= ' AND LOWER(p.PROGRESS_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_name'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
    }
    if ($_REQUEST['progress_description']) {
        $allSQL .= ' AND LOWER(p.PROGRESS_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_description'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
    }
    if ($_REQUEST['doctors_note_comments']) {
        $allSQL .= ' AND LOWER(smn.DOCTORS_NOTE_COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['doctors_note_comments'])) . '%\' AND s.STUDENT_ID=smn.STUDENT_ID ';
    }
    if ($_REQUEST['type']) {
        $allSQL .= ' AND LOWER(sm.TYPE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['type'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
    }
    if ($_REQUEST['imm_comments']) {
        $allSQL .= ' AND LOWER(sm.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['imm_comments'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
    }
    if ($_REQUEST['imm_day'] && $_REQUEST['imm_month'] && $_REQUEST['imm_year']) {
        $imm_date = $_REQUEST['imm_year'] . '-' . $_REQUEST['imm_month'] . '-' . $_REQUEST['imm_day'];
        $allSQL .= ' AND sm.MEDICAL_DATE =\'' . date('Y-m-d', strtotime($imm_date)) . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
    } elseif ($_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
        if ($_REQUEST['imm_day']) {
            $allSQL .= ' AND SUBSTR(sm.MEDICAL_DATE,9,2) =\'' . $_REQUEST['imm_day'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Day :" . $_REQUEST['imm_day'];
        }
        if ($_REQUEST['imm_month']) {
            $allSQL .= ' AND SUBSTR(sm.MEDICAL_DATE,6,2) =\'' . $_REQUEST['imm_month'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Month :" . $_REQUEST['imm_month'];
        }
        if ($_REQUEST['imm_year']) {
            $allSQL .= ' AND SUBSTR(sm.MEDICAL_DATE,1,4) =\'' . $_REQUEST['imm_year'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Year :" . $_REQUEST['imm_year'];
        }
    }
    if ($_REQUEST['med_day'] && $_REQUEST['med_month'] && $_REQUEST['med_year']) {
        $med_date = $_REQUEST['med_year'] . '-' . $_REQUEST['med_month'] . '-' . $_REQUEST['med_day'];
        $allSQL .= ' AND smn.DOCTORS_NOTE_DATE =\'' . date('Y-m-d', strtotime($med_date)) . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
    } elseif ($_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
        if ($_REQUEST['med_day']) {
            $allSQL .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,9,2) =\'' . $_REQUEST['med_day'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Day :" . $_REQUEST['med_day'];
        }
        if ($_REQUEST['med_month']) {
            $allSQL .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,6,2) =\'' . $_REQUEST['med_month'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Month :" . $_REQUEST['med_month'];
        }
        if ($_REQUEST['med_year']) {
            $allSQL .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,1,4) =\'' . $_REQUEST['med_year'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Year :" . $_REQUEST['med_year'];
        }
    }
    if ($_REQUEST['ma_day'] && $_REQUEST['ma_month'] && $_REQUEST['ma_year']) {
        $ma_date = $_REQUEST['ma_year'] . '-' . $_REQUEST['ma_month'] . '-' . $_REQUEST['ma_day'];
        $allSQL .= ' AND sma.ALERT_DATE =\'' . date('Y-m-d', strtotime($ma_date)) . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
    } elseif ($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
        if ($_REQUEST['ma_day']) {
            $allSQL .= ' AND SUBSTR(sma.ALERT_DATE,9,2) =\'' . $_REQUEST['ma_day'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Day :" . $_REQUEST['ma_day'];
        }
        if ($_REQUEST['ma_month']) {
            $allSQL .= ' AND SUBSTR(sma.ALERT_DATE,6,2) =\'' . $_REQUEST['ma_month'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Month :" . $_REQUEST['ma_month'];
        }
        if ($_REQUEST['ma_year']) {
            $allSQL .= ' AND SUBSTR(sma.ALERT_DATE,1,4) =\'' . $_REQUEST['ma_year'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Year :" . $_REQUEST['ma_year'];
        }
    }
    if ($_REQUEST['nv_day'] && $_REQUEST['nv_month'] && $_REQUEST['nv_year']) {
        $nv_date = $_REQUEST['nv_year'] . '-' . $_REQUEST['nv_month'] . '-' . $_REQUEST['nv_day'];
        $allSQL .= ' AND smv.SCHOOL_DATE =\'' . date('Y-m-d', strtotime($nv_date)) . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
    } elseif ($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
        if ($_REQUEST['nv_day']) {
            $allSQL .= ' AND SUBSTR(smv.SCHOOL_DATE,9,2) =\'' . $_REQUEST['nv_day'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Day :" . $_REQUEST['nv_day'];
        }
        if ($_REQUEST['nv_month']) {
            $allSQL .= ' AND SUBSTR(smv.SCHOOL_DATE,6,2) =\'' . $_REQUEST['nv_month'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Month :" . $_REQUEST['nv_month'];
        }
        if ($_REQUEST['nv_year']) {
            $allSQL .= ' AND SUBSTR(smv.SCHOOL_DATE,1,4) =\'' . $_REQUEST['nv_year'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Year :" . $_REQUEST['nv_year'];
        }
    }

    if ($_REQUEST['med_alrt_title']) {
        $allSQL .= ' AND LOWER(sma.TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_alrt_title'])) . '%\' AND s.STUDENT_ID=sma.STUDENT_ID ';
    }
    if ($_REQUEST['reason']) {
        $allSQL .= ' AND LOWER(smv.REASON) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['reason'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
    }
    if ($_REQUEST['result']) {
        $allSQL .= ' AND LOWER(smv.RESULT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['result'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
    }
    if ($_REQUEST['med_vist_comments']) {
        $allSQL .= ' AND LOWER(smv.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_vist_comments'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
    }

    if ($_REQUEST['day_to_birthdate'] && $_REQUEST['month_to_birthdate']  && $_REQUEST['day_from_birthdate'] && $_REQUEST['month_from_birthdate']) {
        $date_to = $_REQUEST['month_to_birthdate'] . '-' . $_REQUEST['day_to_birthdate'];
        $date_from = $_REQUEST['month_from_birthdate'] . '-' . $_REQUEST['day_from_birthdate'];
        $allSQL .= ' AND (SUBSTR(s.BIRTHDATE,9,2) BETWEEN \'' . $_REQUEST['day_from_birthdate'] . '\' AND \'' . $_REQUEST['day_to_birthdate'] . '\') ';
        $allSQL .= ' AND (SUBSTR(s.BIRTHDATE,6,2) BETWEEN \'' . $_REQUEST['month_from_birthdate'] . '\' AND \'' . $_REQUEST['month_to_birthdate'] . '\') ';
    }


    if ($_REQUEST['day_dob_birthdate'] && $_REQUEST['month_dob_birthdate']  && $_REQUEST['year_dob_birthdate']) {
        $date_dob = $_REQUEST['year_dob_birthdate'] . '-' . $_REQUEST['month_dob_birthdate'] . '-' . $_REQUEST['day_dob_birthdate'];
        //$date_from = $_REQUEST['month_from_birthdate'] . '-' . $_REQUEST['day_from_birthdate'];
        $allSQL .= ' AND s.BIRTHDATE = \'' . $date_dob . '\'';
    }


    if ($_REQUEST['day_to_est'] && $_REQUEST['month_to_est'] && $_REQUEST['day_from_est'] && $_REQUEST['month_from_est']) {
        $date_to_est = $_REQUEST['year_to_est'] . '-' . $_REQUEST['month_to_est'] . '-' . $_REQUEST['day_to_est'];
        $date_from_est = $_REQUEST['year_from_est'] . '-' . $_REQUEST['month_from_est'] . '-' . $_REQUEST['day_from_est'];

        $allSQL .= ' AND (s.ESTIMATED_GRAD_DATE BETWEEN \'' . $date_from_est . '\' AND \'' . $date_to_est . '\') ';
    }

    if ($_REQUEST['day_to_st'] && $_REQUEST['month_to_st'] && $_REQUEST['day_from_st'] && $_REQUEST['month_from_st']) {
        $date_to_st = $_REQUEST['year_to_st'] . '-' . $_REQUEST['month_to_st'] . '-' . $_REQUEST['day_to_st'];
        $date_from_st = $_REQUEST['year_from_st'] . '-' . $_REQUEST['month_from_st'] . '-' . $_REQUEST['day_from_st'];

        $allSQL .= ' AND (ssm.START_DATE BETWEEN \'' . $date_from_st . '\' AND \'' . $date_to_st . '\') ';
    }

    if ($_REQUEST['day_to_en'] && $_REQUEST['month_to_en'] && $_REQUEST['day_from_en'] && $_REQUEST['month_from_en']) {
        $date_to_en = $_REQUEST['year_to_en'] . '-' . $_REQUEST['month_to_en'] . '-' . $_REQUEST['day_to_en'];
        $date_from_en = $_REQUEST['year_from_en'] . '-' . $_REQUEST['month_from_en'] . '-' . $_REQUEST['day_from_en'];

        $allSQL .= ' AND (ssm.END_DATE BETWEEN \'' . $date_from_en . '\' AND \'' . $date_to_en . '\') ';
    }

    // test cases start
    // test cases end
    if ($_SESSION['stu_search']['allSQL'] && $_REQUEST['return_session']) {
        unset($_SESSION['inactive_stu_filter']);

        return $_SESSION['stu_search']['allSQL'];
    } else {
        if ($_REQUEST['sql_save_session'] && !$_SESSION['stu_search']['search_from_grade']) {
            $_SESSION['stu_search']['allSQL'] = $allSQL;
        } else if ($_SESSION['stu_search']['search_from_grade']) {
            unset($_SESSION['stu_search']['search_from_grade']);
        }

        return $allSQL;
    }
}

############################################################################################

function GetStuList_Absence_Summary(&$extra)
{
    global $contacts_RET, $view_other_RET, $_openSIS, $select;
    $offset = 'GRADE_ID';

    if ((!$extra['SELECT_ONLY'] || strpos($extra['SELECT_ONLY'], $offset) !== false) && !$extra['functions']['GRADE_ID'])
        $functions = array('GRADE_ID' => 'GetGrade');
    else
        $functions = array();

    if ($extra['functions'])
        $functions += $extra['functions'];

    if (!$extra['DATE']) {
        $queryMP = UserMP();
        $extra['DATE'] = DBDate();
    } else
        $queryMP = GetCurrentMP('QTR', $extra['DATE'], false);

    if ($_REQUEST['expanded_view'] == 'true') {
        if (!$extra['columns_after'])
            $extra['columns_after'] = array();
        #############################################################################################
        //Commented as it crashing for Linux due to  Blank Database tables

        $view_fields_RET = DBGet(DBQuery('SELECT cf.ID,cf.TYPE,cf.TITLE FROM program_user_config puc,custom_fields cf WHERE puc.TITLE=cf.ID AND puc.PROGRAM=\'StudentFieldsView\' AND puc.USER_ID=\'' . User('STAFF_ID') . '\' AND puc.VALUE=\'Y\''));
        #############################################################################################
        $view_address_RET = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE=\'ADDRESS\' AND USER_ID=\'' . User('STAFF_ID') . '\''));
        $view_address_RET = $view_address_RET[1]['VALUE'];
        $view_other_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE IN (\'PHONE\',\'HOME_PHONE\',\'GUARDIANS\',\'ALL_CONTACTS\') AND USER_ID=\'' . User('STAFF_ID') . '\''), array(), array('TITLE'));

        if (!count($view_fields_RET) && !isset($view_address_RET) && !isset($view_other_RET['CONTACT_INFO'])) {
            $extra['columns_after'] = array(
                'PHONE' => _phone,
                'GENDER' => _gender,
                'ETHNICITY' => _ethnicity,
                'ADDRESS' => _mailingAddress,
                'CITY' => _city,
                'STATE' => _state,
                'ZIPCODE' => _zipcode,
            ) + $extra['columns_after'];


            $select = ',s.PHONE,s.GENDER, (SELECT ETHNICITY_NAME FROM ethnicity WHERE ETHNICITY_ID = s.ETHNICITY_ID) AS ETHNICITY, s.ETHNICITY_ID,a.STREET_ADDRESS_1 as ADDRESS,a.CITY,a.STATE,a.ZIPCODE ';
            $extra['FROM'] = '  LEFT OUTER JOIN student_address a ON (ssm.STUDENT_ID=a.STUDENT_ID AND a.TYPE=\'Home Address\') ' . $extra['FROM'];
            $functions['CONTACT_INFO'] = 'makeContactInfo';
            // if gender is converted to codeds type

            $extra['singular'] = 'Student Address';
            $extra['plural'] = 'Student Addresses';

            $extra2['NoSearchTerms'] = true;
            $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,p.TITLE,s.PHONE,a.ID AS ADDRESS_ID ';
            $extra2['FROM'] .= ',student_address a LEFT OUTER JOIN students_join_people sjp ON (a.STUDENT_ID=sjp.STUDENT_ID  AND (sjp.IS_EMERGENCY=\'Y\')) LEFT OUTER JOIN people p ON (p.CUSTODY=\'Y\' OR p.STAFF_ID=sjp.PERSON_ID) ';
            $extra2['WHERE'] .= ' AND a.STUDENT_ID=sjp.STUDENT_ID AND sjp.STUDENT_ID=ssm.STUDENT_ID ';
            $extra2['ORDER_BY'] .= 'COALESCE(p.CUSTODY,\'N\') DESC';
            $extra2['group'] = array('STUDENT_ID', 'PERSON_ID');

            // EXPANDED VIEW AND ADDR BREAKS THIS QUERY ... SO, TURN 'EM OFF
            if (!$_REQUEST['_openSIS_PDF']) {
                $expanded_view = $_REQUEST['expanded_view'];
                $_REQUEST['expanded_view'] = false;
                $addr = $_REQUEST['addr'];
                unset($_REQUEST['addr']);
                $contacts_RET = GetStuList($extra2);
                $_REQUEST['expanded_view'] = $expanded_view;
                $_REQUEST['addr'] = $addr;
            } else
                unset($extra2['columns_after']['CONTACT_INFO']);
        } else {
            if ($view_other_RET['CONTACT_INFO'][1]['VALUE'] == 'Y' && !$_REQUEST['_openSIS_PDF']) {
                $select .= ',NULL AS CONTACT_INFO ';
                $extra['columns_after']['CONTACT_INFO'] = '<IMG SRC=assets/down_phone_button.gif border=0>';
                $functions['CONTACT_INFO'] = 'makeContactInfo';

                $extra2 = $extra;
                $extra2['NoSearchTerms'] = true;
                $extra2['SELECT'] = '';
                $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,p.TITLE,s.PHONE,a.ID AS ADDRESS_ID,COALESCE(p.CUSTODY,\'N\') ';
                $extra2['FROM'] .= ',student_address a LEFT OUTER JOIN students_join_people sjp ON (sjp.STUDENT_ID=a.STUDENT_ID  AND (p.CUSTODY=\'Y\' OR sjp.IS_EMERGENCY=\'Y\')) LEFT OUTER JOIN people p ON (p.STAFF_ID=sjp.PERSON_ID)  ';
                $extra2['WHERE'] .= ' AND a.STUDENT_ID=sjp.STUDENT_ID AND sjp.STUDENT_ID=ssm.STUDENT_ID ';
                $extra2['ORDER_BY'] .= 'COALESCE(p.CUSTODY,\'N\') DESC';
                $extra2['group'] = array('STUDENT_ID', 'PERSON_ID');
                $extra2['functions'] = array();
                $extra2['link'] = array();

                // EXPANDED VIEW AND ADDR BREAKS THIS QUERY ... SO, TURN 'EM OFF
                $expanded_view = $_REQUEST['expanded_view'];
                $_REQUEST['expanded_view'] = false;
                $addr = $_REQUEST['addr'];
                unset($_REQUEST['addr']);
                $contacts_RET = GetStuList($extra2);
                $_REQUEST['expanded_view'] = $expanded_view;
                $_REQUEST['addr'] = $addr;
            }
            foreach ($view_fields_RET as $field) {
                $custom = DBGet(DBQuery('SHOW COLUMNS FROM students WHERE FIELD=\'CUSTOM_' . $field['ID'] . '\''));
                $custom = $custom[1];
                if ($custom) {
                    $extra['columns_after']['CUSTOM_' . $field['ID']] = $field['TITLE'];
                    if ($field['TYPE'] == 'date')
                        $functions['CUSTOM_' . $field['ID']] = 'ProperDate';
                    elseif ($field['TYPE'] == 'numeric')
                        $functions['CUSTOM_' . $field['ID']] = 'removeDot00';
                    elseif ($field['TYPE'] == 'codeds')
                        $functions['CUSTOM_' . $field['ID']] = 'DeCodeds';
                    $select .= ',s.CUSTOM_' . $field['ID'];
                } else {
                    $custom_stu = DBGet(DBQuery("SELECT TYPE,TITLE FROM custom_fields WHERE ID='" . $field['ID'] . "'"));
                    $custom_stu = $custom_stu[1];
                    if ($custom_stu['TYPE'] == 'date')
                        $functions[strtolower(str_replace(" ", "_", $custom_stu['TITLE']))] = 'ProperDate';
                    elseif ($custom_stu['TYPE'] == 'numeric')
                        $functions[strtolower(str_replace(" ", "_", $custom_stu['TITLE']))] = 'removeDot00';
                    elseif ($custom_stu['TYPE'] == 'codeds')
                        $functions[strtolower(str_replace(" ", "_", $custom_stu['TITLE']))] = 'DeCodeds';
                    $select .= ',s.' . strtoupper(str_replace(" ", "_", $custom_stu['TITLE']));

                    $extra['columns_after'] += array(strtoupper(str_replace(" ", "_", $custom_stu['TITLE'])) => $custom_stu['TITLE']);
                }
            }
            if ($view_address_RET) {
                if ($view_address_RET == 'RESIDENCE')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Home Address\')  ' . $extra['FROM'];
                elseif ($view_address_RET == 'MAILING')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID AND sam.TYPE=\'Mail\') ' . $extra['FROM'];
                elseif ($view_address_RET == 'BUS_PICKUP')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (a.STUDENT_ID=sam.STUDENT_ID AND sam.BUS_PICKUP=\'Y\') ' . $extra['FROM'];
                else
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (a.STUDENT_ID=sam.STUDENT_ID AND sam.BUS_DROPOFF=\'Y\') ' . $extra['FROM'];


                $extra['columns_after'] += array('ADDRESS' => ucwords(strtolower(str_replace('_', ' ', $view_address_RET))) . ' Address', 'CITY' => 'City', 'STATE' => 'State', 'ZIPCODE' => 'Zipcode');

                $select .= ',sam.ID AS ADDRESS_ID,sam.STREET_ADDRESS_1 as ADDRESS,sam.CITY,sam.STATE,sam.ZIPCODE,s.PHONE,ssm.STUDENT_ID AS PARENTS';

                $extra['singular'] = 'Student Address';
                $extra['plural'] = 'Student Addresses';

                if ($view_other_RET['HOME_PHONE'][1]['VALUE'] == 'Y') {
                    $functions['PHONE'] = 'makePhone';
                    $extra['columns_after']['PHONE'] = 'Home Phone';
                }
                if ($view_other_RET['GUARDIANS'][1]['VALUE'] == 'Y' || $view_other_RET['ALL_CONTACTS'][1]['VALUE'] == 'Y') {
                    $functions['PARENTS'] = 'makeParents';
                    if ($view_other_RET['ALL_CONTACTS'][1]['VALUE'] == 'Y')
                        $extra['columns_after']['PARENTS'] = 'Contacts';
                    else
                        $extra['columns_after']['PARENTS'] = 'Guardians';
                }
            } elseif ($_REQUEST['addr'] || $extra['addr']) {
                $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID) ' . $extra['FROM'];
                $distinct = 'DISTINCT ';
            }
        }
        $extra['SELECT'] .= $select;
    } elseif ($_REQUEST['addr'] || $extra['addr']) {
        $extra['FROM'] = ' LEFT OUTER JOIN student_address sam ON (ssm.STUDENT_ID=sam.STUDENT_ID) ' . $extra['FROM'];
        $distinct = 'DISTINCT ';
    }
    $_SESSION['new_customsql'] = $extra['SELECT'];
    switch (User('PROFILE')) {
        case 'admin':
            $sql = 'SELECT ';
            if ($extra['SELECT_ONLY'])
                $sql .= $extra['SELECT_ONLY'];
            else {
                if (Preferences('NAME') == 'Common')
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
                else
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
                $_SESSION['new_sql'] = $sql;
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,ssm.SCHOOL_ID,s.ALT_ID,ssm.SCHOOL_ID AS LIST_SCHOOL_ID,ssm.GRADE_ID' . $extra['SELECT'];
                $_SESSION['new_sql'] .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,ssm.SCHOOL_ID,s.ALT_ID,ssm.SCHOOL_ID AS LIST_SCHOOL_ID,ssm.GRADE_ID' . $_SESSION['new_customsql'];
                //                if ($_REQUEST['include_inactive'] == 'Y')
                //                    $sql .= ',' . db_case(array('(ssm.SYEAR=\'' . UserSyear() . '\' AND ( (ssm.START_DATE IS NOT NULL AND \'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'>=ssm.START_DATE) AND(\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE ';
                //                $_SESSION['new_sql'] .= ',' . db_case(array('(ssm.SYEAR=\'' . UserSyear() . '\' AND ( (ssm.START_DATE IS NOT NULL AND \'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'>=ssm.START_DATE) AND(\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE ';
                //          

                if ($_REQUEST['include_inactive'] == 'Y')
                    $sql .= ',' . db_case(array('(ssm.SYEAR=\'' . UserSyear() . '\' AND  (ssm.START_DATE IS NOT NULL AND  (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE ';
                $_SESSION['new_sql'] .= ',' . db_case(array('(ssm.SYEAR=\'' . UserSyear() . '\' AND  (ssm.START_DATE IS NOT NULL AND  (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE ';
            }

            $sql .= ' FROM students s ';
            $_SESSION['new_sql'] .= ' FROM students s ';
            if ($_REQUEST['mp_comment']) {
                $sql .= ",student_mp_comments smc ";
                $_SESSION['newsql'] .= ',student_mp_comments smc ';
            }
            if ($_REQUEST['goal_title'] || $_REQUEST['goal_description']) {
                $sql .= ',student_goal g ';
                $_SESSION['newsql'] .= ',student_goal g ';
            }
            if ($_REQUEST['progress_name'] || $_REQUEST['progress_description']) {
                $sql .= ",student_goal_progress p ";
                $_SESSION['newsql'] .= ',student_goal_progress p ';
            }
            if ($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
                $sql .= ",student_medical_notes smn ";
                $_SESSION['newsql'] .= ',student_medical_notes smn ';
            }
            if ($_REQUEST['type'] || $_REQUEST['imm_comments'] || $_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
                $sql .= ',student_immunization sm ';
                $_SESSION['newsql'] .= ',student_immunization sm ';
            }
            if ($_REQUEST['med_alrt_title'] || $_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
                $sql .= ",student_medical_alerts sma ";
                $_SESSION['newsql'] .= ',student_medical_alerts sma ';
            }
            if ($_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments'] || $_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
                $sql .= ",student_medical_visits smv ";
                $_SESSION['newsql'] .= ',student_medical_visits smv ';
            }
            $_SESSION['new_sql'] .= $_SESSION['newsql'];
            $sql .= ',student_enrollment ssm ';
            $_SESSION['new_sql'] .= ',student_enrollment ssm ';
            $sql .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID ';
            $_SESSION['new_sql'] .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID ';
            if ($_REQUEST['include_inactive'] == 'Y') {
                $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
                $_SESSION['new_sql'] .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR =\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
            } else {
                //                $sql .= $_SESSION['inactive_stu_filter'] = ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ((ssm.START_DATE IS NOT NULL AND \'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'>=ssm.START_DATE) AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) ';
                //
                //                $_SESSION['new_sql'].=' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ((ssm.START_DATE IS NOT NULL AND \'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'>=ssm.START_DATE) AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) ';
                //           

                $sql .= $_SESSION['inactive_stu_filter'] = ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND  (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) ';

                $_SESSION['new_sql'] .= ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND  (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) ';
            }
            if (UserSchool() && $_REQUEST['_search_all_schools'] != 'Y') {
                $sql .= ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
                $_SESSION['new_sql'] .= ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
            } else {
                $sql .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';
                $_SESSION['new_sql'] .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';

                $extra['columns_after']['LIST_SCHOOL_ID'] = 'School';
                $functions['LIST_SCHOOL_ID'] = 'GetSchool';
            }

            if (!$extra['SELECT_ONLY'] && $_REQUEST['include_inactive'] == 'Y')
                $extra['columns_after']['ACTIVE'] = 'Status';

            break;

        case 'teacher':
            $sql = 'SELECT ';
            if ($extra['SELECT_ONLY'])
                $sql .= $extra['SELECT_ONLY'];

            else {
                if (Preferences('NAME') == 'Common')
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
                else
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
                $_SESSION['new_sql'] = $sql;
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];
                $_SESSION['new_sql'] .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.PHONE,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $_SESSION['new_customsql'];
                if ($_REQUEST['include_inactive'] == 'Y') {
                    $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE';
                    $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE_SCHEDULE';
                    $_SESSION['new_sql'] .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE';
                    $_SESSION['new_sql'] .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE_SCHEDULE';
                }
            }

            $sql .= ' FROM students s,course_periods cp,schedule ss ';
            $_SESSION['new_sql'] .= ' FROM students s,course_periods cp,schedule ss ';
            if ($_REQUEST['mp_comment']) {
                $sql .= ',student_mp_comments smc ';
                $_SESSION['newsql'] .= ',student_mp_comments smc ';
            }
            if ($_REQUEST['goal_title'] || $_REQUEST['goal_description']) {
                $sql .= ',student_goal g ';
                $_SESSION['newsql'] .= ',student_goal g ';
            }
            if ($_REQUEST['progress_name'] || $_REQUEST['progress_description']) {
                $sql .= ',student_goal_progress p ';
                $_SESSION['newsql'] .= ',student_goal_progress p ';
            }
            if ($_REQUEST['doctors_note_comments'] || $_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
                $sql .= ',student_medical_notes smn ';
                $_SESSION['newsql'] .= ',student_medical_notes smn ';
            }
            if ($_REQUEST['type'] || $_REQUEST['imm_comments'] || $_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
                $sql .= ',student_immunization sm ';
                $_SESSION['newsql'] .= ',student_immunization sm ';
            }
            if ($_REQUEST['med_alrt_title'] || $_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
                $sql .= ',student_medical_alerts sma ';
                $_SESSION['newsql'] .= ',student_medical_alerts sma ';
            }
            if ($_REQUEST['reason'] || $_REQUEST['result'] || $_REQUEST['med_vist_comments'] || $_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
                $sql .= ',student_medical_visits smv ';
                $_SESSION['newsql'] .= ',student_medical_visits smv ';
            }
            $_SESSION['new_sql'] .= $_SESSION['newsql'];
            $sql .= ' ,student_enrollment ssm ';
            $_SESSION['new_sql'] .= ' ,student_enrollment ssm ';
            $sql .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID
					AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR
					AND (ss.MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ')  OR (ss.START_DATE<=\'' . date('Y-m-d') . '\'  AND (ss.END_DATE>=\'' . date('Y-m-d') . '\'  OR ss.END_DATE IS NULL)))
					AND (cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'
					AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID';
            $_SESSION['new_sql'] .= $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.STUDENT_ID=ss.STUDENT_ID
					AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR
					AND (ss.MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ')   OR (ss.START_DATE<=\'' . date('Y-m-d') . '\'  AND (ss.END_DATE>=\'' . date('Y-m-d') . '\'  OR ss.END_DATE IS NULL)))
					AND (cp.TEACHER_ID=\'' . User('STAFF_ID') . '\' OR cp.SECONDARY_TEACHER_ID=\'' . User('STAFF_ID') . '\') AND cp.COURSE_PERIOD_ID=\'' . UserCoursePeriod() . '\'
					AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID';
            if ($_REQUEST['include_inactive'] == 'Y') {
                $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR ORDER BY START_DATE DESC LIMIT 1)';
                $sql .= ' AND ss.START_DATE=(SELECT START_DATE FROM schedule WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ') AND COURSE_ID=cp.COURSE_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID ORDER BY START_DATE DESC LIMIT 1)';
                $_SESSION['new_sql'] .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR ORDER BY START_DATE DESC LIMIT 1)';
                $_SESSION['new_sql'] .= ' AND ss.START_DATE=(SELECT START_DATE FROM schedule WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ') AND COURSE_ID=cp.COURSE_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID ORDER BY START_DATE DESC LIMIT 1)';
            } else {
                $sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))';
                $sql .= $_SESSION['inactive_stu_filter'] = ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))';

                $_SESSION['new_sql'] .= ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))';
                $_SESSION['new_sql'] .= ' AND (ssm.START_DATE IS NOT NULL AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))';
            }

            if (!$extra['SELECT_ONLY'] && $_REQUEST['include_inactive'] == 'Y') {
                $extra['columns_after']['ACTIVE'] = 'School Status';
                $extra['columns_after']['ACTIVE_SCHEDULE'] = 'Course Status';
            }
            break;

        case 'parent':
        case 'student':
            $sql = 'SELECT ';
            if ($extra['SELECT_ONLY'])
                $sql .= $extra['SELECT_ONLY'];
            else {
                if (Preferences('NAME') == 'Common')
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,';
                else
                    $sql .= 'CONCAT(s.LAST_NAME,\', \',s.FIRST_NAME,\' \',COALESCE(s.MIDDLE_NAME,\' \')) AS FULL_NAME,';
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];
            }
            $sql .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
					WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate() . '\'>ssm.START_DATE)) AND ssm.STUDENT_ID' . ($extra['ASSOCIATED'] ? ' IN (SELECT STUDENT_ID FROM students_join_people WHERE PERSON_ID=\'' . $extra['ASSOCIATED'] . '\')' : '=\'' . UserStudentID() . '\'');
            break;
        default:
            exit('Error');
    }
    if ($expanded_view == true) {
        $custom_str = CustomFields('where', '', 1);
        if ($custom_str != '')
            $_SESSION['custom_count_sql'] = $custom_str;

        $sql .= $custom_str;
    } elseif ($expanded_view == false) {
        $custom_str = CustomFields('where', '', 2);
        if ($custom_str != '')
            $_SESSION['custom_count_sql'] = $custom_str;

        $sql .= $custom_str;
    } else {
        $custom_str = CustomFields('where');
        if ($custom_str != '')
            $_SESSION['custom_count_sql'] = $custom_str;

        $sql .= $custom_str;
    }

    $sql .= $extra['WHERE'] . ' ';
    $sql = appendSQL_Absence_Summary($sql, $extra);



    if ($extra['GROUP'])
        $sql .= ' GROUP BY ' . $extra['GROUP'];

    if (!$extra['ORDER_BY'] && !$extra['SELECT_ONLY']) {
        if (Preferences('SORT') == 'Grade')
            $sql .= ' ORDER BY (SELECT SORT_ORDER FROM school_gradelevels WHERE ID=ssm.GRADE_ID),FULL_NAME';
        else
            $sql .= ' ORDER BY FULL_NAME';
        $sql .= $extra['ORDER'];
    } elseif ($extra['ORDER_BY'] && !($_SESSION['stu_search']['sql'] && $_REQUEST['return_session']))
        $sql .= ' ORDER BY ' . $extra['ORDER_BY'];

    if ($extra['DEBUG'] === true)
        echo '<!--' . $sql . '-->';

    $_SESSION['mainSQL'] = array(
        'SQL'           =>  $sql,
        'FUNCTIONS'     =>  $functions,
        'EXTRA_GROUP'   =>  $extra['group']
    );

    $all_return = DBGet(DBQuery($sql), $functions, $extra['group']);

    $_SESSION['AL_RES_COUNT'] = count($all_return);

    if ($extra['LIMIT']) {
        $sql = $sql . ' LIMIT ' . $extra['LIMIT'];
    } else {
        if (!$_REQUEST['_openSIS_PDF'] || $_REQUEST['_openSIS_PDF'] == false) {
            $sql = $sql . ' LIMIT 0,50';
        }
    }

    $return = DBGet(DBQuery($sql), $functions, $extra['group']);
    $_SESSION['count_stu'] = count($return);
    return $return;
}

function appendSQL_Absence_Summary($sql, &$extra)
{
    global $_openSIS, $imm_date, $med_date, $nv_date, $ma_date;
    if ($_REQUEST['stuid']) {
        $sql .= ' AND ssm.STUDENT_ID = \'' . singleQuoteReplace("'", "\'", $_REQUEST['stuid']) . '\' ';
        $_SESSION['newsql1'] .= ' AND ssm.STUDENT_ID = \'' . singleQuoteReplace("'", "\'", $_REQUEST['stuid']) . '\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Student ID: </b></font>' . $_REQUEST['stuid'] . '<BR>';
    }
    if ($_REQUEST['altid']) {

        $sql .= ' AND LOWER(s.ALT_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['altid']))) . '%\' ';
        $_SESSION['newsql1'] .= ' AND LOWER(s.ALT_ID) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['altid']))) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Student ID: </b></font>' . $_REQUEST['stuid'] . '<BR>';
    }
    if ($_REQUEST['last']) {
        $sql .= ' AND LOWER(s.LAST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['last']))) . '%\' ';
        $_SESSION['newsql1'] .= ' AND LOWER(s.LAST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['last']))) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Last Name starts with: </b></font>' . stripslashes(trim($_REQUEST['last'])) . '<BR>';
    }
    if ($_REQUEST['first']) {
        $sql .= ' AND LOWER(s.FIRST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['first']))) . '%\' ';
        $_SESSION['newsql1'] .= ' AND LOWER(s.FIRST_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['first']))) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>First Name starts with: </b></font>' . stripslashes(trim($_REQUEST['first'])) . '<BR>';
    }
    if ($_REQUEST['grade']) {
        $sql .= ' AND ssm.GRADE_ID = \'' . singleQuoteReplace("'", "\'", $_REQUEST['grade']) . '\' ';
        $_SESSION['newsql1'] .= ' AND ssm.GRADE_ID = \'' . singleQuoteReplace("'", "\'", $_REQUEST['grade']) . '\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Grade: </b></font>' . GetGrade($_REQUEST['grade']) . '<BR>';
    }
    if ($_REQUEST['addr']) {
        $sql .= ' AND (LOWER(a.STREET_ADDRESS_1) LIKE \'%' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(a.CITY) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(a.STATE)=\'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '\' OR ZIPCODE LIKE \'' . trim(singleQuoteReplace("'", "\'", $_REQUEST['addr'])) . '%\')';
        $_SESSION['newsql1'] .= ' AND (LOWER(a.STREET_ADDRESS_1) LIKE \'%' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(a.CITY) LIKE \'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '%\' OR LOWER(a.STATE)=\'' . singleQuoteReplace("'", "\'", strtolower(trim($_REQUEST['addr']))) . '\' OR ZIPCODE LIKE \'' . trim(singleQuoteReplace("'", "\'", $_REQUEST['addr'])) . '%\')';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Address contains: </b></font>' . trim($_REQUEST['addr']) . '<BR>';
    }
    if ($_REQUEST['preferred_hospital']) {
        $sql .= ' AND LOWER(s.PREFERRED_HOSPITAL) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['preferred_hospital'])) . '%\' ';
        $_SESSION['newsql1'] .= ' AND LOWER(s.PREFERRED_HOSPITAL) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['preferred_hospital'])) . '%\' ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Preferred Medical Facility starts with: </b></font>' . $_REQUEST['preferred_hospital'] . '<BR>';
    }
    if ($_REQUEST['mp_comment']) {
        $sql .= ' AND LOWER(smc.COMMENT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['mp_comment'])) . '%\' AND s.STUDENT_ID=smc.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(smc.COMMENT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['mp_comment'])) . '%\' AND s.STUDENT_ID=smc.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Comments starts with: </b></font>' . $_REQUEST['mp_comment'] . '<BR>';
    }
    if ($_REQUEST['goal_title']) {
        $sql .= ' AND LOWER(g.GOAL_TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_title'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(g.GOAL_TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_title'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>GoalInc Title starts with: </b></font>' . $_REQUEST['goal_title'] . '<BR>';
    }
    if ($_REQUEST['goal_description']) {
        $sql .= ' AND LOWER(g.GOAL_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_description'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(g.GOAL_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['goal_description'])) . '%\' AND s.STUDENT_ID=g.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>GoalInc Description starts with: </b></font>' . $_REQUEST['goal_description'] . '<BR>';
    }
    if ($_REQUEST['progress_name']) {
        $sql .= ' AND LOWER(p.PROGRESS_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_name'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(p.PROGRESS_NAME) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_name'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Progress Period Name starts with: </b></font>' . $_REQUEST['progress_name'] . '<BR>';
    }
    if ($_REQUEST['progress_description']) {
        $sql .= ' AND LOWER(p.PROGRESS_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_description'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(p.PROGRESS_DESCRIPTION) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['progress_description'])) . '%\' AND s.STUDENT_ID=p.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Progress Assessment starts with: </b></font>' . $_REQUEST['progress_description'] . '<BR>';
    }
    if ($_REQUEST['doctors_note_comments']) {
        $sql .= ' AND LOWER(smn.DOCTORS_NOTE_COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['doctors_note_comments'])) . '%\' AND s.STUDENT_ID=smn.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(smn.DOCTORS_NOTE_COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['doctors_note_comments'])) . '%\' AND s.STUDENT_ID=smn.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Doctor\'s Note starts with: </b></font>' . $_REQUEST['doctors_note_comments'] . '<BR>';
    }
    if ($_REQUEST['type']) {
        $sql .= ' AND LOWER(sm.TYPE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['type'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(sm.TYPE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['type'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Type starts with: </b></font>' . $_REQUEST['type'] . '<BR>';
    }
    if ($_REQUEST['imm_comments']) {
        $sql .= ' AND LOWER(sm.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['imm_comments'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(sm.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['imm_comments'])) . '%\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Comments starts with: </b></font>' . $_REQUEST['imm_comments'] . '<BR>';
    }
    if ($_REQUEST['imm_day'] && $_REQUEST['imm_month'] && $_REQUEST['imm_year']) {
        $imm_date = $_REQUEST['imm_year'] . '-' . $_REQUEST['imm_month'] . '-' . $_REQUEST['imm_day'];
        $sql .= ' AND sm.MEDICAL_DATE =\'' . date('Y-m-d', strtotime($imm_date)) . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND sm.MEDICAL_DATE =\'' . date('Y-m-d', strtotime($imm_date)) . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Immunization Date: </b></font>' . $imm_date . '<BR>';
    } elseif ($_REQUEST['imm_day'] || $_REQUEST['imm_month'] || $_REQUEST['imm_year']) {
        if ($_REQUEST['imm_day']) {
            $sql .= ' AND SUBSTR(sm.MEDICAL_DATE,9,2) =\'' . $_REQUEST['imm_day'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(sm.MEDICAL_DATE,9,2) =\'' . $_REQUEST['imm_day'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Day :" . $_REQUEST['imm_day'];
        }
        if ($_REQUEST['imm_month']) {
            $sql .= ' AND SUBSTR(sm.MEDICAL_DATE,6,2) =\'' . $_REQUEST['imm_month'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(sm.MEDICAL_DATE,6,2) =\'' . $_REQUEST['imm_month'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Month :" . $_REQUEST['imm_month'];
        }
        if ($_REQUEST['imm_year']) {
            $sql .= ' AND SUBSTR(sm.MEDICAL_DATE,1,4) =\'' . $_REQUEST['imm_year'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(sm.MEDICAL_DATE,1,4) =\'' . $_REQUEST['imm_year'] . '\' AND s.STUDENT_ID=sm.STUDENT_ID ';
            $imm_date .= " Year :" . $_REQUEST['imm_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Immunization Date: </b></font>' . $imm_date . '<BR>';
    }
    if ($_REQUEST['med_day'] && $_REQUEST['med_month'] && $_REQUEST['med_year']) {
        $med_date = $_REQUEST['med_year'] . '-' . $_REQUEST['med_month'] . '-' . $_REQUEST['med_day'];
        $sql .= ' AND smn.DOCTORS_NOTE_DATE=\'' . date('Y-m-d', strtotime($med_date)) . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND smn.DOCTORS_NOTE_DATE =\'' . date('Y-m-d', strtotime($med_date)) . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Date: </b></font>' . $med_date . '<BR>';
    } elseif ($_REQUEST['med_day'] || $_REQUEST['med_month'] || $_REQUEST['med_year']) {
        if ($_REQUEST['med_day']) {
            $sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,9,2) =\'' . $_REQUEST['med_day'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,9,2) =\'' . $_REQUEST['med_day'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Day :" . $_REQUEST['med_day'];
        }
        if ($_REQUEST['med_month']) {
            $sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,6,2) =\'' . $_REQUEST['med_month'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,6,2) =\'' . $_REQUEST['med_month'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Month :" . $_REQUEST['med_month'];
        }
        if ($_REQUEST['med_year']) {
            $sql .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,1,4) =\'' . $_REQUEST['med_year'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(smn.DOCTORS_NOTE_DATE,1,4) =\'' . $_REQUEST['med_year'] . '\' AND s.STUDENT_ID=smn.STUDENT_ID ';
            $med_date .= " Year :" . $_REQUEST['med_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Date: </b></font>' . $med_date . '<BR>';
    }
    if ($_REQUEST['ma_day'] && $_REQUEST['ma_month'] && $_REQUEST['ma_year']) {
        $ma_date = $_REQUEST['ma_year'] . '-' . $_REQUEST['ma_month'] . '-' . $_REQUEST['ma_day'];
        $sql .= ' AND sma.ALERT_DATE =\'' . date('Y-m-d', strtotime($ma_date)) . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND sma.ALERT_DATE =\'' . date('Y-m-d', strtotime($ma_date)) . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Alert Date: </b></font>' . $ma_date . '<BR>';
    } elseif ($_REQUEST['ma_day'] || $_REQUEST['ma_month'] || $_REQUEST['ma_year']) {
        if ($_REQUEST['ma_day']) {
            $sql .= ' AND SUBSTR(sma.ALERT_DATE,9,2) =\'' . $_REQUEST['ma_day'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(sma.ALERT_DATE,9,2) =\'' . $_REQUEST['ma_day'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Day :" . $_REQUEST['ma_day'];
        }
        if ($_REQUEST['ma_month']) {
            $sql .= ' AND SUBSTR(sma.ALERT_DATE,6,2) =\'' . $_REQUEST['ma_month'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(sma.ALERT_DATE,6,2) =\'' . $_REQUEST['ma_month'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Month :" . $_REQUEST['ma_month'];
        }
        if ($_REQUEST['ma_year']) {
            $sql .= ' AND SUBSTR(sma.ALERT_DATE,1,4) =\'' . $_REQUEST['ma_year'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(sma.ALERT_DATE,1,4) =\'' . $_REQUEST['ma_year'] . '\' AND s.STUDENT_ID=sma.STUDENT_ID ';
            $ma_date .= " Year :" . $_REQUEST['ma_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Medical Alert Date: </b></font>' . $ma_date . '<BR>';
    }
    if ($_REQUEST['nv_day'] && $_REQUEST['nv_month'] && $_REQUEST['nv_year']) {
        $nv_date = $_REQUEST['nv_year'] . '-' . $_REQUEST['nv_month'] . '-' . $_REQUEST['nv_day'];
        $sql .= ' AND smv.SCHOOL_DATE =\'' . date('Y-m-d', strtotime($nv_date)) . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND smv.SCHOOL_DATE =\'' . date('Y-m-d', strtotime($nv_date)) . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Date: </b></font>' . $nv_date . '<BR>';
    } elseif ($_REQUEST['nv_day'] || $_REQUEST['nv_month'] || $_REQUEST['nv_year']) {
        if ($_REQUEST['nv_day']) {
            $sql .= ' AND SUBSTR(smv.SCHOOL_DATE,9,2) =\'' . $_REQUEST['nv_day'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(smv.SCHOOL_DATE,9,2) =\'' . $_REQUEST['nv_day'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Day :" . $_REQUEST['nv_day'];
        }
        if ($_REQUEST['nv_month']) {
            $sql .= ' AND SUBSTR(smv.SCHOOL_DATE,6,2) =\'' . $_REQUEST['nv_month'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(smv.SCHOOL_DATE,6,2) =\'' . $_REQUEST['nv_month'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Month :" . $_REQUEST['nv_month'];
        }
        if ($_REQUEST['nv_year']) {
            $sql .= ' AND SUBSTR(smv.SCHOOL_DATE,1,4) =\'' . $_REQUEST['nv_year'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $_SESSION['newsql1'] .= ' AND SUBSTR(smv.SCHOOL_DATE,1,4) =\'' . $_REQUEST['nv_year'] . '\' AND s.STUDENT_ID=smv.STUDENT_ID ';
            $nv_date .= " Year :" . $_REQUEST['nv_year'];
        }
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Date: </b></font>' . $nv_date . '<BR>';
    }


    if ($_REQUEST['med_alrt_title']) {
        $sql .= ' AND LOWER(sma.TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_alrt_title'])) . '%\' AND s.STUDENT_ID=sma.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(sma.TITLE) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_alrt_title'])) . '%\' AND s.STUDENT_ID=sma.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Alert starts with: </b></font>' . $_REQUEST['med_alrt_title'] . '<BR>';
    }
    if ($_REQUEST['reason']) {
        $sql .= ' AND LOWER(smv.REASON) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['reason'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Reason starts with: </b></font>' . $_REQUEST['reason'] . '<BR>';
    }
    if ($_REQUEST['result']) {
        $sql .= ' AND LOWER(smv.RESULT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['result'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(smv.RESULT) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['result'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Result starts with: </b></font>' . $_REQUEST['result'] . '<BR>';
    }
    if ($_REQUEST['med_vist_comments']) {
        $sql .= ' AND LOWER(smv.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_vist_comments'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        $_SESSION['newsql1'] .= ' AND LOWER(smv.COMMENTS) LIKE \'' . singleQuoteReplace("'", "\'", strtolower($_REQUEST['med_vist_comments'])) . '%\' AND s.STUDENT_ID=smv.STUDENT_ID ';
        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Nurse Visit Comments starts with: </b></font>' . $_REQUEST['med_vist_comments'] . '<BR>';
    }
    if ($_REQUEST['day_to_birthdate'] && $_REQUEST['month_to_birthdate'] && $_REQUEST['day_from_birthdate'] && $_REQUEST['month_from_birthdate']) {
        $date_to = $_REQUEST['month_to_birthdate'] . '-' . $_REQUEST['day_to_birthdate'];
        $date_from = $_REQUEST['month_from_birthdate'] . '-' . $_REQUEST['day_from_birthdate'];

        $sql .= ' AND (SUBSTR(s.BIRTHDATE,6) BETWEEN \'' . $date_from . '\' AND \'' . $date_to . '\') ';
        $_SESSION['newsql1'] .= ' AND (SUBSTR(s.BIRTHDATE,6) BETWEEN \'' . $date_from . '\' AND \'' . $date_to . '\') ';

        if (!$extra['NoSearchTerms'])
            $_openSIS['SearchTerms'] .= '<font color=gray><b>Birthday Starts from ' . $date_from . ' to ' . $date_to . '</b></font>';
    }
    // test cases start
    // test cases end
    if ($_SESSION['stu_search']['sql'] && $_REQUEST['return_session']) {
        if (($_REQUEST['absence_go'] || $_REQUEST['chk']) && (User('PROFILE') == 'teacher' || User('PROFILE') == 'admin') && $_REQUEST['return_session']) {
            $new_sql = $_SESSION['new_sql'] . $_SESSION['newsql1'];
            unset($_SESSION['inactive_stu_filter']);
            return $new_sql;
        } else {
            unset($_SESSION['inactive_stu_filter']);
            return $_SESSION['stu_search']['sql'];
        }
    } else {
        if ($_REQUEST['sql_save_session'] && !$_SESSION['stu_search']['search_from_grade']) {
            $_SESSION['stu_search']['sql'] = $sql;
        } else if ($_SESSION['stu_search']['search_from_grade']) {
            unset($_SESSION['stu_search']['search_from_grade']);
        }
        return $sql;
    }
}

function _make_Parents($value, $column = '')
{
    global $THIS_RET;

    $sql = 'SELECT DISTINCT person_id AS STAFF_ID, CONCAT( people.LAST_NAME, \' \', people.FIRST_NAME ) AS PARENT FROM students_join_people sju, people, staff_school_relationship ssr WHERE people.staff_id = sju.person_id and sju.student_id=\'' . $value . '\' AND ssr.syear=\'' . UserSyear() . '\'';
    $parents_RET = DBGet(DBQuery($sql));
    $parents = '';
    foreach ($parents_RET as $parent) {
        $parents .= $parent['PARENT'] . ',';
    }
    return trim($parents, ',');
}

function _make_sections($value)
{
    if ($value != '') {
        $get = DBGet(DBQuery('SELECT NAME FROM school_gradelevel_sections WHERE ID=' . $value));
        return $get[1]['NAME'];
    } else
        return '';
}
