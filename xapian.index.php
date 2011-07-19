<?php
	session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.xapian.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.crypt.php');

	if(isset($_GET["xapsearch"])){xapsearch();exit;}
	
page();

exit;
function page(){
	
	$html="
	
	<script>
	var x_InstantSearchSave= function (obj) {
		var res=obj.responseText;
		document.getElementById('xapresults').innerHTML=res;
	}		
	
function InstantSearchQuery(){
		var xapsearch=document.getElementById('xapsearch').value;
		var XHR = new XHRConnection();
		document.getElementById('xapresults').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.appendData('xapsearch',xapsearch);
		XHR.sendAndLoad('$page', 'GET',x_InstantSearchSave);
		 		
	}
	
	function InstantSearchQueryPress(e){
		if(checkEnter(e)){InstantSearchQuery();}
	}
	
		
	</script>
	
	
	<center><img src='img/bg_instantsearch.png'></center>
	<br>
	<center><span style='font-size:16px;'>{search}:</span>&nbsp;
	<input type='text' name='xapsearch' id='xapsearch' 
	style='font-size:16px;width:350px;border:1px solid #CCCCCC;padding:3px'
	OnKeyPress=\"javascript:InstantSearchQueryPress(event)\">
	</center>
	
	<div id='xapresults'></div>
	
	
	";
	
$tpl=new template_users('InstantSearch',$html);
echo $tpl->web_page;	
	
}

