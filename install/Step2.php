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
echo '<script type="text/javascript">
var page=parent.location.href.replace(/.*\//,"");
if(page && page!="index.php"){
	window.location.href="index.php";
	}

</script>';
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
        <link rel="stylesheet" type="text/css" href="assets/sweetalert2/css/sweetalert2.css">
        <link rel="stylesheet" href="assets/css/installer.css?v=<?php echo rand(000, 999); ?>" type="text/css" />
        <noscript><META http-equiv=REFRESH content='0;url=../EnableJavascript.php' /></noscript>
        <script src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/Validator.js"></script>
        <script src="assets/sweetalert2/js/sweetalert2.min.js"></script>
    </head>
    <body class="outer-body">
        <section class="login">
            <div class="login-wrapper">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="logo">
                            <img src="assets/images/opensis_logo.png" alt="openSIS">
                        </div>
                        <h3>openSIS Installation - Database Selection</h3>
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-instructions">
                                <ul class="installation-steps-label">
                                    <li>Choose Package</li>
                                    <li>System Requirements</li>
                                    <li>Database Connection</li>
                                    <li class="active">Database Selection</li>
                                    <li>School Information</li>
                                    <li>Site Admin Account Setup</li>
                                    <li>Ready to Go!</li>
                                </ul>
                                <!--<h4 class="no-margin">Installation Instructions</h4>
                                <p>Installer has successfully connected to MySQL.</p>
                                <p>It is a good practice to name the database &quot;opensis&quot; so that it can be identified easily if you have many databases running in the same server.</p>
                                <p>Remember the Database Selection takes some time, so please be patient and do not close the browser.</p>-->
                            </div>
                            <div class="installation-steps">
                                <h4 class="m-t-0 m-b-5">System needs a new database</h4>
                                <p class=" m-b-20 text-muted">(This could take up to a minute or two to complete)</p>
                                <div id="error" class="m-b-5"></div>
                                <div id="calculating" class="loading clearfix"><div><i class="fa fa-cog fa-spin fa-lg fa-fw"></i> Preparing Database. Please wait...</div></div>
                                <?php if (isset($_REQUEST['err'])) { ?>
                                    <script type='text/javascript'>
                                        swal({
                                            title: 'Oops!',
                                            text: '<?php echo $_REQUEST['err']; ?>',
                                            type: 'error',
                                            confirmButtonText: 'Close'
                                        }).then(function (){
                                                history.back();
                                            });
                                    </script>
                                <?php } ?>
                                <form name='step2' id='step2' method='post' onsubmit='return db_validate()' action='Ins2.php'>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Database Name</label>
                                                <input type="text" name="db" id="db" size="20" value="opensis" class="form-control"  />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">&nbsp;</label>
                                                <div class="m-t-0">
                                                    <label class="radio-inline"><input type="radio" name="data_choice" value="purgedb" /> Remove data from existing database</label>
                                                    <br>
                                                    <label class="control-label m-0">OR</label>
                                                    <br>
                                                    <label class="radio-inline"><input type="radio" name="data_choice" value="newdb" /> Create new database</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr/>
                                    <div class="text-right">
                                        <input type="submit" value="Save & Next" class="btn btn-success" name="Add_DB" />
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

            function db_validate()
            {
	        //sessionStorage.setItem("step_2_complete", false);
                var db_name = document.getElementById('db').value;
                if (db_name.trim() != ''){
                    document.getElementById('calculating').style.display = 'block';
                    document.getElementById('step_container').style.display = 'none';
                }
            }
        </script>
    </body>
</html>
