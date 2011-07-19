<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.crypt.php');
	
	
$users=new usersMenus();
if(!$users->AsAnAdministratorGeneric){die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["sources-path-list"])){sources_path_list();exit;}
if(isset($_GET["mounted-path-list"])){mounted_path_list();exit;}

if(isset($_GET["browse-source-path"])){js_browse();exit;}
if(isset($_POST["dir"])){json_root();exit;}
if(isset($_GET["dar-list-files"])){list_files();exit;}
if(isset($_GET["darLinesCount"])){count_of_tables();exit;}
if(isset($_GET["clean-dar-table"])){table_clean();exit;}
if(isset($_GET["detailsof"])){detailsof();exit;}


js();


function js_browse(){
	
	$crypt=new SimpleCrypt();
	$root=$_GET["browse-source-path"];
	$mounted_path=$_GET["mounted-path"];
	$root_name=basename($root);
	$page=CurrentPageName();
$html="
	var mem_branch_id='';
	var mem_item='';

	
	
function initTree(){
		$(document).ready( function() {
			$('#browserarea').fileTree({ 
					root: '$root', 
					script: '$page?browse-tree=$root&mounted-path=$mounted_path', 
					folderEvent: 'click', 
					expandSpeed: 750, 
					collapseSpeed: 750, 
					expandEasing: 'easeOutBounce', 
					collapseEasing: 'easeOutBounce' ,
					multiFolder: false}, function(file) { 
					DarFolderClick(file);
				});
				
				
			});
}

function DarFolderClick(branch){
     
     if(document.getElementById('folder_area')){
        LoadAjax('folder_area','$page?dar-list-files='+branch+'&browse-tree=$root&mounted-path=$mounted_path');
     }
        
     return true;   
}


var x_TreeDar= function (obj) {
	initTree();       
}


	var XHR = new XHRConnection();
	XHR.appendData('browse-folder','$root');
	XHR.appendData('mounted-path','$mounted_path');
	XHR.sendAndLoad('$page', 'GET',x_TreeDar); 
";	
	
	echo $html;
	
}


function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{browse_external_storage}...','dar.index.php');
	$areyousure=$tpl->_ENGINE_parse_body('{empty_table_confirm}');
	$page=CurrentPageName();	
	
	unset($_SESSION["DAR_FILE_PAGE"]);
	unset($_SESSION["DAR_FILE_PATH"]);
	unset($_SESSION["DAR_SUB_PATH"]);
	
	$html="
	var mem_branch_id='';
	var mem_item='';
	var tree;	
	
	function dar_run(){
		YahooWin3(750,'$page?popup=yes','$title');
		setTimeout('sources_path_list()',950);
		setTimeout('darLinesCount()',950);
		
	}
	
	function sources_path_list(){
		LoadAjax('mounted_path','$page?mounted-path-list=yes');
		}
	
	
	function browse_mounted_path(){
		var mounted_path=document.getElementById('mounted-path-list').value;
		if(mounted_path){
			LoadAjax('source_list','$page?sources-path-list=yes&mounted-path='+mounted_path);
		}
	}
	
	
	function browse_source_path(){
		var source_path=document.getElementById('source_path').value;
		var mounted_path=document.getElementById('mounted-path-list').value;
		document.getElementById('folder_area').innerHTML='';
		document.getElementById('browserarea').innerHTML='';	
		Loadjs('$page?browse-source-path='+source_path+'&mounted-path='+mounted_path);
		
	
	}
	
function darLinesCount(){
	LoadAjax('darLinesCount','$page?darLinesCount=yes');
	
}

var x_DarBrowseEmptyMysqlDatabase=function (obj) {
	var tempvalue=obj.responseText;
	if (tempvalue.length>0){alert(tempvalue);} 
	YahooWin3Hide();
	dar_run();
}

function DarBrowseEmptyMysqlDatabase(){
	if(confirm('$areyousure')){
		var XHR = new XHRConnection();
		XHR.appendData('clean-dar-table','yes');
		document.getElementById('dialog3').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_DarBrowseEmptyMysqlDatabase); 	
	}
}