function generateTabs($max,$current){
	$page=CurrentPageName();
	$start=0;
	$nb_pages=round($max/10,0);
	
if($nb_pages>10){
	if(isset($_GET["next"])){$start=$_GET["next"];}
	if(isset($_GET["forward"])){$start=$_GET["forward"];}	
	
	
	$next_link=$_GET["next"]+10;
	$next="<li><a href=\"javascript:LoadAjax('xapresults','$page?p=$next_link&xapsearch={$_GET["xapsearch"]}&tmpmax=$max&next=$next_link&nbpages=$nb_pages')\" $class>&raquo;&raquo;</a></li>";

	$forward_link=$_GET["forward"]-10;
	if($forward_link<0){$forward_link=0;}
	$reverse="<li><a href=\"javascript:LoadAjax('xapresults','$page?p=$forward_link&xapsearch={$_GET["xapsearch"]}&tmpmax=$max&forward=$forward_link&nbpages=$nb_pages')\" $class>&laquo;&laquo;</a></li>";
	
}
$count=0;

for($i=$start;$i<($start+9);$i++){
	$p=$i+1;
	$arr[$i]="{page} ".$p;
}

if($next_link>$nb_pages){$next_link=null;}
if($next_link<20){$reverse=null;}
$nextp=$_GET["next"];
	$html=$reverse;
while (list ($num, $ligne) = each ($arr) ){
		if($current==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('xapresults','$page?p=$num&xapsearch={$_GET["xapsearch"]}&tmpmax=$max&next=$nextp')\" $class>$ligne</a></li>\n";
			
		}
		$html=$html . $next;
	return "<div id=tablist>$html</div>";		
	
}


function xapsearch(){
	
	$userid=$_SESSION["uid"];
	$ct=new user($userid);
	
	if($userid<>null){
		$samba=new samba();
		$samba_folders=$samba->GetUsrsRights($userid);
	}
	
	$users=new usersMenus();
	
	writelogs("$userid ->AllowXapianDownload \"$users->AllowXapianDownload\"",__FUNCTION__,__FILE__);;
	$xapian=new XapianSearch();
	if(!is_file("/usr/share/artica-postfix/LocalDatabases/home_$userid.db")){
		$xapian->add_database("/usr/share/artica-postfix/LocalDatabases/home_$userid.db");
	}
	while (list ($sharename, $path) = each ($samba_folders) ){
		if(!is_file("/usr/share/artica-postfix/LocalDatabases/share_$sharename.db")){
			$xapian->add_database("/usr/share/artica-postfix/LocalDatabases/share_$sharename.db");
		}
		
	}
	
	if(is_array($ct->HASH_ALL_MAILS)){
		while (list ($num, $mail) = each ($ct->HASH_ALL_MAILS) ){
		$xapian->add_database("/usr/share/artica-postfix/LocalDatabases/mail_$mail.db");
	}}
	
	
	$current=$_GET["p"];
	if($current==null){$current=0;}

	
	$xapian->terms=$_GET["xapsearch"];
	$xapian->start=$current;
	if(count($xapian->databases)>0){
		$array=$xapian->search();
	}

	$maxdocs=$array["ESTIMATED"];

	if($maxdocs>10){
		$tabs=generateTabs($maxdocs,$current);
	}	
	
	if(is_array($array["RESULTS"])){
		while (list ($num, $arr) = each ($array["RESULTS"]) ){
			$tr[]=FormatResponse($arr,$users,$ct->password);
		}
	}
	
	$html="<div style='font-size:12px;font-weight:bold;margin:5px;text-align:right'>{found} {$array["ESTIMATED"]} {documents} in <strong>" . count($xapian->ParsedDatabases)." {databases}</strong></div>";
	
	$tpl=new templates();
	if(is_array($tr)){echo $tpl->_ENGINE_parse_body($html.$tabs.implode("\n",$tr));}else{
		if($_GET["tmpmax"]>0){
			$tabs=generateTabs($maxdocs,$current);
		}
		
		echo $tpl->_ENGINE_parse_body($html.$tabs.'<p style="font-size:14px;font-weight:bold;margin:10px">{ERR_NO_DOC_FOUND}</p>'); 
	
		}
	
	
}

function FormatResponse($ligne,$users,$pass){
	$f=new filesClass();
	$crypt=new SimpleCrypt($pass);
	$uri="<a href=\"download.attach.php?xapian-file=".$crypt->encrypt($ligne["PATH"])."\">";
	$text_deco="text-decoration:underline";
	
	
	
	if(!$users->AllowXapianDownload){
		$text_deco=null;
	}
	$ligne["PATH"]=str_replace("'",'`',$ligne["PATH"]);
	$title=substr($ligne["DATA"],0,60);
	$title="$uri<span style='color:#0000CC;$text_deco;font-size:medium'>{$ligne["PERCENT"]}%&nbsp;$title</span></a>";
	$body=$ligne["DATA"];
	$body=wordwrap($body, 100, "<br />\n");
	
	$img="img/file_ico/unknown.gif";
	$file=basename($ligne["PATH"]);
	$ext=$f->Get_extension(strtolower($file));
	if(is_file("img/file_ico/$ext.gif")){
			$img="img/file_ico/$ext.gif";
		}
	
	
	$html="
	
	<table style='width:99%;margin-top:6px'>
	<tr>
		<td>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='$img'></td>
			<td valign='top' width=1%>" . imgtootltip("folderopen.gif","{path}:{$ligne["PATH"]}<br>{size}:{$ligne["SIZE"]}<br>{date}:{$ligne["TIME"]}")."</td>
			<td valing='top'>$title</td>
		</tr>
		</table>
	</tr>
	<tr>
	<td><span style='font-size:small;color:#676767;'>&laquo&nbsp;<strong>{$file}</strong>&nbsp;&raquo;&nbsp;-&nbsp;{$ligne["TIME"]}</span></td>
	</tr>
	<tr>
	<td style='font-size:11px;'>$body</td>
	</tr>
	<tr>
	<td style='font-size:small;color:green;' align='left'>{$ligne["TYPE"]} ({$ligne["SIZE"]})</td>
	</tr>	
	</table>
	";
	
	return $html;	
	
	
	
}



include_once("ressources/class.xapian.inc");
// Open the database for searching.
try {
    $database = new XapianDatabase("/home/dtouzeau/Documents/doc1.db");
    $database1=new XapianDatabase("/home/dtouzeau/Documents/doc1.db");
    
	$database->add_database($database1);
    // Start an enquire session.
    $enquire = new XapianEnquire($database);

    // Combine the rest of the command line arguments with spaces between
    // them, so that simple queries don't have to be quoted at the shell
    // level.
    $query_string = "david";

    $qp = new XapianQueryParser();
    $stemmer = new XapianStem("english");
    $qp->set_stemmer($stemmer);
    $qp->set_database($database);
    $qp->set_stemming_strategy(XapianQueryParser::STEM_SOME);
    $query = $qp->parse_query($query_string);
    print "Parsed query is: {$query->get_description()}\n";

    // Find the top 10 results for the query.
    $enquire->set_query($query);
    $matches = $enquire->get_mset(0, 10);

    // Display the results.
    print "{$matches->get_matches_estimated()} results found:\n";

    $i = $matches->begin();
    while (!$i->equals($matches->end())) {
	$n = $i->get_rank() + 1;
	$data = $i->get_document()->get_data();
	print "$n: {$i->get_percent()}% docid={$i->get_docid()} [$data]\n\n";
	$i->next();
    }
} catch (Exception $e) {
    print $e->getMessage() . "\n";
    exit(1);
}


?>