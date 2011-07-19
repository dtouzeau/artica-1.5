/*
  * mailspy.c - hacked milter filter to do wierd stuff to mail -- log sender,
  * recipients, size, attachment names, subject (to logfile)
  *
  * Copyright (c) 2001, 2002 Andrew McGill and Leading Edge Business Solutions
  * (South Africa).  
  *
  * Mailspy is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  */
#include <string.h>
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <ctype.h>
#include <stdarg.h>
#include "libmilter/mfapi.h"
#include <time.h>
#include <pthread.h>
#include <signal.h>
#include <sys/types.h> /* getpwnam */
#include <pwd.h>
#include <grp.h>

#define EX_USAGE 64
#define EX_UNAVAILABLE 69
#define ATTPREFIX0  "\tfile="
#define ATTPREFIX   "\tfile="
#define RCPTPREFIX0 "\tto="
#define RCPTPREFIX  "\tto="

#define VERSION     "0.0"

/* We don't have autoconf, so hack it right here */
#define HAVE_INITGROUPS
#define HAVE_SETGROUPS

FILE *headerlogfile;
FILE *logfile;
char *logfilename;
char *headerlogfilename;
char *processpipeto;
char *mailspyuser = "mailspy";
int new_logfile = 1;

// We don't want multiple threads interferring during output logging ..
pthread_mutex_t mlfi_outputlock = PTHREAD_MUTEX_INITIALIZER;

// Open or reopen the log file
void mlfi_openlogfile(void)
{
	/* Just once */
	if (!new_logfile) return;
	new_logfile = 0;

	// fprintf(stderr,"Logging to %s\n",logfilename);
	if (logfile) {
		fclose(logfile);
		logfile = NULL;
	}
	if (logfilename) {
		logfile = fopen(logfilename,"a");
		if (!logfile) {
			perror(logfilename);
			exit(4);
		}
	}

	// FIXME: Cloned code ...
	if (headerlogfile) {
		fclose(headerlogfile);
		headerlogfile = NULL;
	}
	if (headerlogfilename) {
		headerlogfile = fopen(headerlogfilename,"a");
		if (!headerlogfile) {
			perror(headerlogfilename);
			exit(4);
		}
	}
}

// Persistent information about a connection
struct MLFI_PRIV {
	int length;		// where we add data
	char subject[80];
	char envfrom[80];
	int rcptcount;
	char rcptto[1024];
	int recipients;
	int bodylen;
	int filecount;
	char filenames[1024];
	char headers[8192];
	FILE *pipeto;		// popen
};

sfsistat mlfi_closepipe(SMFICTX *ctx)
{
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	if (priv && priv->pipeto) {
		pclose(priv->pipeto);
		priv->pipeto=NULL;
	}
	return SMFIS_CONTINUE;
}


// Append something to what we have to say ...
int mlfi_snprintcat(char *dest, int destlen, char *format, ...)
{
	int avail,n;
	va_list ap;

	n = strlen(dest);
	avail = (destlen-n)-1;
	va_start(ap, format);
	n = vsnprintf (dest+n, avail, format, ap);
	va_end(ap);
	return n;
}

