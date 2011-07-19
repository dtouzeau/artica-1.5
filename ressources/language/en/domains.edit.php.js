/**
 * @author touzeau
 */
var memory_ou;
var memory_gid;

	function CurrentPageName(){
		var sPath = window.location.pathname;
		var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
		return sPage;		
		}

	function edit_transport(xdomain,ou){
		var XHR = new XHRConnection();
		var transport_maps=document.getElementById('transport_maps').value;
		var transport_maps_port=document.getElementById('transport_maps_port').value;
		var transport_maps_service=document.getElementById('transport_maps_service').value;				
			XHR.appendData('save_transport',xdomain);
			XHR.appendData('ou',ou);
			XHR.appendData('transport_maps',transport_maps);
			XHR.appendData('transport_maps_port',transport_maps_port);
			XHR.appendData('transport_maps_service',transport_maps_service);
			XHR.setRefreshArea('users_table');									
			XHR.sendAndLoad(CurrentPageName(), 'GET');
		document.getElementById("windows").innerHTML='';
		document.getElementById("windows").style.visibility="hidden";			
			
		}
		
	function delete_transport(xdomain,ou){
		if (xdomain.length==0){return false}
		
		if(confirm(document.getElementById('ERROR_DELETE_TRANSPORT').value)){
			var XHR = new XHRConnection();
			XHR.appendData('delete_transport',xdomain);
			XHR.appendData('domain',xdomain);
			XHR.appendData('ou',ou);
			XHR.sendAndLoad(CurrentPageName(), 'GET');
			window.location.reload();
			}
		}
		
		
function add_transport(xdomain,ou){
			if (document.getElementById("windows").style.left==''){
				document.getElementById("windows").style.left=xMousePos - 250 + 'px';
				document.getElementById("windows").style.top='100px';
				}
			document.getElementById("windows").style.height='auto';
    		
			document.getElementById("windows").style.width='700px';
			document.getElementById("windows").style.zIndex='3000';
    		document.getElementById("windows").style.visibility="visible";
			var XHR = new XHRConnection();
			XHR.setRefreshArea('windows');
			XHR.appendData('add_transport',xdomain);
			XHR.appendData('ou',ou);
			XHR.appendData('domain',xdomain);
			XHR.sendAndLoad(CurrentPageName(), 'GET');					
	
}	
	
	function editmailbox(cn,ou,xdomain,tab){
			if (document.getElementById("windows").style.left==''){
				document.getElementById("windows").style.left=xMousePos - 250 + 'px';
				document.getElementById("windows").style.top='100px';
				}
			document.getElementById("windows").style.height='auto';
    		
			document.getElementById("windows").style.width='700px';
			document.getElementById("windows").style.zIndex='3000';
    		document.getElementById("windows").style.visibility="visible";
				
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('edit_mailbox',cn);
		XHR.appendData('ou',ou);
		XHR.appendData('tab',tab);
		XHR.appendData('domain',xdomain);
		XHR.sendAndLoad('users.edit.php', 'GET');	
		
		
	}
	
			
		
	function edit_antivirus_protection(xdomain){
		var enabled;
		if (document.getElementById("kav4mailservers_enabled").checked==true){enabled=1}else{enabled=0}
		var XHR = new XHRConnection();
		XHR.appendData('edit_antivirus_protection',xdomain);
		XHR.appendData('enabled',enabled);
		XHR.setRefreshArea('kav');
		XHR.sendAndLoad(CurrentPageName(), 'GET');
		
	}
	
	function LoadKasperskySettings(xdomain,ou){
		document.getElementById("windows").style.width='850px';
		document.getElementById("windows").style.height='auto';
    	document.getElementById("windows").style.top='100px';
    	document.getElementById("windows").style.left='500px';
		document.getElementById("windows").style.zIndex='3000';
    	document.getElementById("windows").style.visibility="visible";	
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('LoadKasperskySettings',xdomain);
		XHR.appendData('ou',ou);
		XHR.sendAndLoad(CurrentPageName(), 'GET');		
		
	}
	
	function EditKasperskySettings(xdomain){
		var XHR = new XHRConnection();
		var tetss;
		var type;
		XHR.appendData("kasperskyactions",xdomain);
		with(window.document.forms["kasperskyactions"]){
    		for (i=0; i<elements.length; i++){
        		type = elements[i].type;
				tetss=tetss + type + "\n";
				switch (type){
            	case "text" :
					XHR.appendData(elements[i].name,elements[i].value);
					break;
					 
            	case "password" : 
            	case "hidden" :
            	case "textarea" :
                case "radio" :
            	case "checkbox" :
                	if(elements[i].checked == true){
						XHR.appendData(elements[i].name,"yes");
					}else{
						XHR.appendData(elements[i].name,"no");
					}
                    break;			
            	case "select-one" :XHR.appendData(elements[i].name,elements[i].value);break;
            	case "select-multiple" :
                }
			}
		}
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad(CurrentPageName(), 'GET');			
}

