/**
 * @author touzeau
 */
var secs;
var cursec;
var Indexlimit="0:20";
var Indexparselimit;


function index_StartTimer(){
	Indexparselimit=Indexlimit.split(":")
	Indexparselimit=Indexparselimit[0]*60+Indexparselimit[1]*1;
	Index_beginrefresh();	
	
}

function Index_beginrefresh(){
	if (Indexparselimit==1){
		index_LoadStatus();
		index_StartTimer();
		}
	else{ 
		Indexparselimit-=1
		curmin=Math.floor(Indexparselimit/60)
		cursec=Indexparselimit%60
		setTimeout("Index_beginrefresh()",800)
		}
}

