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
function DeCodeds($value,$column)
{	global $_openSIS;
	$field = substr($column,7);
	if(!$_openSIS['DeCodeds'][$field])
	{
		$select_options = DBGet(DBQuery('SELECT SELECT_OPTIONS FROM custom_fields WHERE ID=\''.$field.'\''));
		$select_options = str_replace("\n","\r",str_replace("\r\n","\r",$select_options[1]['SELECT_OPTIONS']));
		$select_options = explode("\r",$select_options);
		foreach($select_options as $option)
		{
			$option = explode('|',$option);
			if($option[0]!='' && $option[1]!='')
				$options[$option[0]] = $option[1];
		}
		if(count($options))
			$_openSIS['DeCodeds'][$field] = $options;
		else
			$_openSIS['DeCodeds'][$field] = true;
	}

	if($value!='')
		if($_openSIS['DeCodeds'][$field][$value]!='')
			return $_openSIS['DeCodeds'][$field][$value];
		else
			return "<FONT color=red>$value</FONT>";
	else
		return '';
}

function cleanParamMod($param)
{
$return='';
  $pa_arr=explode('P',$param);
 foreach($pa_arr as $val)
  {
      
      $return.=chr($val/3);
  }

  return $return;

}
?>
