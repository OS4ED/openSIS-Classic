<?php
include '../Data.php';
include 'function/DbGetFnc.php';
include 'function/ParamLib.php';
//include 'function/Current.php';
include 'function/app_functions.php';
include 'function/function.php';
include 'function/PercentFnc.php';
//include '../functions/UserFnc.php';

header('Content-Type: application/json');
$type = $_REQUEST['type'];
$teacher_id = $_REQUEST['staff_id'];
$cpv_id = $_REQUEST['cpv_id'];
$mp_id = $_REQUEST['mp_id'];
$_SESSION['UserSchool'] = $_REQUEST['school_id'];
$_SESSION['UserSyear'] = $_REQUEST['syear'];
$auth_data = check_auth();
if(count($auth_data)>0)
{
    if($auth_data['user_id']==$teacher_id && $auth_data['user_profile']=='teacher')
    {
$cp_RET = array();
if($cpv_id!='')
{
$cp_sql = 'SELECT cp.COURSE_ID,cp.COURSE_PERIOD_ID FROM course_period_var cpv LEFT JOIN course_periods cp ON cp.COURSE_PERIOD_ID = cpv.COURSE_PERIOD_ID WHERE cpv.ID ='.$cpv_id;
$cp_RET = DBGet(DBQuery($cp_sql));
}
if(count($cp_RET)>0)
{
    $course_id = $_SESSION['UserCourse'] = $cp_RET[1]['COURSE_ID'];
    $course_period_id = $cp_RET[1]['COURSE_PERIOD_ID'];
}
else 
{
  $course_id = '';
  $course_period_id = '';
}
$_SESSION['STAFF_ID'] = $teacher_id;
$_SESSION['UserMP'] = $mp_id;
$table = 'gradebook_assignments';

$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$teacher_id.'\' AND PROGRAM=\'Gradebook\''));

if(count($config_RET))
{
    foreach($config_RET as $value)
    {
        $programconfig[$value['TITLE']] = $value['VALUE'];
    }
}
else
    $programconfig = true;

