<?php

class miniadmin{
	var $title;
	var $webpage;
	var $content;
	var $language;
	var $register=false;
	var $single_panel=false;
	
	function miniadmin($content=null,$notask=false,$register=false,$nodouble_panel=false){
		$this->register=$register;
		if($nodouble_panel==true){$this->single_panel=true;}
		$this->language=$this->detect_lang();
		if(!$notask){
			$this->content=$content;
			$this->webpage=$this->headers().$this->body();
		}
		
	}
	
	function Buildpage(){
		$this->language=$this->detect_lang();
		$this->webpage=$this->headers().$this->body();
		
	}
	
	private function detect_lang(){
		if(isset($_COOKIE["artica-language"])){return $_COOKIE["artica-language"];}
		if(isset($_SESSION["detected_lang"])){return $_SESSION["detected_lang"];}
		if(isset($_SESSION["OU_LANG"])){return $_SESSION["OU_LANG"];}
	}
	
	private function headers(){
		$jsArtica=$this->jsArtica();
		$tpl=new templates();
		if($this->title==null){
			$tpl->_ENGINE_parse_body($this->title="Artica::{authentication}");
		}
	$html="<html xmlns='http://www.w3.org/1999/xhtml'>
	<head>
		<meta http-equiv='content-type' content='text/html; charset=utf-8' />
		<title>$this->title</title>
		<!-- default template  -->
		<meta name='keywords' content=''>
		<meta name='description' content=''>
		<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7' />
		<link rel='stylesheet' type='text/css' rel='styleSheet'  href='/css/artica-theme/mini-admin/jquery-ui-1.8.13.custom.css'/>
		<link rel='stylesheet' type='text/css' rel='styleSheet'  href='/css/artica-theme/mini-admin/styles.css'/>
		
		<link rel='stylesheet' type='text/css' rel='styleSheet'  href='/ressources/templates/default/blurps.css' />
		
		
		
		<div id='PopUpInfos' style='position:absolute'></div>
		<div id='find' style='position:absolute'></div>
		$jsArtica
		<script type='text/javascript' language='javascript' src='/js/jquery.qtip.js'></script>
		<link rel='stylesheet' type='text/css' rel='styleSheet'  href='/css/jquery.qtip.css'/>
	</head>";
	return $html;
		
		
	}
	
	private function jsArtica(){
		include_once(dirname(__FILE__)."/class.page.builder.inc");
		$pges=new pagebuilder();
		$pges->language=$this->language;
		return $pges->jsArtica();
		
	}
	
	
	private function body(){
		$yahoo=$this->YahooBody();
		if(strlen($this->content)>2){$tpl=new templates();$this->content=$tpl->_ENGINE_parse_body($this->content);}
		$load="LoadAjax('BodyContent','miniadm.php?start-page=yes');";
		if($this->register){$load=null;}
		
		$panel="
			<td width=220px style='border:0px;padding:0px;margin:0px;padding-left:5px' valign='top'>
				<span id='left-menus' style='overflow-x: hidden'></span>
			</td>
			<td width='100%' style='border:0px;padding:0px;margin:0px' valign='top'>
				<div style='float:right;margin-top:-90px;margin-left:5px;z-index:5000 !important;display:inline;zoom:1'><img src='img/server-196.png'></div>
				<div id='BodyToolbox' style='margin-left:10px;height:110px'></div>
				<div id='BodyContent' style='margin-left:10px'>$this->content</div>
			</td>";
		
		if($this->single_panel){
			$panel="
			<td width='100%' style='border:0px;padding:0px;margin:0px' valign='top' colspan=2>
				<div style='float:right;margin-top:-90px;margin-left:5px;z-index:5000'><img src='img/server-196.png'></div>
				<div id='BodyToolbox' style='margin-left:10px;height:110px'></div>
				<div id='BodyContent' style='margin-left:10px'>$this->content</div>
			</td>";
		}
		
		$html="
		$yahoo
		<body style='border:0px;padding:0px;margin:0px;background-color:#FAFAFA;'>
		<center style='border:0px;padding:0px;margin:0px;background-color:#FAFAFA;'>
		<table style='width:1024px;height:760px;border:0px;padding:0px;margin:0px;border:1px solid black;background-color:white' cellspacing=0 cellpadding=0>
		<tr style='heigth:auto;background-image:url(/css/artica-theme/mini-admin/images/bg-top.png)'>
			<td colspan=2 style='height:90px'>
			 <div style='height:90px;width:100%;z-index:1'>
				<table style='width:100%;border:0px;padding:0px;margin:0px'>
					<tr style='heigth:auto'>
						<td><img src='/css/artica-theme/mini-admin/images/artica-logo.png' style='margin-left:10px;margin-top:5px'></td>
					</tr>
				</table> 
			</div>
			</td>
		</tr>
		<tr style='heigth:auto;border:0px;padding:0px;margin:0px'>
			$panel
		</tr>
		</table>
		</center>
		<script>
			$load
		</script>
		<input type='hidden' id='LoadAjaxPicture' value='/img/wait_verybig_mini_red.gif'>
		</body>
		</html>
		
		";
		return $html;
	}
	
	
	private function YahooBody(){
			include_once(dirname(__FILE__)."/class.page.builder.inc");
			$pges=new pagebuilder();
			$pges->language=$this->language;
			return $pges->YahooBody();
	}	
	
	
}


