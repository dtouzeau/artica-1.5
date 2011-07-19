
var x_CrossRoadsSaveMaster= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	}
        
        
var x_CrossRoadsSaveSlave= function (obj) {
	var tempvalue=obj.responseText;
	
        
        LoadAjax('mainconfig','crossroads.index.php?main=slaves&tab=slaves&hostname=')
        
}        


function CrossRoadsSaveMaster(){
      var XHR = new XHRConnection();
      var ip=document.getElementById('PostfixMasterServerIdentity_ip').value;
      if (ip.length>0){
            document.getElementById('PostfixMasterServerIdentity').value=ip;
      }
      
      
      XHR.appendData('PostfixMasterServerIdentity',document.getElementById('PostfixMasterServerIdentity').value);
      XHR.appendData('CrossRoadsBalancingServerIP',document.getElementById('CrossRoadsBalancingServerIP').value);
      XHR.appendData('CrossRoadsPoolingTime',document.getElementById('CrossRoadsPoolingTime').value);
      
      
      
      XHR.sendAndLoad('crossroads.index.php', 'GET',x_CrossRoadsSaveMaster);
      }
      
      
function CrossRoadsSaveSlave(){
 var XHR = new XHRConnection();
      XHR.appendData('PostfixSlaveServersIdentity',document.getElementById('PostfixSlaveServersIdentity').value);
      XHR.sendAndLoad('crossroads.index.php', 'GET',x_CrossRoadsSaveSlave);   
    }
    
    
function SynchronizeSlaves(){
      var XHR = new XHRConnection();
      XHR.appendData('SynchronizeSlaves','yes');
      XHR.sendAndLoad('crossroads.index.php', 'GET',x_CrossRoadsSaveSlave);      
    }
    
function CrossRoadsDeleteServer(num){
      var XHR = new XHRConnection();
      XHR.appendData('CrossRoadsDeleteServer',num);
      XHR.sendAndLoad('crossroads.index.php', 'GET',x_CrossRoadsSaveSlave);                  
     }
     
     