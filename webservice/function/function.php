<?php
include('ConnectionClass.php');
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
//		  	$sql = par_rep("([,\(=])[\r\n\t ]*''",'\\1NULL',$sql);
                        
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

						$sql = par_rep("/'$match'/","'$dt'",$sql);
					}
				}
                                
                        $sql = mysqli_real_escape_string($sconnection,$sql);
			if(substr($sql,0,6)=="BEGIN;")
			{
				$array = explode( ";", $sql );
				foreach( $array as $value )
				{
					if($value!="")
					{
                                           $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                                           if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || strpos($_SERVER['HTTP_USER_AGENT'],'Darwin')!='' || $user_agent[0]=='PostmanRuntime')
                                           { 
                                           $result = $connection->query($value);
                                           }
						if(!$result)
						{
                                                    $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                                                    if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || strpos($_SERVER['HTTP_USER_AGENT'],'Darwin')!='' || $user_agent[0]=='PostmanRuntime')
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
                            if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || strpos($_SERVER['HTTP_USER_AGENT'],'Darwin')!='' || $user_agent[0]=='PostmanRuntime')
                            {				
                            $result = $connection->query($sql) or die(db_show_error($sql,_dbExecuteFailed,mysql_error()));
                            }
                                
			}
		break;
	}
	return $result;
}
function DBQuery_assignment($sql)
{	global $DatabaseType,$_openSIS;

	$connection = db_start();

	switch($DatabaseType)
	{
		case 'mysqli':
//		  	$sql = par_rep("/([,\(=])[\r\n\t ]*''/",'\\1NULL',$sql);
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

						$sql = par_rep("/'$match'/","'$dt'",$sql);
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
                                                if($user_agent[0]=='Mozilla')
                                                { 
                                                $result = $connection->query($value);
                                                }
						if(!$result)
						{
                                                    $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                                                    if($user_agent[0]=='Mozilla')
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
                                if($user_agent[0]=='Mozilla')
                                { 
                                $result = $connection->query($sql) or die(db_show_error($sql,_dbExecuteFailed,mysql_error()));
                                }
				
			}
		break;
	}
	return $result;
}
function DBQueryMod($sql)
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

			if(preg_match_all("/'(\d\d-[A-Za-z]{3}-\d{2,4})'/",$sql,$matches))
				{
					foreach($matches[1] as $match)
					{
						$dt = date('Y-m-d',strtotime($match));
						$sql = par_rep("/'$match'/","'$dt'",$sql);
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
                                                if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || strpos($_SERVER['HTTP_USER_AGENT'],'Darwin')!='' || $user_agent[0]=='PostmanRuntime')
                                                { 
                                                $result = $connection->query($value);
                                                }
						
						if(!$result)
						{
                                                    $user_agent=explode('/',$_SERVER['HTTP_USER_AGENT']);
                                                    if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || strpos($_SERVER['HTTP_USER_AGENT'],'Darwin')!='' || $user_agent[0]=='PostmanRuntime')
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
                               if($user_agent[0]=='Mozilla' || $user_agent[0]=='Dalvik' || strpos($_SERVER['HTTP_USER_AGENT'],'Darwin')!='' || $user_agent[0]=='PostmanRuntime')
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



// DECODE and CASE-WHEN support

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


/*
function Version()
{
	$query = DBQuery("select value from app where name='version'");
	$sql = mysql_fetch_assoc($query);
	return($sql['value']);
}

function BuildDate()
{
	$query = DBQuery("select value from app where name='build'");
	$build = mysql_fetch_assoc($query);
	$month = substr($build['value'],0,-9);
	$day = substr($build['value'],2,-7);
	$year = substr($build['value'],4,-3);
	switch($month)
	{
		case '01':
		$month = 'January';
		break;
		case '02':
		$month = 'February';
		break;
		case '03':
		$month = 'March';
		break;
		case '04':
		$month = 'April';
		break;
		case '05':
		$month = 'May';
		break;
		case '06':
		$month = 'June';
		break;
		case '07':
		$month = 'July';
		break;
		case '08':
		$month = 'August';
		break;
		case '09':
		$month = 'September';
		break;
		case '10':
		$month = 'October';
		break;
		case '11':
		$month = 'November';
		break;
		case '12':
		$month = 'December';
		break;
	}
	$build_date = $month.'&nbsp;'.$day.',&nbsp;'.$year;
	return($build_date);
}



$http_status_codes = array(100 => "Continue", 101 => "Switching Protocols", 102 => "Processing", 200 => "OK", 201 => "Created", 202 => "Accepted", 203 => "Non-Authoritative Information", 204 => "No Content", 205 => "Reset Content", 206 => "Partial Content", 207 => "Multi-Status", 300 => "Multiple Choices", 301 => "Moved Permanently", 302 => "Found", 303 => "See Other", 304 => "Not Modified", 305 => "Use Proxy", 306 => "(Unused)", 307 => "Temporary Redirect", 308 => "Permanent Redirect", 400 => "Bad Request", 401 => "Unauthorized", 402 => "Payment Required", 403 => "Forbidden", 404 => "Not Found", 405 => "Method Not Allowed", 406 => "Not Acceptable", 407 => "Proxy Authentication Required", 408 => "Request Timeout", 409 => "Conflict", 410 => "Gone", 411 => "Length Required", 412 => "Precondition Failed", 413 => "Request Entity Too Large", 414 => "Request-URI Too Long", 415 => "Unsupported Media Type", 416 => "Requested Range Not Satisfiable", 417 => "Expectation Failed", 418 => "I'm a teapot", 419 => "Authentication Timeout", 420 => "Enhance Your Calm", 422 => "Unprocessable Entity", 423 => "Locked", 424 => "Failed Dependency", 424 => "Method Failure", 425 => "Unordered Collection", 426 => "Upgrade Required", 428 => "Precondition Required", 429 => "Too Many Requests", 431 => "Request Header Fields Too Large", 444 => "No Response", 449 => "Retry With", 450 => "Blocked by Windows Parental Controls", 451 => "Unavailable For Legal Reasons", 494 => "Request Header Too Large", 495 => "Cert Error", 496 => "No Cert", 497 => "HTTP to HTTPS", 499 => "Client Closed Request", 500 => "Internal Server Error", 501 => "Not Implemented", 502 => "Bad Gateway", 503 => "Service Unavailable", 504 => "Gateway Timeout", 505 => "HTTP Version Not Supported", 506 => "Variant Also Negotiates", 507 => "Insufficient Storage", 508 => "Loop Detected", 509 => "Bandwidth Limit Exceeded", 510 => "Not Extended", 511 => "Network Authentication Required", 598 => "Network read timeout error", 599 => "Network connect timeout error");
$res_code = http_response_code();*/


function GetAllMP($mp,$marking_period_id='0')
{	global $_openSIS;

	if($marking_period_id==0)
	{
		// there should be exactly one fy marking period
		$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
		$marking_period_id = $RET[1]['MARKING_PERIOD_ID'];
		$mp = 'FY';
	}
	elseif(!$mp) 
		 $mp = GetMPTable(GetMP($marking_period_id,'TABLE'));
        
     // echo $marking_period_id;
	if(!$_openSIS['GetAllMP'][$mp])
	{
		switch($mp)
		{
			case 'PRO':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
				{
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] .= ','.GetChildrenMP($mp,$value['MARKING_PERIOD_ID']);
					if(substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],-1)==',')
						$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = substr($_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],0,-1);
				}
			break;

			case 'QTR':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[SEMESTER_ID]','$value[MARKING_PERIOD_ID]'";
			break;

			case 'SEM':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_years WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$sem] = "'$fy','$sem'";
					foreach($value as $qtr)
						$_openSIS['GetAllMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'$fy','$value[MARKING_PERIOD_ID]'";
			break;

			case 'FY':
				// there should be exactly one fy marking period which better be $marking_period_id
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				$_openSIS['GetAllMP'][$mp][$marking_period_id] = "'$marking_period_id'";
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$sem'";
					foreach($value as $qtr)
						$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM school_semesters s WHERE NOT EXISTS (SELECT \'\' FROM school_quarters q WHERE q.SEMESTER_ID=s.MARKING_PERIOD_ID) AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $value)
					$_openSIS['GetAllMP'][$mp][$marking_period_id] .= ",'$value[MARKING_PERIOD_ID]'";
			break;
                        
		}
	}

	return $_openSIS['GetAllMP'][$mp][$marking_period_id];
}

