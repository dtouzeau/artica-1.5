/**
 * @author touzeau XHRParseElements DisableFieldsFromId LoadAjaxPreload
 */
var xTimeOut;
var xMousePos=0;
var yMousePos=0;
var secs
var timerID = null;
var timerRunning = false;
var limit="0:20";
var parselimit;
var memory_branch;
var memory_service;
var popup_process_pid_id;
var TEMP_BOX_ID='';
var LoadWindows_id='';
var count_action;
var maxcount;
var mem_page;
var mem_branch_id;
var mem_item;
var mem_search=0;
var imgsrcMem='';
var tree;
var MEM_ID;
var MEM_TIMOUT=0;
var ParseFormUriReturnedBack='';
var ParseFormUriReturnedID;
document.onmousemove = pointeurDeplace;
UnlimitedSession();
ONKEYPRESS="function(event){e0.onKeyPress(event,0)}";

var compteur_global_timerID  = null;
var compteur_global_tant=0;
var compteur_global_reste=0;
var compteur_global_idname;
var compteur_global_num=0;
var compteur_global_max=0;

function  compteur_global_demarre(){
	 compteur_global_tant = compteur_global_tant+1;
		if ( compteur_global_tant <50 ) {                           
			 compteur_global_timerID = setTimeout("compteur_global_demarre()",1500);
	      } else {
	    	  compteur_global_tant = 0;
	    	  compteur_global_actions();
	    	  compteur_global_demarre();
	   }
	}

var x_time_fill= function (obj) {
	var results=obj.responseText;
	document.getElementById('topemnucurrentdate').innerHTML=results;
}


function UnlimitedSession(){
    var dt=new Date();
    try{
    	var jqueryIsLoaded=jQuery;
    	jQueryIsLoaded=true;
    	}
    	catch(err){
    	var jQueryIsLoaded=false;
    	}
    
    
    window.status=dt.getHours()+":"+dt.getMinutes()+":"+dt.getSeconds();
    if (jQueryIsLoaded) {  
    	Loadjs('Inotify.php');
    }
    setTimeout("UnlimitedSession()",120000);
    
}	
	
	


function QuickLinks(){
	var z = $("#middle").css('display');
	if(z!=="none"){
		$('#middle').slideUp('normal');
		$('#middle').html('');
		$('#quick-links').html('');
		$('#middle').slideDown({
			duration:900,
			easing:"easeOutExpo",
			complete:function(){
				QuickLinksLoad();
				}
			});
		}
	
}

function SquidQuickLinks(){
	var z = $("#middle").css('display');
	if(z!=="none"){
		$('#middle').slideUp('normal');
		$('#middle').html('');
		$('#quick-links').html('');
		$('#middle').slideDown({
			duration:900,
			easing:"easeOutExpo",
			complete:function(){
				QuickLinksSquidLoad();
				}
			});
		}
	
}
function SquidMainQuickLinks(){
	var z = $("#middle").css('display');
	if(z!=="none"){
		$('#middle').slideUp('normal');
		$('#middle').html('');
		$('#quick-links').html('');
		$('#middle').slideDown({
			duration:900,
			easing:"easeOutExpo",
			complete:function(){
				QuickLinksSquidMainLoad();
				}
			});
		}
	
}
function QuickLinksSquidLoad(){
	LoadAjax('middle','squid-quicklinks.php?stats=yes');
}
function QuickLinksSquidMainLoad(){
	LoadAjax('middle','squid.main.quicklinks.php');
}
function QuickLinksLoad(){
	
	LoadAjax('middle','quicklinks.php');
	
}
function QuickLinksHide(){
	$('#middle').slideUp('normal');
	$('#middle').html('');
	$('#quick-links').html('');
	$('#middle').slideDown({
		duration:900,
		easing:"easeOutExpo",
		complete:function(){
			LoadAjax('middle','quicklinks.php?off=yes')
			}
		});
	}	

function x_ChangeHTMLTitle(obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){
		document.title=tempvalue;
    }else{
    	document.title="!!! Error !!!";
    }
}

function ChangeHTMLTitle(){
	  setTimeout('ChangeHTMLTitlePerform()',500);
}
 
function ChangeHTMLTitlePerform(){
var XHR = new XHRConnection();
XHR.appendData('GetMyTitle','yes');
XHR.sendAndLoad("change.title.php", 'POST',x_ChangeHTMLTitle);	
}


function compteur_global_actions(){}

function isNumber(v){
	return /^-?(0|[1-9]\d*|(?=\.))(\.\d+)?$/.test(v);
}
function Rebullet(myThis){
	document.getElementById(myThis).src='img/fullbullet.gif';
}

var refresh_action=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById('message_'+count_action).innerHTML=tempvalue;
      count_action=count_action+1;
      
      if(count_action<maxcount){
        setTimeout('action_run('+count_action+')',1500);
      }
}

function StartAction(page,maxcountop){
    mem_page=page;
    maxcount=maxcountop;
    YahooWin(440,page+'?op=-1');
    setTimeout('action_run(0)',1500);
}

function action_run(number){
     var XHR = new XHRConnection();
     document.getElementById('message_'+number).innerHTML='<img src="/img/wait.gif">';
      count_action=number;
      XHR.appendData('op',number);
      XHR.sendAndLoad(mem_page, 'GET',refresh_action);
}

function StartServiceInDebugMode(service_name,cmd){
	YahooWin3(550,'admin.index.php?EmergencyStart='+cmd,service_name);
}

function pointeurDeplace(e){
	xMousePos=pointeurX(e);
    yMousePos = pointeurY(e);
   }
function ShowTopLinks(){
	if(!document.getElementById('TpLink')){return false;}
	document.getElementById('TpLink').style.visibility='visible';
		document.getElementById('TpLink').style.left =xMousePos + "px";
	document.getElementById('TpLink').style.top =yMousePos + "px";	
	
}

function Ipv4FieldDisable(id){
	document.getElementById(id+'_0').disabled=true;
	document.getElementById(id+'_1').disabled=true;
	document.getElementById(id+'_2').disabled=true;
	document.getElementById(id+'_3').disabled=true;
	
}
function Ipv4FieldEnable(id){
	document.getElementById(id+'_0').disabled=false;
	document.getElementById(id+'_1').disabled=false;
	document.getElementById(id+'_2').disabled=false;
	document.getElementById(id+'_3').disabled=false;
	
}

function GlobalSystemNetInfos(ipaddr){
	ipaddr=escape(ipaddr);
	RTMMail('550','system.netinfos.php?ipaddr='+ipaddr,ipaddr);
	
}

function IndexStartPostfix(){LoadAjax('servinfos','users.index.php?StartPostfix=yes');}

function LoadAjax(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	
	if(concatene){
		uri_add='&datas='+concatene;
	}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
			var WAITX=ID+'_WAITX';
			if(document.getElementById(WAITX)){return;}
	 $.ajax({
        type: "GET",
        timeout: 40000,
        url: uri,
        beforeSend: function() {
		    AnimateDiv(ID);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
        	document.getElementById(ID).innerHTML="<strong style='font-size:14px'>An error has occurred making the request: " + errorThrown+"<br>"+textStatus+"</strong>";
        },
        success: function(data) {
            $('#'+ID).html(data);
        }
	});	
	
	}
}
function LoadAjaxSilent(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){uri_add='&datas='+concatene;}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
		 	document.getElementById(ID).innerHTML='Wait....';
	        $('#'+ID).load(uri);
	}else{
		alert(ID+' no such id');
	}

}

function LoadAjaxTiny(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){uri_add='&datas='+concatene;}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
		 	document.getElementById(ID).innerHTML='<img src=ajax-menus-loader.gif>';
	        $('#'+ID).load(uri);
	}else{
		alert(ID+' no such id');
	}

}
function LoadAjaxPreload(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){uri_add='&datas='+concatene;}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
		 	document.getElementById(ID).innerHTML='<img src="/img/preloader.gif">';
	        $('#'+ID).load(uri);
	}else{
		alert(ID+' no such id');
	}

}

function ValidateIPAddress(ipaddr) {
    ipaddr = ipaddr.replace( /\s/g, "");
    var re = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/; 
    
    if (re.test(ipaddr)) {
        var parts = ipaddr.split(".");
        //if the first unit/quadrant of the IP is zero
        //if (parseInt(parseFloat(parts[0])) == 0) {
         //   return false;
        //}
       return true;
    } else {
        return false;
    }
}


function LoadAjaxHidden(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){uri_add='&datas='+concatene;}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
			var WAITX=ID+'_WAITX';
			if(document.getElementById(WAITX)){return;}
	 $.ajax({
        type: "GET",
        timeout: 40000,
        url: uri,
        beforeSend: function() {
			document.getElementById(ID).innerHTML='wait...';
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
        	document.getElementById(ID).innerHTML=errorThrown;
        },
        success: function(data) {
            $('#'+ID).html(data);
        }
	});	
	
	}
}

function LoadAjaxSequence(ID,uri,next) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){
		uri_add='&datas='+concatene;
	}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
			var WAITX=ID+'_WAITX';
			if(document.getElementById(WAITX)){return;}
	        document.getElementById(ID).innerHTML='<center style="margin:20px;padding:20px" id='+WAITX+'><img src="/img/ajax-loader.gif"></center>';
	        $('#'+ID).load(uri, next);
	}	
}


function LoadAjaxTiny(ID,uri,concatene) {
	var uri_add='';
	var datas='';
	var xurl='';
	if(concatene){
		uri_add='&datas='+concatene;
	}
	uri=uri+uri_add;
	if(document.getElementById(ID)){ 
			var WAITX=ID+'_WAITX';
			if(document.getElementById(WAITX)){return;}
	        document.getElementById(ID).innerHTML='<center><img src="/img/load.gif"></center>';
	        //$('#'+ID).load(uri_add, function() {Orgfillpage();});
	        $('#'+ID).load(uri);
	}

}


function XHRParseElements(idToParse){
	var XHR = new XHRConnection();
	 //select-one
	$('input,select,hidden,textarea', '#'+idToParse).each(function() {
	 	var $t = $(this);
	 	var id=$t.attr('id');
	 	var value=$t.attr('value');
	 	var type=$t.attr('type');
	 	
	 	if(type=='checkbox'){
	 		if(!document.getElementById(id).checked){
	 			if(value==1){value=0;}
	 			if(value=='yes'){value='no';}
	 		}
	 	}
	 	XHR.appendData(id,value);
	 });
	
	return XHR;
}


function LoadAjax2(ID,uri) {
	var XHR = new XHRConnection();
        MEM_ID=ID;
	XHR.setRefreshArea(ID);
        XHR.sendAndLoad(uri,"GET")
	//XHR.sendAndLoad(uri,"GET",x_ajax);
}

function UploadstartCallback() {
         return true;
        }


function UploadcompleteCallback(response) {
            document.getElementById('UploadedResponse').innerHTML = response;
        }


function LoadPostfixHistoryMsgID(mid,id){
	if(id.length>0){
	var txt=document.getElementById(id).innerHTML;
	if(txt.lenght>1){document.getElementById(id).innerHTML=''}else{
	LoadAjax(id,'users.index.php?PostfixHistoryMsgID='+mid);
	}}
}

function LoadHelp(textz,currpage,notitle){
        if(!notitle){notitle='Help';}
        YahooWin3(450,'users.index.php?loadhelp='+ textz + '&title='+ notitle + '&currpage='+currpage);
	}
        
        
        
