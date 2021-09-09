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

include 'RedirectRootInc.php';
include 'ConfigInc.php';
include 'Warehouse.php';

$id = sqlSecurityFilter($_REQUEST['id']);

if ($_REQUEST['table_name'] != '' && $_REQUEST['table_name'] == 'courses') {

    $sql = "SELECT COURSE_ID,c.TITLE, CONCAT_WS(' - ',c.short_name,c.title) AS GRADE_COURSE FROM courses c LEFT JOIN school_gradelevels sg ON c.grade_level=sg.id WHERE SUBJECT_ID='".$id."' ORDER BY c.TITLE";
    $QI = DBQuery($sql);
    $courses_RET = DBGet($QI);
    $html = 'course_modal_request||';
    $html.= '<h6>'.count($courses_RET) . ((count($courses_RET) == 1) ? ' '._courseWas.'' : ' '._coursesWere.'') . ' '._found.'.</h6>';
    if (count($courses_RET) > 0) {
        $html.='<table class="table table-bordered"><thead><tr class="alpha-grey"><th>'._course.'</th></tr></thead>';
        $html.='<tbody>';
        foreach ($courses_RET as $val) {

            $html.= '<tr><td><a href=javascript:void(0); onclick="requestPasteField(\'' . $val['TITLE'] . '\',\'' . $val['COURSE_ID'] . '\');">' . $val['TITLE'] . '</a></td></tr>';
        }
        $html.='</tbody>';
        $html.='</table>';
        $html .= '<div id="request_div" style="display:none;"></div>';
    }
}

echo $html;
?>
