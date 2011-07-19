<?php

class miniadmin{
	var $title;
	var $webpage;
	var $content;
	
	function miniadmin($content){
		$this->content=$content;
		$this->webpage=$this->headers().$this->body();
	}
	
	private function headers(){
		
	$html="<html xmlns='http://www.w3.org/1999/xhtml'>
	<head>
		<meta http-equiv='content-type' content='text/html; charset=utf-8' />
		<title>$this->title</title>
		<!-- default template  -->
		<meta name='keywords' content=''>
		<meta name='description' content=''>
		<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7' />
		<link rel='stylesheet' type='text/css' rel='styleSheet'  href='/css/artica-theme/mini-admin/jquery-ui-1.8.13.custom.css'/>
	</head>";
	return $html;
		
		
	}
	
	
	private function body(){
		
		$html="
		<body style='border:0px;padding:0px;margin:0px'>
		<table style='width:1024px;height:760px;border:0px;padding:0px;margin:0px'>
		<tr style='heigth:auto'>
			<td colspan=3 style='height:90px;'>
			<div style='height:90px;width:100%;background-image:url(/css/artica-theme/mini-admin/images/bg-top.jpg)'>
				<table style='width:100%;border:0px;padding:0px;margin:0px'>
					<tr style='heigth:auto'>
						<td><img src='/css/artica-theme/mini-admin/images/artica-logo.png'></td>
					</tr>
				</table> 
			</div>
			</td>
		</tr>
		<tr style='heigth:auto;border:0px;padding:0px;margin:0px'>
			<td width=220px style='border:0px;padding:0px;margin:0px'><span id='left-menus'></span></td>
			<td widdt='100%' style='border:0px;padding:0px;margin:0px' valign='top'><div id='BodyContent'>sxsxsxsxsxsxx$this->content</div></td>
			<td width=220px style='background-color:#9b1e18'><span id='right-menus'></span></td>
		</tr>
		</table>
		</body>
		</html>
		
		";
		return $html;
		
	}
	
	
	
	
	
}
?>