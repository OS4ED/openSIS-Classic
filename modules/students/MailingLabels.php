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
$max_cols = 3;
$max_rows = 10;
$to_family = ''._toTheParentsOf.':';

if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
{
    $_REQUEST['search_modfunc'] = 'list';
}

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'save') {
    if (count($_REQUEST['st_arr'])) {
        $st_list = '\'' . implode('\',\'', $_REQUEST['st_arr']) . '\'';
        $extra['WHERE'] = ' AND s.STUDENT_ID IN (' . $st_list . ')';
        $_REQUEST['mailing_labels'] = 'Y';
        Widgets('mailing_labels');
        $extra['SELECT'] .= ',coalesce(s.COMMON_NAME,s.FIRST_NAME) AS NICK_NAME';
        $extra['group'] = array('ADDRESS_ID');
        $RET = GetStuList($extra);

        if (count($RET)) {
            echo '<div style="height:26px;">&nbsp;</div><table width="100%" border="0" cellspacing="0" cellpadding="0" style=font-family:Arial; font-size:12px;>';
            $cols = 0;
            $rows = 0;

            if ($_REQUEST['start_row'] > 1 || $_REQUEST['start_col'] > 1) {

                if ($_REQUEST['start_row'] > 1 && $_REQUEST['start_col'] > 1) {

                    $skip_num = (($_REQUEST['start_row'] - 1) * 3) + ($_REQUEST['start_col'] - 1);
                } else if ($_REQUEST['start_row'] == 1 && $_REQUEST['start_col'] > 1) {
                    $skip_num = ($_REQUEST['start_row'] * $_REQUEST['start_col']) - 1;
                } else if ($_REQUEST['start_row'] > 1 && $_REQUEST['start_col'] == 1) {
                    $skip_num = ($_REQUEST['start_row'] - 1) * 3;
                }
                for ($box = 1; $box <= $skip_num; $box++) {
                    if ($cols < 1) {
                        echo '<tr>';
                    }
                    echo '<td width="33.3%" height="97" style="padding-top:15px;" align="center" valign="middle">&nbsp;</td>';
                    $cols++;
                    if ($cols == $max_cols) {
                        echo '</tr>';
                        $rows++;
                        $cols = 0;
                    }

                    if ($rows == $max_rows) {
                        echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';
                        $rows = 0;
                    }
                }
            }



            foreach ($RET as $i => $addresses) {

                if ($i < 1) {



                    if ($_REQUEST['to_address'] == 'student') {

                        foreach ($addresses as $key => $address) {
                            $Stu_address = DBGet(DBQuery('SELECT STREET_ADDRESS_1 as ADDRESS,STREET_ADDRESS_2 as STREET,CITY,STATE,ZIPCODE FROM student_address WHERE STUDENT_ID=\'' . $address['STUDENT_ID'] . '\' AND TYPE=\'Home Address\' LIMIT 1'));
                            $Stu_address = $Stu_address[1];
                            if ($cols < 1)
                                echo '<tr>';
                            echo '<td width="33.3%" height="97" align="center" valign="middle">';
                            if ($_REQUEST['to_address'] == 'student') {
                                if ($address['MIDDLE_NAME'] != '') {
                                    echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['MIDDLE_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                } else {
                                    echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                }
                                echo $Stu_address['ADDRESS'] . "<br/>";
                                if ($Stu_address['STREET']) {
                                    echo $Stu_address['STREET'] . "<br/>";
                                }
                                echo $Stu_address['CITY'] . ', ' . $Stu_address['STATE'] . '-' . $Stu_address['ZIPCODE'] . "<br/>" . "<br/>";
                            }
                            echo '</span></td>';

                            $cols++;
                            if ($cols == $max_cols) {
                                echo '</tr>';
                                $rows++;
                                $cols = 0;
                            }

                            if ($rows == $max_rows) {
                                echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';

                                $rows = 0;
                            }
                        }
                    } elseif ($_REQUEST['to_address'] == 'pri_contact') {

                        foreach ($addresses as $key => $address) {
                            $pri_par_id = DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID=\'' . $address['STUDENT_ID'] . '\' AND EMERGENCY_TYPE=\'Primary\''));
                            $p_addr = DBGet(DBQuery('SELECT p.FIRST_NAME as PRI_FIRST_NAME,p.LAST_NAME as PRI_LAST_NAME,sa.STREET_ADDRESS_1 as PRIM_ADDRESS,sa.STREET_ADDRESS_2 as PRIM_STREET,sa.CITY as PRIM_CITY,sa.STATE as PRIM_STATE,sa.ZIPCODE as PRIM_ZIPCODE from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\'' . $pri_par_id[1]['PERSON_ID'] . '\'  AND sa.PEOPLE_ID IS NOT NULL '));


                            //echo 'SELECT p.FIRST_NAME as PRI_FIRST_NAME,p.LAST_NAME as PRI_LAST_NAME,sa.STREET_ADDRESS_1 as PRIM_ADDRESS,sa.STREET_ADDRESS_2 as PRIM_STREET,sa.CITY as PRIM_CITY,sa.STATE as PRIM_STATE,sa.ZIPCODE as PRIM_ZIPCODE FROM student_address sa,people p WHERE sa.STUDENT_ID=\''.$address['STUDENT_ID'].'\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Primary\' LIMIT 1';
                            //echo'<br><br>';
                            //$Stu_address=DBGet(DBQuery('SELECT p.FIRST_NAME as PRI_FIRST_NAME,p.LAST_NAME as PRI_LAST_NAME,sa.STREET_ADDRESS_1 as PRIM_ADDRESS,sa.STREET_ADDRESS_2 as PRIM_STREET,sa.CITY as PRIM_CITY,sa.STATE as PRIM_STATE,sa.ZIPCODE as PRIM_ZIPCODE FROM student_address sa,people p WHERE sa.STUDENT_ID=\''.$address['STUDENT_ID'].'\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Primary\' LIMIT 1'));
                            $Stu_address = $p_addr[1];
                            if ($cols < 1)
                                echo '<tr>';
                            echo '<td width="33.3%" height="97" align="center" valign="middle"><span style="font-size:12px;">';
                            if ($_REQUEST['to_address'] == 'pri_contact') {

                                if ($address['MIDDLE_NAME'] != '') {
                                    echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['MIDDLE_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                } else {
                                    echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                }
                                echo "C/O &nbsp;" . $Stu_address['PRI_FIRST_NAME'] . ' ' . $Stu_address['PRI_LAST_NAME'] . '<br/>';
                                echo $Stu_address['PRIM_ADDRESS'] . "<br/>";
                                if ($Stu_address['PRIM_STREET']) {
                                    echo $Stu_address['PRIM_STREET'] . ", ";
                                }
                                echo $Stu_address['PRIM_CITY'] . ", ";
                                echo $Stu_address['PRIM_STATE'] . '-' . $Stu_address['PRIM_ZIPCODE'] . "<br/>" . "<br/>";
                            }
                            echo '</span></td>';

                            $cols++;
                            if ($cols == $max_cols) {
                                echo '</tr>';
                                $rows++;
                                $cols = 0;
                            }

                            if ($rows == $max_rows) {
                                echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';

                                $rows = 0;
                            }
                        }
                    } elseif ($_REQUEST['to_address'] == 'sec_contact') {

                        foreach ($addresses as $key => $address) {

                            $sec_par_id = DBGet(DBQuery('SELECT * FROM students_join_people WHERE STUDENT_ID=\'' . $address['STUDENT_ID'] . '\' AND EMERGENCY_TYPE=\'Secondary\''));
                            $Stu_address = DBGet(DBQuery('SELECT p.FIRST_NAME as SEC_FIRST_NAME,p.LAST_NAME as SEC_LAST_NAME,sa.STREET_ADDRESS_1 as SEC_ADDRESS,sa.STREET_ADDRESS_2 as SEC_STREET,sa.CITY as SEC_CITY,sa.STATE as SEC_STATE,sa.ZIPCODE as SEC_ZIPCODE from people p,student_address sa WHERE p.STAFF_ID=sa.PEOPLE_ID  AND p.STAFF_ID=\'' . $sec_par_id[1]['PERSON_ID'] . '\'  AND sa.PEOPLE_ID IS NOT NULL '));

                            //$Stu_address=DBGet(DBQuery('SELECT p.FIRST_NAME as SEC_FIRST_NAME,p.LAST_NAME as SEC_LAST_NAME,sa.STREET_ADDRESS_1 as SEC_ADDRESS,sa.STREET_ADDRESS_2 as SEC_STREET,sa.CITY as SEC_CITY,sa.STATE as SEC_STATE,sa.ZIPCODE as SEC_ZIPCODE FROM student_address sa,people p WHERE sa.STUDENT_ID=\''.$address['STUDENT_ID'].'\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Secondary\' LIMIT 1'));
                            $Stu_address = $Stu_address[1];
                            if ($cols < 1)
                                echo '<tr>';
                            echo '<td width="33.3%" height="97" style="padding-top:15px;" align="center" valign="middle"><span style="font-size:12px;">';
                            if ($_REQUEST['to_address'] == 'sec_contact') {

                                if ($address['MIDDLE_NAME'] != '') {
                                    echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['MIDDLE_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                } else {
                                    echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                }
                                echo "C/O &nbsp;" . $Stu_address['SEC_FIRST_NAME'] . ' ' . $Stu_address['SEC_LAST_NAME'] . '<br/>';
                                echo $Stu_address['SEC_ADDRESS'] . "<br/>";
                                if ($Stu_address['SEC_STREET']) {
                                    echo $Stu_address['SEC_STREET'] . ", ";
                                }
                                echo $Stu_address['SEC_CITY'] . ", ";
                                echo $Stu_address['SEC_STATE'] . '-' . $Stu_address['SEC_ZIPCODE'] . "<br/>" . "<br/>";
                            }
                            echo '</span></td>';

                            $cols++;
                            if ($cols == $max_cols) {
                                echo '</tr>';
                                $rows++;
                                $cols = 0;
                            }

                            if ($rows == $max_rows) {
                                echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';

                                $rows = 0;
                            }
                        }
                    } elseif ($_REQUEST['to_address'] == 'contact') {

                        foreach ($addresses as $key => $address) {
                            $Stu_prim_address = DBGet(DBQuery('SELECT p.FIRST_NAME as PRI_FIRST_NAME,p.LAST_NAME as PRI_LAST_NAME,sa.STREET_ADDRESS_1 as PRIM_ADDRESS,sa.STREET_ADDRESS_2 as PRIM_STREET,sa.CITY as PRIM_CITY,sa.STATE as PRIM_STATE,sa.ZIPCODE as PRIM_ZIPCODE FROM student_address sa,people p WHERE sa.STUDENT_ID=\'' . $address['STUDENT_ID'] . '\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Primary\' LIMIT 1'));
                            $Stu_sec_address = DBGet(DBQuery('SELECT p.FIRST_NAME as SEC_FIRST_NAME,p.LAST_NAME as SEC_LAST_NAME,sa.STREET_ADDRESS_1 as SEC_ADDRESS,sa.STREET_ADDRESS_2 as SEC_STREET,sa.CITY as SEC_CITY,sa.STATE as SEC_STATE,sa.ZIPCODE as SEC_ZIPCODE FROM student_address sa,people p WHERE sa.STUDENT_ID=\'' . $address['STUDENT_ID'] . '\' AND sa.PEOPLE_ID=p.STAFF_ID AND sa.TYPE=\'Secondary\' LIMIT 1'));

                            $Stu_address = $Stu_prim_address[1];
                            foreach ($Stu_sec_address[1] as $ind => $col)
                                $Stu_address[$ind] = $col;
                            if ($cols < 1)
                                echo '<tr>';
                            echo '<td width="33.3%" height="97" align="center" valign="middle"><span style="font-size:12px;">';
                            if ($_REQUEST['to_address'] == 'contact') { {

                                    if ($address['MIDDLE_NAME'] != '') {
                                        echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['MIDDLE_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                    } else {
                                        echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                    }
                                    echo "C/O &nbsp;" . $Stu_address['PRI_FIRST_NAME'] . ' ' . $Stu_address['PRI_LAST_NAME'] . '<br/>';
                                    echo $Stu_address['PRIM_ADDRESS'] . "<br/>";
                                    if ($Stu_address['PRIM_STREET']) {
                                        echo $Stu_address['PRIM_STREET'] . ", ";
                                    }
                                    echo $Stu_address['PRIM_CITY'] . ", ";
                                    echo $Stu_address['PRIM_STATE'] . '-' . $Stu_address['PRIM_ZIPCODE'] . "<br/>" . "<br/>";
                                }
                                echo '</span></td>';
                                $cols++;
                                if ($cols == $max_cols) {
                                    echo '</tr>';
                                    $rows++;
                                    $cols = 0;
                                }
                                if ($rows == $max_rows) {
                                    echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';

                                    $rows = 0;
                                } {
                                    echo'<td width="33.3%" height="97" align="center" valign="middle"><span style="font-size:12px;">';


                                    if ($address['MIDDLE_NAME'] != '') {
                                        echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['MIDDLE_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                    } else {
                                        echo '<span style="font-size:12px;">' . $address['FIRST_NAME'] . ' ' . $address['LAST_NAME'] . '<br/>';
                                    }
                                    echo "C/O &nbsp;" . $Stu_address['SEC_FIRST_NAME'] . ' ' . $Stu_address['SEC_LAST_NAME'] . '<br/>';
                                    echo $Stu_address['SEC_ADDRESS'] . "<br/>";
                                    if ($Stu_address['SEC_STREET']) {
                                        echo $Stu_address['SEC_STREET'] . ", ";
                                    }
                                    echo $Stu_address['SEC_CITY'] . ", ";
                                    echo $Stu_address['SEC_STATE'] . '-' . $Stu_address['SEC_ZIPCODE'] . "<br/>" . "<br/>";
                                }
                                echo '</span></td>';

                                $cols++;


                                if ($cols == $max_cols) {
                                    echo '</tr>';
                                    $rows++;
                                    $cols = 0;
                                }
                            }


                            if ($rows == $max_rows) {
                                echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';

                                $rows = 0;
                            }
                        }
                    }
                }

                foreach ($addresses as $address) {
                    $address['MAILING_LABEL'];
                    if (!$address['MAILING_LABEL'])
                        continue;

                    if ($cols < 1)
                        echo '<tr>';
                    echo '<td width="33.3%" height="97" align="center" valign="middle"><span style="font-size:12px;">';
                    if ($_REQUEST['to_address'] == 'student') {
                        echo $address['NICK_NAME'] . ' &nbsp; ' . $address['LAST_NAME'] . '<BR>';
                        echo "C/O &nbsp;" . $address['MAILING_LABEL'] . '<BR>';
                    } else
                        echo $address['MAILING_LABEL'];
                    echo '</span></td>';

                    $cols++;

                    if ($cols == $max_cols) {
                        echo '</tr>';
                        $rows++;
                        $cols = 0;
                    }

                    if ($rows == $max_rows) {
                        echo '<tr><td colspan="3" style="height:24px;">&nbsp;</td></tr><tr><td colspan="3" style="height:14px;">&nbsp;</td></tr><!--NEW PAGE -->';

                        $rows = 0;
                    }
                }
            }

            if ($cols == 0 && $rows == 0) {
                
            } else {
                while ($cols != 0 && $cols < $max_cols) {
                    echo '<td width="33.3%" height="97" align="center" valign="middle">&nbsp;</td>';
                    $cols++;
                }
                if ($cols == $max_cols)
                    echo '</tr>';
                echo '</table>';
            }

            PDFstop($handle);
        } else
            BackPrompt(_noStudentsWereFound.'.');
    }
}

if (!$_REQUEST['modfunc']) {
    DrawBC(""._students." -> " . ProgramTitle());

    if ($_REQUEST['search_modfunc'] == 'list') {
        echo "<FORM action=ForExport.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_search_all_schools=$_REQUEST[_search_all_schools]&_openSIS_PDF=true method=POST target=_blank>";


//        $extra['extra_header_left'] = '<p>Avery Label 15660, 18660, 28660, 5630, 5660, 8660, 5620, 8620 compatible</p>';

        //$extra['extra_header_left'] .= '<div class="row">';
        //$extra['extra_header_left'] .= '<div class="col-md-6">';
        $extra['extra_header_left'] .= '<div class="radio"><label><INPUT class="styled" type=radio name=to_address value="student" checked> '._toStudent.'</label></div>';
        $extra['extra_header_left'] .= '<div class="radio"><label><INPUT class="styled" type=radio name=to_address value="pri_contact">'._toPrimaryEmergencyContact.'</label></div>';
        $extra['extra_header_left'] .= '<div class="radio"><label><INPUT class="styled" type=radio name=to_address value="sec_contact">'._toSecondaryEmergencyContact.'</label></div>';
        $extra['extra_header_left'] .= '<div class="radio"><label><INPUT class="styled" type=radio name=to_address value="contact">'._toBothEmergencyContacts.'</label></div>';
        //$extra['extra_header_left'] .= '</div>';
        //$extra['extra_header_left'] .= '<div class="col-md-6">';
        $extra['extra_header_right'] .= '<div style="min-width:300px;">';
        $extra['extra_header_right'] .= '<div class="row">';
        $extra['extra_header_right'] .= '<div class="col-md-6">';
        $extra['extra_header_right'] .= '<div class="form-group">';
        $extra['extra_header_right'] .= '<label>'._startingRow.'</label>';
        $extra['extra_header_right'] .= '<SELECT name="start_row" class="form-control">';
        for ($row = 1; $row <= $max_rows; $row++) {
            $extra['extra_header_right'] .= '<OPTION value="' . $row . '">' . $row . '</OPTION>';
        }
        $extra['extra_header_right'] .= '</SELECT>';
        $extra['extra_header_right'] .= '</div>'; //.form-group
        $extra['extra_header_right'] .= '</div>'; //.col-md-6
        $extra['extra_header_right'] .= '<div class="col-md-6">';
        $extra['extra_header_right'] .= '<div class="form-group">';
        $extra['extra_header_right'] .= '<label>'._startingColumn.'</label>';
        $extra['extra_header_right'] .= '<SELECT name="start_col" class="form-control">';
        for ($col = 1; $col <= $max_cols; $col++) {
            $extra['extra_header_right'] .= '<OPTION value="' . $col . '">' . $col . '</OPTION>';
        }
        $extra['extra_header_right'] .= '</SELECT>';
        $extra['extra_header_right'] .= '</div>'; //.form-group
        $extra['extra_header_right'] .= '</div>'; //.col-md-6
        $extra['extra_header_right'] .= '</div>'; //.row
        $extra['extra_header_right'] .= '</div>';
    }

    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('course');
    $extra['search'] .= '</div><div class="col-md-6">';
    Widgets('request');
    $extra['search'] .= '</div>';
    $extra['search'] .= '</div>';


    $extra['search'] .= '<div class="row">';
    $extra['search'] .= '<div class="col-md-6">';
    Widgets('activity');
    $extra['search'] .= '</div><div class="col-md-6">';

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



    $extra['SELECT'] .= ",s.STUDENT_ID AS CHECKBOX";
    if(isset($_SESSION['student_id']) && $_SESSION['student_id'] != '')
    {
        $extra['WHERE'] .= ' AND s.STUDENT_ID=' . $_SESSION['student_id'];
    }
    $extra['link'] = array('FULL_NAME' =>false);
    $extra['functions'] = array('CHECKBOX' => '_makeChooseCheckbox');
    $extra['columns_before'] = array('CHECKBOX' => '</A><INPUT type=checkbox value=Y name=controller onclick="checkAllDtMod(this,\'st_arr\');"><A>');
    $extra['options']['search'] = false;
    $extra['new'] = true;

    Search('student_id', $extra);
    if ($_REQUEST['search_modfunc'] == 'list') {
        echo '<div class="text-right p-b-20 p-r-20"><INPUT type=submit class="btn btn-primary" value=\''._printLabelsForSelectedStudents.'\'></div>';
        echo "</FORM>";
    }

    echo '<div id="modal_default" class="modal fade">';
    echo '<div class="modal-dialog modal-lg">';
    echo '<div class="modal-content">';
    echo '<div class="modal-header">';
    echo '<button type="button" class="close" data-dismiss="modal">×</button>';
    echo '<h5 class="modal-title">'._chooseCourse.'</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-4">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
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
    echo '<h5 class = "modal-title">'._chooseCourse.'</h5>';
    echo '</div>';

    echo '<div class="modal-body">';
    echo '<center><div id="conf_div"></div></center>';

    echo '<div class="row" id="resp_table">';
    echo '<div class="col-md-6">';
    $sql = "SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY TITLE";
    $QI = DBQuery($sql);
    $subjects_RET = DBGet($QI);

    echo '<h6>' . count($subjects_RET) . ((count($subjects_RET) == 1) ? ' '._subjectWas : ' '._subjectsWere) . ' '._found.'.</h6>';
    if (count($subjects_RET) > 0) {
        echo '<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._subject.'</th></tr></thead><tbody>';
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

function _makeChooseCheckbox($value, $title) {
    global $THIS_RET;
//    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
    
    return "<input name=unused[$THIS_RET[STUDENT_ID]] value=" . $THIS_RET['STUDENT_ID'] . "  type='checkbox' id=$THIS_RET[STUDENT_ID] onClick='setHiddenCheckboxStudents(\"st_arr[]\",this,$THIS_RET[STUDENT_ID]);' />";
}

?>
