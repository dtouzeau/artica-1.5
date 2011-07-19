/**
 * @author touzeau
 */

var tempvalue='';

			
	function CurrentPageName(){
		var sPath = window.location.pathname;
		var sPage = sPath.substring(sPath.lastIndexOf('/') + 1);
		return sPage;		
		}
		
	
	function AddEntity(){
		var addtext=document.getElementById('text_add_entity').value
		var entity=prompt(addtext);
		if(entity){
			var XHR = new XHRConnection();
			XHR.appendData('add_entity',entity);
			XHR.setRefreshArea('list_entities');			
			XHR.sendAndLoad(CurrentPageName(), 'GET');
			ListDomainByEntity(entity);
		}
		
	}
	
	function AddDomainByEntity(entity_name){
		var addtext=document.getElementById('text_add_domain_entity').value
		var entity_domain=prompt(addtext);
		if(entity_domain){
			var XHR = new XHRConnection();
			XHR.appendData('add_domain_entity',entity_domain);
			XHR.appendData('entity_name',entity_name);			
			XHR.setRefreshArea('list_entities');			
			XHR.sendAndLoad(CurrentPageName(), 'GET');
			ListDomainByEntity(entity_name);

		}
				
	}


	function ListDomainByEntity(ou){
			var XHR = new XHRConnection();
			XHR.appendData('ListDomainByEntity',ou);
			XHR.setRefreshArea('domain_list');
			XHR.sendAndLoad(CurrentPageName(), 'GET');	
			}
	

		
var xedit_transport= function (obj) {
	tempvalue=obj.responseText;
	}		
		

	
var xdelete_transport_text= function (obj) {
	tempvalue=obj.responseText;
	}	
	
var alert_answer= function (obj) {
	var respons;
	respons=obj.responseText;
	respons=respons.replace(/(^\s*)|(\s*$)/g,''); 
	if (respons.length>0){
		alert(obj.responseText);
	}		
}
	function delete_transport(xdomain){
		var XHR = new XHRConnection();
		XHR.appendData('xdelete_transport_text',xdomain);
		XHR.sendAndLoad(CurrentPageName(), 'GET',xdelete_transport_text);
		if(confirm(tempvalue)){
			var XHR2 = new XHRConnection();
			XHR2.appendData('delete_transport_confirm',xdomain);
			XHR2.sendAndLoad(CurrentPageName(), 'GET');
			alert('document deleted');
			expand_domain(xdomain);
			}
		}
		
	
		
	function DelDomain(xdomain){
		var XHR = new XHRConnection();
		var tempvalue=document.getElementById("xdelete_domain_text").value;
		if(confirm(tempvalue)){
			var XHR = new XHRConnection();
			XHR.appendData('delete_domain_confirm',xdomain);
			XHR.setRefreshArea('domain_list');
			XHR.sendAndLoad(CurrentPageName(), 'GET');
			}
		
	}
		

		
		
	