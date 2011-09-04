<?php
	ini_set("memory_limit","400M");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.httpd.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["tri"])){echo events_table();exit;}
if(isset($_GET["events-table"])){events_table();exit;}
if(isset($_GET["ShowID"])){ShowID();exit;}
if(isset($_GET["delete_all_items"])){delete_all_items();exit;}


js();	


		
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{artica_events}');
	$start="artica_events_start()";
	
	if(isset($_GET["filterby"])){
		$filterby="&tri=yes&LockBycontext={$_GET["filterby"]}";
	}
	
	if(isset($_GET["in-div"])){
		$start="LoadAjax('{$_GET["in-div"]}','$page?popup=yes$filterby')";
	}
	
	if(isset($_GET["in-front-ajax"])){
		$start="artica_events_start2()";
	}
	if(isset($_GET["external-events"])){
		$start="articaShowEvent({$_GET["external-events"]})";
		
	}
	
	
	$html="
	
	function artica_events_start(){
	 	YahooWin5('750','$page?popup=yes&without-tri={$_GET["without-tri"]}','$title');
	}
	
	function artica_events_start2(){
		$('#BodyContent').load('$page?popup=yes$filterby');
	}
	 
	 function tripar(){
	 	var context=document.getElementById('context').value;
	 	var process=document.getElementById('process').value;
	 	var se=document.getElementById('event-search').value;
	 	se=escape(se);
	 	LoadAjax('articaevents','$page?tri=yes&context='+context+'&process='+process+'&pattern='+se);
	 
	}
	
	function EventSearchCheck(e){
		if(checkEnter(e)){tripar();}
	}
	
	function articaShowEvent(ID){
		 YahooWin6('750','$page?ShowID='+ID,'$title::'+ID);
	}
	 
	
	
	$start;";
	
	echo $html;	
	
}

function popup(){
	$sock=new sockets();
	$datas=$sock->APC_GET(md5(__FILE__.__FUNCTION__),10);
	if($datas<>null){echo $datas;return;}
	$page=CurrentPageName();
	$tpl=new templates();
	$delete_all_items=$tpl->javascript_parse_text("{delete_all_items}");
	
	$html="
	<div style='text-align:right;width:750px'>
	<table style='width:100%'>
	<tr>
	<td width=99%><span style='font-size:16px;'>{artica_events}</span>&nbsp;</td>
	<td width=1% class=legend>{search}:</td>
	<td width=1%>". Field_text("event-search",null,"font-size:14px;padding:3px;width:210px",null,null,null,false,"EventSearchCheck(event)")."</td>
	<td width=1%>". button("{submit}","tripar()")."</td>
	<td width=1% nowrap>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td width=1%>". imgtootltip("delete-24.png","{delete_all_items}","DeleteAllArticaEvents()")."
	</td>
	</tr>
	</table>
	</div>
		
	<div style='width:100%;height:500px;width:750px;overflow:auto' id='articaevents'></div>
	
	
	<script>
		LoadAjax('articaevents','$page?events-table=yes&LockBycontext={$_GET["LockBycontext"]}');
		
		var x_DeleteAllArticaEvents= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('articaevents','$page?events-table=yes&LockBycontext={$_GET["LockBycontext"]}');
				
		}			
		
		function DeleteAllArticaEvents(){
			if(confirm('$delete_all_items\\n')){
			var XHR = new XHRConnection();
			XHR.appendData('delete_all_items','yes');
			XHR.sendAndLoad('$page', 'GET',x_DeleteAllArticaEvents);	
		}

	}	
		
		
		
	</script>
		";
	
	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html);
	$sock->APC_SAVE(md5(__FILE__.__FUNCTION__),$html);
	echo $datas;	
	
}

function delete_all_items(){
	$sql="TRUNCATE TABLE events";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");	
	if(!$q->ok){echo $q->mysql_error;}
	
}

function events_table(){
	if($_GET["LockBycontext"]<>null){
		$_GET["context"]=$_GET["LockBycontext"];
		$_GET["without-tri"]="yes";
		$field_context=Field_hidden("context",$_GET["context"]);
		$field_process=Field_hidden("process",'');
	}
	$pattern=$_GET["pattern"];
	if($pattern<>null){
		$pattern="*$pattern*";
		$pattern=str_replace("*","%",$pattern);
		$pattern=str_replace("%%","%",$pattern);
		$sqlpat=" AND ((`text` LIKE '$pattern') OR (`content` LIKE '$pattern'))";
		
	}
	
	
	
	$q=new mysql();
	if($_GET["without-tri"]==null){
			$sql="SELECT process FROM events WHERE 1 $sqlpat GROUP BY process ORDER BY process";
			$results=$q->QUERY_SQL($sql,"artica_events");	
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["process"]==null){$ligne["process"]="{unknown}";}
					$text_interne=$ligne["process"];
				$text_externe=$ligne["process"];
				if($text_externe=="class.templates.inc"){$text_externe="system";}
				if($text_externe=="process1"){$text_externe="watchdog";}
			
				$arr[$text_interne]=$text_externe;
			}
			$arr[null]="{select}";
			
			$sql="SELECT context FROM events WHERE 1 $sqlpat GROUP BY context ORDER BY context";
			$results=$q->QUERY_SQL($sql,"artica_events");	
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["context"]==null){$ligne["context"]="{unknown}";}
			
				$text_interne=$ligne["context"];
				$text_externe=$ligne["context"];
			
				$arr1[$text_interne]=$text_externe;
			}
			
					
			$arr1[null]="{select}";
			
			$field_process="<input type=hidden value='' id='process' name='process'>";
			$field_context=Field_array_Hash($arr1,'context',$_GET["context"],"tripar()");
			
			

			
		
			
	}
	
