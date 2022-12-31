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
#######################################################################################################################
include('../../RedirectModulesInc.php');
include('lang/language.php');

if (clean_param($_REQUEST['modfunc'], PARAM_ALPHAMOD) == 'print_all' && $_REQUEST['report']) {
    echo '<link rel="stylesheet" type="text/css" href="assets/css/export_print.css" />';
    $sql_subject = 'SELECT SUBJECT_ID,TITLE FROM  course_subjects WHERE
                                        SCHOOL_ID=' . UserSchool() . ' AND SYEAR= ' . UserSyear();
    $sql_subject_ret = DBGet(DBQuery($sql_subject));
    if (count($sql_subject_ret)) {
        foreach ($sql_subject_ret as $subject) {
            echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
            echo "<tr><td width=105>" . DrawLogo() . "</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">" . GetSchool(UserSchool()) . "<div style=\"font-size:12px;\">" . _allCourses . "</div></td><td align=right style=\"padding-top:20px;\">" . ProperDate(DBDate()) . "<br />" . _poweredBy . " openSIS</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
            echo '<table border="0" width="100%" align="center"><tr><td><font face=verdana size=-1><b>' . $subject['TITLE'] . '</b></font></td></tr><tr>';

            $sql_course = 'SELECT COURSE_ID,TITLE FROM  courses WHERE
                                        SCHOOL_ID=' . UserSchool() . ' AND SYEAR= ' . UserSyear() . ' AND SUBJECT_ID=' . $subject['SUBJECT_ID'];

            $sql_course_ret = DBGet(DBQuery($sql_course));
            foreach ($sql_course_ret as $course) {
                echo '<table border="0"><tr><td style=padding-left:40px;><font face=verdana size=-1><b>' . $course['TITLE'] . '</b></font></td></tr></table>';

                $sql_course_period = 'SELECT TITLE FROM  course_periods WHERE
                                        SCHOOL_ID=' . UserSchool() . ' AND SYEAR= ' . UserSyear() . ' AND COURSE_ID=' . $course['COURSE_ID'];

                $sql_course_period_ret = DBGet(DBQuery($sql_course_period));
                foreach ($sql_course_period_ret as $course_period) {
                    echo '<table border="0" width="100%"><tr><td style=padding-left:80px;><font face=verdana size=-1><b>' . $course_period['TITLE'] . '</b></font></td></tr></table>';
                }
            }
            echo '</tr><tr><td colspan="2" valign="top" align="right">';
            echo '</td></tr></table>';
            echo "<div style=\"page-break-before: always;\"></div>";
        }
    }
} else {
    echo '<div class="row">';
    echo '<div class="col-md-6 col-md-offset-3">';
    PopTable('header', _printAllCourses, 'class="panel panel-default"');
    echo '<div class="alert bg-success alert-styled-left">' . _reportGenerated . '</div>';
    echo "<FORM name=exp id=exp action=ForExport.php?modname=" . strip_tags(trim($_REQUEST['modname'])) . "&modfunc=print_all&_openSIS_PDF=true&report=true method=POST target=_blank>";
    echo '<div class="text-right"><INPUT type=submit class="btn btn-primary" value=\'' . _print . '\'></div>';
    echo '</form>';
    PopTable('footer');
    echo '</div>'; //.col-md-6.col-md-offset-3
    echo '</div>'; //.row
}