function DarLoadPathDetails(id){
	YahooWin4('550','dar.browse.php?detailsof='+id);
}
	
	
	dar_run();
	";
	
	echo $html;
	
	
}

function mounted_path_list(){
$cryt=new SimpleCrypt();	
$sql="SELECT mount_md5 FROM dar_index GROUP BY mount_md5";
	$q=new mysql();
	$resultats=$q->QUERY_SQL($sql,'artica_backup');
		while($ligne=@mysql_fetch_array($resultats,MYSQL_ASSOC)){
			$pathx[$ligne["mount_md5"]]=$ligne["mount_md5"];
			
		}
		if(is_array($pathx)){
		while (list ($num, $line) = each ($pathx)){
			$crypted=base64_encode($cryt->encrypt(trim($line)));
			$hidden_path=hide_ressources($line);
			$path[$crypted]=$hidden_path;
		}}
	
	$path[null]="{select}";
	
	$tpl=new templates();
	$html=Field_array_Hash($path,'mounted-path-list',null,"browse_mounted_path()");
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}



function sources_path_list(){
	$cryt=new SimpleCrypt();
	$_GET["mounted-path"]=base64_decode(trim($_GET["mounted-path"]));
	$mounted_path=$cryt->decrypt($_GET["mounted-path"]);
	$sql="SELECT source_path,mount_md5 FROM dar_index GROUP BY source_path,mount_md5 HAVING mount_md5='$mounted_path'";
	$q=new mysql();
	$resultats=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){
		echo $q->mysql_error;
		return null;
	}
		while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){
			$path[trim($ligne["source_path"])]=$ligne["source_path"];
			
		}
	
	$path[null]="{select}";
	
	$tpl=new templates();
	$html=Field_array_Hash($path,'source_path',null,"browse_source_path()");
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function table_clean(){
	$sql="DELETE FROM dar_index";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function count_of_tables(){
	$sql="SELECT COUNT(*) as tcount FROM dar_index";
	$q=new mysql();
	writelogs("$sql",__FUNCTION__,__FILE__);
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["tcount"]==null){$ligne["tcount"]=0;}
	$tpl=new templates();
	$line="{$ligne["tcount"]} {rows} {in_mysql_database}";
	if($ligne["tcount"]>0){
		$html="<table style='width:100%'>
		<tr>
			<td valign='top'><strong style='font-size:12px'>$line</strong></td>
			<td valign='top' width='1%'>" . imgtootltip('ed_delete.gif',"{clean_database}","DarBrowseEmptyMysqlDatabase();")."</td>
		</tr>
		</table>";
			
	}else{
		$html=$line;
	}
	
	echo $tpl->_ENGINE_parse_body($html,"dar.index.php");
	
	
}

function DirCaches($source_path,$mountedpath){
$KEY=$source_path.$mountedpath;
if(is_array($_SESSION["DAR_PATH__BROWSER_CACHE"][$KEY])){
		writelogs("$source_path: ".count($_SESSION["DAR_PATH__BROWSER_CACHE"][$KEY])." entries",__FUNCTION__,__FILE__);
		return $_SESSION["DAR_PATH__BROWSER_CACHE"][$KEY];
	}	
	
$source_path=addslashes($source_path);	
$sql="SELECT basepath,source_path,mount_md5 from dar_index GROUP BY basepath,mount_md5 HAVING source_path='$source_path' AND mount_md5='$mountedpath'";
$q=new mysql();
writelogs("$sql",__FUNCTION__,__FILE__);
$resultats=$q->QUERY_SQL($sql,'artica_backup');

	$resultats=$q->QUERY_SQL($sql,'artica_backup');
		while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){
			$_SESSION["DAR_PATH__BROWSER_CACHE"][$KEY][$ligne["basepath"]]=$ligne["basepath"];
			
		}

return $_SESSION["DAR_PATH__BROWSER_CACHE"][$KEY];		
	
}

