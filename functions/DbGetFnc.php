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
/*
	Send it an SQL Select Query Identifier and an optional functions array where the key is the
	column in the database that the function (contained in the value of the array) is applied.

	Use the second parameter (an array of functions indexed by the column to apply them to)
	if you need to do complicated formatting and don't want to loop through the
	array before sending it to ListOutput.  Use especially when expecting a large result.

	$THIS_RET is a useful variable for the functions in the second parameter.  It is the current row of the
	query result.

	Furthermore, the third parameter can be used to change the array index to a column in the
	result.  For instance, if you selected student_id from students, and chose to index by student_id,
	you would get a result similar to this :
	$array[1031806][1] = array('STUDENT_ID'=>'1031806');

	The third parameter should be an array -- ordered by the importance of the index.  So, if you select
	COURSE_ID,COURSE_WEIGHT,COURSE_PERIOD_ID from COURSE_PERIODS, and choose to index by
	array('COURSE_ID','COURSE_WEIGHT','COURSE_PERIOD_ID') then you will be returned an array formatted like this:
	$array[10101][1][402345][1] = array('COURSE_ID'=>'10101','COURSE_WEIGHT'=>'1','COURSE_PERIOD_ID'=>'402345')
*/
function DBGet($QI, $functions = array(),$index = array())
{
	global $THIS_RET;

	$index_count = is_countable($index) ? count($index) : false;
	$tmp_THIS_RET = $THIS_RET;

	$results = array();
	while ($RET = db_fetch_row($QI)) {
		$THIS_RET = $RET;
		if ($index_count) {
			$ind = '';
			foreach ($index as $col)
				$ind .= "['" . str_replace("'", "\'", $THIS_RET[$col]) . "']";
			eval('$s' . $ind . '++;$this_ind=$s' . $ind . ';');
		} else{
			if(isset($s))$s++; // 1-based if no index specified
			else $s=1;
		}
		foreach ($RET as $key => $value) {
			if ($functions[$key] && function_exists($functions[$key])) {
				if ($index_count)
					eval('$results' . $ind . '[$this_ind][$key] = $functions[$key]($value,$key);');
				else
					$results[$s][$key] = $functions[$key]($value, $key);
			} else {
				if ($index_count)
					eval('$results' . $ind . '[$this_ind][$key] = $value;');
				else
					$results[$s][$key] = $value;
			}
		}
	}

	$THIS_RET = $tmp_THIS_RET;

	return $results;
}
