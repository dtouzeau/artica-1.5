/*
helo toto.com
mail from:toto@toto.com
rcpt to:sqfdlsdkqf@example.com
data

3

*/

#include <stdlib.h>
#include <unistd.h>
#include <stdio.h>
#include <mysql/mysql.h>
#include <sys/time.h>
#include <time.h>
#include <dotconf.h>
#include <string.h>
#include <limits.h>
#include <sys/types.h>
#include <pwd.h>
#include <errno.h>

int extern errno;
extern char **environ;
extern FILE* stdin;
extern FILE* stdout;
extern FILE* stderr;

/////////////////////////////////////////////////
// Config file reader stuff: using dotconf lib //
/////////////////////////////////////////////////
typedef struct{
	char* mysql_hostname;
	char* mysql_user;
	char* mysql_pass;
	char* mysql_db;
	char* mysql_table_smtp_logs;
	char* mysql_table_pop_access;
}mysqmail_config_t;
mysqmail_config_t mysqmail_config;

DOTCONF_CB(cb_mysql_hostname);
DOTCONF_CB(cb_mysql_user);
DOTCONF_CB(cb_mysql_pass);
DOTCONF_CB(cb_mysql_db);
DOTCONF_CB(cb_mysql_table_smtp_logs);
DOTCONF_CB(cb_mysql_table_pop_access);

static configoption_t options[] = {
	{"mysql_hostname", ARG_STR, cb_mysql_hostname, NULL, 0},
	{"mysql_user", ARG_STR, cb_mysql_user, NULL, 0},
	{"mysql_pass", ARG_STR, cb_mysql_pass, NULL, 0},
	{"mysql_db", ARG_STR, cb_mysql_db, NULL, 0},
	{"mysql_table_smtp_logs", ARG_STR, cb_mysql_table_smtp_logs, NULL, 0},
	{"mysql_table_pop_access", ARG_STR, cb_mysql_table_pop_access, NULL, 0},
	LAST_OPTION
};

DOTCONF_CB(cb_mysql_hostname){
	mysqmail_config.mysql_hostname = (char*)malloc(strlen(cmd->data.str)+1);
	strcpy(mysqmail_config.mysql_hostname,cmd->data.str);
	return NULL;
}
DOTCONF_CB(cb_mysql_user){
	mysqmail_config.mysql_user = (char*)malloc(strlen(cmd->data.str)+1);
	strcpy(mysqmail_config.mysql_user,cmd->data.str);
	return NULL;
}
DOTCONF_CB(cb_mysql_pass){
	mysqmail_config.mysql_pass = (char*)malloc(strlen(cmd->data.str)+1);
	strcpy(mysqmail_config.mysql_pass,cmd->data.str);
	return NULL;
}
DOTCONF_CB(cb_mysql_db){
	mysqmail_config.mysql_db = (char*)malloc(strlen(cmd->data.str)+1);
	strcpy(mysqmail_config.mysql_db,cmd->data.str);
	return NULL;
}
DOTCONF_CB(cb_mysql_table_smtp_logs){
	mysqmail_config.mysql_table_smtp_logs = (char*)malloc(strlen(cmd->data.str)+1);
	strcpy(mysqmail_config.mysql_table_smtp_logs,cmd->data.str);
	return NULL;
}
DOTCONF_CB(cb_mysql_table_pop_access){
	mysqmail_config.mysql_table_pop_access = (char*)malloc(strlen(cmd->data.str)+1);
	strcpy(mysqmail_config.mysql_table_pop_access,cmd->data.str);
	return NULL;
}
int read_config_file(){
	configfile_t *configfile;
	configfile = dotconf_create("/etc/mysqmail.conf", options, 0, CASE_INSENSITIVE);
	if (dotconf_command_loop(configfile) == 0){
		fprintf(stderr, "Error reading config file\n");
		exit(2);
	}
	dotconf_cleanup(configfile);
	return 0;
}
/////////////////////////
// Mysql connect stuff //
/////////////////////////
MYSQL mysql,*sock;
MYSQL_RES *res;
int do_ze_mysql_connect(){
	char query[256]="";
	mysql_init(&mysql);
	if (!(sock = mysql_real_connect(&mysql,mysqmail_config.mysql_hostname,
				mysqmail_config.mysql_user,mysqmail_config.mysql_pass,mysqmail_config.mysql_db,0,NULL,0))){
		fprintf(stderr,"Couldn't connect to engine!\n%s\n\n",mysql_error(&mysql));
		exit(2);
	}
	sprintf(query,"DELETE FROM %s WHERE newmsg_id=NULL;",
			mysqmail_config.mysql_table_smtp_logs);
	if(mysql_query(sock,query)){
		fprintf(stdout,"Query: \"%s\" failed ! %s\n",query,mysql_error(sock));
	}
	fprintf(stderr,"qmail-logger connected to MySQL !\n");
	return 0;
}