var PSaveUser= function (obj) {
	tempvalue=obj.responseText;
	var reg=tempvalue.match(/<error>(.+)?<\/error>/);
	if(reg){
		if(reg.length>0){
				alert(reg[1]);
				}
		}
		
	reg=tempvalue.match(/<b>Warning<\/b>:(.+)/);
	if(reg){
		if(reg.length>0){
				var newval=CleanErrorHtml(reg[1]);
				alert('ERROR ! \n'+newval);
				return false;
			}	
	
	
	}
	
}	
	
	
function CleanErrorHtml(datas){
	var reg1=/(<[a-z\/\s]+>)/gi;
	datas=datas.replace(/\[(.+)?\]/gi,"");
	datas=datas.replace(reg1,"");
	return datas;
	
}

function del_user(dn,ou,xdomain){
	if(confirm(document.getElementById("ERROR_DELETE_USER").value)){
	var XHR = new XHRConnection();
	XHR.appendData('deleteuserdn',dn);
	XHR.appendData('ou',ou);
	XHR.appendData('domain',xdomain);
	XHR.setRefreshArea('users_table');
	XHR.sendAndLoad(CurrentPageName(), 'GET');	
	}
}

function Refresh_users_table(ou,xdomain){
	var XHR = new XHRConnection();
	XHR.appendData('users_refresh_table','yes');	
	XHR.appendData('ou',ou);
	XHR.appendData('domain',xdomain);
	XHR.setRefreshArea('users_table');
	XHR.sendAndLoad(CurrentPageName(), 'GET');
}



function users_add_aliases(dn,e){
	if(e){if(!checkEnter(e)){return false;}}
	
	var xdomain=document.getElementById("domain").value;	
	var aliases=document.getElementById("aliases").value;	
	var ou=document.getElementById("ou").value;	
	var error_bad_mail_formated=document.getElementById("error_bad_mail_formated").value;
		
		if(verifMail(aliases)==false){alert(error_bad_mail_formated);
		return null;
		}	
		var XHR = new XHRConnection();
		XHR.appendData('ou',ou);
		XHR.appendData('domain',xdomain);	
		XHR.appendData('users_add_aliases',dn);	
		XHR.appendData('aliases',aliases);
		XHR.appendData('tab','1');
		document.getElementById("windows").innerHTML="";
		XHR.setRefreshArea('windows');
		XHR.sendAndLoad('users.edit.php', 'GET');
		}
		
		
function users_delete_aliases(dn,num){
	var xdomain=document.getElementById("domain").value;	
	var aliases=document.getElementById("aliases").value;	
	var ou=document.getElementById("ou").value;
	var XHR = new XHRConnection();
	XHR.appendData('ou',ou);
	XHR.appendData('domain',xdomain);	
	XHR.appendData('users_delete_aliases',dn);	
	XHR.appendData('aliases_num',num);
	XHR.appendData('tab','1');
	document.getElementById("windows").innerHTML="";
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('users.edit.php', 'GET');			
	
}	

function users_create_mailbox(dn,uid){
	var xdomain=document.getElementById("domain").value;
	var ou=document.getElementById("ou").value;		

	var XHR = new XHRConnection();
	XHR.appendData('ou',ou);
	XHR.appendData('domain',xdomain);	
	XHR.appendData('users_create_mailbox',dn);	
	XHR.appendData('uid',uid);
	XHR.appendData('tab','2');
	document.getElementById("windows").innerHTML="";
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('users.edit.php', 'GET');			
	
}
function user_set_quota(dn)	{
	var xdomain=document.getElementById("domain").value;
	var ou=document.getElementById("ou").value;		
	var XHR = new XHRConnection();
	XHR.appendData('ou',ou);
	XHR.appendData('domain',xdomain);	
	XHR.appendData('user_set_quota',dn);
	XHR.appendData('quota',document.getElementById("quota").value);
	XHR.appendData('tab','2');
	document.getElementById("windows").innerHTML="";
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('users.edit.php', 'GET');				
}

	
function SaveUser(){
	var FirstName=document.getElementById("firstname").value;
	var LastName=document.getElementById("lastname").value;
	var surname=document.getElementById("surname").value;
	var email=document.getElementById("email").value;
	var xdomain=document.getElementById("domain").value;
	var dn_path=document.getElementById("dn_path").value;
	var password=document.getElementById("password").value;
	var userid=document.getElementById("userid").value;	
	var error_bad_mail_formated=document.getElementById("error_bad_mail_formated").value;
	if (verifMail(email+ '@' + xdomain)==false){
		alert(error_bad_mail_formated);
		return null;
	}
	var ou=document.getElementById("ou").value;	
	var XHR = new XHRConnection();
	XHR.appendData('SaveUser_datas','yes');
	XHR.appendData('firstname',FirstName);
	XHR.appendData('lastname',LastName);	
	XHR.appendData('surname',surname);
	XHR.appendData('email',email);
	XHR.appendData('domain',xdomain);
	XHR.appendData('dn_path',dn_path);
	XHR.appendData('password',password);
	XHR.appendData('userid',userid);
	XHR.appendData('ou',ou);
	XHR.sendAndLoad('users.edit.php', 'GET',PSaveUser);
	Refresh_users_table(ou,xdomain);
}
function verifMail(email) {
   var reg = /^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,3}$/
   return (reg.exec(email)!=null)
}

