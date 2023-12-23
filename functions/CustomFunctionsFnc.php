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

function TextAreaInputOrg($value, $name, $title = '', $options = '', $div = true, $divwidth = '500px') {
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if (AllowEdit() && !$_REQUEST['_openSIS_PDF']) {
        $value = str_replace("'", '&#39;', str_replace('"', '&rdquo;', $value));

        if (strpos($options, 'cols') === false)
            $options .= ' cols=30';
        if (strpos($options, 'rows') === false)
            $options .= ' rows=4';
        $rows = substr($options, strpos($options, 'rows') + 5, 2) * 1;
        $cols = substr($options, strpos($options, 'cols') + 5, 2) * 1;

        if ($value == '' || $div == false)
            return "<TEXTAREA name=$name $options>$value</TEXTAREA>" . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
        else
            return "<DIV id='div$name'><div style='width:500px;' onclick='javascript:addHTML(\"<TEXTAREA id=textarea$name name=$name $options>" . par_rep("/[\n\r]/", '\u000D\u000A', str_replace("\r\n", '\u000D\u000A', str_replace("'", "&#39;", $value))) . "</TEXTAREA>" . ($title != '' ? "<BR><small>" . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . "</small>" : '') . "\",\"div$name\",true); document.getElementById(\"textarea$name\").value=unescape(document.getElementById(\"textarea$name\").value);'><TABLE class=LO_field height=100%><TR><TD>" . ((substr_count($value, "\r\n") > $rows) ? '<DIV style="overflow:auto; height:' . (15 * $rows) . 'px; width:' . ($cols * 10) . '; padding-right: 16px;">' . nl2br($value) . '</DIV>' : '<DIV style="overflow:auto; width:' . $divwidth . '; padding-right: 16px;">' . nl2br($value) . '</DIV>') . '</TD></TR></TABLE>' . ($title != '' ? '<BR><small>' . str_replace("'", '&#39;', (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '')) . '</small>' : '') . '</div></DIV>';
    } else
        return (($value != '') ? nl2br($value) : '-') . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');
}

function ShowErr($msg) {
    echo "<script type='text/javascript'>";
    echo "$('body').find('.jGrowl').attr('class', '').attr('id', '').hide();
        $.jGrowl('".$msg."', {
            position: 'top-center',
            theme: 'alert-styled-left bg-danger',
            life: 5000,
        });";
    echo "</script>";
}

function ShowErrPhp($msg) {
    echo '<div class="alert alert-danger no-border">' . $msg . '</div>';
}

function for_error() {
    $css = getCSS();
    echo "<form action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=post>";
    echo '<BR><CENTER>' . SubmitButton(_tryAgain, '', 'class="btn btn-primary"') . '</CENTER>';
    echo "</form>";
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';                
    echo '</div>';
    echo '</div>';
    // page container
    
    $get_app_details = DBGet(DBQuery('SELECT * FROM app'));
    // Footer
    echo '<div class="navbar footer">';
    echo '<div class="navbar-collapse" id="footer">';
    echo '<div class="row">';
    echo '<div class="col-md-9">';
    echo '<div class="navbar-text">';
    echo _footerText;
    echo '</div>';
    echo '</div>';
    echo '<div class="col-md-3">';
    echo '<div class="version-info">';
    echo 'Version <b>' . $get_app_details[1][VALUE] . '</b>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    // footer end
    echo '</body>';
    echo '</html>';

    exit();
}

function ExportLink($modname, $title = '', $options = '') {
    if (AllowUse($modname))
        $link = '<A HREF=ForExport.php?modname=' . $modname . $options . '>';
    if ($title)
        $link .= $title;
    if (AllowUse($modname))
        $link .= '</A>';

    return $link;
}

function getCSS() {
    $css = 'Blue';
    if (User('STAFF_ID')) {
        $sql = 'select value from program_user_config where title=\'THEME\' and user_id=' . User('STAFF_ID');
        $data = DBGet(DBQuery($sql));
        if (is_countable($data[1]) && count($data[1]))
            $css = $data[1]['VALUE'];
    }
    return $css;
}

