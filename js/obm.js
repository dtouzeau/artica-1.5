
function obm_export(){
    var warn_export_obm=document.getElementById('warn_export_obm').value;
    if(confirm(warn_export_obm)){
        start_export();
    }
    
    
}

function start_export(){
 YahooWin(440,'obm.index.php?export=-1');
        for(var i=0;i<6;i++){
                setTimeout('start_export_run('+i+')',1500);
        }
}
function start_export_run(number){
        LoadAjax2('message_'+number,'obm.index.php?export='+number)
        }