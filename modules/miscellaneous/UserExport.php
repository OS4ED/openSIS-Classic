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
$extra['search'] .= '<TR><TD align=center colspan=2><TABLE><TR><TD><DIV id=fields_div></DIV></TD></TR></TABLE></TD></TR>';
$extra['new'] = true;
$_openSIS['CustomFields'] = true;
if($_REQUEST['fields']['TITLE'] || $_REQUEST['fields']['FIRST_NAME'] || $_REQUEST['fields']['LAST_NAME'] || $_REQUEST['fields']['MIDDLE_NAME'] || $_REQUEST['fields']['LAST_YEAR_ID'] || $_REQUEST['fields']['PHONE'] || $_REQUEST['fields']['USERNAME'] || $_REQUEST['fields']['IS_DISABLE'] || $_REQUEST['fields']['EMAIL'] || $_REQUEST['fields']['LAST_LOGIN'] || $_REQUEST['fields']['PROFILE'])
{
    
    if($_REQUEST['modname']=='users/UserAdvancedReportStaff.php')
    {
    if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
    {
        $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssr.SCHOOL_ID as SCHOOLS ';
    }
    else {
         $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.PHONE,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssr.SCHOOL_ID as SCHOOLS ';
    }
    }
    else
    {
        if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))
        {
        $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssm.SCHOOL_ID as SCHOOLS ';
        }
        else {
         $extra['SELECT'] .= ',s.TITLE,s.FIRST_NAME,s.LAST_NAME,s.MIDDLE_NAME,la.USERNAME,la.LAST_LOGIN,s.EMAIL,s.PHONE,s.IS_DISABLE,s.CURRENT_SCHOOL_ID,ssm.SCHOOL_ID as SCHOOLS ';
        }
    }
         
         
         $extra['WHERE'] .=' AND la.USER_ID=s.STAFF_ID AND la.PROFILE_ID=s.PROFILE_ID ';
	
}

if(!$extra['functions'])
	$extra['functions'] = array('LAST_LOGIN'=>'ProperDate','SCHOOLS'=>'Tot_School');

