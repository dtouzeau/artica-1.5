<?php
session_start();
if(!isset($_SESSION["uid"])){die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
$users=new usersMenus()	;
$users->BuildLeftMenus();
if(!is_array($users->main_left_menus)){die();}


$html="<ul id='blurbs'>";
$maxmenus=count($users->main_left_menus);
while (list ($num, $ligne) = each ($users->main_left_menus) ){
	$js="OnClick=\"javascript:YahooWin5(750,'$num?ajaxmenu=yes');\"";	

if($ligne["NOAJAX"]){
	if($ligne['POPUP']){
		$js="OnClick=\"javascript:s_PopUpFull('$num',800,600,'webmail');\"";
	}else{
		if(preg_match('#avascript:#',$num)){
			$js="OnClick=\"$num\";";
		}else{
			$js="OnClick=\"javascript:MyHref('$num');\"";		
		}
	}
}

if($ligne["AJAX"]<>null){$js=$ligne["AJAX"];}
if(!preg_match('#^OnClick#',$js)){$js="OnClick=\"$js\"";}
	
	
$arr[]="
<li>
	<div class='blurb-body-wrapper'>
		<div class='blurb-cap'></div>
		<div class='blurb-body' 
			OnMouseOver=\"this.className='blurb-body_over';this.style.cursor='pointer';\" 
			OnMouseOut=\"this.className='blurb-body';this.style.cursor='default'\">
			<table style='width:100%' $js>
			<tr>
				<td valign='top' width=1%>
					<img src='img/{$ligne["IMG"]}'>
				</td>
				<td valign='top'><H3>{$ligne["TITLE"]}</H3>
				<p>{$ligne["TEXT"]}</p>
			</tr>
			</table>
		</div>
	</div>
	<div class='blurb-base'></div>
</li>";
	
}

if(!isset($_GET["fromnum"])){
	$html=$html.@implode("\n",$arr);
}else{
	for($i=$_GET["fromnum"];$i<$_GET["tonum"];$i++){
		$html=$html . $arr[$i];
		
	}
	
}

$html=$html."</ul>";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

?>