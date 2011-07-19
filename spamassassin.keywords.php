<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.spamassassin.inc');
	$user=new usersMenus();
		if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["keywords"])){keywords();exit;}
	if(isset($_GET["keywords-popup"])){keywords_add_popup();exit;}
	if(isset($_GET["keywords-list"])){keywords_list();exit;}
	if(isset($_POST["keywords-save"])){keywords_add();exit;}
	if(isset($_GET["keywords-edit"])){keywords_edit();exit;}
	if(isset($_GET["keywords-edit-save"])){keywords_edit_save();exit;}
	if(isset($_GET["SimpleKeywordDisable"])){SimpleKeywordDisable();exit;}
	if(isset($_GET["SimpleWordsDelete"])){SimpleWordsDelete();exit;}
	
js();

function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{APP_SPAMASSASSIN}::{block_keywords}");		
	echo "YahooWin3('700','$page?tabs=yes','$title');";
	
}


function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["keywords"]='{block_keywords}';
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_spamass_keywords style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_spamass_keywords\").tabs();});
		</script>";		
	
}

function keywords_list(){
	
	
	
	
	$page=CurrentPageName();
	$se="%{$_GET["keywords-list"]}%";
	$se=str_replace("*","%",$se);
	$se=str_replace("%%","%",$se);
	
	
	$sql="SELECT * FROM spamassassin_keywords WHERE 1 AND `pattern` LIKE '$se' ORDER BY pattern LIMIT 0,100";
	$tpl=new templates();
	$q=new mysql();
	$q->Check_quarantine_table();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{rule}</th>
		<th>{words}</th>
		<th nowrap>{header}</th>
		<th>{score}</th>
		<th>{enabled}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		
		$disable=Field_checkbox("enabled_{$ligne["ID"]}",1,$ligne["enabled"],"SimpleKeyWordDisable('{$ligne["ID"]}')");
		$delete=imgtootltip("delete-32.png","{delete}","SimpleKeyWordDelete('{$ligne["ID"]}')");
		$color="black";
		if($ligne["enabled"]==0){$color="#A8A5A5";}		
		
		$icon="datasource-32.png";
		$js="<a href=\"javascript:blur();\" OnClick=\"javascript:EditSpamAssKeyWord({$ligne["ID"]})\" style='text-decoration:underline'>";
		$html=$html . "
		<tr  class=$classtr>
		<td width=1%><img src='img/$icon'></td>
		<td width=1%><strong style='font-size:14px'>{$ligne["ID"]}</td>
		<td><strong style='font-size:14px'><code style='color:$color'>$js{$ligne["pattern"]}</a></code></td>
		<td width=1% align='center' style='font-size:14px'>$js{$ligne["header"]}</a></td>
		<td width=1% align='center' style='font-size:14px'>$js{$ligne["score"]}</a></td>
		<td width=1% align='center' style='font-size:14px'>$disable</td>
		<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
	$rule=$tpl->_ENGINE_parse_body("{rule}");
	$html=$html."</tbody></table>
	
	
	<script>
	var x_SimpleKeyWordDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		spamass_keywords_refresh();
	}	
	
	var x_SimpleKeyWordDisable= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		
	}		
	
	function SimpleKeyWordDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('SimpleWordsDelete',key);	
		document.getElementById('spamass_keywords').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SimpleKeyWordDelete);
		}	
		
	function SimpleKeyWordDisable(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ID',ID);
		if(document.getElementById('enabled_'+ID).checked){XHR.appendData('SimpleKeywordDisable',1);}else{XHR.appendData('SimpleKeywordDisable',0);}
		XHR.sendAndLoad('$page', 'GET',x_SimpleKeyWordDisable);
	}
	
	function EditSpamAssKeyWord(ID){
		YahooWin4('550','$page?keywords-edit='+ID,'$rule&raquo;'+ID);
	}
			
	
	</script>";
	
		
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function SimpleKeywordDisable(){
	$sql="UPDATE spamassassin_keywords SET enabled='{$_GET["SimpleKeywordDisable"]}' WHERE ID={$_GET["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?spamass-build=yes");	
	
}

function SimpleWordsDelete(){
	$sql="DELETE FROM spamassassin_keywords WHERE ID={$_GET["SimpleWordsDelete"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?spamass-build=yes");		
}