function DirSubCaches($source_path,$subcache,$mountedpath){
	$KEY=md5($mountedpath.$source_path);
if(is_array($_SESSION["DAR_SUB_PATH"][$KEY][$subcache])){
	writelogs("$source_path/$subcache: ".count($_SESSION["DAR_SUB_PATH"][$KEY][$subcache])." entries",__FUNCTION__,__FILE__);
	return $_SESSION["DAR_SUB_PATH"][$KEY][$subcache];
	}
$source_path=addslashes($source_path);	
$subcache=addslashes($subcache);
$sql="SELECT basepath,source_path,mount_md5 from dar_index GROUP BY basepath,mount_md5 HAVING source_path='$source_path' AND mount_md5='$mountedpath' 
AND basepath LIKE '$subcache/%'";
writelogs($sql,__FUNCTION__,__FILE__);

$q=new mysql();
$resultats=$q->QUERY_SQL($sql,'artica_backup');

	$resultats=$q->QUERY_SQL($sql,'artica_backup');
		while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){
			
			$paths=$ligne["basepath"];
			$paths=str_replace("$subcache/","",$paths);
			$tbl=explode('/',$paths);
			writelogs("$paths={$tbl[0]}",__FUNCTION__,__FILE__);
			$realpath=$tbl[0];
			if($realpath==$subcache){continue;}
			$_SESSION["DAR_SUB_PATH"][$KEY][$subcache][$realpath]=$realpath;
			
		}

	if(!is_array( $_SESSION["DAR_SUB_PATH"][$KEY][$subcache])){ $_SESSION["DAR_SUB_PATH"][$KEY][$subcache]=array();}	
	return $_SESSION["DAR_SUB_PATH"][$KEY][$subcache];
}

function DirFilesCaches($source_path,$subcache,$mountedpath){
$KEY=md5($mountedpath.$source_path);	
if(is_array($_SESSION["DAR_FILE_PATH"][$KEY][$subcache])){return $_SESSION["DAR_FILE_PATH"][$KEY][$subcache];}
$subcache=addslashes($subcache);
$source_path=addslashes($source_path);	
$sql="SELECT filepath,filedate,filekey,filesize,mount_md5 from dar_index WHERE source_path='$source_path' AND basepath='$subcache' AND mount_md5='$mountedpath'";
writelogs($sql,__FUNCTION__,__FILE__);

$q=new mysql();
$resultats=$q->QUERY_SQL($sql,'artica_backup');
		while($ligne=mysql_fetch_array($resultats,MYSQL_ASSOC)){
			
			$paths=$ligne["filepath"];
			$paths=str_replace($subcache."/","",$paths);
			$_SESSION["DAR_FILE_PATH"][$KEY][$subcache][$ligne["filekey"]]=array("DATE"=>$ligne["filedate"],"PATH"=>$paths,"SIZE"=>$ligne["filesize"]);
			
		}

	if(!is_array( $_SESSION["DAR_FILE_PATH"][$KEY][$subcache])){ $_SESSION["DAR_FILE_PATH"][$KEY][$subcache]=array();}	
	return $_SESSION["DAR_FILE_PATH"][$KEY][$subcache];
}

function browse_source_path(){
	$cryt=new SimpleCrypt();
	$mounted_path=$cryt->decrypt(base64_decode($_GET["mounted-path"]));	
	$source_path=$_GET["browse-source-path"];
	$array=DirCaches($source_path,$mounted_path);
	
	$html="<table style='width:100%'>";
	while (list ($num, $line) = each ($array)){
		
		
		$path_array=explode('/',$num);
		$path[$path_array[0]]=$path_array[0];
		
	}
	
	while (list ($num, $line) = each ($path)){
		$html=$html . "<tr>
			<td><strong>$num</strong></td>
			</tr>";
	}
		
	
	
	
	$html=$html . "</table>";
	echo $html;
	
}


