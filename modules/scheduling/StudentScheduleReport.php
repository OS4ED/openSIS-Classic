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
include('lang/language.php');

if ($_REQUEST['modfunc'] == 'save') {
    if (UserStudentID()) {
        $extra['WHERE'] = ' AND s.STUDENT_ID =\'' . UserStudentID() . '\'';
        if ($_REQUEST['day_include_active_date'] && $_REQUEST['month_include_active_date'] && $_REQUEST['year_include_active_date']) {
            $date = $_REQUEST['day_include_active_date'] . '-' . $_REQUEST['month_include_active_date'] . '-' . $_REQUEST['year_include_active_date'];
            $date_extra = 'OR (\'' . $date . '\' >= sr.START_DATE AND sr.END_DATE IS NULL)';
        } else {
            $date = DBDate();
            $date_extra = 'OR sr.END_DATE IS NULL';
        }
        $columns = array('DAYS' => 'Days', 'DURATION' => 'Time', 'PERIOD_TITLE' => 'Period - Teacher', 'ROOM' => 'Room/Location', 'MARKING_PERIOD_ID' => 'Term', 'DAYS' => 'Days', 'COURSE_TITLE' => 'Course');
        $extra['SELECT'] .= ',c.TITLE AS COURSE_TITLE,p_cp.TITLE AS PERIOD_TITLE,sg.TITLE AS GRD_LVL,sr.MARKING_PERIOD_ID,cpv.DAYS, CONCAT(sp.START_TIME, " to ", sp.END_TIME) AS DURATION,r.TITLE AS ROOM';
        $extra['FROM'] .= ' LEFT OUTER JOIN schedule sr ON (sr.STUDENT_ID=ssm.STUDENT_ID),courses c, school_gradelevels sg, course_periods p_cp,course_period_var cpv,school_periods sp,rooms r ';
        $extra['WHERE'] .= ' AND cpv.PERIOD_ID=sp.PERIOD_ID AND p_cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID AND ssm.SYEAR=sr.SYEAR AND sr.COURSE_ID=c.COURSE_ID AND sr.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND ssm.GRADE_ID=sg.ID AND (\'' . $date . '\' BETWEEN sr.START_DATE AND sr.END_DATE ' . $date_extra . ')';
        if ($_REQUEST['mp_id'])
            $extra['WHERE'] .= ' AND sr.MARKING_PERIOD_ID IN (' . GetAllMP(GetMPTable(GetMP($_REQUEST['mp_id'], 'TABLE')), $_REQUEST['mp_id']) . ' OR sr.MARKING_PERIOD_ID is null )';
        $extra['functions'] = array('MARKING_PERIOD_ID' => 'GetMP', 'DAYS' => '_makeDays');
        $extra['group'] = array('STUDENT_ID');
        $extra['ORDER'] = ',sp.SORT_ORDER';
        if ($_REQUEST['mailing_labels'] == 'Y')
            $extra['group'][] = 'ADDRESS_ID';
        Widgets('mailing_labels');

        $RET_stu = GetStuList($extra);

        $sel_mp = $_REQUEST['sel_mp'];
        $sql_mp_detail = 'SELECT title, start_date, end_date, parent_id, grandparent_id from marking_periods WHERE marking_period_id = \'' . $sel_mp . '\'';

        $row_mp_detail = DBGet(DBQuery($sql_mp_detail));

        $mp_string = '(s.marking_period_id=' . $sel_mp . '';

        if ($row_mp_detail['parent_id'] != -1)
            $mp_string.=' or s.marking_period_id=' . $row_mp_detail[1]['PARENT_ID'] . '';
        if ($row_mp_detail['grandparent_id'] != -1)
            $mp_string.=' or s.marking_period_id=' . $row_mp_detail[1]['GRANDPARENT_ID'] . '';

        $mp_string.=' or s.marking_period_id is null';

        # -------------------------- Date Function Start ------------------------------- #

        function cov_date($dt) {
            $temp_date = explode("-", $dt);
            $final_date = $temp_date[1] . '-' . $temp_date[2] . '-' . $temp_date[0];
            return $final_date;
        }

        # -------------------------- Date Function End ------------------------------- #

        if (count($RET_stu)) {
            $handle = PDFStart();

            foreach ($RET_stu as $student_id => $courses) {
                echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
                echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">Student Daily Schedule</div></td><td style=\"font-size:15px; font-weight:bold; padding-top:20px;\"> Schedule for: " . $row_mp_detail[1]['TITLE'] . " : " . cov_date($row_mp_detail[1]['START_DATE']) . " - " . cov_date($row_mp_detail[1]['END_DATE']) . "</td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />Powered by openSIS</td></tr><tr><td colspan=4 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";


                # --------------------------------------- Start Change ------------------------------------------- #


                $st_data = DBGet(DBQuery("SELECT * FROM students WHERE student_id = " . $courses[1]['STUDENT_ID']));

                unset($_openSIS['DrawHeader']);
                echo '<br>';
                echo '<table  border=0>';

                echo '<tr><td>Student ID:</td>';
                echo '<td>' . $courses[1]['STUDENT_ID'] . '</td></tr>';
                echo '<tr><td>'._studentName.':</td>';
                echo '<td><b>' . $courses[1]['FULL_NAME'] . '</b></td></tr>';
                echo '<tr><td>' . $courses[1]['GRD_LVL'] . '</td>';
                echo '<td>' . $st_data[1]['CUSTOM_10'] . '</td></tr>';
                echo '</table>';

                $sch_exist = DBGet(DBQuery("SELECT COUNT(s.id) AS SCH_COUNT FROM schedule s WHERE s.syear=" . UserSyear() . "
					AND s.student_id='" . $courses[1]['STUDENT_ID'] . "'
					AND s.school_id=" . UserSchool() . "
					AND $mp_string )"));


                $sch_exist_yn = $sch_exist[1]['SCH_COUNT'];
                if ($sch_exist_yn != 0) {

                    echo '<table style="border-collapse: collapse;" width="100%" align="center" border="1px solid #a9d5e9 " cellpadding="6" cellspacing="1">';
                    echo '<tr><td width=15% bgcolor="#d3d3d3"><strong>Days</strong></td>';
                    echo '<td bgcolor="#d3d3d3"><strong>Start Time</strong></td>';
                    echo '<td bgcolor="#d3d3d3"><strong>End Time</strong></td>';
                    echo '<td bgcolor="#d3d3d3"><strong>Period - Teacher</strong></td>';
                    echo '<td bgcolor="#d3d3d3"><strong>Marking Period</strong></td>';
                    echo '<td bgcolor="#d3d3d3"><strong>Room/Location</strong></td>';
                    echo '</tr>';
                    $ar = array('Sunday' => 'U', 'Monday' => 'M', 'Tuesday' => 'T', 'Wednesday' => 'W', 'Thursday' => 'H', 'Friday' => 'F', 'Saturday' => 'S');
                    foreach ($ar as $day => $value) {
                        $counter = 0;

                        ////////////new////////////

                        $r_ch = DBGet(DBQuery('SELECT cp.title AS cp_title, cp.short_name, r.title as room, sp.start_time, sp.end_time,cp.marking_period_id as title ,sp.sort_order
			FROM school_periods sp, course_periods cp, schedule s, marking_periods mp,course_period_var cpv,rooms r
			WHERE cp.syear=\'' . UserSyear() . '\'
			AND s.syear=\'' . UserSyear() . '\'
			AND s.student_id=\'' . $courses[1]['STUDENT_ID'] . '\'
			AND s.course_period_id=cp.course_period_id
			AND sp.period_id=cpv.period_id
                        AND cp.course_period_id=cpv.course_period_id
                        AND r.room_id=cpv.room_id
                        AND s.start_date<=\'' . date('Y-m-d') . '\'
                        AND (s.end_date IS NULL OR s.end_date>=\'' . date('Y-m-d') . '\')
			AND cpv.days like \'' . '%' . $value . '%' . '\'
			AND s.school_id=\'' . UserSchool() . '\'
			
			AND ' . $mp_string . ' ) GROUP BY cpv.course_period_id  order by sp.sort_order'));


                        foreach ($r_ch as $mp_k => $mp_v) {

                            if ($mp_v['TITLE'] == '') {
                                $r_ch[$mp_k]['TITLE'] = 'Full Year';
                            } else {
                                $qr_mp = DBGet(DBQuery('SELECT TITLE FROM marking_periods WHERE marking_period_id=' . $mp_v['TITLE']));
                                $r_ch[$mp_k]['TITLE'] = $qr_mp[1]['TITLE'];
                            }
                        }



                        ////////////   end  new  //////////// 

                        $rs = DBQuery('SELECT cp.title AS cp_title, cp.short_name, r.title as room, sp.start_time, sp.end_time, mp.title,sp.sort_order
			FROM school_periods sp, course_periods cp, schedule s, marking_periods mp,course_period_var cpv,rooms r
			WHERE cp.syear=\'' . UserSyear() . '\'
			AND s.syear=\'' . UserSyear() . '\'
			AND s.student_id=\'' . $courses[1]['STUDENT_ID'] . '\'
			AND s.course_period_id=cp.course_period_id
			AND sp.period_id=cpv.period_id
                        AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                        AND r.ROOM_ID=cpv.ROOM_ID
                        AND s.start_date<=\'' . date('Y-m-d') . '\'
                        AND (s.end_date IS NULL OR s.end_date>=\'' . date('Y-m-d') . '\')
			AND cpv.days like \'' . '%' . $value . '%' . '\'
			AND s.school_id=\'' . UserSchool() . '\'
			AND s.marking_period_id=mp.marking_period_id
			AND ' . $mp_string . ') order by sp.sort_order');
                        $no_record = count($r_ch);

                        foreach ($r_ch as $sch) {
                            echo "<tr>";
                            if ($counter == 0) {

                                if ($value == 'U') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                } elseif ($value == 'M') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                } elseif ($value == 'T') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                } elseif ($value == 'W') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                } elseif ($value == 'H') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                } elseif ($value == 'F') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                } elseif ($value == 'S') {
                                    echo "<td rowspan='" . $no_record . "'>" . $day . "</td>";
                                }
                            }
                            echo "<td>" . $sch['START_TIME'] . "</td>";
                            echo "<td>" . $sch['END_TIME'] . "</td>";
                            echo "<td>" . $sch['CP_TITLE'] . "</td>";
                            echo "<td>" . $sch['TITLE'] . "</td>";
                            echo "<td>" . $sch['ROOM'] . "</td></tr>";

                            $counter++;
                        }
                    }
                    echo "</table>";
                } else {
                    echo _noScheduleFound;
                }



                echo '<div style="page-break-before: always;">&nbsp;</div><!-- NEW PAGE -->';


                # --------------------------------------- End Change --------------------------------------------- #
            }
            PDFStop($handle);
        } else
            BackPrompt(''._noRecordsWereFound.'.');
    }
}

