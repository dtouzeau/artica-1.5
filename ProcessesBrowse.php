<?php
	include_once('ressources/class.templates.inc');
	
	
	


	
	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["query"])){query();exit;}
	
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if($_GET["prepend"]==null){$_GET["prepend"]=0;}
	$title=$tpl->_ENGINE_parse_body("{browse}::{processes}");
	echo "LoadWinORG('600','$page?popup=yes&field-user={$_GET["field-user"]}&function={$_GET["function"]}','$title');";	
	
	
	
}



function popup(){
	$page=CurrentPageName();
	$tpl=new templates();		


	
	$html="
	<center>
	<table class=form>
		<tr>
		<td>" . Field_text('BrowseProcessQuery',null,'width:100%;font-size:14px;padding:3px',null,null,null,null,"BrowseFindProcessClick(event);")."</td>
		<td align='right'><input type='button' OnClick=\"javascript:BrowseFindProcess();\" value='{search}&nbsp;&raquo;'></td>
		</tR>
	</table>
	</center>
	<br>
	<div style='padding:5px;height:350px;overflow:auto' id='ProcessesBrowseLIST'></div>
	<script>
	function BrowseFindProcessClick(e){
		if(checkEnter(e)){BrowseFindProcess();}
	}
	
	function BrowseFindProcess(){
		LoadAjax('ProcessesBrowseLIST','$page?query='+escape(document.getElementById('BrowseProcessQuery').value)+'&prepend={$_GET["prepend"]}&field-user={$_GET["field-user"]}&function={$_GET["function"]}');
	
	}	
	
	BrowseFindProcess();
</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function query(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$_GET["query"]=trim($_GET["query"]);
	if($_GET["query"]=='*'){$_GET["query"]=null;}
	$sock=new sockets();
	$hash=unserialize(base64_decode($sock->getFrameWork("cgroup.php?ProcessExplore=yes")));
	if(strlen(trim($_GET["query"]))>0){
		$_GET["query"]=str_replace(".", "\.", $_GET["query"]);
		$_GET["query"]=str_replace("*", ".*?", $_GET["query"]);
		$_GET["query"]=str_replace("/", "\/", $_GET["query"]);
		$_GET["query"]=str_replace(" ", "\s+", $_GET["query"]);
		
	}
	if(!is_array($hash)){return null;}
	
	$html=$html."
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{cpu}</th>
	<th>{memory}</th>
	<th colspan=2>{processes}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	
while (list ($num, $ligne) = each ($hash) ){
		$ligne=trim($ligne);
		if($ligne==null){continue;}
		if(!preg_match("#^(.+?)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9\.]+)\s+(.+?)\s+(.+?)$#", $ligne,$re)){echo "<li>$ligne</li>\n";continue;}
		
		
		$user=$re[1];
		$cmd=trim($re[6]);
		$cmdOriginal=$cmd;
		$cgroup=$re[5];
		//$size=FormatBytes($re[1])." - ";
		if(preg_match("#:\/(.+?)\?#", $cgroup,$ria)){$cgroumname=$ria[1];}
		
		
		if(preg_match("#^\[.+?\]#", $cmd)){continue;}
		if(preg_match("#^sh -c.+?#", $cmd)){continue;}
		if(preg_match("#^\/bin\/sh#", $cmd)){continue;}
		if(preg_match("#^sleep#", $cmd)){continue;}
		if(preg_match("#\/ps\s+#", $cmd)){continue;}
		if(preg_match("#^init#", $cmd)){continue;}
		if(preg_match("#\/getty#", $cmd)){continue;}			
		if(preg_match("#cgrulesengd#", $cmd)){continue;}
		if(preg_match("#\/daemon#", $cmd)){continue;}
		if(preg_match("#\/bin\/bash", $cmd)){continue;}
		if(preg_match("#^sshd:.*?#", $cmd)){continue;}
		if($_GET["query"]<>null){
			if(!preg_match("#{$_GET["query"]}#", $cmd)){continue;}
		}
		
		
		
		$psespace=strpos($cmd, " ");
		if($psespace>1){
			$pname=substr($cmd, 0,$psespace);
		}else{
			$pname=$cmd;
		}
		
		if(isset($already[$pname])){continue;}
		$already[$pname]=true;

		
		$js="SambaBrowseSelect('$num','$prepend')";
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
		<tr class=$classtr>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>{$re[3]}%</td>
		<td width=1% align='center' valign='middle' style='font-size:14px;'>{$re[4]}%</td>
		<td 
		onMouseOver=\"this.style.cursor='pointer'\" 
		OnMouseOut=\"this.style.cursor='default'\"
		
		><strong style='font-size:14px;text-decoration:underline' >
		". texttooltip($pname,"$cmd<br>{$cgroup}","ProcessBrowseSelect('$user','$cmdOriginal')",null,null,"font-size:14px;font-weight:bold",1)."</strong>
		<div style='text-align:right;text-decoration:none'><i style='font-size:11px;font-weight:normal;text-decoration:none'>{member}:$user&nbsp;|&nbsp;{APP_CGROUPS}: $cgroumname</i></div>
		
		</td>
		<td width=1% align='center' valign='middle'>". imgtootltip("plus-24.png","{select}","ProcessBrowseSelect('$user','$cmdOriginal')")."</td>
		</tr>
	";
	}
	
	$html=$html."</table>
	
	<script>
	function ProcessBrowseSelect(process_user,process_path){
		{$_GET["function"]}(process_user,process_path);
	}
		
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
	
}

