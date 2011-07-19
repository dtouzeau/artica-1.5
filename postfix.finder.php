<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(isset($_POST["hostname"])){$_GET["hostname"]=$_POST["hostname"];}
	
	$users=new usersMenus();
	
	
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["search-logs"])){queries_add();exit;}
	if(isset($_GET["details"])){details();exit;}
	if(isset($_GET["PostFinderDelete"])){PostFinderDelete();exit;}
	if(isset($_GET["PostFinderRebuild"])){PostFinderRebuild();exit;}	
	if(isset($_GET["js"])){js();exit;}	
		
	
	
	popup();
	
function js(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page');";
}
	
	
function popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$delete_this_query=$tpl->javascript_parse_text("{delete_this_query}");	
	$html="
		<div class=explain>{postfix_finder_explain}</div>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th align='center'>{pattern}</th>
			<th align='center'>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>	
		<tr>
			<td>" . Field_text('postfinder-query-logs',"",'width:530px;font-size:14px',
	null,null,null,false,"PostfixFinderSearchLogsCheck(event)")."</td>
			<td>". button("{search}","PostFinderSearchLogs()")."</td>
		</tr>
	</tbody>
	</table>
	<div style='text-align:right;width:100%;margin-bottom:10px'>". imgtootltip("22-recycle.png","{refresh}","PostFinderRefresh()")."</div>
	
	<div id='postfinder_search_logs'></div>
		
	<script>
		function PostfixFinderSearchLogsCheck(e){
			if(checkEnter(e)){PostFinderSearchLogs();return;}
		
		}
		
		function PostFinderRefresh(){
			LoadAjax('postfinder_search_logs','$page?search-logs=yes&pattern=');
		}			
	
	
		function PostFinderSearchLogs(){
			var pattern=escape(document.getElementById('postfinder-query-logs').value);
			LoadAjax('postfinder_search_logs','$page?search-logs=yes&pattern='+pattern);
		}	
		
		var x_PostFinderDelete=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}	
			PostFinderRefresh();		
		}			

		function PostFinderDelete(md5){
			if(confirm('$delete_this_query')){
				var XHR = new XHRConnection();
				XHR.appendData('PostFinderDelete',md5);
    			XHR.sendAndLoad('$page', 'GET',x_PostFinderDelete);
			}
		}

		function PostFinderRebuild(md5){
			var XHR = new XHRConnection();
			XHR.appendData('PostFinderRebuild',md5);
    		XHR.sendAndLoad('$page', 'GET',x_PostFinderDelete);	
		}
		
		
		
		PostFinderRefresh();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
}	

function PostFinderDelete(){
	$sql="DELETE FROM postfinder WHERE md5='{$_GET["PostFinderDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;}
}

function PostFinderRebuild(){
	$date=date('Y-m-d H:i:s');
	$sql="UPDATE postfinder 
	SET finish=0,
	date_start='$date',
	date_end='0000-00-00 00:00:00'
	WHERE md5='{$_GET["PostFinderRebuild"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;}	
	
}


function queries_add(){
	$query=trim($_GET["pattern"]);
	$tpl=new templates();
	$md=md5($query);
	$sock=new sockets();
	
	if($query<>null){
	if(!preg_match("#.+?@.+?#",$query)){
		echo $tpl->_ENGINE_parse_body("<H2>{query_must_be_like}: xxx@xxx (* {allowed})</H2>");
	}else{
		$sql="SELECT `md5` FROM postfinder WHERE md5='$md'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["md5"]==null){
			$sql="INSERT INTO postfinder (`md5`,`date_start`,`pattern`) VALUES
			('$md',NOW(),'".addslashes($query)."');
			";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){echo "<H2>".__LINE__."<hr><code>$q->mysql_error</code></H2>";}else{
				$sock->getFrameWork("cmd.php?postfinder=yes");
			}
			
		}
	}}else{
		$sock->getFrameWork("cmd.php?postfinder=yes");
	}
	
	queries_list();
	
}


