<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


include("../functions/ParamLibFnc.php");
include("../functions/SqlSecurityFnc.php");
require_once("../Data.php");
require_once("../functions/PragRepFnc.php");
function db_start() {
    global $DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName, $DatabasePort, $DatabaseType;

    switch ($DatabaseType) {
        case 'mysqli':
            $connection = new mysqli($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);
            break;
    }

    // Error code for both.
    if ($connection === false) {
        switch ($DatabaseType) {
            case 'mysqli':
                $errormessage =$connection->err;
                break;
        }
        db_show_error("", ""._couldNotConnectToDatabase.": $DatabaseServer", $errstring);
    }
    return $connection;
}

// This function connects, and does the passed query, then returns a connection identifier.
// Not receiving the return == unusable search.
//		ie, $processable_results = DBQuery("select * from students");
function DBQuery($sql) {
    global $DatabaseType, $_openSIS;

    $connection = db_start();

    switch ($DatabaseType) {
        case 'mysqli':
            $sql = str_replace('&amp;', "", $sql);
            $sql = str_replace('&quot', "", $sql);
            $sql = str_replace('&#039;', "", $sql);
            $sql = str_replace('&lt;', "", $sql);
            $sql = str_replace('&gt;', "", $sql);
            $sql = par_rep("/([,\(=])[\r\n\t ]*''/", '\\1NULL', $sql);
            if (preg_match_all("/'(\d\d-[A-Za-z]{3}-\d{2,4})'/", $sql, $matches)) {
                foreach ($matches[1] as $match) {
                    $dt = date('Y-m-d', strtotime($match));
                    $sql = par_rep("/'$match'/", "'$dt'", $sql);
                }
            }
            if (substr($sql, 0, 6) == "BEGIN;") {
                $array = explode(";", $sql);
                foreach ($array as $value) {
                    if ($value != "") {
                        $result = $connection->query($value);
                        if (!$result) {
                            $connection->query("ROLLBACK");
                            die(db_show_error($sql, _dbExecuteFailed, mysqli_error($connection)));
                        }
                    }
                }
            } else {
                $result = $connection->query($sql) or die(db_show_error($sql, _dbExecuteFailed, mysqli_error($connection)));
            }
            break;
    }
    return $result;
}

// return next row.
function db_fetch_row($result) {
    global $DatabaseType;

    switch ($DatabaseType) {
        case 'mysqli':
            $return = $result->fetch_assoc();
            if (is_array($return)) {
                foreach ($return as $key => $value) {
                    if (is_int($key))
                        unset($return[$key]);
                }
            }
            break;
    }
    return @array_change_key_case($return, CASE_UPPER);
}

// returns code to go into SQL statement for accessing the next value of a sequence function db_seq_nextval($seqname)
function db_seq_nextval($seqname) {
    global $DatabaseType;

    if ($DatabaseType == 'mysqli')
        $seq = "fn_" . strtolower($seqname) . "()";

    return $seq;
}

function db_case($array) {
    global $DatabaseType;

    $counter = 0;
    if ($DatabaseType == 'mysqli') {
        $array_count = count($array);
        $string = " CASE WHEN $array[0] =";
        $counter++;
        $arr_count = count($array);
        for ($i = 1; $i < $arr_count; $i++) {
            $value = $array[$i];

            if ($value == "''" && substr($string, -1) == '=') {
                $value = ' IS NULL';
                $string = substr($string, 0, -1);
            }

            $string.="$value";
            if ($counter == ($array_count - 2) && $array_count % 2 == 0)
                $string.=" ELSE ";
            elseif ($counter == ($array_count - 1))
                $string.=" END ";
            elseif ($counter % 2 == 0)
                $string.=" WHEN $array[0]=";
            elseif ($counter % 2 == 1)
                $string.=" THEN ";

            $counter++;
        }
    }
    return $string;
}

function db_properties($table) {
    global $DatabaseType, $DatabaseUsername;

    switch ($DatabaseType) {
        case 'mysqli':
            $result = DBQuery("SHOW COLUMNS FROM $table");
            while ($row = db_fetch_row($result)) {
                $properties[strtoupper($row['FIELD'])]['TYPE'] = strtoupper($row['TYPE'], strpos($row['TYPE'], '('));
                if (!$pos = strpos($row['TYPE'], ','))
                    $pos = strpos($row['TYPE'], ')');
                else
                    $properties[strtoupper($row['FIELD'])]['SCALE'] = substr($row['TYPE'], $pos + 1);

                $properties[strtoupper($row['FIELD'])]['SIZE'] = substr($row['TYPE'], strpos($row['TYPE'], '(') + 1, $pos);

                if ($row['NULL'] != '')
                    $properties[strtoupper($row['FIELD'])]['NULL'] = "Y";
                else
                    $properties[strtoupper($row['FIELD'])]['NULL'] = "N";
            }
            break;
    }
    return $properties;
}
function singleQuoteReplace($param1=false,$param2=false,$param3)
{
    return str_replace("'","''",str_replace("\'","'",$param3));
}
function DBGet($QI,$functions=array(),$index=array())
{	global $THIS_RET;

	$index_count = count($index);
	$tmp_THIS_RET = $THIS_RET;
	$results = array();
	while($RET=db_fetch_row($QI))
	{
		$THIS_RET = $RET;

		if($index_count)
		{
			$ind = '';
			foreach($index as $col)
				$ind .= "['".singleQuoteReplace("'","\'",$THIS_RET[$col])."']";
			eval('$s'.$ind.'++;$this_ind=$s'.$ind.';');
		}
		else
			$s++; // 1-based if no index specified
		foreach($RET as $key=>$value)
		{                    
                    if(strlen($value) == strlen(strip_tags($value)))
                    $value=  htmlentities($value);
			if($functions[$key] && function_exists($functions[$key]))
			{
				if($index_count)
					eval('$results'.$ind.'[$this_ind][$key] = $functions[$key]($value,$key);');
				else
					$results[$s][$key] = $functions[$key]($value,$key);
			}
			else
			{
				if($index_count)
					eval('$results'.$ind.'[$this_ind][$key] = $value;');
				else
					$results[$s][$key] = $value;
			}
		}
	}

	$THIS_RET = $tmp_THIS_RET;
	return $results;
}