if (!$_REQUEST['modfunc']) {
    DrawBC(""._scheduling." > " . ProgramTitle());
    echo "<FORM name=schs id=schs action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname]))."  method=POST >";
    echo '<div class="panel">';
    
    # ---------------------------------------- Marking period selection Start ------------------------------------------ #
    $RET1 = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE FROM marking_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY MARKING_PERIOD_ID"));
    $link = 'Modules.php?modname=' . strip_tags(trim($_REQUEST[modname])) . '&sel_mp=';
    
    echo '<div class="panel-heading">';
    echo '<div class="form-inline"><div class="input-group"><span class="input-group-addon" id="sizing-addon1">'._pleaseSelectTheMarkingPeriod.' :</span>';
    echo "<SELECT name=sel_mp id=sel_mp class=form-control onChange=\"window.location='".$link."' + this.options[this.selectedIndex].value;\">";
    if (count($RET1)) {
        if ($_REQUEST['sel_mp'])
            $mp = $_REQUEST['sel_mp'];
        else {
            $mp = UserMP();
        }
        foreach ($RET1 as $quarter) {
            echo "<OPTION value=$quarter[MARKING_PERIOD_ID]" . ($mp === $quarter['MARKING_PERIOD_ID'] ? ' SELECTED' : '') . ">" . $quarter['TITLE'] . "</OPTION>";
        }
    }
    echo "</SELECT>";
    echo '</div> &nbsp;</div>';
    echo '</div>';
    
    echo '<hr class="no-margin" />';
    
    echo '<div class="panel-body">';

    ###################################################################################################################

    $sql = "SELECT CONCAT(s.LAST_NAME,', ',coalesce(s.COMMON_NAME,s.FIRST_NAME)) AS FULL_NAME,s.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,s.ALT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ,c.TITLE AS COURSE_TITLE,p_cp.TITLE AS PERIOD_TITLE,sr.MARKING_PERIOD_ID,cpv.DAYS, CONCAT(sp.START_TIME, ' to ', sp.END_TIME) AS DURATION,r.TITLE AS ROOM FROM students s,student_enrollment ssm LEFT OUTER JOIN schedule sr ON (sr.STUDENT_ID=ssm.STUDENT_ID),courses c,course_periods p_cp,course_period_var cpv,rooms r,school_periods sp WHERE ssm.STUDENT_ID=s.STUDENT_ID AND p_cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND r.ROOM_ID=cpv.ROOM_ID AND ssm.SYEAR='" . UserSyear() . "' AND ssm.SCHOOL_ID='" . UserSchool() . "' AND ('" . DBDate() . "' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND '" . DBDate() . "'>ssm.START_DATE)) AND ssm.STUDENT_ID='" . UserStudentID() . "' AND s.STUDENT_ID = '" . UserStudentID() . "' AND cpv.PERIOD_ID=sp.PERIOD_ID AND ssm.SYEAR=sr.SYEAR AND sr.COURSE_ID=c.COURSE_ID AND sr.COURSE_PERIOD_ID=p_cp.COURSE_PERIOD_ID AND cpv.PERIOD_ID=sp.PERIOD_ID AND ('" . DBDate() . "' BETWEEN sr.START_DATE AND sr.END_DATE OR sr.END_DATE IS NULL) ORDER BY FULL_NAME,sp.SORT_ORDER";

    $stu_id = UserStudentID();
    $RET_show[$stu_id] = DBGet(DBQuery($sql));
    $date = date('Y' . "-" . 'm' . "-" . 'd');

    if (!$_REQUEST['sel_mp']) {
        $sel_mp = GetCurrentMP('QTR', $date);
        if (!$sel_mp) {
            $sel_mp = GetCurrentMP('SEM', $date);
            if (!$sel_mp) {
                $sel_mp = GetCurrentMP('FY', $date);
            }
        }
    } else
        $sel_mp = $_REQUEST['sel_mp'];
    $sql_mp_detail = 'SELECT title, start_date, end_date, parent_id, grandparent_id from marking_periods WHERE marking_period_id = \'' . $sel_mp . '\'';
    $res_mp_detail = DBQuery($sql_mp_detail);
    $row_mp_detail = DBGet($res_mp_detail);

    $mp_string = '(s.marking_period_id=' . $sel_mp . '';

    if ($row_mp_detail[1]['PARENT_ID'] != -1) {
        $mp_string.=' or s.marking_period_id=' . $row_mp_detail[1]['PARENT_ID'] . '';
    }
    if ($row_mp_detail[1]['GRANDPARENT_ID'] != -1) {
        $mp_string.=' or s.marking_period_id=' . $row_mp_detail[1]['GRANDPARENT_ID'] . '';
    }


    $mp_string.=' or s.marking_period_id is null';
    $mp_d = is_array($mp_d) ? implode(',', $mp_d) : '';
    if (count($RET_show)) {
        foreach ($RET_show as $student_id => $courses) {
            if (count($RET_show[$stu_id]) > 0)
                $sch_exist = DBGet(DBQuery('SELECT COUNT(s.id) AS SCH_COUNT FROM schedule s WHERE s.syear=\'' . UserSyear() . '\'
					AND s.student_id=\'' . $courses[1]['STUDENT_ID'] . '\'
					AND s.school_id=\'' . UserSchool() . '\'
					AND ' . $mp_string . ' )'));
            $sch_exist_yn = $sch_exist[1]['SCH_COUNT'];
            if ($sch_exist_yn != 0) {

                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered">';
                echo '<thead>';
                echo '<tr><th width=15% class="info">Days</th>';
                echo '<th>Start Time</th>';
                echo '<th>End Time</th>';
                echo '<th>Period - Teacher</th>';
                echo '<th>Marking Period</th>';
                echo '<th>Room/Location</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                $ar = array('Sunday' => 'U', 'Monday' => 'M', 'Tuesday' => 'T', 'Wednesday' => 'W', 'Thursday' => 'H', 'Friday' => 'F', 'Saturday' => 'S');
                foreach ($ar as $day => $value) {
                    $counter = 0;

                    $r_ch = DBGet(DBQuery('SELECT cp.title AS cp_title, cp.short_name, r.title as room, sp.start_time, sp.end_time,cp.marking_period_id as title ,sp.sort_order
			FROM school_periods sp, course_periods cp, schedule s, marking_periods mp,course_period_var cpv,rooms r
			WHERE cp.syear=\'' . UserSyear() . '\'
			AND s.syear=\'' . UserSyear() . '\'
			AND s.student_id=\'' . $courses[1]['STUDENT_ID'] . '\'
			AND s.course_period_id=cp.course_period_id
			AND sp.period_id=cpv.period_id
                        AND cp.course_period_id=cpv.course_period_id
                        AND r.room_id=cpv.room_id
                        AND s.start_date<=\'' . date('Y-m-d') . '\'
                        AND (s.end_date IS NULL OR s.end_date>=\'' . date('Y-m-d') . '\')
			AND cpv.days like \'' . '%' . $value . '%' . '\'
			AND s.school_id=\'' . UserSchool() . '\'
			
			AND ' . $mp_string . ' ) GROUP BY cpv.course_period_id  order by sp.sort_order'));


                    foreach ($r_ch as $mp_k => $mp_v) {

                        if ($mp_v['TITLE'] == '') {
                            $r_ch[$mp_k]['TITLE'] = 'Full Year';
                        } else {
                            $qr_mp = DBGet(DBQuery('SELECT TITLE FROM marking_periods WHERE marking_period_id=' . $mp_v['TITLE']));
                            $r_ch[$mp_k]['TITLE'] = $qr_mp[1]['TITLE'];
                        }
                    }

                    $rs = DBQuery('SELECT cp.title AS cp_title, cp.short_name, r.title as room, sp.start_time, sp.end_time, mp.title,sp.sort_order
			FROM school_periods sp, course_periods cp, schedule s, marking_periods mp,course_period_var cpv,rooms r
			WHERE cp.syear=\'' . UserSyear() . '\'
			AND s.syear=\'' . UserSyear() . '\'
			AND s.student_id=\'' . $courses[1]['STUDENT_ID'] . '\'
			AND s.course_period_id=cp.course_period_id
			AND sp.period_id=cpv.period_id
                        AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                        AND r.room_id=cpv.room_id
                        AND s.start_date<=\'' . date('Y-m-d') . '\'
                        AND (s.end_date IS NULL OR s.end_date>=\'' . date('Y-m-d') . '\')
			AND cpv.days like \'' . '%' . $value . '%' . '\'
			AND s.school_id=\'' . UserSchool() . '\'
			AND s.marking_period_id=mp.marking_period_id
			AND ' . $mp_string . ') order by sp.sort_order');



                    $no_record = count($r_ch);

                    foreach ($r_ch as $sch) {
                        echo '<tr class="even">';
                        if ($counter == 0) {

                            if ($value == 'U') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            } elseif ($value == 'M') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            } elseif ($value == 'T') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            } elseif ($value == 'W') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            } elseif ($value == 'H') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            } elseif ($value == 'F') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            } elseif ($value == 'S') {
                                echo "<th class='info' rowspan='" . $no_record . "'>" . $day . "</th>";
                            }
                        }
                        echo "<td>" . $sch['START_TIME'] . "</td>";
                        echo "<td>" . $sch['END_TIME'] . "</td>";
                        echo "<td>" . $sch['CP_TITLE'] . "</td>";
                        echo "<td>" . $sch['TITLE'] . "</td>";
                        echo "<td>" . $sch['ROOM'] . "</td></tr>";

                        $counter++;
                    }
                }
                echo '</tbody>';
                echo "</table>";
                echo "</div>"; //.table-responsive
            } else {
                $error = _noScheduleFound;
            }
        }

        if ($error) {

            echo $error;
        }
    } else {
        BackPrompt(_noStudentsWereFound.'.');
    }
