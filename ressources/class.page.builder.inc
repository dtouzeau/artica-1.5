<?php

class pagebuilder{
	var $language;
	
	
	function pagebuilder(){
		if(!function_exists("writelogs")){include_once(dirname(__FILE__)."/logs.inc");}
		
	}
	
	
	function buildHeads($array){
		if($GLOBALS["EXECUTED_AS_ROOT"]){return;}
		if(!isset( $GLOBALS["ADD_HTML_HEADER"])){ $GLOBALS["ADD_HTML_HEADER"]="";}
		if(!isset( $array["TITLE"])){ $array["TITLE"]="";}
		if(!isset( $array["css"])){ $array["css"]="";}
		if(!isset( $array["INSERT"])){ $array["INSERT"]="";}
		if(!isset( $array["JS"])){ $array["JS"]=array();}
		if(!isset( $array["refresh"])){ $array["refresh"]=0;}
		
		
		
		$js_addon="";
		$js_addon3="";
		$js_addon_2="";
		$javascripts="";
		$html="";
		$mouse_position_js="";
		
		$backspace="\n\t\t";

		if(substr($this->language,strlen($this->language),1)=='/'){
			$this->language=substr($this->language,0,strlen($this->language)-1);
		}
		$page=$this->CurrentPageName();
		if(is_file(dirname(__FILE__) . "/language/$this->language/$page.js")){
		 	$js_addon="$backspace<script type=\"text/javascript\" language=\"JavaScript\" src=\"/ressources/language/$this->language/$page.js\"></script>";
		}
		
		if($array["INSERT"]<>null){
			$js_addon3="$backspace<script type=\"text/javascript\">\n\t".file_get_contents($array["INSERT"]) . "\n</script>";
		}		
		
		if($this->js_add<>null){
			$js_addon_2="$backspace<script type=\"text/javascript\" language=\"JavaScript\" src=\"/ressources/language/$this->language/$this->js_add\"></script>";
		}
		
		if($array["css"]==null){
			$array["css"]="$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/img/content.css\"/>$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"img/forms.css\"/>";
			
		}
		
		
		if($array["refresh"]>0){$refresh="<meta http-equiv=\"refresh\" content=\"{$array["refresh"]}\">";}
		
		$tafelpages["domains.edit.group.php"]=true;
		$tafelpages["artica.backup.index.php"]=true;
		$tafelpages["SambaBrowse.php"]=true;
		$tafelpages["fileshares.index.php"]=true;
			
			
			
			
			if(is_array($array["JS"])){
				while (list ($num, $val) = each ($array["JS"]) ){
				if($val==null){continue;}
				$javascripts=$javascripts . "$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"$val\"></script>";
				}
			}else{$javascripts=$array["JS"];}
			
		
			$html=$html . $backspace.$mouse_position_js;
			$html=$html . "$backspace<!--  css files  -->\n";
			$html=$html .  $GLOBALS["ADD_HTML_HEADER"]."\n".$this->headcss();
			$html=$html . "$backspace<!-- Artica javascript  -->";
			$html=$html . $this->jsArtica();
			$html=$html . "$backspace<!-- js Artica  -->";
			$html=$html . $js_addon;
			$html=$html . $js_addon_2;
			$html=$html . $js_addon3;
			$html=$html . "$javascripts\n";
			//$html=$html . $this->YahooInstances();
			
			$html=str_replace('<script type="text/javascript" language="javascript" src=""></script>','',$html);
			$this->head=$html;
			return $this->head;		
		
		
	}
	
