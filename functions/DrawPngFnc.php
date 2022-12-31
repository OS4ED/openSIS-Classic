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
function DrawPNG($src,$extra='')
{
	if(strpos($_SERVER['HTTP_USER_AGENT'],"MSIE 6") || strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 5.5")) 
		$img .= "<img src=\"assets/pixel_trans.gif\" $extra style=\"filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='assets/".$src."');\" >";
	else
		$img .= '<img src="assets/'.$src.'" '.$extra.'>';
	
	return $img;
}
function DrawLogo()
{	                        
    
                        $sch_img_info= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE SCHOOL_ID='. UserSchool().' AND FILE_INFO=\'schlogo\''));
                        if(!$_REQUEST['new_school'] && count($sch_img_info)>0){
                            $image="<img src='data:image/jpeg;base64,".base64_encode($sch_img_info[1]['CONTENT'])."' width='60' style='float: left;' class='m-r-15 img-responsive' alt='Logo'/>";
//                        $logo_ret = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\''.  UserSchool().'\' AND program=\'SchoolLogo\''));    
//                        if($logo_ret && file_exists($logo_ret[1]['VALUE'])){
                            //$logo=$logo_ret[1]['VALUE'];
                            // $size = getimagesize($logo);
                            // $width=$size[0];
                            // $height=$size[1];
                            // $image='<img src="data:image/jpeg;base64,'.base64_encode($sch_img_info[1]['CONTENT']).'" width="60" style="float: left;" class="m-r-15" alt="Logo" />';
                        }
                         else {
                             $image= '<img src="assets/logo.png" width="60" style="float: left;" class="m-r-15" alt="Logo" />';
                        }

	return $image;
}
function DrawLogoReport()
{	                        
    
                        $sch_img_info= DBGet(DBQuery('SELECT * FROM user_file_upload WHERE SCHOOL_ID='. UserSchool().' AND FILE_INFO=\'schlogo\''));
                        if(!$_REQUEST['new_school'] && count($sch_img_info)>0){
//                            $image="<img src='data:image/jpeg;base64,".base64_encode($sch_img_info[1]['CONTENT'])."' class=img-responsive />";
//                        $logo_ret = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\''.  UserSchool().'\' AND program=\'SchoolLogo\''));    
//                        if($logo_ret && file_exists($logo_ret[1]['VALUE'])){
                            $logo=$logo_ret[1]['VALUE'];
                            $size = getimagesize($logo);
                            $width=$size[0];
                            $height=$size[1];
                            $image='<img src="data:image/jpeg;base64,'.base64_encode($sch_img_info[1]['CONTENT']).'" width="60" style="float: left;" class="m-r-15" alt="Logo" />';
                        }
                         else {
                             $image= '<img src="assets/logo.png" width="60" style="float: left;" class="m-r-15" alt="Logo" />';
                        }

	return $image;
}
function DrawLogoParam($param='')
{	                        $logo_ret = DBGet(DBQuery('SELECT VALUE FROM program_config WHERE school_id=\''.($param==''?UserSchool():$param).'\' AND program=\'SchoolLogo\''));    
                        if($logo_ret && file_exists($logo_ret[1]['VALUE'])){
                            $logo=$logo_ret[1]['VALUE'];
                            $size = getimagesize($logo);
                            $width=$size[0];
                            $height=$size[1];
                            $image='<img src="'.$logo.'" '.($width>100 && $height>100?($width>$height?($height>100 || $width>100?' width=100':''):($height>100 || $width>100?' height=100':'')):'').' alt="Logo" />';
                        }
                         else {
                             $image= '<img src="assets/logo.png" alt="Logo" />';
                        }

	return $image;
}
?>