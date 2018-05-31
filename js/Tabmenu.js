	var menuAlignment = 'left';	
	var topMenuSpacer = 0; 
	var activateSubOnClick = true; 
	var leftAlignSubItems = true; 
	var time1, time2;
	
	var activeMenuItem = false;	
	var activeTabIndex = 0;	
	var rememberActiveTabByCookie = true;
	
	var MSIE = navigator.userAgent.indexOf('MSIE')>=0?true:false;
	var Opera = navigator.userAgent.indexOf('Opera')>=0?true:false;
	var navigatorVersion = navigator.appVersion.replace(/.*?MSIE ([0-9]\.[0-9]).*/g,'$1')/1;
	
	function Get_Cookie(name) { 
	   var start = document.cookie.indexOf(name+'='); 
	   var len = start+name.length+1; 
	   if ((!start) && (name != document.cookie.substring(0,name.length))) return null; 
	   if (start == -1) return null; 
	   var end = document.cookie.indexOf(';',len); 
	   if (end == -1) end = document.cookie.length; 
	   return unescape(document.cookie.substring(len,end)); 
	} 
	function Set_Cookie(name,value,expires,path,domain,secure) { 
		expires = expires * 60*1000;
		var today = new Date();
		var expires_date = new Date( today.getTime() + (expires) );
	    var cookieString = name + '=' +escape(value) + 
	       ( (expires) ? ';expires=' + expires_date.toGMTString() : '') + 
	       ( (path) ? ';path=' + path : '') + 
	       ( (domain) ? ';domain=' + domain : '') + 
	       ( (secure) ? ';secure' : ''); 
	    document.cookie = cookieString; 
	}	
	
	function dissub(tab)
	{
		document.getElementById(tab).style.display='block';
	}
	
	function hidsub(tab)
	{
		clearTimeout(time2);
		document.getElementById(tab).style.display='none';
	}
	
	function showHide()
	{
		if(activeMenuItem){
			clearTimeout(time1);
			activeMenuItem.className = 'inactiveMenuItem'; 	
			var theId = activeMenuItem.id.replace(/[^0-9]/g,'');
			var sis = 'submenu_'+theId;
			time2 = window.setTimeout('hidsub(\'' + sis + '\')', 390);
			var img = activeMenuItem.getElementsByTagName('IMG');
			if(img.length>0)img[0].style.display='none';			
		}

		var img = this.getElementsByTagName('IMG');
		if(img.length>0)img[0].style.display='inline';
		
		activeMenuItem = this;		
		this.className = 'activeMenuItem';
		var theId = this.id.replace(/[^0-9]/g,'');
		var sis = 'submenu_'+theId;
		time1 = window.setTimeout('dissub(\'' + sis + '\')', 400);

				
		if(rememberActiveTabByCookie){
			Set_Cookie('dhtmlgoodies_tab_menu_tabIndex','index: ' + (theId-1),30);
		}
		
	}
	
	function roll(img_name1, img_src1)
   {
   document[img_name1].src = img_src1;
   }

	
	
	function ShowsubMenu()
	{
		if(activeMenuItem){
			activeMenuItem.className = 'inactiveMenuItem'; 	
			var theId = activeMenuItem.id.replace(/[^0-9]/g,'');
			document.getElementById('submenu_'+theId).style.display='none';
			var img = activeMenuItem.getElementsByTagName('IMG');
			if(img.length>0)img[0].style.display='none';			
		}

		var img = this.getElementsByTagName('IMG');
		if(img.length>0)img[0].style.display='inline';
				
		activeMenuItem = this;		
		this.className = 'activeMenuItem';
		var theId = this.id.replace(/[^0-9]/g,'');
		document.getElementById('submenu_'+theId).style.display='block';
	}
	
	function initMenu()
	{
	 try
	 {
	 
		var mainMenuObj = document.getElementById('cdnavheader');
		var menuItems = mainMenuObj.getElementsByTagName('A');
		if(document.all){
		 
		}		
		if(rememberActiveTabByCookie){
			var cookieValue = Get_Cookie('dhtmlgoodies_tab_menu_tabIndex') + '';
			cookieValue = cookieValue.replace(/[^0-9]/g,'');
			if(cookieValue.length>0 && cookieValue<menuItems.length){
				activeTabIndex = cookieValue/1;
			}	
	}
			
		var currentLeftPos = 15;
		for(var no=0;no<menuItems.length;no++){			
			if(activateSubOnClick)menuItems[no].onclick = showHide; else menuItems[no].onmouseover = showHide;
			
			menuItems[no].id = 'cdnavheaderItem' + (no+1);
			if(menuAlignment=='left')
				menuItems[no].style.left = currentLeftPos + 'px';
			else
				menuItems[no].style.right = currentLeftPos + 'px';
			currentLeftPos = currentLeftPos + menuItems[no].offsetWidth + topMenuSpacer; 
			
			var img = menuItems[no].getElementsByTagName('IMG');
			if(img.length>0){
				img[0].style.display='none';
				if(MSIE && Safari && !Opera && navigatorVersion<7){
					img[0].style.bottom = '-1px';
					img[0].style.right = '-1px';
				}
			}
						
			if(no==activeTabIndex){
				menuItems[no].className='activeMenuItem';
				activeMenuItem = menuItems[no];
				var img = activeMenuItem.getElementsByTagName('IMG');
				if(img.length>0)img[0].style.display='inline';	
							
			}else menuItems[no].className='inactiveMenuItem';
			if(!document.all)menuItems[no].style.bottom = '-1px';
			if(MSIE && navigatorVersion < 6)menuItems[no].style.bottom = '-2px';
			

		}		
		
		var mainMenuLinks = mainMenuObj.getElementsByTagName('A');
		
		var subCounter = 1;
		var parentWidth = mainMenuObj.offsetWidth;
		while(document.getElementById('submenu_' + subCounter)){
			var subItem = document.getElementById('submenu_' + subCounter);
			
			if(leftAlignSubItems){
			}else{							
				var leftPos = mainMenuLinks[subCounter-1].offsetLeft;
				document.getElementById('submenu_'+subCounter).style.paddingLeft =  leftPos + 'px';
				subItem.style.position ='absolute';
				if(subItem.offsetWidth > parentWidth){
					leftPos = leftPos - Math.max(0,subItem.offsetWidth-parentWidth); 	
				}
				subItem.style.paddingLeft =  leftPos + 'px';
				subItem.style.position ='static';
					
				
			}
			if(subCounter==(activeTabIndex+1)){
				subItem.style.display='block';
			}else{
				subItem.style.display='none';
			}
			
			subCounter++;
		}
		if(document.all){
			mainMenuObj.style.visibility = 'visible';
			document.getElementById('submenu').style.visibility='visible';
		}		
		document.getElementById('submenu').style.display='block';
		
		}
		catch(ex)
		{
		}
	}