	private function CurrentPageName(){
		$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);
		return $phpPage;
	}  	
	
	
	public function jsArtica(){
			if($GLOBALS["EXECUTED_AS_ROOT"]){return;}
			$mem_id=__CLASS__.__FUNCTION__.$_SERVER['HTTP_USER_AGENT'];
			if(isset($GLOBALS[$mem_id])){if($GLOBALS[$mem_id]<>null){return $GLOBALS[$mem_id];}}
			if(isset($_SESSION["CACHE_PAGE"][$mem_id])){if($_SESSION["CACHE_PAGE"][$mem_id]<>null){return $_SESSION["CACHE_PAGE"][$mem_id];}}
			$backspace="\t\t";
			$page=$this->CurrentPageName();
			$mouse="";
			$tafel=null;
			$jsTable=array();
			
			$html="\n$backspace<!-- {$_SERVER['HTTP_USER_AGENT']}  -->\n" ;
			writelogs("HTTP_USER_AGENT::{$_SERVER['HTTP_USER_AGENT']} {$_SERVER["SERVER_NAME"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			
			if(preg_match("#curl\/[0-9\.]+#",$_SERVER['HTTP_USER_AGENT'])){
				$IP=$_SERVER["REMOTE_ADDR"];
				$hostname=gethostbyaddr($IP);
				$file=basename($_SERVER["SCRIPT_FILENAME"]);
				if($file=="miniadm.php"){die();}
				writelogs("HTTP_USER_AGENT::curl -> $IP ($hostname) {$_SERVER["SCRIPT_FILENAME"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(is_array($_SERVER["argv"])){
					while (list ($num, $val) = each ($_SERVER["argv"]) ){
						writelogs("HTTP_USER_AGENT::curl -> argv $num \"$val\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					}
				}
			}
			$jquerys[]="jquery-1.6.1.min.js";
			$jquerys[]="jquery-1.6.min.js";
			$jquerys[]="jquery-1.5.1.min.js";
			$jquerys[]="jquery-1.4.4.min.js";
			$jquerys[]="jquery-1.4.2.min.js";
			
			while (list ($num, $val) = each ($jquerys) ){
				if(is_file("/usr/share/artica-postfix/js/$val")){$jqueryok=$val;break;}
			}
			
			
			$jsTable[]="/mouse.js";
			$jsTable[]="/XHRConnection.js";
			$jsTable[]="/js/float-barr.js";
			$jsTable[]="/TimersLogs.js";
			$jsTable[]="/js/artica_confapply.js";
			$jsTable[]="/js/edit.user.js";
			$jsTable[]="/js/cookies.js";
			$jsTable[]="/default.js";
			//$jsTable[]="/js/jquery-1.4.2.min.js";
			//$jsTable[]="/js/jquery-ui-1.8.6.custom.min.js";
			//$jsTable[]="/js/jquery-1.5.1.min.js";
			$jsTable[]="/js/$jqueryok";
			//$jsTable[]="/js/jquery-ui-1.8.10.custom.min.js";
			$jsTable[]="/js/jquery-ui-1.8.13.custom.min.js";
			$jsTable[]="/js/jqueryFileTree.js";
			$jsTable[]="/js/jquery.easing.1.3.js";
			$jsTable[]="/js/thickbox-compressed.js";
			$jsTable[]="/js/jquery.simplemodal-1.3.3.min.js";
			$jsTable[]="/js/jquery.jgrowl_minimized.js";
			$jsTable[]="/js/jquery.cluetip.js";
			$jsTable[]="/js/jquery.blockUI.js";
			$jsTable[]="/js/jquery.treeview.min.js";
			$jsTable[]="/js/jquery.treeview.async.js";
			$jsTable[]="/js/jquery.tools.min.js";
			$jsTable[]="/js/jquery.qtip.js";


			if(($this->CurrentPageName()=="logon.php") OR ($this->CurrentPageName()=="logoff.php") OR ($this->CurrentPageName()=="404.php")){
				$tafel=null;
				$floatbarr=null;
				$TimersLogs=null;
				$artica_confapply=null;
				$editusers=null;
				}
			
			// view http://headjs.com/
			$f[]="\n{$backspace}<script>";
			while (list ($num, $func) = each ($jsTable) ){
				$f[]="{$backspace}head.js('$func');";
				$chrome[]="$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"$func\"></script>";
				
			}
			
			$f[]="{$backspace}if(head.browser.ie){head.js('/mouse_ie.js');}";
			$f[]="{$backspace}</script>\n";
			
			$oldMethod=false;
			if(preg_match("#Chrome\/8#",$_SERVER['HTTP_USER_AGENT'])){$oldMethod=true;}
			if(preg_match("#Safari\/#",$_SERVER['HTTP_USER_AGENT'])){$oldMethod=true;}
			if(preg_match("#Firefox\/4\.0b[0-9]+#",$_SERVER['HTTP_USER_AGENT'])){$oldMethod=true;}
			$oldMethod=true;
			
			writelogs("Using old method to load js : $oldMethod",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			if($oldMethod){
				$html=$html . "$backspace".@implode("\n",$chrome);
				
			}else{
				$html=$html . "\n$backspace<script type=\"text/javascript\" language=\"javascript\" src=\"js/head.min.js\"></script>";
				$html=$html . "$backspace".@implode("\n",$f);				
			}
			$_SESSION["CACHE_PAGE"][$mem_id]=$html;
			$_SESSION["CACHE_PAGE"][$mem_id]=$html;
			return "$html\n";		
	}
	
	function headcss(){
		if($GLOBALS["EXECUTED_AS_ROOT"]){return;}
		$backspace="\n\t\t";
		if(!isset($_COOKIE["artica-template"])){$_COOKIE["artica-template"]="";}
		//if(strlen($_SESSION["ARTICA_HEAD_TEMPLATE"])>100){return $backspace."<!-- Session head -->".$_SESSION["ARTICA_HEAD_TEMPLATE"];}
		if($_COOKIE["artica-template"]==null){$_COOKIE["artica-template"]="default";}
		if($_COOKIE["artica-template"]=='!nil'){$_COOKIE["artica-template"]="default";}
		$template_file="index.html";
		if(isset($GLOBALS["CHANGE_TEMPLATE"])){$template_file=$GLOBALS["CHANGE_TEMPLATE"];}
		
		
			$html=$backspace."<!-- template used :".dirname(__FILE__)."/templates/{$_COOKIE["artica-template"]}/$template_file-->";
			
			$css_files["menu.css"]="menu.css";
			$css_files["contact.css"]="contact.css";
			$css_files["style.css"]="style.css";
			$css_files["styles_rounded.css"]="styles_rounded.css";
			$css_files["styles_tabs.css"]="styles_tabs.css";
			$css_files["styles_forms.css"]="styles_forms.css";
			$css_files["styles_tables.css"]="styles_tables.css";
			$css_files["titles.css"]="titles.css";
			$css_files["nav.css"]="nav.css";
			$css_files["menus_top.css"]="menus_top.css";
			$css_files["blurps.css"]="blurps.css";
			$css_files["isoqlog.css"]="isoqlog.css";
			
			if((CurrentPageName()=="logon.php") OR (CurrentPageName()=="logoff.php") OR (CurrentPageName()=="404.php")){
				unset($css_files["menu.css"]);
				unset($css_files["style.css"]);
				unset($css_files["isoqlog.css"]);
				unset($css_files["blurps.css"]);
				unset($css_files["nav.css"]);
				unset($css_files["styles_tables.css"]);	
				unset($css_files["styles_forms.css"]);
				unset($css_files["styles_tabs.css"]);
				unset($css_files["styles_rounded.css"]);		
			}
			
			
			if($GLOBALS["JQUERY_UI"]<>null){
				unset($css_files["styles_forms.css"]);
				unset($css_files["style.css"]);
				unset($css_files["styles_tabs.css"]);
				unset($css_files["styles_rounded.css"]);
				unset($css_files["menus_top.css"]);
				unset($css_files["styles_tables.css"]);	
			
			}
			
				reset($css_files);
				while (list ($num, $val) = each ($css_files) ){
					if(file_exists(dirname(__FILE__)."/templates/{$_COOKIE["artica-template"]}/$val")){
						$css_array[]="$backspace<link rel=\"stylesheet\" type=\"text/css\" rel=\"styleSheet\"  href=\"/ressources/templates/{$_COOKIE["artica-template"]}/$val\" />";
					}else{
						$csserror[]="$backspace<!-- missing ".dirname(__FILE__)."/templates/{$_COOKIE["artica-template"]}/$val -->";
						
					}
				}
				
			if(is_array($csserror)){$html=$html . "$backspace".implode("",$csserror);}
			if(is_array($css_array)){$html=$html . "$backspace".implode("",$css_array);}
			$html=$html . "$backspace<link href=\"/css/calendar.css\" rel=\"stylesheet\" type=\"text/css\">";
			$html=$html . "$backspace<link href=\"/js/jqueryFileTree.css\" rel=\"stylesheet\" type=\"text/css\">";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/uploadify.css\" />";	
			//$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"css/artica-theme/jquery-ui-1.8.6.custom.css\" />";
			
			$jqueryUi="artica-theme";
			if(isset($GLOBALS["JQUERY_UI"])){$jqueryUi=$GLOBALS["JQUERY_UI"];}
			$uicss=$this->JqueryUiCss($jqueryUi);
			
			$html=$html . "$backspace<!-- jqueryUi:$jqueryUi -->";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"$uicss\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/jquery.jgrowl.css\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/jquery.cluetip.css\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/jquery.treeview.css\" />";
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/thickbox.css\" media=\"screen\"/>";		
			$html=$html . "$backspace<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/jquery.qtip.css\" />";
			$html=$html . "$backspace<div id='PopUpInfos' style='position:absolute'></div>";
			$html=$html . "$backspace<div id='find' style='position:absolute'></div>";
			
			
			
			$_SESSION["ARTICA_HEAD_TEMPLATE"]=$html;
			return $html;
		}

		private function JqueryUiCss($theme){
			if(is_file("/usr/share/artica-postfix/css/$theme/jquery-ui.custom.css")){return "/css/$theme/jquery-ui.custom.css";}
			if(is_file("/usr/share/artica-postfix/css/$theme/jquery-ui-1.8.10.custom.css")){return "/css/$theme/jquery-ui-1.8.10.custom.css";}
			if(is_file("/usr/share/artica-postfix/css/$theme/jquery-ui-1.8.6.custom.css")){return "/css/$theme/jquery-ui-1.8.6.custom.css";}
			if(is_file("/usr/share/artica-postfix/css/$theme/jquery-ui-1.7.2.custom.css")){return "/css/$theme/jquery-ui-1.7.2.custom.css";}
			}

	function YahooBody(){
			if($GLOBALS["EXECUTED_AS_ROOT"]){return;}
			$conf="";
			$conf=$conf ."<div id=\"SetupControl\" style='width:0;height:0'></div>\n";			
			$conf=$conf. "<div id=\"dialogS\" style='width:0;height:0'></div> \n";
			$conf=$conf. "<div id=\"dialogT\" style='width:0;height:0'></div> \n";
			$conf=$conf ."<div id=\"dialog0\" style='width:0;height:0'></div> \n";
			$conf=$conf ."<div id=\"dialog1\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog2\" style='width:0;height:0'></div> \n";
			$conf=$conf ."<div id=\"dialog3\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog4\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog5\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"dialog6\" style='width:0;height:0'></div>\n"; 
			$conf=$conf ."<div id=\"YahooUser\" style='width:0;height:0'></div>\n"; 
			$conf=$conf ."<div id=\"logsWatcher\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"WinORG\" style='width:0;height:0'></div>\n";	
			$conf=$conf ."<div id=\"WinORG2\" style='width:0;height:0'></div>\n";						
			$conf=$conf ."<div id=\"RTMMail\" style='width:0;height:0'></div>\n";			
			$conf=$conf ."<div id=\"Browse\" style='width:0;height:0'></div>\n";
			$conf=$conf ."<div id=\"SearchUser\" style='width:0;height:0'></div>\n";									
			return $conf;		
	}

	function monitorix_array($type="day"){
				$arrayz[]="monitorix/cpu1z.day.png";
				$arrayz[]="monitorix/cpu1z.month.png";
				$arrayz[]="monitorix/cpu1z.week.png";
				$arrayz[]="monitorix/cpu1z.year.png";
				$arrayz[]="monitorix/cpu2z.day.png";
				$arrayz[]="monitorix/cpu2z.month.png";
				$arrayz[]="monitorix/cpu2z.week.png";
				$arrayz[]="monitorix/cpu2z.year.png";	
				$arrayz[]="monitorix/cpu3z.day.png";
				$arrayz[]="monitorix/cpu3z.month.png";
				$arrayz[]="monitorix/cpu3z.week.png";
				$arrayz[]="monitorix/cpu3z.year.png";
				
				$arrayz[]="monitorix/kern1z.day.png";
				$arrayz[]="monitorix/kern1z.month.png";
				$arrayz[]="monitorix/kern1z.week.png";
				$arrayz[]="monitorix/kern1z.year.png";				
				
				$arrayz[]="monitorix/disk3z.day.png";
				$arrayz[]="monitorix/disk3z.month.png";
				$arrayz[]="monitorix/disk3z.week.png";
				$arrayz[]="monitorix/disk3z.year.png";				
				
				$arrayz[]="monitorix/int1z.day.png";
				$arrayz[]="monitorix/int1z.month.png";
				$arrayz[]="monitorix/int1z.week.png";
				$arrayz[]="monitorix/int1z.year.png";				
				
				$arrayz[]="monitorix/disk2z.day.png";
				$arrayz[]="monitorix/disk2z.month.png";
				$arrayz[]="monitorix/disk2z.week.png";
				$arrayz[]="monitorix/disk2z.year.png";				

				$arrayz[]="monitorix/disk1z.day.png";
				$arrayz[]="monitorix/disk1z.month.png";
				$arrayz[]="monitorix/disk1z.week.png";
				$arrayz[]="monitorix/disk1z.year.png";	
				
				$arrayz[]="monitorix/kern2z.day.png";
				$arrayz[]="monitorix/kern2z.month.png";
				$arrayz[]="monitorix/kern2z.week.png";
				$arrayz[]="monitorix/kern2z.year.png";	
				
				$arrayz[]="monitorix/net02z.day.png";
				$arrayz[]="monitorix/net02z.month.png";
				$arrayz[]="monitorix/net02z.week.png";
				$arrayz[]="monitorix/net02z.year.png";							

				
				$arrayz[]="monitorix/int2z.day.png";
				$arrayz[]="monitorix/int2z.month.png";
				$arrayz[]="monitorix/int2z.week.png";
				$arrayz[]="monitorix/int2z.year.png";	

				$arrayz[]="monitorix/net01z.day.png";
				$arrayz[]="monitorix/net01z.month.png";
				$arrayz[]="monitorix/net01z.week.png";
				$arrayz[]="monitorix/net01z.year.png";								
	
				$arrayz[]="monitorix/cpu1.day.png";
				$array[]="monitorix/cpu2.day.png";
				$array[]="monitorix/cpu3.day.png";
				$arrayz[]="monitorix/disk1.day.png";
				$array[]="monitorix/disk2.day.png";
				$array[]="monitorix/disk3.day.png";
				
			
				
				
				$array[]="monitorix/cpu1.month.png";
				$array[]="monitorix/cpu1.week.png";
				$array[]="monitorix/cpu1.year.png";
				
				
				
				$array[]="monitorix/cpu2.month.png";
				$array[]="monitorix/cpu2.week.png";
				$array[]="monitorix/cpu2.year.png";
				
				
				$array[]="monitorix/cpu3.month.png";
				$array[]="monitorix/cpu3.week.png";
				$array[]="monitorix/cpu3.year.png";
				
				
				$array[]="monitorix/disk1.month.png";
				$array[]="monitorix/disk1.week.png";
				$array[]="monitorix/disk1.year.png";
				
				
				$array[]="monitorix/disk2.month.png";
				$array[]="monitorix/disk2.week.png";
				$array[]="monitorix/disk2.year.png";
				
				
				$array[]="monitorix/disk3.month.png";
				$array[]="monitorix/disk3.week.png";
				$array[]="monitorix/disk3.year.png";
				
				$arrayz[]="monitorix/int1.day.png";
				$array[]="monitorix/int1.month.png";
				$array[]="monitorix/int1.week.png";
				$array[]="monitorix/int1.year.png";
				
				$array[]="monitorix/int2.day.png";
				$array[]="monitorix/int2.month.png";
				$array[]="monitorix/int2.week.png";
				$array[]="monitorix/int2.year.png";
				
				
				$arrayz[]="monitorix/kern1.day.png";
				$array[]="monitorix/kern1.month.png";
				$array[]="monitorix/kern1.week.png";
				$array[]="monitorix/kern1.year.png";
				
				$array[]="monitorix/kern2.day.png";
				$array[]="monitorix/kern2.month.png";
				$array[]="monitorix/kern2.week.png";
				$array[]="monitorix/kern2.year.png";
				
				$arrayz[]="monitorix/net01.day.png";
				$array[]="monitorix/net01.month.png";
				$array[]="monitorix/net01.week.png";
				$array[]="monitorix/net01.year.png";

				$array[]="monitorix/net02.day.png";
				$array[]="monitorix/net02.month.png";
				$array[]="monitorix/net02.week.png";
				$array[]="monitorix/net02.year.png";
				
				$array[]="monitorix/net03.day.png";
				$array[]="monitorix/net03.month.png";
				$array[]="monitorix/net03.week.png";
				$array[]="monitorix/net03.year.png";
				$arrayz[]="monitorix/net03z.day.png";
				$arrayz[]="monitorix/net03z.month.png";
				$arrayz[]="monitorix/net03z.week.png";
				$arrayz[]="monitorix/net03z.year.png";
				$arrayz[]="monitorix/port01.day.png";
				$array[]="monitorix/port01.month.png";
				$array[]="monitorix/port01.week.png";
				$array[]="monitorix/port01.year.png";
				$arrayz[]="monitorix/port01z.day.png";
				$arrayz[]="monitorix/port01z.month.png";
				$arrayz[]="monitorix/port01z.week.png";
				$arrayz[]="monitorix/port01z.year.png";
				$array[]="monitorix/port02.day.png";
				$array[]="monitorix/port02.month.png";
				$array[]="monitorix/port02.week.png";
				$array[]="monitorix/port02.year.png";
				$arrayz[]="monitorix/port02z.day.png";
				$arrayz[]="monitorix/port02z.month.png";
				$arrayz[]="monitorix/port02z.week.png";
				$arrayz[]="monitorix/port02z.year.png";
				$array[]="monitorix/port03.day.png";
				$array[]="monitorix/port03.month.png";
				$array[]="monitorix/port03.week.png";
				$array[]="monitorix/port03.year.png";
				$array[]="monitorix/port03z.day.png";
				$array[]="monitorix/port03z.month.png";
				$array[]="monitorix/port03z.week.png";
				$array[]="monitorix/port03z.year.png";
				$array[]="monitorix/port04.day.png";
				$array[]="monitorix/port04.month.png";
				$array[]="monitorix/port04.week.png";
				$array[]="monitorix/port04.year.png";
				$array[]="monitorix/port04z.day.png";
				$array[]="monitorix/port04z.month.png";
				$array[]="monitorix/port04z.week.png";
				$array[]="monitorix/port04z.year.png";
				$array[]="monitorix/port05.day.png";
				$array[]="monitorix/port05.month.png";
				$array[]="monitorix/port05.week.png";
				$array[]="monitorix/port05.year.png";
				$array[]="monitorix/port05z.day.png";
				$array[]="monitorix/port05z.month.png";
				$array[]="monitorix/port05z.week.png";
				$array[]="monitorix/port05z.year.png";
				$array[]="monitorix/port06.day.png";
				$array[]="monitorix/port06.month.png";
				$array[]="monitorix/port06.week.png";
				$array[]="monitorix/port06.year.png";
				$array[]="monitorix/port06z.day.png";
				$array[]="monitorix/port06z.month.png";
				$array[]="monitorix/port06z.week.png";
				$array[]="monitorix/port06z.year.png";
				$array[]="monitorix/port07.day.png";
				$array[]="monitorix/port07.month.png";
				$array[]="monitorix/port07.week.png";
				$array[]="monitorix/port07.year.png";
				$array[]="monitorix/port07z.day.png";
				$array[]="monitorix/port07z.month.png";
				$array[]="monitorix/port07z.week.png";
				$array[]="monitorix/port07z.year.png";
				$array[]="monitorix/port08.day.png";
				$array[]="monitorix/port08.month.png";
				$array[]="monitorix/port08.week.png";
				$array[]="monitorix/port08.year.png";
				$array[]="monitorix/port08z.day.png";
				$array[]="monitorix/port08z.month.png";
				$array[]="monitorix/port08z.week.png";
				$array[]="monitorix/port08z.year.png";
				$array[]="monitorix/port09.day.png";
				$array[]="monitorix/port09.month.png";
				$array[]="monitorix/port09.week.png";
				$array[]="monitorix/port09.year.png";
				$array[]="monitorix/port09z.day.png";
				$array[]="monitorix/port09z.month.png";
				$array[]="monitorix/port09z.week.png";
				$array[]="monitorix/port09z.year.png";
				$array[]="monitorix/port10.day.png";
				$array[]="monitorix/port10.month.png";
				$array[]="monitorix/port10.week.png";
				$array[]="monitorix/port10.year.png";
				$array[]="monitorix/port10z.day.png";
				$array[]="monitorix/port10z.month.png";
				$array[]="monitorix/port10z.week.png";
				$array[]="monitorix/port10z.year.png";
				$array[]="monitorix/port11.day.png";
				$array[]="monitorix/port11.month.png";
				$array[]="monitorix/port11.week.png";
				$array[]="monitorix/port11.year.png";
				$array[]="monitorix/port11z.day.png";
				$array[]="monitorix/port11z.month.png";
				$array[]="monitorix/port11z.week.png";
				$array[]="monitorix/port11z.year.png";
				$array[]="monitorix/port12.day.png";
				$array[]="monitorix/port12.month.png";
				$array[]="monitorix/port12.week.png";
				$array[]="monitorix/port12.year.png";
				$array[]="monitorix/port12z.day.png";
				$array[]="monitorix/port12z.month.png";
				$array[]="monitorix/port12z.week.png";
				$array[]="monitorix/port12z.year.png";
				$arrayz[]="monitorix/proc0.day.png";
				$array[]="monitorix/proc0z.day.png";
				$arrayz[]="monitorix/proc1.day.png";
				$array[]="monitorix/proc1z.day.png";
				$arrayz[]="monitorix/servu1.day.png";
				$array[]="monitorix/servu1.month.png";
				$array[]="monitorix/servu1.week.png";
				$array[]="monitorix/servu1.year.png";
				$arrayz[]="monitorix/servu1z.day.png";
				$arrayz[]="monitorix/servu1z.month.png";
				$arrayz[]="monitorix/servu1z.week.png";
				$arrayz[]="monitorix/servu1z.year.png";
				$array[]="monitorix/servu2.day.png";
				$array[]="monitorix/servu2.month.png";
				$array[]="monitorix/servu2.week.png";
				$array[]="monitorix/servu2.year.png";
				$arrayz[]="monitorix/servu2z.day.png";
				$arrayz[]="monitorix/servu2z.month.png";
				$arrayz[]="monitorix/servu2z.week.png";
				$arrayz[]="monitorix/servu2z.year.png";
				$array[]="monitorix/servu3.day.png";
				$array[]="monitorix/servu3.month.png";
				$array[]="monitorix/servu3.week.png";
				$array[]="monitorix/servu3.year.png";
				$arrayz[]="monitorix/servu3z.day.png";
				$arrayz[]="monitorix/servu3z.month.png";
				$arrayz[]="monitorix/servu3z.week.png";
				$arrayz[]="monitorix/servu3z.year.png";
				$arrayz[]="monitorix/user1.day.png";
				$array[]="monitorix/user1.month.png";
				$array[]="monitorix/user1.week.png";
				$array[]="monitorix/user1.year.png";
				$array[]="monitorix/user1z.day.png";
				$array[]="monitorix/user1z.month.png";
				$array[]="monitorix/user1z.week.png";
				$array[]="monitorix/user1z.year.png";
				$array[]="monitorix/user2.day.png";
				$array[]="monitorix/user2.month.png";
				$array[]="monitorix/user2.week.png";
				$array[]="monitorix/user2.year.png";
				$array[]="monitorix/user2z.day.png";
				$array[]="monitorix/user2z.month.png";
				$array[]="monitorix/user2z.week.png";
				$array[]="monitorix/user2z.year.png";
				$array[]="monitorix/user3.day.png";
				$array[]="monitorix/user3.month.png";
				$array[]="monitorix/user3.week.png";
				$array[]="monitorix/user3.year.png";
				$array[]="monitorix/user3z.day.png";
				$array[]="monitorix/user3z.month.png";
				$array[]="monitorix/user3z.week.png";
				$array[]="monitorix/user3z.year.png";	
	
			while (list ($num, $val) = each ($array) ){
				
				if(!preg_match('#z\.#',$val)){
					if(preg_match('#month#',$val)){$array_m["month"][$val]=$val;}
					if(preg_match('#week#',$val)){$array_m["week"][$val]=$val;}
					if(preg_match('#day#',$val)){$array_m["day"][$val]=$val;}
				}	
				
			}
			return $array_m[$type];
				
			}	
	
	
	
}

?>