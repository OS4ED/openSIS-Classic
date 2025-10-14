<?php
include("../functions/ParamLibFnc.php");
include("../Data.php");
include('function/ConnectionClass.php');
include("function/DbGetFnc.php");

header('Content-Type: application/json');
$fp_data = array();
function db_start()
{	global $DatabaseServer,$DatabaseUsername,$DatabasePassword,$DatabaseName,$DatabasePort,$DatabaseType,$connection ;

	switch($DatabaseType)
	{
		case 'mysqli':
//                    mysqli_connect($DatabaseServer,$DatabaseUsername,$DatabasePassword,$DatabaseName) or die(mysqli_connect_error());
                        $connection = new ConnectDBOpensis();
                        
                        if($connection->auto_init==true)
                        {
                        $connection=$connection->init($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);
                       mysqli_set_charset($connection,"utf8");
                        }
		break;
	}

	// Error code for both.
	if($connection === false)
	{
		switch($DatabaseType)
		{
			case 'mysqli':
				$errormessage = mysqli_error($connection);
			break;
		}
		db_show_error("",""._couldNotConnectToDatabase.": $DatabaseServer",$errstring);
	}
	return $connection;
}

// This function connects, and does the passed query, then returns a connection identifier.
// Not receiving the return == unusable search.
//		ie, $processable_results = DBQuery("select * from students");
function DBQuery($sql)
{	global $DatabaseType,$_openSIS;

	$connection = db_start();

	switch($DatabaseType)
	{
		case 'mysqli':
                        
			$sql = str_replace('&amp;', "", $sql);
			$sql = str_replace('&quot', "", $sql);
			$sql = str_replace('&#039;', "", $sql);
			$sql = str_replace('&lt;', "", $sql);
			$sql = str_replace('&gt;', "", $sql);
//		  	$sql = ereg_replace("([,\(=])[\r\n\t ]*''",'\\1NULL',$sql);
                        
			if(preg_match_all("/'(\d\d-[A-Za-z]{3}-\d{2,4})'/",$sql,$matches))
				{
					foreach($matches[1] as $match)
					{
                                        $date_cheker_mod=explode('-',$match);
                                        if(strlen($date_cheker_mod[2])==4 && $date_cheker_mod[2]<1970)
                                        {
                                         $month_names=array('JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08','SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12');
                                         $date_cheker_mod[1]=$month_names[$date_cheker_mod[1]];
                                         $dt =$date_cheker_mod[2].'-'.$date_cheker_mod[1].'-'.$date_cheker_mod[0] ;
                                        }
                                        else
						$dt = date('Y-m-d',strtotime($match));

						$sql = preg_replace("/'$match'/","'$dt'",$sql);
					}
				}
			if(substr($sql,0,6)=="BEGIN;")
			{
				$array = explode( ";", $sql );
				foreach( $array as $value )
				{
					if($value!="")
					{
                                           $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                                           if($user_agent[2]=='758.0.2 Darwin')
                                               $chk = explode(' ',$user_agent[2]);
                                           else 
                                               $chk='';
                                           if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || $chk=='Darwin' || $user_agent[0]=='PostmanRuntime')
                                           { 
                                           $result = $connection->query($value);
                                           }
						if(!$result)
						{
                                                    $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                                                    if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || $chk=='Darwin' || $user_agent[0]=='PostmanRuntime')
                                                    {
                                                    $connection->query("ROLLBACK");
							die(db_show_error($sql,_dbExecuteFailed,mysql_error()));
						}
					}
				}
			}
			}
			else
			{
                            $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                            if($user_agent[2]=='758.0.2 Darwin')
                                $chk = explode(' ',$user_agent[2]);
                            else 
                                $chk='';
                            if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || $chk=='Darwin' || $user_agent[0]=='PostmanRuntime')
                            {				
                                $result = $connection->query($sql) or die(db_show_error($sql,_dbExecuteFailed,mysql_error()));
			}
                                
			}
		break;
	}
	return $result;
}