function popup(){
	
$html="<H1>{browse_external_storage}</H1>

		<table style='width:100%'>
		<tr>
			<td valign='top'>
			<p class=caption>{browse_external_storage_text}<hr><span id='darLinesCount' style='font-weight:bold;font-size:12px'></span></p>
			</td>
		<td valign='top' class=legend>
			<table style='width:100%'>
			<tr>
				<td valign='top' class=legend>{resource}:</td>	
				<td valign='top'><div id='mounted_path'></div></td>
				<td valign='top'>". imgtootltip('20-refresh.png',"{refresh}","sources_path_list()")."</td>
				
			</tr>			
			<tr>
				<td valign='top' class=legend>{original_path}:</td>	
				<td valign='top'><div id='source_list'></div></td>
				<td valign='top'>". imgtootltip('20-refresh.png',"{refresh}","browse_mounted_path()")."</td>
			</tr>
			</table>			
				
				
			</td>
		</tr>
		
		
		
		</table>
<br>
<table style='width:100%'>
<tr>
<td valign='top' width=50%>
<div style='width:100%;height:400px;overflow:auto'>" . RoundedLightWhite("
<div id='browserarea'></div>")."</div>
</td>
<td valign='top' width=50%>
".RoundedLightWhite("<div id='folder_area' style='width:100%;height:400px;overflow:auto'></div>")."
</td>
</tr>
</table>



";	

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,"dar.index.php");

}



function list_files(){
	$base=$_GET["browse-tree"];
	$folder=$_GET["dar-list-files"];
	$cryt=new SimpleCrypt();
	$mounted_path_decrypted=$cryt->decrypt(base64_decode($_GET["mounted-path"]));
	
	
	
	$tpl=new templates();
	$KEY=md5(implode("",$_GET));
	if(isset($_SESSION["DAR_FILE_PAGE"][$KEY])){
		echo $tpl->_ENGINE_parse_body($_SESSION["DAR_FILE_PAGE"][$KEY],"dar.index.php");
		return true;
	}
	
	$folder=str_replace($base,'',$folder);
	if(substr($folder,strlen($folder)-1,1)=='/'){$folder=substr($folder,0,strlen($folder)-1);}
	if(substr($folder,0,1)=='/'){$folder=substr($folder,1,strlen($folder));}
	$folder=str_replace('//','/',$folder);
	
	$array=DirFilesCaches($base,$folder,$mounted_path_decrypted);
	
	$foldernum=get_path_id($folder,$mounted_path_decrypted); 
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='middle'><strong>$folder</strong></td>
		<td width=1%>". imgtootltip('24-redo.png',"{restore}","DarLoadPathDetails('$foldernum');")."</td>
	</tr>
	</table>
	<hr>
	<table style='width:100%'>
	";
	
	while (list ($num, $line) = each ($array)){
		
		$file_path=$line["PATH"];
		$date=$line["DATE"];
		$size=$line["SIZE"];
		$js="DarLoadPathDetails('$num');";
		
		$size=ParseBytes($size);
		
		$html=$html . "
		<tr ". CellRollOver($js).">
		<td width=1% valign='top'>".get_img_ext($file_path)."</td>
		<td><code style='font-size:11px'>$file_path</code></td>
		<td><code style='font-size:11px'>$size</code></td>
		<td width=1% valign='top' nowrap>$date</td>
		
		</tr>
		";
		
	}
	
	
	$html=$html . "</table>";
	
	
	$_SESSION["DAR_FILE_PAGE"][$KEY]=$html;
	echo $tpl->_ENGINE_parse_body($html,"dar.index.php");
	
	
}

function get_path_id($path,$mounted){
	$sql="SELECT filekey FROM dar_index where filepath LIKE '%$path' AND mount_md5='$mounted' AND filesize=0";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return $ligne["filekey"];
}


