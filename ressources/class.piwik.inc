<?php
class piwik{
	
	function piwik(){
		
		
		
	}
	
	
	
	function checkWebsite($path){
		
		$f["/plugins/AnonymizeIP/AnonymizeIP.php"]=true;
		$f["/plugins/CoreAdminHome/templates/styles.css"]=true;
		$f["/plugins/CoreAdminHome/templates/header.tpl"]=true;
		$f["/plugins/CoreAdminHome/templates/menu.tpl"]=true;
		$f["/plugins/CoreAdminHome/templates/generalSettings.js"]=true;
		$f["/plugins/CoreAdminHome/templates/generalSettings.tpl"]=true;
		$f["/plugins/CoreAdminHome/templates/footer.tpl"]=true;
		$f["/plugins/CoreAdminHome/templates/optOut.tpl"]=true;
		$f["/plugins/CoreAdminHome/templates/menu.css"]=true;
		$f["/plugins/CoreAdminHome/CoreAdminHome.php"]=true;
		$f["/plugins/CoreAdminHome/Controller.php"]=true;
		$f["/plugins/CoreAdminHome/API.php"]=true;
		$f["/plugins/Provider/Provider.php"]=true;
		$f["/plugins/Provider/functions.php"]=true;
		$f["/plugins/Provider/Controller.php"]=true;
		$f["/plugins/Provider/API.php"]=true;
		$f["/plugins/Widgetize/templates/widgetize.js"]=true;
		$f["/plugins/Widgetize/templates/iframe.tpl"]=true;
		$f["/plugins/Widgetize/templates/index.tpl"]=true;
		$f["/plugins/Widgetize/templates/test_jsinclude.tpl"]=true;
		$f["/plugins/Widgetize/templates/js.tpl"]=true;
		$f["/plugins/Widgetize/templates/test_jsinclude2.tpl"]=true;
		$f["/plugins/Widgetize/Widgetize.php"]=true;
		$f["/plugins/Widgetize/Controller.php"]=true;
		$f["/plugins/ExampleFeedburner/templates/feedburner.tpl"]=true;
		$f["/plugins/ExampleFeedburner/ExampleFeedburner.php"]=true;
		$f["/plugins/CustomVariables/Controller.php"]=true;
		$f["/plugins/CustomVariables/CustomVariables.php"]=true;
		$f["/plugins/CustomVariables/API.php"]=true;
		$f["/plugins/Live/Visitor.php"]=true;
		$f["/plugins/Live/templates/live.css"]=true;
		$f["/plugins/Live/templates/index.tpl"]=true;
		$f["/plugins/Live/templates/visitorLog.tpl"]=true;
		$f["/plugins/Live/templates/scripts"]=true;
		$f["/plugins/Live/templates/scripts/spy.js"]=true;
		$f["/plugins/Live/templates/scripts/live.js"]=true;
		$f["/plugins/Live/templates/totalVisits.tpl"]=true;
		$f["/plugins/Live/templates/lastVisits.tpl"]=true;
		$f["/plugins/Live/Controller.php"]=true;
		$f["/plugins/Live/Live.php"]=true;
		$f["/plugins/Live/API.php"]=true;
		$f["/plugins/API/templates/listAllAPI.tpl"]=true;
		$f["/plugins/API/css"]=true;
		$f["/plugins/API/css/styles.css"]=true;
		$f["/plugins/API/Controller.php"]=true;
		$f["/plugins/API/API.php"]=true;
		$f["/plugins/ExampleAPI/ExampleAPI.php"]=true;
		$f["/plugins/ExampleAPI/API.php"]=true;
		$f["/plugins/MultiSites/templates/row.tpl"]=true;
		$f["/plugins/MultiSites/templates/styles.css"]=true;
		$f["/plugins/MultiSites/templates/index.tpl"]=true;
		$f["/plugins/MultiSites/templates/common.js"]=true;
		$f["/plugins/MultiSites/MultiSites.php"]=true;
		$f["/plugins/MultiSites/Controller.php"]=true;
		$f["/plugins/CorePluginsAdmin/templates/manage.tpl"]=true;
		$f["/plugins/CorePluginsAdmin/CorePluginsAdmin.php"]=true;
		$f["/plugins/CorePluginsAdmin/Controller.php"]=true;
		while (list ($xpath, $none) = each ($f) ){
			$testfile="$path$xpath";
			if(!is_file($testfile)){
				if(!is_dir($testfile)){
					echo "echo Starting......: Apache $testfile No such file or directory\n";
					return false;
				}
			}
			
		}
		
		return true;
		
	}
	
	
	
	
}