function Prompt_Calender($title = 'Confirm', $question = '', $message = '', $pdf = '') {
    $tmp_REQUEST = $_REQUEST;
    
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        PopTable('header', $title);
         $req_mod_name = strip_tags(trim($_REQUEST['modname']));
        echo "<h4>$question</h4><FORM name=prompt_form class=\"form-horizontal no-margin\" id=prompt_form action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<hr class=\"no-margin\"/><div class=\"text-right p-t-15\"><INPUT type=submit id=\"setupCalendarBtn\" class=\"btn btn-primary\" value="._ok." onclick='formcheck_school_setup_calender(this);'> &nbsp; <INPUT type=button class=\"btn btn-white\" name=delete_cancel value="._cancel." onclick='load_link(\"Modules.php?modname=$req_mod_name\");'></div></FORM>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function Prompt_Copy_School($title = 'Confirm', $question = '', $message = '', $pdf = '') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', $title);
        echo "<h2 class=\"no-margin-top\">$question</h2><FORM class=no-margin name=prompt_form id=prompt_form action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<hr class=\"no-margin-top\"><div class=\"text-right\"><INPUT type=submit id=\"copySchoolBtn\" class=\"btn btn-primary\" value="._ok." onclick='formcheck_school_setup_copyschool(this);'>&nbsp;<INPUT type=button class=\"btn btn-default\" name=delete_cancel value="._cancel." onclick='load_link(\"Modules.php?modname=schoolsetup/Calendar.php\");'></div></FORM>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function Prompt_rollover($title = 'Confirm', $question = '', $message = '', $pdf = '') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {

        echo '<FORM class="form-horizontal" name=roll_over id=roll_over action=' . $PHP_tmp_SELF . '&delete_ok=1 METHOD=POST>';
        PopTable('header', $title);

        echo '<h4 class="text-center">' . $question . '</h4>';
        echo '<p class="text-center"><span class="text-danger"><i class="icon-alert"></i> Caution : </span> '._rolloverIsAnIrreversibleProcessIfYouAreSureYouWantToProceedTypeInThe.' <BR>'._effectiveRollOverDateBelowYouCanUseTheNextSchoolYearSAttendanceStartDate.'.</p>';
        echo '<hr/>';
        echo '<div class="row">';
        echo '<div class="col-md-6">';

        echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._studentEnrollmentDate.'</label>';
        echo '<div class="col-md-8">' . DateInputAY('', 'roll_start_date', '1') . '</div>'; //.col-md-8
        echo '</div>'; //.form-group
        echo '<input type=hidden id=custom_date name=custom_date value=Y>';

        echo '</div>'; //.col-md-4
        echo '</div>'; //.row

        echo '<input type=hidden id=check_click value=1>';

        echo '<div class="row">';
        echo '<div class="col-md-12"><h5 class="text-primary">'._enterNextSchoolYearSBeginAndEndDates.'</h5></div>';

        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._schoolBeginDate.'</label>';
        echo '<div class="col-md-8">' . DateInputAY('', 'roll_school_start_date', '2') . '</div>'; //.col-md-8
        echo '</div>'; //.form-group
        echo '</div>'; //.col-md-4

        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="col-md-4 control-label text-right">'._schoolEndDate.'</label>';
        echo '<div class="col-md-8">' . DateInputAY('', 'roll_school_end_date', '3') . '</div>'; //.col-md-8
        echo '</div>'; //.form-group
        echo '</div>'; //.col-md-4

        echo '</div>'; //.row



        $prev_st_d = DBGet(DBQuery('SELECT END_DATE FROM school_years WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' '));
        echo '<input type=hidden id=prev_start_date value=' . $prev_st_d[1]['END_DATE'] . ' >';
        $check_ss = DBGet(DBQuery('SELECT * FROM school_semesters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' ORDER BY MARKING_PERIOD_ID'));
        if (count($check_ss) > 0) {
            $i = 4;
            $j = 4;
            $t = 0;
            $q = 0;
            $p = 0;
            $sem = 0;
            $qrtr = 0;
            $prog = 0;
            $counter1 = 4;
            foreach ($check_ss as $ss_i => $ss_d) {
                $sem++;
                echo '<div class="row">';
                echo '<div class="col-md-6">';

                echo '<div class="form-group">';
                echo '<label class="col-md-4 control-label text-right">' . $ss_d['TITLE'] . ' '._beginDate.'</label>';
                echo '<div class="col-md-8">' . DateInputAY('', 'sem_start_' . $sem, $counter1);
                echo '<input type=hidden id=name_' . $j . ' value="' . $ss_d['TITLE'] . ' '._beginDate.'" ></div>';
                echo '</div>'; //.form-group
                $j++;

                echo '</div><div class="col-md-6">';

                echo '<div class="form-group">';
                $counter1 = $counter1 + 1;
                echo '<label class="col-md-4 control-label text-right">' . $ss_d['TITLE'] . ' '._endDate.'</label><div class="col-md-8">' . DateInputAY('', 'sem_end_' . $sem, $counter1);
                echo '<input type=hidden id=name_' . $j . ' value="' . $ss_d['TITLE'] . ' '._endDate.'" ></div>';
                echo '</div>'; //.form-group

                echo '</div>'; //.col-md-4
                echo '</div>'; //.row


                $check_sq = DBGet(DBQuery('SELECT * FROM school_quarters WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND SEMESTER_ID=\'' . $ss_d['MARKING_PERIOD_ID'] . '\' '));
                if (count($check_sq) > 0) {
                    $q = $j + 1;
                    $q_val = '';
                    $p_val = '';

                    foreach ($check_sq as $sq_i => $sq_d) {
                        $qrtr++;
                        $counter1 = $counter1 + 1;
                        echo '<div class="row">';
                        echo '<div class="col-md-6">';

                        echo '<div class="form-group">';
                        echo '<label class="col-md-4 control-label text-right">' . $sq_d['TITLE'] . ' '._beginDate.'</label>';
                        echo '<div class="col-md-8">' . DateInputAY('', 'qrtr_start_' . $qrtr, $counter1);
                        echo '<input type=hidden id=name_' . $q . ' value="' . $sq_d['TITLE'] . ' '._beginDate.'" ></div>';
                        echo '</div>'; //.form-group

                        $q_val.=$q . '`';
                        $q++;
                        $q_val.=$q . '-';
                        $counter1 = $counter1 + 1;

                        echo '</div><div class="col-md-6">';

                        echo '<div class="form-group">';
                        echo '<label class="col-md-4 control-label text-right">' . $sq_d['TITLE'] . ' '._endDate.'</label><div class="col-md-8">' . DateInputAY('', 'qrtr_end_' . $qrtr, $counter1);
                        echo '<input type=hidden id=name_' . $q . ' value="' . $sq_d['TITLE'] . ' '._endDate.'" ></div>';
                        echo '</div>'; //.form-group

                        echo '</div>'; //.col-md-4
                        echo '</div>'; //.row

                        $check_sp = DBGet(DBQuery('SELECT * FROM school_progress_periods WHERE SYEAR=\'' . UserSyear() . '\' AND SCHOOL_ID=\'' . UserSchool() . '\' AND QUARTER_ID=\'' . $sq_d['MARKING_PERIOD_ID'] . '\'   '));
                        if (count($check_sp) > 0) {

                            $p = $q + 1;
                            $max = count($check_sp);

                            foreach ($check_sp as $sp_i => $sp_d) {
                                $prog++;
                                $counter1 = $counter1 + 1;
                                echo '<div class="row">';
                                echo '<div class="col-md-6">';

                                echo '<div class="form-group">';
                                echo '<label class="col-md-4 control-label text-right">' . $sp_d['TITLE'] . ' '._beginDate.'</label>';

                                echo '<div class="col-md-8">' . DateInputAY('', 'prog_start_' . $prog, $counter1);
                                echo '<input type=hidden id=name_' . $p . ' value="' . $sp_d['TITLE'] . ' '._beginDate.'" ></div>';
                                echo '</div>'; //.form-group
                                $p_val.=$p . '`';
                                $p++;
                                if ($sp_i != $max) {
                                    $p_val.=$p . '^';
                                } else
                                    $p_val.=$p . '-';
                                $counter1 = $counter1 + 1;
                                echo '</div><div class="col-md-6">';

                                echo '<div class="form-group">';
                                echo '<label class="col-md-4 control-label text-right">' . $sp_d['TITLE'] . ' '._endDate.'</label><div class="col-md-8">' . DateInputAY('', 'prog_end_' . $prog, $counter1);
                                echo '<input type=hidden id=name_' . $p . ' value="' . $sp_d['TITLE'] . ' '._endDate.'" ></div>';
                                echo '</div>'; //.form-group

                                echo '</div>'; //.col-md-4
                                echo '</div>'; //.row
                                $p++;
                                $counter3++;
                            }
                        }
                        if ($p != 0)
                            $q = $p;
                        else
                            $q++;
                    }
                }
                $t++;
                echo '<input type=hidden id=round_' . $t . ' value=' . $j . '>';
                $q_val = substr($q_val, 0, -1);
                echo '<input type=hidden id=quarter_' . $t . ' value=' . $q_val . '>';
                $p_val = substr($p_val, 0, -1);
                echo '<input type=hidden id=progress_' . $t . ' value=' . $p_val . '>';
                echo '<hr>';
                if ($q != 0)
                    $j = $q;
                else
                    $j++;
                echo '<input type=hidden id=roll_' . $t . ' value=' . $j . '>';

                $counter1++;
            }
            echo '<input type=hidden name=tot_round id=tot_round value=' . $t . '>';
            echo '<input type=hidden name=total_sem value=' . $sem . '>';
            echo '<input type=hidden name=total_qrt value=' . $qrtr . '>';
            echo '<input type=hidden name=total_prg value=' . $prog . '>';
        }

        echo '<p class="text-danger"><i class="fa fa-info-circle"></i> '._theFollowingItemsWillBeRolledOverToTheNextSchoolYearUncheckTheItemSYouDoNotWantToBeRolledOverSomeItemsAreMandatoryAndCannotBeUnchecked.'</p>';
        
        echo $message;

        $btn = "<INPUT type=submit class='btn btn-danger' value="._rollover." onclick=\"return formcheck_rollover();\"> &nbsp; <INPUT type=button class='btn btn-default' name=delete_cancel value="._cancel." onclick='load_link(\"Modules.php?modname=tools/LogDetails.php\");'>";
        PopTable('footer', $btn);
        echo '</FORM>';
        return false;
    } else
        return true;
}

function Prompt_rollover_back($title = _rollover, $question = '', $pdf = '') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', $title);

        echo "<CENTER><h4>$question</h4><FORM name=roll_over id=roll_over action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST><BR>&nbsp;<INPUT type=submit class='btn btn-primary' name=delete_cancel value="._ok." onclick='load_link(\"Modules.php?modname=tools/LogDetails.php\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function Prompt_Runschedule($title = 'Confirm', $question = '', $message = '', $pdf = '') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', $title);
        echo "<CENTER><h4>$question</h4><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<BR><BR><INPUT type=submit class='btn btn-primary' value="._ok.">&nbsp;<INPUT type=button class='btn btn-primary' name=delete_cancel value="._ok." onclick='load_link(\"Modules.php?modname=scheduling/Schedule.php\");'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

function PrepareDateSchedule($date = '', $title = '', $allow_na = true, $options = '') {
    global $_openSIS;
    static $counter = 0;
    if ($options == '')
        $options = array();
    if (!$options['Y'] && !$options['M'] && !$options['D'] && !$options['C'])
        $options += array('Y' =>true, 'M' =>true, 'D' =>true, 'C' =>true);

    if ($options['short'] == true)
        $extraM = "style='width:60;' ";
    if ($options['submit'] == true) {
        $tmp_REQUEST['M'] = $tmp_REQUEST['D'] = $tmp_REQUEST['Y'] = $_REQUEST;
        unset($tmp_REQUEST['M']['month' . $title]);
        unset($tmp_REQUEST['D']['day' . $title]);
        unset($tmp_REQUEST['Y']['year' . $title]);
        $extraM .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['M']) . "&amp;month$title=\"+this.form.month$title.value;'";
        $extraD .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['D']) . "&amp;day$title=\"+this.form.day$title.value;'";
        $extraY .= "onchange='document.location.href=\"" . PreparePHP_SELF($tmp_REQUEST['Y']) . "&amp;year$title=\"+this.form.year$title.value;'";
    }

    if ($options['C'])
        $_openSIS['PrepareDate'] ++;

    if ($options['C']) {

		$return .= DateInputAY($date, $title, $counter);
        //$return .= DateInputAY($date, $title, $counter);
        //$return .= DateInputAY($date!="" ? date("d-M-Y", strtotime($date)) : "", $title, $counter);
        $counter++;
    }

    if ($_REQUEST['_openSIS_PDF'])
        $return = ProperDateAY($date);
    return $return;
}

#############################################################################################

function PromptCourseWarning($title = 'Confirm', $question = '', $message = '', $pdf = '') {
    $tmp_REQUEST = $_REQUEST;
    unset($tmp_REQUEST['delete_ok']);
    if ($pdf == true)
        $tmp_REQUEST['_openSIS_PDF'] = true;

    $PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

    if (!$_REQUEST['delete_ok'] && !$_REQUEST['delete_cancel']) {
        echo '<BR>';
        PopTable('header', $title);
        echo "<CENTER><h4>$question</h4><FORM action=$PHP_tmp_SELF&delete_ok=1 METHOD=POST>$message<BR><BR><INPUT type=button class='btn btn-primary' name=delete_cancel value="._cancel." onclick='javascript:history.go(-1);'></FORM></CENTER>";
        PopTable('footer');
        return false;
    } else
        return true;
}

# ---------------------- Solution for screen error in Group scheduling start ---------------------------------------- #

function for_error_sch() {
    $css = getCSS();
    echo "<br><br><form action=Modules.php?modname=" . strip_tags(trim($_REQUEST[modname])) . " method=post>";
    echo '<BR><CENTER>' . SubmitButton(_tryAgain, '', 'class="btn btn-primary"') . '</CENTER>';
    echo "</form>";
    echo "</div>";

    echo "</td>
                                        </tr>
                                      </table></td>
                                  </tr>
                                </table></td>
                            </tr>
                          </table></td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
          </tr>

        </table></td>
    </tr>
  </table>
</center>
</body>
</html>";

    exit();
}

# ------------------------------ Solution for screen error in Group scheduling end------------------------------------- #
################################### Select input with Disable Onlcik edit feature ##############

function SelectInput_Disonclick($value, $name, $title, $options, $allow_na = 'N/A', $extra = '', $div = true) {
    if(empty($title)) $title = '';
    if (Preferences('HIDDEN') != 'Y')
        $div = false;

    if ($value != '' && !$options[$value])
        $options[$value] = array($value, '<FONT color=red>' . $value . '</FONT>');

    $return = (((is_array($options[$value]) ? $options[$value][1] : $options[$value]) != '') ? (is_array($options[$value]) ? $options[$value][1] : $options[$value]) : ($allow_na !== false ? ($allow_na ? $allow_na : '-') : '-')) . ($title != '' ? '<BR><small>' . (strpos(strtolower($title), '<font ') === false ? '<FONT color=' . Preferences('TITLES') . '>' : '') . $title . (strpos(strtolower($title), '<font ') === false ? '</FONT>' : '') . '</small>' : '');

    return $return;
}

###################################################################################################
###########################################################################

function GetStuListAttn(& $extra) {
    global $contacts_RET, $view_other_RET, $_openSIS;

    if ((!$extra['SELECT_ONLY'] || strpos($extra['SELECT_ONLY'], 'GRADE_ID') !== false) && !$extra['functions']['GRADE_ID'])
        $functions = array('GRADE_ID' => 'GetGrade');
    else
        $functions = array();

    if ($extra['functions'])
        $functions += $extra['functions'];

    if (!$extra['DATE']) {
        $queryMP = UserMP();
        $extra['DATE'] = DBDate();
    } else {

        $queryMP = UserMP();
    }
    if ($_REQUEST['expanded_view'] == 'true') {
        if (!$extra['columns_after'])
            $extra['columns_after'] = array();
#############################################################################################
//Commented as it crashing for Linux due to  Blank Database tables
#############################################################################################
        $view_address_RET = DBGet(DBQuery('SELECT VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE=\'ADDRESS\' AND USER_ID=\'' . User('STAFF_ID') . '\''));
        $view_address_RET = $view_address_RET[1]['VALUE'];
        $view_other_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE PROGRAM=\'StudentFieldsView\' AND TITLE IN (\'CONTACT_INFO\',\'HOME_PHONE\',\'GUARDIANS\',\'ALL_CONTACTS\') AND USER_ID=\'' . User('STAFF_ID') . '\''), array(), array('TITLE'));

        if (!count($view_fields_RET) && !isset($view_address_RET) && !isset($view_other_RET['CONTACT_INFO'])) {
            $extra['columns_after'] = array('CONTACT_INFO' =>  '<IMG SRC=assets/down_phone_button.gif border=0>',
             'gender' =>_gender,
             'ethnicity' =>_ethnicity,
             'ADDRESS' =>_mailingAddress,
             'CITY' =>_city,
             'STATE' =>_state,
             'ZIPCODE' =>_zipcode,
            ) + $extra['columns_after'];

            $select = ',s.STUDENT_ID AS CONTACT_INFO,s.GENDER,s.ETHNICITY_ID,a.STREET_ADDRESS_1 as ADDRESS,a.CITY,a.STATE,a.ZIPCODE';
            $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (ssm.STUDENT_ID=a.STUDENT_ID AND a.TYPE=\'Mail\')  ' . $extra['FROM'];

            $functions['CONTACT_INFO'] = 'makeContactInfo';
            // if gender is converted to codeds type
            //$functions['CUSTOM_200000000'] = 'DeCodeds';
            $extra['singular'] = 'Student Address';
            $extra['plural'] = 'Student Addresses';

            $extra2['NoSearchTerms'] = true;


            $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP as STUDENT_RELATION,p.TITLE,s.PHONE,a.ID AS ADDRESS_ID ';
            $extra2['FROM'] .= ',student_address a LEFT OUTER JOIN students_join_people sjp ON (a.STUDENT_ID=sjp.STUDENT_ID AND (p.CUSTODY=\'Y\' OR sjp.IS_EMERGENCY=\'Y\')) LEFT OUTER JOIN people p ON (p.STAFF_ID=sjp.PERSON_ID) ';
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
        }
        else {
            if ($view_other_RET['CONTACT_INFO'][1]['VALUE'] == 'Y' && !$_REQUEST['_openSIS_PDF']) {
                $select .= ',NULL AS CONTACT_INFO ';
                $extra['columns_after']['CONTACT_INFO'] = '<IMG SRC=assets/down_phone_button.gif border=0>';
                $functions['CONTACT_INFO'] = 'makeContactInfo';

                $extra2 = $extra;
                $extra2['NoSearchTerms'] = true;
                $extra2['SELECT'] = '';

                $extra2['SELECT_ONLY'] = 'ssm.STUDENT_ID,p.STAFF_ID AS PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.RELATIONSHIP AS STUDENT_RELATION,p.TITLE,s.PHONE,a.ID AS ADDRESS_ID,COALESCE(p.CUSTODY,\'N\') ';
                $extra2['FROM'] .= ',student_address a LEFT OUTER JOIN students_join_people sjp ON (a.STUDENT_ID=sjp.STUDENT_ID AND (p.CUSTODY=\'Y\' OR sjp.IS_EMERGENCY=\'Y\')) LEFT OUTER JOIN people p ON (p.STAFF_ID=sjp.PERSON_ID)  ';
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
                $extra['columns_after']['CUSTOM_' . $field['ID']] = $field['TITLE'];
                if ($field['TYPE'] == 'date')
                    $functions['CUSTOM_' . $field['ID']] = 'ProperDate';
                elseif ($field['TYPE'] == 'numeric')
                    $functions['CUSTOM_' . $field['ID']] = 'removeDot00';
                elseif ($field['TYPE'] == 'codeds')
                    $functions['CUSTOM_' . $field['ID']] = 'DeCodeds';
                $select .= ',s.CUSTOM_' . $field['ID'];
            }
            if ($view_address_RET) {
                if ($view_address_RET == 'RESIDENCE')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (ssm.STUDENT_ID=a.STUDENT_ID AND a.TYPE=\'Home Address\')  ' . $extra['FROM'];
                elseif ($view_address_RET == 'MAILING')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (ssm.STUDENT_ID=a.STUDENT_ID AND a.TYPE=\'Mail\') ' . $extra['FROM'];
                elseif ($view_address_RET == 'BUS_PICKUP')
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (a.STUDENT_ID=a.STUDENT_ID AND a.BUS_PICKUP=\'Y\') ' . $extra['FROM'];
                else
                    $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (a.STUDENT_ID=a.STUDENT_ID AND a.BUS_DROPOFF=\'Y\') ' . $extra['FROM'];


                $extra['columns_after'] += array('ADDRESS' => ucwords(strtolower(str_replace('_', ' ', $view_address_RET))) . ' Address', 'CITY' => 'City', 'STATE' => 'State', 'ZIPCODE' => 'Zipcode');

                $select .= ',a.ID AS ADDRESS_ID,a.STREET_ADDRESS_1 as ADDRESS,a.CITY,a.STATE,a.ZIPCODE,s.PHONE,ssm.STUDENT_ID AS PARENTS';
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
            }
            elseif ($_REQUEST['addr'] || $extra['addr']) {
                $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (ssm.STUDENT_ID=a.STUDENT_ID) ' . $extra['FROM'];
                $distinct = 'DISTINCT ';
            }
        }
        $extra['SELECT'] .= $select;
    } elseif ($_REQUEST['addr'] || $extra['addr']) {
        $extra['FROM'] = ' LEFT OUTER JOIN student_address a ON (ssm.STUDENT_ID=a.STUDENT_ID) ' . $extra['FROM'];
        $distinct = 'DISTINCT ';
    }

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
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,ssm.SCHOOL_ID AS LIST_SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];
                if ($_REQUEST['include_inactive'] == 'Y')
                    $sql .= ',' . db_case(array('(ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE ';
            }

            $sql .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID ';
            if ($_REQUEST['include_inactive'] == 'Y')
                $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR<=\'' . UserSyear() . '\' ORDER BY START_DATE DESC LIMIT 1)';
            else
                $sql .= ' AND ssm.SYEAR=\'' . UserSyear() . '\' AND (ssm.START_DATE IS NOT NULL AND (\'' . date('Y-m-d', strtotime($extra['DATE'])) . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL)) ';

            if (UserSchool() && $_REQUEST['_search_all_schools'] != 'Y')
                $sql .= ' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\'';
            else {
//				
                $sql .= ' AND ssm.SCHOOL_ID IN (' . GetUserSchools(UserID(), true) . ') ';
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
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];
                if ($_REQUEST['include_inactive'] == 'Y') {
                    $sql .= ',' . db_case(array('(ssm.START_DATE IS NOT NULL AND  (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE';
                    $sql .= ',' . db_case(array('(\'' . $extra['DATE'] . '\'>=ss.START_DATE AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))', 'true', "'<FONT color=green>Active</FONT>'", "'<FONT color=red>Inactive</FONT>'")) . ' AS ACTIVE_SCHEDULE';
                }
            }

            $sql .= ' FROM students s,course_periods cp,schedule ss,student_enrollment ssm,course_period_var cpv ' . $extra['FROM'] . ' WHERE ssm.STUDENT_ID=s.STUDENT_ID AND cpv.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cpv.ID="' . $extra['ID'] . '" AND ssm.STUDENT_ID=ss.STUDENT_ID AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SYEAR=cp.SYEAR AND ssm.SYEAR=ss.SYEAR AND ' . db_case(array(User('STAFF_ID'), 'cp.teacher_id', ' cp.teacher_id=' . User('STAFF_ID'), 'cp.secondary_teacher_id', ' cp.secondary_teacher_id=' . User('STAFF_ID'), 'cp.course_period_id IN(SELECT course_period_id from teacher_reassignment tra WHERE cp.course_period_id=tra.course_period_id AND tra.pre_teacher_id=' . User('STAFF_ID') . ')')) . ' AND cp.COURSE_PERIOD_ID=\'' . (isset($_REQUEST['cp_id_miss_attn']) ? $_REQUEST['cp_id_miss_attn'] : UserCoursePeriod()) . '\' AND cp.COURSE_ID=ss.COURSE_ID AND cp.COURSE_PERIOD_ID=ss.COURSE_PERIOD_ID';
            if ($extra['cpvdate'] != '')
                $sql .= $extra['cpvdate'];
            if ($_REQUEST['include_inactive'] == 'Y') {
                $sql .= ' AND ssm.ID=(SELECT ID FROM student_enrollment WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR ORDER BY START_DATE DESC LIMIT 1)';
                $sql .= ' AND ss.START_DATE=(SELECT START_DATE FROM schedule WHERE STUDENT_ID=ssm.STUDENT_ID AND SYEAR=ssm.SYEAR AND MARKING_PERIOD_ID IN (' . GetAllMP('', $queryMP) . ') AND COURSE_ID=cp.COURSE_ID AND COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID ORDER BY START_DATE DESC LIMIT 1)';
            } else {
                $sql .= ' AND (ssm.START_DATE IS NOT NULL  AND \'' . $extra['DATE'] . '\'>=ssm.START_DATE AND (\'' . $extra['DATE'] . '\'<=ssm.END_DATE OR ssm.END_DATE IS NULL))';
                $sql .= ' AND (\'' . $extra['DATE'] . '\'>=ss.START_DATE AND (\'' . $extra['DATE'] . '\'<=ss.END_DATE OR ss.END_DATE IS NULL))';
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
                $sql .= 's.LAST_NAME,s.FIRST_NAME,s.MIDDLE_NAME,s.STUDENT_ID,ssm.SCHOOL_ID,ssm.GRADE_ID ' . $extra['SELECT'];
            }
            $sql .= ' FROM students s,student_enrollment ssm ' . $extra['FROM'] . '
					WHERE ssm.STUDENT_ID=s.STUDENT_ID AND ssm.SYEAR=\'' . UserSyear() . '\' AND ssm.SCHOOL_ID=\'' . UserSchool() . '\' AND (\'' . DBDate() . '\' BETWEEN ssm.START_DATE AND ssm.END_DATE OR (ssm.END_DATE IS NULL AND \'' . DBDate() . '\'>ssm.START_DATE)) AND ssm.STUDENT_ID' . ($extra['ASSOCIATED'] ? ' IN (SELECT STUDENT_ID FROM students_join_users WHERE STAFF_ID=\'' . $extra['ASSOCIATED'] . '\')' : '=\'' . UserStudentID() . '\'');
            break;
        default:
            exit('Error');
    }

    $sql = appendSQL($sql, $extra);

    $sql .= $extra['WHERE'] . ' ';
    $sql .= CustomFields('where');

    if ($extra['GROUP'])
        $sql .= ' GROUP BY ' . $extra['GROUP'];

    if (!$extra['ORDER_BY'] && !$extra['SELECT_ONLY']) {
        if (Preferences('SORT') == 'Grade')
            $sql .= ' ORDER BY (SELECT SORT_ORDER FROM school_gradelevels WHERE ID=ssm.GRADE_ID),FULL_NAME';
        else
            $sql .= ' ORDER BY FULL_NAME';
        $sql .= $extra['ORDER'];
    }
    elseif ($extra['ORDER_BY'])
        $sql .= ' ORDER BY ' . $extra['ORDER_BY'];

    if ($extra['DEBUG'] === true)
        echo '<!--' . $sql . '-->';

    return DBGet(DBQuery($sql), $functions, $extra['group']);
}

###########################################################################
########################validation functions#######################################

function scheduleAssociation($cp_id) {
    // $asso = DBGet(DBQuery('SELECT COURSE_PERIOD_ID FROM schedule WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\'  LIMIT 0,1'));

    // if ($asso[1]['COURSE_PERIOD_ID'] != '' )
    //     return true;
    # Function used to return true if any schedule record was present irrespective of dropped status.
    # Modified: Function will only return true if any *ACTIVE* scheduling record is present i.e. the difference between the total number of students scheduled and the total number of students dropped in a Course Period is greater than zero (0).

    $tot_ScheduleStu = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS tot_sche FROM schedule WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\''));
    $tot_droppedStu = DBGet(DBQuery('SELECT COUNT(STUDENT_ID) AS tot_drop FROM schedule WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\' AND END_DATE < CURRENT_DATE() '));

    if ($tot_ScheduleStu[1]['TOT_SCHE'] - $tot_droppedStu[1]['TOT_DROP'] > 0)
        return true;
}