function imap_unlock_session(){
var XHR = new XHRConnection();
	XHR.appendData('imap_unlock_session','yes');
	XHR.sendAndLoad('users.edit.php', 'GET',PSaveUser);	
	
	
}

var x_GroupADD= function (obj) {
	tempvalue=obj.responseText;
	alert(tempvalue);
	GroupLoad(memory_ou);
	}


function GroupADD(ou){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('group_table');
	XHR.appendData('NewGroup',ou);
	memory_ou=ou;
	XHR.sendAndLoad('group.edit.php', 'GET',x_GroupADD);	
	
}

function GroupLoad(ou){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('group_table');
	XHR.appendData('GroupLists',ou);
	XHR.sendAndLoad('group.edit.php', 'GET');	
	
}

function GroupDelete(guid){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('group_table');
	XHR.appendData('GroupDelete',guid);
	XHR.sendAndLoad('group.edit.php', 'GET');	

	
}

function GroupEdit(gid,tab){
			if (document.getElementById("windows").style.left==''){
				document.getElementById("windows").style.left=xMousePos - 250 + 'px';
				document.getElementById("windows").style.top='100px';
				}
			document.getElementById("windows").style.height='auto';
			document.getElementById("windows").style.width='700px';
			document.getElementById("windows").style.zIndex='3000';
    		document.getElementById("windows").style.visibility="visible";
				
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('GroupEdit',gid);
		XHR.appendData('tab',tab);
		XHR.sendAndLoad('group.edit.php', 'GET');		
	
	
}
var x_GroupSaveIdentity= function (obj) {
	tempvalue=obj.responseText;
	alert(tempvalue);
	GroupLoad(memory_ou);
	//GroupLoad(memory_ou);
	//XHR.setRefreshArea('group_table');
	}
	
		
function GroupSaveIdentity(gid,ou){
	memory_ou=ou;
	var XHR = new XHRConnection();
	XHR.appendData('GroupSaveIdentity',gid);
	XHR.appendData('cn',document.getElementById("cn").value);
	XHR.appendData('description',document.getElementById("description").value);
	XHR.sendAndLoad('group.edit.php', 'POST',x_GroupSaveIdentity);
	}
	
var x_GroupSavePrivileges= function (obj) {
	var tempvalue=obj.responseText;
	alert(tempvalue);
	
}	
	function ActionsGroupSavePrivileges(gid){
		var XHR = new XHRConnection();
		var tetss;
		var type;
		XHR.appendData("GroupSavePrivileges",gid);
		with(window.document.forms["GroupSavePrivileges"]){
    		for (i=0; i<elements.length; i++){
        		type = elements[i].type;
				tetss=tetss + type + "\n";
				switch (type){
            	case "text" :
					XHR.appendData(elements[i].name,elements[i].value);
					break;
					 
            	case "password" : 
            	case "hidden" :
            	case "textarea" :
                case "radio" :
            	case "checkbox" :
                	if(elements[i].checked == true){
						XHR.appendData(elements[i].name,"yes");
					}else{
						XHR.appendData(elements[i].name,"no");
					}
                    break;			
            	case "select-one" :XHR.appendData(elements[i].name,elements[i].value);break;
            	case "select-multiple" :
                }
			}
		}
	
	XHR.sendAndLoad('group.edit.php', 'POST',x_GroupSavePrivileges);			
}

var x_GroupUserAdd= function (obj) {
	var tempvalue=obj.responseText;
	alert(tempvalue);
	GroupEdit(memory_gid,2);
	
}

function GroupUserDelete(gid,userid){
	memory_gid=gid;
	var XHR = new XHRConnection();
	XHR.appendData('GroupUserDelete',gid);
	XHR.appendData('userid',userid);
	XHR.sendAndLoad('group.edit.php', 'GET',x_GroupUserAdd);
	
}


function GroupUserAdd(gid,userid){
	memory_gid=gid;
	var XHR = new XHRConnection();
	XHR.appendData('GroupUserAdd',gid);
	XHR.appendData('userid',userid);
	XHR.sendAndLoad('group.edit.php', 'GET',x_GroupUserAdd);
}



	
	