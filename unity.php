<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');	
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["find"])){find();exit;}
if(isset($_GET["icon"])){icon();exit;}		
	js();

	
	
function icon(){
	return;
	$users=new usersMenus();
	$tpl=new templates();
		$LinuxDistriCode=$users->LinuxDistriCode;
		$LinuxDistriCodeIMG="unity-ubuntu-22.png";
		if($LinuxDistriCode=="DEBIAN"){$LinuxDistriCodeIMG="unity-debian-22.png";}
		if($LinuxDistriCode=="CENTOS"){$LinuxDistriCodeIMG="CENTOS-22.png";}
		if($LinuxDistriCode=="SUSE"){$LinuxDistriCodeIMG="unity-suse-22.png";}
		echo $tpl->_ENGINE_parse_body("<span style='float:left;margin-left:-4px'>".imgtootltip($LinuxDistriCodeIMG,"{find_features_and_options}","Loadjs('unity.php')")."</span>");
		
}	
	

function js(){
	
	
$page=CurrentPageName();

$html="function StartUnity(){
	document.onmousemove = pointeurDeplace;
	var width=750;
	var title='title';
	var uri='admin.index.php';
	var options={};
	if(!document.getElementById('UnityDivBodyContent')){
		document.getElementById('middle').innerHTML=\"<div id='UnityDivBodyContent'></div>\"+document.getElementById('middle').innerHTML;
		}else{
		$('#UnityDivBodyContent').empty().remove();
		RefreshLeftMenu();
		return;
		}
		
	document.getElementById('UnityDivBodyContent').innerHTML='';
	document.getElementById('UnityDivBodyContent').style.classname='unityDiv';
	document.getElementById('UnityDivBodyContent').style.position='absolute';
	document.getElementById('UnityDivBodyContent').style.top=(yMousePos -20) + 'px';
    document.getElementById('UnityDivBodyContent').style.left=(xMousePos +15)+ 'px';		
	document.getElementById('UnityDivBodyContent').style.opacity =0.9;
	document.getElementById('UnityDivBodyContent').style.width ='850px';
	document.getElementById('UnityDivBodyContent').style.height ='650px';
    document.getElementById('UnityDivBodyContent').style.MozOpacity =0.9;
    document.getElementById('UnityDivBodyContent').style.KhtmlOpacity =0.9;
    document.getElementById('UnityDivBodyContent').style.backgroundColor='#000000';
    document.getElementById('UnityDivBodyContent').style.zIndex='10000';
    document.getElementById('UnityDivBodyContent').style.border='3px solid white';
	document.getElementById('UnityDivBodyContent').style.borderRadius = '5px'; 
	document.getElementById('UnityDivBodyContent').style.MozBorderRadius = '5px';
    document.getElementById('UnityDivBodyContent').style.filter = \"progid:DXImageTransform.Microsoft.gradient(startColorstr='#605D5D', endColorstr='#000000');\";
    
	
	$('#Unitycallback').show('fast');
	LoadAjaxSilent('UnityDivBodyContent','$page?popup=yes');
	
	
}

function UnityFind(){
	var se =document.getElementById('unitySearchField').value;
	Set_Cookie('UNITY_FIELD_SEARCH', se, '360000', '/', '', '');
	se =escape(se);
	
	LoadAjaxSilent('UnityResults','$page?find='+se);
}

function UnityFindChck(e){
	if(checkEnter(e)){UnityFind();}
}


StartUnity();
";
	
	echo $html;
}

function popup(){
	
	$html="
	<div style='margin-top:30px;padding-left:15px'>
		<input type='text' value='{$_COOKIE["UNITY_FIELD_SEARCH"]}' style='width:90%;padding:5px' class='unityDivForm' OnKeyPress=\"javascript:UnityFindChck(event)\" id='unitySearchField'>
	</div>
	<div id='UnityResults' style='margin-top:40px;width:100%;margin-left:10px;margin-right:10px;padding:15px'></div>
	
	
	<script>
		UnityFind();
	</script>
	";
	
	echo $html;
	
}

function find(){
	$tpl=new templates();
	$lang=$_COOKIE["artica-language"];
	if($lang==null){$lang="en";}
	$uid=$_SESSION["uid"];
	if($uid==-100){$uid="RootMaster";}		
	$_GET["find"]=strtolower($_GET["find"]);
	$sql="SELECT * ,MATCH (`text`) AGAINST ('{$_GET["find"]}') AS score FROM icons_db WHERE MATCH (title,`text`) AGAINST ('{$_GET["find"]}' IN BOOLEAN MODE) 
	AND uid='$uid' AND lang='$lang' ORDER BY score DESC LIMIT 0,30";

	if(strlen(trim($_GET["find"]))<2){
		$sql="SELECT * FROM icons_db WHERE uid='$uid' AND lang='$lang' LIMIT 0,30";
	}
	
	$q=new mysql();
	if($q->COUNT_ROWS("icons_db","artica_backup")==0){
		echo $tpl->_ENGINE_parse_body("<div style='font-size:18px;text-align:center;margin:30px;color:white'>{UNITY_NO_DATAS_TEXT}</div>");
		return;
	}
	
	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<div style='color:white;font-size:16px'>$q->mysql_error<p>$sql</p></div>";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$tr[]=UnityParagraphe($ligne);
	}
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
$CC=1;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$CC++;
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		if($CC>15){break;}
}
if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>";	
	$html=implode("\n",$tables);	
    echo $tpl->_ENGINE_parse_body($html);
	
}

function UnityParagraphe($ligne){

	if(isset($GLOBALS["UNITY_LIST"][$ligne["js"]])){return;}
	$md5=md5(serialize($ligne));
	$GLOBALS["UNITY_LIST"][$ligne["js"]]=true;
	$js=base64_decode($ligne["js"]);
	$img=$ligne["icon"];
	if(preg_match("#script:(.+)#", $js,$re)){$js=$re[1];}

	if(preg_match("#([0-9]+)#", $img,$re)){
		$size=$re[1];
		if($size<64){
			$img2=str_replace($size, 64, $img);
			if(is_file("img/$img2")){$img=$img2;}
		}
	}
	
	$img=imgtootltip($img,$ligne["text"],"StartUnity();$js","left");
	$styles="
	onmouseout=\"this.className='unityPar';this.style.cursor='default';\" 
	onmouseover=\"this.className='unityParOver';this.style.cursor='pointer';\"
	OnClick=\"javascript:StartUnity();$js\"";
	
	return "
	<div id='$md5' class=unityPar style='width:220px' $styles>
			$img
			<div style='padding-left:70px'>{$ligne["title"]}
			<div style='text-align:right;border-top:1px solid #CCCCCC;font-weight:normal;margin-top:3px;padding-top:3px'><i style='font-size:11px'>{$ligne["family"]}</i></div>
			</div>
		
	</div>
			
	
	";
	
	
}





?>