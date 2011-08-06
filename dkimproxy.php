<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(isset($_POST["hostname"])){$_GET["hostname"]=$_POST["hostname"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup_index();exit;}
	if(isset($_GET["status"])){popup_status();exit;}
	if(isset($_GET["parms"])){popup();exit;}
	if(isset($_GET["dkimproxyEnabled"])){save();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_DKIMPROXY_TITLE}");
	
	$html="
		function dkimproxyLoad(){
			YahooWin4('650','$page?popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');
		}
	
	dkimproxyLoad();";
	
	echo $html;
	}
	
function popup_status(){
	
}
	
function popup_index(){
	$tpl=new templates;
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["parms"]='{settings}';
	$array["domains"]='{domains}';

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_dkimproxy style='width:100%;height:450px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_dkimproxy').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
}	
	
	
function popup(){
	$ou=base64_decode($_GET["ou"]);
	$page=CurrentPageName();
	if($_GET["hostname"]=="master"){$ipstr="127.0.0.1";}
	
	$main=new maincf_multi($_GET["hostname"],$ou,$ipstr);
	$dkimproxyEnabled=$main->GET("dkimproxyEnabled");
	$freeport=$main->GET("dkimproxy_listenport");
	if($freeport==null){
		$freeport=findFreePort();
		$main->SET_VALUE("dkimproxy_listenport",$freeport);
		}
		
		
	$method_arr=array("simple"=>"simple","relaxed"=>"relaxed","relaxed/relaxed"=>"relaxed/relaxed");
	
	$tpl=new templates();
	$explian=$tpl->_ENGINE_parse_body("{dkimproxy_selector_text}");
	
	$array=unserialize(base64_decode($main->GET_BIGDATA("dkimproxy_datas")));
	if($array["selector_name"]==null){$array["selector_name"]="selector1";}
	if($array["method"]==null){$array["method"]="simple";}
	
	$method=Field_array_Hash($method_arr,"method",$array["method"],null,null,0,"font-size:13px;padding:3px");
	
	$explian=str_replace("--selector--",$array["selector_name"],$explian);
	
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{enable_dkimproxyout}:</td>
		<td>". Field_checkbox("dkimproxyEnabled",1,$dkimproxyEnabled,"CheckdkimproxyEnabled()")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{selector_name}:</td>
		<td>". Field_text("selector_name",$array["selector_name"],"font-size:13px;padding:3px;width:100px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{dkimproxy_method}:</td>
		<td>$method</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td>". Field_text("listen_port",$freeport,"font-size:13px;padding:3px;width:50px")."</td>
	</tr>	
	<tr>
		<td colspan=2><div class=explain>$explian</div></td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","dkimproxySave()")."</td>
	</tr>
	</table>
	
	<script>
	

	var x_dkimproxySave=function(obj){
	      var tempvalue=trim(obj.responseText);
	      if(tempvalue.length>3){alert(tempvalue);}
		  RefreshTab('main_config_dkimproxy');
	}	
	
		function dkimproxySave(num){
		      var XHR = new XHRConnection();
		      if(document.getElementById('dkimproxyEnabled').checked){
		      	    XHR.appendData('dkimproxyEnabled',1);
				}else{
					XHR.appendData('dkimproxyEnabled',0);
				}
		      XHR.appendData('ou','{$_GET["ou"]}');
		      XHR.appendData('hostname','{$_GET["hostname"]}');   
		      XHR.appendData('selector_name',document.getElementById('selector_name').value);  
		      XHR.appendData('listen_port',document.getElementById('listen_port').value);
		      XHR.appendData('method',document.getElementById('method').value);

		      
		      XHR.sendAndLoad('$page', 'GET',x_dkimproxySave);
		      
		}

	function CheckdkimproxyEnabled(){
		document.getElementById('selector_name').disabled=true;
		document.getElementById('listen_port').disabled=true;
		document.getElementById('method').disabled=true;
		
		 if(document.getElementById('dkimproxyEnabled').checked){
				document.getElementById('selector_name').disabled=false;
				document.getElementById('listen_port').disabled=false;
				document.getElementById('method').disabled=false;
			}
	}
	
	CheckdkimproxyEnabled();
	</script>";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
	
}


function findFreePort(){
	$port[48100]=48100;
	$q=new mysql();
	$sql="SELECT `value` FROM postfix_multi WHERE `key`='dkimproxy_listenport'";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$port[$ligne["value"]]=$ligne["value"];
	}
	
	krsort($port);
	while (list ($num, $pp) = each ($port) ){
		$p[]=$num;
	}
	
	return $p[0]+1;
	
}

	
function save(){
	$ou=base64_decode($_GET["ou"]);
	if($_GET["hostname"]=="master"){$ipstr="127.0.0.1";}
	$page=CurrentPageName();
	$main=new maincf_multi($_GET["hostname"],$ou,$ipstr);
	$myhostname=$main->GET("myhostname");
	if($myhostname==null){
		$main->SET_VALUE("myhostname",$_GET["hostname"]);
		$main=new maincf_multi($_GET["hostname"],$ou);
	}
	
	
	$main->SET_VALUE("dkimproxyEnabled",$_GET["dkimproxyEnabled"]);
	$main->SET_VALUE("dkimproxy_listenport",$_GET["listen_port"]);
	$main->SET_BIGDATA("dkimproxy_datas",base64_encode(serialize($_GET)));
	
}	

