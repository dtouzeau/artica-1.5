/**
 * @author touzeau
 */
var Winid;
var Working_page='postfix.performances.cache.php';



function PostFixAddServerCache(){
	Winid=LoadWindows(430,330,Working_page,'PostFixAddServerCache=yes');
	}
	
function PostFixSaveServerCache(){
	ParseForm('FFM3Cache',Working_page,true);
	RemoveDocumentID(Winid);
	CacheReloadList();
	}
	
function PostFixSaveServerCacheSettings(){
	ParseForm('FFMA',Working_page,true);
	
}	

function PostFixVerifyDatabaseSave(){
	ParseForm('FFMDBCache','postfix.performances.verify.map.php',true);
	
}
function PostFixVerifyDatabaseDeleteSave(){
	document.getElementById('address_verify_map').value='';
	ParseForm('FFMDBCache','postfix.performances.verify.map.php',true);
}

	

function CacheReloadList(){
	var XHR = new XHRConnection();	
	XHR.appendData('CacheReloadList','yes');	
	XHR.setRefreshArea('ServerCacheList');
	XHR.sendAndLoad(Working_page, 'GET');	
	}
	
function PostFixDeleteServerCache(server){
	var XHR = new XHRConnection();	
	XHR.appendData('PostFixDeleteServerCache',server);
	XHR.sendAndLoad(Working_page, 'GET');	
	CacheReloadList();
}	