function GetParentMP($mp,$marking_period_id='0')
{	global $_openSIS;

	if(!$_openSIS['GetParentMP'][$mp])
	{
		switch($mp)
		{
			case 'QTR':

			break;

			case 'SEM':
				$_openSIS['GetParentMP'][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID AS PARENT_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('MARKING_PERIOD_ID'));
			break;

			case 'FY':
				$_openSIS['GetParentMP'][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,YEAR_ID AS PARENT_ID FROM school_semesters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('MARKING_PERIOD_ID'));
			break;
		}
	}

	return $_openSIS['GetParentMP'][$mp][$marking_period_id][1]['PARENT_ID'];
}

function GetChildrenMPWs($mp,$marking_period_id='0')
{	global $_openSIS;

	switch($mp)
	{
		case 'FY':
			if(!$_openSIS['GetChildrenMP']['FY'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('SEMESTER_ID'));
				foreach($RET as $sem=>$value)
				{
					$_openSIS['GetChildrenMP'][$mp]['0'] .= ",'$sem'";
					foreach($value as $qtr)
						$_openSIS['GetChildrenMP'][$mp]['0'] .= ",'$qtr[MARKING_PERIOD_ID]'";
				}
				$_openSIS['GetChildrenMP'][$mp]['0'] = substr($_openSIS['GetChildrenMP'][$mp]['0'],1);
			}
			return $_openSIS['GetChildrenMP'][$mp]['0'];
		break;

		case 'SEM':
			if(GetMP($marking_period_id,'TABLE')=='school_quarters')
				$marking_period_id = GetParentMP('SEM',$marking_period_id);
			if(!$_openSIS['GetChildrenMP']['SEM'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,SEMESTER_ID FROM school_quarters WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));
				foreach($RET as $sem=>$value)
				{
					foreach($value as $qtr)
						$_openSIS['GetChildrenMP'][$mp][$sem] .= ",'$qtr[MARKING_PERIOD_ID]'";
					$_openSIS['GetChildrenMP'][$mp][$sem] = substr($_openSIS['GetChildrenMP'][$mp][$sem],1);
				}
			}
			return $_openSIS['GetChildrenMP'][$mp][$marking_period_id];
		break;

		case 'QTR':
			return "'".$marking_period_id."'";
		break;

		case 'PRO':
			if(!$_openSIS['GetChildrenMP']['PRO'])
			{
				$RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,QUARTER_ID FROM school_progress_periods WHERE SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''),array(),array('QUARTER_ID'));
				foreach($RET as $qtr=>$value)
				{
					foreach($value as $pro)
						$_openSIS['GetChildrenMP'][$mp][$qtr] .= ",'$pro[MARKING_PERIOD_ID]'";
					$_openSIS['GetChildrenMP'][$mp][$qtr] = substr($_openSIS['GetChildrenMP'][$mp][$qtr],1);
				}
			}
			return $_openSIS['GetChildrenMP'][$mp][$marking_period_id];
		break;
	}
}

