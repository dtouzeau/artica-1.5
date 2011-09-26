/**
 * @author touzeau
 */
var Winid;
var Working_page='postfix.performances.cache.php';




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


	


	




