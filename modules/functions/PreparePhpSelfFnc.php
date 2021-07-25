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
function PreparePHP_SELF($tmp_REQUEST='')
{
	if(!$tmp_REQUEST)
		$tmp_REQUEST = $_REQUEST;
	foreach($_COOKIE as $key=>$value)
		unset($tmp_REQUEST[$key]);
	
	$PHP_tmp_SELF = 'Modules.php?modname=' . $tmp_REQUEST['modname'];
	
	unset($tmp_REQUEST['modname']);
		if(count($tmp_REQUEST))
	{

        
		foreach($tmp_REQUEST as $key=>$value)
		{
			if(is_array($value))
			{
				foreach($value as $key1=>$value1)
				{
					if(is_array($value1))
					{
						foreach($value1 as $key2=>$value2)
						{	
							if(is_array($value2))
							{
								foreach($value2 as $key3=>$value3)
								{
									$PHP_tmp_SELF .= "&amp;".$key.'['.$key1.']['.$key2.']['.$key3.']='.str_replace('\"','"',$value3);
								}
							}
							else
								$PHP_tmp_SELF .= "&amp;".$key.'['.$key1.']['.$key2.']='.str_replace('\"','"',$value2);
						}
					}
					else
						$PHP_tmp_SELF .= "&amp;".$key.'['.$key1.']='.str_replace('\"','"',$value1);
				}
			}
			else
			{
				if($tmp_REQUEST[$key] != '')
				{
					$PHP_tmp_SELF .= "&amp;" . $key . "=" . str_replace('\"','"',$value);
					
				}
			}
		}
	}
	
	return str_replace(' ','+',$PHP_tmp_SELF);
}

function PreparePHP_SELF1($tmp_REQUEST='')
{
	if(!$tmp_REQUEST)
		$tmp_REQUEST = $_FILES;

	foreach($_COOKIE as $key=>$value)
		unset($tmp_REQUEST[$key]);
	
//	$PHP_tmp_SELF = 'Modules.php?modname=' . $tmp_REQUEST['modname'];
	
	unset($tmp_REQUEST['modname']);
	
	if(count($tmp_REQUEST))
	{

        
		foreach($tmp_REQUEST as $key=>$value)
		{
			if(is_array($value))
			{
				foreach($value as $key1=>$value1)
				{
					if(is_array($value1))
					{
						foreach($value1 as $key2=>$value2)
						{	
							if(is_array($value2))
							{
								foreach($value2 as $key3=>$value3)
								{
									$PHP_tmp_SELF .= "&amp;".$key.'['.$key1.']['.$key2.']['.$key3.']='.str_replace('\"','"',$value3);
								}
							}
							else
								$PHP_tmp_SELF .= "&amp;".$key.'['.$key1.']['.$key2.']='.str_replace('\"','"',$value2);
						}
					}
					else
						$PHP_tmp_SELF .= "&amp;".$key.'['.$key1.']='.str_replace('\"','"',$value1);
				}
			}
			else
			{
				if($tmp_REQUEST[$key] != '')
				{
					$PHP_tmp_SELF .= "&amp;" . $key . "=" . str_replace('\"','"',$value);
					
				}
			}
		}
	}
	
	return str_replace(' ','+',$PHP_tmp_SELF);
}
?>