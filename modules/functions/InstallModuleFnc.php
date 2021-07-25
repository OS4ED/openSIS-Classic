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
function install_module($dir, $filename)
{
 	$app_ver =DBGet(DBQuery('select * from app'));
	$ver = $app_ver[1]['VALUE'];
	$bld = $app_ver[4]['VALUE'];

if (file_exists($dir."/".$filename)) {
    require($dir."/".$filename);
    
    if($version && $version==$ver)
	{		 //Starting our code here
		 	//Installing the SQL
			if($sql && file_exists($dir."/".$sql))
			{
				$myFile = $dir."/".$sql;
				$rsql = file_get_contents($myFile );
				DBQuery($rsql);
				unlink($myFile);
			}
						
			//Coping the files
			if($files)
			$cnt = count($files);
			
			if($cnt > 0)
			{
				for($i=0; $i<$cnt; $i++)
				{
				 	if(file_exists($dir."/".$files[$i]))
				 	{
					rename($files[$i], $files[$i].".".$ver);
					copy($dir."/".$files[$i], $files);
					unlink($dir."/".$files[$i]);					
					echo "Successfully changed ".$files[$i]." <br>";
					}
					else
					echo "The requested file ".$files[$i]." is not available with the update. <br>";
				}
			}
			
			//For Coping Dirs
			if($dirs)
			$cnt = count($dirs);
			
			if($cnt > 0)
			{
				for($i=0; $i<$cnt; $i++)
				{
						$install = @ dir($dir."/".$dirs[$i]);
				 while (($file = $install->read()) !== false)
					{	
					 	if(file_exists($dirs[$i]."/".$file))
					 	{
							rename($dirs[$i]."/".$file, $dirs[$i]."/".$file.".".$ver);
							copy($dir."/".$dirs[$i]."/".$file, $dirs[$i]."/".$file);
							unlink($dir."/".$dirs[$i]."/".$file);					
							echo "Successfully changed ".$dirs[$i]."/".$file." <br>";
						}
						else
						{
							copy($dir."/".$dirs[$i]."/".$file, $dirs[$i]."/".$file);
							unlink($dir."/".$dirs[$i]."/".$file);					
							echo "Successfully copied ".$dirs[$i]."/".$file." <br>";
						}
					}
					$install->close();
					rmdir($dir."/".$dirs[$i]);
				}
			}
			
			$sql = 'update app set value=\''.date('l F d, Y').'\' where name=\'last_updated\'';
			DBQuery($sql);
			unlink($dir."/".$filename);
			
			echo "<br><br><b>All the updates are installed successfully. Please restart the application to take effect</b>";
		
	}
	else
	echo "Version mismatch";

} else {
    echo "<b>The file <font color=red>$filename</font> does not exist or corruped update</b>";
}
}
?>