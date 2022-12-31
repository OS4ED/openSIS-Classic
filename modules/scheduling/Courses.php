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
unset($_SESSION['_REQUEST_vars']['subject_id']);
unset($_SESSION['_REQUEST_vars']['course_id']);
unset($_SESSION['_REQUEST_vars']['course_period_id']);
// if only one subject, select it automatically -- works for Course Setup and Choose a Course
if ($_REQUEST['modfunc'] != 'delete' && !$_REQUEST['subject_id']) {
	$subjects_RET = DBGet(DBQuery('SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
	if (count($subjects_RET) == 1)
		$_REQUEST['subject_id'] = $subjects_RET[1]['SUBJECT_ID'];
}
if ($_REQUEST['course_modfunc'] == 'search') {
	PopTable('header', 'Search');
	echo "<FORM name=F1 id=F1 action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=" . strip_tags(trim($_REQUEST[modfunc])) . "&course_modfunc=search method=POST>";
	echo '<TABLE><TR><TD><INPUT type=text class=form-control name=search_term value="' . strip_tags(trim($_REQUEST['search_term'])) . '"></TD><TD><INPUT type=submit class="btn btn-primary" value=' . _search . ' onclick=\'formload_ajax("F1")\';></TD></TR></TABLE>';
	echo '</FORM>';
	PopTable('footer');

	if ($_REQUEST['search_term']) {
		$subjects_RET = DBGet(DBQuery("SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE (UPPER(TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));

		$courses_RET = DBGet(DBQuery("SELECT SUBJECT_ID,COURSE_ID,TITLE FROM courses WHERE (UPPER(TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
		$periods_RET = DBGet(DBQuery("SELECT c.SUBJECT_ID,cp.COURSE_ID,cp.COURSE_PERIOD_ID,cp.TITLE FROM course_periods cp,courses c WHERE cp.COURSE_ID=c.COURSE_ID AND (UPPER(cp.TITLE) LIKE '%" . strtoupper($_REQUEST['search_term']) . "%' OR UPPER(cp.SHORT_NAME) = '" . strtoupper($_REQUEST['search_term']) . "') AND cp.SYEAR='" . UserSyear() . "' AND cp.SCHOOL_ID='" . UserSchool() . "'"));

		echo '<TABLE><TR><TD valign=top>';
		$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";

		$link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
		ListOutput($subjects_RET, array('TITLE' => 'Subject'), _subject, _subjects, $link);
		echo '</TD><TD valign=top>';
		$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";

		$link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID');
		ListOutput($courses_RET, array('TITLE' => 'Course'), _course, _courses, $link);
		echo '</TD><TD valign=top>';
		$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]";

		$link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID', 'course_id' => 'COURSE_ID', 'course_period_id' => 'COURSE_PERIOD_ID');
		ListOutput($periods_RET, array('TITLE' => 'Course Period'), _coursePeriod, _coursePeriods, $link);
		echo '</TD></TR></TABLE>';
	}
}
// UPDATING
if ($_REQUEST['tables'] && ($_POST['tables'] || $_REQUEST['ajax']) && AllowEdit()) {
	$where = array(
		'course_subjects' => 'SUBJECT_ID',
		'courses' => 'COURSE_ID',
		'course_periods' => 'COURSE_PERIOD_ID'
	);

	if ($_REQUEST['tables']['parent_id'])
		$_REQUEST['tables']['course_periods'][$_REQUEST['course_period_id']]['PARENT_ID'] = $_REQUEST['tables']['parent_id'];

	foreach ($_REQUEST['tables'] as $table_name => $tables) {
		foreach ($tables as $id => $columns) {
			if ($columns['TOTAL_SEATS'] && !is_numeric($columns['TOTAL_SEATS']))
				$columns['TOTAL_SEATS'] = par_rep('/[^0-9]/+', '', $columns['TOTAL_SEATS']);
			if ($columns['DAYS']) {
				foreach ($columns['DAYS'] as $day => $y) {
					if ($y == 'Y')
						$days .= $day;
				}
				$columns['DAYS'] = $days;
			}
			if ($id != 'new') {
				if ($table_name == 'courses' && $columns['SUBJECT_ID'] && $columns['SUBJECT_ID'] != $_REQUEST['subject_id'])
					$_REQUEST['subject_id'] = $columns['SUBJECT_ID'];

				$sql = 'UPDATE ' . $table_name . ' SET ';

				if ($table_name == 'course_periods') {
					$current = DBGet(DBQuery('SELECT cp.TEACHER_ID,cpv.PERIOD_ID,cp.MARKING_PERIOD_ID,cpv.DAYS,cp.SHORT_NAME FROM course_periods cp,course_period_var cpv WHERE cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND \'' . $where[$table_name] . '\'=\'' . $id . '\''));

					if ($columns['TEACHER_ID'])
						$staff_id = $columns['TEACHER_ID'];
					else
						$staff_id = $current[1]['TEACHER_ID'];
					if ($columns['PERIOD_ID'])
						$period_id = $columns['PERIOD_ID'];
					else
						$period_id = $current[1]['PERIOD_ID'];
					if (isset($columns['MARKING_PERIOD_ID']))
						$marking_period_id = $columns['MARKING_PERIOD_ID'];
					else
						$marking_period_id = $current[1]['MARKING_PERIOD_ID'];
					if ($columns['DAYS'])
						$days = $columns['DAYS'];
					else
						$days = $current[1]['DAYS'];
					if ($columns['SHORT_NAME'])
						$short_name = $columns['SHORT_NAME'];
					else
						$short_name = $current[1]['SHORT_NAME'];

					$teacher = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME FROM staff WHERE SYEAR=\'' . UserSyear() . '\' AND STAFF_ID=\'' . $staff_id . '\''));
					$period = DBGet(DBQuery('SELECT TITLE FROM school_periods WHERE PERIOD_ID=\'' . $period_id . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));
					if (GetMP($marking_period_id, 'TABLE') != 'school_years')
						$mp_title = GetMP($marking_period_id, 'SHORT_NAME') . ' - ';
					if (strlen($days) < 5)
						$mp_title .= $days . ' - ';
					if ($short_name)
						$mp_title .= $short_name . ' - ';

					$title = str_replace("'", "''", $period[1]['TITLE'] . ' - ' . $mp_title . $teacher[1]['FIRST_NAME'] . ' ' . $teacher[1]['MIDDLE_NAME'] . ' ' . $teacher[1]['LAST_NAME']);
					$sql .= 'TITLE=\'' . $title . '\',';

					if (isset($columns['MARKING_PERIOD_ID'])) {
						if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
							$columns['MP'] = 'FY';
						elseif (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
							$columns['MP'] = 'SEM';
						else
							$columns['MP'] = 'QTR';
					}
				}

				foreach ($columns as $column => $value)
					$sql .= $column . '=\'' . str_replace("\'", "''", $value) . '\',';

				$sql = substr($sql, 0, -1) . ' WHERE ' . $where[$table_name] . '=\'' . $id . '\'';
				DBQuery($sql);

				// ----------------------------------------------- //
				if ($_REQUEST['w_course_period_id']) {
					$sql_1 = 'UPDATE' . $table_name . 'SET PARENT_ID=\'' . $_REQUEST['w_course_period_id'] . '\' WHERE \'' . $where[$table_name] . '=\'' . $id . '\'';
					DBQuery($sql_1);
				}
				// ----------------------------------------------- //

			} else {
				$sql = 'INSERT INTO ' . $table_name . ' ';

				if ($table_name == 'course_subjects') {

					// $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'' . _course_subjects . '\''));
					// $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];

					$fields = 'SCHOOL_ID,SYEAR,';
					$values = '\'' . UserSchool() . '\',\'' . UserSyear() . '\',';
					// $_REQUEST['subject_id'] = $id[1]['ID'];
				} elseif ($table_name == 'courses') {

					// $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'' . _courses . '\''));
					// $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
					// $_REQUEST['course_id'] = $id[1]['ID'];
					$fields = 'SUBJECT_ID,SCHOOL_ID,SYEAR,';
					$values = '\'' . $_REQUEST['subject_id'] . '\',\'' . UserSchool() . '\',\'' . UserSyear() . '\',';
				} elseif ($table_name == 'course_periods') {

					// edited
					// $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'' . _course_periods . '\''));
					// $id[1]['ID'] = $id[1]['AUTO_INCREMENT'];
					// edited
					$fields = 'SYEAR,SCHOOL_ID,COURSE_ID,TITLE,';
					$teacher = DBGet(DBQuery('SELECT FIRST_NAME,LAST_NAME,MIDDLE_NAME FROM staff WHERE SYEAR=\'' . UserSyear() . '\' AND STAFF_ID=\'' . $columns[TEACHER_ID] . '\''));
					$period = DBGet(DBQuery('SELECT TITLE FROM school_periods WHERE PERIOD_ID=\'' . $columns[PERIOD_ID] . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\''));


					// if (!isset($columns['PARENT_ID']))
					// 	$columns['PARENT_ID'] = $id[1]['ID'];

					if (isset($columns['MARKING_PERIOD_ID'])) {
						if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_years')
							$columns['MP'] = 'FY';
						elseif (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') == 'school_semesters')
							$columns['MP'] = 'SEM';
						else
							$columns['MP'] = 'QTR';

						if (GetMP($columns['MARKING_PERIOD_ID'], 'TABLE') != 'school_years')
							$mp_title = GetMP($columns['MARKING_PERIOD_ID'], 'SHORT_NAME') . ' - ';
					}

					if (strlen($columns['DAYS']) < 5)
						$mp_title .= $columns['DAYS'] . ' - ';
					if ($columns['SHORT_NAME'])
						$mp_title .= $columns['SHORT_NAME'] . ' - ';
					$title = str_replace("'", "''", $period[1]['TITLE'] . ' - ' . $mp_title . $teacher[1]['FIRST_NAME'] . ' ' . $teacher[1]['MIDDLE_NAME'] . ' ' . $teacher[1]['LAST_NAME']);

					$values = '\'' . UserSyear() . '\',\'' . UserSchool() . '\',' . $_REQUEST[course_id] . ',' . $title . ',';
					// $_REQUEST['course_period_id'] = $id[1]['ID'];
				}

				$go = 0;
				foreach ($columns as $column => $value) {
					if (isset($value)) {
						$fields .= $column . ',';
						$values .= '\'' . str_replace("\'", "''", $value) . '\',';
						$go = true;
					}
				}
				$sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

				if ($go){
					DBQuery($sql);
					if ($table_name == 'course_subjects') {
                        $_REQUEST['subject_id'] = mysqli_insert_id($connection);
                    } else if ($table_name == 'courses') {
                        $_REQUEST['course_id'] = mysqli_insert_id($connection);
                    } else if ($table_name == 'course_periods') {
                        $_REQUEST['course_period_id'] = mysqli_insert_id($connection);
                        if (!isset($columns['PARENT_ID'])) {
                            $update_parent_id = "UPDATE course_periods SET PARENT_ID=" . $_REQUEST['course_period_id'] . " WHERE COURSE_PERIOD_ID = " . $_REQUEST['course_period_id'];
                            DBQuery($update_parent_id);
                        }
                    }
				}	

				// ----------------------------------------------- //
				if ($_REQUEST['w_course_period_id']) {
					$max_id = DBGet(DBQuery('SELECT MAX(COURSE_PERIOD_ID) FROM course_periods;'));
					$sql_2 = 'UPDATE course_periods SET PARENT_ID=\'' . $_REQUEST['w_course_period_id'] . '\' WHERE COURSE_PERIOD_ID = \'' . $max_id[1]['MAX'];;
					DBQuery($sql_2);
				}
				// ----------------------------------------------- //

			}
		}
	}
	unset($_REQUEST['tables']);
}

if ($_REQUEST['modfunc'] == 'delete' && AllowEdit()) {
	unset($sql);
	if ($_REQUEST['course_period_id']) {
		$table = 'course period';
		$sql[] = 'UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID=\'' . $_REQUEST[course_period_id] . '\'';
		$sql[] = 'DELETE FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_REQUEST[course_period_id] . '\'';
		$sql[] = 'DELETE FROM schedule WHERE COURSE_PERIOD_ID=\'' . $_REQUEST[course_period_id] . '\'';
	} elseif ($_REQUEST['course_id']) {
		$table = 'course';
		$sql[] = 'DELETE FROM courses WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\'';
		$sql[] = 'UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID IN (SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\')';
		$sql[] = 'DELETE FROM course_periods WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\'';
		$sql[] = 'DELETE FROM schedule WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\'';
		$sql[] = 'DELETE FROM schedule_requests WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\'';
	} elseif ($_REQUEST['subject_id']) {
		$table = 'subject';
		$sql[] = 'DELETE FROM course_subjects WHERE SUBJECT_ID=\'' . $_REQUEST[subject_id] . '\'';
		$courses = DBGet(DBQuery('SELECT COURSE_ID FROM courses WHERE SUBJECT_ID=\'' . $_REQUEST[subject_id] . '\''));
		if (count($courses)) {
			foreach ($courses as $course) {
				$sql[] = 'DELETE FROM courses WHERE COURSE_ID=\'' . $course[COURSE_ID] . '\'';
				$sql[] = 'UPDATE course_periods SET PARENT_ID=NULL WHERE PARENT_ID IN (SELECT COURSE_PERIOD_ID FROM course_periods WHERE COURSE_ID=\'' . $course[COURSE_ID] . '\)';
				$sql[] = 'DELETE FROM course_periods WHERE COURSE_ID=\'' . $course[COURSE_ID] . '\'';
				$sql[] = 'DELETE FROM schedule WHERE COURSE_ID=\'' . $course[COURSE_ID] . '\'';
				$sql[] = 'DELETE FROM schedule_requests WHERE COURSE_ID=\'' . $course[COURSE_ID] . '\'';
			}
		}
	}

	if (DeletePrompt($table)) {
		if (BlockDelete($table)) {
			foreach ($sql as $query)
				DBQuery($query);
			unset($_REQUEST['modfunc']);
		}
	}
}

if ((!$_REQUEST['modfunc'] || $_REQUEST['modfunc'] == 'choose_course') && !$_REQUEST['course_modfunc']) {
	if ($_REQUEST['modfunc'] != 'choose_course')
		DrawBC("" . _scheduling . " > " . ProgramTitle());
	$sql = 'SELECT SUBJECT_ID,TITLE FROM course_subjects WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY TITLE';
	$QI = DBQuery($sql);
	$subjects_RET = DBGet($QI);

	if ($_REQUEST['modfunc'] != 'choose_course') {
		if (AllowEdit())
			$delete_button = "<INPUT type=button class='btn btn-primary' value='._delete.' onClick='javascript:window.location=\"Modules.php?modname=$_REQUEST[modname]&modfunc=delete&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id]\"'> ";
		// ADDING & EDITING FORM
		if ($_REQUEST['course_period_id']) {
			if ($_REQUEST['course_period_id'] != 'new') {
				$sql = 'SELECT cp.PARENT_ID,cp.TITLE,cp.SHORT_NAME,cpv.PERIOD_ID,cpv.DAYS,
								cp.MP,cp.MARKING_PERIOD_ID,cp.TEACHER_ID,cp.CALENDAR_ID,
								r.TITLE AS ROOM,cp.TOTAL_SEATS,cpv.DOES_ATTENDANCE,
								cp.GRADE_SCALE_ID,cp.DOES_HONOR_ROLL,cp.DOES_CLASS_RANK,
								cp.GENDER_RESTRICTION,cp.HOUSE_RESTRICTION,cp.CREDITS,
								cp.HALF_DAY,cp.DOES_BREAKOFF
						FROM course_periods cp,course_period_var cpv,rooms r
						WHERE cp.COURSE_PERIOD_ID=\'' . $_REQUEST[course_period_id] . '\' AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID AND cpv.ROOM_ID=r.ROOM_ID';
				$QI = DBQuery($sql);
				$RET = DBGet($QI);
				$RET = $RET[1];
				$title = $RET['TITLE'];
				$new = false;
			} else {
				$sql = 'SELECT TITLE
						FROM courses
						WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\'';
				$QI = DBQuery($sql);
				$RET = DBGet($QI);
				$title = $RET[1]['TITLE'] . ' - New Period';
				unset($delete_button);
				unset($RET);
				$checked = 'CHECKED';
				$new = true;
			}

			echo "<FORM name=F2 id=F2 action=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=$_REQUEST[course_period_id] method=POST>";
			DrawHeaderHome($title, $delete_button . SubmitButton(_save, '', 'class="btn btn-primary" onclick="formcheck_scheduling_course_F2();"'));

			$header .= '<TABLE cellpadding=3 width=760 >';
			$header .= '<TR>';

			$header .= '<TD>' . TextInput($RET['SHORT_NAME'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][SHORT_NAME]', 'Short Name', 'class=form-control') . '</TD>';

			$teachers_RET = DBGet(DBQuery('SELECT STAFF_ID,LAST_NAME,FIRST_NAME,MIDDLE_NAME FROM staff WHERE (SCHOOLS IS NULL OR FIND_IN_SET(\'' . UserSchool() . '\',SCHOOLS)>0) AND SYEAR=\'' . UserSyear() . '\' AND PROFILE=' . 'teacher' . ' ORDER BY LAST_NAME,FIRST_NAME'));
			if (count($teachers_RET)) {
				foreach ($teachers_RET as $teacher)
					$teachers[$teacher['STAFF_ID']] = $teacher['LAST_NAME'] . ', ' . $teacher['FIRST_NAME'] . ' ' . $teacher['MIDDLE_NAME'];
			}
			$header .= '<TD>' . SelectInput($RET['TEACHER_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][TEACHER_ID]', 'Teacher', $teachers) . '</TD>';

			$header .= '<TD>' . TextInput($RET['ROOM'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][ROOM]', 'Room', 'class=form-control') . '</TD>';

			$periods_RET = DBGet(DBQuery('SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY SORT_ORDER'));
			if (count($periods_RET)) {
				foreach ($periods_RET as $period)
					$periods[$period['PERIOD_ID']] = $period['TITLE'];
			}
			$header .= '<TD>' . SelectInput($RET['PERIOD_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][PERIOD_ID]', 'Period', $periods) . '</TD>';
			$header .= '<TD>';
			if ($new == false)
				$header .= '<DIV id=days><div onclick=\'addHTML("';
			$header .= '<TABLE><TR>';
			$days = array('U', 'M', 'T', 'W', 'H', 'F', 'S');
			foreach ($days as $day) {
				if (strpos($RET['DAYS'], $day) !== false || ($new && $day != 'S' && $day != 'U'))
					$value = 'Y';
				else
					$value = '';

				$header .= '<TD>' . str_replace('"', '\"', CheckboxInput($value, 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DAYS][' . $day . ']', ($day == 'U' ? 'S' : $day), $checked, false, '', '', false)) . '</TD>';
			}
			$header .= '</TR></TABLE>';

			if ($new == false)
				$header .= '","days",true);\'>' . $RET['DAYS'] . '</div></DIV><small><FONT color=' . Preferences('TITLES') . '>Meeting Days</FONT></small>';
			$header .= '</TD>';

			$mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SHORT_NAME,2 AS `TABLE`,SORT_ORDER FROM school_quarters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' UNION SELECT MARKING_PERIOD_ID,SHORT_NAME,1 AS `TABLE`,SORT_ORDER FROM school_semesters WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' UNION SELECT MARKING_PERIOD_ID,SHORT_NAME,0 AS `TABLE`,SORT_ORDER FROM school_years WHERE SCHOOL_ID=\'' . UserSchool() . '\' AND SYEAR=\'' . UserSyear() . '\' ORDER BY 3,4'));
			unset($options);
			if ($_REQUEST['course_period_id'] != 'new') {
				$available_seats = DBGet(DBQuery('SELECT (TOTAL_SEATS - FILLED_SEATS) AS AVAILABLE_SEATS FROM course_periods WHERE COURSE_PERIOD_ID=\'' . $_REQUEST['course_period_id'] . '\''));
				$available_seats = $available_seats[1]['AVAILABLE_SEATS'];
			}

			if (count($mp_RET)) {
				foreach ($mp_RET as $mp)
					$options[$mp['MARKING_PERIOD_ID']] = $mp['SHORT_NAME'];
			}
			$header .= '<TD>' . SelectInput($RET['MARKING_PERIOD_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][MARKING_PERIOD_ID]', 'Marking Period', $options, false) . '</TD>';
			$header .= '<TD>' . TextInput($RET['TOTAL_SEATS'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][TOTAL_SEATS]', 'Seats', 'size=4 class=form-control') . '</TD>';
			if ($_REQUEST['course_period_id'] != 'new')
				$header .= '<TD><FONT color=green>' . $available_seats . '</FONT><BR><FONT color=gray><SMALL>Available Seats</SMALL></FONT></TD>';

			$header .= '</TR>';

			$header .= '<TR>';

			$header .= '<TD>' . CheckboxInput($RET['DOES_ATTENDANCE'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_ATTENDANCE]', 'Takes Attendance', $checked, $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
			$header .= '<TD>' . CheckboxInput($RET['DOES_HONOR_ROLL'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_HONOR_ROLL]', 'Affects Honor Roll', $checked, $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
			$header .= '<TD>' . CheckboxInput($RET['DOES_CLASS_RANK'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_CLASS_RANK]', 'Affects Class Rank', $checked, $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
			$header .= '<TD>' . SelectInput($RET['GENDER_RESTRICTION'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][GENDER_RESTRICTION]', 'Gender Restriction', array('N' => 'None', 'M' => 'Male', 'F' => 'Female'), false) . '</TD>';

			$options_RET = DBGet(DBQuery("SELECT TITLE,ID FROM report_card_grade_scales WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "'"));
			$options = array();
			foreach ($options_RET as $option)
				$options[$option['ID']] = $option['TITLE'];
			$header .= '<TD>' . SelectInput($RET['GRADE_SCALE_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][GRADE_SCALE_ID]', 'Grading Scale', $options, 'Not Graded') . '</TD>';
			//BJJ Added to handle credits
			$header .= '<TD>' . TextInput(sprintf('%0.3f', $RET['CREDITS']), 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][CREDITS]', 'Credits', 'size=4 class=form-control') . '</TD>';
			$options_RET = DBGet(DBQuery("SELECT TITLE,CALENDAR_ID FROM school_calendars WHERE SYEAR='" . UserSyear() . "' AND SCHOOL_ID='" . UserSchool() . "' ORDER BY DEFAULT_CALENDAR"));
			$options = array();
			foreach ($options_RET as $option)
				$options[$option['CALENDAR_ID']] = $option['TITLE'];
			$header .= '<TD>' . SelectInput($RET['CALENDAR_ID'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][CALENDAR_ID]', 'Calendar', $options, false) . '</TD>';

			//BJJ Parent course select was here.

			$header .= '</TR>';

			$header .= '<TR>';


			$header .= '<TD>' . CheckboxInput($RET['HALF_DAY'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][HALF_DAY]', 'Half Day', $checked, $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
			$header .= '<TD>' . CheckboxInput($RET['DOES_BREAKOFF'], 'tables[course_periods][' . $_REQUEST['course_period_id'] . '][DOES_BREAKOFF]', 'Allow Teacher Gradescale', $checked, $new, '<IMG SRC=assets/check.gif height=15 vspace=0 hspace=0 border=0>', '<IMG SRC=assets/x.gif height=15 vspace=0 hspace=0 border=0>') . '</TD>';
			//BJJ added cells to place parent selection in last column.
			$header .=  "<td colspan= 4>&nbsp;</td>";


			//BJJ moved parent course select here:
			if ($_REQUEST['course_period_id'] != 'new' && $RET['PARENT_ID'] != $_REQUEST['course_period_id']) {
				$parent = DBGet(DBQuery('SELECT cp.TITLE as CP_TITLE,c.TITLE AS C_TITLE FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID=\'' . $RET['PARENT_ID'] . '\''));
				$parent = $parent[1]['C_TITLE'] . ' : ' . $parent[1]['CP_TITLE'];
			} elseif ($_REQUEST['course_period_id'] != 'new') {
				$children = DBGet(DBQuery('SELECT COURSE_PERIOD_ID FROM course_periods WHERE PARENT_ID=\'' . $_REQUEST['course_period_id'] . '\' AND COURSE_PERIOD_ID!=\'' . $_REQUEST['course_period_id'] . '\''));
				if (count($children))
					$parent = 'N/A';
				else
					$parent = 'None';
			}

			//		--------------------------------------------- Temp Coment ------------------------------------------------- 	//

			$header .= "<TD colspan=2><DIV id=course_div>" . $parent . "</DIV> " . ($parent != 'N/A' ? "<A HREF=# onclick='window.open(\"ForWindow.php?modname=miscellaneous/ChooseParentCourse.php\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'>Choose</A><BR>" : '') . "<small><FONT color=" . Preferences('TITLES') . ">Parent Course Period</FONT></small></TD>";

			//		--------------------------------------------- Temp Coment ------------------------------------------------- 	//
			$header .= '</TR>';
			$header .= '</TABLE>';

			DrawHeaderHome($header);

			echo '</FORM>';
		} elseif ($_REQUEST['course_id']) {
			if ($_REQUEST['course_id'] != 'new') {
				$sql = 'SELECT TITLE,SHORT_NAME,GRADE_LEVEL
						FROM courses
						WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\'';
				$QI = DBQuery($sql);
				$RET = DBGet($QI);
				$RET = $RET[1];
				$title = $RET['TITLE'];
			} else {
				$sql = 'SELECT TITLE
						FROM course_subjects
						WHERE SUBJECT_ID=\'' . $_REQUEST[subject_id] . '\' ORDER BY TITLE';
				$QI = DBQuery($sql);
				$RET = DBGet($QI);
				$title = $RET[1]['TITLE'] . ' - ' . _newCourse . '';
				unset($delete_button);
				unset($RET);
			}

			echo "<FORM name=F3 id=F3 action=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id] method=POST>";
			DrawHeaderHome($title, $delete_button . SubmitButton(_save, '', 'class="btn btn-primary" onclick="formcheck_scheduling_course_F3();"'));
			$header .= '<TABLE cellpadding=3 width=100%>';
			$header .= '<TR>';

			$header .= '<TD>' . TextInput($RET['TITLE'], 'tables[courses][' . $_REQUEST['course_id'] . '][TITLE]', 'Title', 'class=cell_mod_wide') . '</TD>';
			$header .= '<TD>' . TextInput($RET['SHORT_NAME'], 'tables[courses][' . $_REQUEST['course_id'] . '][SHORT_NAME]', 'Short Name', 'class=form-control') . '</TD>';
			if ($_REQUEST['modfunc'] != 'choose_course') {
				foreach ($subjects_RET as $type)
					$options[$type['SUBJECT_ID']] = $type['TITLE'];

				$header .= '<TD>' . SelectInput($RET['SUBJECT_ID'] ? $RET['SUBJECT_ID'] : $_REQUEST['subject_id'], 'tables[courses][' . $_REQUEST['course_id'] . '][SUBJECT_ID]', 'Subject', $options, false) . '</TD>';
			}
			$header .= '</TR>';
			$header .= '</TABLE>';
			DrawHeaderHome($header);
			echo '</FORM>';
		} elseif ($_REQUEST['subject_id']) {
			if ($_REQUEST['subject_id'] != 'new') {
				$sql = 'SELECT TITLE
						FROM course_subjects
						WHERE SUBJECT_ID=\'' . $_REQUEST[subject_id] . '\'';
				$QI = DBQuery($sql);
				$RET = DBGet($QI);
				$RET = $RET[1];
				$title = $RET['TITLE'];
			} else {
				$title = 'newSubject';
				unset($delete_button);
			}

			echo "<FORM name=F4 id=F4 action=Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id] method=POST>";
			DrawHeaderHome($title, $delete_button . SubmitButton(_save, '', 'class="btn btn-primary" onclick="formcheck_scheduling_course_F4();"'));
			$header .= '<TABLE cellpadding=3 width=100%>';
			$header .= '<TR>';

			$header .= '<TD>' . TextInput($RET['TITLE'], 'tables[course_subjects][' . $_REQUEST['subject_id'] . '][TITLE]', 'Title', 'class=cell_wide') . '</TD>';

			$header .= '</TR>';
			$header .= '</TABLE>';
			DrawHeader($header);
			echo '</FORM>';
		}
	}

	// DISPLAY THE MENU
	$LO_options = array('save' => false, 'search' => false);

	if (!$_REQUEST['subject_id'] || $_REQUEST['modfunc'] == 'choose_course')

		echo "<A HREF=ForWindow.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&course_modfunc=search><i class=\"fa fa-search\"></i> Search</A>";

	echo '<TABLE><TR>';

	if (count($subjects_RET)) {
		if ($_REQUEST['subject_id']) {
			foreach ($subjects_RET as $key => $value) {
				if ($value['SUBJECT_ID'] == $_REQUEST['subject_id'])
					$subjects_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
			}
		}
	}

	echo '<TD valign=top>';
	$columns = array('TITLE' => _subject);
	$link = array();
	$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]";

	$link['TITLE']['variables'] = array('subject_id' => 'SUBJECT_ID');
	if ($_REQUEST['modfunc'] != 'choose_course')
		$link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=new";

	else
		$link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";

	ListOutput($subjects_RET, $columns, _subject, _subjects, $link, array(), $LO_options);
	echo '</TD>';

	if ($_REQUEST['subject_id'] && $_REQUEST['subject_id'] != 'new') {
		$sql = 'SELECT COURSE_ID,TITLE FROM courses WHERE SUBJECT_ID=\'' . $_REQUEST[subject_id] . '\' ORDER BY TITLE';
		$QI = DBQuery($sql);
		$courses_RET = DBGet($QI);

		if (count($courses_RET)) {
			if ($_REQUEST['course_id']) {
				foreach ($courses_RET as $key => $value) {
					if ($value['COURSE_ID'] == $_REQUEST['course_id'])
						$courses_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
				}
			}
		}

		echo '<TD valign=top>';
		$columns = array('TITLE' => _course);
		$link = array();
		$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]";

		$link['TITLE']['variables'] = array('course_id' => 'COURSE_ID');
		if ($_REQUEST['modfunc'] != 'choose_course')
			$link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=new";

		$link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";

		ListOutput($courses_RET, $columns, _course, _courses, $link, array(), $LO_options);
		echo '</TD>';

		if ($_REQUEST['course_id'] && $_REQUEST['course_id'] != 'new') {

			$sql = 'SELECT COURSE_PERIOD_ID,TITLE,COALESCE(TOTAL_SEATS-FILLED_SEATS,0) AS AVAILABLE_SEATS FROM course_periods WHERE COURSE_ID=\'' . $_REQUEST[course_id] . '\' ORDER BY TITLE';
			$QI = DBQuery($sql);
			$periods_RET = DBGet($QI);

			if (count($periods_RET)) {
				if ($_REQUEST['course_period_id']) {
					foreach ($periods_RET as $key => $value) {
						if ($value['COURSE_PERIOD_ID'] == $_REQUEST['course_period_id'])
							$periods_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
					}
				}
			}

			echo '<TD valign=top>';
			$columns = array('TITLE' => _coursePeriod);
			if ($_REQUEST['modname'] == 'scheduling/Schedule.php')
				$columns += array('AVAILABLE_SEATS' => _availableSeats);
			$link = array();
			$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]";

			$link['TITLE']['variables'] = array('course_period_id' => 'COURSE_PERIOD_ID');
			if ($_REQUEST['modfunc'] != 'choose_course')
				$link['add']['link'] = "Modules.php?modname=$_REQUEST[modname]&subject_id=$_REQUEST[subject_id]&course_id=$_REQUEST[course_id]&course_period_id=new";

			$link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";

			ListOutput($periods_RET, $columns, _period, _periods, $link, array(), $LO_options);
			echo '</TD>';
		}
	}

	echo '</TR></TABLE>';
}

if ($_REQUEST['modname'] == 'scheduling/Courses.php' && $_REQUEST['modfunc'] == 'choose_course' && $_REQUEST['course_period_id']) {
	$course_title = DBGet(DBQuery("SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID='" . $_REQUEST['course_period_id'] . "'"));
	$course_title = $course_title[1]['TITLE'] . '<INPUT type=hidden name=tables[parent_id] value=' . $_REQUEST['course_period_id'] . '>';

	echo "<script language=javascript>opener.document.getElementById(\"course_div\").innerHTML = \"$course_title</small>\"; window.close();</script>";
}