function queries_list(){
	$page=CurrentPageName();
	$tpl=new templates();	

	
	$sql="SELECT * FROM postfinder ORDER BY `date_end` DESC";
	$q=new mysql();
	
	$results=$q->QUERY_SQL($sql,"artica_events");

	if(!$q->ok){
			echo "<H2>$q->mysql_error</H2>";
			return null;
		}
		
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{date}</th>
		<th>{pattern}</th>
		<th>{status}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	$count=0;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$distance=null;
		$md5=$ligne["md5"];
		$pattern=$ligne["pattern"];
		$events=$ligne["msg_num"]." {mailss}";
		$href="<a href=\"javascript:blur();\" Onclick=\"javascript:PostFinderShow('$md5','$pattern');\" style='font-size:14px;text-decoration:underline'>";
		$tt=strtotime($ligne["date_start"]);
		$date=date('l F d H:i',strtotime($ligne["date_start"]));
		if($ligne["finish"]==0){$events="{scheduled}";$href=null;}
		$delete=imgtootltip("delete-32.png","{delete}:{query}","PostFinderDelete('$md5')");
		
		$rebuild=imgtootltip("service-restart-32.png","{rebuild}:{query}","PostFinderRebuild('$md5')");
		
		if($ligne["finish"]==-1){$events="{running}";$href=null;}
		
		if($href<>null){
			$tt2=strtotime($ligne["date_end"]);
			$distance="<div style='font-size:10px;width:100%;text-align:right'><i style='font-size:10px'>".distanceOfTimeInWords($tt,$tt2)."</i></div>";
			
		}
		
		$html=$html . "
		<tr  class=$classtr>
			<td width=1% nowrap><strong style='font-size:14px'>$date</strong></td>
			<td width=99% align='left' nowrap><strong style='font-size:14px'>$href<code>$pattern</code></a></strong>$distance</td>
			<td width=1% nowrap align='left'><strong style='font-size:14px'>$href$events</a></strong></td>
			<td width=1%>$rebuild</td>
			<td width=1%>$delete</td>
		</td>
		</tr>";		
	}
	
	$html=$html."</table>
	
	<script>
		function PostFinderShow(md5,pattern){
			YahooWin5('700','$page?details=yes&md5='+md5,pattern);
		
		}
		

		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function details(){
		$tpl=new templates();
	
		$sql="SELECT `search_datas` FROM postfinder WHERE md5='{$_GET["md5"]}'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));	
		$array=unserialize($ligne["search_datas"]);
		$c=0;
		$html[]="
		<div style='width:100%;height:650px;overflow:auto'>
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>";
		
		while(list($time, $array_messages) = each($array)) {
			$j=strtolower(date('l',$time));
			$m=strtolower(date('F',$time));
			
			$date="{{$j}} {{$m}} ".date('d H:i:s',$time)." ".count($array_messages)." {mailss}";
			
				$c++;
				$msgid="$msgid-$c";
				while(list($msgid, $msgid_array) = each($array_messages)) {
				if($classtr2=="oddRow"){$classtr2=null;}else{$classtr2="oddRow";}
					$jsclose[]="document.getElementById('$msgid').style.visibility='hidden';";
					$jsclose[]="document.getElementById('$msgid').style.width='0px';";
					$jsclose[]="document.getElementById('$msgid').style.height='0px';";
					
					
					
					
					$html[]="<tr class=$classtr2>
					<td>
						<div style='font-size:16px;margin:5px'><a href=\"javascript:blur();\" OnClick=\"PostFinderOpen('$msgid');\" style='text-decoration:underline'>$date</div>
						<div id='$msgid' style='visibility:hidden;width:0px;height:0px;overflow:auto'>
							<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%;border:1px solid black'>
					";
					while(list($num, $messages) = each($msgid_array)) {
						if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
						$html[]="<tr class=$classtr><td><div style='font-size:13px'>". htmlspecialchars($messages)."</div></td>";
					}
					
					$html[]="</table>";
					$html[]="</div>";
					$html[]="</td>";
					$html[]="</tr>";
					
				}
				
			}
			$html[]="</table>
			</div>";
			$html[]="<script>
				function PostFinderHideAll(){
				".@implode("\n",$jsclose)."				
				}
				
				function PostFinderOpen(id){
					PostFinderHideAll();
					document.getElementById(id).style.visibility='visible';
					document.getElementById(id).style.width='100%';
					document.getElementById(id).style.height='450px';		
					
				}
				
				</script>";
				
			
			
			
			
		echo $tpl->_ENGINE_parse_body(@implode("",$html));
			
		
		
		
	
}

