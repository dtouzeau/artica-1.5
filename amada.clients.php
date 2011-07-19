<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.mysql.inc');

$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["browse-amanda-list"])){clients_list();exit();}
	if(isset($_GET["id-js"])){client_js();exit();}
	if(isset($_GET["id-popup"])){client_popup();exit;}
	if(isset($_POST["ID"])){client_save();exit;}
	
page();	

function client_js(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$title="{computer}:{add}";
	if($_GET["id-js"]>0){
		$q=new mysql();
		$sql="SELECT hostname FROM amanda_hosts WHERE ID='{$_GET["id-js"]}'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$title="{$ligne["hostname"]}";
	}
	$title=$tpl->_ENGINE_parse_body($title);
	$html="YahooWin3('550','$page?id-popup={$_GET["id-js"]}','$title')";
	echo $html;
	
}

function client_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$id=$_GET["id-popup"];
	$button="{add}";
	if(!is_numeric($id)){$id=0;}
	$q=new mysql();
	$sql="SELECT dumpname,comment FROM amanda_dumptype ORDER BY dumpname";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$dumps[$ligne["dumpname"]]=$ligne["comment"];
		}
		
	if($id>0){
		$q=new mysql();
		$sql="SELECT * FROM amanda_hosts WHERE ID='$id'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$button="{apply}";
		
	}			
		
		$dumps["always-full"]="Full dump of this filesystem always";
		$dumps["root-tar"]="root partitions dumped with tar";
		$dumps["user-tar"]="user partitions dumped with tar";
		$dumps["user-tar-span"]="tape-spanning user partitions dumped with tar";
		$dumps["high-tar"]="partitions dumped with tar";
		$dumps["comp-root-tar"]="Root partitions with compression";
		$dumps["comp-user"]="Non-root partitions on reasonably fast machines";
		$dumps["comp-user-span"]="Tape-spanning non-root partitions on reasonably fast machines";
		$dumps["nocomp-user"]="Non-root partitions on slow machines";
		$dumps["comp-root"]="Root partitions with compression";
		$dumps["nocomp-root"]="Root partitions without compression";
		$dumps["comp-high"]="very important partitions on fast machines";
		$dumps["nocomp-high"]="very important partitions on slow machines";
		
		
		
		while (list ($num, $ligne_s) = each ($dumps) ){
			if($ligne["dumptype"]==$num){$val=1;}else{$val=0;}
			$dumptd[]="
			<tr>
			<td class=legend style='font-size:11px'>$ligne_s</td>
			<td>". Field_checkbox("f-$num",1,$val,"EnableDumpStrat('$num')")."</td>
			</tr>
			";
			$dumptdjsoff[]="if(document.getElementById('f-$num')){document.getElementById('f-$num').checked=false;}";
			
			
		}
		
	

	if($ligne["dumptype"]==null){$ligne["dumptype"]="high-tar";}
	$jsdef="EnableDumpStrat('{$ligne["dumptype"]}');";
	
	
	$html="
	<span id='formAmandaHostCheckID'></span>
	<inpuyt type='hidden' id='x_dumptype' value='{$ligne["dumptype"]}'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{hostname}:</td>
		<td>". Field_text("x_hostname",$ligne["hostname"],"font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{local_directory}:</td>
		<td>". Field_text("x_directory",$ligne["directory"],"font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend valign='top'>{Amandadumptypes}:</td>
		<td><table>".@implode("\n", $dumptd)."</table></td>
	</tr>
	<tr>
		<td colspan=2 align='right'>". button($button,"SaveCLientAmandaCOnf()")."</td>
	</tr>
	</table>
	
	<script>
		function EnableDumpStrat(dump){
		".@implode("\n", $dumptdjsoff)."
		if(document.getElementById('f-'+dump)){document.getElementById('f-'+dump).checked=true;}
		document.getElementById('x_dumptype').value=dump;
		}
	
	var x_SaveCLientAmandaCOnf=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>0){alert(tempvalue);document.getElementById('formAmandaHostCheckID').innerHTML='';return;}
      YahooWin3Hide();
      if(document.getElementById('browse-amanda-list')){BrowseAmandaSearch();}
      }	

	function SaveCLientAmandaCOnf(){
		var XHR = new XHRConnection();
		XHR.appendData('dumptype',document.getElementById('x_dumptype').value);
		XHR.appendData('directory',document.getElementById('x_directory').value);
		XHR.appendData('hostname',document.getElementById('x_hostname').value);
		XHR.appendData('ID','$id');
		AnimateDiv('formAmandaHostCheckID');
		XHR.sendAndLoad('$page', 'POST',x_SaveCLientAmandaCOnf);		
	}	
	
	
	$jsdef
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function client_save(){
		$id=$_POST["ID"];
		unset($_POST["ID"]);
		while (list ($num, $ligne) = each ($_POST) ){
		$field[]="`$num`";
		$ligne=addslashes($ligne);
		$vals[]="'$ligne'";
		$upd[]="`$num`='$ligne'";
	}
	
	$sql_add="INSERT INTO amanda_hosts (".@implode(",", $field).") VALUES (".@implode(",", $vals).")";
	$sql_update="UDPATE amanda_hosts SET " .@implode(",", $upd) ." WHERE ID='{$id}'";;
	$sql=$sql_add;
	if($id>0){$sql=$sql_update;}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
}

	
function page(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	

	$add=Paragraphe("64-net-server-add.png", "{add_computer}", "{add_amanda_computer}","javascript:AmandaComputer(0)");
	
	$html="
	<table style='width:750px'>
	<tr>
	<td width=550px valign='top'>
		<div class=explain>{amanda_computers_explain}</div>
		<center>
			<table style='width:100%' class=form>
			<tr>
				<td class=legend>{clients}:</td>
				<td>". Field_text("browse-amandac-search",null,"font-size:14px;padding:3px",null,null,null,false,"BrowseAmandaSearchCheck(event)")."</td>
				<td>". button("{search}","BrowseSambaSearch()")."</td>
			</tr>
			</table>
		</center>		
		<div id='browse-amanda-list' style='width:100%;height:450px;overflow:auto;text-align:center'></div>
		
	</td>
	</tr>
	</table>
	
	
	
<script>
		function BrowseAmandaSearchCheck(e){
			if(checkEnter(e)){BrowseAmandaSearch();}
		}
		
		function BrowseAmandaSearch(){
			var se=escape(document.getElementById('browse-amandac-search').value);
			LoadAjax('browse-amanda-list','$page?browse-amanda-list=yes&search='+se+'&field={$_GET["field"]}');
		}
		
		function AmandaComputer(ID){
			Loadjs('$page?id-js='+ID);
		}		
		
			
	BrowseAmandaSearch();
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function clients_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ldap=new clladp();
	$sock=new sockets();
	$users=new usersMenus();
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("***","*",$search);
	$search=str_replace("**","*",$search);
	$search_sql=str_replace("*","%",$search);
	$search_sql=str_replace("%%","%",$search_sql);
	$search_regex=str_replace(".","\.",$search);	
	$search_regex=str_replace("*",".*?",$search);

	
	$add=imgtootltip("plus-24.png","{add}","AmandaComputer(0)");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=5>{computers}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";

		$q=new mysql();
		$sql="SELECT * FROM amanda_hosts WHERE hostname LIKE '$search_sql' ORDER BY hostname";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
		$results=$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	

		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$select=imgtootltip("32-parameters.png","{edit}","AmandaComputer('{$ligne["ID"]}')");
		$select2=imgtootltip("32-network-server.png","{edit}","AmandaComputer('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","AmandaComputerDel('{$ligne["ID"]}')");
		$color="black";

		$html=$html."
		<tr class=$classtr>
			<td width=1%>$select2</td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["hostname"]}<div><i style='font-size:10px'>{$ligne["resolved"]}</i></div></a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["directory"]}</a></td>
			<td style='font-size:14px;font-weight:bold;color:$color'>{$ligne["dumptype"]}</td>
			<td width=1%>$select</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table></center>
	<script>
	
		var x_SambaVirtalDel=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			BrowseAmandaSearch();
		}
	
	
		function SambaVirtalDel(hostname){
			if(confirm('$sure_delete_smb_vrt ['+hostname+']')){
				var XHR = new XHRConnection();
				XHR.appendData('delete-hostname',hostname);
				AnimateDiv('browse-samba-list');
    			XHR.sendAndLoad('$page', 'POST',x_SambaVirtalDel);
			}
		}
	
		


	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}
