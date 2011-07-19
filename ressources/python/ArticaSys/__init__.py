import MySQLdb
import os
import logging, syslog
import time
import datetime
import hashlib
import ldap

def GET_INFO(key):
    targetFile="/etc/artica-postfix/settings/Mysql/"+key
    linestring="";
    if os.path.exists(targetFile):
        linestring = open(targetFile, 'r').read()
        linestring=linestring.strip()
    return linestring
  

def GET_LDAP(key):
    targetFile=" /etc/artica-postfix/ldap_settings/"+key
    linestring="";
    if os.path.exists(targetFile):
        linestring = open(targetFile, 'r').read()
        linestring=linestring.strip()
    return linestring
   
    
def GET_MYSQL(key):
    targetFile="/etc/artica-postfix/settings/Mysql/"+key;
    line="";
    if os.path.exists(targetFile):
        line = open(targetFile, 'r').read()
        line=line.strip()
        
    if key == "mysql_port":
         if not line:
             return 389
             
    return line   

def QUERY_SQL(sql, database):
    server=GET_MYSQL("mysql_server");
    port=GET_MYSQL("mysql_port");
    username=GET_MYSQL("database_admin");
    password=GET_MYSQL("database_password");
    try:
        conn = MySQLdb.connect(server,username,password, database)
    except MySQLdb.OperationalError, message:
        events("QUERY_SQL: "+str(message), "ArticaSys")
        errorcode = str(message) 
        print(errorcode)
        return
        
    curs = conn.cursor (MySQLdb.cursors.DictCursor)
    result=curs.execute(sql)
    result=curs.fetchall()
    conn.close ()
    return result;   
  

def QUERY_LDAP():
    server_string=GET_LDAP("server")
    port_string=GET_LDAP("port")
    if server_string is "":
        server_string="127.0.0.1"
    
    if port_string is "":
        port_string="389"
      
    
    server = "ldap://"+server_string+':'+port_string
    print "QUERY_LDAP():: INIT "+str(server)  
     
    try:
        l = ldap.initialize(server)
    except ldap.LDAPError, errorMsg:
        print "QUERY_LDAP():: LDAP ERROR "+str(errorMsg)
        return None
    
    
    
def syslog_mail(text):
    syslog.syslog(syslog.LOG_MAIL, text)
    syslog.closelog()
    
def strToMd5(text):
    return hashlib.md5(text ).hexdigest()
    
def writefile(path, datas):
    syslog_mail("open "+path)
    file = open(path, 'w')
    file.write(datas)
    file.close()


def events(text, service):
    now = datetime.datetime.now()
    mtime=now.strftime("%Y-%m-%d %H:%M:%S")
    LOG_FILENAME = '/var/log/artica-postfix/'+service+'.log'
    logging.basicConfig(filename=LOG_FILENAME,level=logging.INFO)    
    logging.info(mtime+' '+text)