function db_show_error($sql, $failnote, $additional = '') {
    global $openSISTitle, $openSISVersion, $openSISNotifyAddress, $openSISMode;

    $tb = debug_backtrace();
    $error = $tb[1]['file'] . " at " . $tb[1]['line'];

    echo "
                    <TABLE CELLSPACING=10 BORDER=0>
                            <TD align=right><b>Date:</TD>
                            <TD><pre>" . date("m/d/Y h:i:s") . "</pre></TD>
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
			<TD><pre>" . date("m/d/Y h:i:s") . "</pre></TD>
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

    echo "<!-- SQL STATEMENT: \n\n $sql \n\n -->";

    if ($openSISNotifyAddress) {
        $message = "System: $openSISTitle \n";
        $message .= "Date: " . date("m/d/Y h:i:s") . "\n";
        $message .= "Page: " . $_SERVER['PHP_SELF'] . ' ' . ProgramTitle() . " \n\n";
        $message .= "Failure Notice:  $failnote \n";
        $message .= "Additional Info: $additional \n";
        $message .= "\n $sql \n";
        $message .= "Request Array: \n" . ShowVar($_REQUEST, 'Y', 'N');
        $message .= "\n\nSession Array: \n" . ShowVar($_SESSION, 'Y', 'N');
        mail($openSISNotifyAddress, 'openSIS Database Error', $message);
    }

    die();
}
function GetMP($mp='',$column='TITLE',$syear,$school)
{	global $_openSIS;
    $school=sqlSecurityFilter($school);
	// mab - need to translate marking_period_id to title to be useful as a function call from dbget
	// also, it doesn't make sense to ask for same thing you give
    
	if($column=='MARKING_PERIOD_ID')
		$column='TITLE';

	if(!$_openSIS['GetMP'])
	{
           
		$_openSIS['GetMP'] = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_quarters\' AS `TABLE`,\'SEMESTER_ID\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_quarters         WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$school.'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_semesters\' AS `TABLE`,\'YEAR_ID\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_semesters        WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$school.'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_years\' AS `TABLE`, \'-1\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_years            WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$school.'\'
					UNION      SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,\'school_progress_periods\' AS `TABLE`, \'-1\' AS `PA_ID`,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_EXAM,DOES_COMMENTS FROM school_progress_periods WHERE SYEAR=\''.$syear.'\' AND SCHOOL_ID=\''.$school.'\''),array(),array('MARKING_PERIOD_ID'));

        }
        
	if(substr($mp,0,1)=='E')
	{
		if($column=='TITLE' || $column=='SHORT_NAME')
			$suffix = ' Exam';
		$mp = substr($mp,1);
	}
