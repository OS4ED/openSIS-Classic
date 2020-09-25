<?php
include('../../RedirectModulesInc.php');
DrawBC(""._schoolSetup." > ".ProgramTitle());
if(($_REQUEST['action'] == 'update') && ($_REQUEST['button']=='Save') && User('PROFILE')=='student')
{
	$stu_PASS=DBGet(DBQuery('SELECT PASSWORD FROM students WHERE STUDENT_ID=\''.UserStudentId().'\''));
	$pass_old=$_REQUEST['old'];
	if($pass_old=="")
	 {
	   $error[] = _pleaseTypeThePassword;
	   echo ErrorMessage($error,'Error');
	 }
	 else
	 {
	  $pass_old = str_replace("\'","''",md5($_REQUEST['old']));
	  $pass_new = str_replace("\'","''",md5($_REQUEST['new']));
	  $pass_retype = str_replace("\'","''",md5($_REQUEST['retype']));
	  if($stu_PASS[1]['PASSWORD']==$pass_old)
	   {
	 	if($pass_new==$pass_retype)
		 {
	 	  $sql='UPDATE students SET PASSWORD=\''.$pass_new.'\' WHERE STUDENT_ID=\''.UserStudentId().'\'';
		  DBQuery($sql);
		  $note[] = _passwordSucessfullyChanged;
	 	    echo ErrorMessage($note,'note');
		 }
		else
		 {
		 	$error[] = _pleaseRetypePassword;
	 	    echo ErrorMessage($error,'Error');
		 }
	 }
	  else
	   {
	 	$error[] = _passwordDoesNtExist;
	 	echo ErrorMessage($error,'Error');
	 }
	 }
	
}


echo "<span id='error' name='error'></span>";

PopTable('header', _changePassword);
echo "<FORM name=change_password id=change_password action=Modules.php?modname=$_REQUEST[modname]&action=update method=POST>";
echo "<table border=0 width=350px><tr><td><table border=0 cellpadding=4 align=center >";
echo "<tr><td align='right'><strong>"._oldPassword." :</strong> </td><td><INPUT type=password class=cell_floating name=old AUTOCOMPLETE = �off�></td></tr>";
echo "<tr><td align='right'><strong>"._newPassword." :</strong> </td><td><INPUT type=password class=cell_floating name=new AUTOCOMPLETE = �off�></td></tr>";
echo "<tr><td align='right'><strong>"._retypePassword." :</strong> </td><td><INPUT type=password class=cell_floating name=retype AUTOCOMPLETE = �off�></td></tr>";
echo "</table></td></tr></table>";
DrawHeader('','',"<INPUT TYPE=SUBMIT name=button id=button class='btn btn-primary' VALUE='"._save."' onclick='return change_pass();'></CENTER>");
echo "</FORM>";
PopTable('footer');

?>