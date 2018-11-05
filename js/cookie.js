var myexpirydate =new Date();
myexpirydate.setTime(myexpirydate.getTime() +  (24 * 60 * 60 * 1000*180));

// set cookie funtion
var now= new Date();
now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);

function setCookie(name, value, expires, path, domain, secure)
{
  var thisCookie = name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires.toGMTString() : "") +
      ((path) ? "; path=" + path : "") +
      ((domain) ? "; domain=" + domain : "") +
      ((secure) ? "; secure" : "");
  document.cookie = thisCookie;
 }
// get cookie name funtion
function GetCookie (name) 
{
  var arg = name + "=";
  var alen = arg.length;
  var clen = document.cookie.length;
  var i = 0;
  var o = " Newcommer";
      while (i < clen) 
  {
    var j = i + alen;
    if (document.cookie.substring(i, j) == arg)
	return getCookieVal(j);
    i = document.cookie.indexOf(" ", i) + 1;
    if (i == 0) break; 
  }
  return o;
}

function getCookieVal (offset) 
{
  var endstr = document.cookie.indexOf(";", offset);
  if (endstr == -1)
    endstr = document.cookie.length;
  return unescape(document.cookie.substring(offset, endstr));
}

function DeleteCookie (name, path, domain)
{
if (GetCookie(name))
{
document.cookie = name + "=" +
((path) ? "; path=" + path : "") +
((domain) ? "; domain=" + domain : "") +
"; expires=Sat, 01-Jan-00 00:00:01 GMT ";
}
window.location.href="homepage.html";
}

//function setcookie()
//{
//document.cookie='Cookie Name=' +document.form1.cookieName.value;
//}

function CheckUser()
{
var mChkUser=GetCookie("username");
if (!((!mChkUser)||(mChkUser==" Newcommer"))){
window.location.href="homepage.html";}
}
function validate() 
{
uname=document.form1.username.value
pass=document.form1.password.value
if (uname.length==0)
{
alert ("User cannot remain blank.")
return false;
}

else if (pass.length==0)
{
alert ("Password cannot remain blank.")
return false;
}
else if(testuname() == false)
			return false;
else
{
setCookie("username",uname, now);
return true;
}
}
function testuname()
{		
	var myxml = new ActiveXObject("Msxml2.DOMDocument.4.0");
	myxml.async=false;
	myxml.load("XML/User.xml")
	if( myxml.readyState == 4 && myxml.parseError.errorCode == 0)
	{
		var root = myxml.documentElement;
		for (i = 0 ; i < root.childNodes.length ; i++) 
		{	
			var user = myxml.getElementsByTagName("user")[i];	//tim nut user
			var name = user.childNodes[0].nodeValue;
			var pass = myxml.getElementsByTagName("pass")[i];	//tim nut pass
			var pwd = pass.childNodes[0].nodeValue;
			if (document.form1.username.value == name && document.form1.password.value == pwd)
			{				
				this.location.href("homepage.html");
				break;
			}
			else
			{
					//neu di chua het cac user trong file XML thi di tiep-->chay lai vong lap
					//neu di het thi i = so nut <user> -1 ==> dang nhap sai --> alert
				if (i == root.childNodes.length - 1)	
				{
					alert("Login failed");
					return false;
				}
			}
		}
	}
	else
		alert("Error" + myxml.parseError.reason);
}
