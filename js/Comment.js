function InsertComment(txt, img)
{
 var MSIE = navigator.userAgent.indexOf('MSIE')>=0?true:false;
 var navigatorVersion = navigator.appVersion.replace(/.*?MSIE ([0-9]\.[0-9]).*/g,'$1')/1;
 var sel = document.getElementsByTagName('select');
 if(MSIE && navigatorVersion<=6)
  {
	for(var i=0; i<sel.length; i++)
	{
      sel[i].style.visibility="hidden";
	}
  }
var obj = document.getElementById('dc');
obj.style.top = (zxcPos(document.getElementById(txt))[1] -155) + 'px'
obj.style.left = 300;
obj.innerHTML = '<br><br><table width=100%><tr><td align=center><textarea id="tempid" rows="6" name="S1" cols="49" style="width:450px; height:210px;">' + document.getElementById(txt).value + '</textarea></td></tr><tr><td align=center><input type=button class=btn_medium value="OK" onclick="InsertIntoInput(\''+txt+'\');"></td></tr></table>';
obj.style.visibility='visible';
}

function InsertIntoInput(txt)
{
  var sel = document.getElementsByTagName('select');
  for(var i=0; i<sel.length; i++)
  {
	sel[i].style.visibility="visible";
  }
try
{
document.getElementById(txt).value = document.getElementById('tempid').value
document.getElementById('dc').style.visibility='hidden';
}
catch(e)
{
alert(e);
}
}

function zxcPos(zxcobj){
 zxclft=zxcobj.offsetLeft;
 zxctop=zxcobj.offsetTop;
 while(zxcobj.offsetParent!=null){
  zxcpar=zxcobj.offsetParent;
  zxclft+=zxcpar.offsetLeft;
  zxctop+=zxcpar.offsetTop;
  zxcobj=zxcpar;
 }
 return [zxclft,zxctop];
}