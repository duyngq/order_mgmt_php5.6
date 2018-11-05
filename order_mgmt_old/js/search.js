function loadXMLDoc(dname) 
{
try //Internet Explorer
  {
  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
  }
catch(e)
  {
  try //Firefox, Mozilla, Opera, etc.
    {
    xmlDoc=document.implementation.createDocument("","",null);
    }
  catch(e) {alert(e.message)}
  }
try 
  {
  xmlDoc.async=false;
  xmlDoc.load(dname);
  return(xmlDoc);
  }
catch(e) {alert(e.message)}
return null;
}

var jobDoc;
window.onload = function() 
{
jobDoc = loadXMLDoc("XML/CurrentOpening.xml");	
}


function searchJobs()
{
	var searchstr=searchform.keysearch.value.toLowerCase();
	var searchcate=searchform.category.options[searchform.category.selectedIndex].text.toLowerCase();
	var searchloca=searchform.location.options[searchform.location.selectedIndex].text.toLowerCase();
	var bingo=false;
	var result = '';
	var x=jobDoc.getElementsByTagName("c-opening");	
	
	if(searchform.keysearch.value=='' && searchform.category.value==0 && searchform.location.value==0)
	{
		alert("Fill your search.");
		return false;
	}
	for (var i = 0; i < x.length; i++) 
	{					
		var s_org_name = x[i].childNodes[0].childNodes[0].nodeValue.toLowerCase();
		var s_profile = x[i].childNodes[1].childNodes[0].nodeValue.toLowerCase();
		var s_designation = x[i].childNodes[2].childNodes[0].nodeValue.toLowerCase();
		var s_qualification = x[i].childNodes[3].childNodes[0].nodeValue.toLowerCase();
		var s_desc = x[i].childNodes[4].childNodes[0].nodeValue.toLowerCase();
		var s_category = x[i].childNodes[5].childNodes[0].nodeValue.toLowerCase();		
		var s_location = x[i].childNodes[7].childNodes[0].nodeValue.toLowerCase();	
		
		//describes a pattern of characters, trong do i la attribute peform case-insensitive matching
		var myExp= new RegExp(searchstr,"i");
		var myExp1= new RegExp(searchcate,"i");
		var myExp2= new RegExp(searchloca,"i");	
		
		if(searchform.keysearch.value!='' && searchform.category.value==0 && searchform.location.value==0)
			{
				if(s_profile.match(myExp)||s_qualification.match(myExp)||s_org_name.match(myExp) || s_designation.match(myExp) || s_desc.match(myExp) || s_category.match(myExp) || s_location.match(myExp))
				{	
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}			
										
				}
			}
		else if(searchform.keysearch.value=='' && searchform.category.value!=0 && searchform.location.value==0)
			{
				if(s_category.match(myExp1))
				{	
				
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}					
				}
			}
		else if(searchform.keysearch.value=='' && searchform.category.value==0 && searchform.location.value!=0)
			{
				if(s_location.match(myExp2))
				{	
				
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}						
				}
			}
		else if(searchform.keysearch.value!='' && searchform.category.value!=0 && searchform.location.value==0)
			{
				if((s_profile.match(myExp)||s_qualification.match(myExp)||s_org_name.match(myExp) || s_designation.match(myExp) || s_desc.match(myExp) || s_category.match(myExp) || s_location.match(myExp)) && s_category.match(myExp1))
				{	
				
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}					
				}
			}
		else if(searchform.keysearch.value!='' && searchform.category.value==0 && searchform.location.value!=0)
			{
				if((s_profile.match(myExp)||s_qualification.match(myExp)||s_org_name.match(myExp) || s_designation.match(myExp) || s_desc.match(myExp) || s_category.match(myExp) || s_location.match(myExp)) && s_location.match(myExp2))
				{	
				
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}					
				}
			}
		else if(searchform.keysearch.value=='' && searchform.category.value!=0 && searchform.location.value!=0)
			{
			if(s_category.match(myExp1) && s_location.match(myExp2))
				{	
				
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}					
				}
			}
		else if(searchform.keysearch.value!='' && searchform.category.value!=0 && searchform.location.value!=0)
			{
				if((s_profile.match(myExp)||s_qualification.match(myExp)||s_org_name.match(myExp) || s_designation.match(myExp) || s_desc.match(myExp) || s_category.match(myExp) || s_location.match(myExp)) && s_category.match(myExp1)&& s_location.match(myExp2))
				{	
				
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}					
				}
			}				
			
	}
	
	// vong for chi chay toi 10????****************************************************************
	if (bingo = true) 
	{	
				
		category.style.display = "none";
		found.style.display = "block";
		uresult.style.display = "block";
		uresult.innerHTML = '<dl>'+result+'</dl>';
	} 
	else 
	{		
		category.style.display = "none";
		found.style.display = "block";
		uresult.style.display = "block";		
	}	
	return false;	
}


function searchcate(cate)
{	
	var bingo=false;
	var result = '';
	var x=jobDoc.getElementsByTagName("c-opening");		
		
	for (var i = 0; i < x.length; i++) 
	{					
		var s_category = x[i].childNodes[5].childNodes[0].nodeValue;
		
		
		//describes a pattern of characters, trong do i la attribute peform case-insensitive matching
		var myExp= new RegExp(cate,"i");			
				if( s_category.match(myExp))
				{	
					bingo=true;			
					result += '<dt><strong><font color="#00AEA9">Organization</font></strong>: '+x[i].childNodes[0].childNodes[0].nodeValue+'</dt>';			
					result += '<dd><strong>Profile</strong>: '+x[i].childNodes[1].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Designation</strong>: '+x[i].childNodes[2].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Qualification</strong>: '+x[i].childNodes[3].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Description</strong>: '+x[i].childNodes[4].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Category</strong>: '+x[i].childNodes[5].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Salary</strong>: '+x[i].childNodes[6].childNodes[0].nodeValue+'</dd>';
					result += '<dd><strong>Location</strong>: '+x[i].childNodes[7].childNodes[0].nodeValue+'</dd>';
					if (GetCookie("username")!=" Newcommer")
					{
						result += '<dd><strong>Contact</strong>: '+x[i].childNodes[8].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Address</strong>: '+x[i].childNodes[9].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Contact Person</strong>: '+x[i].childNodes[10].childNodes[0].nodeValue+'</dd>';	
						result += '<dd><strong>Phone</strong>: '+x[i].childNodes[11].childNodes[0].nodeValue+'</dd>';		
					}			
										
				}						
	}
	
	// vong for chi chay toi 10????****************************************************************
	if (bingo = true) 
	{	
		window.location.href="cateresult.html";
		category.style.display = "none";
		cfound.style.display = "block";
		cresult.style.display = "block";
		cresult.innerHTML = '<dl>'+result+'</dl>';
	} 
	else 
	{	
		window.location.href="cateresult.html";	
		category.style.display = "none";
		cfound.style.display = "block";
		curesult.style.display = "block";		
	}	
	return false;	
}