function keywords(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$title=$tpl->_ENGINE_parse_body("{add}&raquo;{add_keywords}");
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'><div class=explain>{block_keywords_explain}</div>
	<td valign='top'>". Paragraphe32("add_keywords","add_keywords_smtp_check","add_keywords_smtp()", "32-plus.png")."</td>
	</tr>	
	</table>
	<table>
		<tr>
		<td class=legend>{search}:</td>
		<td>". Field_text("spamass_keywords_search",null,"font-size:14px;padding:3px;width:450px","script:spamass_keywords_searchEnter(event)")."</td>
	</tr>
	</table>
	<div id='spamass_keywords'></div>
	
	<script>
		function add_keywords_smtp(){
			YahooWin4('550','$page?keywords-popup=yes','$title');
		
		}
		
		function spamass_keywords_searchEnter(e){
			if(checkEnter(e)){spamass_keywords_refresh();}
		}
		
		function spamass_keywords_refresh(){
			var lists=escape(document.getElementById('spamass_keywords_search').value);
			LoadAjax('spamass_keywords','$page?keywords-list='+lists);
		}
	
	spamass_keywords_refresh();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function  hash_headers(){
	
	$f["all"]="{all}";
	$f["subject"]="{subject}";
	return $f;
	
}

function keywords_edit(){
	if(!is_numeric($_GET["keywords-edit"])){return null;}
	$sql="SELECT * FROM spamassassin_keywords WHERE ID={$_GET["keywords-edit"]}";
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$header=hash_headers();
	$headers=Field_array_Hash($header,"keyword_header",$ligne["header"],"style:font-size:14px;padding:3px");
	
	
	$html="
	<div id='simplekeywords-smtp-div'>
	<code style='font-size:17px'>{$ligne["pattern"]}</code><hr>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{header}:</td>
		<td>$headers</td>
		<td class=legend>{score}:</td>
		<td>". Field_text("keyword_score",$ligne["score"],"font-size:14px;padding:3px;width:60px")."</td>
	</tr>	
	<tr>
	<td colspan=4 align=right><hr>". button("{apply}","SaveEditKeyWord()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	var x_SaveEditKeyWord= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		YahooWin4Hide();
		spamass_keywords_refresh();
	}			
		
	function SaveEditKeyWord(){
		var XHR = new XHRConnection();
		XHR.appendData('keywords-edit-save','{$_GET["keywords-edit"]}');
		XHR.appendData('keyword_header',document.getElementById('keyword_header').value);
		XHR.appendData('keyword_score',document.getElementById('keyword_score').value);
		document.getElementById('simplekeywords-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveEditKeyWord);		
		}
	
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function keywords_edit_save(){
	
	$sql="UPDATE spamassassin_keywords
	SET `header`='{$_GET["keyword_header"]}',
	`score`='{$_GET["keyword_score"]}'
	WHERE ID='{$_GET["keywords-edit-save"]}'
	";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?spamass-build=yes");	
	
}


function keywords_add_popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	
	
	$header=hash_headers();
	$headers=Field_array_Hash($header,"keyword_header",null,"font-size:14px;padding:3px");
	
	
	$html="
	<div id='simplekeywords-smtp-div'>
	<div class=explain>{add_multiple_keywords_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{header}:</td>
		<td>$headers</td>
		<td class=legend>{score}:</td>
		<td>". Field_text("keyword_score","7","font-size:14px;padding:3px;width:60px")."</td>
	</tr>
	
	</table>
	
	<textarea id='keywords-servers-container' style='width:100%;height:450px;overflow:auto;font-size:14px'></textarea>
	<div style='text-align:right'>". button("{add}","KeyWordsSave()")."</div>
	</div>
	<script>
	
	var x_KeyWordsSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue)};
		YahooWin4Hide();
		spamass_keywords_refresh();
	}			
		
	function KeyWordsSave(){
		var XHR = new XHRConnection();
		XHR.appendData('keywords-save',document.getElementById('keywords-servers-container').value);
		XHR.appendData('keyword_header',document.getElementById('keyword_header').value);
		XHR.appendData('keyword_score',document.getElementById('keyword_score').value);
		document.getElementById('simplekeywords-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_KeyWordsSave);		
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function keywords_add(){
	
	$datas=explode("\n",$_POST["keywords-save"]);
	$prefix="INSERT INTO spamassassin_keywords (pattern,header,score) VALUES ";
	
	if(!is_array($datas)){echo "No data";return;}
	while (list ($num, $words) = each ($datas) ){	
		if(trim($words)==null){continue;}
		$words=addslashes($words);
		$ws[]="('$words','{$_POST["keyword_header"]}','{$_POST["keyword_score"]}')";
	}
	
	$q=new mysql();
	$q->BuildTables();
	$sql=$prefix.@implode(",",$ws);
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n".$sql."\n";return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?spamass-build=yes");
}