if($_REQUEST['search_modfunc']=='list')
{
    
	if(!$fields_list)
	{
		$fields_list = array('FULL_NAME'=>'Last, First','FIRST_NAME'=>'First','TITLE'=>'Title','LAST_NAME'=>'Last','MIDDLE_NAME'=>'Middle','STAFF_ID'=>'Staff Id','ROLLOVER_ID'=>'Last Year Id','SCHOOLS'=>'Schools','USERNAME'=>'Username','IS_DISABLE'=>'Disable','EMAIL'=>'Email ID','PHONE'=>'Phone','LAST_LOGIN'=>'Last Login','PROFILE'=>'User Profile');
		if($extra['field_names'])
			$fields_list += $extra['field_names'];


	}

	$custom_RET = DBGet(DBQuery("SELECT TITLE,ID,TYPE FROM staff_fields ORDER BY SORT_ORDER"));

	foreach($custom_RET as $field)
	{
                
                        if(!$fields_list[$field['TITLE']])
                        {
                                $title=strtolower(trim($field['TITLE']));
                                if(strpos(trim($field['TITLE']),' ')!=0)
                                {
                                     $p1=substr(trim($field['TITLE']),0,strpos(trim($field['TITLE']),' '));
                                     $p2=substr(trim($field['TITLE']),strpos(trim($field['TITLE']),' ')+1);
                                     $title=strtolower($p1.'_'.$p2);
                                }
                                $fields_list[$title] = $field['TITLE'];
                         

                        }
	}


                if($_REQUEST['fields'])
                {
                    foreach($_REQUEST['fields'] as $field => $on)
                    {
                        if(strtoupper($field)=='PHONE')
                        {
                            $field='HOME_PHONE';
                            $columns[$field]='Phone';
                        }
                        elseif(strtoupper($field)=='SCHOOLS')
                        {
                            $field='SCHOOLS';
                            $columns[$field]='Schools';
                        }
                        else 
                            $columns[strtoupper($field)] = $fields_list[$field];
                        if(!$fields_list[$field])
                        {
                            $get_column = DBGet(DBQuery("SELECT ID,TITLE FROM staff_fields  ORDER BY SORT_ORDER"));
                            foreach($get_column as $COLUMN_NAME)
                            {
                                if('CUSTOM_'.$COLUMN_NAME['ID']==$field)
                                    $columns[strtoupper($field)] = $COLUMN_NAME['TITLE'];
                                else if(str_replace (" ", "_",strtoupper($COLUMN_NAME['TITLE']))==strtoupper($field))
                                    $columns[strtoupper($field)] = $COLUMN_NAME['TITLE'];
                            }
                            if(strpos($field,'CUSTOM')===0)
                            {
                                    $custom_id=str_replace("CUSTOM_","",$field);
                                    $custom_RET=DBGet(DBQuery("SELECT TYPE FROM staff_fields WHERE ID=$custom_id"));
                                    if($custom_RET[1]['TYPE']=='date' && !$extra['functions'][$field]){
                                        $extra['functions'][$field] = 'ProperDate';
                                    }
                                    elseif($custom_RET[1]['TYPE']=='codeds' && !$extra['functions'][$field]){
                                        $extra['functions'][$field] = 'DeCodeds';
                                    }
                            }
                        }
                }
               
                if(isset($extra['user_profile']) &&  ($extra['user_profile']=='parent'))  
                    $RET = GetStaffList($extra);
                else
                     $RET = GetUserStaffList($extra);
                    if($extra['array_function'] && function_exists($extra['array_function']))
                        $extra['array_function']($RET);
                    echo "<html><link rel='stylesheet' type='text/css' href='styles/Export.css'><body style=\" font-family:Arial; font-size:12px;\">";

                    ListOutputPrint_Report($RET,$columns,$extra['singular']?$extra['singular']:'User',$extra['plural']?$extra['plural']:'users',array(),$extra['LO_group'],$extra['LO_options']);
                    echo "</body></html>";
                }
}
else
{
	if(!$fields_list)
	{
		if(AllowUse('users/User.php&category_id=1'))
			$fields_list['General'] = array('FULL_NAME'=>'Last, First','FIRST_NAME'=>'First','TITLE'=>'Title','LAST_NAME'=>'Last','MIDDLE_NAME'=>'Middle','STAFF_ID'=>'Staff Id','ROLLOVER_ID'=>'Last Year Id','SCHOOLS'=>'Schools','USERNAME'=>'Username','IS_DISABLE'=>'Disable','EMAIL'=>'Email ID','PHONE'=>'Phone','LAST_LOGIN'=>'Last Login','PROFILE'=>'User Profile');

		if($extra['field_names'])
			$fields_list['General'] += $extra['field_names'];
	}
/*******************************************************************************/
	$categories_RET = DBGet(DBQuery('SELECT ID,TITLE FROM staff_field_categories ORDER BY SORT_ORDER'));
	$custom_RET = DBGet(DBQuery('SELECT TITLE,ID,TYPE,CATEGORY_ID FROM staff_fields ORDER BY SORT_ORDER'),array(),array('CATEGORY_ID'));
	foreach($categories_RET as $category)
	{
		if(AllowUse('users/User.php&category_id='.$category['ID']))
		{
			foreach($custom_RET[$category['ID']] as $field)
			{
				 $title=strtolower(trim($field['TITLE']));
			 	if(strpos(trim($field['TITLE']),' ')!=0)
				{
				 $p1=substr(trim($field['TITLE']),0,strpos(trim($field['TITLE']),' '));
				 $p2=substr(trim($field['TITLE']),strpos(trim($field['TITLE']),' ')+1);
				 $title=strtolower($p1.'_'.$p2);
				}
				$fields_list[$category['TITLE']]['CUSTOM_'.$field['ID']] = $field['TITLE'];
			}	
		}
	}

	
	echo '<div class="row">';
        echo '<div class="col-sm-6 col-md-9">';
	//DrawHeader("<div><a class=big_font><img src=\"themes/blue/expanded_view.png\" />Select Fields To Generate Report</a></div>",$extra['header_right']);
	echo '<h2 class="no-margin-top">Select Fields To Generate Report</h2>';

	foreach($fields_list as $category=>$fields)
	{
		
		echo '<h5>'.$category.'</h5>';
                echo '<div class="row">';
		foreach($fields as $field=>$title)
		{
			$i++;
			echo '<div class="col-sm-6 col-md-4"><div class="checkbox"><label><INPUT type=checkbox onclick="addHTML(\'<LI>'.$title.'</LI>\',\'names_div\',false);addHTML(\'<INPUT type=hidden name=fields['.$field.'] value=Y>\',\'fields_div\',false);addHTML(\'\',\'names_div_none\',true);this.disabled=true">'.$title.'</label>'.($field=='PARENTS'?'<p class="help-block">(<label>Relation:</label> <input type=text id=relation name=relation size=8 class="form-control">)</p>':'').'</label></div></div>';
			//if($i%2==0)
			//echo '</TR><TR>';
		}
                echo '</div>';
		/*if($i%2!=0)
		{
			echo '<TD></TD></TR><TR>';
			$i++;
		}*/
	}
	//PopTable('footer');
	echo '</div><div class="col-sm-6 col-md-3">';
        
	echo '<h2 class="no-margin-top">Selected Fields</h2>';
	echo '<div id="names_div_none" class="p-10 text-white bg-danger">No fields selected</div><ol id=names_div class="selected_report_list"></ol>';
	

	echo '</div>';
	echo '</div>';
}

function Tot_School($value,$column)
{	

        $school=$value;
        $sql = "SELECT ID,TITLE FROM schools WHERE ID IN($school)";
	$QI = DBQuery($sql);
	$schools_RET = DBGet($QI);
        foreach($schools_RET as $key=>$s_name)
        $Schools[]=$s_name['TITLE'];
        $Schools=implode(",",$Schools);
        return $Schools;
        
}
?>