function GetMP($mp,$column='TITLE',$syear,$usrschool)
{	global $_openSIS;

	// mab - need to translate marking_period_id to title to be useful as a function call from dbget
	// also, it doesn't make sense to ask for same thing you give
	if($column=='MARKING_PERIOD_ID')
		$column='TITLE';

	if(!$_openSIS['GetMP'])
	{
		$_openSIS['GetMP'] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_quarters\'        AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters         WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_semesters\'       AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters        WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_years\'           AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years            WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_progress_periods\' AS `TABLE`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$usrschool.'\''),array(),array('MARKING_PERIOD_ID'));
	}
	if(substr($mp,0,1)=='E')
	{
		if($column=='TITLE' || $column=='SHORT_NAME')
			$suffix = ' Exam';
		$mp = substr($mp,1);
	}

	if($mp==0 && $column=='TITLE')
		return 'Full Year'.$suffix;
	else
		return $_openSIS['GetMP'][$mp][1][$column].$suffix;
}

function GetMPTable($mp_table)
{
	switch($mp_table)
	{
		case 'school_years':
			return 'FY';
		break;
		case 'school_semesters':
			return 'SEM';
		break;
		case 'school_quarters':
			return 'QTR';
		break;
		case 'school_progress_periods':
			return 'PRO';
		break;
		default:
			return 'FY';
		break;
	}
}

function GetCurrentMP($mp,$date,$error=true)
{	global $_openSIS;

	switch($mp)
	{
		case 'FY':
			$table = 'school_years';
		break;

		case 'SEM':
			$table = 'school_semesters';
		break;

		case 'QTR':
			$table = 'school_quarters';
		break;

		case 'PRO':
			$table = 'school_progress_periods';
		break;
	}

	if(!$_openSIS['GetCurrentMP'][$date][$mp])
	 	$_openSIS['GetCurrentMP'][$date][$mp] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID FROM '.$table.' WHERE \''.$date.'\' BETWEEN START_DATE AND END_DATE AND SYEAR=\''.UserSyear().'\' AND SCHOOL_ID=\''.UserSchool().'\''));

	if($_openSIS['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'])
		return $_openSIS['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'];
}