///////////////////////////////////////////////////////////////////
// A line of log must be parsed and sent to correct mysql record //
///////////////////////////////////////////////////////////////////
#define MAX_LOG_WORDS 32
#define EMAIL_ADDR_SIZE 256
#define MAX_QUERY_SIZE 1024

typedef struct{
	long tv_sec;
	long tv_usec;
}timeval_t;

int log_a_line(char* logline){
/*	Log lines sent by postfix should have the following format
	Mar 16 00:00:08 <hostname> postfix/smtpd[<pid>]: connect from <hostname>[<ip>] 
	Mar 16 00:00:09 <hostname> postfix/smtpd[<pid>]: NOQUEUE: reject: RCPT from <hostname>[<ip>]
	Mar 16 00:00:11 <hostname> postfix/smtpd[<pid>]: disconnect from <hostname>[<ip>]
	Mar 16 00:01:25 <hostname> postfix/smtpd[<pid>]: connect from <hostname>[<ip>]
	Mar 16 00:01:28 <hostname> postfix/smtpd[<pid>]: <queue id>: client=<hostname>[<ip>]
	Mar 16 00:01:29 <hostname> postfix/cleanup[<pid>]: <queue id>: message-id=
	Mar 16 00:01:29 <hostname> postfix/qmgr[<pid>]: <queue id>: from=<email address>, size=<message size>, nrcpt=1 (queue active) 
	Mar 16 00:01:29 <hostname> postfix/local[<pid>]: <queue id>: to=<email address>, relay=local, delay=4, status=sent (delivered to command: /usr/bin/procmail) 
	Mar 16 00:01:29 <hostname> postfix/qmgr[<pid>]: <queue id>: removed 
	Mar 16 00:01:29 <hostname> postfix/smtpd[<pid>]: disconnect from <hostname>[<ip>]
	
*/
	char* words[MAX_LOG_WORDS+1];
	char query[MAX_QUERY_SIZE+1]="";
	char* delivery_id;
	char* sender_user;
	char* sender_domain;
	char* delivery_user;
	char* delivery_domain;
	char* msg_id;
	char* cur_p;
	char* tmp;
	char* new_delivery_id;
	char* relay;
	char* bytes;
	char* status;
	int num_words=0;
	char* smtp_logs;
	int i;

	struct timeval tv;

	//fprintf(stdout,"%s\n",logline);

	smtp_logs = mysqmail_config.mysql_table_smtp_logs;

	// Tokenise the line in words
	cur_p = strtok(logline," ");
	while( (words[num_words++] = cur_p) != NULL){
		//fprintf(stdout,"Word %d: %s\n",num_words-1,cur_p);
		cur_p = strtok(NULL," ");
	}
	num_words--;

	//sprintf(query,"");

	//----new ID appear on lines like the following:

	// 0 1 2 3 postfix/pickup[17425]: 04D341007DA9
	// 0 1 2 3 postfix/smtpd[17495]: E8A8E1007DA9: client=
	// 0 1 2 3 postfix/smtpd[2919]: E0AB117E26: client=
	// 0 1 2 3 postfix/smtpd[6333]: A4A4A17E2B: client=

	//----new from and size details appear like this:

	// 0 1 2 3 postfix/qmgr[17116]: 04D341007DA9: from=<root@mx.new.tusker.net>, size=286, nrcpt=1 (queue active)
	// 0 1 2 3 postfix/qmgr[17116]: E8A8E1007DA9: from=<tusker@ordo.cable.nu>, size=569, nrcpt=1 (queue active)
	// 0 1 2 3 postfix/qmgr[1108]: E0AB117E26: from=<>, size=8507, nrcpt=1 (queue active)
	// 0 1 2 3 postfix/qmgr[1108]: A4A4A17E2B: from=<sans@sans.org>, size=25573, nrcpt=1 (queue active)

	//----new to details appear like this:

	// 0 1 2 3 postfix/virtual[17491]: 04D341007DA9: to=<tester@new.tusker.net>, relay=virtual, delay=0, status=sent (maildir)
	// 0 1 2 3 postfix/virtual[17491]: E8A8E1007DA9: to=<tester@new.tusker.net>, relay=virtual, delay=0, status=sent (maildir)
	// 0 1 2 3 postfix/smtp[3191]: E0AB117E26: to=<adam@fission.tusker.net>, orig_to=<zboszor@ali.as>, relay=127.0.0.1[127.0.0.1], delay=2, status=sent (250 2.6.0 Ok, id=03278-02, from MTA: 250 Ok: queued as F0BF617E30)

	//----reject message
	// 0 1 2 3 postfix/smtpd[2410]: A1CE861A83: reject: RCPT from unknown[218.246.34.68]: 557 <reason>
	//----bounce message
	// 0 1 2 3 postfix/local[6334]: A4A4A17E2B: to=<dee@fission.tusker.net>, relay=local, delay=1, status=bounced (unknown user: "dee")

	if (!(num_words > 4 && strstr(words[4], "postfix")) && !(num_words > 4 && strstr(words[4], "vmailer"))){
		//this isn't a postfix line!
		return 0;
	}

	if ((num_words > 6 && strstr(words[6], "from=")) || (num_words > 7 && strstr(words[7], "from="))){
		int offset = 0;
		sender_user = "";
		sender_domain = "";
		// handle initial email
		// we need to handle from in position 5 also (local emails)
		if (strstr(words[7], "from=")) offset = 1;
			
		fprintf(stdout,"This is from line for %s\n", words[5]);
		delivery_id = words[5];
		delivery_id[strlen(delivery_id)-1] = 0;	// Remove the :
		sender_user = words[6 + offset];
		if (strstr(sender_user,"from=<") == NULL)
			return 1;
		if (strstr(sender_user,"<>") != NULL){
			// we have a null sender, no worries... 
			sender_user = "null";
			sender_domain = "null";
		} else {
			sender_user = strstr(sender_user, "<"); //remove the from=
			sender_user++;
			//fprintf(stdout, "sender_user = %s\n", sender_user);
			sender_domain = strstr(sender_user,"@");
			if (sender_domain != NULL)
			{
				//fprintf(stdout, "sender_domain=%s", sender_domain);
				sender_user[strlen(sender_user) - strlen(sender_domain)] = 0; // strip off the users domain
				sender_domain++;
				sender_domain[strlen(sender_domain)-1] = 0; // Remove the > (or ,)
				while (strlen(sender_domain) > 1 && (sender_domain[strlen(sender_domain)-1] == '>' || sender_domain[strlen(sender_domain)-1] == ','))
				{
					sender_domain[strlen(sender_domain)-1] = 0; // Remove the >
				}
			} else {
				sender_user[strlen(sender_user) - 1] = 0; // strip off the trailing >
				sender_domain = "null";
			}
		}
		//if we don't have an offset, then we have a size :)
		bytes = 0;
		if (offset == 0)
		{
			bytes = words[7];
			//shift away the size=
			if (strstr(bytes, "size=") != NULL)
			{
				bytes += 5;
			}
			if (bytes[strlen(bytes)-1] == ',')
			{
				bytes[strlen(bytes)-1] = 0; // strip the last ,
			}
			fprintf(stdout,"Bytes used = %s\n", bytes);
		}
		fprintf(stdout,"Delivery id = %s, Sender User = %s, Sender Domain = %s\n", delivery_id, sender_user, sender_domain);
		if (bytes != 0){
			sprintf(query,"UPDATE %s SET sender_user='%s', sender_domain='%s', bytes='%s' WHERE delivery_id_text='%s';", smtp_logs, sender_user, sender_domain, bytes,  delivery_id);
		} else {
			sprintf(query,"UPDATE %s SET sender_user='%s', sender_domain='%s' WHERE delivery_id_text='%s';", smtp_logs, sender_user, sender_domain, delivery_id);
		}
	} else if (num_words > 6 && strstr(words[6],"client=")){
		char *ip_address = strstr(words[6], "[");
		//we are going to keep this client domain just in case the sender didn't have a "from" field
		delivery_id = words[5];
                delivery_id[strlen(delivery_id)-1] = 0; // Remove the :
		delivery_domain = words[6];
		delivery_domain = strstr(delivery_domain, "="); // remove the 'client'
		delivery_domain++; // remove the =
		delivery_domain[strlen(delivery_domain) - strlen(ip_address)] = 0; // remove the IP address brackets 
		//sprintf(query,"UPDATE %s SET delivery_domain='%s' where delivery_id_text='%s';", smtp_logs, delivery_domain, delivery_id);
		//don't worry about this for now, we are deleting this row anyway... 
	} else if (num_words > 6 && strstr(words[6],"message-id=")){
		//message-id will link all locally delivered mail
		delivery_id = words[5];
                delivery_id[strlen(delivery_id)-1] = 0; // Remove the :
		msg_id = words[6];
		msg_id = strstr(msg_id, "=");
		msg_id++; //shift past the =
		fprintf(stdout,"New message id [%s] with queue ID [%s]\n",  msg_id, delivery_id);
		gettimeofday(&tv,NULL);
		sprintf(query,"INSERT INTO %s (time_stamp,msg_id_text, delivery_id_text) VALUES('%ld', '%s', '%s')", smtp_logs, tv.tv_sec,  msg_id, delivery_id);
	} else if (num_words > 6 && strstr(words[6],"to=")){
		int requeued;
		int local;
		int virtual;
		int not_delivered;

		//update email with send status
		//first get the delivery id
		delivery_id = words[5];
                delivery_id[strlen(delivery_id)-1] = 0; // Remove the :
		delivery_user = words[6];
		if (strstr(delivery_user,"to=<") == NULL)
			return 1;
		delivery_user = strstr(delivery_user, "<"); //remove the from=
		delivery_user++; //shift after the <
		status = "";
		if (strstr(words[9], "status=") != NULL)
		{
			status = words[9];
		}
		if (strstr(words[10], "status=") != NULL)
		{
			status = words[10];
		}
		//shift away the status=
		if (strstr(status, "status=") != NULL)
		{
			status += 7;
		}
		while (status[strlen(status)-1] == ',' || status[strlen(status)-1] == ')')
		{
			status[strlen(status)-1] = 0; // strip the , or ) if it's there
		}
		requeued = 0;

		//see if we have a orig_to field, and then we need to see what we queued as (for new delivery rule internally)
		if (num_words > 7 && (tmp = words[7]) != NULL  && strstr(tmp, "orig_to=") != NULL){
			int q_found;
			int as_found;
			new_delivery_id = NULL;
			tmp = strstr(tmp, "<"); //remove the orig_to=
			tmp++; //shift to after the <
			while (tmp[strlen(tmp)-1] == '>' || tmp[strlen(tmp)-1] == ',')
			{
				tmp[strlen(tmp)-1] = 0; // Remove the > or ,
			}
			fprintf(stdout,"This message was originally to %s\n", tmp);
			// only if the orig_to has a domain attached should we actually update this, otherwise, use the default domain of the mailserver
			if (strstr(tmp, "@")){
				delivery_user = tmp;
			}
			// need to go and find "queued as" || "forwarded as"
			q_found = -1;
			as_found = -1;
			for(i=0; i<num_words; i++)
			{
				if (strstr(words[i], "queued") || strstr(words[i], "forwarded")){
					q_found = i;
				} else if (q_found != -1 && strstr(words[i], "as")){
					as_found = i;
				} else if (q_found != -1 && as_found != -1){
					new_delivery_id = words[i];
					new_delivery_id[strlen(new_delivery_id)-1] = 0; // Remove the trailing )
					requeued = 1;
					break;
				}
			}
			if (new_delivery_id != NULL && strlen(new_delivery_id) > 0){
				fprintf(stdout, "This message is requeued as %s\n", new_delivery_id);
				//reset the delivery_id to the correct one
			}
		}
		// process the delivery domain after we have checked the "orig_to"
		delivery_domain = strstr(delivery_user,"@");
		if (delivery_domain != NULL)
		{
			delivery_user[strlen(delivery_user) - strlen(delivery_domain)] = 0; //strip off the users domain
			delivery_domain++;
			while (delivery_domain[strlen(delivery_domain)-1] == '>' || delivery_domain[strlen(delivery_domain)-1] == ',')
			{
				delivery_domain[strlen(delivery_domain)-1] = 0; // Remove the >
			}
		}
		//now check to see if we have relayed this locally, or remotely :)
		local = 0;
		virtual = 0;
		not_delivered = 0;
		if (num_words > 8 && (relay = words[8]) != NULL && strstr(relay, "relay=") != NULL)
		{
			relay = strstr(relay, "=");
			relay++; //shift past the =
			while (relay[strlen(relay)-1] == ',' || relay[strlen(relay)-1] == '('){
				relay[strlen(relay)-1] = 0; // strip off , or (
			}
			if (strlen(relay) == 5 && strstr(relay, "local") != NULL){
				local = 1;
			} else if (strstr(relay, "127.0.0.1")){
				local = 1;
			} else if (strlen(relay) == 7 && strstr(relay, "virtual")){
				local = 1;
				virtual = 1;
			} else if (strlen(relay) == 4 && strstr(relay, "none")){
				local = 0;
				not_delivered = 1;
			} else {
				local = 0;
			}
			if (local) fprintf(stdout, "I'm treating %s as a local address\n", relay);
			if (!local) fprintf(stdout, "I'm treating %s as a remote address\n", relay);
			if (virtual) fprintf(stdout, " and I'm a virtual user!\n");
			if (not_delivered) fprintf(stdout, " but I'm a not delivered yet... error?!\n");
		}

		//check our delivery status, so we can update the table
		if (strstr(status, "sent")){
			status = "yes";
		} else {
			status = "no";
		}

		if (local && requeued){
			//need to delete this entry since we are being requeued under a different local message_id
			sprintf(query,"DELETE FROM %s WHERE delivery_id_text='%s'", smtp_logs, delivery_id);
		} else {
			//don't issue this update
			//if (delivery_domain == 0 || strstr(delivery_domain, "null")){
				//sprintf(query,"UPDATE %s SET delivery_user='%s', delivery_success='%s' WHERE delivery_id_text='%s';", smtp_logs, delivery_user, status, delivery_id);
			//} else {
			//default query... 	
			sprintf(query,"UPDATE %s SET delivery_user='%s', delivery_domain='%s', delivery_success='%s' WHERE delivery_id_text='%s';", smtp_logs, delivery_user, delivery_domain, status, delivery_id);
			//}
		}
		fprintf(stdout,"This is to line for %s\n", words[5]);
		fprintf(stdout,"Delivery id = %s, Delivery User = %s, Delivery Domain = %s\n",  delivery_id, delivery_user, delivery_domain);
		fprintf(stdout,"Send status = %s\n", status);
	} else if (num_words > 6 && strstr(words[6],"reject:")){
		//update email with reject status
		//first get the delivery id
		delivery_id = words[5];
                delivery_id[strlen(delivery_id)-1] = 0; // Remove the :

		fprintf(stdout,"This is reject line for %s\n", words[5]);
		sprintf(query,"UPDATE %s SET delivery_success='%s' WHERE delivery_id_text='%s';", smtp_logs, "no", delivery_id);
	// Message not recognised: send to next piped logger (syslog?)
	} else {
		for(i=0;i<num_words;i++){
			if(i!=0)	fprintf(stdout," ");
			fprintf(stdout,"%s",words[i]);
		}
		fprintf(stdout,"\n");
	}
	fprintf(stdout,"Query: \"%s\" !\n",query);
	// Issue the query then return from function.
	if(strlen(query)!=0){
		if(mysql_query(sock,query)){
			fprintf(stdout,"Query: \"%s\" failed ! %s\n",query,mysql_error(sock));
		}
	}
	return 0;

	//end of postfix stuff
	//start of qmail stuff

	// new msg MSG_NUMBER
	if(!strcmp(words[0],"new")){
/*		sprintf(query,"INSERT INTO %s (newmsg_id) VALUES('%s')",
								smtp_logs,				words[2]);
	// info msg MSG_NUMBER: bytes NUMBER from <SENDER@DOMAIN.COM> qp NUM uid 64011
*/	}else if(!strcmp(words[0],"info") && !strcmp(words[1],"msg")){
		msg_id = words[2];
		msg_id[ (strlen(msg_id)-1) ] = 0;
		sender_user = words[6];

		// Try to detect bounced or double-bounced messages by sender addr (don't know if it's the good way...)
		if(!strcmp(sender_user,"<>") || !strcmp(sender_user,"<#@[]>")){
			sprintf(query,"UPDATE %s SET bytes='%s',newmsg_id='%s' WHERE bounce_qp='%s';",
							smtp_logs,words[4],msg_id,words[8]);
		}else{
			sender_user++;
			sender_domain = strstr(sender_user,"@");
			if(sender_domain != NULL){
				*sender_domain++ = 0;
				sender_domain[ (strlen(sender_domain)-1) ] = 0;
				gettimeofday(&tv,NULL);
				sprintf(query,"INSERT INTO %s (time_stamp,newmsg_id,bytes,sender_user,sender_domain) VALUES('%ld','%s','%s','%s','%s')",
									smtp_logs,tv.tv_sec,words[2],words[4],sender_user,sender_domain);
			}
		}
	// starting delivery D_NUMBER: msg MSG_NUMBER to local domain-com-user@domain.com
	// starting delivery D_NUMBER: msg MSG_NUMBER to remote user@domain.com
	}else if(!strcmp(words[0],"starting") && !strcmp(words[1],"delivery")){
		delivery_id = words[2];
		delivery_id[strlen(delivery_id)-1] = 0;	// Remove the :
		delivery_user = words[7];
		delivery_domain = strstr(delivery_user,"@");	// Separate domain & user
		*delivery_domain++ = 0;
		if(!strcmp(words[6],"local")){
			// If to local, virtual domains does domain-com-user@domain.com
			// so we have to remove domain-com- from the username...
			if(strlen(delivery_user)>strlen(delivery_domain)){
				char tmp2[256];
				char* c__p;
				strncpy(tmp2,delivery_domain,254);
				tmp2[254]=0;
				c__p = &tmp2[0];
				while(*c__p){
					if(*c__p == '.')	*c__p = '-';
					c__p++;
				}
				*c__p++ = '-';
				*c__p++ = 0;
				if(!strncmp(delivery_user,tmp2,strlen(tmp2))){
					delivery_user += strlen(tmp2);
				}
			}
			sprintf(query,"UPDATE %s SET delivery_id='%s',delivery_user='%s',delivery_domain='%s' WHERE newmsg_id='%s';",
			smtp_logs,delivery_id,delivery_user,delivery_domain,words[4]);
		}else{
			sprintf(query,"UPDATE %s SET delivery_id='%s',delivery_user='%s',delivery_domain='%s' WHERE newmsg_id='%s';",
			smtp_logs,delivery_id,delivery_user,delivery_domain,words[4]);
		}
	// delivery D_NUMBER:
	}else if(!strcmp(words[0],"delivery")){
		delivery_id = words[1];
		delivery_id[strlen(delivery_id)-1] = 0;
		// delivery D_NUMBER: success:
		if(!strcmp(words[2],"success:")){
			sprintf(query,"UPDATE %s SET delivery_success='yes',delivery_id=NULL WHERE delivery_id='%s';",
							smtp_logs,delivery_id);
		// delivery D_NUMBER: failure:
		}else if(!strcmp(words[2],"failure:")){
		// delivery D_NUMBER: deferral:
		}else if(!strcmp(words[2],"deferral:")){
		}else{
		}
	// bounce msg 236400 qp 16889
	}else if(!strcmp(words[0],"bounce")){
		sprintf(query,"UPDATE %s SET bounce_qp='%s' WHERE newmsg_id='%s';",
							smtp_logs,words[4],words[2]);
	// triple bounce: discarding bounce/MSG_NUMBER
	}else if(!strcmp(words[0],"triple")){
		tmp = words[3];
		tmp += strlen("bounce/");
		sprintf(query,"DELETE FROM %s WHERE newmsg_id='%s';",
						smtp_logs,tmp);
	// end msg MSG_NUMBER
	}else if(!strcmp(words[0],"end")){
		sprintf(query,"UPDATE %s SET newmsg_id=NULL WHERE newmsg_id='%s';",
							smtp_logs,words[2]);
	// Message not recognised: send to next piped logger (syslog?)
	}else{
		for(i=0;i<num_words;i++){
			if(i!=0)	fprintf(stdout," ");
			fprintf(stdout,"%s",words[i]);
		}
		fprintf(stdout,"\n");
	}

	fprintf(stdout,"Query: \"%s\" !\n",query);
	// Issue the query return from function.
	if(strlen(query)!=0){
		if(mysql_query(sock,query)){
			fprintf(stdout,"Query: \"%s\" failed ! %s\n",query,mysql_error(sock));
		}
	}
	return 0;
}

// This read from stdin until EOF and detect whenever a line
// ends (whenever a \n occure).
#define MAX_LOG_LINE 1024
int log_all_lines(){
	char logline[MAX_LOG_LINE+1];
	int r;
	char c;

//	for(r=0;r<MAX_LOG_LINE+1;r++)	logline[r] = 0;
	r=0;
	if (feof(stdin) || ferror(stdin))
	{
		printf("Oops, we lost stdin...");
		exit(1);
	}
	while(!feof(stdin) && !ferror(stdin) && (c=getchar()) ){
//		if(r==MAX_LOG_LINE)	exit(2);
		if(c =='\n' || r==MAX_LOG_LINE-1){
			logline[r++] = 0;
			if(r > 1)	log_a_line(logline);
			r=0;
		}else{
			logline[r++] = c;
		}
	}
	return 0;
}

int main(int argc, char **argv){
	read_config_file();
	do_ze_mysql_connect();
	log_all_lines();
	mysql_close(sock);
	return 0;
}