if($course_period_id!='')
{
    if($type=='view')
    {
        $sql = 'SELECT ASSIGNMENT_ID,TITLE,ASSIGNED_DATE,DUE_DATE,POINTS FROM gradebook_assignments WHERE (COURSE_ID=\''.$course_id.'\' OR COURSE_PERIOD_ID=\''.$course_period_id.'\') AND ASSIGNMENT_TYPE_ID=\''.$_REQUEST['assignment_type_id'].'\' AND MARKING_PERIOD_ID=\''.(GetCpDetWs($course_period_id,'MARKING_PERIOD_ID')!=''?$mp_id:GetMPIdWs('FY')).'\' ORDER BY '.PreferencesWs('ASSIGNMENT_SORTING','Gradebook').' DESC'; 
        $QI = DBQuery($sql);
        $assn_RET = DBGet($QI);
        $asgnmnt = array();
         foreach($assn_RET as $assn)
             $asgnmnt[]=$assn;
         if(count($asgnmnt)>0)
         {
        $data['success'] = 1;
        $data['err_msg'] = 'nil';
        $data['assignments'] = $asgnmnt;
        }
         else 
         {
             $data['success'] = 0;
            $data['err_msg'] = 'No data found';
            $data['assignments'] = $asgnmnt;
         }
    }
    if($type=='view_selected')
    {
        $sql = 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS,ga.DESCRIPTION,ga.COURSE_ID,ga.COURSE_PERIOD_ID,gat.TITLE AS ASSIGNMENT_TYPE_TITLE FROM gradebook_assignments ga LEFT JOIN gradebook_assignment_types gat ON gat.assignment_type_id = ga.ASSIGNMENT_TYPE_ID WHERE ga.ASSIGNMENT_ID = '.$_REQUEST['assignment_id']; 
        $QI = DBQuery($sql);
        $assn_RET = DBGet($QI);
        $asgnmnt = array();
         foreach($assn_RET as $assn)
             $asgnmnt=$assn;
         if(isset($asgnmnt['COURSE_ID']) && $asgnmnt['COURSE_ID']!='NULL' && $asgnmnt['COURSE_ID']!='')
         {
             $asgnmnt['APPLY_TO_ALL']='Y';
         }
         elseif(isset($asgnmnt['COURSE_PERIOD_ID']) && $asgnmnt['COURSE_PERIOD_ID']!='NULL' && $asgnmnt['COURSE_PERIOD_ID']!='')
         {
             $asgnmnt['APPLY_TO_ALL']='N';
         }
         if(isset($asgnmnt['DESCRIPTION']) && $asgnmnt['DESCRIPTION']!='NULL')
         {
             $asgnmnt['DESCRIPTION']=  htmlspecialchars_decode($asgnmnt['DESCRIPTION']);
         }
         if(isset($asgnmnt['DESCRIPTION']))
         {
         $asgnmnt['DESCRIPTION']=html_entity_decode(html_entity_decode($asgnmnt['DESCRIPTION']));
         $asgnmnt['DESCRIPTION']=strip_tags($asgnmnt['DESCRIPTION']);
         }
         $data = $asgnmnt;
         if(count($asgnmnt)>0)
         {
        $data['success'] = 1;
        $data['err_msg'] = 'nil';
         }
         else 
         {
             $data['success'] = 0;
            $data['err_msg'] = 'No data found';
         }
//        $data['assignment'] = $asgnmnt;
    }
    elseif($type=='add_view' || $type=='edit_view')
    {
        $sql = ' SELECT ASSIGNMENT_TYPE_ID,TITLE 
                     FROM (
                        ( select gat.ASSIGNMENT_TYPE_ID,gat.TITLE,gat.FINAL_GRADE_PERCENT  FROM gradebook_assignment_types gat where gat.COURSE_PERIOD_ID=\''.$course_period_id.'\' )
                      UNION  
                       (SELECT gat.ASSIGNMENT_TYPE_ID as ASSIGNMENT_TYPE_ID,concat(gat.TITLE,\' (\',cp.title,\')\') as TITLE,gat.FINAL_GRADE_PERCENT as FINAL_GRADE_PERCENT FROM gradebook_assignment_types gat , gradebook_assignments ga, course_periods cp
                        where cp.course_period_id =gat.course_period_id and gat.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID AND ga.COURSE_ID IS NOT NULL 
                        AND ga.COURSE_PERIOD_ID IS NULL AND ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.$teacher_id.'\' ) 
                      )as t
                      GROUP BY ASSIGNMENT_TYPE_ID';

            $QI = DBQuery($sql);
            $types_RET = DBGet($QI);
            $assignment_type = array();
            $total = 0;
            foreach($types_RET as $at)
            {
                $assignment_type[]=$at;
            }
            if(isset($_REQUEST['assignment_id']) && $_REQUEST['assignment_id']!='')
            {
            $sql = 'SELECT ga.ASSIGNMENT_ID,ga.TITLE,ga.ASSIGNED_DATE,ga.DUE_DATE,ga.POINTS,ga.DESCRIPTION,ga.COURSE_ID,ga.COURSE_PERIOD_ID,gat.TITLE AS ASSIGNMENT_TYPE_TITLE FROM gradebook_assignments ga LEFT JOIN gradebook_assignment_types gat ON gat.assignment_type_id = ga.ASSIGNMENT_TYPE_ID WHERE ga.ASSIGNMENT_ID = '.$_REQUEST['assignment_id']; 
            $QI = DBQuery($sql);
            $assn_RET = DBGet($QI);
            $asgnmnt = array();
             foreach($assn_RET as $assn)
                 $asgnmnt=$assn;
                if(isset($asgnmnt['COURSE_ID']) && $asgnmnt['COURSE_ID']!='NULL' && $asgnmnt['COURSE_ID']!='')
             {
                 $asgnmnt['APPLY_TO_ALL']='Y';
             }
                elseif(isset($asgnmnt['COURSE_PERIOD_ID']) && $asgnmnt['COURSE_PERIOD_ID']!='NULL' && $asgnmnt['COURSE_PERIOD_ID']!='')
             {
                 $asgnmnt['APPLY_TO_ALL']='N';
             }
                if(isset($asgnmnt['DESCRIPTION']) && $asgnmnt['DESCRIPTION']!='NULL')
             {
                 $asgnmnt['DESCRIPTION']=  htmlspecialchars_decode($asgnmnt['DESCRIPTION']);
             }
             $data = $asgnmnt;
            }
            if(count($assignment_type)>0)
            {
                $data['assignment_types'] = $assignment_type;
                $data['success'] = 1;
                $data['err_msg'] = 'nil';
                $data['default_due_date'] = '';
                $data['default_assigned_date'] = '';
                foreach($programconfig as $key => $value)
                {
                    if($key=='DEFAULT_DUE')
                    {
                        $data['default_due_date'] = ($programconfig['DEFAULT_DUE']=='Y')?date('Y-m-d'):'';
                    }
                    
                    if($key == 'DEFAULT_ASSIGNED')
                    {
                        $data['default_assigned_date'] = ($programconfig['DEFAULT_ASSIGNED']=='Y')?date('Y-m-d'):'';
                    }
                }
            }
            else 
            {
                $data['success'] = 0;
                $data['err_msg'] = 'No assignment type found.';
            }
    }
    elseif($type=='add_submit')
    {
        
        $_SESSION['UserSchool'] = $_REQUEST['school_id'];
        $_SESSION['UserSyear'] = $_REQUEST['syear'];
        $assignment=$_REQUEST["assignment_details"];
        $columns = json_decode($assignment,TRUE);
        $columns = $columns[0];
        
        
        $sql = 'INSERT INTO '.$table.' ';

        if($table=='gradebook_assignments')
        {
                if(isset($columns['ASSIGNMENT_TYPE_ID']) && $columns['ASSIGNMENT_TYPE_ID']!='')
                {
                        $_REQUEST['assignment_type_id'] = $columns['ASSIGNMENT_TYPE_ID'];
                        unset($columns['ASSIGNMENT_TYPE_ID']);
                }
                

                // $id = DBGet(DBQuery('SHOW TABLE STATUS LIKE \'gradebook_assignments\''));
                // $id[1]['ID']= $id[1]['AUTO_INCREMENT'];
                // $id = $id[1]['ID'];


                // $_REQUEST['assignment_id'] = $id;
                
                $check_cp_type=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM course_periods WHERE COURSE_PERIOD_ID='.$course_period_id));
                
                if($check_cp_type[1]['MARKING_PERIOD_ID']!='')
                {
                $fields = 'ASSIGNMENT_TYPE_ID,STAFF_ID,MARKING_PERIOD_ID,';
                $values = "'".$_REQUEST['assignment_type_id']."','".$teacher_id."','".$mp_id."',";
                }
                else
                {
                $full_year_mp=DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SCHOOL_ID='.$_REQUEST['school_id'].' AND SYEAR='.$_REQUEST['syear']));
                $full_year_mp=$full_year_mp[1]['MARKING_PERIOD_ID'];
                $fields = 'ASSIGNMENT_TYPE_ID,STAFF_ID,MARKING_PERIOD_ID,';
                $values = "'".$_REQUEST['assignment_type_id']."','".$teacher_id."','".$full_year_mp."',";
                }
        }

        $go = false;

        if(!$columns['APPLY_TO_ALL'] && $_REQUEST['table']=='gradebook_assignments')
                $columns['COURSE_ID'] = 'N';
        
        foreach($columns as $column=>$value)
        {
                if($columns['DUE_DATE'] && $columns['ASSIGNED_DATE'])
                { 
                    if(strtotime($columns['DUE_DATE'])<strtotime($columns['ASSIGNED_DATE']))
                    {
                        $msg='Due date cannot be less than assigned date';
                        $go=false;
                        $err=true;
                        break;
                    }
                }
                if($column=='APPLY_TO_ALL' && $value=='Y')
                {
                        $column ='COURSE_ID';
                        $value = $course_id;
                }
                elseif($column=='APPLY_TO_ALL' && $value=='N')
                {
                        $column = 'COURSE_PERIOD_ID';
                        $value = $course_period_id;                                        
                }
                if($column=='DESCRIPTION' && $value!='')
                {
                    //$value=htmlspecialchars($value);
                    $value=html_entity_decode(html_entity_decode($value));
                    $value=strip_tags($value);
                }

                if($value!='')
                {
                    if($column!='DESCRIPTION' && $table=='gradebook_assignments')
                    {
                        $value= paramlib_validation($column,$value);
                    }
                        $fields .= $column.',';

                        $values .= '\''.str_replace("'","''",$value).'\',';
                        $go = true;                                        
                }
        }
        $sql .= '(' . substr($fields,0,-1) . ') values(' . substr($values,0,-1) . ')';
//		}
                

        if((isset($columns['DUE_DATE']) || isset($columns['ASSIGNED_DATE'])) && $err==false)
        {
            
            $get_dates=DBGet(DBQuery('SELECT START_DATE,END_DATE FROM marking_periods WHERE marking_period_id=\''.$mp_id.'\' '));
            $s_d=strtotime($get_dates[1]['START_DATE']);
            $e_d=strtotime($get_dates[1]['END_DATE']);
            if(isset($columns['DUE_DATE']) && isset($columns['ASSIGNED_DATE']))
            {
                if(strtotime($columns['DUE_DATE'])>$s_d && strtotime($columns['DUE_DATE'])<=$e_d && strtotime($columns['ASSIGNED_DATE'])>=$s_d && strtotime($columns['ASSIGNED_DATE'])<$e_d)
                    $go=true;
                else
                {
                    $msg='Assigned date and due date must be within the current marking periods start date and end date.';
                    $go=false;
                    $_REQUEST['assignment_id'] = 'new'; 
                }
            }
            if(isset($columns['DUE_DATE']) && !isset($columns['ASSIGNED_DATE']))
            {
                if(strtotime($columns['DUE_DATE'])>$s_d && strtotime($columns['DUE_DATE'])<=$e_d)
                    $go=true;
                else
                {
                    $msg='Due date must be within the current marking periods start date and end date.';
                    $go=false;
                    $_REQUEST['assignment_id'] = 'new'; 
                }
            }
            if(!isset($columns['DUE_DATE']) && isset($columns['ASSIGNED_DATE']))
            {
                if(strtotime($columns['ASSIGNED_DATE'])>=$s_d && strtotime($columns['ASSIGNED_DATE'])<$e_d)
                    $go=true;
                else
                {
                    $msg='Assigned date must be within the current marking periods start date and end date.';
                    $go=false;
                    $_REQUEST['assignment_id'] = 'new'; 
                }
            }
        }
        if(!isset($_REQUEST['assignment_type_id']) || $_REQUEST['assignment_type_id']=='')
        {
            $msg='Assignment type cannot be blank';
            $go=false;
            $_REQUEST['assignment_id'] = 'new'; 
        }
        if($go==true)
        {
            if($_REQUEST['assignment_id']!=''){
                DBQuery_assignment($sql);
                $_REQUEST['assignment_id'] = mysqli_insert_id($connection);
            } else
                DBQuery ($sql);
//            DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
            $data['success'] = 1;
            $data['err_msg'] = 'Nil';
        }
        else 
        {
            $data['success'] = 0;
            $data['err_msg'] = $msg;
        }
    }
    elseif($type=='edit_submit')
    {
        $assignment_detail=$_REQUEST["assignment_detail"];
        $columns = json_decode($assignment_detail,TRUE);
        $columns = $columns[0];
        
        if(trim($columns['TITLE'])!="" || !isset($columns['TITLE']))
        {

            $sql = 'UPDATE '.$table.' SET ';

            if(isset($columns['COURSE_ID']) && $columns['COURSE_ID']=='' && $table=='gradebook_assignments')
                    $columns['COURSE_ID'] = 'N';

            foreach($columns as $column=>$value)
            {

                    if($column=='DUE_DATE' || $column=='ASSIGNED_DATE')
                    {

                        $due_date_sql = DBGet(DBQuery('SELECT ASSIGNED_DATE,DUE_DATE FROM gradebook_assignments WHERE ASSIGNMENT_ID=\''.$_REQUEST['assignment_id'].'\''));
                        if($columns['DUE_DATE'] && $columns['ASSIGNED_DATE'])
                        {   
                            if(strtotime($columns['DUE_DATE'])<strtotime($columns['ASSIGNED_DATE']))
                            {
                                $err=true;
                                continue;
                            }
                        }
                        if($columns['DUE_DATE'] && !$columns['ASSIGNED_DATE'])
                        {   
                            if(strtotime($columns['DUE_DATE'])<strtotime($due_date_sql[1]['ASSIGNED_DATE']) && $due_date_sql[1]['ASSIGNED_DATE']!='')
                            {
                                $err=true;
                                continue;
                            }
                        }
                        if(!$columns['DUE_DATE'] && $columns['ASSIGNED_DATE'])
                        {   
                            if(strtotime($due_date_sql[1]['DUE_DATE'])<strtotime($columns['ASSIGNED_DATE']) && $due_date_sql[1]['DUE_DATE']!='')
                            {
                                $err=true;
                                continue;
                            }
                        }
                    }
                    if($column=='DESCRIPTION' && $value!='' && $table=='gradebook_assignments')
                    {
                        $value= htmlspecialchars($value);
                    }

                    if($column=='APPLY_TO_ALL' && $value=='Y' && $table=='gradebook_assignments')
                    {
                            $column ='COURSE_ID';
                            $value = $course_id;
                            $sql .= 'COURSE_PERIOD_ID=NULL,';
                    }
                    elseif($column=='APPLY_TO_ALL' && $value=='N' && $table=='gradebook_assignments')
                    {
                            $column = 'COURSE_PERIOD_ID';
                            $get_assignment_course_period=DBGet(DBQuery('SELECT gat.COURSE_PERIOD_ID FROM gradebook_assignment_types gat,gradebook_assignments ga WHERE ga.ASSIGNMENT_TYPE_ID=gat.ASSIGNMENT_TYPE_ID AND ga.ASSIGNMENT_ID='.$_REQUEST['assignment_id']));
                            $value = ($get_assignment_course_period[1]['COURSE_PERIOD_ID']!=''?$get_assignment_course_period[1]['COURSE_PERIOD_ID']:$course_period_id);
                            $sql .= 'COURSE_ID=NULL,';
                            if($get_assignment_course_period[1]['COURSE_PERIOD_ID']!=$course_period_id)
                            $redirect_now='y';

                    }
                    if($column!='DESCRIPTION' && $table=='gradebook_assignments')
                    {
                     $value= paramlib_validation($column,$value);
                    }

                                    $value=str_replace("'","''",$value);
                    $sql .= $column.'=\''.$value.'\',';
                                    }
            $sql = substr($sql,0,-1) . ' WHERE '.substr($table,10,-1).'_ID=\''.$_REQUEST['assignment_id'].'\'';
            $go = true;
        }
        else 
        {
            $msg = 'Title Cannot be Blank';
        }
        
        if((isset($columns['DUE_DATE']) || isset($columns['ASSIGNED_DATE'])) && $err==false)
                {
                    
                    $get_dates=DBGet(DBQuery('SELECT START_DATE,END_DATE FROM marking_periods WHERE marking_period_id=\''.$mp_id.'\' '));
                    $s_d=strtotime($get_dates[1]['START_DATE']);
                    $e_d=strtotime($get_dates[1]['END_DATE']);
                    if(isset($columns['DUE_DATE']) && isset($columns['ASSIGNED_DATE']))
                    {
                        if(strtotime($columns['DUE_DATE'])>$s_d && strtotime($columns['DUE_DATE'])<=$e_d && strtotime($columns['ASSIGNED_DATE'])>=$s_d && strtotime($columns['ASSIGNED_DATE'])<$e_d)
                            $go=true;
                        else
                        {
                            $msg='Assigned date and due date must be within the current marking periods start date and end date.';
                            $go=false;
                            $_REQUEST['assignment_id'] = 'new'; 
                        }
                    }
                    if(isset($columns['DUE_DATE']) && !isset($columns['ASSIGNED_DATE']))
                    {
                        if(strtotime($columns['DUE_DATE'])>$s_d && strtotime($columns['DUE_DATE'])<=$e_d)
                            $go=true;
                        else
                        {
                            $msg='Due date must be within the current marking periods start date and end date.';
                            $go=false;
                            $_REQUEST['assignment_id'] = 'new'; 
                        }
                    }
                    if(!isset($columns['DUE_DATE']) && isset($columns['ASSIGNED_DATE']))
                    {
                        if(strtotime($columns['ASSIGNED_DATE'])>=$s_d && strtotime($columns['ASSIGNED_DATE'])<$e_d)
                            $go=true;
                        else
                        {
                            $msg='Assigned date must be within the current marking periods start date and end date.';
                            $go=false;
                            $_REQUEST['assignment_id'] = 'new'; 
                        }
                    }
                }
                
		if($go)
                {
                    if($_REQUEST['assignment_id']!='')
                        DBQuery_assignment($sql);
                    else
                        DBQuery ($sql);
//                    DBQuery('UPDATE gradebook_assignments SET UNGRADED=2 WHERE ASSIGNMENT_ID IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NULL OR POINTS=\'\') OR ASSIGNMENT_ID NOT IN (SELECT ASSIGNMENT_ID FROM gradebook_grades WHERE POINTS IS NOT NULL OR POINTS!=\'\')');
                    $data['success'] = 1;
                    $data['err_msg'] = 'Nil';
                }
                else 
                {
                    $data['success'] = 0;
                    $data['err_msg'] = $msg;
                }
        
    }
    elseif($type=='delete')
    {
        $table = 'assignment';

        $has_assigned=0;
        $marking_period_id =  $mp_id.",'E".$mp_id."'";
        
	$stmt = DBGet(DBQuery("SELECT ID AS TOTAL_ASSIGNED FROM student_report_card_grades WHERE COURSE_PERIOD_ID=".$course_period_id." AND MARKING_PERIOD_ID IN($marking_period_id)"));

        $has_assigned=$stmt[1]['TOTAL_ASSIGNED'];
        if($has_assigned>0)
        {
            $data['success'] = 0;
            $data['err_msg'] = 'Gradebook Assignment cannot be deleted because grade was given for this assignment.';
        }
        else
        {
            DBQuery('DELETE FROM gradebook_grades WHERE assignment_id=\''.$_REQUEST['assignment_id'].'\'');
            DBQuery('DELETE FROM gradebook_assignments WHERE assignment_id=\''.$_REQUEST['assignment_id'].'\'');
            $data['success'] = 1;
            $data['err_msg'] = 'Nil';
        }
    }
}
else 
{
    $data['success'] = 0;
    $data['err_msg'] = 'You don\'t have a course this period.';
}
    }
    else 
    {
       $data = array('success' => 0, 'msg' => 'Not authenticated user'); 
    }
}
else 
{
    $data = array('success' => 0, 'msg' => 'Not authenticated user');
}
function _makePercent($value,$column)
{
	return Percent($value,2);
}
echo json_encode($data);

?>