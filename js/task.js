/**
 * @author touzeau
 */

var Working_page="system.tasks.settings.php";
var Winid;

function TasksAdd(taskName){
		if(!taskName){taskName='';}
		Winid=LoadWindows(650,530,Working_page,'TasksAdd=yes&taskName='+taskName);
		}
		
function CronAddArticaTasks(){
	Winid=LoadWindows(300,250,Working_page,'CronAddArticaTasks=yes');
	
}		
function TaskStripCarcTaskName(){
	var taskname=document.getElementById('taskname').value;
	taskname=taskname.replace(/\./g,"-");
	taskname=taskname.replace(/\s+/g,"-");
	document.getElementById('taskname').value=taskname;
}


function CronAddTime(e,fi){
	if (checkEnter(e)){
		if(fi.value.length>0){
			var FieldName=fi.name;
			var FieldDatas=fi.value;
	    	var taskname=document.getElementById('taskname').value;
			var XHR = new XHRConnection();
			XHR.appendData('FieldName',FieldName);
			XHR.appendData('FieldDatas',FieldDatas);
			XHR.appendData('CronAddTime',taskname);	
			XHR.sendAndLoad(Working_page, 'GET',x_parseform);
			ReloadForm(taskname);
			}
		}
	}
	
	
function SaveTaskSettings(){
	var taskname=document.getElementById('taskname').value;
	var TaskMAILTO=document.getElementById('TaskMAILTO').value;
	var taskUser=document.getElementById('taskUser').value;
	var TaskObservations=document.getElementById('TaskObservations').value;
	var TaskCommand=document.getElementById('TaskCommand').value;
	var XHR = new XHRConnection();
	XHR.appendData('TaskMAILTO',TaskMAILTO);
	XHR.appendData('taskUser',taskUser);
	XHR.appendData('TaskObservations',TaskObservations);		
	XHR.appendData('TaskCommand',TaskCommand);
	XHR.appendData('SaveTaskSettings',taskname);		
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	ReloadForm(taskname);	
	CronReloadMasterTable();
	}	
	
function TaskDelete(taskname){
	var XHR = new XHRConnection();
	XHR.appendData('CronTaskDelete',taskname);
	XHR.sendAndLoad(Working_page, 'GET');
	CronReloadMasterTable();
}

function CronReloadMasterTable(){
var XHR = new XHRConnection();
	document.getElementById('local_tasks').innerHTML='';
	XHR.appendData('CronReloadMasterTable','yes');	
	XHR.setRefreshArea('local_tasks');
	XHR.sendAndLoad(Working_page, 'GET');
}	
	
function CronAddTimeList(fi){
	if(fi.value.length>0){
			var FieldName=fi.name;
			var FieldDatas=fi.value;
	    	var taskname=document.getElementById('taskname').value;
			var XHR = new XHRConnection();
			XHR.appendData('FieldName',FieldName);
			XHR.appendData('FieldDatas',FieldDatas);
			XHR.appendData('CronAddTime',taskname);	
			XHR.sendAndLoad(Working_page, 'GET',x_parseform);
			ReloadForm(taskname);
			}
		}
		
function CronDeleteSchedule(TASK_TYPE,NUM){
	var taskname=document.getElementById('taskname').value;
	var XHR = new XHRConnection();
	XHR.appendData('FieldName',TASK_TYPE);
	XHR.appendData('index',NUM);
	XHR.appendData('CronDeleteSchedule',taskname);
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	ReloadForm(taskname);		
	}	
		
	
function ReloadForm(taskname){
	var XHR = new XHRConnection();
	XHR.appendData('TasksForm','yes');
	XHR.appendData('taskName',taskname);
	XHR.setRefreshArea('TaskDatas');
	XHR.sendAndLoad(Working_page, 'GET');
	}
function CronAddArticaTasksSelect(){
	var XHR = new XHRConnection();
	XHR.setRefreshArea('tasks_field_obs');
	var taskname=document.getElementById('articaTasks').value;
	XHR.appendData('CronAddArticaTasksSelect',taskname);
	XHR.sendAndLoad(Working_page, 'GET');
	}	
function CronAddArticaTasksSave(){
	var taskname=document.getElementById('articaTasks').value;
	var XHR = new XHRConnection();
	XHR.appendData('CronAddArticaTasksSave',taskname);	
	XHR.sendAndLoad(Working_page, 'GET',x_parseform);
	CronReloadMasterTable();	
}	
