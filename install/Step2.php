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
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,400italic,600italic" rel="stylesheet" type="text/css">
        <link href="../styles/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
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
                        <div class="row">
                            <div class="col-xs-8 text-left">
                                <div class="logo">
                                    <img src="assets/images/opensis_logo.png" alt="openSIS">
                                </div>
                                <h3>Step 2 of 5</h3>
                            </div>
                            <div class="col-xs-4 text-center" style="padding: 30px 20px 0;">
                                Installation Progress
                                <div class="progress no-margin">
                                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="installation-steps-wrapper">
                            <div class="installation-steps">
                                <h2 class="text-center">Connected to MySQL DBMS</h2><br/>
                                <div id="calculating" class="loading clearfix"><i class="fa fa-cog fa-spin fa-lg fa-fw"></i> Creating Database. Please wait...</div>
                                <?php if ($_REQUEST['err']) { ?>
                                    <script type='text/javascript'>
                                        swal({
                                            title: 'Oops!',
                                            text: '<?php echo $_REQUEST['err']; ?>',
                                            type: 'error',
                                            confirmButtonText: 'Close'
                                        });
                                    </script>
                                <?php } ?>
                                <form name='step2' id='step2' method="post" action="Ins2.php">
                                    <table border="0" cellspacing="6" cellpadding="3" align="center">
                                        <tr>
                                            <td align="center" valign="top" style="padding-top: 0">System needs to create a new database.<br />
                                                (This could take up to a minute to complete)<br />
                                                Please enter a name.</td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top" style="padding-top: 15px;">
                                                <div class="form-group">
                                                    <input type="text" name="db" id="db" size="20" value="opensis" class="form-control"  />
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top">
                                                <div class="form-group">
                                                    <label class="checkbox-inline"><input type="checkbox" name="purgedb" value="opensis" /> Remove data from existing database</label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <input type="submit" value="Save & Next" class="btn btn-primary" name="Add_DB" onClick="return db_validate();"  />
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                            <div class="installation-instructions">
                                <h4 class="no-margin">Installation Instructions</h4>
                                <p>Installer has successfully connected to MySQL.</p>
                                <p>It is a good practice to name the database &quot;opensis&quot; so that it can be identified easily if you have many databases running in the same server.</p>
                                <p>Remember the database creation takes some time, so please be patient and do not close the browser.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <footer>
                    Copyright &copy; Open Solutions for Education, Inc. (<a href="http://www.os4ed.com">OS4Ed</a>).
                </footer>
            </div>
        </section>
        <script language="JavaScript" type="text/javascript">

            function db_validate()
            {
                var db_name = document.getElementById('db');
                if (db_name.value.trim() == '')
                {
                    document.getElementById("error").innerHTML = '<font style="color:red"><b>Database name cannot be blank</b></font>';
                    db_name.focus();
                    return false;
                }
                else {
                    document.getElementById('calculating').style.display = 'block';
                    document.getElementById('step_container').style.display = 'none';
                }
            }
        </script>
    </body>
</html>