// return next row.
function db_fetch_row($result)
{	global $DatabaseType;

	switch($DatabaseType)
	{
		case 'mysqli':
			$return = $result->fetch_assoc();
			if(is_array($return))
			{
				foreach($return as $key => $value)
				{
					if(is_int($key))
						unset($return[$key]);
				}
			}
		break;
	}
	return @array_change_key_case($return,CASE_UPPER);
}

// returns code to go into SQL statement for accessing the next value of a sequence function db_seq_nextval($seqname)
function db_seq_nextval($seqname)
{	global $DatabaseType;

	if($DatabaseType=='mysqli')
		$seq="fn_".strtolower($seqname)."()";
		
	return $seq;
}

function db_case($array)
{	global $DatabaseType;

	$counter=0;
	if($DatabaseType=='mysqli')
	{
		$array_count=count($array);
		$string = " CASE WHEN $array[0] =";
		$counter++;
		$arr_count = count($array);
		for($i=1;$i<$arr_count;$i++)
		{
			$value = $array[$i];

			if($value=="''" && substr($string,-1)=='=')
			{
				$value = ' IS NULL';
				$string = substr($string,0,-1);
			}

			$string.="$value";
			if($counter==($array_count-2) && $array_count%2==0)
				$string.=" ELSE ";
			elseif($counter==($array_count-1))
				$string.=" END ";
			elseif($counter%2==0)
				$string.=" WHEN $array[0]=";
			elseif($counter%2==1)
				$string.=" THEN ";

			$counter++;
		}
	}

	return $string;
}

function db_properties($table)
{	global $DatabaseType,$DatabaseUsername;

	switch($DatabaseType)
	{
		case 'mysqli':
			$result = DBQuery("SHOW COLUMNS FROM $table");
			while($row = db_fetch_row($result))
			{
				$properties[strtoupper($row['FIELD'])]['TYPE'] = strtoupper($row['TYPE'],strpos($row['TYPE'],'('));
				if(!$pos = strpos($row['TYPE'],','))
					$pos = strpos($row['TYPE'],')');
				else
					$properties[strtoupper($row['FIELD'])]['SCALE'] = substr($row['TYPE'],$pos+1);

				$properties[strtoupper($row['FIELD'])]['SIZE'] = substr($row['TYPE'],strpos($row['TYPE'],'(')+1,$pos);

				if($row['NULL']!='')
					$properties[strtoupper($row['FIELD'])]['NULL'] = "Y";
				else
					$properties[strtoupper($row['FIELD'])]['NULL'] = "N";
			}
		break;
	}
	return $properties;
}

function db_show_error($sql,$failnote,$additional='')
{	global $openSISTitle,$openSISVersion,$openSISNotifyAddress;

	PopTable('header','Error');
	$tb = debug_backtrace();
	$error = $tb[1]['file'] . " at " . $tb[1]['line'];
            echo "
                    <TABLE CELLSPACING=10 BORDER=0>
                            <TD align=right><b>Date:</TD>
                            <TD><pre>".date("m/d/Y h:i:s")."</pre></TD>
                    </TR><TR>
                            <TD align=right><b>Failure Notice:</b></TD>
                            <TD><pre> $failnote </pre></TD>
                    </TR><TR>
                            <TD align=right><b>SQL:</b></TD>
                            <TD>$sql</TD>
                    </TR>
                    </TR><TR>
                            <TD align=right><b>Traceback:</b></TD>
                            <TD>$error</TD>
                    </TR>
                    </TR><TR>
                            <TD align=right><b>Additional Information:</b></TD>
                            <TD>$additional</TD>
                    </TR>
                    </TABLE>";
		echo "
		<TABLE CELLSPACING=10 BORDER=0>
			<TR><TD align=right><b>Date:</TD>
			<TD><pre>".date("m/d/Y h:i:s")."</pre></TD>
		</TR><TR>
			<TD align=right></TD>
			<TD>openSIS has encountered an error that could have resulted from any of the following:
			<br/>
			<ul>
			<li>Invalid data input</li>
			<li>Database SQL error</li>
			<li>Program error</li>
			</ul>
			
			Please take this screen shot and send it to your openSIS representative for debugging and resolution.
			</TD>
		</TR>
		
		</TABLE>";
	//Something you have asked the system to do has thrown a database error.  A system administrator has been notified, and the problem will be fixed as soon as possible.  It might be that changing the input parameters sent to this program will cause it to run properly.  Thanks for your patience.
	PopTable('footer');
	echo "<!-- SQL STATEMENT: \n\n $sql \n\n -->";



	if($openSISNotifyAddress)
	{
		$message = "System: $openSISTitle \n";
		$message .= "Date: ".date("m/d/Y h:i:s")."\n";
		$message .= "Page: ".$_SERVER['PHP_SELF'].' '.ProgramTitle()." \n\n";
		$message .= "Failure Notice:  $failnote \n";
		$message .= "Additional Info: $additional \n";
		$message .= "\n $sql \n";
		$message .= "Request Array: \n".ShowVar($_REQUEST,'Y', 'N');
		$message .= "\n\nSession Array: \n".ShowVar($_SESSION,'Y', 'N');
		mail($openSISNotifyAddress,'openSIS Database Error',$message);

	}

	die();
}
$log_msg =  DBGet(DBQuery("SELECT MESSAGE FROM login_message WHERE DISPLAY='Y'"));