$html="
			<table class=tableView style='width:100%'>
				<thead class=thead>
				<tr>
					<th width=1% nowrap colspan=2>{context}:</td>
					<th width:99%'>$field_context$field_process</td>			
				</tr>
				</thead>
				";	
	
	if($_GET["process"]<>null){$pp1=" AND process='{$_GET["process"]}'";}
	if($_GET["context"]<>null){$pp2=" AND context='{$_GET["context"]}'";}
	
	$sql="SELECT * FROM events WHERE 1 $pp2$pp1$sqlpat ORDER by zDate DESC LIMIT 0,50";
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	if(!$q->ok){
		echo "<H2>$q->mysql_error</H2>";
		return;
	}
	
	$tt=date('Y-m-d');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		
		if($ligne["process"]==null){$ligne["process"]="{unknown}";}
		$original_date=$ligne["zDate"];
		$ligne["zDate"]=str_replace($tt,'{today}',$ligne["zDate"]);
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+\((.+?)\)\s+:(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+\((.+?)\)\:\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+\((.+?)\)\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\((.+?)\)\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[2];
			$computer=$re[1];
		}
		
		
		if(preg_match("#\[.+?\]:\s+\[.+?\]:\s+(.+)#",$ligne["text"],$re)){
			$ligne["text"]=$re[1];
			
		}
		
		$affiche_text=$ligne["text"];
		if(strlen($affiche_text)>90){$affiche_text=substr($affiche_text,0,85).'...';}
		
		$tooltip="<li><strong>{date}:&nbsp;$original_date</li><li><strong>{computer}:&nbsp;$computer</strong></li><li><strong>{process}:&nbsp;{$ligne["process"]}</li>";
		$tooltip=$tooltip."<li><strong>{context}:&nbsp;{$ligne["context"]}</strong></li><hr>{click_to_display}<hr>";
		$tooltip=$tooltip."<div style=font-size:9px;padding:3px>{$ligne["text"]}</div>";
		
	if(preg_match("#<body>(.+?)</body>#is",$ligne["content"],$re)){
		$content=strip_tags($re[1]);
	}else{
		$content=strip_tags($ligne["content"]);
	}
		

		if(strlen($content)>300){
		
			$content=substr($content,0,290)."...";
		
		}
	
		$ID=$ligne["ID"];
		$js="articaShowEvent($ID);";
		
		$color="5C81A7";
		if(preg_match("#(error|fatal|unable)#i",$affiche_text)){$color="B50113";}
		
		$affiche_text=texttooltip($affiche_text,$tooltip,$js,null,0,"font-size:13px;font-weight:bolder;color:#$color");
		if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$time=strtotime($original_date);
		$distanceOfTimeInWords=distanceOfTimeInWords($time,time());
		
		$html=$html . "<tr class=$cl>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td valign='middle' nowrap style='font-size:13px' width=1% nowrap>{$ligne["zDate"]}</td>
		<td valign='top' width=99%><div style='font-size:13px'>$affiche_text</div><div style='font-size:11px'><i>$distanceOfTimeInWords</i><br><i>$content</i></div></td>
		</tR>
		
		";
		
	}
	$html=$html . "</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function ShowID(){
	$id=$_GET["ShowID"];
	if(!is_numeric($id)){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{error}</H2>");
		return;
		
	}
	$sql="SELECT * FROM events WHERE ID=$id";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	
	$subject=$ligne["text"];
	
	
	if(preg_match("#<body>(.+?)</body>#is",$ligne["content"],$re)){
		$content=$re[1];
	}
	
	;
	if($content==null){
		
		if(strpos($ligne["content"],"<td")>0){$html=true;}
		$tbl=explode("\n",$ligne["content"]);
			if(is_array($tbl)){
				while (list ($index, $line) = each ($tbl) ){
				if($html){
					$content=$content .$line;
				}else{
					$content=$content."<div><code>". htmlentities(stripslashes($line))."</code></div>";
				}
			
				}
			}
		}
	
	$html="<H3>$subject</H3>
	<hr>
	<div style='width:92%;height:450px;overflow:auto;margin:5px;padding:5px'>
	$content
	</div>
	
	
	";
	
	echo $html;
	
	
}


//ChangeSuperSuser	
	
?>	

