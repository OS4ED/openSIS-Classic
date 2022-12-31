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
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>openSIS Installer</title>
        <link href="../assets/css/icons/fontawesome/styles.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/installer.css?v=<?php echo rand(000, 999); ?>" type="text/css" />
        <noscript><META http-equiv=REFRESH content='0;url=../EnableJavascript.php' /></noscript>
        <script src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/Validator.js"></script>
        <?php
        echo '<script type="text/javascript">
        var page=parent.location.href.replace(/.*\//,"");
        if(page && page!="index.php"){
                window.location.href="index.php";
                }

        </script>';
        ?>
    </head>
    <body class="outer-body">

        <section class="login">
            <div class="login-wrapper">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="logo">
                            <img src="assets/images/opensis_logo.png" alt="openSIS">
                        </div>
                        <h3>openSIS Installation - Site Admin Account Setup</h3>
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-instructions">
                                <ul class="installation-steps-label">
                                    <li>Choose Package</li>
                                    <li>System Requirements</li>
                                    <li>Database Connection</li>
                                    <li>Database Selection</li>
                                    <li>School Information</li>
                                    <li class="active">Site Admin Account Setup</li>
                                    <li>Ready to Go!</li>
                                </ul>
                                <!--<h4 class="no-margin">Installation Instructions</h4>
                                <p>Installer has successfully created your school.</p>
                                <p>The username and password you enter here will become the login credentials for the super administrator of the system.</p>
                                <p>Please make sure you give a strong alphanumeric password to prevent unauthorized access to the system.</p>-->
                            </div>
                            <div class="installation-steps">
                                <h4 class="m-t-0 m-b-5">Setup a Site Administrator Account</h4>

                                <div id="error" class="text-center">&nbsp;</div>
                                <form name='step4' id='step4' method="post" action="Ins4.php">
                                    <input type="hidden" id="auname_flag" value="2"/>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">First Name</label>
                                                <input type="text" name="fname" id="fname" size="20" tabindex="1" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Last Name</label>
                                                <input type="text" name="lname" id="lname" size="20" tabindex="2" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Middle Name</label>
                                                <input type="text" id="mname" name="mname" size="20" tabindex="3" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Email ID</label>
                                                <input type="text" name="emailid" id="emailid" size="20" tabindex="2" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Username</label>
                                                <input type="text" name="auname" id="auname" size="20" tabindex="4" onblur="check_username_install(this.value);" class="form-control"/>
                                                <div id="ucheck" style="font-weight:bold;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Password</label>
                                                <input type="password" id="apassword" name="apassword" size="20" tabindex="5" autocomplete="off" onkeyup="passwordStrength(this.value);" class="form-control" />
                                                <div class="help-block" style="font-size: 12px; height: 5px;"><span id="passwordStrengthWrap" style="display: none">Password Strength:</span> <span id="passwordStrength" style="font-weight:bold;"></span></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Confirm Password</label>
                                                <input type="password" id="capassword" name="capassword" size="20" tabindex="6" class="form-control" />
                                            </div>
                                        </div>
                                    </div>

                                    <hr/>
                                    <div class="text-right">
                                        <input type="submit" value="Save & Next"  class="btn btn-success" name="btninsert" onclick="return pass_check();" />
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <footer>
                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4ED</a>).
                </footer>
            </div>
        </section>

        <script language="JavaScript" type="text/javascript">

            function CheckPasswords()
            {
                var frm = document.forms["step4"];
                if (frm.apassword.value != frm.capassword.value)
                {
                    alert('The Password and Confirm Password does not match!');
                    frm.capassword.focus();
                    return false;
                }
                else
                {
                    return true;
                }
            }

            function passwordStrength(password)
            {
                document.getElementById("passwordStrength").style.display = "none";                
                document.getElementById("passwordStrengthWrap").style.display = "none";

                var desc = new Array();
                desc[0] = "Very Weak";
                desc[1] = "Weak";
                desc[2] = "Good";
                desc[3] = "Strong";
                desc[4] = "Strongest";

                //if password bigger than 7 give 1 point
                if (password.length > 0)
                {
                    $("#passwordStrengthWrap").show();
                    $("#passwordStrength").css({'display': 'inline-block', 'color': '#ff0000'});
                    $("#passwordStrength").html(desc[0]);
                }

                //if password has at least one number give 1 point
                if (password.match(/\d+/) && password.length > 5)
                {
                    $("#passwordStrengthWrap").show();
                    $("#passwordStrength").css({'display': 'inline-block', 'color': '#ff0000'});
                    $("#passwordStrength").html(desc[1]);
                }

                //if password has at least one special caracther give 1 point
                if (password.match(/\d+/) && password.length > 7 && password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))
                {
                    $("#passwordStrengthWrap").show();
                    $("#passwordStrength").css({'display': 'inline-block', 'color': '#8ed087'});
                    $("#passwordStrength").html(desc[2]);
                }


                //if password has both lower and uppercase characters give 1 point      

                if (password.match(/\d+/) && password.length > 10 && password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) && (password.match(/[A-Z]/)))
                {
                    $("#passwordStrengthWrap").show();
                    $("#passwordStrength").css({'display': 'inline-block', 'color': '#84b756'});
                    $("#passwordStrength").html(desc[3]);
                }


                //if password bigger than 12 give another 1 point

                if (password.match(/\d+/) && password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) && (password.match(/[a-z]/)) && (password.match(/[A-Z]/)) && password.length > 12)
                {
                    $("#passwordStrengthWrap").show();
                    $("#passwordStrength").css({'display': 'inline-block', 'color': '#43820b'});
                    $("#passwordStrength").html(desc[4]);
                }

            }

            function pass_check()
            {
                var fname = document.getElementById("fname");
                var lname = document.getElementById("lname");
                var mname = document.getElementById("mname");
                var emailid = document.getElementById("emailid");
                var auname = document.getElementById("auname");
                var apassword = document.getElementById("apassword");
                var capassword = document.getElementById("capassword");
                var auname_flag = document.getElementById("auname_flag");

                if (fname.value == '')
                {
                    document.getElementById("error").innerHTML = '<span class="text-danger">First name cannot be blank.</span>';

                    fname.focus();
                    return false;
                }
                else
                {
                    if (fname.value.length > 50)
                    {

                        document.getElementById('error').innerHTML = '<span class="text-danger">Max length for First name is 50 characters.</span>';
                        fname.focus();
                        return false;
                    }
                }
                if (lname.value == '')
                {
                    document.getElementById("error").innerHTML = '<span class="text-danger">Last name cannot be blank.</span>';

                    lname.focus();
                    return false;
                }
                else
                {
                    if (lname.value.length > 50)
                    {
                        document.getElementById('error').innerHTML = '<span class="text-danger">Max length for Last name is 50 characters.</span>';
                        lname.focus();
                        return false;
                    }
                }

                if (emailid.value == '')
                {
                    document.getElementById("error").innerHTML = '<span class="text-danger">Email ID cannot be blank.</span>';

                    emailid.focus();
                    return false;
                }
                else
                {
                    if (emailid.value.length > 100)
                    {
                        document.getElementById('error').innerHTML = '<span class="text-danger">Max length for Email ID is 100 characters.</span>';
                        emailid.focus();
                        return false;
                    }

                    var atSymbol = emailid.value.indexOf("@");
                    var dot = emailid.value.indexOf(".");

                    if (atSymbol < 1) {
                        document.getElementById('error').innerHTML = '<span class="text-danger">Please enter a valid email ID.</span>';
                        emailid.focus();
                        return false;
                    }

                    if (dot <= atSymbol + 2) {
                        document.getElementById('error').innerHTML = '<span class="text-danger">Please enter a valid email ID.</span>';
                        emailid.focus();
                        return false;
                    }

                    if (dot === emailid.value.length - 1) {
                        document.getElementById('error').innerHTML = '<span class="text-danger">Please enter a valid email ID.</span>';
                        emailid.focus();
                        return false;
                    }
                }

                if (mname.value.length > 50)
                {
                    document.getElementById('error').innerHTML = '<span class="text-danger">Max length for Middle name is 50 characters.</span>';
                    mname.focus();
                    return false;
                }
                if (auname.value.trim() == '')
                {
                    document.getElementById("error").innerHTML = '<span class="text-danger">Administrative username cannot be blank.</span>';

                    auname.focus();
                    return false;
                }
                else
                {
                    if (auname_flag.value == 1)
                    {
                        auname.focus();
                        return false;
                    }
                    else
                    {
                        if (auname.value.length > 50)
                        {
                            document.getElementById('error').innerHTML = '<span class="text-danger">Max length for Administrative username is 50 characters.</span>';
                            auname.focus();
                            return false;
                        }
                    }
                }
                if (apassword.value.trim() == '')
                {
                    document.getElementById("error").innerHTML = '<span class="text-danger">Administrative password cannot be blank.</span>';
                    auname.focus();
                    return false;
                }

                if (apassword.value == capassword.value)
                {
                    if (apassword.value.length < 7 || (apassword.value.length > 7 && !apassword.value.match((/\d+/))) || (apassword.value.length > 7 && !apassword.value.match((/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))))
                    {
                        document.getElementById("error").innerHTML = '<span class="text-danger">Password should be minimum 8 characters with atleast one number and one special character.</span>';
                        return false;
                    }

                    return true;
                }
                else
                {
                    document.getElementById("error").innerHTML = '<span class="text-danger">Confirm password mismatch.</span>';
                    capassword.focus();
                    return false;
                }


                return true;
            }
            function check_username_install(username)
            {
                if (username != '' && username.toLowerCase() != 'os4ed')
                    ajax_call('UsernameCheckOthers.php?username=' + username, check_username_install_callback, check_username_install_error);
                if (username.toLowerCase() == 'os4ed')
                {
                    document.getElementById("auname_flag").value = 2;
                    document.getElementById('error').style.color = '#008800';
                    document.getElementById('error').innerHTML = 'Username available';
                }
            }
            function check_username_install_callback(data) {


                var obj = document.getElementById('error');
                if (data == '1')
                {
                    obj.style.color = '#ff0000';
                    obj.innerHTML = 'Username already taken';
                    document.getElementById('auname_flag').value = '1';
                }

                if (data == '0')
                {
                    obj.style.color = '#008800';
                    obj.innerHTML = 'Username available';
                    document.getElementById('auname_flag').value = '2';
                }
            }
            function check_username_install_error(err)
            {
                alert("Error: " + err);
            }

            function ajax_call(url, callback_function, error_function) {

                var xmlHttp = null;
                try {
                    // for standard browsers
                    xmlHttp = new XMLHttpRequest();
                } catch (e) {
                    // for internet explorer
                    try {
                        xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
                    } catch (e) {
                        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                }
                xmlHttp.onreadystatechange = function () {
                    if (xmlHttp.readyState == 4)
                        try {
                            if (xmlHttp.status == 200) {

                                callback_function(xmlHttp.responseText);
                            }
                        } catch (e) {

                            error_function(e.description);
                        }
                }

                xmlHttp.open("GET", url);
                xmlHttp.send(null);
            }
        </script>
    </body>
</html>