if($_REQUEST['pass_type_form']=='password')
{
    if($_REQUEST['pass_user_type']=='pass_student')
    {
        if($_REQUEST['password_stn_id']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Student Id.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['flag']='';
            $fp_data['user_info']='';
        }
        if($_REQUEST['uname']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Username.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['flag']='';
            $fp_data['user_info']='';
        }
        if($_REQUEST['student_dob']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Birthday Properly.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['flag']='';
            $fp_data['user_info']='';
        }
        
        if($_REQUEST['password_stn_id']!='' && $_REQUEST['uname']!='' && $_REQUEST['student_dob']!='')
        {
            $stu_dob=$_REQUEST['student_dob'];
            $stu_info=  DBGet(DBQuery('SELECT s.* FROM students s,login_authentication la  WHERE la.USER_ID=s.STUDENT_ID AND la.USERNAME=\''.trim($_REQUEST['uname'],' ').'\' AND s.BIRTHDATE=\''.date('Y-m-d',strtotime($stu_dob)).'\' AND s.STUDENT_ID='.$_REQUEST['password_stn_id'].' AND la.PROFILE_ID=3'));

            if($stu_info[1]['STUDENT_ID']=='')
            {
                $fp_data['err_msg'] = 'Incorrect login credential.';
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['flag']='';
                $fp_data['user_info']=''; 
            }
            else
            {
                $fp_data['err_msg'] = '';
                $fp_data['success'] = 1;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['flag']='stu_pass';
                $fp_data['user_info']=$stu_info[1]['STUDENT_ID'].',3,'.$_REQUEST['uname'];
            }
        }
//         if($_REQUEST['password_stn_id']!='')
//        {echo "hi";
//           $res=  DBGet(DBQuery('select USERNAME from login_authentication where user_id='.$_REQUEST['password_stn_id'].' and profile_id=3 '));
//             if(strtolower($res[1]['USERNAME'])!=strtolower($_REQUEST['uname']))
//        echo    $_SESSION['err_msg'] = 'Please Enter correct Username111.';
//          exit; 
////          echo'<script>window.location.href="ForgotPass.php"</script>';
//        }
    }
    if($_REQUEST['pass_user_type']=='pass_staff')
    {
        
        if($_REQUEST['uname']=='')
        {
            $fp_data['err_msg'] = "Please Enter Username.";
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['flag']='';
            $fp_data['user_info']='';
        }
        if($_REQUEST['password_stf_email']=='')
        {
            $fp_data['err_msg'] = "Please Enter Email Address.";
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['flag']='';
            $fp_data['user_info']='';
        }
        
        if($_REQUEST['password_stf_email']!='' && $_REQUEST['uname']!='')
        {
            
            $stf_info=  DBGet(DBQuery('SELECT s.* FROM staff s,login_authentication la  WHERE la.USER_ID=s.STAFF_ID AND la.USERNAME=\''.trim($_REQUEST['uname'],' ').'\' AND s.EMAIL=\''.$_REQUEST['password_stf_email'].'\' AND la.PROFILE_ID IN (SELECT ID FROM user_profiles WHERE ID NOT IN (0,3,4))'));
        
            if($stf_info[1]['STAFF_ID']=='')
            {
                $fp_data['err_msg'] = "Incorrect login credential.";
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['flag']='';
                $fp_data['user_info']='';
            }
            else
            {
                $fp_data['flag']='stf_pass';
                $fp_data['user_info']=$stf_info[1]['STAFF_ID'].','.$stf_info[1]['PROFILE_ID'].','.$_REQUEST['uname'];
                $fp_data['success'] = 1;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['err_msg'] = '';
            }
        }
    }
    if($_REQUEST['pass_user_type']=='pass_parent')
    {
        if($_REQUEST['uname']=='')
        {
            $fp_data['err_msg'] = "Please Enter Username.";
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['flag']='';
                $fp_data['user_info']='';
        }
        if($_REQUEST['password_stf_email']=='')
        {
            $fp_data['err_msg'] = "Please Enter Email Address.";
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['flag']='';
                $fp_data['user_info']='';
        }
        
        if($_REQUEST['password_stf_email']!='' && $_REQUEST['uname']!='')
        {
            
            $par_info=  DBGet(DBQuery('SELECT p.* FROM people p,login_authentication la  WHERE la.USER_ID=p.STAFF_ID AND la.USERNAME=\''.trim($_REQUEST['uname'],' ').'\' AND p.EMAIL=\''.$_REQUEST['password_stf_email'].'\' AND la.PROFILE_ID = 4'));
        
            if($par_info[1]['STAFF_ID']=='')
            {
                $fp_data['err_msg'] = "<font color='red' ><b>Incorrect login credential.</b></font>";
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['flag']='';
                $fp_data['user_info']='';
            }
            else
            {
                $fp_data['flag']='par_pass';
                $fp_data['user_info']=$par_info[1]['STAFF_ID'].','.$par_info[1]['PROFILE_ID'].','.$_REQUEST['uname'];
                $fp_data['success'] = 1;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['err_msg'] = '';
            }
        }
    }
}
if($_REQUEST['user_type_form']=='username')
{
    if($_REQUEST['uname_user_type']=='uname_student')
    {
        if($_REQUEST['username_stn_id']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Student Id.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        if($_REQUEST['pass']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Password.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        if($_REQUEST['student_dob']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Birthday Properly.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        
        if($_REQUEST['username_stn_id']!='' && $_REQUEST['pass']!='' && $_REQUEST['student_dob']!='')
        {
            $stu_dob=$_REQUEST['student_dob'];
            $stu_info=  DBGet(DBQuery('SELECT s.* FROM students s,login_authentication la  WHERE la.USER_ID=s.STUDENT_ID AND la.PASSWORD=\''.md5(trim($_REQUEST['pass'],' ')).'\' AND s.BIRTHDATE=\''.date('Y-m-d',strtotime($stu_dob)).'\' AND s.STUDENT_ID='.$_REQUEST['username_stn_id'].''));
        
            if($stu_info[1]['STUDENT_ID']=='')
            {
                $fp_data['err_msg'] = 'Incorrect login credential.';
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['fill_username']='';
            }
            else
            {
                $get_uname=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID='.$_REQUEST['username_stn_id'].' AND PROFILE_ID=3'));
                $fp_data['fill_username']=$get_uname[1]['USERNAME'];
                $fp_data['success'] = 1;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['err_msg'] = '';
            }
        }
    }
    if($_REQUEST['uname_user_type']=='uname_staff')
    {
        
        if($_REQUEST['pass']=='')
        {
            $fp_data['err_msg'] = "Please Enter Password.";
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        if($_REQUEST['username_stf_email']=='')
        {
            $fp_data['err_msg'] = "Please Enter Email Address.";
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        
        if($_REQUEST['username_stf_email']!='' && $_REQUEST['pass']!='')
        {
            $stf_info=  DBGet(DBQuery('SELECT s.* FROM staff s,login_authentication la WHERE la.USER_ID=s.STAFF_ID AND la.PASSWORD=\''.md5(trim($_REQUEST['pass'],' ')).'\' AND s.EMAIL=\''.$_REQUEST['username_stf_email'].'\''));
        
            if($stf_info[1]['STAFF_ID']=='')
            {
                $fp_data['err_msg'] = "Incorrect login credential.";
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['fill_username']='';
            }
            else
            {
                $get_uname=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID='.$stf_info[1]['STAFF_ID'].' AND PROFILE_ID='.$stf_info[1]['PROFILE_ID']));
                $fp_data['fill_username']=$get_uname[1]['USERNAME'];
                $fp_data['success'] = 1;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['err_msg'] = '';
            }
        }
    }
    if($_REQUEST['uname_user_type']=='uname_parent')
    {
        if($_REQUEST['pass']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Password.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        if($_REQUEST['username_stf_email']=='')
        {
            $fp_data['err_msg'] = 'Please Enter Email Address.';
            $fp_data['success'] = 0;
            $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
            $fp_data['fill_username']='';
        }
        
        if($_REQUEST['username_stf_email']!='' && $_REQUEST['pass']!='')
        {
            $par_info=  DBGet(DBQuery('SELECT p.* FROM people p,login_authentication la WHERE la.USER_ID=p.STAFF_ID AND la.PASSWORD=\''.md5(trim($_REQUEST['pass'],' ')).'\' AND p.EMAIL=\''.$_REQUEST['username_stf_email'].'\' '));
        
            if($par_info[1]['STAFF_ID']=='')
            {
                $fp_data['err_msg'] = "Incorrect login credential.";
                $fp_data['success'] = 0;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['fill_username']='';
            }
            else
            {
                $get_uname=DBGet(DBQuery('SELECT USERNAME FROM login_authentication WHERE USER_ID='.$par_info[1]['STAFF_ID'].' AND PROFILE_ID=4'));
                $fp_data['fill_username']=$get_uname[1]['USERNAME'];
                $fp_data['success'] = 1;
                $fp_data['log_msg'] = $log_msg[1]['MESSAGE'];
                $fp_data['err_msg'] = '';
            }
        }
    }
}
    if($_REQUEST['new_pass']!='' && $_REQUEST['ver_pass']!='')
    {
        if($_REQUEST['new_pass'] == $_REQUEST['ver_pass'])
        {
       $get_vals=explode(",",$_REQUEST['user_info']);

       $fp_data['flag']='submited_value';

        $get_info=DBGet(DBQuery('SELECT COUNT(*) AS EX_REC FROM login_authentication WHERE user_id!='.$get_vals[0].' AND profile_id!='.$get_vals[1].' AND password=\''.md5(trim($_REQUEST['new_pass'],' ')).'\' '));
       if($get_info[1]['EX_REC']>0)
       {
          $fp_data['err_msg_mod'] = "Incorrect login credential.";
          $fp_data['conf_msg'] = '';
          $fp_data['success'] = 0;
       }
       else
       {
            DBQuery('UPDATE login_authentication SET password=\''.md5(trim($_REQUEST['new_pass'],' ')).'\' WHERE user_id='.$get_vals[0].' AND profile_id='.$get_vals[1].' ');
           $fp_data['conf_msg'] = "Password updated successfully.";
           $fp_data['err_msg_mod'] = '';
           $fp_data['success'] = 1;
       }
    }
    else 
    {
        $fp_data['err_msg_mod'] = "New & verified password did not match.";
        $fp_data['conf_msg'] = '';
        $fp_data['success'] = 0;
    }
}
echo json_encode($fp_data);
?>