function attendanceAssociation($cp_id) {
    $asso = DBGet(DBQuery('SELECT COURSE_PERIOD_ID FROM attendance_period WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\' LIMIT 0,1'));

    if ($asso[1]['COURSE_PERIOD_ID'] != '')
        return true;
}

function gradeAssociation($cp_id) {
    $asso = DBGet(DBQuery('SELECT COURSE_PERIOD_ID FROM student_report_card_grades WHERE COURSE_PERIOD_ID=\'' . $cp_id . '\' LIMIT 0,1'));
    if ($asso[1]['COURSE_PERIOD_ID'] != '')
        return true;
}

###########################################################################

function singleQuoteReplace($param1, $param2, $param3) {
    if(empty($param1))  $param1 = false;
    if(empty($param2))  $param2 = false;
    return str_replace("'", "''", str_replace("\'", "'", $param3));
}

function isDateInMarkingPeriodWorkingDates($marking_period, $date){
    $markingPeriodHasDate = DBGet(DBQuery("SELECT * FROM `marking_periods` WHERE `marking_period_id` = '$marking_period' AND ('$date' BETWEEN `start_date` AND `end_date`)"));

    if(count($markingPeriodHasDate) === 0) return false;
    
    $childMarkingPeriods = DBGet(DBQuery("SELECT * FROM marking_periods WHERE parent_id = '$marking_period';"));

    if(count($childMarkingPeriods) === 0) return true;

    foreach($childMarkingPeriods as $id => $childMarkingPeriod){
        if(isDateInMarkingPeriodWorkingDates($childMarkingPeriod['MARKING_PERIOD_ID'], $date)) return true;
    }
    
    return false;
}
?>