sfsistat mlfi_body(SMFICTX *ctx, u_char *bodyp, size_t bodylen)
{
	/* check body block for vbs data */
	// smfi_setreply(ctx, "554", "5.6.1", m);
	char *p, *q, *r, saved, eof;
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  

	/* output body block to log process */
	if (priv->pipeto) 
	if (fwrite(bodyp, bodylen, 1, priv->pipeto) <= 0) {
		/* how to bomb if write failed: */
		/* (void) mlfi_cleanup(ctx, false);
		return SMFIS_TEMPFAIL; */
	}

	priv->bodylen += bodylen;
	// FIXME?  A file which happens to exist across a 64k boundary will not
	// FIXME?  be logged :)
	if (!bodylen) return SMFIS_CONTINUE;
	eof = bodyp[bodylen-1];
	bodyp[bodylen-1] = 0;  // I sure hope this memory is not threaded :)
	// FIXME!! I want the body to be null-terminated!
	for (p = (char*)bodyp; p && (p-(char*)bodyp) <bodylen && (p = strstr(p, "Content-Type:")); p++) {
		if (( q = strstr(p, "name=\"") )) {
			q+=6;
			if (( r=strpbrk(q,"\"\n") )) {
				saved = *r; // temporary null termination ..
				*r=0;
				mlfi_snprintcat(priv->filenames, sizeof(priv->filenames), "%s%s",
					(priv->filecount==0 ? ATTPREFIX0 : ATTPREFIX), q);
				*r=saved;
				p=r; // Avoid finding the same name many times
				priv->filecount++;
			}
		}
	}
	bodyp[bodylen-1] = eof;
	return SMFIS_CONTINUE;
}

// MAIL FROM: spammer@spam.net
sfsistat mlfi_envfrom( SMFICTX * ctx, char ** argv )
{
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	mlfi_closepipe(ctx);  // Do any cleanup ...
	memset(priv,0,sizeof(*priv));
	strncpy(priv->envfrom,argv[0],sizeof(priv->envfrom)-1);
	if (processpipeto) {
		priv->pipeto = popen(processpipeto,"w");
		// Note: if process fails, pipeto=NULL
	}
	if (priv->pipeto) { // Here's a place to put in an envelope header 
		char datestring[48];
		time_t t;
		struct tm *tm;
		char *strftimeformat = "%a %b %d %H:%M:%S %Y"; 
		char *fromstart, *fromend,zapped;

		fromstart = index(argv[0],'<');
		if (!fromstart) fromstart = argv[0]; else fromstart++;
		fromend = rindex(argv[0],'>');
		if (!fromend || fromend < fromstart) {
			fromend = argv[0] + strlen(argv[0]);
		}
		time(&t);
		tm = gmtime(&t);
		strftime(datestring,sizeof(datestring)-1, strftimeformat, tm);
		zapped = *fromend;
		*fromend=0;
		fprintf(priv->pipeto, "From %s %s\n", fromstart,
			datestring);
		*fromend = zapped;
	}
	return SMFIS_CONTINUE;
}

// RCPT TO: victim@victim.org
sfsistat mlfi_envrcpt( SMFICTX * ctx, char ** argv )
{
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	if (strstr(priv->rcptto, argv[0])==NULL) {
		mlfi_snprintcat(priv->rcptto, sizeof(priv->rcptto), "%s%s",
			(priv->rcptcount==0 ? RCPTPREFIX0 : RCPTPREFIX), argv[0] );
		priv->rcptcount++;
	}
	return SMFIS_CONTINUE;
}


// Pull out subject line from headers
sfsistat mlfi_header(SMFICTX *ctx, char *headerf, char *headerv)
{
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	char *p;
	
	if (priv->pipeto) {
		/* write the header to the log file */
		fprintf(priv->pipeto, "%s: %s\n", headerf, headerv);
	}
	// Grab the subject header and clean it up a little
	if (strcasecmp(headerf,"subject")==0) {
		// sure hope we can write the the header value ...
		strncpy(priv->subject,headerv,sizeof(priv->subject)-1);
		for (p=priv->subject; *p; p++) {
			// blank out control characters
			if (*p=='\t' || *p=='\n' || *p=='\r') { 
				*p=' ';
			}
		}
	}
	if (headerlogfile) {
		if (headerf && headerv) {
			mlfi_snprintcat(priv->headers, sizeof(priv->headers),
				"%s: %s\n",headerf,headerv);
		}
	}
	return SMFIS_CONTINUE;
}

sfsistat mlfi_eoh(SMFICTX *ctx)
{
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	if (priv->pipeto) {
		/* output the blank line between the header and the body */
		fprintf(priv->pipeto, "\n");
	}

	/* continue processing */
	return SMFIS_CONTINUE;
}