function json_root($path=null){
	
	$cryt=new SimpleCrypt();
	$mounted_path_decrypted=$cryt->decrypt(base64_decode($_GET["mounted-path"]));
	$source_path=$_GET["browse-tree"];
	$mountedpath=$_GET["mounted-path"];
	$path_requested=$_POST["dir"];
	$path_requested=str_replace('//','/',$path_requested);
	$tpl=new templates();
	$settings=$tpl->_ENGINE_parse_body('{settings}');
	$directory=$tpl->_ENGINE_parse_body('{directory}');
	
	writelogs("Requested $path_requested ($source_path on $mounted_path_decrypted))",__FUNCTION__,__FILE__);
	$array=DirCaches($source_path,$mounted_path_decrypted);
	$page=CurrentPageName();
	
	
	if($path_requested<>$source_path){
		writelogs("$path_requested<>$source_path",__FUNCTION__,__FILE__);
		if(substr($path_requested,strlen($path_requested)-1,1)=='/'){
			writelogs("strip slash of $path_requested",__FUNCTION__,__FILE__);
			$path_requested=substr($path_requested,0,strlen($path_requested)-1);
		}
		
		$path_requested=str_replace($source_path,'',$path_requested);
		if(substr($path_requested,0,1)=='/'){$path_requested=substr($path_requested,1,strlen($path_requested));}
		writelogs("path_requested=$path_requested",__FUNCTION__,__FILE__);
		writelogs("DirSubCaches($source_path,$path_requested,$mounted_path_decrypted)",__FUNCTION__,__FILE__);
		$path=DirSubCaches($source_path,$path_requested,$mounted_path_decrypted);
		
		
	}else{
		if(is_array($array)){
			while (list ($num, $line) = each ($array)){
				$path_array=explode('/',$num);
				$path[$path_array[0]]=$path_array[0];
			}	
			$newpath="P:";
		}
	}
	
	
	
if(!is_array($path)){return null;}	
echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
echo "<li class=\"file ext_settings\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir']) . "\">". htmlentities("$directory ".basename($_POST['dir'])." - $settings")."</a></li>";
	while (list($num,$val)=each($path)){
		if(trim($val)<>null){
			writelogs($val);
			echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] .'/' .$val) . "/\">" . htmlentities($val) . "</a></li>";
			
			
		}
		
	}
	if(!is_array($arr)){return null;}
	$res=implode("\n",$arr);
	if(substr($res,strlen($res)-1,1)==','){
		$res=substr($res,0,strlen($res)-1);
	}
	

}

function detailsof(){
$id=$_GET["detailsof"];
$sql="SELECT * FROM dar_index where filekey='$id'";
$q=new mysql();
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));


//print_r($ligne);

$html="<H1>".basename($ligne["filepath"])."</h1>
<table style='width:100%'>
<tr>
	<td valign='top'><img src='img/128-view-file.png'></td>
<td valign='top'>
<table style='width:100%' class=table_form>
<tr>
	<td valign='top' class=legend>{filedate}:</td>
	<td valing='top' ><strong>{$ligne["filedate"]}</strong></td>	
</tr>
<tr>
	<td valign='top' class=legend>{filesize}:</td>
	<td valing='top' ><strong>{$ligne["filesize"]}</strong></td>	
</tr>
<tr>
	<td valign='top' class=legend>{database_name}:</td>
	<td valing='top'><strong>{$ligne["database_name"]}</strong></td>	
</tr>
<tr>
	<td valign='top' class=legend>{source_path}:</td>
	<td valing='top'><strong>{$ligne["source_path"]}</strong></td>	
</tr>
<tr>
	<td valign='top' class=legend>{resource}:</td>
	<td valing='top'><strong>".hide_ressources($ligne["mount_md5"])."</strong></td>	
</tr>			
<tr>
	<td valign='top' class=legend>{server}:</td>
	<td valing='top'><strong>{$ligne["servername"]}</strong></td>	
</tr>	
<tr>
<td colspan=2 align='right'>
	<input type='button' value='{restore}&nbsp;&raquo;&raquo;' OnClick=\"javascript:Loadjs('dar.restore.php?fileid=$id')\">
</td>
</tr>


</table>
</td>
</tr>
</table>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);



	
	
	
}

?>