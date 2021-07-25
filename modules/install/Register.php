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
		error_reporting(0);
		session_start();
		$_SESSION['db']=$_POST["sdb"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<link rel="stylesheet" href="../styles/Installer.css" type="text/css" />
<script type="text/javascript" src="js/Validator.js"></script>
</head>
<body>
<div class="heading">Registration
  <div style="height:270px; vertical-align:middle">
    <form name='step3' id='step3' method="post" action="Step5.php">
      <table border="0" cellspacing="6" cellpadding="3" align="center" style="margin-top:30px">
        <tr>
          <td align="center"><strong style="font-size:13px;">Do you want to register?</strong></td>
        </tr>
        <tr>
          <td align="center" valign="top"><table width="245" border="0" cellpadding="4" cellspacing="0" id="table1">
              <tr>
                <td><table border="0" cellspacing="0" cellpadding="0" align="center">
                    <tr>
                      <td><input name="option" type="radio" value="Yes" /></td>
                      <td>Yes</td>
                      <td>&nbsp;</td>
                      <td><input name="option" type="radio" value="No" /></td>
                      <td>No</td>
                    </tr>
                  </table></td>
              </tr>
			  <tr><td class="clear"></td></tr>
              <tr>
                <td align="center"><input type="submit" value="Continue" class="btn_wide" /></td>
              </tr>
            </table>
            <script language="JavaScript" type="text/javascript">
				
				function CheckYear()
				{
					  var frm = document.forms["step3"];
					  if(frm.syear.value <2000)
						{
							alert('The year should start from 2000');
							frm.syear.focus();
							return false;
						  }
						  else
						  {
							return true;
						  }
				}
				
					var frmvalidator  = new Validator("step3");
					frmvalidator.addValidation("syear","req","Please enter the System Year");
					  frmvalidator.addValidation("syear","maxlen=4", "Maximum length of year is 4");
					  frmvalidator.addValidation("syear","numeric");
					  frmvalidator.setAddnlValidationFunction("CheckYear");
				</script>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
</body>
</html>