// Allocate a buffer for the new connection
sfsistat mlfi_connect( SMFICTX *ctx, char *hostname, _SOCK_ADDR *hostaddr)
{
	struct MLFI_PRIV *priv;
	priv = calloc(1,sizeof(*priv));
	if (!priv) {
		perror("calloc-priv");
		exit(3); 
	}
	smfi_setpriv(ctx,(void *)priv);
	return SMFIS_CONTINUE;
}


// End of message -- print to the log and go home
sfsistat mlfi_eom( SMFICTX * ctx )
{
	time_t t;
	char datestring[48];
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	struct tm *tm;
	char *strftimeformat = "%" "y-%m-%d %H:%M"; //suppress stupid y2k no century warning

	// End of pipe
	mlfi_closepipe(ctx);

	time(&t);
	tm = gmtime(&t);
	strftime(datestring,sizeof(datestring)-1, strftimeformat, tm);
	// Just in case it's a problem for two threads to do
	// fprintf at the same time ... 
	pthread_mutex_lock(&mlfi_outputlock);
	mlfi_openlogfile();
	if (logfile) {
		fseek( logfile, 0, SEEK_END );
		fprintf(logfile,
			"%s"		// date
			"\ttime=%lu"	// seconds aka squid for parsing
			"\tfrom=%s"	// sender
			"%s"		// recipient(s)
			"\tsize=%d"	// body size
			"\tsubject=%s"	// subject
			"%s"		// att= ... optionally, if there were attachments
			"\n",
			datestring,
			(long unsigned)time(&t),
			priv->envfrom, priv->rcptto,
			(int)priv->bodylen, priv->subject,
			priv->filecount ? priv->filenames : ""
			);
		fflush(logfile);
	}
	if (headerlogfile) {
		char *strftimeformat = "%a %b %d %H:%M:%S %Y"; 
		strftime(datestring,sizeof(datestring)-1, strftimeformat, tm);
		fseek( headerlogfile, 0, SEEK_END );
		fprintf(headerlogfile,
			"From %s %s\n"
			"X-Timestamp: %lu\n"	// seconds aka squid for parsing
			"%s"		// headers
			"\n",
			priv->envfrom,
			datestring,
			(long unsigned)time(&t),
			priv->headers
			);
		fflush(headerlogfile);
	}
	pthread_mutex_unlock(&mlfi_outputlock);
	return SMFIS_CONTINUE;
}

sfsistat mlfi_abort(SMFICTX *ctx)
{
	return mlfi_closepipe(ctx);
}

// Time toooo, say good bye...
sfsistat mlfi_close( SMFICTX * ctx )
{
	struct MLFI_PRIV *priv = smfi_getpriv(ctx);  
	mlfi_closepipe(ctx);
	free(priv);
	smfi_setpriv(ctx,NULL);
	return SMFIS_CONTINUE;
}

struct smfiDesc smfilter = {
    "mailspy", /* filter name */
    SMFI_VERSION, /* version code -- do not change */
    0, /* flags -- we don't change anything, so we say so */
    mlfi_connect, /* connection info filter */
    NULL, /* SMTP HELO command filter */
    mlfi_envfrom, /* envelope sender filter */
    mlfi_envrcpt, /* envelope recipient filter */
    mlfi_header, /* header filter */
    mlfi_eoh, /* end of header */
    mlfi_body, /* body block filter */
    mlfi_eom, /* end of message */
    mlfi_abort, /* message aborted */
    mlfi_close /* connection cleanup */
};

// Print usage message and croak
void mlfi_usage(char *programname) 
{
	fprintf(stdout,
		"mailspy version %s\n"
		"\t%s -p socket [-f logfile] [-h headerlog] [-P pipe-msg-to-cmd]\n"
		"\n"
		"Where socket is one of the following connecting to sendmail:\n"
		"\tunix:/path/to/file  -- A named pipe.\n"
		"\tinet:port@hostname  -- An IPV4 socket.\n"
		"\tinet6:port@hostname -- An IPV6 socket.\n"
		, VERSION
		, programname
		);
	exit(4);
}

