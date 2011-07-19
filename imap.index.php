<?php

include_once('ressources/class.templates.inc');
include_once('ressources/class.imap.inc');


if(isset($_GET["folders"])){imap_folder();exit;}
if(isset($_GET["MessagesFromFolder"])){imap_show_messages();exit;}
if(isset($_GET["ShowMessagesFolders"])){imap_BuildSectionMessages();exit;}
if(isset($_GET["ShowMessages"])){imap_list_messages();exit;}
if(isset($_GET["ShowMessageByID"])){imap_show_messageByID($_GET["ShowMessageByID"]);exit;}
if(isset($_GET["ShowMessageAttachment"])){imap_show_attach();exit;}

page();




function Page(){
	$page=CurrentPageName();
	$cfg["JS"][]="js/imap.js";
	$cfg["JS"][]="js/tafelTree/js/scriptaculous.js";
	$cfg["JS"][]="js/tafelTree/Tree.js";
	
	
	$html="
	
	<div id='webmail_corps'>
	
	</div>
	<div id='webmail_view' style='width:670px'></div>
	
	
	<script>imap_Load_folders()</script>
	";
	
	
	$tpl=new template_users("{your_inbox}",$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	
}



function imap_folder(){
	
$imap=new IMAP();
if($imap->open('david.touzeau','180872','127.0.0.1','143')){
	if(!isset($_GET["folder_path"])){$_GET["folder_path"]='INBOX';}
$tpl=new templates();	
$array=$imap->HashListFolders($_GET["folder_path"]);
$html="	
<div style='padding-top:30px'>



	<div id='inbox' style='width:250px'></div>
<img src='img/spacer.gif' width=250px height=1px>	
</div>
<script type=\"text/javascript\">
var struct = [
{
'id':'INBOX',
'txt':'{$tpl->_ENGINE_parse_body('{Inbox}')}',
'img':'home_small.png', 
'imgopen':'home_small.png', 
'imgclose':'home_small.png',
'onclick':ImapLoadMessages,
'items':[\n\t" . buildItems($array)."]}
];
var tree = new TafelTree('inbox', struct, 'img/', '180px', 'auto');
tree.generate();
</script>";
echo $html;
}
}

function buildItems($array){
	
	
	
	
	while(list($key, $val) = each($array)) {
		$folders=explode('/',$key);
		$folder_affiche=$folders[count($folders)-1];
		switch ($folder_affiche) {
			case 'Trash':
				$img='trash.gif';
				$imgopen='trash.gif';
				break;
		
			default:
				$img="folder.gif";
				$imgopen='fol.gif';
				break;
		}


		
		$item=$item .'{';
		$item=$item ."'id':'{$key}',\n";
   		$item=$item ."\t'txt':'{$folder_affiche}',\n";
   		$item=$item ."\t'imgopen':'$imgopen',\n";
		$item=$item ."\t'imgclose':'$img',\n";   		
    	$item=$item ."\t'img':'$img',\n";
    	$item=$item ."\t'onclick':ImapLoadMessages,\n";
		if(is_array($val)){
			$item=$item ."\t'items':[" .buildItems($val)."]";
		}
		
		$item=$item ."\n},";
		
		
	}
	
	return $item;
	
}

function imap_show_messages(){
	$page=CurrentPageName();
	$folder=explode("/",$_GET["MessagesFromFolder"]);
	$title=$folder[count($folder)-1];
	$datas=imap_list_messages($_GET["MessagesFromFolder"]);
	$html="<H5>$title</H5>
	$datas";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}




function imap_list_messages($folder){
	$imap=new IMAP();
	
	if($imap->open('david.touzeau','180872','127.0.0.1','143')){
		$hash=$imap->ListMessagesInFolder($folder);
		if(count($hash)>10){$max=10;}else{ $max=count($hash);}
		if(!isset($_GET["FolderPanTab"])){$_GET["FolderPanTab"]=1;};
		
		
		$count=count($hash);
		$start=$_GET["FolderPanTab"]-1;
		$start=$start*$max;
		$end=$start+$max;
		
		
		$html=imap_list_messages_tabs(count($hash),$max,$folder)."<br><table style='width:100%;border:1px solid #005447'>
		<tr>
		<th>&nbsp;</th>
		<th>{from}</th>
		<th>{subject}</th>
		<th>{date}</th>
		</tr>
		";
		if(is_array($hash)){
			for($i=$start;$i<=$start+$max;$i++){
			$msg_array=$hash[$i];
			$msg_id=$msg_array["msg_id"];
			$link="LoadAjax('webmail_view','imap.index.php?ShowMessageByID=$msg_id&f=$folder');";
			$style='style="border-bottom:1px solid #CCCCCC;padding:2px"';
			
			if($msg_id<>null){
				$html=$html . "
				<tr " . CellRollOver($link) .">
				<td width=1% $style><img src='img/spamailbox_storage.gif'></td>
				<td $style width=1% ><strong>{$msg_array["from"]}</strong></td>
				<td $style><strong>{$msg_array["subject"]}</strong></td>
				<td width=5% nowrap $style><strong>{$msg_array["date"]}</strong></td>
				
				</tr>
				";}
			}}
	$html=$html . "</table>";}
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
}


function imap_list_messages_tabs($messages_number,$max,$folder){
	if(!isset($_GET["FolderPanTab"])){$_GET["FolderPanTab"]=1;};
	$page=CurrentPageName();
	if($messages_number==0){return null;}
	$mymax=$messages_number/$max;
	$tp=explode('.',$mymax);
	if(is_array($tp)){if($tp[1]<6){$mymax=$mymax+1;}}
	
	
	for($i=1;$i<=$mymax;$i++){
		$array[$i]="{page} $i";
		
	}
	if(!is_array($array)){return null;}
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["FolderPanTab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('webmail_corps','imap.index.php?MessagesFromFolder=$folder&FolderPanTab=$num');\" $class >$ligne</a></li>\n";
			
		}
	return "<div id='tablist'>$html</div>";		
}

function imap_show_messageByID($msg_id){
		$imap=new IMAP();
		if($imap->open('david.touzeau','180872','127.0.0.1','143')){
			$hash=$imap->DecodeMessage($msg_id,$_GET["f"]);
			
			$content=$hash[1];
			
			$subject=$hash[2];
			if(preg_match('#<BODY(.*?)>(.+?)</BODY>#is',$content,$re)){
				$content=$re[2];
			}
			
			$html="
			<br>
			
			<table style='width:100%;border:1px dotted #CCCCCC;padding:5px;margin-top:5px'>
			<tr>
			<th width=1% align='right'><strong>{from}:</strong></th>
			<th width=99%><strong>{$hash[4]}</strong></th>
			</tr>			
			<tr>
			<th width=1% align='right'><strong>{date}:</strong></th>
			<th><strong>{$hash[3]}</strong></th>
			</tr>			
			<tr>
			<th  width=1% align='right'><strong>{subject}:</strong></th>
			<th width=99%><strong>$subject</strong></th>
			</tr>			


			<tr>
			<td colspan=2 style='padding-top:10px'>$content</td>
			</tr>
			</table>
			
			";
			
			$tpl=new templates();
			echo $tpl->_ENGINE_parse_body($html);
			
			
		
		
	}
	}
function imap_show_attach(){
	$imap=new IMAP();
	if($imap->open('david.touzeau','180872','127.0.0.1','143')){
	$hash=$imap->_decode_msg($_GET["msgid"]);
	$filename=$_GET["ShowMessageAttachment"];
	header("Content-type: ".$hash["attachment"][$filename]["type"]);
	header("Content-Disposition: attachment; filename=".$filename); 
	echo $hash["attachment"][$filename]["filedata"];
	}
}
	
	



?>