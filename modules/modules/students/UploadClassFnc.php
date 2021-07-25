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
class upload{

var $target_path;
var $destination_path;
var $name;
var $fileExtension;
var $fileSize;
var $allowExtension=array("jpg","jpeg","png","gif","bmp");
var $wrongFormat=0;
var $wrongSize=0;
function deleteOldImage($id=''){
    if($id!='')
    {
        DBQuery('DELETE FROM user_file_upload WHERE ID='.$id);
}
//if(file_exists($this->target_path))
//	unlink($this->target_path);
}

function setFileExtension(){
$this->fileExtension=strtolower(substr($this->name,strrpos($this->name,".")+1));
}

function validateImageSize(){
if($this->fileSize > 10485760){
$this->wrongSize=1;
}
}

function validateImage(){
if(!in_array($this->fileExtension, $this->allowExtension)){
$this->wrongFormat=1;
}
}
function get_file_extension($file_name) {
return end(explode('.',$file_name));
}
}
?>