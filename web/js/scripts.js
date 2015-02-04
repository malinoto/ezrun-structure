// Removes leading whitespaces
function LTrim( value )
{
	var re = /\s*((\S+\s*)*)/;
	return value.replace(re, "$1");
}

// Removes ending whitespaces
function RTrim( value )
{
	var re = /((\s*\S+)*)\s*/;
	return value.replace(re, "$1");
}

// Removes leading and ending whitespaces
function trim( value )
{
	return LTrim(RTrim(value));
}

function valid_email(email)
{
	var chk = /^[^@]{1,64}@[^@]{1,255}$/g;
	if ( !email.match ( chk ) )
	{
		return false;
	}
	
	email_array = email.split("@");
	local_array = email_array[0].split(".");
	for (var i = 0; i < local_array.length; i++)
	{
		var chk = /^(([A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~-][A-Za-z0-9!#$%&#038;'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/g;
		if ( !local_array[i].match ( chk ) )
		{ 
			return false;
		}
	}
	
	var chk = /^\[?[0-9\.]+\]?$/g;
	if ( !email_array[1].match ( chk ) )
	{
		domain_array = email_array[1].split(".");
		if(domain_array.length < 2) return false;
		for (var i = 0; i < domain_array.length; i++)
		{
			if(domain_array[i].length < 2) return false;
			
			var chk = /^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/g;
			if ( !domain_array[i].match ( chk ) ) return false;
		}
	}
	return true;
}

function checkForm(formname, fields_array, errors_array)
{
	eval("var reg = document." + formname + ";");
	
	for(var i = 0; i < fields_array.length; i++)
	{
		eval("var curr_field = reg." + fields_array[i] + ";");
		if(trim(curr_field.value) == '')
		{
			curr_field.focus();
			showLightboxAlert(errors_array[i], 'Грешка', 'document.' + formname + '.' + fields_array[i] + '.focus();');
			return false;
		}
	}
	reg.submit();
}

function setHomepage(what)
{
	if (document.all)
	{
		what.style.behavior='url(#default#homepage)';
		what.setHomePage('{$WEBPATH}');
	}
	else if (window.sidebar)
	{
		if(window.netscape)
		{
			try
			{
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			}  
			catch(e)
			{
				var msg = "Това действие е забранено от Вашия браузер. Ако искате да го активирате, моля въведете 'about:config' в адресната линия, и сменете стойноста на 'signed.applets.codebase_principal_support' на 'true'";
				showLightboxAlert(msg, 'Грешка', '');
			}
		}
	}
}

function addToFavorites(url, title)
{
	// Mozilla Firefox Bookmark
	if ( window.sidebar )
	{
		window.sidebar.addPanel(title, url, "");
	}
	// IE Favorite
	else if( window.external )
	{
		window.external.AddFavorite( url, title);
	}
	// Opera Hotlist
	else if( window.opera && window.print )
	{
		//Opera Hotlist
    obj.setAttribute('href', url);
    obj.setAttribute('title', title);
    obj.setAttribute('rel', 'sidebar');
    obj.click();
    return false;
	}
}

function closeBox(eval_script)
{
	if(document.getElementById('showMessage')) 
	{
		document.body.removeChild(document.getElementById('showMessage'));
		document.body.removeChild(document.getElementById('showMessageBox'));
		
		eval(eval_script);
		
		//enable scroll
		var elementBody = document.getElementsByTagName('html');
		elementBody[0].style.overflow = '';
		elementBody[0].onselectstart = function() {return true;};
	}
}

function showLightboxAlert(msg, msg_title, eval_script)
{
	var sizes = getPageSize();
	var scrollWidth = getScrollX();
	var scrollHeight = getScrollY();
	var windowWidth = winWidth();
	var windowHeight = winHeight();
	var bodyHeight = getPageSize();
	
	//main div
	dl = document.createElement('div');
	dl.id = 'showMessage';
	dl.style.position = 'absolute';
	dl.style.width = '100%';
	dl.style.height = bodyHeight[1] + 'px';
	dl.style.backgroundColor = '#000000';
	dl.style.top = 0+'px';
	dl.style.left = scrollWidth+'px';
	dl.style.textAlign = 'center';
	dl.style.filter='alpha(opacity=70)';
	dl.style.opacity='.70';
	dl.style.zIndex = 1000;
	
	//msg div
	pl = document.createElement('div');
	pl.id = 'showMessageBox';
	pl.style.width = '300px';
	pl.style.position = 'absolute';
	pl.style.backgroundColor = '#FFFFFF';
	pl.style.top = ((windowHeight/2)+scrollHeight)-100+'px';
	pl.style.left = '50%';
	pl.style.marginLeft = '-150px';
	pl.style.padding = '0px';
	pl.style.textAlign = 'left';
	pl.style.border = '#000000 1px solid';
	pl.style.zIndex = 1001;
	
	//close div
	cl = document.createElement('div');
	cl.id = 'showMessageBoxClose';
	cl.className = 'alert_bottom';
	cl.innerHTML = '<button class="login_button" onfocus="this.blur();" onclick="closeBox(\'' + eval_script + '\');"><img src="{$IMGPATH}iz_close_button.gif" alt=""/></button>';
	//cl.innerHTML = '<input type="button" onclick="javascript:closeBox(\'' + eval_script + '\');" class="input-btn" value="Затвори"/>';
	
	pl.innerHTML = '<div class="alert_top"><b>' + msg_title + '</b></div>';
	pl.innerHTML += '<div class="alert_holder">' + msg + '</div>';
	pl.appendChild(cl);
	
	document.body.insertBefore(dl, document.body.firstChild);
	document.body.insertBefore(pl, document.body.firstChild);
	
	//disable scroll
	var elementBody = document.getElementsByTagName('html');
	elementBody[0].style.overflow = 'hidden';
	elementBody[0].onselectstart = function() {return false;};
}

function Set_Cookie( name, value, expires, path, domain, secure ) 
{
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );
	
	/*
	if the expires variable is set, make the correct 
	expires time, the current script below will set 
	it for x number of days, to make it for hours, 
	delete * 24, for minutes, delete * 60 * 24
	*/
	
	if ( expires )
	{
		expires = expires * 1000 * 60 * 60 * 24;
	}
	
	var expires_date = new Date( today.getTime() + (expires) );
	
	document.cookie = name + "=" +escape( value ) +
	( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
	( ( path ) ? ";path=" + path : "" ) + 
	( ( domain ) ? ";domain=" + domain : "" ) +
	( ( secure ) ? ";secure" : "" );
}

// this function gets the cookie, if it exists
function Get_Cookie( name )
{
	var start = document.cookie.indexOf( name + "=" );
	var len = start + name.length + 1;
	
	if ( ( !start ) &&
	( name != document.cookie.substring( 0, name.length ) ) )
	{
		return null;
	}
	
	if ( start == -1 ) return null;
	var end = document.cookie.indexOf( ";", len );
	if ( end == -1 ) end = document.cookie.length;
	
	return unescape( document.cookie.substring( len, end ) );
}

function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowW, windowH;
	if (self.innerHeight) {	// all except Explorer
		windowW = self.innerWidth;
		windowH = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowW = document.documentElement.clientWidth;
		windowH = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowW = document.body.clientWidth;
		windowH = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowH){
		pageHeight = windowH;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowW){	
		pageWidth = windowW;
	} else {
		pageWidth = xScroll;
	}
	
	arrayPageSize = new Array(pageWidth,pageHeight,windowW,windowH) 
	return arrayPageSize;
}

function winWidth()
{
  var myWidth = 0;
  if( typeof( window.innerWidth ) == 'number' )
  {
    //Non-IE
    myWidth = window.innerWidth;
  }
  else if( document.documentElement && document.documentElement.clientWidth )
  {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
  }
  else if( document.body && document.body.clientWidth )
  {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
  }
  return myWidth;
}

function winHeight()
{
  var myHeight = 0;
  if( typeof( window.innerHeight ) == 'number' )
  {
    //Non-IE
    myHeight = window.innerHeight;
  }
  else if( document.documentElement && document.documentElement.clientHeight )
  {
    //IE 6+ in 'standards compliant mode'
    myHeight = document.documentElement.clientHeight;
  }
  else if( document.body && document.body.clientHeight )
  {
    //IE 4 compatible
    myHeight = document.body.clientHeight;
  }
  return myHeight;
}

function getScrollY()
{
  var scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' )
 	{
    //Netscape compliant
    scrOfY = window.pageYOffset;
  }
  else if( document.body && document.body.scrollTop )
  {
    //DOM compliant
    scrOfY = document.body.scrollTop;
  }
  else if( document.documentElement && document.documentElement.scrollTop )
  {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
  }
  return scrOfY;
}

function getScrollY()
{
  var scrOfY = 0;
  if( typeof( window.pageYOffset ) == 'number' )
 	{
    //Netscape compliant
    scrOfY = window.pageYOffset;
  }
  else if( document.body && document.body.scrollTop )
  {
    //DOM compliant
    scrOfY = document.body.scrollTop;
  }
  else if( document.documentElement && document.documentElement.scrollTop )
  {
    //IE6 standards compliant mode
    scrOfY = document.documentElement.scrollTop;
  }
  return scrOfY;
}

function getScrollX()
{
  var scrOfX = 0;
  if( typeof( window.pageXOffset ) == 'number' )
 	{
    //Netscape compliant
    scrOfX = window.pageXOffset;
  }
  else if( document.body && document.body.scrollLeft )
  {
    //DOM compliant
    scrOfX = document.body.scrollLeft;
  }
  else if( document.documentElement && document.documentElement.scrollLeft )
  {
    //IE6 standards compliant mode
    scrOfX = document.documentElement.scrollLeft;
  }
  return scrOfX;
}

function fixField(what, default_phrase, cfg)
{
	if(typeof cfg == 'undefined') cfg = 1;
	
	/* focus */
	if(cfg == 1)
	{
		if(trim(what.value) == default_phrase) what.value = '';
	}
	/* blur */
	else if(cfg == 2)
	{
		if(trim(what.value) == '') what.value = default_phrase;
	}
}

function closeAjaxModule(callback) {
	
	if(document.getElementById('showAM')) 
	{
		document.body.removeChild(document.getElementById('showAM'));
		//document.body.removeChild(document.getElementById('showAMBox'));
		
		$('#ajax_module').html('');
		
		if(typeof callback == 'function') callback.call();
	}
}

function ajaxModule(module, params) {
	
	var pars = 'module=' + module + '&params=' + (typeof params == 'object' ? encodeURIComponent(JSON.stringify(params)) : '');
	$.ajax({
		cache: false,
		async: true,
		type: "POST",
		url: '{$WEBPATH}ajax/ajax_module.php',
		data: pars,
		dataType: 'html',
		success: function(res) {
			
			var sizes = getPageSize();
			var scrollWidth = getScrollX();
			var scrollHeight = getScrollY();
			var windowWidth = winWidth();
			var windowHeight = winHeight();
			var bodyHeight = getPageSize();
			
			/* main div */
			dl = document.createElement('div');
			dl.id = 'showAM';
			dl.className = 'sam_curtain';
			dl.style.height = bodyHeight[1] + 'px';
			dl.style.left = scrollWidth + 'px';
			//dl.onclick = closeAjaxModule;
			
			/* msg div */
			pl = document.createElement('div');
			pl.id = 'showAMBox';
			pl.className = 'sam_holder';
			pl.style.top = ((windowHeight / 2) + scrollHeight) - 250 + 'px';
			pl.style.left = (windowWidth / 2) + 'px';
			
			var cl = document.createElement('img');
			cl.id = 'closeAM';
			cl.src = '{$IMGPATH}close.png';
			cl.className = 'close';
			cl.setAttribute('alt', '{$close}');
			cl.onclick = closeAjaxModule;
			
			$(pl).append(cl).append(res);
			
			$('#ajax_module').append(pl);
			document.body.insertBefore(dl, document.body.firstChild);
			//document.body.insertBefore(pl, document.body.firstChild);
		}
	});
}

function parseModule(module, params, update_element) {
	
	var pars = 'module=' + module + '&params=' + (typeof params == 'object' ? encodeURIComponent(JSON.stringify(params)) : '');
	$.ajax({
		cache: false,
		async: true,
		type: "POST",
		url: '{$WEBPATH}ajax/parse_module.php',
		data: pars,
		dataType: 'html',
		success: function(res) {
			
			$(update_element).html(res);
		}
	});
}

function addslashes(str) {

	return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

function stripslashes(str) {

	return (str + '').replace(/\\(.?)/g, function (s, n1) {
		switch (n1) {
			case '\\':
			return '\\';
			case '0':
			return '\u0000';
			case '':
			return '';
			default:
			return n1;
		}
	});
}

jQuery.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ? 
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

$(document).ready( function() {
	
	
	
});

