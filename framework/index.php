<?php
include_once("frame.class.inc");
echo build();

function build(){
	
	$title="Welcome On Artica-Postfix";
	$logo="logon2.png";
	
	if(is_file("/etc/artica-postfix/KASPER_MAIL_APP")){
		$title="Welcome On Artica For Kaspersky Mail Appliance";
		$logo="logon-k.png";
	}
	
	$interfaces=buildInterfaces();
	$articaport=GetArticaPort();
	$arrp=ArticaUserNamePassword();
	$version=trim(@file_get_contents("/usr/share/artica-postfix/VERSION"));
	$inter="
	<H2 style='border-bottom:1px solid #8F341B'>Administration Interfaces</H2>
	<p style='font-size:12px;margin-top:-10px;font-weight:bold'>Click on these links in order to open Artica interface:
	<table style='width:100%'>
	<tr>
		<td valign='top'>
	<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($interfaces) ){
		$inter=$inter."<tr>
			
			<td width=1%><img src='img/fleche-20-right.png'></td>
			<td>
			<a href='https://$ligne:$articaport' style='font-size:14px' target=_new>https://$ligne:$articaport</a></td></tR>";
	}
	
	$inter=$inter."</table>
	</td>
	<td valign='top'>
		<div style='font-size:18px'>Using this account:</div>
		<div style='font-size:16px'>Username:&nbsp;{$arrp[0]}</div>
		<div style='font-size:16px'>Password:&nbsp;{$arrp[1]}</div>
		
	</td>
	
	</tr>
	</table>
	</p>";
	
$html="
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
	<title>$title</title>
	<!-- default template  -->
	<meta name='keywords' content=''>
	<meta name='description' content=\"\">
	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=EmulateIE7\" />
	<link href='css/styles_main.css'    rel=\"styleSheet\"  type='text/css' />

	<link href='css/styles_header.css'  rel=\"styleSheet\"  type='text/css' />
	<link href='css/styles_middle.css'  rel=\"styleSheet\"  type='text/css' />
	<link href='css/styles_tables.css'  rel=\"styleSheet\"  type='text/css' />
	<link href=\"css/styles_rounded.css\" rel=\"stylesheet\"  type=\"text/css\" />
	<!--[if lt IE 7]>
	<link rel='stylesheet' type='text/css' href='css/styles_ie.css' />
	<![endif]-->
	<!--[if IE 7]>
	<link rel='stylesheet' type='text/css' href='css/styles_ie7.css' />
	<![endif]-->
		<link href=\"css/calendar.css\" rel=\"stylesheet\" type=\"text/css\">
		<link href=\"js/jqueryFileTree.css\" rel=\"stylesheet\" type=\"text/css\">
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/uploadify.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/artica-theme/jquery-ui-1.7.2.custom.css\" />

		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.jgrowl.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.cluetip.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery.treeview.css\" />
		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/thickbox.css\" media=\"screen\"/>
		<div id='PopUpInfos' style='position:absolute'></div>
		<div id='find' style='position:absolute'></div>
		<!-- Artica javascript  -->
		<!-- en  -->
		<!-- /usr/share/artica-postfix/ressources/language/en/.js  -->

		
		<script type=\"text/javascript\" language=\"javascript\" src=\"XHRConnection.js\"></script>
		<script type=\"text/javascript\" language=\"JavaScript\" src=\"mouse.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"default.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/cookies.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-1.3.2.min.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jqueryFileTree.js\"></script>

		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.easing.1.3.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-ui-1.7.2.custom.min.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/thickbox-compressed.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.simplemodal-1.3.3.min.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.jgrowl_minimized.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.cluetip.js\"></script>

		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.blockUI.js\"></script>
		<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery.treeview.min.js\"></script>
		<!-- js Artica  -->
		


