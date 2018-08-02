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
// If there are missing vals or similar, show them a msg.
//
// Pass in an array with error messages and this will display them
// in a standard fashion.
//
// in a program you may have:
/*
  if(!$sch)
  $error[]="School not provided.";
  if($count == 0)
  $error[]="Number of students is zero.";
  ErrorMessage($error);
 */

// (note that array[], the brackets with nothing in them makes
// PHP automatically use the next index.
// Why use this?  It will tell the user if they have multiple errors
// without them having to re-run the program each time finding new
// problems.  Also, the error display will be standardized.
// If a 2ND is sent, the list will not be treated as errors, but shown anyway
function ErrorMessage($errors, $code = 'error') {
    $errors = array_unique($errors);
    if ($errors) {
        if (count($errors) == 1) {
            if ($code == 'error' || $code == 'fatal' || $code == 'note')
                $return .= '<div class="alert alert-warning no-border">';
            else
                $return .= '<div class="alert alert-danger no-border">';
            $return .= ($errors[0] ? $errors[0] : $errors[1]);
        }
        else {
            if ($code == 'error' || $code == 'fatal' || $code == 'note')
                $return .= '<div class="alert alert-warning no-border">';
            else
                $return .= '<div class="alert alert-danger no-border">';
            $return .= '<ul>';
            foreach ($errors as $value)
                $return .= "<li>$value</li>\n";
            $return .= '</ul>';
        }
        $return .= "</div>";

        if ($code == 'fatal') {
            $css = getCSS();
            if (User('PROFILE') != 'teacher') {
                $return .= "<table width=\"100%\" border=\"0\">
								<tr>
    <td align='center' class='copyright'>
       <center>openSIS is a product of Open Solutions for Education, Inc. (<a href='http://www.os4ed.com' target='_blank'>OS4ED</a>) and is licensed under the <a href='http://www.gnu.org/licenses/gpl.html' target='_blank'>GPL license</a>.
                </center></td>
  </tr>
								</table>
								</td>
								</tr>
								</table>";
            }
            if ($isajax == "")
                echo $return;
            if (!$_REQUEST['_openSIS_PDF'])
                Warehouse('footer');
            exit;
        }


        return $return;
    }
}

function ErrorMessage1($errors, $code = 'error') {

    if ($errors) {
        if (count($errors) == 1) {
            if ($code == 'error' || $code == 'fatal')
                $return .= '<div class="alert alert-warning no-border">';
            else
                $return .= '<div class="alert alert-danger no-border">';
            $return .= ($errors[0] ? $errors[0] : $errors[1]);
        }
        else {
            if ($code == 'error' || $code == 'fatal')
                $return .= '<div class="alert alert-warning no-border">';
            else
                $return .= '<div class="alert alert-danger no-border">';
            foreach ($errors as $value)
                $return .= "<li>$value</li>\n";
            $return .= '</ul>';
        }
        $return .= "</div>";

        if ($code == 'fatal') {
            $css = getCSS();
            $return .= "<table width=\"100%\" border=\"0\">
							<tr>
								<td align='center' class='copyright'>
								   <center>openSIS is a product of Open Solutions for Education, Inc. (<a href='http://www.os4ed.com' target='_blank'>OS4ED</a>) and is licensed under the <a href='http://www.gnu.org/licenses/gpl.html' target='_blank'>GPL license</a>.
											</center></td>
							  </tr>
							</table>
							</td>
							</tr>
							</table>";
            if ($isajax == "")
                if (!$_REQUEST['_openSIS_PDF'])
                    Warehouse('footer');
            exit;
        }

        return $return;
    }
}

?>
