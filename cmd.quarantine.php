<?php
	include_once('ressources/class.sockets.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.mysql.inc');
	$_GET["NO_SESSION"]='yes';
if(!isset($_GET["method"])){exit;}

switch ($_GET["method"]) {
	case 'release':release();break;
	case 'white':WhiteListSender();break;
	case 'deleteAllOther':deleteAllOther();break;
	case 'deleteAllYesterday':deleteAllYesterday();break;
	default:
		break;
}



function release(){
	$sock=new sockets();
	$res=$sock->getfile('releasemailmd5:'.$_GET["id"]);
	SinglePage(ParseLogs($res));
	}


function ParseLogs($datas){
	
	
$re=explode("\n",$datas);
	while (list ($key, $value) = each ($re) ){
		if($value<>null){
			if(preg_match('#Subject:<strong>(.+?)</strong>#',$value,$tb)){
				$value=str_replace($tb[1],htmlentities($tb[1]),$value);
			}
			if(!preg_match('#<div#',$value)){
				$results=$results . "<div style='width:90%'><code>$value</code></div>\n";
			}else{$results=$results .$value;}
		}
		
	}
	
	$tpl=new templates(null,null,nul,1);
	$results="<H1>{operation}</H1>$results";
	return $tpl->_ENGINE_parse_body($results);	
	
}


function SinglePage($content){
	
	$html="
	<html>
	<head>
	<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
	<script type='text/javascript' language='JavaScript' src='mouse.js'></script>
	<script type='text/javascript' language='javascript' src='XHRConnection.js'></script>
	<script type='text/javascript' language='javascript' src='default.js'></script>
	</head>
	<body width=100% style='background-color:white'> 
	<center>
	<div style='width:80%;border:1px solid #CCCCCC;padding:10px;margin:10px'>
		<div style='text-align:left'>$content</div>
	</div>
	</body>
	</html>";
	
	echo $html;
	
}
function WhiteListSender(){
	$sql="SELECT mail_from,mail_to from messages WHERE zMD5=\"{$_GET["id"]}\"";
	$result=QUERY_SQL($sql);
	$ligne=@sqlite3_fetch_array(QUERY_SQL($sql));
	$mail_from=$ligne["mail_from"];
	if($mail_from==null){
		SinglePage(ParseLogs("ERROR\n"));
		exit;
	}
	
	
	$ldap=new clladp();
	$upd["KasperkyASDatasAllow"]=$mail_from;
	$uid=$ldap->uid_from_email($ligne["mail_to"]);
	
if($uid==null){
		SinglePage(ParseLogs("ERROR\n"));
		exit;
	}	
	
	$hash=$ldap->UserDatas($uid);
	$dn=$hash["dn"];
	if(!$ldap->Ldap_add_mod($dn,$upd)){$error=$ldap->ldap_last_error."\n";}
	$sock=new sockets();
	$error=$error.$sock->getfile('releaseallmailfrommd5:'.$_GET["id"]);
	SinglePage(ParseLogs($error));	
}

function deleteAllOther(){
$sock=new sockets();
	$error=$error.$sock->getfile('deleteallmailfrommailtoother:'.$_GET["mail"]);
	SinglePage(ParseLogs($error));	
	
	
}
function deleteAllYesterday(){
$sock=new sockets();
	$error=$error.$sock->getfile('deleteallmailfrommailtoyesterday:'.$_GET["mail"]);
	SinglePage(ParseLogs($error));		
	
}

