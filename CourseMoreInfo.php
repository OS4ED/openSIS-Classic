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
include('RedirectRootInc.php');
include'ConfigInc.php';
include 'Warehouse.php';
// include('functions/SqlSecurityFnc.php');

$id = sqlSecurityFilter($_REQUEST['id']);

 $sql = 'SELECT
                                s.COURSE_ID,s.COURSE_PERIOD_ID,
                                s.MARKING_PERIOD_ID,s.START_DATE,s.END_DATE,s.MODIFIED_DATE,s.MODIFIED_BY,
                                UNIX_TIMESTAMP(s.START_DATE) AS START_EPOCH,UNIX_TIMESTAMP(s.END_DATE) AS END_EPOCH,sp.PERIOD_ID,
                                cpv.PERIOD_ID,s.MARKING_PERIOD_ID as COURSE_MARKING_PERIOD_ID,cp.MARKING_PERIOD_ID as mpa_id,cp.MP,sp.SORT_ORDER,
                                c.TITLE,cp.COURSE_PERIOD_ID AS PERIOD_PULLDOWN,
                                s.STUDENT_ID,r.TITLE AS ROOM,(SELECT GROUP_CONCAT(cpv.DAYS) FROM course_period_var cpv WHERE cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID) as DAYS,SCHEDULER_LOCK,CONCAT(st.LAST_NAME, \'' . ' ' . '\' ,st.FIRST_NAME) AS MODIFIED_NAME
                                FROM courses c,course_periods cp,course_period_var cpv,rooms r,school_periods sp,schedule s
                                LEFT JOIN staff st ON s.MODIFIED_BY = st.STAFF_ID
                                WHERE
                                s.COURSE_ID = c.COURSE_ID AND s.COURSE_ID = cp.COURSE_ID
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
                                 AND r.ROOM_ID=cpv.ROOM_ID
                                AND s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID
                                AND s.SCHOOL_ID = sp.SCHOOL_ID AND s.SYEAR = c.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
                                AND s.ID=' . $id . '  GROUP BY cp.COURSE_PERIOD_ID';

        $QI = DBQuery($sql);
        $schedule_RET = DBGet($QI, array('TITLE' => '_makeTitle', 'PERIOD_PULLDOWN' => '_makePeriodSelect', 'COURSE_MARKING_PERIOD_ID' => '_makeMPA', 'DAYS' => '_makeDays', 'SCHEDULER_LOCK' => '_makeViewLock', 'START_DATE' => '_makeViewDate', 'END_DATE' => '_makeViewDate', 'MODIFIED_DATE' => '_makeViewDate'));
        $columns = array('TITLE' =>_course,
         'PERIOD_PULLDOWN' =>_periodTeacher,
         'ROOM' =>_room,
         'DAYS' =>_daysOfWeek,
         'COURSE_MARKING_PERIOD_ID' =>_term,
         'SCHEDULER_LOCK' =>  '<IMG SRC=assets/locked.gif border=0>',
         'START_DATE' =>_enrolled,
         'END_DATE' =>_endDateDropDate,
         'MODIFIED_NAME' =>_modifiedBy,
         'MODIFIED_DATE' =>_modifiedDate,
        );
        $options = array('search' =>false, 'count' =>false, 'save' =>false, 'sort' =>false);
        ListOutput($schedule_RET, $columns,  _course, _courses, $link, '', $options);
        
        function _makeViewDate($value, $column) {
                if ($value)
                    return ProperDate($value);
                else
                    return '<center>n/a</center>';
            }
            
        //        echo '<br /><div align="center"><input type="button" class="btn btn-primary" value="Close" onclick="window.close();"></div>';

        function _makeViewLock($value, $column) {
            global $THIS_RET;
        
            if ($value == 'Y')
                $img = 'locked';
            else
                $img = 'unlocked';
        
            return '<IMG SRC=assets/' . $img . '.gif >';
        }
?>

