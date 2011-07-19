<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ldap.inc');
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}

	if(isset($_GET["disks-list"])){disks_list();exit;}
	if(isset($_GET["tools"])){tools();exit;}
	if(isset($_GET["form-edit"])){disk_form();exit;}
	if(isset($_POST["loop-dir"])){disk_form_save();exit;}
	if(isset($_POST["loop-del"])){disk_del();exit;}
	if(isset($_POST["loopcheck"])){disk_check();exit;}
	start();
	
	
	
function start(){
	$page=CurrentPageName();
	$tpl=new templates();
	$virtual_disks=$tpl->_ENGINE_parse_body("{virtual_disks}");
	$html="<table style='width:720px'>
	<tr>
		<td valign='top' width=1%><div id='loop-tools'></div>
		<td valign='top' width=99%><div id='loop-disks-list'></div>
	</tr>
	</table>
	
	
	<script>
		function refreshLoopList(){
			LoadAjax('loop-disks-list','$page?disks-list=yes');
		}
		
		function LoopAddForm(filename){
			YahooWin2(490,'$page?form-edit=yes&filename='+escape(filename),'$virtual_disks');
		}
		
		
	refreshLoopList();
	</script>
	
	";
	echo $html;
	
}

function disks_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$refresh=imgtootltip("refresh-32.png","{refresh}","refreshLoopList()");
	$delete_disk_confirm=$tpl->javascript_parse_text("{delete_disk_confirm}");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>$refresh</th>
		<th>{disk}</th>
		<th>{name}</th>
		<th>{size}</th>
		<th>dev</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	

	$q=new mysql();
	$sql="SELECT * FROM loop_disks ORDER BY `size` DESC";
	
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$disk=basename($ligne["path"]);
		$pathesc=urlencode($ligne["path"]);
		$size=FormatBytes($ligne["size"]*1024);
		$img="Database32.png";
		if($ligne["loop_dev"]==null){$img="Database32-red.png";$ligne["loop_dev"]="&nbsp;";}
		$delete=imgtootltip("delete-32.png","{delete}","LoopDel('{$ligne["path"]}')");
		
		$href="<a href=\"javascript:blur()\" OnClick=\"javascript:LoopAddForm('{$ligne["path"]}')\"
		style='font-size:14px;font-weight:bold;text-decoration:underline'>";
		$href=null;
		$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/$img'></td>
		<td width=1% nowrap style='font-size:14px;font-weight:bold;'>$href$disk</a></td>
		<td width=1% nowrap style='font-size:14px;font-weight:bold;'>$href{$ligne["disk_name"]}</a></td>
		<td width=1% nowrap style='font-size:14px;font-weight:bold;'>$href$size</a></td>
		<td width=99% nowrap style='font-size:14px;font-weight:bold;'>{$ligne["loop_dev"]}</a></td>
		<td width=1% nowrap style='font-size:14px;font-weight:bold;'>$delete</td>
		</tr>	
		<tr class=$classtr>
		<td colspan=6 align='right'><i style='font-size:13px'>/automounts/{$ligne["disk_name"]}</i></td>
		</tr>
		";
		
	}
	
	$html=$tpl->_ENGINE_parse_body($html);
	echo "
	$html
	</table>
	<script>
		function RefreshTools(){
			LoadAjax('loop-tools','$page?tools=yes');
			}
		
	var x_LoopDel= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		refreshLoopList();
		}	
					
		
		function LoopDel(path){
			if(confirm('$delete_disk_confirm')){
				var XHR = new XHRConnection();
				XHR.appendData('loop-del',path);
				XHR.sendAndLoad('$page', 'POST',x_LoopDel);
			}
		}
		
		function loopcheck(){
			var XHR = new XHRConnection();
			XHR.appendData('loopcheck','yes');
			XHR.sendAndLoad('$page', 'POST',x_LoopDel);		
		
		}
		
		RefreshTools();
		
	</script>";
}
function tools(){
	$page=CurrentPageName();
	$tpl=new templates();
	$p=Paragraphe("64-hd-plus.png","{create_new_disk}","{create_new_virtual_disk}","javascript:LoopAddForm('')");
	$rebuild=Paragraphe("service-check-64.png","{verify_disks}","{verify_disks_text}","javascript:loopcheck()");
	
	$html="
	$p<br>$rebuild
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function disk_form(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="<div class=explain>{disk_loop_explain}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{name}:</td>
		<td>". Field_text("loop-name",null,"font-size:14px;width:120px")."</td>
		<td></td>
	</tr>	
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("loop-dir",null,"font-size:14px;width:220px")."</td>
		<td><input type='button' value='{browse}...' OnClick=\"Loadjs('SambaBrowse.php?no-shares=yes&field=loop-dir')\"></td>
	</tr>
	<tr>
		<td class=legend>{size}:</td>
		<td style='font-size:14px'>". Field_text("loop-size",null,"font-size:14px;width:90px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveLoopINMysql()")."</td>
	</tr>
	</table>

	<script>
	var x_SaveLoopINMysql= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				refreshLoopList();
				YahooWin2Hide();
			}	
			
		function SaveLoopINMysql(lvs){
				var XHR = new XHRConnection();
				XHR.appendData('loop-name',document.getElementById('loop-name').value);
				XHR.appendData('loop-dir',document.getElementById('loop-dir').value);
				XHR.appendData('loop-size',document.getElementById('loop-size').value);
				XHR.sendAndLoad('$page', 'POST',x_SaveLoopINMysql);
				
			}	
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function disk_del(){
	$path=urlencode($_POST["loop-del"]);
	$sock=new sockets();
	echo $sock->getFrameWork("lvm.php?loop-del=$path");
	
}

function disk_form_save(){
	if($_POST["loop-dir"]==null){$_POST["loop-dir"]="/home/virtuals-disks";}
	if($_POST["loop-name"]==null){$_POST["loop-name"]=time();}
	$path=$_POST["loop-dir"]."/".time().".disk";
	$size=$_POST["loop-size"];
	$t=new htmltools_inc();
	$_POST["loop-name"]=$t->StripSpecialsChars($_POST["loop-name"]);	
	if(!is_numeric($size)){$size="10000";}
	$_POST["loop-name"]=addslashes($_POST["loop-name"]);
	$sql="INSERT INTO loop_disks (`path`,`size`,`disk_name`) VALUES ('$path','$size','{$_POST["loop-name"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("lvm.php?loopcheck=yes");
}

function disk_check(){
	$sock=new sockets();
	$sock->getFrameWork("lvm.php?loopcheck=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{install_app}");	
	
}

?>
