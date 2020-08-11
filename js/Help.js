
var hh=0;
var inter;
var MSIE = navigator.userAgent.indexOf('MSIE')>=0?true:false;

function ShowBox(obj, height, width, left, top, obj1)
{
	if(hh>=height)
	{
	clearInterval(inter);
	return;
	}

	obj.style.visibility = 'visible';
	hh+=10;
	obj.style.height = hh + 'px';
	obj.style.width = width + 'px';
	if(MSIE)
	{
		obj.style.left = ((screen.width/2)-85) + 'px';
		obj.style.top = top+4 + 'px';
	}
	else
	{
		obj.style.top = top + 'px';
		obj.style.left = ((screen.width/2)-85) + 'px';
	}
	Hide(obj1);
}

function HideBox(obj, obj1)
{
	if(hh==10)
	{
	obj.style.visibility = 'hidden';
	obj.style.height = '0.1em';
	clearInterval(inter);
	Show(obj1);
	return;
	}
	hh-=10;
	obj.style.height = hh + 'px';
}

function Hide(obj)
{
	obj.style.visibility = 'hidden';
}

function Show(obj)
{
	obj.style.visibility = 'visible';
}