function Paragraphe_miniadm($img,$title,$text,$js){
	$n = rand(10e16, 10e20);
	$rand=base_convert($n, 10, 36);	
	$md5=md5("$img,$title,$text,$js,$rand");
	$dir=dirname(__FILE__)."/logs/web/cache/{$_SESSION["uid"]}";
	$targetFile="$dir/$md5.object.cache";
	if(is_file($targetFile)){return @file_get_contents($targetFile);}
	$tpl=new templates();
	$tooltip=$tpl->_ENGINE_parse_body("$text");
	$tooltip=str_replace("\n","<br>",$tooltip);
	$tooltip=str_replace("'","`",$tooltip);
	$html="
	<div id='$md5' style='width:175px'>
	<table style='width:100%;margin-bottom:15px' 
	OnMouseOver=\";this.style.cursor='pointer';\" 
	OnMouseOut=\";this.style.cursor='default';\"
	OnClick=\"javascript: Paragraphe_miniadm_$md5()\"
	>
	<tr>
		<td valign='top' width=1%><img src='img/$img' id='img-$md5'></td>
		<td valign='middle'><H3 id='h3-$md5'>$title</H3></div>
		</td>
	</tr>
	</table>
	</div>
	<input type='hidden' id='content_$md5' value='$tooltip'>
	<script>
	
		function Paragraphe_miniadm_$md5(){
			document.getElementById('img-$md5').src='img/wait_verybig_mini_red-48.gif';
			setTimeout('Paragraphe_miniadm_{$md5}_inversed()',1200);
			$js;
		}
		
		function Paragraphe_miniadm_{$md5}_inversed(){
			document.getElementById('img-$md5').src='img/$img';
		}
		
		
		function qtyp_{$md5}(){
			$(document).ready(function() {		
				$('#img-$md5').qtip({
		  			content: $('#content_$md5').val(),
		  			show: 'mouseover',
		   			hide: 'mouseout',
		   			style: 'cream',
		   			tip: true
					});
				

				$('#h3-$md5').qtip({
		  			content: $('#content_$md5').val(),
		  			show: 'mouseover',
		   			hide: 'mouseout',
		   			style: 'cream',
		   			tip: true
					});
				});					
		
		}
		
		
		
		qtyp_{$md5}();
	</script>
	"; 
	$mini=new miniadmin(null,true);
	
	$tpl=new templates();
	$tpl->language=$mini->language;
	$object=$tpl->_ENGINE_parse_body($html);
	$dir=dirname(__FILE__)."/logs/web/cache/{$_SESSION["uid"]}";
	if(!is_dir($dir)){@mkdir($dir,644,true);}
	$targetFile="$dir/$md5.object.cache";
	@file_put_contents($targetFile,$object);
	return $object;	
	
}

?>