#############################################################################################
    echo "</FORM>";
    $footer_options  = "<FORM name=sch class=\"no-padding no-margin\" id=sch action=ForExport.php?modname=" . strip_tags(trim($_REQUEST[modname]). "&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_openSIS_PDF=true") . " method=POST target=_blank>";
    $footer_options .= "<input type=hidden name=sel_mp value=$sel_mp>";
    $footer_options .= '<button type=submit class="btn btn-success btn-labeled pull-right" value="Print"><b><i class="icon-printer4"></i></b>'._print.'</button>';
    $footer_options .= "</FORM>";
    //PopTable('footer',$footer_options);
    echo '</div>'; //.panel-body
    echo '<div class="panel-footer"><div class="heading-elements">'.$footer_options.'</div></div>';
    echo '</div>'; //.panel
}

function _makeDays($value, $column) {
    foreach (array('U', 'M', 'T', 'W', 'H', 'F', 'S') as $day) {
        foreach (array('U') as $day) {
            if (strpos($value, $day) !== false)
                $return = 'Sunday';
        }
        foreach (array('M') as $day) {
            if (strpos($value, $day) !== false)
                $return1 = 'Monday';
        }

        foreach (array('T') as $day) {
            if (strpos($value, $day) !== false)
                $return2 = 'Tuesday';
        }
        foreach (array('F') as $day) {
            if (strpos($value, $day) !== false)
                $return3 = 'FridaY';
        }
        return $return . $return1 . $return2 . $return3;
    }
}

function _makeChooseCheckbox($value, $title) {
    return '<INPUT type=checkbox name=st_arr[] value=' . $value . ' checked>';
}

?>