function add_fetchmail_rules(){
	YahooWin2('700','artica.wizard.fetchmail.php?AddNewFetchMailRule=yes','Fetchmail rules');
}


function LoadTaskManager(){
	Loadjs('/system.tasks.manager.php');
}
function ProcessTaskEdit(PID){
        YahooWin2('550','system.tasks.manager.php?PID='+ PID,'Process ' +PID);
        }
function KillProcessByPid(PID){
		var XHR = new XHRConnection();
		XHR.sendAndLoad('system.tasks.manager.php?KillProcessByPid='+ PID,"GET",x_parseform);	
		YAHOO.example.container.dialog2.hide();
		ReloadTaskManager();
	}

function ReloadTaskManager(){
	if(document.getElementById('page_taskM')){
		document.getElementById('page_taskM').innerHTML='<center><img src="/img/frw8at_ajaxldr_7.gif"></center>';
		setTimeout('ReloadTaskManager2()',1200);			
	}
	
}
function ReloadTaskManager2(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('page_taskM');
		XHR.sendAndLoad('system.tasks.manager.php?reload=yes',"GET");
		}
	
	
function HelpExpand(key){
	var html=document.getElementById(key+'_fill').innerHTML;
	
	if(html.length==0){
		document.getElementById(key+'_fill').style.border='2px solid #CCCCCC'
		document.getElementById(key+'_fill').innerHTML=document.getElementById(key+'_source').value;
		document.getElementById(key+'_fill').style.backgroundColor='#F4F2F2';
		document.getElementById(key+'_fill').style.padding='3px;';
		document.getElementById(key+'_fill').style.margin='5px;';
		document.getElementById(key+'_img').src='img/collapse.gif';
	}else
	document.getElementById(key+'_fill').innerHTML='';
	document.getElementById(key+'_fill').style.border='0px solid #CCCCCC'
	document.getElementById(key+'_fill').style.padding='0px;';
	document.getElementById(key+'_fill').style.margin='0px;';
	document.getElementById(key+'_img').src='img/expand.gif';
	document.getElementById(key+'_fill').style.backgroundColor='transparent';
	}
	
function rol1_(id){
	document.getElementById(id).className='RLightGreen';
	document.getElementById(id + "_0").className='RLightGreen';								
	document.getElementById(id + "_1").className='RLightGreen1';				
	document.getElementById(id + "_2").className='RLightGreen2';					
	document.getElementById(id + "_3").className='RLightGreen3';	
	document.getElementById(id + "_4").className='RLightGreen4';	
	document.getElementById(id + "_5").className='RLightGreen5';	
	document.getElementById(id + "_6").className='RLightGreen5';	
	document.getElementById(id + "_7").className='RLightGreen4';	
	document.getElementById(id + "_8").className='RLightGreen3';	
	document.getElementById(id + "_9").className='RLightGreen2';					
	document.getElementById(id + "_10").className='RLightGreen1';									
	document.getElementById(id + "_11").className='RLightGreenfg';					
}
				
function rol0_(id){
	document.getElementById(id).className='RLightGrey';
	document.getElementById(id + "_0").className='RLightGrey';								
	document.getElementById(id + "_1").className='RLightGrey1';				
	document.getElementById(id + "_2").className='RLightGrey2';					
	document.getElementById(id + "_3").className='RLightGrey3';	
	document.getElementById(id + "_4").className='RLightGrey4';	
	document.getElementById(id + "_5").className='RLightGrey5';	
	document.getElementById(id + "_6").className='RLightGrey5';	
	document.getElementById(id + "_7").className='RLightGrey4';	
	document.getElementById(id + "_8").className='RLightGrey3';	
	document.getElementById(id + "_9").className='RLightGrey2';					
	document.getElementById(id + "_10").className='RLightGrey1';									
	document.getElementById(id + "_11").className='RLightGreyfg';					
}		
	

function CurrentPageName(){
		var sPath = window.location.pathname;
		var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
		return sPage;		
	}
	
function IsNumeric(sText){
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < sText.length && IsNumber == true; i++) { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) {IsNumber = false;}}
   return IsNumber;}
	
function Help(field){
	hide_explains();
	var id_name=field;
	var close_this;
	var text_html;
	var html;

	text_html=document.getElementById(id_name).value;
	html="<div id='SHADOW' style='position:relative; top:7px; left:7px;background:black;'>";
	html=html + "<div style='position:relative;top:-7px; left:-7px;background:#FCFCFC;border:1px solid #005447;'>";
	html=html + "<div  id='locker' style='padding:0px;background-color:#005447;background-image:url(img/barrecroix.gif);";
	html=html + "background-repeat:no-repeat;height:19px;padding-right:3px;background-position:right;cursor:pointer'>";
	html=html + "<a href='#' OnClick=\"javascript:HideDive('windows');\">";
	html=html + "<img src='http://images.kaspersky.fr/vide.gif' height=18 width=90 border=0 align='right'></a>";
	html=html + "</div>";
	html=html + "<div style='margin:4px;padding:15px;'>" + text_html + "</div>";
	html=html + "</div>";
	html=html + "</div>";
	document.onmousemove = pointeurDeplace
	document.getElementById('windows').style.visibility="visible";
	document.getElementById('windows').style.border ="none";
	document.getElementById('windows').style.width ="550px";
	document.getElementById('windows').style.padding ="0";
	document.getElementById('windows').style.left =xMousePos-550 + "px";
	document.getElementById('windows').style.top =yMousePos-100 + "px";	
	document.getElementById('windows').style.backgroundColor="#FFFFFF";
	document.getElementById('windows').style.zIndex='3000';	
	document.getElementById('windows').innerHTML=html;
	
}
function HelpIcon(div_name){Help(div_name);}
function lightup(imageobject, opacity){
if (navigator.appName.indexOf("Netscape")!=-1 &&parseInt(navigator.appVersion)>=5){
        imageobject.style.MozOpacity=opacity/100;
        imageobject.style.backgroundColor='none';
        }
else if (navigator.appName.indexOf("Microsoft")!= -1 &&parseInt(navigator.appVersion)>=4){imageobject.filters.alpha.opacity=opacity}
}


function closediv(div){
	document.getElementById(div).style.visibility="hidden";
	}
	