function UpdateAttendanceDaily($student_id,$date='',$comment=false)
{
	if(!$date)
		$date = DBDate();

	$current_mp=GetCurrentMP('QTR',$date);
        $MP_TYPE='QTR';
        if(!$current_mp){
            $current_mp=GetCurrentMP('SEM',$date);
            $MP_TYPE='SEM';
        }
        if(!$current_mp){
            $current_mp=GetCurrentMP('FY',$date);
            $MP_TYPE='FY';
        }
        
        $sql = 'SELECT
				SUM(sp.LENGTH) AS TOTAL
			FROM schedule s,course_periods cp,course_period_var cpv,school_periods sp,attendance_calendar ac
			WHERE
				s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND cpv.DOES_ATTENDANCE=\'Y\'
				AND ac.SCHOOL_DATE=\''.$date.'\' AND (ac.BLOCK=sp.BLOCK OR sp.BLOCK IS NULL)
                                AND cp.COURSE_PERIOD_ID=cpv.COURSE_PERIOD_ID
				AND ac.CALENDAR_ID=cp.CALENDAR_ID AND ac.SCHOOL_ID=s.SCHOOL_ID AND ac.SYEAR=s.SYEAR
				AND s.SYEAR = cp.SYEAR AND sp.PERIOD_ID = cpv.PERIOD_ID
				AND position(substring(\'UMTWHFS\' FROM DAYOFWEEK(\''.$date.'\')  FOR 1) IN cpv.DAYS)>0
				AND s.STUDENT_ID=\''.$student_id.'\'
				AND s.SYEAR=\''.UserSyear().'\'
				AND (\''.$date.'\' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND \''.$date.'\'>=s.START_DATE))
				AND s.MARKING_PERIOD_ID IN ('.GetAllMP($MP_TYPE,$current_mp).')
			';
	$RET = DBGet(DBQuery($sql));
	$total = $RET[1]['TOTAL'];
	if($total==0)
		return;

        $current_RET = DBGet(DBQuery('SELECT MINUTES_PRESENT,STATE_VALUE,COMMENT FROM attendance_day WHERE STUDENT_ID='.$student_id.' AND SCHOOL_DATE=\''.$date.'\''));
        $total=$current_RET['MINUTES_PRESENT'];
        
        $sql = 'SELECT SUM(sp.LENGTH) AS TOTAL
			FROM attendance_period ap,school_periods sp,attendance_codes ac
			WHERE ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE=\''.$date.'\' AND ap.PERIOD_ID=sp.PERIOD_ID AND ac.ID = ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'P\'
			AND sp.SYEAR=\''.UserSyear().'\'';
	$RET = DBGet(DBQuery($sql));
	$total += $RET[1]['TOTAL'];

	$sql = 'SELECT SUM(sp.LENGTH) AS TOTAL
			FROM attendance_period ap,school_periods sp,attendance_codes ac
			WHERE ap.STUDENT_ID=\''.$student_id.'\' AND ap.SCHOOL_DATE=\''.$date.'\' AND ap.PERIOD_ID=sp.PERIOD_ID AND ac.ID = ap.ATTENDANCE_CODE AND ac.STATE_CODE=\'H\'
			AND sp.SYEAR=\''.UserSyear().'\'';
	$RET = DBGet(DBQuery($sql));
	$total += $RET[1]['TOTAL']*.5;

        if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux')){
          $comment=  str_replace("'","\'",$comment);
        }
	$sys_pref = DBGet(DBQuery('SELECT * FROM system_preference WHERE SCHOOL_ID='.UserSchool()));
	$fdm = $sys_pref[1]['FULL_DAY_MINUTE'];
	$hdm = $sys_pref[1]['HALF_DAY_MINUTE'];

	if($total>=$fdm)
		$length = '1.0';
	elseif($total>=$hdm)
		$length = '.5';
	else
		$length = '0.0';

	$current_RET = DBGet(DBQuery('SELECT MINUTES_PRESENT,STATE_VALUE,COMMENT FROM attendance_day WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\''));
	if(count($current_RET) && $current_RET[1]['MINUTES_PRESENT']!=$total)
		DBQuery('UPDATE attendance_day SET MINUTES_PRESENT=\''.$total.'\',STATE_VALUE=\''.$length.'\''.($comment!=false?',COMMENT=\''.str_replace("","",$comment).'\'':'').' WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\'');
	elseif(count($current_RET) && $comment!=false && $current_RET[1]['COMMENT']!=$comment)
		DBQuery('UPDATE attendance_day SET COMMENT=\''.str_replace("","",$comment).'\' WHERE STUDENT_ID=\''.$student_id.'\' AND SCHOOL_DATE=\''.$date.'\'');
	elseif(count($current_RET)==0)
		DBQuery('INSERT INTO attendance_day (SYEAR,STUDENT_ID,SCHOOL_DATE,MINUTES_PRESENT,STATE_VALUE,MARKING_PERIOD_ID,COMMENT) values(\''.UserSyear().'\',\''.$student_id.'\',\''.$date.'\',\''.$total.'\',\''.$length.'\',\''.$current_mp.'\',\''.str_replace("","",$comment).'\')');
}
?>