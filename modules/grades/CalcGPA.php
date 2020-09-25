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
echo '<div id="calculating" style="display: none; padding-top:20px; padding-bottom:15px;"><img src="assets/missing_attn_loader.gif" /><br/><br/><br/><span style="color:#c90000;"><span style=" font-size:15px; font-weight:bold;">'._pleaseWait.'.</span><br /><span style=" font-size:12px;">'._pleaseWait.'.</span></span></div>
<div id="resp" style="font-size:14px"></div>';
$QI = DBQuery("SELECT PERIOD_ID,TITLE FROM school_periods WHERE SCHOOL_ID='" . UserSchool() . "' AND SYEAR='" . UserSyear() . "' ORDER BY SORT_ORDER ");
$RET = DBGet($QI);

$SCALE_RET = DBGet(DBQuery('SELECT * from schools where ID = \'' . UserSchool() . '\''));

DrawBC(""._gradebook." > " . ProgramTitle());
$mps = GetAllMP(GetMPTable(GetMP(UserMP(), 'TABLE')), UserMP());
$mps = explode(',', str_replace("'", '', $mps));
$table = '<TABLE><TR><TD valign=top><TABLE>
	</TR><TD align=right valign=top><font color=gray>'._calculateGpaFor.'</font></TD><TD>';

foreach ($mps as $mp) {
    if ($mp != '0')
        $table .= '<INPUT type=radio name=marking_period_id value=' . $mp . ($mp == UserMP() ? ' CHECKED' : '') . '>' . GetMP($mp) . '<BR>';
}

$table .= '</TD>
	</TR>
	<TR>
		<TD colspan = 2 align=center><font color=gray>'._gpaBasedOnAScaleOf.' ' . $SCALE_RET[1]['REPORTING_GP_SCALE'] . '</TD>
	</TR>' .
        '</TABLE></TD><TD width=350><small>'._gpaCalculationModifiesExistingRecords.'.<BR><BR>'._gpaCalculationModifiesExistingRecords.'.  </small></TD></TR></TABLE>';

if (!$_REQUEST['modfunc']) {
    echo "<FORM name=sav id=sav action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . "&modfunc=save method=POST>";
    PopTable_wo_header('header');
    echo '<CENTER><h4>'._calculateGpaAndClassRank.'</CENTER></h4><br/>';
    echo '<center>' . $table . '</center>';
    PopTable('footer');
    echo '<BR><CENTER>' . SubmitButton(_calculateGpa, '', 'class=btn_re_enroll') . '</CENTER>';
    echo "</FORM>";
}
if (clean_param($_REQUEST['modfunc'], PARAM_ALPHA) == 'save') {
    unset($_REQUEST['modfunc']);
    echo '<script type=text/javascript>calculate_gpa(\'' . strip_tags(trim($_REQUEST['marking_period_id'])) . '\');</script>';
}
?>