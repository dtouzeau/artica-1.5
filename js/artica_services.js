/**
 * @author touzeau
 */
var win;
var SetTimeOut=0;
function DaemonStart(APPS){
	MyHref('system.services.php?daemon_start='+APPS)	
}


function DaemonStop(APPS){
		MyHref('system.services.php?daemon_stop='+APPS)	
		}
		
