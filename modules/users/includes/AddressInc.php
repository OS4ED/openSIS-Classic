<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include staff demographic info, scheduling, grade book, attendance,
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


if (!empty($_REQUEST['values']['ADDRESS']))
    foreach ($_REQUEST['values']['ADDRESS'] as $index => $data)
        $_REQUEST['values']['ADDRESS'][$index] = $data;
if (!empty($_REQUEST['values']['EMERGENCY_CONTACT']))
    foreach ($_REQUEST['values']['EMERGENCY_CONTACT'] as $index => $data)
        $_REQUEST['values']['EMERGENCY_CONTACT'][$index] = $data;

if ($_REQUEST['values'] && ($_POST['values'] || $_REQUEST['ajax'])) {


    if ($_REQUEST['values']) {
        if ($_REQUEST['address_id'] != 'new') {



            if ($_REQUEST['values']['ADDRESS']) {
                $sql = "UPDATE staff_address  SET ";

                foreach ($_REQUEST['values']['ADDRESS'] as $column => $value) {
                    if (!is_array($value))
                        $sql .= $column . "='" . singleQuoteReplace('', '', $value) . "',";
                    else {
                        $sql .= $column . "='||";
                        foreach ($value as $val) {
                            if ($val)
                                $sql .= singleQuoteReplace('', '', $val) . '||';
                        }
                        $sql .= "',";
                    }
                }
                $sql = substr($sql, 0, -1) . " WHERE STAFF_ADDRESS_ID='$_REQUEST[address_id]'";
                DBQuery($sql);
            }






            if ($_REQUEST['values']['CONTACT']) {
                $sql = "UPDATE staff_contact  SET ";

                foreach ($_REQUEST['values']['CONTACT'] as $column => $value) {
                    if (!is_array($value))
                        $sql .= $column . "='" . singleQuoteReplace('', '', $value) . "',";
                    else {
                        $sql .= $column . "='||";
                        foreach ($value as $val) {
                            if ($val)
                                $sql .= singleQuoteReplace('', '', $val) . '||';
                        }
                        $sql .= "',";
                    }
                }
                $sql = substr($sql, 0, -1) . " WHERE STAFF_ID=" . UserStaffID();
                DBQuery($sql);
            }




            if ($_REQUEST['values']['EMERGENCY_CONTACT']) {
                $sql = "UPDATE staff_emergency_contact  SET ";

                foreach ($_REQUEST['values']['EMERGENCY_CONTACT'] as $column => $value) {
                    if (!is_array($value))
                        $sql .= $column . "='" . singleQuoteReplace('', '', $value) . "',";
                    else {
                        $sql .= $column . "='||";
                        foreach ($value as $val) {
                            if ($val)
                                $sql .= singleQuoteReplace('', '', $val) . '||';
                        }
                        $sql .= "',";
                    }
                }
                $sql = substr($sql, 0, -1) . " WHERE STAFF_ID=" . UserStaffID();

                DBQuery($sql);
            }
        }
        else {



            if ($_REQUEST['values']['ADDRESS']) {

                if ($_REQUEST['r4'] == 'Y') {
                    $_REQUEST['values']['ADDRESS']['STAFF_ADDRESS1_MAIL'] = $_REQUEST['values']['ADDRESS']['STAFF_ADDRESS1_PRIMARY'];
                    $_REQUEST['values']['ADDRESS']['STAFF_ADDRESS2_MAIL'] = $_REQUEST['values']['ADDRESS']['STAFF_ADDRESS2_PRIMARY'];
                    $_REQUEST['values']['ADDRESS']['STAFF_CITY_MAIL'] = $_REQUEST['values']['ADDRESS']['STAFF_CITY_PRIMARY'];
                    $_REQUEST['values']['ADDRESS']['STAFF_STATE_MAIL'] = $_REQUEST['values']['ADDRESS']['STAFF_STATE_PRIMARY'];
                    $_REQUEST['values']['ADDRESS']['STAFF_ZIP_MAIL'] = $_REQUEST['values']['ADDRESS']['STAFF_ZIP_PRIMARY'];
                }

                $sql = "INSERT INTO staff_address ";
                $fields = 'STAFF_ID,';
                $values = "'" . UserStaffID() . "',";
                foreach ($_REQUEST['values']['ADDRESS'] as $column => $value) {
                    if ($value) {
                        $fields .= $column . ',';

                        $values .= "'" . singleQuoteReplace('', '', $value) . "',";
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                DBQuery($sql);

                $id = DBGet(DBQuery("select max(staff_address_id) as ADDRESS_ID  from staff_address"));
                $id = $id[1]['ADDRESS_ID'];
                $_REQUEST['address_id'] = $id;
            }

            if ($_REQUEST['values']['CONTACT']) {
                $sql = "INSERT INTO staff_contact ";
                $fields = 'STAFF_ID,';
                $values = "'" . UserStaffID() . "',";
                foreach ($_REQUEST['values']['CONTACT'] as $column => $value) {
                    if ($value) {
                        $fields .= $column . ',';

                        $values .= "'" . singleQuoteReplace('', '', $value) . "',";
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                DBQuery($sql);
            }

            if ($_REQUEST['values']['EMERGENCY_CONTACT']) {
                $sql = "INSERT INTO staff_emergency_contact ";
                $fields = 'STAFF_ID,';
                $values = "'" . UserStaffID() . "',";
                foreach ($_REQUEST['values']['EMERGENCY_CONTACT'] as $column => $value) {
                    if ($value) {
                        $fields .= $column . ',';

                        $values .= "'" . singleQuoteReplace('', '', $value) . "',";
                    }
                }
                $sql .= '(' . substr($fields, 0, -1) . ') values(' . substr($values, 0, -1) . ')';

                DBQuery($sql);
            }
        }
    }









    unset($_REQUEST['modfunc']);
    unset($_REQUEST['values']);
}



if (!$_REQUEST['modfunc']) {

    if ($_REQUEST['address_id'] != '' && $_REQUEST['address_id'] != 'new') {
        $this_address_RET = DBGet(DBQuery("SELECT * FROM staff_address
        WHERE STAFF_ADDRESS_ID=" . $_REQUEST['address_id'] . " AND STAFF_ID=" . UserStaffID()));
        $this_address = $this_address_RET[1];

        $this_contact_RET = DBGet(DBQuery("SELECT * FROM staff_contact
        WHERE STAFF_ID=" . UserStaffID()));
        $this_contact = $this_contact_RET[1];

        $this_emer_contact_RET = DBGet(DBQuery("SELECT * FROM staff_emergency_contact
        WHERE STAFF_ID=" . UserStaffID()));
        $this_emer_contact = $this_emer_contact_RET[1];
    }


    ############################################################################################

    $style = '';





    ############################################################################################	
    // New Address


    if (isset($_REQUEST['address_id'])) {
        echo "<INPUT type=hidden name=address_id value=$_REQUEST[address_id]>";

        if ($_REQUEST['address_id'] != '0' && $_REQUEST['address_id'] !== 'old') {
            if ($_REQUEST['address_id'] == 'new')
                $size = true;
            else
                $size = false;


            if ($_REQUEST['address_id'] != 'new' && $_REQUEST['address_id'] != '0') {
                $display_address = urlencode($this_address['STAFF_ADDRESS1_PRIMARY'] . ', ' . ($this_address['STAFF_CITY_PRIMARY'] ? ' ' . $this_address['STAFF_CITY_PRIMARY'] . ', ' : '') . $this_address['STAFF_STATE_PRIMARY'] . ($this_address['STAFF_ZIP_PRIMARY'] ? ' ' . $this_address['STAFF_ZIP_PRIMARY'] : ''));
                $link = 'http://google.com/maps?q=' . $display_address;
                echo '<div class="pull-right"><A class="btn bg-teal-400 btn-xs btn-labeled" HREF=# onclick=\'window.open("' . $link . '","","scrollbars=yes,resizable=yes,width=800,height=700");\'><b><i class="icon-location4"></i></b> '._mapIt.'</A></div>';
            }
            echo '<h5 class="text-primary">'._homeAddress.'</h5>';
                        
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_ADDRESS1_PRIMARY'], 'values[ADDRESS][STAFF_ADDRESS1_PRIMARY]', _streetAddress_1, 'class=cell_medium') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_ADDRESS2_PRIMARY'], 'values[ADDRESS][STAFF_ADDRESS2_PRIMARY]', _streetAddress_2, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            
            echo '<div class="row">';            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_CITY_PRIMARY'], 'values[ADDRESS][STAFF_CITY_PRIMARY]', _city, 'class=cell_medium') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_STATE_PRIMARY'], 'values[ADDRESS][STAFF_STATE_PRIMARY]', _state, 'class=cell_medium') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';       
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_ZIP_PRIMARY'], 'values[ADDRESS][STAFF_ZIP_PRIMARY]', _zipPostalCode, 'class=cell_medium') . '</div>';
            echo '</div>'; //.col-md-4
            echo '</div>'; //.row

            if ($_REQUEST['address_id'] == 'new') {
                $new = true;
                $this_address['RESIDENCE'] = 'Y';
                $this_address['MAILING'] = 'Y';
            }

            
            if ($_REQUEST['address_id']!='new'){
                 $st_mail=DBGet(DBQuery('SELECT staff_address1_mail FROM staff_address WHERE staff_address_id='.$_REQUEST['address_id'])); 
                 if(count($st_mail)){
                   echo '<h5 class="text-primary visible-lg-inline-block">'._mailingAddress.'</h5>';  
                 }
            }else{
                 echo '<h5 class="text-primary visible-lg-inline-block">'._mailingAddress.'</h5><div class="visible-lg-inline-block p-l-15"><label class="radio-inline p-t-0"><input type="radio" id="r4" name="r4" value="Y" onClick="hidediv();" checked>'._sameAsHomeAddress.'</label><label class="radio-inline p-t-0"><input type="radio" id="r4" name="r4" value="N" onClick="showdiv();">'._addNewAddress.'</label></div>';

            }
                
                
            if ($_REQUEST['address_id'] == 'new')
                echo '<div id="hideShow" style="display:none">';
            else
                echo '<div id="hideShow">';

            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_ADDRESS1_MAIL'], 'values[ADDRESS][STAFF_ADDRESS1_MAIL]', _streetAddress_1, '') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_ADDRESS2_MAIL'], 'values[ADDRESS][STAFF_ADDRESS2_MAIL]', _streetAddress_2, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_CITY_MAIL'], 'values[ADDRESS][STAFF_CITY_MAIL]', _city, '') . '</div>';
            echo '</div>'; //.col-md-6            
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_STATE_MAIL'], 'values[ADDRESS][STAFF_STATE_MAIL]', _state, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_address['STAFF_ZIP_MAIL'], 'values[ADDRESS][STAFF_ZIP_MAIL]', _zipPostalCode, 'class=cell_medium') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '</div>'; //#hideShow


            echo '<h5 class="text-primary">'._contactInformation.'</h5>';
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_contact['STAFF_HOME_PHONE'], 'values[CONTACT][STAFF_HOME_PHONE]', _homePhone, '') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_contact['STAFF_MOBILE_PHONE'], 'values[CONTACT][STAFF_MOBILE_PHONE]', _mobilePhone, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row            
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_contact['STAFF_WORK_PHONE'], 'values[CONTACT][STAFF_WORK_PHONE]', _officePhone, '') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_contact['STAFF_WORK_EMAIL'], 'values[CONTACT][STAFF_WORK_EMAIL]', _workEmail, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_contact['STAFF_PERSONAL_EMAIL'], 'values[CONTACT][STAFF_PERSONAL_EMAIL]', _personalEmail, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row


            ############################################################################################		

            echo '<h5 class="text-primary">'._emergencyContactInformation.'</h5>';
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_emer_contact['STAFF_EMERGENCY_FIRST_NAME'], 'values[EMERGENCY_CONTACT][STAFF_EMERGENCY_FIRST_NAME]', _firstName, '') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_emer_contact['STAFF_EMERGENCY_LAST_NAME'], 'values[EMERGENCY_CONTACT][STAFF_EMERGENCY_LAST_NAME]', _lastName, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row            
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . _makeAutoSelectInputX($this_emer_contact['STAFF_EMERGENCY_RELATIONSHIP'], 'STAFF_EMERGENCY_RELATIONSHIP', 'EMERGENCY_CONTACT', _relationshipToStaff, $relation_options) . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_emer_contact['STAFF_EMERGENCY_HOME_PHONE'], 'values[EMERGENCY_CONTACT][STAFF_EMERGENCY_HOME_PHONE]', _homePhone, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_emer_contact['STAFF_EMERGENCY_WORK_PHONE'], 'values[EMERGENCY_CONTACT][STAFF_EMERGENCY_WORK_PHONE]', _workPhone, '') . '</div>';
            echo '</div>'; //.col-md-6
            
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_emer_contact['STAFF_EMERGENCY_MOBILE_PHONE'], 'values[EMERGENCY_CONTACT][STAFF_EMERGENCY_MOBILE_PHONE]', _mobilePhone, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row
            
            echo '<div class="row">';
            echo '<div class="col-md-6">';
            echo '<div class="form-group">' . TextInput($this_emer_contact['STAFF_EMERGENCY_EMAIL'], 'values[EMERGENCY_CONTACT][STAFF_EMERGENCY_EMAIL]', _email, '') . '</div>';
            echo '</div>'; //.col-md-6
            echo '</div>'; //.row



            $_REQUEST['category_id'] = 2;
            $_REQUEST['custom'] = 'staff';
            include('modules/users/includes/OtherInfoInc.php');
            ############################################################################################			
        }
    } 
    else
        echo '';


    $separator = '<HR>';
}




function _makeAutoSelectInputX($value, $column, $table, $title, $select, $id = '', $div = true) {
    if ($column == 'CITY' || $column == 'MAIL_CITY')
        $options = 'maxlength=60';
    if ($column == 'STATE' || $column == 'MAIL_STATE')
        $options = 'size=3 maxlength=10';
    elseif ($column == 'ZIPCODE' || $column == 'MAIL_ZIPCODE')
        $options = 'maxlength=10';
    else
        $options = 'maxlength=100';

    if ($value != '---' && !empty($select))
        return SelectInput($value, "values[$table]" . ($id ? "[$id]" : '') . "[$column]", $title, $select, 'N/A', '', $div);
    else
        return TextInput($value == '---' ? array('---', '<FONT color=red>---</FONT>') : $value, "values[$table]" . ($id ? "[$id]" : '') . "[$column]", $title, $options, $div);
}

?>