</head>
<body>
<center>
<div style=\"width:900px;background-image:url(css/images/bg_header.gif);background-repeat:repeat-x;background-position:center top;margin:0px;padding:0px;\">

	<table style=\"width:100%;margin:0px;padding:0px;border:0px;\">
		<tr>
		    <td valign=\"top\" style='padding:0px;margin:0px;border:0px;padding-top:24px'>
			<div style=\"height:72px\">
				<table style=\"padding:0px;margin:0px;border:0px;margin-left:-6px;\">
				<tr>
			   		<td style='padding:0px;border:0px;' valign=\"top\" align=\"left\">
						
							<table style=\"margin:0px;border:0px;padding:0px;\">

							<tr>
			 				<td style=\"margin:0px;padding:0px;background-color:#005447\" width=\"160px\">
								<img src='css/images/logo.gif' style=\"margin:0px;padding:0px;\">
							</td>
							<td style=\"margin:0px;padding:0px;\" valign=\"middle\">
								<div style=\"margin-top:-7px;padding-left:5px\"> </div>
							</td>
							<td style=\"margin:0px;padding:0px;border:0px solid black\" valign=\"middle\" align='right' width=50%>

								
							</td>
							</tr>
							</table>
						
					  </td>
				</tr>
				<tr>
				  <td style='height:25px'>
					<div id='menus_2'><ul></ul></div id='menus_2'>
				 </td>

				</tr>
				</table>
		</div>
		     </td>
		  
			
		 
	    	</tr>
		<tr>
		<td valign=\"top\" colspan=2 style=\"margin:0px;padding:0px;padding-top:4px;background-color:white;\">	
<div id='middle'>
	<div id='content' style='background-color:white;'>

		<table style='width:100%'>
			<tr>
				<td valign='top' style='padding:0px;margin:0px;width:160px'>
					
				</td>
				<td valign='top' style='padding-left:3px'>
					<div id='template_users_menus'></div>
					<div id='BodyContentTabs'></div>
						<div id='BodyContent'>
							<h1 id='template_title'></h1>

							<!-- content -->
							
<script>
function SaveSession(){
	var template=document.getElementById('template').value;
	var lang=document.getElementById('lang').value;
	Set_Cookie('artica-template', template, '3600', '/', '', '');
	Set_Cookie('artica-language', lang, '3600', '/', '', '');
	var XHR = new XHRConnection();
	XHR.appendData('lang',lang);
	XHR.sendAndLoad('logon.php', 'GET');		
	
	
	location.reload();
}

function LoadModal(){
$('#loginform').modal({onOpen: function (dialog) {
	dialog.overlay.fadeIn('slow', function () {
		dialog.container.slideDown('slow', function () {
			dialog.data.fadeIn('slow');
		});
	});
}});



}


</script>
<H1 style='text-align:left'>$title v$version</h1>
	$inter

				
				<div style='width:667px;height:395px;background-image:url(img/$logo);background-repeat:no-repeat;border:1px solid #FFFFFF'>
				
				</div>
				
	
			
							<!-- content end -->
						</div>

				</td>
				<td valign='top'></td>
			</tr>	
	</table>	

	<div class='clearleft'></div>

	<div class='clearright'></div>
	</div id='content'>

</div id='middle'>
</td>
</tr>
<tr>
<td valign='top' align=left colspan=2 >
<div style='background-color:#736e6c;font-size:13px;color:white;height:25px;padding:0px;margin:0px;padding-top:5px;width:900px;text-align:center;margin-left:-5px;margin-bottom:-3px'>
<strong>Artica for postfix. Copyright 2006</strong>
</div>
</td>
</tr>
</table>

</div>
</center>
<div id=\"SetupControl\"></div>
<div id=\"dialogS\"></div> 
<div id=\"dialogT\"></div> 
<div id=\"dialog0\"></div> 
<div id=\"dialog1\"></div>
<div id=\"dialog2\"></div> 
<div id=\"dialog3\"></div>
<div id=\"dialog4\"></div>
<div id=\"dialog5\"></div>
<div id=\"dialog6\"></div>
<div id=\"YahooUser\"></div>
<div id=\"logsWatcher\"></div>
<div id=\"WinORG\"></div>
<div id=\"WinORG2\"></div>
<div id=\"RTMMail\"></div>

<div id=\"Browse\"></div>
<div id=\"SearchUser\"></div>
 
</body>
</html>
";	

return $html;
	
}


function buildInterfaces(){
	$unix=new unix();
	exec($unix->find_program("ifconfig"),$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#inet addr:(.+?)\s+#",$ligne,$re)){
			if($re[1]=="127.0.0.1"){continue;}
			$interface[]=$re[1];
		}
	}
	
	return $interface;
	
}

function GetArticaPort(){
	$results=explode("\n",@file_get_contents("/etc/lighttpd/lighttpd.conf"));
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#server\.port.+?([0-9]+)#",$ligne,$re)){
			return $re[1];
		}
	}
	
	
}


function ArticaUserNamePassword(){
	$results=explode("\n",@file_get_contents("/etc/ldap/slapd.conf"));
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#rootdn.+?cn=(.+?),#",$ligne,$re)){
			$username=$re[1];
		}
		if(preg_match("#rootpw\s+(.+)#",$ligne,$re)){
			$password=trim($re[1]);
		}		
	}
	
	
	return array($username,$password);
	
}


?>