function SwitchOnOff(id){
	id_value=document.getElementById(id).value;

	if(!id_value){
		document.getElementById(id).value='yes';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='yes'){
		document.getElementById(id).value='no';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}else{
		document.getElementById(id).value='yes';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
}

function SwitchOnOff_on(id){
id_value=document.getElementById(id).value;

	if(!id_value){
		document.getElementById(id).value='on';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='on'){
		document.getElementById(id).value='off';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}else{
		document.getElementById(id).value='on';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}        
}


function SwitchDenySkip(id){
id_value=document.getElementById(id).value;

	if(!id_value){
		document.getElementById(id).value='skip';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='skip'){
		document.getElementById(id).value='deny';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}else{
		document.getElementById(id).value='skip';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
}



function SwitchBigNumeric(id){
	id_value=document.getElementById(id).value;

	if(!id_value){
		document.getElementById(id).value='1';
		document.getElementById('img_' + id).src='img/64-green.png';
		return;
	}
	
	if(id_value=='1'){
		document.getElementById(id).value='0';
		document.getElementById('img_' + id).src='img/64-red.png';
		return;
	}else{
		document.getElementById(id).value='1';
		document.getElementById('img_' + id).src='img/64-green.png';
		return;
	}        
        
        
}

function Switch32Numeric(id){
	id_value=document.getElementById(id).value;

	if(!id_value){
		document.getElementById(id).value='1';
		document.getElementById('img_' + id).src='img/ok32.png';
		return;
	}
	
	if(id_value=='1'){
		document.getElementById(id).value='0';
		document.getElementById('img_' + id).src='img/danger32.png';
		return;
	}else{
		document.getElementById(id).value='1';
		document.getElementById('img_' + id).src='img/ok32.png';
		return;
	}        
}

function sleep(milliseconds) {
	  var start = new Date().getTime();
	  while(true) {
	    if ((new Date().getTime() - start) > milliseconds){
	      break;
	    }
	  }
	}


function SwitchNumeric(id){
	id_value=document.getElementById(id).value;

	document.getElementById('img_' + id).src='img/wait.gif';
	if(!id_value){
		document.getElementById(id).value='1';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='1'){
		document.getElementById(id).value='0';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}else{
		document.getElementById(id).value='1';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
}
function SwitchKeyOnOff(id){
	id_value=document.getElementById(id).value;

	if(!id_value){
		document.getElementById(id).value='justkey';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='justkey'){
		document.getElementById(id).value='nokey';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}else{
		document.getElementById(id).value='justkey';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
}	
function s_PopUp(url,l,h,asc){
	var PopupWindow=null;
        var toolbal="scrollbars=no";
        if(asc){
            toolbal="scrollbars=yes";    
        }
	settings='width='+l +',height='+h +',location=no,directories=no,menubar=no,toolbar=no,status=no,'+toolbal+',resizable=no,dependent=yes';
	PopupWindow=window.open(url,'',settings);
	PopupWindow.focus();
	} 
	
function s_PopUpScroll(url,l,h,mtitle){
	var PopupWindow=null;
	settings='width='+l +',height='+h +',location=no,directories=no,menubar=no,toolbar=no,status=no,scrollbars=yes,resizable=yes,dependent=yes';
	PopupWindow=window.open(url,mtitle,settings);
	PopupWindow.focus();
	PopupWindow.moveTo(0,0);
	}
        
function s_PopUpFull(url,l,h,mtitle){
	var PopupWindow=null;
	settings='width='+l +',height='+h +',location=no,directories=no,menubar=yes,toolbar=yes,status=yes,scrollbars=yes,resizable=yes,dependent=yes';
	PopupWindow=window.open(url,mtitle,settings);
	PopupWindow.focus();
	PopupWindow.moveTo(0,0);
	}      

function CheckBoxValidate(id){
	if(document.getElementById(id).checked){return 1;}
	return 0;
	
}

function SwitchTRUEFALSE(id){
	id_value=document.getElementById(id).value;
	id_value=id_value.toUpperCase();
	if(id_value.length==0){id_value='FALSE';}
	
	
	if(!id_value){
		document.getElementById(id).value='TRUE';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}
	
	if(id_value=='TRUE'|id_value=='true'|id_value=='1'){
		document.getElementById(id).value='FALSE';
		document.getElementById('img_' + id).src='img/status_critical.gif';
		return;
	}
	
	if(id_value=='FALSE'|id_value=='false'|id_value=='0'){
		document.getElementById(id).value='TRUE';
		document.getElementById('img_' + id).src='img/status_ok.gif';
		return;
	}	
	
}	

var xmain_cf_submit_fields= function (obj) {
	alert(obj.responseText);
}

function BuildXHRForms(){
	var inputs	= document.getElementsByTagName('input');
	var count	= inputs.length;
	var XHR = new XHRConnection();
	for (i = 0; i < count; i++) {
		_input = inputs.item(i);
		if(_input.type=='text'){
			XHR.appendData(_input.id, _input.value);
		}
		if(_input.type=='hidden'){
			XHR.appendData(_input.id, _input.value);
		}		
		
		
		if(_input.type=='checkbox'){
			if(_input.checked==true){
				XHR.appendData(_input.id, '1');
			}
			
			if(_input.checked==false){
				XHR.appendData(_input.id, '0');
			}
		}
	}
	return XHR;
	}
	
function FreeForms(list){
	var inputs	= document.getElementsByTagName('input');
	for (var i = 0; i < inputs.length; i++) {
		_input = inputs.item(i);
		if(hide_forms_parse(list,_input.id)==true){
			_input.disabled=false;
			}
		
		}
	}	
	
function hideForms(list){
	var inputs	= document.getElementsByTagName('input');
	for (var i = 0; i < inputs.length; i++) {
		_input = inputs.item(i);
		if(hide_forms_parse(list,_input.id)==true){
			_input.disabled=true;
			_input.value='';
			}
		
		}
	}
function hide_forms_parse(list,xname){
		var s_array;
		var divid;
		if(list.lastIndexOf(",")==-1){
			if(list==xname){return true}else{return false}
		}
		s_array=list.split(',');
	 	for(var i = 0; i < s_array.length; i++){
			divid=s_array[i];
			if (xname==divid){return true;}
		}	
	return false;
}
function Findusr(e){
	if(checkEnter(e)){
		FindUser();
	}
	
}

function checkEnter(e){
	var characterCode 
	characterCode = (typeof e.which != "undefined") ? e.which : event.keyCode;
	if(characterCode == 13){ return true;}else{return false;}
}

	
function main_cf_submit_fields(){
	var inputs	= document.getElementsByTagName('input');
	var count	= inputs.length;
	var XHR = new XHRConnection();
	for (i = 0; i < count; i++) {
		_input = inputs.item(i);
		if(_input.type=='text'){XHR.appendData(_input.id, _input.value);}
	}
	XHR.sendAndLoad('post.main.cf.php', "POST",xmain_cf_submit_fields);
}	
	
function hide_explains(){
var inputs	= document.getElementsByTagName('div');
	var count	= inputs.length;
	var id_name;
	for (i = 0; i < count; i++) {
		
		_input = inputs.item(i);
		id_name=_input.id;
		if (id_name.lastIndexOf("_explain")>0){
			closediv(id_name);
		}		
		
	    }
	}
	


function StartTimer(){
if (!document.images){return}
	parselimit=limit.split(":")
	parselimit=parselimit[0]*60+parselimit[1]*1;
	beginrefresh();	
	
}
function artica_StartTimer(){
if (!document.images){return}
	parselimit=limit.split(":")
	parselimit=parselimit[0]*60+parselimit[1]*1;
	artica_beginrefresh();	
	
}
function Load_artica_log(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('log_area');
		XHR.sendAndLoad('artica.log.php?logs=yes',"GET");	
}

function Load_mail_log(){
		var XHR = new XHRConnection();
		XHR.setRefreshArea('log_area');
		XHR.sendAndLoad('mail.log.php?logs=yes',"GET");	
}
function artica_beginrefresh(){
	if (parselimit==1){
		Load_artica_log();
		artica_StartTimer();
		}
	else{ 
		parselimit-=1
		curmin=Math.floor(parselimit/60)
		cursec=parselimit%60
		setTimeout("artica_beginrefresh()",500)
		}
}
	
function beginrefresh(){
	if (parselimit==1){
		Load_mail_log();
		StartTimer();
		}
	else{ 
		parselimit-=1
		curmin=Math.floor(parselimit/60)
		cursec=parselimit%60
		setTimeout("beginrefresh()",500)
		}
}
function HideBulle() {
	document.onmousemove = pointeurDeplace
	document.getElementById('PopUpInfos').style.visibility="hidden";
	document.getElementById('PopUpInfos').style.border ="none";
	document.getElementById('PopUpInfos').style.padding ="0";
	document.getElementById('PopUpInfos').style.backgroundColor="#FFFFFF";
	document.getElementById('PopUpInfos').style.zIndex='0';
	
}

function BuildLeftMenus(){
	var XHR = new XHRConnection();
	var currentPage=document.URL;
	XHR.setRefreshArea('menu');
	XHR.sendAndLoad('index.php?leftmenus=yes&url='+ currentPage,"GET");
}
function BuildTopMenus(){
	var XHR = new XHRConnection();
	var currentPage=document.URL;
	XHR.setRefreshArea('topmenus');
	XHR.sendAndLoad('index.php?topmenus=yes&url='+ currentPage,"GET");	
	}


function AffBulle(texte) {
		document.onmousemove = pointeurDeplace
  		var contenu=texte;
 	 	document.getElementById('PopUpInfos').innerHTML="<div style='padding:10px;text-align:left;font-size:14px;margin:0px' OnMouseOver=\"javascript:HideBulle();\">"+ contenu+ "</div>";
	 	document.getElementById('PopUpInfos').style.width='auto';
		document.getElementById('PopUpInfos').style.height='auto';
        document.getElementById('PopUpInfos').style.top=(yMousePos -20) + 'px';
        document.getElementById('PopUpInfos').style.left=(xMousePos +15)+ 'px';
        document.getElementById('PopUpInfos').style.visibility="visible";
        document.getElementById('PopUpInfos').style.backgroundColor="#ffffff";
        document.getElementById('PopUpInfos').style.borderRight = "solid 2px #005447";
        document.getElementById('PopUpInfos').style.borderBottom = "solid 2px #005447";
		document.getElementById('PopUpInfos').style.borderTop = "solid 2px #005447";
		document.getElementById('PopUpInfos').style.borderLeft = "solid 2px #005447";
        if(document.getElementById('PopUpInfos').style.zIndex>99999){
            document.getElementById('PopUpInfos').style.zIndex=document.getElementById('PopUpInfos').style.zIndex+100;
        }else{document.getElementById('PopUpInfos').style.zIndex = "10000";}
		
  	}
	
function HideDive(DivName){
	 document.getElementById(DivName).innerHTML='';
	 document.getElementById(DivName).style.zIndex = "0";
	 document.getElementById(DivName).style.visibility = "hidden";
	 document.getElementById("windows").style.left='';
	
}
function x_ChangeFetchMailUser(obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){
                alert(tempvalue);
                document.getElementById('is').value='';
                document.getElementById('is_html').innerHTML='Change...';
        }
}

function mindTerm() { 
window.open("index.mindterm.php","","width=1,height=1,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no");
}





//----------------------------------------------------------- Global fetchmail rules

function SwitchFetchMailUserForm(id){
   document.getElementById('server_options').style.display='none';
   document.getElementById('users_options').style.display='none';
   document.getElementById(id).style.display='block';     
        
}


function ChangeFetchMailUser(){
	var text=document.getElementById('ChangeFetchMailUserText').value;
	var email=prompt(text);
	if(email){
		document.getElementById('_is').value=email;
		document.getElementById('is_html').innerHTML=email;
		
	}
        
        var XHR = new XHRConnection();
        XHR.appendData('ChangeFetchMailUser',email);
        XHR.sendAndLoad('artica.wizard.fetchmail.php', 'GET',x_ChangeFetchMailUser);        
	}

function UserFetchMailRule(num,userid){
if(document.getElementById('dialog3_c')){
        if(document.getElementById('dialog3_c').style.visibility=='visible'){
            YahooWin4('700','artica.wizard.fetchmail.php?LdapRules='+ num + '&uid='+ userid,'Fetchmail rule');
            return true;
        }
}
       	YahooWin2('700','artica.wizard.fetchmail.php?LdapRules='+ num + '&uid='+ userid,'Fetchmail rule');
        }
        
function UserDeleteFetchMailRule(num){
       var uid=document.getElementById('uid').value;
       if(confirm(document.getElementById('confirm').value)){
        var XHR = new XHRConnection();
        XHR.appendData('UserDeleteFetchMailRule',num);
        XHR.appendData('uid',uid);
        XHR.sendAndLoad('artica.wizard.fetchmail.php', 'GET',x_FetchMailPostForm);
       }
       if(document.getElementById('left')){
        LoadAjax('left',Working_page + '?LoadFetchMailRuleFromUser='+uid);              
       }
        if(document.getElementById(TEMP_BOX_ID)){RemoveDocumentID(TEMP_BOX_ID);}
        YahooWin2Hide();
       
                
}        

var x_FetchMailPostForm= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
        if(document.getElementById('fetchmail_users_datas')){LoadAjax('fetchmail_users_datas','users.fetchmail.index.php?LoadRules=yes');}
        if(document.getElementById('fetchmail_daemon_rules')){
        	LoadAjax('fetchmail_daemon_rules','fetchmail.daemon.rules.php?Showlist=yes&section=yes&tab=0');}
	}

function FetchMailPostForm(edit_mode){
	var pool=document.getElementById('MailBoxServer').value;
	if(pool.length==0){
		alert('Mailbox server = null !');
		return;
	}
	
	
	var XHR = new XHRConnection();
    XHR.appendData('edit_mode',edit_mode);
    XHR.appendData('rule_number',document.getElementById('rule_number').value);
	XHR.appendData('poll',document.getElementById('MailBoxServer').value);
	XHR.appendData('proto',document.getElementById('_proto').value);	
	XHR.appendData('port',document.getElementById('_port').value);	
	XHR.appendData('timeout',document.getElementById('_timeout').value);		
	XHR.appendData('interval',document.getElementById('_interval').value);		
	XHR.appendData('user',document.getElementById('_user').value);	
	XHR.appendData('pass',document.getElementById('_pass').value);		
	XHR.appendData('is',document.getElementById('_is').value);
    XHR.appendData('enabled',document.getElementById('_enabled').value);
    XHR.appendData('aka',document.getElementById('_aka').value);
    
    
    if(document.getElementById('_dropdelivered').checked){XHR.appendData('dropdelivered',1);}else{XHR.appendData('dropdelivered',0);}	
    if(document.getElementById('_multidrop').checked){XHR.appendData('multidrop',1);}else{XHR.appendData('multidrop',0);}	
	if(document.getElementById('_tracepolls').checked){XHR.appendData('tracepolls',1);}else{XHR.appendData('tracepolls',0);}	
	if(document.getElementById('_ssl').checked){XHR.appendData('ssl',1);}else{XHR.appendData('ssl',0);}
	if(document.getElementById('_fetchall').checked){XHR.appendData('fetchall',1);}else{XHR.appendData('fetchall',0);}		
	if(document.getElementById('_keep').checked){XHR.appendData('keep',1);}else{XHR.appendData('keep',0);}
	if(document.getElementById('_nokeep').checked){XHR.appendData('nokeep',1);}else{XHR.appendData('nokeep',0);}
	
	if(document.getElementById('_fingerprint')){XHR.appendData('sslfingerprint',document.getElementById('_fingerprint').value);}
	if(document.getElementById('_sslcertck').checked){XHR.appendData('sslcertck',1);}else{XHR.appendData('sslcertck',0);}
	XHR.sendAndLoad('artica.wizard.fetchmail.php', 'GET',x_FetchMailPostForm);
       
        
}

//-----------------------------------------------------------
function RemoveDocumentID(ID){
	var element = document.getElementById(ID);
	element.remove(0);
	}
var x_parseform= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue);}
		if(ParseFormUriReturnedBack){
			if(ParseFormUriReturnedBack.length>0){
              LoadAjax(ParseFormUriReturnedID,ParseFormUriReturnedBack);
			}
        }
	}
        
        
