<?php
$GLOBALS["FORCE"]=false;
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["posix_getuid"]=0;
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');


if($argv[1]=="--build"){build();die();}
if($argv[1]=="--javadirs"){checkJavaDirs();die();}



function build(){
	
	$ldap=new clladp();
	$sock=new sockets();
	$TomcatListenPort=$sock->GET_INFO($TomcatListenPort);
	$TomcatAdminName=$sock->GET_INFO("TomcatAdminName");
	$TomcatAdminPass=$sock->GET_INFO("TomcatAdminPass");	
	$unix=new unix();
	if(!is_numeric($TomcatListenPort)){$TomcatListenPort=8080;}
	
	if(($TomcatAdminName==null) OR ($TomcatAdminPass==null)) {
		$TomcatAdminName=$ldap->ldap_admin;
		$TomcatAdminPass=$ldap->ldap_password;
	}
	
	
	@mkdir('/opt/openemm/tomcat/conf',644,true);
	$f[]="<?xml version='1.0' encoding='utf-8'?>";
	$f[]="<tomcat-users>";
	$f[]="<role rolename=\"manager\"/>";
	$f[]="<role rolename=\"admin\"/>";
	$f[]="<user username=\"$TomcatAdminName\" password=\"$TomcatAdminPass\" roles=\"manager-gui,manager-script,manager-jmx,manager-status\"/>";
	$f[]="</tomcat-users>";
	$f[]="";
	@file_put_contents("/opt/openemm/tomcat/conf/tomcat-users.xml", @implode("\n", $f));
	echo "Starting......:  Tomcat server tomcat-users.xml done...\n";
	echo "Starting......:  Tomcat server listen on port $TomcatListenPort\n";
	
	
	unset($f);
	$f[]="<?xml version='1.0' encoding='utf-8'?>";
	$f[]="<!--";
	$f[]="  Licensed to the Apache Software Foundation (ASF) under one or more";
	$f[]="  contributor license agreements.  See the NOTICE file distributed with";
	$f[]="  this work for additional information regarding copyright ownership.";
	$f[]="  The ASF licenses this file to You under the Apache License, Version 2.0";
	$f[]="  (the \"License\"); you may not use this file except in compliance with";
	$f[]="  the License.  You may obtain a copy of the License at";
	$f[]="";
	$f[]="      http://www.apache.org/licenses/LICENSE-2.0";
	$f[]="";
	$f[]="  Unless required by applicable law or agreed to in writing, software";
	$f[]="  distributed under the License is distributed on an \"AS IS\" BASIS,";
	$f[]="  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.";
	$f[]="  See the License for the specific language governing permissions and";
	$f[]="  limitations under the License.";
	$f[]="-->";
	$f[]="<!-- Note:  A \"Server\" is not itself a \"Container\", so you may not";
	$f[]="     define subcomponents such as \"Valves\" at this level.";
	$f[]="     Documentation at /docs/config/server.html";
	$f[]=" -->";
	$f[]="<Server port=\"8005\" shutdown=\"SHUTDOWN\">";
	$f[]="  <!-- Security listener. Documentation at /docs/config/listeners.html";
	$f[]="  <Listener className=\"org.apache.catalina.security.SecurityListener\" />";
	$f[]="  -->";
	$f[]="  <!--APR library loader. Documentation at /docs/apr.html -->";
	$f[]="  <Listener className=\"org.apache.catalina.core.AprLifecycleListener\" SSLEngine=\"on\" />";
	$f[]="  <!--Initialize Jasper prior to webapps are loaded. Documentation at /docs/jasper-howto.html -->";
	$f[]="  <Listener className=\"org.apache.catalina.core.JasperListener\" />";
	$f[]="  <!-- Prevent memory leaks due to use of particular java/javax APIs-->";
	$f[]="  <Listener className=\"org.apache.catalina.core.JreMemoryLeakPreventionListener\" />";
	$f[]="  <Listener className=\"org.apache.catalina.mbeans.GlobalResourcesLifecycleListener\" />";
	$f[]="  <Listener className=\"org.apache.catalina.core.ThreadLocalLeakPreventionListener\" />";
	$f[]="";
	$f[]="  <!-- Global JNDI resources";
	$f[]="       Documentation at /docs/jndi-resources-howto.html";
	$f[]="  -->";
	$f[]="  <GlobalNamingResources>";
	$f[]="    <!-- Editable user database that can also be used by";
	$f[]="         UserDatabaseRealm to authenticate users";
	$f[]="    -->";
	$f[]="    <Resource name=\"UserDatabase\" auth=\"Container\"";
	$f[]="              type=\"org.apache.catalina.UserDatabase\"";
	$f[]="              description=\"User database that can be updated and saved\"";
	$f[]="              factory=\"org.apache.catalina.users.MemoryUserDatabaseFactory\"";
	$f[]="              pathname=\"conf/tomcat-users.xml\" />";
	$f[]="  </GlobalNamingResources>";
	$f[]="";
	$f[]="  <!-- A \"Service\" is a collection of one or more \"Connectors\" that share";
	$f[]="       a single \"Container\" Note:  A \"Service\" is not itself a \"Container\", ";
	$f[]="       so you may not define subcomponents such as \"Valves\" at this level.";
	$f[]="       Documentation at /docs/config/service.html";
	$f[]="   -->";
	$f[]="  <Service name=\"Catalina\">";
	$f[]="  ";
	$f[]="    <!--The connectors can use a shared executor, you can define one or more named thread pools-->";
	$f[]="    <!--";
	$f[]="    <Executor name=\"tomcatThreadPool\" namePrefix=\"catalina-exec-\" ";
	$f[]="        maxThreads=\"150\" minSpareThreads=\"4\"/>";
	$f[]="    -->";
	$f[]="    ";
	$f[]="    ";
	$f[]="    <!-- A \"Connector\" represents an endpoint by which requests are received";
	$f[]="         and responses are returned. Documentation at :";
	$f[]="         Java HTTP Connector: /docs/config/http.html (blocking & non-blocking)";
	$f[]="         Java AJP  Connector: /docs/config/ajp.html";
	$f[]="         APR (HTTP/AJP) Connector: /docs/apr.html";
	$f[]="         Define a non-SSL HTTP/1.1 Connector on port $TomcatListenPort";
	$f[]="    -->";
	$f[]="    <Connector port=\"$TomcatListenPort\" protocol=\"HTTP/1.1\" ";
	$f[]="               connectionTimeout=\"20000\" ";
	$f[]="               redirectPort=\"8443\" />";
	$f[]="    <!-- A \"Connector\" using the shared thread pool-->";
	$f[]="    <!--";
	$f[]="    <Connector executor=\"tomcatThreadPool\"";
	$f[]="               port=\"$TomcatListenPort\" protocol=\"HTTP/1.1\" ";
	$f[]="               connectionTimeout=\"20000\" ";
	$f[]="               redirectPort=\"8443\" />";
	$f[]="    -->           ";
	$f[]="    <!-- Define a SSL HTTP/1.1 Connector on port 8443";
	$f[]="         This connector uses the JSSE configuration, when using APR, the ";
	$f[]="         connector should be using the OpenSSL style configuration";
	$f[]="         described in the APR documentation -->";
	$f[]="    <!--";
	$f[]="    <Connector port=\"8443\" protocol=\"HTTP/1.1\" SSLEnabled=\"true\"";
	$f[]="               maxThreads=\"150\" scheme=\"https\" secure=\"true\"";
	$f[]="               clientAuth=\"false\" sslProtocol=\"TLS\" />";
	$f[]="    -->";
	$f[]="";
	$f[]="    <!-- Define an AJP 1.3 Connector on port 8009 -->";
	$f[]="    <Connector port=\"8009\" protocol=\"AJP/1.3\" redirectPort=\"8443\" />";
	$f[]="";
	$f[]="";
	$f[]="    <!-- An Engine represents the entry point (within Catalina) that processes";
	$f[]="         every request.  The Engine implementation for Tomcat stand alone";
	$f[]="         analyzes the HTTP headers included with the request, and passes them";
	$f[]="         on to the appropriate Host (virtual host).";
	$f[]="         Documentation at /docs/config/engine.html -->";
	$f[]="";
	$f[]="    <!-- You should set jvmRoute to support load-balancing via AJP ie :";
	$f[]="    <Engine name=\"Catalina\" defaultHost=\"localhost\" jvmRoute=\"jvm1\">";
	$f[]="    --> ";
	$f[]="    <Engine name=\"Catalina\" defaultHost=\"localhost\">";
	$f[]="";
	$f[]="      <!--For clustering, please take a look at documentation at:";
	$f[]="          /docs/cluster-howto.html  (simple how to)";
	$f[]="          /docs/config/cluster.html (reference documentation) -->";
	$f[]="      <!--";
	$f[]="      <Cluster className=\"org.apache.catalina.ha.tcp.SimpleTcpCluster\"/>";
	$f[]="      -->        ";
	$f[]="";
	$f[]="      <!-- Use the LockOutRealm to prevent attempts to guess user passwords";
	$f[]="           via a brute-force attack -->";
	$f[]="      <Realm className=\"org.apache.catalina.realm.LockOutRealm\">";
	$f[]="        <!-- This Realm uses the UserDatabase configured in the global JNDI";
	$f[]="             resources under the key \"UserDatabase\".  Any edits";
	$f[]="             that are performed against this UserDatabase are immediately";
	$f[]="             available for use by the Realm.  -->";
	$f[]="        <Realm className=\"org.apache.catalina.realm.UserDatabaseRealm\"";
	$f[]="               resourceName=\"UserDatabase\"/>";
	$f[]="      </Realm>";
	$f[]="";
	$f[]="      <Host name=\"localhost\"  appBase=\"webapps\"";
	$f[]="            unpackWARs=\"true\" autoDeploy=\"true\">";
	$f[]="";
	$f[]="        <!-- SingleSignOn valve, share authentication between web applications";
	$f[]="             Documentation at: /docs/config/valve.html -->";
	$f[]="        <!--";
	$f[]="        <Valve className=\"org.apache.catalina.authenticator.SingleSignOn\" />";
	$f[]="        -->";
	$f[]="";
	$f[]="        <!-- Access log processes all example.";
	$f[]="             Documentation at: /docs/config/valve.html";
	$f[]="             Note: The pattern used is equivalent to using pattern=\"common\" -->";
	$f[]="        <Valve className=\"org.apache.catalina.valves.AccessLogValve\" directory=\"logs\"  ";
	$f[]="               prefix=\"localhost_access_log.\" suffix=\".txt\"";
	$f[]="               pattern=\"%h %l %u %t &quot;%r&quot; %s %b\" resolveHosts=\"false\"/>";
	$f[]="";
	$f[]="      </Host>";
	$f[]="    </Engine>";
	$f[]="  </Service>";
	$f[]="</Server>";	
	@file_put_contents("/opt/openemm/tomcat/conf/server.xml", @implode("\n", $f));
	echo "Starting......:  Tomcat server server.xml done...\n";	
	
	$java=$unix->JAVA_HOME_GET();
	if(!is_dir($java)){
		echo "Starting......: Tomcat server JAVA_HOME not set try to find a good one...\n";
		checkJavaDirs();	
	}
	
	

}

function checkJavaDirs(){
	$unix=new unix();
	$dirs=$unix->dirdir("/opt/openemm");
	while (list ($index, $directory) = each ($dirs) ){
		$dirtmp=basename($directory);
		if(is_file("$directory/bin/java")){
			
			$vbin=getjavaversion("$directory/bin/java");
			echo "Starting......: Tomcat found $dirtmp $vbin\n";
			$jaws[$vbin]=$directory;
		}
			
	}
	if(!is_array($jaws)){
		echo "Starting......: Tomcat server unable to find java\n";
		return;
		
	}
	krsort($jaws);
	while (list ($index, $directory) = each ($jaws) ){
		$f[]=$directory;
	}
	
	if(is_dir($f[0])){
		echo "Starting......: Tomcat server set java to {$f[0]}\n";
		$unix->JAVA_HOME_SET($f[0]);
	}
	
}

function getjavaversion($path){
	
	exec("$path -version 2>&1",$results);
	while (list ($index, $directory) = each ($results) ){
		if(preg_match('#java version\s+"(.+?)"#', $directory,$re)){
			$v=$re[1];
			$v=str_replace(".", "", $v);
			$v=str_replace("_", "", $v);
			return intval($v);
		}
	}
	
}

