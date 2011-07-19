<?php
include_once('ressources/class.templates.inc');

$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{die("No session");}

if(isset($_GET["subpage"])){echo subpage();exit;}
startpage();


function startpage(){
	
	$page=file_get_contents("ressources/isoqlog/index.html");
	$page=transformpage($page);
	echo $page;
	}
	
	
function subpage(){
	$link=$_GET["subpage"];
	$orginal_link=$_GET["subpage"];
	$refer=$_GET["refer"];

	
	$subBack=explode('\.\./',$link);
	writelogs("Count back=".count($subBack),__FUNCTION__,__FILE__);
	if(count($subBack)>1){
		$referB=explode("/",$refer);
		for($i=0;$i<count($subBack)-1;$i++){
			writelogs("Unset ".$referB[count($subBack)-$i],__FUNCTION__,__FILE__);
			unset($referB[count($subBack)-$i]);
		}
		
		$link=implode('/',$referB);
		if(substr($link,strlen($link)-strlen(".html"),strlen(".html"))<>".html"){$link=$link.'/';}
		writelogs("New link=$link",__FUNCTION__,__FILE__);
		$link=str_replace('//','/',$link);
	}
	
	
	if($link=="../"){$link="/";}
	
	if(substr($link,strlen($link)-1,1)=='/'){
		$link=$link."index.html";
	}
	writelogs("loading $link....",__FUNCTION__,__FILE__);
	
	if(!file_exists("ressources/isoqlog/$link")){
		writelogs("unable to stat ressources/isoqlog/$link",__FUNCTION__,__FILE__);
		
		if(file_exists("ressources/isoqlog/$refer$link")){
			writelogs("Found ressources/isoqlog/$refer$link",__FUNCTION__,__FILE__);
			$_GET["subpage"]="$refer$orginal_link";
			$page=file_get_contents("ressources/isoqlog/$refer$link");
		}else{
			writelogs("unable to stat ressources/isoqlog/$refer$link",__FUNCTION__,__FILE__);
			$page=file_get_contents("ressources/isoqlog/index.html");
		}
	}else{
		
		if(file_exists("ressources/isoqlog/$link")){
			$page=file_get_contents("ressources/isoqlog/$link");
		}else{
			$page=file_get_contents("ressources/isoqlog/index.html");
		}
	}
	
	
	$page=transformpage($page);
	echo $page;
	
	
	
}

function transformpage($content){
	
	$page=CurrentPageName();
	if(preg_match('#<body.+?>(.+?)</body#is',$content,$re)){
		$content=$re[1];
	}
	
	$content=str_replace("images/dot.gif",'img/link_a1.gif',$content);
	$content=str_replace('<img src="images/pk.gif" width="20" height="12" alt="pk.gif">','<img src=\'img/fw_bold.gif\'>',$content);
	$content=str_replace('class="created_date"','class="caption"',$content);
	$content=str_replace("<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#666600'>","<span class=caption>",$content);
	$content=str_replace('<font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#666600">',"<span class=caption>",$content);
	$content=str_replace("</font>","</span>",$content);
	$content=str_replace("<a href='http://validator.w3.org/check/referer'>","",$content);
	$content=str_replace("<img border='0' src='http://www.w3.org/Icons/valid-html401' alt='Valid HTML 4.01!' height='31' width='88'></a>","",$content);
	$content=str_replace("<a href='http://www.enderunix.org'>EnderUNIX software development team @Istanbul/Turkey</a>",'',$content);
	$content=str_replace("../images/isoqlog.gif","img/fleche-20-red-left.png",$content);
	$content=str_replace("../images/home.gif","img/fleche-20-black-left.png",$content);
	$content=str_replace("../images/up.gif","img/fleche-20-up.jpg",$content);
	$content=str_replace('width="24" height="23"','',$content);
	
	
	
	
	if(preg_match_all('#href=[\'"](.+?)[\'"]#is',$content,$re)){
		
		while (list ($num, $val) = each ($re[0]) ){
			$link=$re[1][$num];
			$content=str_replace($val,"href='#' OnClick=\"javascript:LoadAjax('events','$page?main=isoqlog&subpage=$link&refer={$_GET["subpage"]}');\"",$content);
		}
		
		
	}
	
	
	return "<div id='isoqlog' style='margin-left:10px;margin-top:10px;padding:5px;border-bottom:1px solid #CCCCCC;border-right:1px solid #CCCCCC;'>
		<input type='hidden' name='switch' value='no' id='switch'>
		$content
	</div>";
	
	
	
	
}




?>