function ParseYahooForm(Form_name,pageToSend,return_box,noHidden){
		var XHR = new XHRConnection();
		var tetss;
		var type;
		if(!noHidden){noHidden=false;}
		
		with(window.document.forms[Form_name]){
                       
    		for (i=0; i<elements.length; i++){
                        type = elements[i].type;
                        FieldDisabled=elements[i].disabled;
			if(FieldDisabled==false){XHR.appendData(elements[i].name,elements[i].value);}
		}
	}
	if(return_box==true){		
		XHR.sendAndLoad(pageToSend, 'GET',x_parseform);}
		else{XHR.sendAndLoad(pageToSend, 'GET');}			
}


	 
function ParseForm(Form_name,pageToSend,return_box,noHidden,ReturnValues,idRefresh,uriRefresh,function_callback){
		var XHR = new XHRConnection();
		var tetss;
		var type;
		if(!noHidden){noHidden=false;}
                if(!ReturnValues){ReturnValues=false;}
		
		with(window.document.forms[Form_name]){
                       
    		for (i=0; i<elements.length; i++){
                        
        		type = elements[i].type;
                       // alert('type='+type+' '+ i+' '+elements[i].value+ ' diabled='+elements[i].disabled)
                        
				FieldDisabled=elements[i].disabled;
				if(FieldDisabled==false){
				
					switch (type){
            			case "text" :
							XHR.appendData(elements[i].name,elements[i].value);
							break;
					 
            			case "password" : 
							XHR.appendData(elements[i].name,elements[i].value);
							break;
            			case "hidden" :
							if(noHidden==false){
								XHR.appendData(elements[i].name,elements[i].value);
							}
							break;
            			case "textarea" :
							XHR.appendData(elements[i].name,elements[i].value);
							break;
                		case "radio" :
            			case "checkbox" :
                			if(elements[i].checked == true){
								XHR.appendData(elements[i].name,elements[i].value);
								}else{
								    if(elements[i].value=='1'){XHR.appendData(elements[i].name,"0");}
								    if(elements[i].value=='yes'){XHR.appendData(elements[i].name,"no")};
                                                                }
                    		break;			
            			case "select-one" :XHR.appendData(elements[i].name,elements[i].value);break;
            			case "select-multiple" :
                		}
			}
		}
	}
        
        if(ReturnValues==true){
                return XHR;
        }
        
	if(return_box==true){
		 		AnimateDiv(idRefresh);
                if(uriRefresh){
                  if(uriRefresh.length>0){
                        ParseFormUriReturnedBack=uriRefresh;
                        ParseFormUriReturnedID=idRefresh;
                  }
                }
	
                
                
       XHR.sendAndLoad(pageToSend, 'GET',x_parseform);
       }else{
    	   AnimateDiv(idRefresh);
           
    	   
    	   if(uriRefresh){
               if(uriRefresh.length>0){
                     ParseFormUriReturnedBack=uriRefresh;
                     ParseFormUriReturnedID=idRefresh;
               }}    	   
    	   
			if(isDefined(function_callback)){
				XHR.sendAndLoad(pageToSend, 'GET',function_callback);
			}else{
				XHR.sendAndLoad(pageToSend, 'GET');
			}
		}
}

function isDefined(variable){
return (!(!( variable||false )))
}


function ParseFormPOST(Form_name,pageToSend,return_box){
		var XHR = new XHRConnection();
		var tetss;
		var type;
		
		with(window.document.forms[Form_name]){
    		for (i=0; i<elements.length; i++){
        		type = elements[i].type;
				FieldDisabled=elements[i].disabled;
				if(FieldDisabled==false){
				
					switch (type){
            			case "text" :
							XHR.appendData(elements[i].name,elements[i].value);
							break;
					 
            			case "password" : 
							XHR.appendData(elements[i].name,elements[i].value);
							break;
            			case "hidden" :
							XHR.appendData(elements[i].name,elements[i].value);
							break;
            			case "textarea" :
							XHR.appendData(elements[i].name,elements[i].value);
							break;
                		case "radio" :
            			case "checkbox" :
                			if(elements[i].checked == true){
									if(elements[i].value=='1'){XHR.appendData(elements[i].name,"1");}
									if(elements[i].value=='yes'){XHR.appendData(elements[i].name,"yes");}
							}else{
								    if(elements[i].value=='1'){XHR.appendData(elements[i].name,"0");}
								    if(elements[i].value=='yes'){XHR.appendData(elements[i].name,"no")};
							}
                    		break;			
            			case "select-one" :XHR.appendData(elements[i].name,elements[i].value);break;
            			case "select-multiple" :
                		}
			}
		}
	}
	if(return_box==true){

		XHR.sendAndLoad(pageToSend, 'POST',x_parseform);}
		else{XHR.sendAndLoad(pageToSend, 'POST');}			
}

	

function LoadWindows(Windowswidth,windowsHeight,ajax,ajax_parameters,ShowIndex,ShowCenter){
                YahooWin(Windowswidth,ajax + '?'+ajax_parameters);
        }
function LoadFind(Windowswidth){
			if (document.getElementById("find").style.left==''){
	document.getElementById("find").style.left=xMousePos - 250 + 'px';document.getElementById("windows").style.top='100px';}
			document.getElementById("find").style.height='auto';
			document.getElementById("find").style.width=Windowswidth + 'px';
			document.getElementById("find").style.zIndex='3000';
    		document.getElementById("find").style.visibility="visible";	
			document.getElementById("find").innerHTML='<center>Loading</center>';
			
			}			



function EditLdapUser(dn){
		LoadWindows(450);
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('EditLdapUser',dn);
		XHR.sendAndLoad('users.edit.php', 'GET');	
		}
		
		
function PageEditGroup(gpid){
		LoadWindows(650);
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('PageEditGroup',gpid);
		XHR.sendAndLoad('group.edit.php', 'GET');	
		}
		
function TabGroupEdit(gpid,tab){
		LoadWindows(650);
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('GroupEdit',gpid);
		XHR.appendData('tab',tab);
		XHR.sendAndLoad('group.edit.php', 'GET');	
	
}

function MyHref(url,add){
        var uri='';
        if(add){uri='&data=' + add}
	document.location.href=url+uri;
	}
function RefreshPostfixStatus(){
	
	
}

