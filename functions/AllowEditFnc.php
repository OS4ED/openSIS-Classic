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
function AllowEdit($modname = false)
{
    global $_openSIS;
    if (!$modname)
        $modname = $_REQUEST['modname'];

    if ($modname == 'students/Student.php' && $_REQUEST['category_id'])
        $modname = $modname . '&category_id=' . $_REQUEST['category_id'];
    if ($modname == 'users/Staff.php' && $_REQUEST['category_id'])
        $modname = $modname . '&category_id=' . $_REQUEST['category_id'];

    if (User('PROFILE') == 'admin') {

        if (!$_openSIS['AllowEdit']) {

            if (User('PROFILE_ID') != '') {
                $_openSIS['AllowEdit'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND CAN_EDIT=\'Y\''), array(), array('MODNAME'));
            } else {
                $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
                $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
                if ($profile_id_mod != '') {
                    $_openSIS['AllowEdit'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . $profile_id_mod . '\' AND CAN_EDIT=\'Y\''), array(), array('MODNAME'));
                }
            }
        }
        if (!$_openSIS['AllowEdit'])
            $_openSIS['AllowEdit'] = array(true);
        if (!empty($_openSIS['AllowEdit'][$modname]))
            return true;
        else
            return false;
    } else {
        if (User('PROFILE_ID') == 3 || User('PROFILE_ID') == 4) {
            if ($modname == 'attendance/StudentSummary.php')
                return true;
            elseif ($modname == 'schoolsetup/Calendar.php')
                return true;
            elseif ($modname == 'attendance/DailySummary.php')
                return true;
            elseif ($modname == 'scheduling/ViewSchedule.php')
                return true;
            elseif ($modname == 'messaging/Group.php')
                return true;
            else
                return $_openSIS['allow_edit'];
        } elseif (User('PROFILE') == 'teacher') {
            if (User('PROFILE_ID') != '')
                $_openSIS['AllowEdit'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND CAN_EDIT=\'Y\''), array(), array('MODNAME'));

            if ($modname == 'attendance/StudentSummary.php')
                return true;

            elseif ($modname == 'scheduling/ViewSchedule.php')
                return true;
            elseif ($modname == 'attendance/DailySummary.php')
                return true;
            elseif ($modname == 'schoolsetup/Calendar.php')
                return true;
            elseif ($modname == 'scheduling/PrintSchedules.php')
                return true;
            elseif ($modname == 'messaging/Group.php')
                return true;
            elseif ($modname == 'grades/Assignments.php')
                return true;
            elseif ($modname == 'grades/Grades.php')
                return true;
            elseif ($modname == 'grades/InputFinalGrades.php') {
                if (!empty($_REQUEST['mp'])) {
                    if (substr($_REQUEST['mp'], 0, 1) == 'E')
                        $selected_mp = ltrim($_REQUEST['mp'], 'E');
                    else
                        $selected_mp = $_REQUEST['mp'];
                } else {
                    $selected_mp = UserMP();
                }

                $grade_post_date = DBGet(DBQuery('SELECT POST_START_DATE, POST_END_DATE FROM marking_periods WHERE SCHOOL_ID=' . UserSchool() . ' AND SYEAR=' . UserSyear() . ' AND MARKING_PERIOD_ID="' . $selected_mp . '" '));
                if ($grade_post_date[1]['POST_START_DATE'] != '' && $grade_post_date[1]['POST_END_DATE'] != '') {
                    if (date('Y-m-d') >= $grade_post_date[1]['POST_START_DATE'] && date('Y-m-d') <= $grade_post_date[1]['POST_END_DATE'])
                        return true;
                    else
                        return false;
                } else
                    return false;
            } elseif ($modname == 'attendance/TakeAttendance.php')
                return true;
            elseif ($modname == 'attendance/StudentSummary.php')
                return true;
            elseif ($modname == 'users/TeacherPrograms.php?include=attendance/TakeAttendance.php')
                return true;
            else {
                if (!$_openSIS['AllowEdit'])
                    $_openSIS['AllowEdit'] = array(true);

                if (is_countable($_openSIS['AllowEdit'][$modname]) && count($_openSIS['AllowEdit'][$modname]))
                    return true;
                else
                    return false;
            }
        } else
            return $_openSIS['allow_edit'];
    }
}

function AllowUse($modname = false)
{
    global $_openSIS;
    if (!$modname)
        $modname = $_REQUEST['modname'];

    if ($modname == 'students/Student.php' && $_REQUEST['category_id'])
        $modname = $modname . '&category_id=' . $_REQUEST['category_id'];

    if (!$_openSIS['AllowUse']) {
        if (User('PROFILE_ID') != '') {
            $_openSIS['AllowUse'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . User('PROFILE_ID') . '\' AND CAN_USE=\'Y\''), array(), array('MODNAME'));

            if (User('PROFILE_ID') == 4) {
                $_openSIS['AllowUse']['scheduling/PrintSchedules.php']['1']['MODNAME'] = 'scheduling/PrintSchedules.php';
            }
        } else {
            $profile_id_mod = DBGet(DBQuery("SELECT PROFILE_ID FROM staff WHERE USER_ID='" . User('STAFF_ID')));
            $profile_id_mod = $profile_id_mod[1]['PROFILE_ID'];
            $_openSIS['AllowUse'] = DBGet(DBQuery('SELECT MODNAME FROM profile_exceptions WHERE PROFILE_ID=\'' . $profile_id_mod . '\' AND CAN_USE=\'Y\''), array(), array('MODNAME'));
        }
    }


    if (!$_openSIS['AllowUse'])
        $_openSIS['AllowUse'] = array(true);

    if (is_countable($_openSIS['AllowUse'][$modname]) && count($_openSIS['AllowUse'][$modname]))
        return true;
    else
        return false;
}

function ProgramLink($modname, $title = '', $options = '')
{
    if (AllowUse($modname))
        $link = '<A HREF=Modules.php?modname=' . $modname . $options . '>';
    if ($title)
        $link .= $title;
    if (AllowUse($modname))
        $link .= '</A>';

    return $link;
}

function ProgramLinkforExport($modname, $title = '', $options = '', $extra = '')
{
    if (AllowUse($modname))
        $link = '<A HREF=ForExport.php?modname=' . $modname . $options . ' ' . $extra . '>';
    if ($title)
        $link .= $title;
    if (AllowUse($modname))
        $link .= '</A>';

    return $link;
}
