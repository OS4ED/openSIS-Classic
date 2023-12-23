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

if (isset($_SESSION['student_id']) && $_SESSION['student_id'] != '') {
    $_REQUEST['search_modfunc'] = 'list';
}

if (!$_REQUEST['modfunc']) {
    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . ' ' . _found . '.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearch(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-4"><div id="course_modal"></div></div>';
    echo '<div class="col-md-4"><div id="cp_modal"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal



    echo '<div id="modal_default_request" class="modal fade">';
    echo '<div class="modal-dialog">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">' . _chooseCourse . '</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-6">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' ' . _subjectWas : ' ' . _subjectsWere) . ' ' . _found . '.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>' . _subject . '</th></tr></thead><tbody>';
        foreach ($subjects_RET as $val) {
            echo '<tr><td><a href=javascript:void(0); onclick="chooseCpModalSearchRequest(' . $val['SUBJECT_ID'] . ',\'courses\')">' . $val['TITLE'] . '</a></td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
    echo '<div class="col-md-6"><div id="course_modal_request"></div></div>';
    echo '</div>'; //.row
    echo '</div>'; //.modal-body

    echo '</div>'; //.modal-content
    echo '</div>'; //.modal-dialog
    echo '</div>'; //.modal
}

if ($_REQUEST['modfunc'] == 'save') {


    if (count($_REQUEST['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';



        if ($_REQUEST['mailing_labels'] == 'Y')
            Widgets('mailing_labels');

        $RET = GetStuList($extra);

        if (count($RET)) {
            include('modules/students/includes/FunctionsInc.php');

            //------------Comment Heading -----------------------------------------------------

            $people_categories_RET = DBGet(DBQuery('SELECT c.ID AS CATEGORY_ID,c.TITLE AS CATEGORY_TITLE,f.ID,f.TITLE,f.TYPE,f.SELECT_OPTIONS,f.DEFAULT_SELECTION,f.REQUIRED FROM people_field_categories c,people_fields f WHERE f.CATEGORY_ID=c.ID ORDER BY c.SORT_ORDER,c.TITLE,f.SORT_ORDER,f.TITLE'), array(), array('CATEGORY_ID'));

            explodeCustom($people_categories_RET, $people_custom, 'p');

            unset($_REQUEST['modfunc']);
            $handle = PDFStart();

            foreach ($RET as $student) {
                $_SESSION['student_id'] = $student['STUDENT_ID'];
                echo "<table width=100% style=\" font-family:Arial; font-size:12px;\" >";
                echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _studentInformationReport . "</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />" . _poweredByOpenSis . "</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";

                echo "<table cellspacing=0  border=\"0\" style=\"border-collapse:collapse\">";
                echo "<tr><td colspan=3 style=\"height:18px\"></td></tr>";
                $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . $student['STUDENT_ID'] . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND FILE_INFO=\'stuimg\''));
                // if ($StudentPicturesPath && (($file = @fopen($picture_path = $StudentPicturesPath . '/' . UserStudentID() . '.JPG', 'r')) || ($file = @fopen($picture_path = $StudentPicturesPath . '/' . UserStudentID() . '.JPG', 'r')))) {
                // echo '<tr><td width=300><IMG SRC="' . $picture_path . '?id=' . rand(6, 100000) . '" width=150  style="padding:4px; background-color:#fff; border:1px solid #333" ></td><td width=12px></td>';
                if (count($stu_img_info) == 0) {
                    $stu_img_info = DBGet(DBQuery('SELECT * FROM user_file_upload WHERE USER_ID=' . $student['STUDENT_ID'] . ' AND PROFILE_ID=3 AND SCHOOL_ID=' . UserSchool() . ' AND FILE_INFO=\'stuimg\''));
                }
                if (count($stu_img_info) > 0) {
                    echo '<tr><td width=300><IMG src="data:image/jpeg;base64,' . base64_encode($stu_img_info[1]['CONTENT']) . '" width=150 class=pic> </td><td width=12px></td>';
                } else {
                    echo '<tr><td width=300><IMG SRC="assets/noimage.jpg?id=' . rand(6, 100000) . '" width=144  style="padding:4px; background-color:#fff; border:1px solid #333"></td><td width=12px></td>';
                }

                is_file($file) ? fclose($file) : '';



                # ---------------- Sql Including Comment ------------------------------- #

                // $sql = DBGet(DBQuery('SELECT s.gender AS GENDER, s.ethnicity_id AS ETHNICITY, s.common_name AS COMMON_NAME,  s.social_security AS SOCIAL_SEC_NO, s.birthdate AS BIRTHDAY, s.email AS EMAIL, s.phone AS PHONE, s.language_id AS LANGUAGE, se.START_DATE AS START_DATE,sec.TITLE AS STATUS, se.NEXT_SCHOOL AS ROLLING  FROM students s, student_enrollment se,student_enrollment_codes sec WHERE s.STUDENT_ID=\'' . $_SESSION['student_id'] . '\'  AND se.SCHOOL_ID=\'' . UserSchool() . '\' AND se.SYEAR=sec.SYEAR AND s.STUDENT_ID=se.STUDENT_ID'), array('BIRTHDAY' => 'ProperDate'));
                $sql = DBGet(DBQuery('SELECT s.gender AS GENDER,sgs.name AS SECTION_NAME,se.section_id AS SECTION_ID,s.estimated_grad_date AS ESTIMATE_GRADE_DATE, etn.ethnicity_name AS ETHNICITY, s.ethnicity_id, s.common_name AS COMMON_NAME,  s.social_security AS SOCIAL_SEC_NO, s.birthdate AS BIRTHDAY, s.email AS EMAIL, s.phone AS PHONE, lan.language_name AS LANGUAGE, s.language_id, se.START_DATE AS START_DATE,sec.TITLE AS STATUS, se.NEXT_SCHOOL AS ROLLING  FROM students s LEFT JOIN ethnicity etn ON s.ethnicity_id = etn.ethnicity_id LEFT JOIN language lan ON s.language_id = lan.language_id, student_enrollment se LEFT JOIN school_gradelevel_sections sgs ON se.section_id = sgs.id ,student_enrollment_codes sec WHERE s.STUDENT_ID=\'' . $_SESSION['student_id'] . '\'  AND se.SCHOOL_ID=\'' . UserSchool() . '\' AND se.SYEAR=sec.SYEAR AND s.STUDENT_ID=se.STUDENT_ID'), array('BIRTHDAY' => 'ProperDate', 'ESTIMATE_GRADE_DATE' => 'ProperDate'));


                $sql = $sql[1];

                $medical_info = DBGet((DBQuery('SELECT  mi.physician AS PHYSICIAN_NAME, mi.physician_phone AS PHYSICIAN_PHONO,mi.preferred_hospital AS HOSPITAL FROM medical_info mi WHERE mi.STUDENT_ID=\'' . $_SESSION['student_id'] . '\'  AND mi.SCHOOL_ID=\'' . UserSchool() . '\'')));
                $sql['PHYSICIAN_NAME'] = $medical_info[1]['PHYSICIAN_NAME'];
                $sql['PHYSICIAN_PHONO'] = $medical_info[1]['PHYSICIAN_PHONO'];
                $sql['HOSPITAL'] = $medical_info[1]['HOSPITAL'];


                echo "<td valign=top width=300>";
                echo "<table width=100% >";




                if ($_REQUEST['category']['1']) {
                    echo "<tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _personalInformation . "</td></tr>";
                    //----------------------------------------------
                    echo "<tr><td width=45% style='font-weight:bold'>" . _studentName . ":</td>";
                    echo "<td width=55%>" . $student['FULL_NAME'] . "</td></tr>";
                    echo "<tr><td style='font-weight:bold'>" . _id . ":</td>";
                    echo "<td>" . $student['STUDENT_ID'] . " </td></tr>";
                    if ($student['ALT_ID'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _altId . ":</td>";
                        echo "<td>" . $student['ALT_ID'] . " </td></tr>";
                    }
                    echo "<tr><td style='font-weight:bold'>" . _grade . ":</td>";
                    echo "<td>" . $student['GRADE_ID'] . " </td></tr>";
                    if ($sql['SECTION_ID'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _section . ":</td>";
                        echo "<td>" . $sql['SECTION_NAME'] . "</td></tr>";
                    }
                    echo "<tr><td style='font-weight:bold'>" . _gender . ":</td>";
                    echo "<td>" . $sql['GENDER'] . "</td></tr>";
                    echo "<tr><td style='font-weight:bold'>" . _ethnicity . ":</td>";
                    echo "<td>" . $sql['ETHNICITY'] . "</td></tr>";
                    if ($sql['COMMON_NAME'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _commonName . ":</td>";
                        echo "<td>" . $sql['COMMON_NAME'] . "</td></tr>";
                    }
                    if ($sql['SOCIAL_SEC_NO'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _socialSecurity . ":</td>";
                        echo "<td>" . $sql['SOCIAL_SEC_NO'] . "</td></tr>";
                    }
                    echo "<tr><td style='font-weight:bold'>" . _birthdate . ":</td>";
                    echo "<td>" . $sql['BIRTHDAY'] . "</td></tr>";
                    if ($sql['ESTIMATE_GRADE_DATE'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _estimatedGradDate . ":</td>";
                        echo "<td>" . $sql['ESTIMATE_GRADE_DATE'] . "</td></tr>";
                    }
                    if ($sql['LANGUAGE'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _languageSpoken . ":</td>";
                        echo "<td>" . $sql['LANGUAGE'] . "</td></tr>";
                    }
                    if ($sql['EMAIL'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _emailId . ":</td>";
                        echo "<td>" . $sql['EMAIL'] . "</td></tr>";
                    }
                    if ($sql['PHONE'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _phone . ":</td>";
                        echo "<td>" . $sql['PHONE'] . "</td></tr>";
                        echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                    }
                    if ($sql['ROLLING'] != '' && $sql['ROLLING'] != 0 && $sql['ROLLING'] != -1) {

                        $rolling = DBGet(DBQuery('SELECT TITLE FROM schools WHERE ID=\'' . $sql['ROLLING'] . '\''));

                        $rolling = $rolling[1]['TITLE'];
                    } elseif ($sql['ROLLING'] != 0)
                        $rolling = _doNotEnrollAfterThisSchoolYear;

                    elseif ($sql['ROLLING'] != -1)
                        $rolling = 'Retain';



                    if ($student['MAILING_LABEL'] != '') {

                        echo "<tr>";

                        echo "<td colspan=2>" . $student['MAILING_LABEL'] . "</td></tr>";
                    }
                    //----------------------------------------------
                }

                ######################## PRINT MEDICAL CUSTOM FIELDS ################################################
                $fields_RET = DBGet(DBQuery('SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED FROM custom_fields WHERE CATEGORY_ID=1 ORDER BY SORT_ORDER,TITLE'));
                $custom_RET = DBGet(DBQuery('SELECT * FROM students WHERE STUDENT_ID=\'' . UserStudentID() . '\''));
                $value = $custom_RET[1];
                if (count($fields_RET)) {

                    $i = 1;
                    foreach ($fields_RET as $field) {
                        if (($value['CUSTOM_' . $field['ID']]) != '') {
                            echo '<TR>';
                            echo '<td style="font-weight:bold">' . $field['TITLE'] . ':</td><td>';
                            if ($field['TYPE'] == 'date') {
                                $cust_date = DBGet(DBQuery('SELECT CUSTOM_\'' . $field[ID] . '\' AS C_DATE FROM students WHERE STUDENT_ID=\'' . UserStudentID() . '\''), array('C_DATE' => 'ProperDate'));
                                echo $cust_date[1]['C_DATE'];
                            } else {
                                echo $value['CUSTOM_' . $field['ID']];
                            }
                            echo '</TD>';
                            echo '</TR>';
                        }
                    }
                }
                ####################################################################################################
                echo '</table>';
                echo "</td></tr>";
                echo "<tr><td colspan=3 height=18px></td></tr>";
                echo "<tr><td valign=top width=300>";
                if ($_REQUEST['category']['3']) {
                    $addr_sql = 'SELECT sa.ID AS ADDRESS_ID, sjp.RELATIONSHIP AS STUDENT_RELATION, sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,p.HOME_PHONE AS PHONE,
               (select STREET_ADDRESS_1 FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_ADDRESS,
               (select STREET_ADDRESS_2 FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_STREET,
               (select CITY FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_CITY,
               (select STATE FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_STATE,
               (select ZIPCODE FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_ZIPCODE,
               p.CUSTODY
               FROM student_address sa,students_join_people sjp,people p 
               WHERE sa.STUDENT_ID=sjp.STUDENT_ID AND sa.STUDENT_ID=' . UserStudentID() . '  AND p.STAFF_ID=sjp.PERSON_ID AND p.STAFF_ID=sa.PEOPLE_ID AND sjp.EMERGENCY_TYPE=\'Primary\'
               UNION 
               SELECT sa.ID AS ADDRESS_ID,\'No Contacts\' AS STUDENT_RELATION,sa.STREET_ADDRESS_1 as ADDRESS,sa.STREET_ADDRESS_2 as STREET,sa.CITY,sa.STATE,sa.ZIPCODE,s.PHONE,
               (select STREET_ADDRESS_1 FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_ADDRESS,
               (select STREET_ADDRESS_2 FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_STREET,
               (select CITY FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_CITY,
               (select STATE FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_STATE,
               (select ZIPCODE FROM student_address WHERE student_id=' . UserStudentID() . ' AND TYPE=\'Mail\' ORDER BY ID DESC LIMIT 0,1) as MAIL_ZIPCODE,
               NULL AS CUSTODY
               FROM students s,student_address sa WHERE sa.STUDENT_ID=' . UserStudentID() . ' AND s.STUDENT_ID=sa.STUDENT_ID AND sa.TYPE=\'Home Address\'
               ORDER BY ADDRESS ASC,CUSTODY ASC,STUDENT_RELATION ';
                    $addresses_RET = DBGet(DBQuery($addr_sql));
                    $address_previous = "x";

                    if ($addresses_RET[1]['ADDRESS'] == '' && $addresses_RET[2]['ADDRESS'] != '' || $addresses_RET[2]['ADDRESS'] != $addresses_RET[1]['ADDRESS']) {
                        unset($addresses_RET[1]);
                    }
                    foreach ($addresses_RET as $address) {
                        $address_current = $address['ADDRESS'];
                        if ($address_current != $address_previous) {
                            echo "<table width=100%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px; font-weight:bold;\">" . _homeAddress . "</td></tr>";
                            echo "<tr><td width=45% style='font-weight:bold'>" . _addressLine_1 . ":</td>";
                            echo "<td width=55%>" . $address['ADDRESS'] . "</td></tr>";
                            if ($address['STREET'] != '') {
                                echo "<tr><td width=35% style='font-weight:bold'>" . _addressLine_2 . ":</td>";
                                echo "<td width=65%>" . $address['STREET'] . "</td></tr>";
                            }
                            echo "<tr><td style='font-weight:bold'>" . _city . ":</td>";
                            echo "<td>" . ($address['CITY'] ? $address['CITY'] . '' : '') . "</td></tr>";
                            echo "<tr><td style='font-weight:bold'>" . _state . ":</td>";
                            echo "<td>" . $address['STATE'] . "</td></tr>";
                            echo "<tr><td style='font-weight:bold'>" . _zipcode . ":</td>";
                            echo "<td>" . ($address['ZIPCODE'] ? $address['ZIPCODE'] . '' : '') . "</td></tr>";
                            echo "</table>";

                            echo "</td><td></td><td valign=top width=300>";
                            echo "<table width=100%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _mailingAddress . "</td></tr>";
                            echo "<tr><td width=45% style='font-weight:bold'>" . _addressLine_1 . ":</td>";
                            echo "<td width=55%>" . $address['MAIL_ADDRESS'] . "</td></tr>";
                            if ($address['MAIL_STREET'] != '') {
                                echo "<tr><td width=35% style='font-weight:bold'>" . _addressLine_2 . ":</td>";
                                echo "<td width=65%>" . $address['MAIL_STREET'] . "</td></tr>";
                            }
                            echo "<tr><td style='font-weight:bold'>" . _city . ":</td>";
                            echo "<td>" . $address['MAIL_CITY'] . "</td></tr>";
                            echo "<tr><td style='font-weight:bold'>" . _state . ":</td>";
                            echo "<td>" . $address['MAIL_STATE'] . "</td></tr>";
                            echo "<tr><td style='font-weight:bold'>" . _zipcode . ":</td>";
                            echo "<td>" . $address['MAIL_ZIPCODE'] . "</td></tr>";
                            echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                            echo "</table>";
                            echo "</td></tr>";
                            echo "<tr><td valign=top>";

                            foreach ($address_categories_RET as $categories) {
                                if (!$categories[1]['RESIDENCE'] && !$categories[1]['MAILING'] && !$categories[1]['BUS'] || $categories[1]['RESIDENCE'] == 'Y' && $address['RESIDENCE'] == 'Y' || $categories[1]['MAILING'] == 'Y' && $address['MAILING'] == 'Y' || $categories[1]['BUS'] == 'Y' && ($address['BUS_PICKUP'] == 'Y' || $address['BUS_DROPOFF'] == 'Y'))
                                    printCustom($categories, $address);
                            }


                            echo "<table width=100% border=0><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _primaryEmergencyContact . "</td></tr>";
                            $pri_par_id = DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID=' . UserStudentID() . ' AND EMERGENCY_TYPE=\'Primary\''));
                            $sec_par_id = DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID=' . UserStudentID() . ' AND EMERGENCY_TYPE=\'Secondary\''));

                            $Stu_prim_address = DBGet(DBQuery('SELECT p.FIRST_NAME as PRI_FIRST_NAME,p.LAST_NAME as PRI_LAST_NAME,sa.STREET_ADDRESS_1 as PRIM_ADDRESS,sa.STREET_ADDRESS_2 as PRIM_STREET,sa.CITY as PRIM_CITY,sa.STATE as PRIM_STATE,sa.ZIPCODE as PRIM_ZIPCODE,sjp.RELATIONSHIP as PRIM_STUDENT_RELATION,p.home_phone as PRIM_HOME_PHONE,p.work_phone as PRIM_WORK_PHONE,p.cell_phone as PRIM_CELL_PHONE, p.email as PRIM_EMAIL, p.custody as PRIM_CUSTODY FROM  student_address sa,people p,students_join_people sjp WHERE  sa.PEOPLE_ID=p.STAFF_ID  AND p.STAFF_ID=\'' . $pri_par_id[1]['PERSON_ID'] . '\' AND sjp.PERSON_ID=p.STAFF_ID LIMIT 1'));
                            $Stu_sec_address = DBGet(DBQuery('SELECT p.FIRST_NAME as SEC_FIRST_NAME,p.LAST_NAME as SEC_LAST_NAME,sa.STREET_ADDRESS_1 as SEC_ADDRESS,sa.STREET_ADDRESS_2 as SEC_STREET,sa.type as SA_TYPE,sa.CITY as SEC_CITY,sa.STATE as SEC_STATE,sa.ZIPCODE as SEC_ZIPCODE,sjp.RELATIONSHIP as SEC_STUDENT_RELATION,sjp.EMERGENCY_TYPE,p.home_phone as SEC_HOME_PHONE,p.work_phone as SEC_WORK_PHONE,p.cell_phone as SEC_CELL_PHONE, p.email as SEC_EMAIL, p.custody as SEC_CUSTODY FROM student_address sa,people p,students_join_people sjp WHERE p.STAFF_ID=\'' . $sec_par_id[1]['PERSON_ID'] . '\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Secondary\' AND sjp.PERSON_ID=p.STAFF_ID LIMIT 1'));

                            $st_ja_pe = DBGet(DBQuery('select * from students_join_people where  STUDENT_ID=\'' . UserStudentID() . '\' and EMERGENCY_TYPE=\'Secondary\''));
                            $contacts_RET[1] = $Stu_prim_address[1];
                            foreach ($Stu_sec_address[1] as $ind => $col)
                                $contacts_RET[1][$ind] = $col;

                            foreach ($contacts_RET as $contact) {


                                echo "<tr><td width=45% style='font-weight:bold'>" . _relation . " :</td><td width=55%>" . $contact['PRIM_STUDENT_RELATION'] . "</td></tr>";

                                echo "<tr><td style='font-weight:bold'>" . _firstName . " :</td><td>" . $contact['PRI_FIRST_NAME'] . "</td></tr>";
                                echo "<tr><td style='font-weight:bold'>" . _lastName . " :</td><td>" . $contact['PRI_LAST_NAME'] . "</td></tr>";

                                if ($contact['EMAIL'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _email . " :</td><td>" . $contact['EMAIL'] . "</td></tr>";
                                }

                                if ($contact['PRIM_ADDRESS'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _addressLine_1 . " :</td><td>" . $contact['PRIM_ADDRESS'] . "</td></tr>";
                                }

                                if ($contact['PRIM_STREET'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _addressLine_2 . " :</td><td>" . $contact['PRIM_STREET'] . "</td></tr>";
                                }

                                if ($contact['PRIM_CITY'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _city . " :</td><td>" . $contact['PRIM_CITY'] . "</td></tr>";
                                }

                                if ($contact['PRIM_STATE'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _state . " :</td><td>" . $contact['PRIM_STATE'] . "</td></tr>";
                                }

                                if ($contact['PRIM_ZIPCODE'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _zipcode . " :</td><td>" . $contact['PRIM_ZIPCODE'] . "</td></tr>";
                                }
                                if ($contact['PRIM_HOME_PHONE'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _homePhone . " :</td><td>" . $contact['PRIM_HOME_PHONE'] . "</td></tr>";
                                }
                                if ($contact['PRIM_WORK_PHONE'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _workPhone . " :</td><td>" . $contact['PRIM_WORK_PHONE'] . "</td></tr>";
                                }
                                if ($contact['PRIM_CELL_PHONE'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _cellPhone . " :</td><td>" . $contact['PRIM_CELL_PHONE'] . "</td></tr>";
                                }
                                if ($contact['PRIM_EMAIL'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _email . " :</td><td>" . $contact['PRIM_EMAIL'] . "</td></tr>";
                                }
                                if ($contact['PRIM_CUSTODY'] != '') {
                                    echo "<tr><td style='font-weight:bold'>" . _custody . " :</td><td>" . ($contact['PRIM_CUSTODY'] == 'Y' ? "Yes" : "No") . "</td></tr>";
                                }
                                echo "</table>";

                                echo "</td><td></td><td valign=top>";
                                if (!empty($st_ja_pe[1])) {

                                    if ($contact['SEC_STUDENT_RELATION'] != '')
                                        echo "<table width=100% border=0><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _secondaryEmergencyContact . "</td></tr>";
                                    if ($contact['SEC_STUDENT_RELATION'] != '')
                                        echo "<tr><td width=45% style='font-weight:bold'>" . _relation . " :</td><td width=55%>" . $contact['SEC_STUDENT_RELATION'] . "</td></tr>";
                                    if ($contact['SEC_FIRST_NAME'] != '')
                                        echo "<tr><td style='font-weight:bold'>" . _firstName . " :</td><td>" . $contact['SEC_FIRST_NAME'] . "</td></tr>";
                                    if ($contact['SEC_LAST_NAME'] != '')
                                        echo "<tr><td style='font-weight:bold'>" . _lastName . " :</td><td>" . $contact['SEC_LAST_NAME'] . "</td></tr>";
                                    // if ($contact['SEC_EMAIL'] != '') {
                                    //     echo "<tr><td style='font-weight:bold'>" . _email . " :</td><td>" . $contact['SEC_EMAIL'] . "</td></tr>";
                                    // }

                                    if ($contact['SEC_ADDRESS'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _addressLine_1 . " :</td><td>" . $contact['SEC_ADDRESS'] . "</td></tr>";
                                    }

                                    if ($contact['SEC_STREET'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _addressLine_2 . " :</td><td>" . $contact['SEC_STREET'] . "</td></tr>";
                                    }

                                    if ($contact['SEC_CITY'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _city . " :</td><td>" . $contact['SEC_CITY'] . "</td></tr>";
                                    }

                                    if ($contact['SEC_STATE'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _state . " :</td><td>" . $contact['SEC_STATE'] . "</td></tr>";
                                    }

                                    if ($contact['SEC_ZIPCODE'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _zipcode . " :</td><td>" . $contact['SEC_ZIPCODE'] . "</td></tr>";
                                    }

                                    if ($contact['SEC_HOME_PHONE'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _homePhone . " :</td><td>" . $contact['SEC_HOME_PHONE'] . "</td></tr>";
                                    }
                                    if ($contact['SEC_WORK_PHONE'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _workPhone . " :</td><td>" . $contact['SEC_WORK_PHONE'] . "</td></tr>";
                                    }
                                    if ($contact['SEC_CELL_PHONE'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _cellPhone . " :</td><td>" . $contact['SEC_CELL_PHONE'] . "</td></tr>";
                                    }
                                    if ($contact['SEC_EMAIL'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _email . " :</td><td>" . $contact['SEC_EMAIL'] . "</td></tr>";
                                    }
                                    if ($contact['SEC_CUSTODY'] != '') {
                                        echo "<tr><td style='font-weight:bold'>" . _custody . " :</td><td>" . ($contact['SEC_CUSTODY'] == 'Y' ? "Yes" : "No") . "</td></tr>";
                                    }

                                    echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
                                    echo "</table>";
                                }
                                echo "</td></tr>";

                                echo "<tr><td valign=top>";
                                $info_RET = DBGet(DBQuery("SELECT p.FIRST_NAME,p.MIDDLE_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,p.HOME_PHONE AS ADDN_HOME_PHONE,p.WORK_PHONE AS ADDN_WORK_PHONE,p.EMAIL AS ADDN_EMAIL,sa.STREET_ADDRESS_1 AS ADDN_ADDRESS,sa.STREET_ADDRESS_2 AS ADDN_STREET,sa.CITY AS ADDN_CITY,sa.STATE AS ADDN_STATE,sa.ZIPCODE AS ADDN_ZIPCODE FROM people p,students_join_people sjp,student_address sa WHERE p.STAFF_ID=sjp.PERSON_ID AND sa.STUDENT_ID=sjp.STUDENT_ID AND sjp.PERSON_ID=sa.PEOPLE_ID AND sjp.STUDENT_ID='" . UserStudentID() . "' AND sjp.emergency_type='Other'"));

                                if ($info_RET[1]['STUDENT_RELATION'] != '') {
                                    echo '<table width=100%>';
                                    echo "<tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px; font-weight:bold;\">" . _additionalContact . "</td></tr>";
                                    foreach ($info_RET as $info) {

                                        echo "<tr><td width=45% style='font-weight:bold'>" . _relation . " :</td><td width=55%>" . $info['STUDENT_RELATION'] . "</td></tr>";
                                        echo "<tr><td style='font-weight:bold'>" . _firstName . " :</td><td>" . $info['FIRST_NAME'] . "</td></tr>";
                                        if ($info['MIDDLE_NAME'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _middleName . " :</td><td>" . $info['MIDDLE_NAME'] . "</td></tr>";
                                        }
                                        echo "<tr><td style='font-weight:bold'>" . _lastName . " :</td><td>" . $info['LAST_NAME'] . "</td></tr>";
                                        if ($info['ADDN_HOME_PHONE'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _homePhone . " :</td><td>" . $info['ADDN_HOME_PHONE'] . "</td></tr>";
                                        }
                                        if ($info['ADDN_WORK_PHONE'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _workPhone . " :</td><td>" . $info['ADDN_WORK_PHONE'] . "</td></tr>";
                                        }
                                        if ($info['ADDN_MOBILE_PHONE'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _mobilePhone . " :</td><td>" . $info['ADDN_MOBILE_PHONE'] . "</td></tr>";
                                        }
                                        if ($info['ADDN_EMAIL'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _email . " :</td><td>" . $info['ADDN_EMAIL'] . "</td></tr>";
                                        }

                                        if ($info['ADDN_ADDRESS'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _addressLine_1 . " :</td><td>" . $info['ADDN_ADDRESS'] . "</td></tr>";
                                        }

                                        if ($info['ADDN_STREET'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _addressLine_2 . " :</td><td>" . $info['ADDN_STREET'] . "</td></tr>";
                                        }

                                        if ($info['ADDN_CITY'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _city . " :</td><td>" . $info['ADDN_CITY'] . "</td></tr>";
                                        }

                                        if ($info['ADDN_STATE'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _state . " :</td><td>" . $info['ADDN_STATE'] . "</td></tr>";
                                        }

                                        if ($info['ADDN_ZIPCODE'] != '') {
                                            echo "<tr><td style='font-weight:bold'>" . _zipcode . " :</td><td>" . $info['ADDN_ZIPCODE'] . "</td></tr>";
                                        }


                                        echo "<tr><td colspan=2 style=\"border-bottom:1px dashed #999999;\">&nbsp;</td></tr>";
                                        echo "<tr><td colspan=2 style=\"height:5px;\">&nbsp;</td></tr>";
                                    }
                                    echo "</table>";
                                }
                                echo "</td><td></td><td valign=top>";
                                echo "</td></tr>";
                                echo "<tr><td valign=top colspan=3>";
                                //                                foreach ($people_categories_RET as $categories)
                                //                                    if (!$categories[1]['CUSTODY'] && !$categories[1]['EMERGENCY'] || $categories[1]['CUSTODY'] == 'Y' && $contact['CUSTODY'] == 'Y' || $categories[1]['EMERGENCY'] == 'Y' && $contact['EMERGENCY'] == 'Y')
                                //                                        printCustom($categories, $contact);
                            }
                        }
                        $address_previous = $address_current;
                    }


                    //                    $contacts_RET2 = DBGet(DBQuery('SELECT p.STAFF_ID as PERSON_ID,p.FIRST_NAME,p.MIDDLE_NAME,p.LAST_NAME,p.CUSTODY,sjp.EMERGENCY_TYPE AS EMERGENCY,sjp.RELATIONSHIP AS STUDENT_RELATION FROM people p,students_join_people sjp WHERE p.STAFF_ID=sjp.PERSON_ID AND sjp.STUDENT_ID=' . UserStudentID()));
                    //                    foreach ($contacts_RET2 as $contact) {
                    // echo '<B>' . $contact['FIRST_NAME'] . ' ' . ($contact['MIDDLE_NAME'] ? $contact['MIDDLE_NAME'] . ' ' : '') . $contact['LAST_NAME'] . ($contact['STUDENT_RELATION'] ? ': ' . $contact['STUDENT_RELATION'] : '') . ' &nbsp;</B>';


                    //                        foreach ($people_categories_RET as $categories)
                    //                            if (!$categories[1]['CUSTODY'] && !$categories[1]['EMERGENCY'] || $categories[1]['CUSTODY'] == 'Y' && $contact['CUSTODY'] == 'Y' || $categories[1]['EMERGENCY'] == 'Y' && $contact['EMERGENCY'] == 'Y')
                    //                                printCustom($categories, $contact);
                    //                    }
                }

                if ($_REQUEST['category']['2'] && ($sql['PHYSICIAN_NAME'] != '' || $sql['PHYSICIAN_PHONO'] != '' || $sql['HOSPITAL'] != '')) {

                    //------------------------------------------------------------------------------

                    echo "<table width='100%'><tr><td style=\"border-bottom:1px solid #333;  font-size:14px; font-weight:bold;\">" . _medicalInformation . "</td></tr></table>";
                    echo "</td><td></td><td valign=top>";
                    echo "</td></tr>";
                    echo "<tr><td valign=top colspan=3>";
                    echo "<table width='100%'>";
                    //                    echo "<tr><td colspan=\"2\" style=\"border-bottom:1px solid #9a9a9a; font-weight:bold; color:4a4a4a; font-size:12px;\">" . _generalInformation . "</td></tr>";
                    echo "<tr><td colspan=2 style=\"height:2px;\"></td></tr>";
                    if ($sql['PHYSICIAN_NAME'] != '') {
                        echo "<tr><td width=21% style='font-weight:bold'>" . _physicianName . ":</td>";
                        echo "<td width=79%>" . $sql['PHYSICIAN_NAME'] . "</td></tr>";
                    }
                    if ($sql['PHYSICIAN_PHONO'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _physicianSPhone . ":</td>";
                        echo "<td>" . $sql['PHYSICIAN_PHONO'] . "</td></tr>";
                    }
                    if ($sql['HOSPITAL'] != '') {
                        echo "<tr><td style='font-weight:bold'>" . _preferredMedicalFacility . ":</td>";
                        echo "<td>" . $sql['HOSPITAL'] . "</td></tr>";
                    }

                    ######################## PRINT MEDICAL CUSTOM FIELDS ################################################
                    $fields_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED FROM custom_fields WHERE CATEGORY_ID='2' ORDER BY SORT_ORDER,TITLE"));
                    $custom_RET = DBGet(DBQuery("SELECT * FROM students WHERE STUDENT_ID='" . UserStudentID() . "'"));
                    $value = $custom_RET[1];
                    if (count($fields_RET)) {
                        echo '<table width=34% cellpadding=5>';
                        $i = 1;
                        foreach ($fields_RET as $field) {
                            if (($value['CUSTOM_' . $field['ID']]) != '') {
                                echo "<tr><td valign='top' style='font-weight:bold'>" . $field['TITLE'] . ":</td>";
                                echo "<td align='justify'>" . $value['CUSTOM_' . $field['ID']] . "</td>";
                                echo '</tr>';
                            }
                        }
                    }
                    echo '</table>';
                    ####################################################################################################

                    ########################## DOCTORS NOTE TABLE ###########################################
                    $medical_note = DBGet(DBQuery('SELECT doctors_note_date AS MCOMNT,doctors_note_comments AS DNOTE FROM student_medical_notes WHERE  STUDENT_ID=\'' . $_SESSION['student_id'] . '\''), array('MCOMNT' => 'ProperDate'));
                    unset($_openSIS['DrawHeader']);
                    foreach ($medical_note as $id => $medical) {
                        $doc_note_col = array(
                            'DNOTE' => _doctorSNote,
                            'MCOMNT' => _date,
                        );
                    }
                    ListOutputPrint_Report($medical_note, $doc_note_col, '', '', false, $group = false, $options, 'ForWindow');
                    echo '</td></tr>';
                    echo '</table>';


                    ########################################################################
                    // $fields_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE,SELECT_OPTIONS,DEFAULT_SELECTION,REQUIRED FROM custom_fields WHERE CATEGORY_ID='2' ORDER BY SORT_ORDER,TITLE"));
                    // $custom_RET = DBGet(DBQuery("SELECT * FROM students WHERE STUDENT_ID='" . UserStudentID() . "'"));
                    // $value = $custom_RET[1];
                    // if (count($fields_RET)) {
                    //     echo '<TABLE cellpadding=5>';
                    //     $i = 1;
                    //     foreach ($fields_RET as $field) {
                    //         if (($value['CUSTOM_' . $field['ID']]) != '') {
                    //             echo '<TR>';
                    //             echo '<td>' . $field['TITLE'] . '</td><td>:</td><td>';
                    //             echo _makeTextInput('CUSTOM_' . $field['ID'], '', 'class=cell_medium');
                    //             echo '</TD>';
                    //             echo '</TR>';
                    //         }
                    //     }
                    #############################################################################
                    echo '<!-- NEW PAGE -->';
                }

                echo "</td></tr><br>";
                echo "<tr><td valign=top colspan=3>";

                # ---------------------------------- Immunization/Physical Record ---------------- #

                $res_immunization = DBGet(DBQuery("SELECT TYPE,MEDICAL_DATE,COMMENTS FROM student_immunization WHERE student_id='" . $_SESSION['student_id'] . "'"), array('MEDICAL_DATE' => 'ProperDate'));
                if ($_REQUEST['category']['2'] && count($res_immunization) >= 1) {
                    //------------------------------------------------------------------------------
                    echo "<table width=24%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _immunizationPhysicalRecord . "</td></tr></table>";
                    foreach ($res_immunization as $row_immunization) {
                        $immunization_note_col = array(
                            'TYPE' => _type,
                            'MEDICAL_DATE' => _date,
                            'COMMENTS' => _comments,
                        );
                    }
                    ListOutputPrint_Report($res_immunization, $immunization_note_col, '', '', false, $group = false, $options, 'ForWindow');
                    echo '</td></tr>';
                    echo '</table>';
                }

                echo '<!-- NEW PAGE -->';
                echo "</td></tr><br>";
                echo "<tr><td valign=top colspan=3>";



                # ---------------------------------- Medical Alert ---------------- #

                $res_alert = DBGet(DBQuery("SELECT TITLE,ALERT_DATE FROM student_medical_alerts WHERE student_id='" . $_SESSION['student_id'] . "'"), array('ALERT_DATE' => 'ProperDate'));
                if ($_REQUEST['category']['2'] && count($res_alert) >= 1) {
                    //------------------------------------------------------------------------------

                    echo "<table width=24%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _medicalAlert . "</td></tr></table>";

                    foreach ($res_alert as $row_alert) {
                        $res_alert_col = array(
                            'TITLE' => _medicalAlert,
                            'ALERT_DATE' => _date,
                        );
                    }
                    ListOutputPrint_Report($res_alert, $res_alert_col, '', '', false, $group = false, $options, 'ForWindow');
                    echo '</td></tr>';
                    echo '</table>';


                    echo '<!-- NEW PAGE -->';
                }
                echo "</td></tr>";
                echo "<tr><td valign=top colspan=3>";


                # ---------------------------------- Nurse Visit Record ---------------- #

                $res_visit = DBGet(DBQuery("SELECT SCHOOL_DATE,TIME_IN,TIME_OUT,REASON,RESULT,COMMENTS FROM student_medical_visits WHERE student_id='" . $_SESSION['student_id'] . "'"), array('SCHOOL_DATE' => 'ProperDate'));

                if ($_REQUEST['category']['2'] && count($res_visit) >= 1) {
                    //------------------------------------------------------------------------------

                    echo "<table width=100%><tr><td colspan=2 style=\"border-bottom:1px solid #9a9a9a; font-weight:bold; color:4a4a4a; font-size:12px;\">" . _nurseVisitRecord . "</td></tr>
				<tr><td colspan=2 style=\"height:5px;\"></td></tr>";

                    foreach ($res_visit as $row_visit) {
                        if ($row_visit['SCHOOL_DATE'] != '') {
                            echo "<tr><td width=21% style='font-weight:bold'>" . _date . ":</td>";
                            echo "<td width=79%>" . $row_visit['SCHOOL_DATE'] . "</td></tr>";
                        }
                        if ($row_visit['TIME_IN'] != '') {
                            echo "<tr><td style='font-weight:bold'>" . _timeIn . ":</td>";
                            echo "<td>" . $row_visit['TIME_IN'] . "</td></tr>";
                        }
                        if ($row_visit['TIME_OUT'] != '') {
                            echo "<tr><td style='font-weight:bold'>" . _timeOut . ":</td>";
                            echo "<td>" . $row_visit['TIME_OUT'] . "</td></tr>";
                        }
                        if ($row_visit['REASON'] != '') {
                            echo "<tr><td style='font-weight:bold'>" . _reason . ":</td>";
                            echo "<td>" . $row_visit['REASON'] . "</td></tr>";
                        }
                        if ($row_visit['RESULT'] != '') {
                            echo "<tr><td style='font-weight:bold'>" . _result . ":</td>";
                            echo "<td>" . $row_visit['RESULT'] . "</td></tr>";
                        }
                        if ($row_visit['COMMENTS'] != '') {
                            echo "<tr><td valign='top' style='font-weight:bold'>" . _comments . ":</td>";
                            echo "<td align='justify'>" . $row_visit['COMMENTS'] . "</td></tr>";
                        }
                        echo "<tr><td colspan=2 style=\"border-bottom:1px dashed #999999;\">&nbsp;</td></tr>";
                        echo "<tr><td colspan=2 style=\"height:5px;\">&nbsp;</td></tr>";
                    }
                    echo '</table>';


                    echo '<!-- NEW PAGE -->';
                }
                echo "</td></tr><br>";

                echo "<tr><td valign=top colspan=3>";

                if ($_REQUEST['category']['4'] != '') {
                    $res_comment = DBGet(DBQuery("SELECT ID,COMMENT_DATE,COMMENT,CONCAT(s.FIRST_NAME,' ',s.LAST_NAME)AS USER_NAME FROM student_mp_comments,staff s WHERE STUDENT_ID='" . $_SESSION['student_id'] . "'  AND s.STAFF_ID=student_mp_comments.STAFF_ID"), array('COMMENT_DATE' => 'ProperDate'));
                    echo "<table width=24%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _comment . "</td></tr></table>";
                    foreach ($res_comment as $row_comment) {
                        $res_comment_col = array(
                            'USER_NAME' => _enteredBy,
                            'COMMENT_DATE' => _date,
                            'COMMENT' => _comments,
                        );
                    }
                    ListOutputPrint_Report($res_comment, $res_comment_col, '', '', false, $group = false, $options, 'ForWindow');
                    echo '</td></tr>';


                    echo '</table>';

                    echo '<!-- NEW PAGE -->';
                }

                echo "</td></tr>";
                if ($_REQUEST['category']['5']) {
                    $res_goal = DBGet(DBQuery("SELECT goal_id AS GOAL,GOAL_TITLE,START_DATE,END_DATE,GOAL_DESCRIPTION FROM student_goal WHERE student_id='" . $_SESSION['student_id'] . "'"), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate'));
                    if ($res_goal) {
                        echo "<tr><td><br></td></tr>";
                        echo "<tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . _goals . "</td></tr>";
                        echo '<tr><td><br></td></tr>';
                        echo '<table cellpadding="6" width="100%" cellspacing="1" border="1" style="border-collapse:collapse" align="center"><tr><td bgcolor="#d3d3d3">' . _title . '</td><td bgcolor="#d3d3d3">' . _startDate . '</td><td bgcolor="#d3d3d3">' . _endDate . '</td><td bgcolor="#d3d3d3">' . _description . '</td></tr>';
                        foreach ($res_goal as $row_goal) {
                            echo '<tr><td>' . $row_goal['GOAL_TITLE'] . '</td><td>' . $row_goal['START_DATE'] . '</td><td>' . $row_goal['END_DATE'] . '</td><td>' . $row_goal['GOAL_DESCRIPTION'] . '</td></tr>';
                        }
                        echo '</table>';
                    }
                    echo "</td></tr>";
                }


                if ($_REQUEST['category']['6']) {
                    $stu_enr = DBGet(DBQuery('SELECT se.*,s.TITLE AS SCHOOL,sg.TITLE AS GRADE FROM student_enrollment se,schools s,school_gradelevels sg WHERE se.STUDENT_ID=' . $_SESSION['student_id'] . ' AND se.SCHOOL_ID=s.ID AND se.GRADE_ID=sg.ID'), array('START_DATE' => 'ProperDate', 'END_DATE' => 'ProperDate'));
                    $stu_enr_col = array(
                        'SCHOOL' => _school,
                        'GRADE' => _gradeLevel,
                        'START_DATE' => _startDate,
                        'ENROLLMENT_CODE' => _enrollmentCode,
                        'END_DATE' => _endDate,
                        'DROP_CODE' => _dropCode,
                    );

                    foreach ($stu_enr as $si => $sd) {
                        $stu_enr[$si]['END_DATE'] = ($stu_enr[$si]['END_DATE'] != '' ? $stu_enr[$si]['END_DATE'] : 'N/A');
                        if ($sd['ENROLLMENT_CODE'] != '')
                            $get_ecode = DBGet(DBQuery('SELECT TITLE as ENROLLMENT_CODE FROM student_enrollment_codes WHERE ID=' . $sd['ENROLLMENT_CODE']));
                        if ($sd['DROP_CODE'] != '')
                            $get_dcode = DBGet(DBQuery('SELECT TITLE as DROP_CODE FROM student_enrollment_codes WHERE ID=' . $sd['DROP_CODE']));

                        $stu_enr[$si]['ENROLLMENT_CODE'] = ($sd['ENROLLMENT_CODE'] != '' ? $get_ecode[1]['ENROLLMENT_CODE'] : 'N/A');
                        $stu_enr[$si]['DROP_CODE'] = ($sd['DROP_CODE'] != '' ? $get_dcode[1]['DROP_CODE'] : 'N/A');

                        unset($get_dcode);
                        unset($get_ecode);
                    }
                    echo "<tr><td><br></td></tr>";
                    if (count($stu_enr) > 0)
                    $enroll =ucfirst(strtolower(_enrollmentInfo));
                $enrollment = str_replace(['i'], ['I'],$enroll);
                        echo "<table width=24%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">". $enrollment. "</td></tr></table>";
                    echo '<tr><td><br>';
                    ListOutputPrint_Report($stu_enr, $stu_enr_col, '', '', false, $group = false, $options, 'ForWindow');
                    echo '</td></tr>';
                }
                echo "<tr><td colspan=3 valign=top>";

                //===NEWLY ADDED====================================================================================
                $cus_RET = DBGet(DBQuery("SELECT sfc.ID,cf.ID as ID1,cf.TITLE, sfc.TITLE AS TITLE1, cf.TYPE, cf.SELECT_OPTIONS, cf.DEFAULT_SELECTION, cf.REQUIRED
                FROM custom_fields AS cf, student_field_categories AS sfc
                WHERE sfc.ID = cf.CATEGORY_ID
                AND sfc.ID != '1'
                AND sfc.ID != '2'
                AND sfc.ID != '3'
                AND sfc.ID != '4'
                AND sfc.ID != '5'
                GROUP BY cf.category_id
                ORDER BY cf.ID"));


                foreach ($cus_RET as $cus) {
                    $fields_RET = DBGet(DBQuery("SELECT ID,TITLE,TYPE,SELECT_OPTIONS FROM custom_fields where CATEGORY_ID='" . $cus['ID'] . "'"));
                    $b = $cus['ID'];
                    if ($_REQUEST['category'][$b]) {

                        $custom_RET = DBGet(DBQuery("SELECT * FROM students WHERE STUDENT_ID='" . UserStudentID() . "'"));

                        $value = $custom_RET[1];
                        echo "<table width=100% >";



                        if (($value['CUSTOM_' . $cus['ID1']]) != '') {

                            echo "<tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . $cus['TITLE1'] . "</td></tr>";
                        }




                        if (count($fields_RET)) {


                            $i = 1;
                            foreach ($fields_RET as $field) {
                                if (($value['CUSTOM_' . $field['ID']]) != '') {
                                    $date = DBGet(DBQuery("SELECT type,id FROM custom_fields WHERE ID='" . $field['ID'] . "'"));

                                    foreach ($date as $da) {
                                        if ($da['TYPE'] == 'date') {
                                            $sql = DBGet(DBQuery("SELECT CUSTOM_" . $da['ID'] . " as DATE FROM students WHERE STUDENT_ID='" . UserStudentID() . "'"), array('DATE' => 'ProperDate'));
                                            foreach ($sql as $sq) {
                                                echo '<TR>';
                                                echo '<td width=125px style="font-weight:bold">' . $field['TITLE'] . ':</td>';
                                                echo '<td class=cell_medium>' . $sq['DATE'] . '';

                                                echo '</TD>';
                                                echo '</TR>';
                                            }
                                        } else {
                                            echo '<TR>';
                                            echo '<td width=125px style="font-weight:bold">' . $field['TITLE'] . ':</td><td>';
                                            echo _makeTextInput('CUSTOM_' . $field['ID'], '', 'class=cell_medium');
                                            echo '</TD>';
                                            echo '</TR>';
                                        }
                                    }
                                }
                            }
                        }

                        echo "</TABLE>";
                    }
                }
                //===NEWLY ADDED====================================================================================

                echo "</td><tr>";
                echo "</table>";

                echo '<div style="page-break-before: always;">&nbsp;</div>';
                foreach ($categories_RET as $id => $category) {
                    if ($id != '1' && $id != '3' && $id != '2' && $id != '4' && $_REQUEST['category'][$id]) {
                        $_REQUEST['category_id'] = $id;

                        $separator = '';
                        if (!$category[1]['INCLUDE'])
                            include('modules/students/includes/OtherInfoInc.inc.php');
                        elseif (!strpos($category[1]['INCLUDE'], '/'))
                            include('modules/students/includes/' . $category[1]['INCLUDE'] . '.inc.php');
                        else {
                            include('modules/' . $category[1]['INCLUDE'] . '.inc.php');
                            $separator = '<HR>';
                        }
                    }
                }
            }






            PDFStop($handle);
        } else
            BackPrompt(_noStudentsWereFound . '.');
    } else
        BackPrompt(_youMustChooseAtLeastOneStudent . '.');
    // unset($_SESSION['student_id']);

    $_REQUEST['modfunc'] = true;
}

if (!$_REQUEST['modfunc']) {
    DrawBC("" . _students . " > " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_search_all_schools=$_REQUEST[_search_all_schools]&_openSIS_PDF=true method=POST target=_blank>";


        $extra['extra_header_left'] .= $extra['search'];
        $extra['search'] = '';
        $extra['extra_header_left'] .= '';

        if (User('PROFILE_ID') != '')
            $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . User('PROFILE_ID') . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
        else {
            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
            if ($profile_id_mod != '')
                $can_use_RET = DBGet(DBQuery("SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID='" . $profile_id_mod . "' AND CAN_USE='Y'"), array(), array('MODNAME'));
        }
        $categories_RET = DBGet(DBQuery("SELECT ID,TITLE,INCLUDE FROM student_field_categories WHERE TITLE NOT LIKE '%Files%' ORDER BY SORT_ORDER,TITLE"));
        $extra['extra_header_left'] .= '';
        foreach ($categories_RET as $category) {

            switch ($category['TITLE']) {
                case 'General Info':
                    $category['TITLE'] = _generalInfo;
                    break;
                case 'Addresses &amp; Contacts':
                    $category['TITLE'] = _addressesContacts;
                    break;
                case 'Medical':
                    $category['TITLE'] = _medical;
                    break;
                case 'Comments':
                    $category['TITLE'] = _comments;
                    break;
                case 'Goals':
                    $category['TITLE'] = _goals;
                    break;
                case 'Enrollment Info':
                    $category['TITLE'] = _enrollmentInfo;
                    break;
                case 'Files':
                    $category['TITLE'] = _files;
                    break;
                default:
                    $category['TITLE'] = $category['TITLE'];
                    break;
                    // case 'Demographic Info':
                    //     $category['TITLE'] = _demographicInfo;
                    //     break;
                    // case 'Addresses &amp; Contacts':
                    //     $category['TITLE'] = _addressesContacts;
                    //     break;
                    // case 'School Information':
                    //     $category['TITLE'] = _schoolInformation;
                    //     break;
                    // case 'Certification Information':
                    //     $category['TITLE'] = _certificationInformation;
                    //     break;
                    // case 'Schedule':
                    //     $category['TITLE'] = _schedule;
                    //     break;
            }
            if ($can_use_RET['students/Student.php&category_id=' . $category['ID']]) {
                $extra['extra_header_left'] .= '<label class="checkbox-inline checkbox-switch switch-success switch-sm"><INPUT type=checkbox name=category[' . $category['ID'] . '] value=Y checked><span></span> ' . ucwords(strtolower($category['TITLE'])) . '</label>';
                //$extra['extra_header_left'] .= '<td></TD></TR>';
            }
        }
    }

    $extra['link'] = array('FULL_NAME' => false);
    $extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
    if (isset($_SESSION['student_id']) && $_SESSION['student_id'] != '') {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    $extra['options']['search'] = false;
    $extra['new'] = true;


    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('course');
    $extra['search'] .= '</div><div class="col-md-6">';
    Widgets('mailing_labels');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('request');
    $extra['search'] .= '</div><div class="col-md-6">';
    Widgets('activity');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('gpa');
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('letter_grade');
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '</div><div class="col-md-6">';
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('class_rank');
    $extra['search'] .= '</div>'; //.well
    $extra['search'] .= '<div class="well mb-20 pt-5 pb-5">';
    Widgets('absences');
    $extra['search'] .= '</div>'; //.well
    Widgets('eligibility');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';

    Search('student_id', $extra);
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\'' . _printInfoForSelectedStudents . '\'></div>';
        echo "</FORM>";
    }
}

// GetStuList by default translates the grade_id to the grade title which we don't want here.
// One way to avoid this is to provide a translation function for the grade_id so here we
// provide a passthru function just to avoid the translation.
function _grade_id($value)
{
    return $value;
}

function _makeChooseCheckbox($value, $title)
{
    //    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    global $THIS_RET;
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

function explodeCustom(&$categories_RET, &$custom, $prefix)
{
    foreach ($categories_RET as $id => $category)
        foreach ($category as $i => $field) {
            $custom .= ',' . $prefix . '.CUSTOM_' . $field['ID'];
            if ($field['TYPE'] == 'select' || $field['TYPE'] == 'codeds') {
                $select_options = str_replace("\n", "\r", str_replace("\r\n", "\r", $field['SELECT_OPTIONS']));
                $select_options = explode("\r", $select_options);
                $options = array();
                foreach ($select_options as $option) {
                    if ($field['TYPE'] == 'codeds') {
                        $option = explode('|', $option);
                        if ($option[0] != '' && $option[1] != '')
                            $options[$option[0]] = $option[1];
                    } else
                        $options[$option] = $option;
                }
                $categories_RET[$id][$i]['SELECT_OPTIONS'] = $options;
            }
        }
}

function printCustom(&$categories, &$values)
{
    echo "<table width=100%><tr><td colspan=2 style=\"border-bottom:1px solid #333;  font-size:14px;  font-weight:bold;\">" . $categories[1]['CATEGORY_TITLE'] . "</td></tr>";
    foreach ($categories as $field) {
        echo '<TR>';
        echo '<TD>' . ($field['REQUIRED'] && $values['CUSTOM_' . $field['ID']] == '' ? '<FONT color=red>' : '') . $field['TITLE'] . ($field['REQUIRED'] && $values['CUSTOM_' . $field['ID']] == '' ? '</FONT>' : '') . '</TD>';
        if ($field['TYPE'] == 'select')
            echo '<TD>' . ($field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] != '' ? '' : '<FONT color=red>') . $values['CUSTOM_' . $field['ID']] . ($field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] != '' ? '' : '</FONT>') . '</TD>';
        elseif ($field['TYPE'] == 'codeds')
            echo '<TD>' . ($field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] != '' ? $field['SELECT_OPTIONS'][$values['CUSTOM_' . $field['ID']]] : '<FONT color=red>' . $values['CUSTOM_' . $field['ID']] . '</FONT>') . '</TD>';
        else
            echo '<TD>' . $values['CUSTOM_' . $field['ID']] . '</TD>';
        echo '</TR>';
    }
    echo '</table>';
}