if($mp=='')
{
    return 'Custom';
}
 else {
   
  if($mp==0 && $column=='TITLE')
      
		return 'Full Year'.$suffix;
	else
        {
		return $_openSIS['GetMP'][$mp][1][$column].$suffix;  
        }
}
	
}
function _makeLetterGrade($percent,$course_period_id=0,$staff_id=0,$ret='')
{	
    global $programconfig,$_openSIS;
	

	
$cp=DBGet(DBQuery('SELECT * FROM course_periods WHERE COURSE_PERIOD_ID='.$course_period_id));
	if(!$programconfig[$staff_id])
	{
		$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.$staff_id.'\' AND PROGRAM=\'Gradebook\' AND VALUE LIKE \'%_'.$course_period_id.'\''),array(),array('TITLE'));
		if(count($config_RET))
			foreach($config_RET as $title=>$value)
                        {
                                $unused_var=explode('_',$value[1]['VALUE']);
                                $programconfig[$staff_id][$title] =$unused_var[0];
//				$programconfig[$staff_id][$title] = rtrim($value[1]['VALUE'],'_'.$course_period_id);
                        }
		else
			$programconfig[$staff_id] = true;
	}
	if(!$_openSIS['_makeLetterGrade']['courses'][$course_period_id])
		$_openSIS['_makeLetterGrade']['courses'][$course_period_id] = DBGet(DBQuery('SELECT DOES_BREAKOFF,GRADE_SCALE_ID FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\''));
	$does_breakoff = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['DOES_BREAKOFF'];
	$grade_scale_id = $_openSIS['_makeLetterGrade']['courses'][$course_period_id][1]['GRADE_SCALE_ID'];

	$percent *= 100;

		if($programconfig[$staff_id]['ROUNDING']=='UP')
                {
			$percent = ceil($percent);
                }
		elseif($programconfig[$staff_id]['ROUNDING']=='DOWN')
                {
			$percent = floor($percent);
                }
		elseif($programconfig[$staff_id]['ROUNDING']=='NORMAL')
                {
			$percent = round($percent,0);
                }
                else
                {
		$percent = round($percent,2); // school default
                }
	if($ret=='%')
		return $percent;

	if(!$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id])
		$_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] = DBGet(DBQuery('SELECT TITLE,ID,BREAK_OFF FROM report_card_grades WHERE SYEAR=\''.$cp[1]['SYEAR'].'\' AND SCHOOL_ID=\''.$cp[1]['SCHOOL_ID'].'\' AND GRADE_SCALE_ID=\''.$grade_scale_id.'\' ORDER BY BREAK_OFF IS NOT NULL DESC,BREAK_OFF DESC,SORT_ORDER'));
	
	foreach($_openSIS['_makeLetterGrade']['grades'][$grade_scale_id] as $grade)
	{
		if($does_breakoff=='Y' ? $percent>=$programconfig[$staff_id][$course_period_id.'-'.$grade['ID']] && is_numeric($programconfig[$staff_id][$course_period_id.'-'.$grade['ID']]) : $percent>=$grade['BREAK_OFF'])
			return $ret=='ID' ? $grade['ID'] : $grade['TITLE'];
	}
}

$connection = new mysqli($DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName);

$format     =   mysqli_real_escape_string($connection,strtolower(optional_param('format', '', PARAM_RAW)));
$api_key    =   mysqli_real_escape_string($connection,optional_param('api_key', '', PARAM_RAW));
$api_secret =   mysqli_real_escape_string($connection, optional_param('api_secret', '', PARAM_RAW));

$api_key    =   sqlSecurityFilter($api_key);
$api_secret =   sqlSecurityFilter($api_secret);

$validate= DBGet(DBQuery('SELECT * FROM api_info WHERE API_KEY=\''.$api_key.'\' AND API_SECRET=\''.$api_secret.'\''));
if(count($validate) > 0)
{
//    $school_id=$_REQUEST['sch_id'];
    $school_info=DBGet(DBQuery('SELECT * FROM schools'));
    

    if($format == 'json') 
    {
        header('Content-type: application/json');
        $data=array();
        if(count($school_info)>0)
        {
            $i=1;
            foreach($school_info as $key=>$value)
            {
                foreach($value as $k=>$val)
                {
                    if($k!='LAST_UPDATED')
                    {
                        if($k!='UPDATED_BY')
                        {
                            if($k=='ID')
                                $data['data'][$i]['SCHOOL_ID']=$val;
                            else if($k=='SYEAR')
                                $data['data'][$i]['SCHOOL_YEAR']=$val;
                             else if($k=='TITLE')
                                $data['data'][$i]['SCHOOL_NAME']=$val;
                             else if($k=='WWW_ADDRESS')
                                $data['data'][$i]['URL']=$val;
                             else
                                 $data['data'][$i][$k]=$val;
                        }
                    }
                }
                $i++;
            }
             echo json_encode($data);
        }               
    }
    if($format == 'xml')
    {
        header('Content-type: text/xml');
        if(count($school_info)> 0)
        {
            echo '<data>';
            $i=1;
            foreach($school_info as $key=>$val)
            {
                echo'<SCHOOL_'.$i.'>';
                foreach ($val as $vkey => $value) {
                    if($vkey!='LAST_UPDATED')
                    {
                        if($vkey!='UPDATED_BY')
                        {
                            if($vkey=='ID')
                                 echo '<SCHOOL_ID>'.htmlentities($value).'</SCHOOL_ID>';
                            else if($vkey=='SYEAR')
                                 echo '<SCHOOL_YEAR>'.htmlentities($value).'</SCHOOL_YEAR>';
                            else if($vkey=='TITLE')
                                 echo '<SCHOOL_NAME>'.htmlentities($value).'</SCHOOL_NAME>';
                            else if($vkey=='WWW_ADDRESS')
                                 echo '<URL>'.htmlentities($value).'</URL>';
                            else
                    echo '<'.$vkey.'>'.htmlentities($value).'</'.$vkey.'>';
                        }
                    }
                }
                 echo'</SCHOOL_'.$i.'>';
                 $i++;
            }
            echo '</data>';
        }
    }
}

else
{
    $data['error']='Invalid Credentials.';
    if($format == 'json') 
    {
        header('Content-type: application/json');
        
        if(count($data)>0)
        {
             echo  json_encode($data);
        }
                      
    }
    if($format == 'xml')
    {
        header('Content-type: text/xml');
        echo '<error>Invalid Credentials.</error>';
    }
}
