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

if (User('PROFILE') == 'admin') {

    if (($_REQUEST['modfunc'] == 'search_fnc' || !$_REQUEST['modfunc']) && !$_REQUEST['search_modfunc']) {
        if ($_SESSION['staff_id']) {
            unset($_SESSION['staff_id']);
//            echo '<script language=JavaScript>parent.side.location="' . $_SESSION['Side_PHP_SELF'] . '?modcat="+parent.side.document.forms[0].modcat.value;</script>';
        }

        echo '<div>';
        PopTable('header',  _findAParent);

        echo "<FORM name=search class=\"no-margin form-horizontal\" action=Modules.php?modname=$_REQUEST[modname]&modfunc=list&next_modname=$_REQUEST[next_modname] method=POST>";
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4">'._lastName.'</label><div class="col-lg-8"><INPUT type=text placeholder="'._lastName.'" class=form-control name=last></div></div>';
        echo '</div><div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4">'._firstName.'</label><div class="col-lg-8"><INPUT type=text placeholder="'._firstName.'" class=form-control name=first></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row

        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="form-group"><label class="control-label col-lg-4">'._username.'</label><div class="col-lg-8"><INPUT type=text placeholder="'._username.'" class=form-control name=username></div></div>';
        echo '</div><div class="col-md-6">';
        $profiles = DBGet(DBQuery('SELECT * FROM user_profiles WHERE profile = \'' . 'parent' . '\''));
        $options[''] = 'N/A';
        $options['none'] = 'No Access';
        foreach ($profiles as $key => $value) {
            $options[$value['ID']] = $value['TITLE'];
        }

        $options['parent'] = 'Parent w/Custom';

        if ($extra['profile'] == 'parent')
            $options = array('3' => $options[3]);
        echo '<div class="form-group"><label class="control-label col-lg-4">'._profile.'</label><div class="col-lg-8"><SELECT class=form-control name=profile>';
        foreach ($options as $key => $val)
            echo '<OPTION value="' . $key . '">' . $val;
        echo '</SELECT></div></div>';
        echo '</div>'; //.col-md-6
        echo '</div>'; //.row
        
        echo '<div class="row">';
        echo '<div class="col-md-12">';
        if ($extra['search'])
            echo $extra['search'];

        if (User('PROFILE') == 'admin')
            echo '<label class="checkbox-inline"><INPUT type=checkbox name=_search_all_schools value=Y' . (Preferences('DEFAULT_ALL_SCHOOLS') == 'Y' ? ' CHECKED' : '') . '>'._searchAllSchools.'</label>';
        echo '<label class="checkbox-inline"><INPUT type=checkbox name=_dis_user value=Y>'._includeDisabledUser.'</label>';
        echo '</div>'; //.col-md-12
        echo '</div>'; //.row
        
        echo "<hr class=\"m-b-15\"/><div class=\"text-right\"><INPUT type=SUBMIT class=\"btn btn-primary\" value='"._submit."' onclick=\"self_disable(this);\" > &nbsp; <INPUT type=RESET class=\"btn btn-default\" value='"._reset."'></div>";

        /*         * ******************for Back to user************************** */
        echo '<input type=hidden name=sql_save_session_staf value=true />';
        /*         * ********************************************* */
        echo '</FORM>';
        // set focus to last name text box
        echo '<script type="text/javascript"><!--
			document.search.last.focus();
			--></script>';
        PopTable('footer');
        echo '</div>';
    }


    else {
        echo '<div class="panel panel-default">';
        if (!$_REQUEST['next_modname'])
            $_REQUEST['next_modname'] = 'users/User.php';

        if (!isset($_openSIS['DrawHeader']))
            DrawHeader(_pleaseSelectAParent);
        if ($_REQUEST['profile'] != 'none') {
            $staff_RET = GetStaffList($extra);
            $_SESSION['count_stf'] = count($staff_RET);
            if ($extra['profile']) {
                $options = array('admin' => 'Administrator', 'teacher' => 'Teacher', 'parent' => 'Parent', 'none' => 'No Access');
                $singular = $options[$extra['profile']];
                $plural = $singular . ($options[$extra['profile']] == 'none' ? 'es' : 's');
                $columns = array('FULL_NAME' => $singular, 'STAFF_ID' =>_staffId);
            } else {

                $singular = _parent;
                $plural = _parents;

                if ($_REQUEST['_dis_user'])
                    $columns = array('FULL_NAME' =>_parent,
                     'USERNAME' =>_username,
                     'PROFILE' =>_profile,
                     'STAFF_ID' =>_userId,
                     'Status' =>_status,
                    );
                else
                    $columns = array('FULL_NAME' =>_parent,
                     'USERNAME' =>_username,
                     'PROFILE' =>_profile,
                     'STAFF_ID' =>_userId,
                    );
            }
            if (is_array($extra['columns_before']))
                $columns = $extra['columns_before'] + $columns;
            if (is_array($extra['columns_after']))
                $columns += $extra['columns_after'];
            if (is_array($extra['link']))
                $link = $extra['link'];
            else {
                
                if($_REQUEST['modname']!='users/UserAdvancedReport.php')
                {
                $link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]";

                $link['FULL_NAME']['variables'] = array('staff_id' => 'STAFF_ID');
                }
            }
            if ($_REQUEST['_dis_user']) {
                foreach ($staff_RET as $i => $d) {

                    if ($d['IS_DISABLE'] == '' || $d['IS_DISABLE'] == NULL)
                        $staff_RET[$i]['Status'] = '<font style="color:green">Active</font>';
                    else
                        $staff_RET[$i]['Status'] = '<font style="color:red">Inactive</font>';
                }
            }
            ListOutput($staff_RET, $columns, $singular, $plural, $link, false, $extra['options']);
        }
        else {
            $staff_RET = GetStaffListNoAccess();

            $_SESSION['count_stf'] = count($staff_RET);

            $singular = _user;
            $plural = _users;

            $columns = array('FULL_NAME' =>_user,
             'PROFILE' =>_profile,
             'STAFF_ID' =>_userId,
            );

            $link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]&profile=none";

            $link['FULL_NAME']['variables'] = array('staff_id' => 'STAFF_ID');

            ListOutput($staff_RET, $columns, $singular, $plural, $link, false);
        }
        echo '</div>'; //.panel
    }
}

function makeLogin($value) {
    return ProperDate(substr($value, 0, 10)) . substr($value, 10);
}

?>