/* Drop privileges .. hopefully we will still be able to write to the log file
 * after a SIGUSR1 ... */
void mlfi_dropprivs(void)
{
	struct passwd *user;

	if((user = getpwnam(mailspyuser))==NULL) {
	    fprintf(stderr, "mailspy: Can't get information about user %s.\n", mailspyuser);
	    exit(1);
	}
	/* If we are already ourselves, then don't worry */
	if (user->pw_uid == getuid()) {
		return;
	}

#ifdef HAVE_INITGROUPS
	if(initgroups(mailspyuser, user->pw_gid)) {
		fprintf(stderr, "mailspy: initgroups() failed.\n");
		exit(1);
	}
#endif
#ifdef HAVE_SETGROUPS
	if(setgroups(1, &user->pw_gid)) {
		fprintf(stderr, "mailspy: setgroups() failed.\n");
		exit(1);
	}
#endif

	if(setgid(user->pw_gid)) {
	    fprintf(stderr, "mailspy: setgid(%d) failed.\n", (int) user->pw_gid);
	    exit(1);
	}

	if(setuid(user->pw_uid)) {
	    fprintf(stderr, "mailspy: setuid(%d) failed.\n", (int) user->pw_uid);
	    exit(1);
	}
}

/* What do we do with the USR1 signal ... we can't use SIGHUP, since it is used
 * internally by the milter library */
void mlfi_reload(int someorothernumber)
{
	new_logfile = 1;
	signal(SIGUSR1,mlfi_reload);
}


int main(int argc, char *argv[])
{
	char c;
	const char *args = "p:f:h:P:u:";

	signal(SIGUSR1,mlfi_reload);

	/* Process command line options */
	while ((c = getopt(argc, argv, args)) != -1) {
		switch (c) {
		case 'p':
			if (optarg == NULL || *optarg == '\0') {
				fprintf(stderr, "Illegal connection: %s\n",optarg);
				exit(1);
			}
			smfi_setconn(optarg);
			break;
		case 'f':
			if (optarg == NULL || *optarg == '\0' || logfilename) {
				fprintf(stderr, "Illegal logfile specified\n");
				exit(2);
			}
			logfilename=strdup(optarg);
			// Error check for strdup out of memory is too painful.  Won't bother.
			break;
		case 'u':
			if (optarg == NULL || *optarg == '\0' ) {
				fprintf(stderr, "Illegal user specified\n");
				exit(2);
			}
			mailspyuser=strdup(optarg);
			// Error check for strdup out of memory is too painful.  Won't bother. again.
			break;
		case 'h':
			// fixme -- cloned code
			if (optarg == NULL || *optarg == '\0' || headerlogfilename) {
				fprintf(stderr, "Illegal header logfile specified\n");
				exit(2);
			}
			headerlogfilename=strdup(optarg);
			// Error check for strdup out of memory is too painful.  Won't bother.
			break;
		case 'P':
			// fixme -- cloned code
			if (optarg == NULL || *optarg == '\0' || processpipeto ) {
				fprintf(stderr, "-P <process>, once only\n");
				exit(2);
			}
			processpipeto=strdup(optarg);
			// Error check for strdup out of memory is too painful.  Won't bother.
			break;
	       }
	}
	if (logfilename==NULL && headerlogfilename==NULL && processpipeto==NULL) {
		mlfi_usage(argv[0]);
		exit(2);
	}
	mlfi_dropprivs();
	mlfi_openlogfile();

	/* And now start up */
	if (smfi_register(smfilter) == MI_FAILURE) {
		fprintf(stderr, "smfi_register failed\n");
		exit(2);
	}
	return smfi_main();
}