function YahooWinS(width,uri,title,waitfor){
	AnimateDiv('dialogS');
	if(!width){width='300';}
    if(!title){title='Windows';}
	$('#dialogS').dialog( 'destroy' );
	$(function(){
	$('#dialogS').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
}



function YahooWinT(width,uri,title,waitfor){
	if(!width){width='300';}
	AnimateDiv('dialogT');
    	if(!title){title='Windows';}
 	$('#dialogT').dialog( 'destroy' );
        if(!title){title='Windows';}
	$(function(){$('#dialogT').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
	}


function YahooWin0(width,uri,title,waitfor){
        if(!width){width='750';}
        if(!title){title='Windows';}	
        
	$('#dialog0').dialog( 'destroy' );
	AnimateDiv('dialog0');
	$(function(){$('#dialog0').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
	}

function AnimateDiv(id){
	var animated="/img/wait_verybig.gif";
	if(!document.getElementById(id)){return;}
	if(document.getElementById("LoadAjaxPicture")){animated=document.getElementById("LoadAjaxPicture").value;}
	document.getElementById(id).innerHTML='<div style="width:100%;height:auto"><center><img src="'+animated+'"></center></div>';
}

function YahooWin(width,uri,title,waitfor,pos){
	
        if(!width){width='300';}
        if(!title){title='Windows';}
        document.getElementById('dialog1').innerHTML='';
        $('#dialog1').dialog( 'destroy' );
        AnimateDiv('dialog1');
        if(waitfor){
        	if(pos){
        		$(function(){
        			$('#dialog1').dialog({
        				autoOpen: true,modal:true,closeOnEscape: false,
        				width: width+'px',title: title,position: pos,
        				open: function(event, ui) { 
        				$(this).parent().children().children('.ui-dialog-titlebar-close').hide();
        				}}).load(uri);});
        		

        	}else{
        		$(function(){$('#dialog1').dialog({autoOpen: true,modal:true,closeOnEscape: true,width: width+'px',title: title,position: 'center'}).load(uri);});
        	}
        	
        }else{
        	 $(function(){$('#dialog1').dialog({autoOpen: true,closeOnEscape: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        }


	}

function HideTips(md5,uid){
	var XHR = new XHRConnection();
	XHR.appendData('HideTips',md5+'-'+uid);
	XHR.sendAndLoad('admin.index.php', 'GET');	
	document.getElementById(md5+'-id').innerHTML='';
	document.getElementById(md5+'-id').style.width='0px';
	document.getElementById(md5+'-id').style.heigth='0px';
	document.getElementById(md5+'-id').className='';
}
        
function YahooWin2(width,uri,title,waitfor){
        if(!width){width='300';}
        if(!title){title='Windows';}
        document.getElementById('dialog2').innerHTML='';
        $('#dialog2').dialog( 'destroy' );
        AnimateDiv('dialog2');
        $(function(){$('#dialog2').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#dialog2').dialog({ closeOnEscape: true });
        $('#dialog2').dialog({ stack: true });        
        } 

function YahooSetupControl(width,uri,title,waitfor){
    if(!width){width='300';}
    if(!title){title='Windows';}
    $('#SetupControl').dialog( 'destroy' );
    AnimateDiv('SetupControl');
    YahooWin2Hide();
    $(function(){$('#SetupControl').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
    $('#SetupControl').dialog({ closeOnEscape: true });
    $('#SetupControl').dialog({ stack: true });     
    }

function RTMMail(width,uri,title,waitfor){
    if(!width){width='300';}
    if(!title){title='Windows';}
    $('#RTMMail').dialog( 'destroy' );
    AnimateDiv('RTMMail');
    $(function(){$('#RTMMail').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
    $('#RTMMail').dialog({ closeOnEscape: true });
    $('#RTMMail').dialog({ stack: true });  
   }

function YahooWinBrowse(width,uri,title,waitfor){
	if(!width){width='300';}
    if(!title){title='Windows';}
    $('#Browse').dialog( 'destroy' );
    AnimateDiv('Browse');
    $(function(){$('#Browse').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
    $('#Browse').dialog({ closeOnEscape: true });
    $('#Browse').dialog({ stack: true });    
   }

function YahooWin3(width,uri,title,waitfor){
        if(!width){width='300';}
        if(!title){title='Help';}
    	$('#dialog3').dialog( 'destroy' );
    	  AnimateDiv('dialog3');
    	$(function(){$('#dialog3').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#dialog3').dialog({ closeOnEscape: true });
        $('#dialog3').dialog({ stack: true });       	
        }

function YahooWin4(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#dialog4').dialog( 'destroy' );
    	AnimateDiv('dialog4');
    	$(function(){$('#dialog4').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#dialog4').dialog({ closeOnEscape: true });
        $('#dialog4').dialog({ stack: true });     	
        }

function YahooWin5(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#dialog5').dialog( 'destroy' );
    	AnimateDiv('dialog5');
    	$(function(){$('#dialog5').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#dialog5').dialog({ closeOnEscape: true });
        $('#dialog5').dialog({ stack: true });      	
        }

// A supprimer document.getElementById("YahooUser_c") is null;
function YahooWin6(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#dialog6').dialog( 'destroy' );
    	AnimateDiv('dialog6');
    	$(function(){$('#dialog6').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#dialog6').dialog({ closeOnEscape: true });
        $('#dialog6').dialog({ stack: true });     	
        }
function LoadWinORG(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#WinORG').dialog( 'destroy' );
    	AnimateDiv('WinORG');
    	$(function(){$('#WinORG').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#WinORG').dialog({ closeOnEscape: true });
        $('#WinORG').dialog({ stack: true });     	
        }
function LoadWinORG2(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#WinORG2').dialog( 'destroy' );
    	AnimateDiv('WinORG2');
    	$(function(){$('#WinORG2').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#WinORG2').dialog({ closeOnEscape: true });
        $('#WinORG2').dialog({ stack: true });      	
        }
function YahooLogWatcher(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#logsWatcher').dialog( 'destroy' );
    	AnimateDiv('logsWatcher');
    	$(function(){$('#logsWatcher').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#logsWatcher').dialog({ closeOnEscape: true });
        $('#logsWatcher').dialog({ stack: true });     	
        }
function YahooUser(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#YahooUser').dialog( 'destroy' );
    	AnimateDiv('YahooUser');
    	$(function(){$('#YahooUser').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#YahooUser').dialog({ closeOnEscape: true });
        $('#YahooUser').dialog({ stack: true });      	
        }  
function YahooSearchUser(width,uri,title,waitfor){
	if(!width){width='300';}
        if(!title){title='Windows';}
    	$('#SearchUser').dialog( 'destroy' );
    	AnimateDiv('SearchUser');
    	$(function(){$('#SearchUser').dialog({autoOpen: true,width: width+'px',title: title,position: 'top'}).load(uri);});
        $('#SearchUser').dialog({ closeOnEscape: true });
        $('#SearchUser').dialog({ stack: true });     	
        }  
function YahooWin0Hide(){ $('#dialog0').empty();$('#dialog0').dialog( 'destroy' );}
function YahooWinBrowseHide(){$('#Browse').empty();$('#Browse').dialog( 'destroy' );}
function RTMMailHide(){$('#RTMMail').empty();$('#RTMMail').dialog( 'destroy' );}
function YahooSetupControlHide(){$('#SetupControl').empty();$('#SetupControl').dialog( 'destroy' );}
function YahooWinHide(){$('#dialog1').empty();$('#dialog1').dialog('destroy');}
function YahooWin2Hide(){$('#dialog2').empty();$('#dialog2').dialog('destroy');}
function YahooWin3Hide(){$('#dialog3').empty();$('#dialog3').dialog('destroy');}
function YahooWin4Hide(){$('#dialog4').empty();$('#dialog4').dialog('destroy');}
function YahooWin5Hide(){$('#dialog5').empty();$('#dialog5').dialog('destroy');}
function YahooWin6Hide(){$('#dialog6').empty();$('#dialog6').dialog('destroy');}
function YahooLogWatcherHide(){$('#logsWatcher').empty();$('#logsWatcher').dialog( 'destroy' );}
function YahooUserHide(){$('#YahooUser').empty();$('#YahooUser').dialog( 'destroy' );}
function WinORGHide(){$('#WinORG').empty();$('#WinORG').dialog( 'destroy' );}
function WinORG2Hide(){$('#WinORG2').empty();$('#WinORG2').dialog( 'destroy' );}
function YahooLogWatcherHide(){$('#logsWatcher').empty();$('#logsWatcher').dialog( 'destroy' );}
function YahooSearchUserHide(){$('#SearchUser').empty();$('#SearchUser').dialog( 'destroy' );}
function YahooWinSHide(){$('#dialogS').empty();$('#dialogS').dialog( 'destroy' );}

function RTMMailOpen(){
	var html=$("#RTMMail").html();
	if(html.length==0){return false;}
	return $('#RTMMail').dialog('isOpen');
	}
function YahooWinSOpen(){
	var html=$("#dialogS").html();
	if(html.length==0){return false;}	
	return $('#dialogS').dialog('isOpen');
	}
function YahooWinOpen(){
	var html=$("#dialog1").html();
	if(html.length==0){return false;}		
	return $('#dialog1').dialog('isOpen');
	}  
function YahooWin5Open(){
	var html=$("#dialog5").html();
	if(html.length==0){return false;}		
	return $('#dialog5').dialog('isOpen');
	}  
function YahooWin3Open(){
	var html=$("#dialog3").html();
	if(html.length==0){return false;}	
	return $('#dialog3').dialog('isOpen');
	}  
function YahooLogWatcherOpen(){
	var html=$("#logsWatcher").html();
	if(html.length==0){return false;}		
	return $('#logsWatcher').dialog('isOpen');
	}  
function YahooSetupControlOpen(){
	var html=$("#SetupControl").html();
	if(html.length==0){return false;}			
	return $('#SetupControl').dialog('isOpen');
	}  
function YahooSearchUserOpen(){
	var html=$("#SearchUser").html();
	if(html.length==0){return false;}			
	return $('#SearchUser').dialog('isOpen');
	}  
function YahooUserOpen(){
	var html=$("#YahooUser").html();
	if(html.length==0){return false;}		
	return $('#YahooUser').dialog('isOpen');
	}  
function YahooWin6Open(){
	var html=$("#dialog6").html();
	if(html.length==0){return false;}		
	return $('#dialog6').dialog('isOpen');
	} 
function YahooWin4Open(){
	var html=$("#dialog4").html();
	if(html.length==0){return false;}			
	return $('#dialog4').dialog('isOpen');
	} 
function YahooWin5Open(){
	var html=$("#dialog5").html();
	if(html.length==0){return false;}		
	return $('#dialog5').dialog('isOpen');
	} 
function YahooWin3Open(){
	var html=$("#dialog3").html();
	if(html.length==0){return false;}		
	return $('#dialog3').dialog('isOpen');
	} 
function YahooWin2Open(){
	var html=$("#dialog2").html();
	if(html.length==0){return false;}		
	return $('#dialog2').dialog('isOpen');} 
function WinORGOpen(){
	var html=$("#WinORG").html();
	if(html.length==0){return false;}		
	return $('#WinORG').dialog('isOpen');
	}

function IfWindowsOpen(){
	if(RTMMailOpen()){return true;}
	RTMMailHide();
	
	if(YahooWinSOpen()){return true;}
	YahooWinSHide();
	
	if(YahooWinOpen()){return true;}
	YahooWinHide();
	
	if(YahooWin5Open()){return true;}
	YahooWin5Hide();
	
	if(YahooLogWatcherOpen()){return true;}
	YahooLogWatcherHide();
	
	if(YahooSetupControlOpen()){return true;}
	YahooSetupControlHide();
	
	if(YahooSearchUserOpen()){return true;}
	YahooSearchUserHide();
	
	if(YahooUserOpen()){return true;}
	YahooUserHide();
	
	if(YahooWin6Open()){return true;}
	YahooWin6Hide();
	
	if(YahooWin4Open()){return true;}
	YahooWin4Hide();
	if(YahooWin5Open()){return true;}
	YahooWin5Hide();
	
	if(YahooWin3Open()){return true;}
	YahooWin3Hide();
	
	if(YahooWin2Open()){return true;}
	YahooWin2Hide();
	
	if(WinORGOpen()){return true;}
	WinORGHide();
	return false;
	}



function RefreshTab(id){
	var $tabs = $('#'+id).tabs();
	var selected =$tabs.tabs('option', 'selected'); 
	$tabs.tabs( 'load' , selected );
}

function DisableFieldsFromId(idToParse){
	$('input,select,hidden,textarea', '#'+idToParse).each(function() {
	 	var $t = $(this);
	 	var id=$t.attr('id');
	 	var value=$t.attr('value');
	 	var type=$t.attr('type');
	 	document.getElementById(id).disabled=true;
	});	
	
}
function EnableFieldsFromId(idToParse){
	$('input,select,hidden,textarea', '#'+idToParse).each(function() {
	 	var $t = $(this);
	 	var id=$t.attr('id');
	 	var value=$t.attr('value');
	 	var type=$t.attr('type');
	 	document.getElementById(id).disabled=false;
	});	
	
}

function SelectTabID(tabid,num){
	var $tabs = $('#'+tabid).tabs();
	$tabs.tabs( 'select' , num );
}

function RefreshLeftMenu(){
	LoadAjax('TEMPLATE_LEFT_MENUS','/admin.tabs.php?left-menus=yes');	
}

var x_CacheOff= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
	RefreshLeftMenu();
	if(document.getElementById('squid_main_config')){RefreshTab('squid_main_config');}
	if(document.getElementById('main_system_settings')){RefreshTab('main_system_settings');}
	if(document.getElementById('main_config_postfix_security')){RefreshTab('main_config_postfix_security');}
	if(document.getElementById('org_main')){RefreshTab('org_main');}
	if(document.getElementById('main_config_samba')){RefreshTab('main_config_samba');}
	if(document.getElementById('main_squidcachperfs')){RefreshTab('main_squidcachperfs');}
	if(document.getElementById('main_group_config')){RefreshTab('main_group_config');}
	if(document.getElementById('main_config_postfix')){RefreshTab('main_config_postfix');}
	if(document.getElementById('main_post_perfs_tabs')){RefreshTab('main_post_perfs_tabs');}
	if(document.getElementById('main_config_dhcpd')){RefreshTab('main_config_dhcpd');}
	if(document.getElementById('admin_perso_tabs')){RefreshTab('admin_perso_tabs');}
	
	
	
	
}

function CacheOff(){
	var XHR = new XHRConnection();
	XHR.appendData('cache','yes');
	XHR.sendAndLoad('CacheOff.php', 'GET',x_CacheOff);	
	}

var x_remove_cache= function (obj) {
	var response=obj.responseText;
	RefreshLeftMenu();
}

function remove_cache(){
	var XHR = new XHRConnection();
	XHR.appendData('cache','yes');
	XHR.sendAndLoad('/CacheOff.php', 'GET',x_remove_cache);
}


function execJS(node){
  var bSaf = (navigator.userAgent.indexOf('Safari') != -1);
  var bOpera = (navigator.userAgent.indexOf('Opera') != -1);
  var bMoz = (navigator.appName == 'Netscape');

  if (!node) return;

  /* IE wants it uppercase */
  var st = node.getElementsByTagName('SCRIPT');
  var strExec;

  for(var i=0;i<st.length; i++)
  {
    if (bSaf) {
      strExec = st[i].innerHTML;
      st[i].innerHTML = "";
    } else if (bOpera) {
      strExec = st[i].text;
      st[i].text = "";
    } else if (bMoz) {
      strExec = st[i].textContent;
      st[i].textContent = "";
    } else {
      strExec = st[i].text;
      st[i].text = "";
    }

    try {
      var x = document.createElement("script");
      x.type = "text/javascript";

      /* In IE we must use .text! */
      if ((bSaf) || (bOpera) || (bMoz))
        x.innerHTML = strExec;
      else x.text = strExec;

      document.getElementsByTagName("head")[0].appendChild(x);
    } catch(e) {
      alert(e);
    }
  }
};        
        
        
function Default_ApplyConfigPostfix(){Loadjs('/postfix.compile.php');}
        
        
function LoadIframe(iframe_id){
	var iframeids=[iframe_id]
	var iframehide="yes"
	var getFFVersion=navigator.userAgent.substring(navigator.userAgent.indexOf("Firefox")).split("/")[1]
	var FFextraHeight=parseFloat(getFFVersion)>=0.1? 16 : 0 


		var dyniframe=new Array()
			for (i=0; i<iframeids.length; i++){
				if (document.getElementById){ 
					dyniframe[dyniframe.length] = document.getElementById(iframeids[i]);
					if (dyniframe[i] && !window.opera){
						dyniframe[i].style.display="block"
						if (dyniframe[i].contentDocument && dyniframe[i].contentDocument.body.offsetHeight) //ns6 syntax
							dyniframe[i].height = dyniframe[i].contentDocument.body.offsetHeight+FFextraHeight; 
						else if (dyniframe[i].Document && dyniframe[i].Document.body.scrollHeight) //ie5+ syntax
							dyniframe[i].height = dyniframe[i].Document.body.scrollHeight;
						}
					}
				
					if ((document.all || document.getElementById) && iframehide=="no"){
						var tempobj=document.all? document.all[iframeids[i]] : document.getElementById(iframeids[i])
						tempobj.style.display="block"
					}
				}				
	

}	

		
		
		

	
function Tree_Internet_domain_delete_transport(MyDomain,ou,suffix){
	var XHR = new XHRConnection();
	XHR.appendData('Tree_Internet_domain_delete_transport',MyDomain);
	XHR.appendData('ou',ou);
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('domains.php', 'GET');	
	ReloadBranch('ou:ou=' + ou + ','+ suffix);	
	}
	


function Tree_ou_Add_user(ou,suffix){
	var MyUserName;
	var input_text=document.getElementById('inputbox add user').value;
if (MyUserName=prompt(input_text)){
		var XHR = new XHRConnection();
		XHR.appendData('Tree_ou_Add_user',MyUserName);
		XHR.appendData('ou',ou);
		XHR.setRefreshArea('rightInfos');
		XHR.sendAndLoad('domains.php', 'GET');	
		ReloadBranch('ou:ou=' + ou + ','+ suffix);			
		}	
}


function Tree_group_edit1(gid,ou,suffix){
		var XHR = new XHRConnection();
		XHR.appendData('Tree_group_edit1',gid);
		XHR.appendData('ou',ou);
		XHR.appendData('description',document.getElementById("description").value);
		XHR.appendData('group_name',document.getElementById("group_name").value);
		XHR.setRefreshArea('rightInfos');
		XHR.sendAndLoad('domains.php', 'GET');	
		ReloadBranch('ou:ou=' + ou + ','+ suffix);		
		}
		
function Tree_group_delete(gid,ou,suffix){
		var XHR = new XHRConnection();
		var input_text=document.getElementById('inputbox delete group').value;
		if(confirm(input_text)){
		XHR.appendData('Tree_group_delete',gid);
		XHR.appendData('ou',ou);
		XHR.setRefreshArea('rightInfos');
		XHR.sendAndLoad('domains.php', 'GET');	
		ReloadBranch('ou:ou=' + ou + ','+ suffix);		
		}		
		}
	


function TreeKavSelect(branch){
	var id=branch.getId();
if (document.getElementById("windows").style.left==''){
	document.getElementById("windows").style.left=xMousePos - 250 + 'px';
	document.getElementById("windows").style.top='100px';
				}
			document.getElementById("windows").style.height='auto';
    		
			document.getElementById("windows").style.width='750px';
			document.getElementById("windows").style.zIndex='3000';
    		document.getElementById("windows").style.visibility="visible";
				
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('TreeKasSelect',id);
		XHR.sendAndLoad('users.kav.php', 'GET');		

}

function TreeKasSelect(branch){
	var id=branch.getId();
if (document.getElementById("windows").style.left==''){
	document.getElementById("windows").style.left=xMousePos - 250 + 'px';
	document.getElementById("windows").style.top='100px';
				}
			document.getElementById("windows").style.height='auto';
    		
			document.getElementById("windows").style.width='750px';
			document.getElementById("windows").style.zIndex='3000';
    		document.getElementById("windows").style.visibility="visible";
				
		var XHR = new XHRConnection();
		XHR.setRefreshArea('windows');
		XHR.appendData('TreeKasSelect',id);
		XHR.sendAndLoad('users.kas.php', 'GET');		

}

function UserAddWhiteList(){
	var XHR = new XHRConnection();
	XHR.appendData('UserAddWhiteList',document.getElementById("white").value);
        document.getElementById('whitelist').innerHTML='<center style="margin:20px;padding:20px"><img src="/img/wait.gif"></center>';
	XHR.setRefreshArea('whitelist');
	XHR.sendAndLoad('users.aswb.php', 'GET');			
}


function UserAddBlackList(){
	var XHR = new XHRConnection();
	XHR.appendData('UserAddBlackList',document.getElementById("black").value);
        document.getElementById('blacklist').innerHTML='<center style="margin:20px;padding:20px"><img src="/img/wait.gif"></center>';
	XHR.setRefreshArea('blacklist');
	XHR.sendAndLoad('users.aswb.php', 'GET');	
	
}
		
function UserDeleteWhiteList(mail,userid){
	var XHR = new XHRConnection();
        document.getElementById('whitelist').innerHTML='<center style="margin:20px;padding:20px"><img src="/img/wait.gif"></center>';
	XHR.appendData('UserDeleteWhiteList',mail);
	XHR.appendData('UserDeleteWhiteUid',userid);
	XHR.setRefreshArea('whitelist');
	XHR.sendAndLoad('users.aswb.php', 'GET');	
	}
function UserDeleteBlackList(mail,userid){
	var XHR = new XHRConnection();
        document.getElementById('blacklist').innerHTML='<center style="margin:20px;padding:20px"><img src="/img/wait.gif"></center>';
	XHR.appendData('UserDeleteBlackList',mail);
	XHR.appendData('UserDeleteBlackListUid',userid);
	XHR.setRefreshArea('blacklist');
	XHR.sendAndLoad('users.aswb.php', 'GET');	
	}	
	
function index_LoadStatus(){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('status');	
	XHR.appendData('SelectBranch',memory_branch);
	XHR.sendAndLoad('tree.functions.php', 'GET');
	
}
   
                

			
			
function WaitingPlease(){
	
var html='<fieldset><legend>Service operation</legend><table><tr><td width=1%><img src="/img/wait.gif"></td><td style="font-size:14px;font-weight:bolder;color:red">Waiting&nbsp;&nbsp;</td></tr></table></fieldset>'	
	return html;	
}	
function SaveFetchForm(){
	ParseForm('ffmFetch','users.account.php',true);
	var vadd='';
	if(document.getElementById("array_num")){
		vadd='&Fetchedit='+document.getElementById("array_num").value;
	}
	document.location.href='users.account.php?tab=1'+vadd;
	
}	
function TreeFetchmailShowServer(server_pool){
	LoadWindows(450);
	var XHR = new XHRConnection();
	XHR.appendData('TreeFetchmailShowServer',server_pool);
	XHR.setRefreshArea('windows');
	XHR.sendAndLoad('domains.php', 'GET');
	
}
function TreeArticaSaveSettings(){
	var XHR = new XHRConnection();
	ParseForm('ffmArtica1','domains.php',true);
	XHR.setRefreshArea('rightInfos');
	XHR.appendData('SelectBranch','Root');
	XHR.sendAndLoad('tree.functions.php', 'GET');	
}

var x_TreeFetchMailApplyConfig= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
	
}

function TreeFetchMailApplyConfig(){
	var XHR = new XHRConnection();
	XHR.appendData('TreeFetchMailApplyConfig','yes');
	XHR.sendAndLoad('domains.php', 'GET',x_TreeFetchMailApplyConfig);
	}
function TreePostfixHeaderCheckInfoActions(){
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixHeaderCheckInfoActions',document.getElementById("fields_action").value);
	XHR.setRefreshArea('TreeRegexFiltersexplain');
	XHR.sendAndLoad('tree.listener.postfix.php', 'GET');	
	}	
function TreePostfixAddHeaderCheckRule(num){
	LoadWindows(650,450,'tree.listener.postfix.php','TreePostfixAddHeaderCheckRule='+num);
		
}
function TreePostfixDeleteHeaderCheckRule(num){
	var XHR = new XHRConnection();	
	XHR.setRefreshArea('rightInfos');
	XHR.appendData('TreePostfixDeleteHeaderCheckRule',num);
	XHR.sendAndLoad('tree.listener.postfix.php', 'GET');	
	}
function TreePostfixHeaderCheckUpdateForm(){
	ParseForm('TreeRegexFilterRuleForm','tree.listener.postfix.php',true)
	var XHR = new XHRConnection();
	XHR.appendData('SelectBranch','settings:postfix:rules');
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('tree.functions.php', 'GET');	
	}


function LoadTree(params){
	var XHR = new XHRConnection();
	XHR.appendData('SelectBranch',params);
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('tree.functions.php', 'GET');	
}

var x_TreeAddNewOrganisation= function (obj) {
	var response=obj.responseText;
	if(response){alert(response);}
	
    if(document.getElementById('TEMPLATE_LEFT_MENUS')){LoadAjax('TEMPLATE_LEFT_MENUS','admin.tabs.php?left-menus=yes');}	
	
    CacheOff();
    
    if(document.getElementById('admin_perso_tabs')){
    	RefreshTab('admin_perso_tabs');
    	
    }
    	
    
    
    if(document.getElementById('orgs')){
             if(document.getElementById('ajaxmenu')){
                 YahooWin5(750,'domains.index.php?ajaxmenu=yes');
                 return;
             }
            LoadAjax('orgs','domains.index.php?ShowOrganizations=yes');
        }
}

function TreeAddNewOrganisation(){
	var texte=document.getElementById("add_new_organisation_text").value
	var org=prompt(texte,'');
	if(org){
		var XHR = new XHRConnection();
		var animated="/img/wait_verybig.gif";
		if(document.getElementById("LoadAjaxPicture")){animated=document.getElementById("LoadAjaxPicture").value;}		
		XHR.appendData('TreeAddNewOrganisation',org);
		if(document.getElementById('orgs')){document.getElementById('orgs').innerHTML='<center style="width:100%"><img src='+animated+'></center>';}
		if(document.getElementById('TEMPLATE_LEFT_MENUS')){document.getElementById('TEMPLATE_LEFT_MENUS').innerHTML='<center style="width:100%"><img src=img/wait_verybig.gif></center>'; }
		XHR.sendAndLoad('domains.php', 'GET',x_TreeAddNewOrganisation);
		}
	
}
function TreeDeleteOrganisation(org){
	var texte=document.getElementById("delete_organisation_text").value;
	var res=confirm(texte);
	if(res){
	var XHR = new XHRConnection();
		XHR.appendData('TreeDeleteOrganisation',org);
		XHR.sendAndLoad('domains.php', 'GET',x_TreeFetchMailApplyConfig);	
		ReloadBranch('server:organisations');	
		LoadTree('server:organisations');
	}
}
function TreeSynchronyzeMailBoxes(){
		var XHR = new XHRConnection();
		XHR.appendData('TreeSynchronyzeMailBoxes','yes');
		XHR.sendAndLoad('domains.php', 'GET',x_TreeFetchMailApplyConfig);	
		LoadTree('applications:cyrus');
		ReloadBranch('applications:cyrus');	
			
}

function LoadUserSectionAjax(num,dn){
	var user_id=document.getElementById('user_id').value;
        var uri='';
        if(!dn){dn='';}
        if(document.getElementById('DnsZoneName')){uri='&zone-name='+ document.getElementById('DnsZoneName').value; }
        LoadAjax('userform','domains.edit.user.php?userid='+ user_id + '&ajaxmode=yes&section='+num+uri+'&dn='+dn)
	}
function LoadUserAliasesAjax(num){
        var user_id=document.getElementById('user_id').value;
        if(!document.getElementById('user_id')){alert('no');}
        LoadAjax('userform','domains.edit.user.php?userid='+ user_id + '&ajaxmode=yes&section=aliases&aliases-section='+num);
}


function TreeUserMailBoxForm(dn){
	LoadWindows(450);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('TreeUserMailBoxForm',dn);
	XHR.sendAndLoad('domains.php', 'GET');
}
function TreeOuLoadPageFindUser(ou){
	LoadFind(450);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('find');
	XHR.appendData('TreeOuLoadPageFindUser',ou);
	XHR.sendAndLoad('domains.php', 'GET');	
}
function TreeOuFindUser(ou){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('find');
	XHR.appendData('TreeOuLoadPageFindUser',ou);
	XHR.appendData('TreeOuFindUser',document.getElementById("Tofind").value);
	XHR.sendAndLoad('domains.php', 'GET');	
}
function TreePostfixBuildConfiguration(){
	var XHR = new XHRConnection();
	XHR.appendData('TreePostfixBuildConfiguration','yes');
	XHR.sendAndLoad('tree.listener.postfix.php','GET', x_TreeFetchMailApplyConfig);	
	LoadTree('applications:postfix');
}
function TreeAveServerLicenceDeleteKey(licenceFile){
	var XHR = new XHRConnection();
	XHR.appendData('TreeAveServerLicenceDeleteKey',licenceFile);
	XHR.sendAndLoad('tree.listener.postfix.php','GET', x_TreeFetchMailApplyConfig);
	LoadTree('settings:aveserver:licence');
	
}
function artica_ldap_settings(){
	LoadWindows(450);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('artica_ldap_settings','yes');	
	XHR.sendAndLoad('tree.functions.php', 'GET');
	}
function TreeLoadKas3Tab(num){
var XHR = new XHRConnection();
	XHR.appendData('SelectBranch','settings:kas3:generalSettings');
	XHR.appendData('tab',num);
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('tree.functions.php', 'GET');		
	}
	

function TreeProcMailRules(){
	LoadWindows(450);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('TreeProcMailRules','yes');	
	XHR.sendAndLoad('procmail.functions.php', 'GET');	
	
}
function ProcmailAddRule(array_number){
	LoadWindows(450);
	var XHR = new XHRConnection();
	XHR.setRefreshArea('windows');
	XHR.appendData('ProcmailAddRule',array_number);	
	XHR.sendAndLoad('procmail.functions.php', 'GET');	
	
}
function ProcMailRuleMove(num,move,other){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('rules');
	XHR.appendData('ProcMailRuleMove',num);
	XHR.appendData('direction',move);		
	XHR.sendAndLoad('procmail.functions.php', 'GET');		
	}
function ProcMailRuleDelete(num,other){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('rules');
	XHR.appendData('ProcMailRuleDelete',num);
	XHR.sendAndLoad('procmail.functions.php', 'GET');	
	
}
function TreeProcMailApplyConfig(){
var XHR = new XHRConnection();
	XHR.setRefreshArea('rules');
	XHR.appendData('TreeProcMailApplyConfig','yes');
	XHR.sendAndLoad('procmail.functions.php', 'GET',x_TreeFetchMailApplyConfig);		
}
function FindUser(){
    var id='';
    mem_search=0; 
    var ss=document.getElementById("finduser").value;
    if(isXSS(ss)){alert('NO XSS !');return;}
    var findstr=escape(ss);
    YahooSearchUser('850','domains.manage.users.index.php?SearchUserNull='+findstr,findstr);
    FindUserTimeout();
}

function FindUserTimeout(){
	 mem_search=mem_search+1;
	 if(mem_search>20){
		 alert('time-out -> FindUserTimeout(); could not perform request');
		 return;
	 }
	 if(!document.getElementById('SearchUserNull')){
		 setTimeout("FindUserTimeout()",500);
		 return;
	 }
	 mem_search=0;
	 LoadAjax('SearchUserNull','domains.manage.users.index.php?finduser='+document.getElementById("finduser").value);
}

function isXSS(TheString){
	
	var pos=(" "+TheString+" ").indexOf("<");
	if(pos>0){return true;}
	pos=(" "+TheString+" ").indexOf("</scrip");
	if(pos>0){return true;}
	
	pos=(" "+TheString+" ").indexOf("function (");
	if(pos>0){return true;}	
	
	pos=(" "+TheString+" ").indexOf("function(");
	if(pos>0){return true;}		
	
	return false;
	
}



	
			
function LeftMenusSwitch(eId, thisImg, state) {
	if (e = document.getElementById(eId)) {
		if (state == null) {
			state = e.style.display == 'none';
			e.style.display = (state ? '' : 'none');
		}
		//...except for this, probably a better way of doing this, but it works at any rate...
		if (state == true){				
			Set_Cookie('ARTICA-MENU_'+eId, '1', '3600', '/', '', '');
			document.getElementById(thisImg).src="/img/fullbullet-down.gif";
                      //  MyHref(document.getElementById(eId+'_link').value);
		}
		if (state == false){
			Delete_Cookie('ARTICA-MENU_'+eId, '/', '');
			document.getElementById(thisImg).src="/img/fullbullet.gif";
		}
	}
}

function LeftMenushide(){
       var re = new RegExp(/ARTICA-MENU_(.+?)_/);
       var m;
       var id;
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
                m=re.exec(c);
                if(m){
                     id=m[1];   
                     if(document.getElementById(id+'_menubullet')){document.getElementById(id+'_menubullet').src="/img/fullbullet-down.gif";}
                     if(document.getElementById(id+'_menubox')){document.getElementById(id+'_menubox').style.display='block';}
                }
		
	}
	return null;
}

function SwitchOrgTabs(num,ou){
     Delete_Cookie('SwitchOrgTabs', '/', '');
     Set_Cookie('SwitchOrgTabs', num, '3600', '/', '', '');
     Set_Cookie('SwitchOrgTabsOu', ou, '3600', '/', '', ''); 
     LoadAjax('org_main','domains.manage.org.index.php?org_section=0&SwitchOrgTabs='+num +'&ou=' +ou);   
}


function DeleteAllCookies(){
   Delete_Cookie('SwitchOrgTabs', '/', '');
   Delete_Cookie('SwitchOrgTabsOu', '/', '');
   Delete_Cookie('ARTICA-POSTFIX-REGEX-PAGE-DIV', '/', '');
   Delete_Cookie('ArticaIsDefaultSelectedGroupId', '/', '');
   Delete_Cookie('ARTICA-POSTFIX-REGEX-PAGE-URI', '/', '');
   Delete_Cookie('ArticaIsDefaultSelectedGroupIdIndex', '/', '');   
   
   
 var re = new RegExp(/ARTICA-MENU_(.+?)_/);
       var m;
       var id;
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
                m=re.exec(c);
                if(m){
                  Delete_Cookie(c, '/', '');
                }
        }
}


function OrgStartPage(){
   var ouselected=document.getElementById('ouselected').value;
   if(ouselected.length>0){Set_Cookie('SwitchOrgTabsOu',ouselected, '3600', '/', '', '');}
   LoadAjax('RightOrgSection','domains.manage.org.index.php?RightOrgSection=yes&ou='+Get_Cookie('SwitchOrgTabsOu'));
   LoadAjax('org_main','domains.manage.org.index.php?org_section=0&SwitchOrgTabs=' + Get_Cookie('SwitchOrgTabs') + '&ou='+Get_Cookie('SwitchOrgTabsOu')+'&mem=yes');        
}
function ChangeOrg(){
        MyHref('domains.manage.org.index.php?ou='+ document.getElementById('ouList').value);
}

function FetchMailParseConfig(){
       var proto=document.getElementById('proto').value;
       if(proto=='hotmail'){
          document.getElementById('poll').value='hotmail';
          document.getElementById('poll').disabled=true;
          document.getElementById('port').value='http';
          document.getElementById('port').disabled=true;
          document.getElementById('timeout').value='0';
          document.getElementById('timeout').disabled=true;
          document.getElementById('interval').disabled=true;
          document.getElementById('tracepolls').disabled=true;
          document.getElementById('ssl').disabled=true;
          document.getElementById('hotmailexplain').innerHTML=document.getElementById('hotmail_text').value;
          return true;
       }
       
       if(proto=='httpp'){
          document.getElementById('poll').value='127.0.0.1';
          document.getElementById('poll').disabled=true;
          document.getElementById('port').value='113';
          document.getElementById('port').disabled=true;
          document.getElementById('ssl').disabled=true;
          document.getElementById('hotmailexplain').innerHTML=document.getElementById('hotwayd_text').value;
          return true;
       }
       
          
          
          document.getElementById('poll').disabled=false;
          document.getElementById('port').value='';
          document.getElementById('port').disabled=false;
          document.getElementById('timeout').value='';
          document.getElementById('timeout').disabled=false;
          document.getElementById('interval').disabled=false;
          document.getElementById('tracepolls').disabled=false;
          document.getElementById('ssl').disabled=false;
          document.getElementById('hotmailexplain').innerHTML='';
        }
        
        
function ExecuteIDScript(id){
        var did=document.getElementById(id);
        var sCodeJavascript = did.getElementsByTagName("script");
        for (var i = 0; i < sCodeJavascript.length; i++){
               var contentScript = sCodeJavascript[i];
               if (contentScript.src && contentScript.src != "") z = 1;
               else{
                       window.eval(contentScript.innerHTML);
                }
        }
}



var x_YahooTreeFolders= function (obj) {
       document.getElementById("dialog1_content").innerHTML=obj.responseText;
       tree = new TafelTree('folderTree', Folerstruct, 'img/', '100%', 'auto');
       tree.generate();   
}

var x_YahooTreeFoldersWhatToRefresh= function (obj) {
     page=CurrentPageName();
     if (page=='domains.edit.group.php'){
         if(document.getElementById("groupid")){
                SharedFolders(document.getElementById("groupid").value);
         }
        
     }
     
     if(page=='samba.index.php'){
        LoadAjax('main_config','samba.index.php?main=shared_folders')
     }
     
}

function YahooSelectedFolders(branch){
        page=CurrentPageName();
        branchid=branch.getId();
        if(branchid!=='/'){
           var text=document.getElementById("YahooSelectedFolders_ask").value;
           text=text+'\n'+branchid;
           if (confirm(text)){
                
                
                
              YAHOO.example.container.dialog1.hide();
              var XHR = new XHRConnection();
              XHR.appendData('AddTreeFolders',branchid);
              if(document.getElementById("groupid")){XHR.appendData('groupid',document.getElementById("groupid").value);}
              if(document.getElementById("YahooSelectedFolders_ask2")){
                        var YahooSelectedFolders_ask2=prompt(document.getElementById("YahooSelectedFolders_ask2").value,ExtractPathName(branchid));
                        XHR.appendData('YahooSelectedFolders_ask2',YahooSelectedFolders_ask2);
                }
              
              XHR.sendAndLoad(page, 'GET',x_YahooTreeFoldersWhatToRefresh); 
           }  
                
        }
}



function YahooTreeClick(branch,status){
     var branch_id=branch.getId();
     page=CurrentPageName();
     if(document.getElementById('TreeRightInfos')){
        LoadAjax('TreeRightInfos',page+'?TreeRightInfos='+branch_id);
     }
        
     return true;   
}


var x_YahooTreeAddSubFolder= function (obj) {
    page=CurrentPageName();
    var branch = tree.getBranchById(mem_branch_id);  
   var item = {
        "id" : mem_item,
        "txt" : ExtractPathName(mem_item),
        'onopenpopulate' : YahooTreeFoldersPopulate,
	'openlink' : 'yahoo.tree.populate.php?p='+page,
	'onclick' : YahooTreeClick,
	'canhavechildren' : true,
	'ondblclick' : YahooSelectedFolders,
        'img':'folder.gif',
        'imgopen':'folderopen.gif', 
	'imgclose':'folder.gif'}
var newBranch = branch.insertIntoLast(item);
}
var x_YahooTreeDelSubFolder= function (obj) {
    page=CurrentPageName();
    tree.removeBranch(mem_branch_id);
    
}

function YahooTreeDelSubFolder(){
  page=CurrentPageName();
      var text=document.getElementById('del_folder_name').value;
      var base=document.getElementById('YahooBranch').value;
      mem_branch_id=base;
      if(confirm(text)){
        var XHR = new XHRConnection();
        mem_item=base;
        XHR.appendData('rmdirp',base);
        XHR.sendAndLoad('yahoo.tree.populate.php', 'GET',x_YahooTreeDelSubFolder);
        }              
        
}

function YahooTreeAddSubFolder(){
      page=CurrentPageName();
      var text=document.getElementById('give_folder_name').value;
      var base=document.getElementById('YahooBranch').value;
      mem_branch_id=base;
      var newfolder=prompt(text,'New folder');
      if(newfolder){
        var XHR = new XHRConnection();
        mem_item=base + '/'+newfolder;
        XHR.appendData('mkdirp',base + '/'+newfolder);
        XHR.sendAndLoad(page, 'GET',x_YahooTreeAddSubFolder);
        }       
}


function YahooTreeFolders(width,page){
        if(!width){width='300';}
        title='Browse...';
        YAHOO.example.container.dialog1.show();
        document.getElementById("dialog1").style.width=width + 'px';
        document.getElementById("dialog1_title").innerHTML=title;
        var XHR = new XHRConnection();
	XHR.appendData('GetTreeFolders','yes');
	XHR.sendAndLoad(page, 'GET',x_YahooTreeFolders);        

}
function YahooTreeFoldersPopulate (branch, response) {
//alert(response);
//alert(branch);

if (response.length>0) {
return response;

}
else {
        return false;
        }

}


function ApplySettings(page){
     mem_page=page;
     maxcount=4;
     count_action=0;
     YahooWin(440,page+'?ApplySettings=-1');
     setTimeout('ApplySettings_run(0)',1500);
    }

var x_ApplySettings_run=function(obj){
      var tempvalue=obj.responseText;
      document.getElementById(memory_branch).innerHTML=tempvalue;
      count_action=count_action+1;
      
      if(count_action<maxcount){
        setTimeout('ApplySettings_run('+count_action+')',1500);
      }
}


function ApplySettings_run(number){
memory_branch='message_'+number;
if( document.getElementById(memory_branch)){
        var XHR = new XHRConnection();
        document.getElementById(memory_branch).innerHTML='<img src="/img/wait.gif">';
	XHR.appendData('ApplySettings',number);
	XHR.sendAndLoad(mem_page, 'GET',x_ApplySettings_run);  
        }
}

function ExtractPathName(path){
        var reg=new RegExp("[\/]+", "g");
         tableau=path.split(reg);
         return tableau[tableau.length-1];
}

function ConfigureYourserver(title){
	if(!document.getElementById('QuickLinksTop')){QuickLinks();}else{QuickLinksHide();}
}

function SquidStatsInterface(){
	if(!document.getElementById('QuickLinksTop')){SquidQuickLinks();}else{QuickLinksHide();}
	}


function ConfigureYourserver_Cancel(){
        var X;
   if(document.getElementById('ConfigureYourserverStart')){
    if(document.getElementById('ConfigureYourserverStart').checked){
        X=1;
    }else{X=0;}
    var XHR = new XHRConnection();
    XHR.appendData('cancel',X);
    XHR.sendAndLoad('firstwizard.php', 'GET');
   }
}


function GetAllIdElements(pattern){
        var ie = (document.all) ? true : false;
        var elements = (ie) ? document.all : document.getElementsByTagName('*');
        var re = new RegExp(pattern);
        var a=new Array();
        var m;
  for (i=0; i<elements.length; i++){
        if (elements[i].id){
               var m=re.exec(elements[i].id);
               if(m){
                a.push(elements[i].id);
               }
        }
   }
  return(a);
}

function DeleteElementByID(eid){
	if(!document.getElementById(eid)){alert('unable to find ' + eid);return;}
	var who=document.getElementById(eid);
	who.parentNode.removeChild(who);
}

function ShowFileLogs(filename){
     YahooWin3('550','admin.index.php?ShowFileLogs='+filename);  
}

function PostfixPopupEvents(){
        s_PopUp("postfix.events.php?pop=true",450,400);
}

function logoffUser(){
        if(document.getElementById('isanuser')){
          if(document.getElementById('isanuser').value==1){
                MyHref('/logoff.php');
          }
        }


}

function logoff(){
        YahooWin(300,'/logoff.php?menus=yes','Logoff');
        setTimeout("logoffUser()",1000);
   }
   
function RestartComputer(){
        var text=document.getElementById('restart_computer_text').value;
        if(confirm(text)){
        	var XHR = new XHRConnection();
        	XHR.appendData('perform','reboot');
        	XHR.sendAndLoad('/logoff.php', 'GET');       
        }
 }
 
function ShutDownCOmputer(){
        var text=document.getElementById('shutdown_computer_text').value;
        if(confirm(text)){
        	var XHR = new XHRConnection();
        	XHR.appendData('perform','shutdown');
        	XHR.sendAndLoad('/logoff.php', 'GET');       
        }
 }

function IsNumeric(sText){
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
   if(!sText){
	   if(sText==0){return true;}
	   return false;}
 
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   
}

 
 function trim(str, chars) {
    return ltrim(rtrim(str, chars), chars);
}

function ltrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function rtrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

function loadssfile(filename){
 
  var fileref=document.createElement("link")
  fileref.setAttribute("rel", "stylesheet")
  fileref.setAttribute("type", "text/css")
  fileref.setAttribute("href", filename)
 
 if (typeof fileref!="undefined")
  document.getElementsByTagName("head")[0].appendChild(fileref)
}


function SwitchPassword(md,field){
var fontsize=document.getElementById(field).style.fontSize;
var stylesize=document.getElementById(field).style.width;
var type=document.getElementById(field).type;
var value=document.getElementById(field).value;
var padding=document.getElementById(field).style.padding;
if(type=='password'){
     document.getElementById(md).innerHTML="<input type='text' id='"+field+"' name='"+field+"' value='"+value+"'>";

}else{
    document.getElementById(md).innerHTML="<input type='password' id='"+field+"' name='"+field+"' value='"+value+"'>";
}
document.getElementById(field).style.fontSize=fontsize;
document.getElementById(field).style.width=stylesize;
document.getElementById(field).style.padding=padding;



}

function Loadjs(src){
	$.getScript(src);
	//$.ajax({ type: "GET", url: src, dataType: "script",error: function(){alert(src+':error');}});
	//$.ajax({ type: "GET", url: src, dataType: "script",error: function(){return;}});
//http://forum.jquery.com/topic/destroy-old-loaded-script
}



function applysettings_dansguardian(){
      Loadjs('/dansguardian.index.php?CompilePolicies=yes');  
        
}

function WizardFindMyNetworksMask(){
	YahooWin5('400','/index.gateway.php?popup-network-masks=yes');
	
}

function ParagrapheWhiteToYellow(id,switch_color){
	if(switch_color==0){
		document.getElementById(id+'_0').className='RLightyellowfg';
		document.getElementById(id+'_1').className='RLightyellow';
		document.getElementById(id+'_2').className='RLightyellow1';
		document.getElementById(id+'_3').className='RLightyellow2';
		document.getElementById(id+'_4').className='RLightyellow3';
		document.getElementById(id+'_5').className='RLightyellow4';		
		document.getElementById(id+'_6').className='RLightyellow5';
		document.getElementById(id+'_7').className='RLightyellow';
		document.getElementById(id+'_8').className='RLightyellow5';
		document.getElementById(id+'_9').className='RLightyellow4';
		document.getElementById(id+'_10').className='RLightyellow3';
		document.getElementById(id+'_11').className='RLightyellow2';
		document.getElementById(id+'_12').className='RLightyellow1';
		lightup(document.getElementById(id+'_img'), 100);
		
	
		
	}
	if(switch_color==1){
		document.getElementById(id+'_0').className='RLightWhitefg';
		document.getElementById(id+'_1').className='RLightWhite';
		document.getElementById(id+'_2').className='RLightWhite1';
		document.getElementById(id+'_3').className='RLightWhite2';
		document.getElementById(id+'_4').className='RLightWhite3';
		document.getElementById(id+'_5').className='RLightWhite4';		
		document.getElementById(id+'_6').className='RLightWhite5';
		document.getElementById(id+'_7').className='RLightWhite';
		document.getElementById(id+'_8').className='RLightWhite5';
		document.getElementById(id+'_9').className='RLightWhite4';
		document.getElementById(id+'_10').className='RLightWhite3';
		document.getElementById(id+'_11').className='RLightWhite2';
		document.getElementById(id+'_12').className='RLightWhite1';	
		lightup(document.getElementById(id+'_img'), 50);
		
	}
	
}

compteur_global_demarre();
setTimeout("compteur_global_demarre()",1000);

function LoadMasterTabs(){
	LoadAjax('BodyContentTabs','admin.tabs.php');	
	
}

function base64_decode (data) {
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');
    dec = this.utf8_decode(dec);

    return dec;
}

function utf8_decode (str_data) {

    var tmp_arr = [],
        i = 0,
        ac = 0,
        c1 = 0,
        c2 = 0,
        c3 = 0;

    str_data += '';

    while (i < str_data.length) {
        c1 = str_data.charCodeAt(i);
        if (c1 < 128) {
            tmp_arr[ac++] = String.fromCharCode(c1);
            i++;
        } else if (c1 > 191 && c1 < 224) {
            c2 = str_data.charCodeAt(i + 1);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
            i += 2;
        } else {
            c2 = str_data.charCodeAt(i + 1);
            c3 = str_data.charCodeAt(i + 2);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }
    }

    return tmp_arr.join('');
}



function base64_encode(text) {
	  var dwOctets = 0;
	  var nbChars = 0;
	  var ret = "";
	  var b;

	  for (i = 0; i < 3 * ((text.length + 2) / 3); i++) {
	    if (i < text.length) b = text.charCodeAt(i);
	    else b = 0;
	    dwOctets <<= 8;
	    dwOctets += b;
	    if (++nbChars == 3) {
	      for (j = 0; j < 4; j++) {
	        b = (dwOctets & 0x00FC0000) >> 18;
	        if (b < 26) ret += String.fromCharCode(b + 65);
	        else if (b < 52) ret += String.fromCharCode(b + 71);
	        else if (b < 62) ret += String.fromCharCode(b - 4);
	        else if (b == 62) ret += "+";
	        else if (b == 63) ret += "/";
	        dwOctets <<= 6;
	      }
	      dwOctets = 0;
	      nbChars = 0;
	    }
	  }

	  ret += "=";

	  return ret;
	}
