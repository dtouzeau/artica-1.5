/*
 * Copyright (c) 2005, Petr Rehor <rx@rx.cz>. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the copyright holders nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNERS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * $Id: main.c,v 1.24 2008/11/10 00:45:10 reho Exp $
 */

#include "amavisd-milter.h"

#include <stdarg.h>
#include <sys/socket.h>
#include <sysexits.h>


/*
** GLOBAL VARIABLES
*/
int		daemonize = 1;
int		daemonized = 0;
int		debug_level = LOG_WARNING;
int		max_conns = 0;
int		max_wait = 5 * 60;
sem_t		max_sem_t;
sem_t	       *max_sem = NULL;
const char     *pid_file = LOCAL_STATE_DIR "/" PACKAGE ".pid";
const char     *mlfi_socket = LOCAL_STATE_DIR "/" PACKAGE ".sock";
#ifdef HAVE_SMFI_SETBACKLOG
int		mlfi_socket_backlog = 0;
#endif
long		mlfi_timeout = 600;
const char     *amavisd_socket = LOCAL_STATE_DIR "/amavisd.sock";
long		amavisd_timeout = 600;
const char     *working_dir = WORKING_DIR;
const char     *delivery_care_of = "client";


/*
** USAGE - Print usage info
*/
static void
usage(const char *progname)
{
    (void) fprintf(stdout, "\nUsage: %s [OPTIONS]\n", progname);
    (void) fprintf(stdout, "Options are:\n");
    (void) fprintf(stdout, "	-d debug-level		Set debug level\n");
    (void) fprintf(stdout, "	-D delivery		Delivery care of server or client\n");
    (void) fprintf(stdout, "	-f			Run in the foreground\n");
    (void) fprintf(stdout, "	-h			Print this page\n");
    (void) fprintf(stdout, "	-m max-conns		Maximum amavisd connections \n");
    (void) fprintf(stdout, "	-M max-wait		Maximum wait for connection in seconds\n");
    (void) fprintf(stdout, "	-p pidfile		Use this pid file\n");
#ifdef HAVE_SMFI_SETBACKLOG
    (void) fprintf(stdout, "	-q backlog		Milter communication socket backlog\n");
#endif
    (void) fprintf(stdout, "	-s socket		Milter communication socket\n");
    (void) fprintf(stdout, "	-S socket		Amavisd communication socket\n");
    (void) fprintf(stdout, "	-t timeout		Milter connection timeout in seconds\n");
    (void) fprintf(stdout, "	-T timeout		Amavisd connection timeout in seconds\n");
    (void) fprintf(stdout, "	-v			Report the version and exit\n");
    (void) fprintf(stdout, "	-w directory		Set the working directory\n\n");
}


/*
** USAGEERR - Print error message, program usage and then exit
*/
static void
usageerr(const char *progname, const char *fmt, ...)
{
    char	buf[MAXLOGBUF];
    va_list	ap;

    /* Format err message */
    va_start(ap, fmt);
    (void) vsnprintf(buf, sizeof(buf), fmt, ap);
    va_end(ap);

    /* Print error message, program usage and then exit */
    (void) fprintf(stderr, "%s: %s\n", progname , buf);
    usage(progname);
    exit(EX_USAGE);
}


/*
** VERSIONINFO - Print program version info
*/
static void
versioninfo(const char *progname)
{
    (void) fprintf(stdout, "%s %s\n", progname, VERSION);
}


/*
** MAIN - Main program loop
*/
int
main(int argc, char *argv[])
{
    static	const char *args = "d:D:fhm:M:p:q:s:S:t:T:vw:";

    int		c, rstat;
    char       *p;
    const char *progname, *socket_name;
    FILE       *fp;
    struct	stat st;
    mode_t	save_umask;
    struct	sockaddr_un unix_addr;

    /* Program name */
    p = strrchr(argv[0], '/');
    if (p == NULL) {
	progname = argv[0];
    } else {
	progname = p + 1;
    }

    /* Open syslog */
    openlog(progname, LOG_PID, LOG_MAIL);

    /* Process command line options */
    while ((c = getopt(argc, argv, args)) != EOF) {
	switch (c) {
	case 'd':		/* debug level */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    debug_level = (int) strtol(optarg, &p, 10);
	    if (p != NULL && *p != '\0') {
		usageerr(progname, "debug level is not valid number: %s",
		    optarg);
	    }
	    if (debug_level < 0) {
		usageerr(progname, "negative debug level: %d", debug_level);
	    }
	    debug_level += LOG_WARNING;
	    break;
	case 'D':		/* delivery mechanism */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    if (strcmp(optarg, "client") != 0 &&
		strcmp(optarg, "server") != 0)
	    {
		usageerr(progname, "unknown delivery mechanism '%s'", optarg);
	    }
	    delivery_care_of = optarg;
	    break;
	case 'f':		/* run in foreground */
	    daemonize = 0;
	    break;
	case '?':		/* options parsing error */
	    (void) fprintf(stderr, "\n");
	case 'h':		/* help */
	    usage(progname);
	    exit(EX_OK);
	    break;
	case 'm':		/* maximum amavisd connections */
	    max_conns = (int) strtol(optarg, &p, 10);
	    if (p != NULL && *p != '\0') {
		usageerr(progname,
		    "maximum amavisd connections is not valid number: %s",
		    optarg);
	    }
	    if (max_conns < 0) {
		usageerr(progname, "negative maximum amavisd connections: %d",
		    max_conns);
	    }
	    break;
	case 'M':		/* maximum wait for connection */
	    max_wait = (int) strtol(optarg, &p, 10);
	    if (p != NULL && *p != '\0') {
		usageerr(progname,
		    "maximum wait for connection is not valid number: %s",
		    optarg);
	    }
	    if (max_wait < 0) {
		usageerr(progname, "negative maximum wait for connection: %d",
		    max_wait);
	    }
	    break;
	case 'p':		/* pid file name */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    pid_file = optarg;
	    break;
#ifdef HAVE_SMFI_SETBACKLOG
	case 'q':		/* milter communication socket backlog */
	    mlfi_socket_backlog = (int) strtol(optarg, &p, 10);
	    if (p != NULL && *p != '\0') {
		usageerr(progname,
		    "milter communication socket backlog is not valid number: "
		    "%s", optarg);
	    }
	    if (mlfi_socket_backlog < 0) {
		usageerr(progname,
		    "negative milter communication socket backlog: %d",
		    mlfi_socket_backlog);
	    }
	    break;
#endif
	case 's':		/* milter communication socket */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    if (strlen(optarg) >= sizeof(unix_addr.sun_path) - 1) {
		usageerr(progname,
		    "milter communication socket name too long: %s", optarg);
	    }
	    mlfi_socket = optarg;
	    break;
	case 't':		/* milter connection timeout */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    mlfi_timeout = (int) strtol(optarg, &p, 10);
	    if (p != NULL && *p != '\0') {
		usageerr(progname,
		    "milter connection timeout is not valid number: %s",
		    optarg);
	    }
	    if (mlfi_timeout < 0) {
		usageerr(progname, "negative milter connection timeout: %ld",
		    mlfi_timeout);
	    }
	    break;
	case 'v':		/* version info */
	    versioninfo(progname);
	    exit(EX_OK);
	    break;
	case 'w':		/* working directory */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    working_dir = optarg;
	    break;
	case 'S':		/* amavisd communication socket */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    if (strlen(optarg) >= sizeof(unix_addr.sun_path) - 1) {
		usageerr(progname,
		    "amavisd communication socket name too long: %s", optarg);
	    }
	    amavisd_socket = optarg;
	    break;
	case 'T':		/* amavisd connection timeout */
	    if (optarg == NULL || *optarg == '\0') {
		usageerr(progname, "option requires an argument -- %c",
		    (char)c);
	    }
	    amavisd_timeout = (int) strtol(optarg, &p, 10);
	    if (p != NULL && *p != '\0') {
		usageerr(progname,
		    "amavisd connection timeout is not valid number: %s",
		    optarg);
	    }
	    if (amavisd_timeout < 0) {
		usageerr(progname, "negative amavisd connection timeout: %ld",
		    amavisd_timeout);
	    }
	    break;
	default:		/* unknown option */
	    usageerr(progname, "illegal option -- %c", (char)c);
	    break;
	}
    }

    /* Create amavisd connections semaphore */
    if (max_conns > 0) {
	if (sem_init(&max_sem_t, 0, max_conns) == -1) {
	    logmsg(LOG_ERR,
		"could not initialize amavisd connections semaphore: %s",
		strerror(errno));
	    exit(EX_SOFTWARE);
	} else {
	    max_sem = &max_sem_t;
	}
    }

    /* Check permissions on working directory */
    /* TODO: traverse working directory path */
    if (stat(working_dir, &st) != 0) {
	logmsg(LOG_ERR, "could not stat() to working directory %s: %s",
	    working_dir, strerror(errno));
	exit(EX_SOFTWARE);
    }
    if (!S_ISDIR(st.st_mode)) {
	logmsg(LOG_ERR, "%s is not directory", working_dir);
	exit(EX_SOFTWARE);
    }
    if ((st.st_mode & S_IRWXO) != 0) {
	logmsg(LOG_ERR, "working directory %s is world accessible", working_dir);
	exit(EX_SOFTWARE);
    }

    /* Configure milter */
    socket_name = NULL;
    if (mlfi_socket[0] == '/') {
	socket_name = mlfi_socket;
    }
    if (! strncmp(mlfi_socket, "unix:", 5)) {
	socket_name = mlfi_socket + 5;
    }
    if (! strncmp(mlfi_socket, "local:", 6)) {
	socket_name = mlfi_socket + 6;
    }
    if (socket_name != NULL && unlink(socket_name) != 0 && errno != ENOENT) {
	logmsg(LOG_ERR, "could not unlink old milter socket %s: %s",
	    socket_name, strerror(errno));
	exit(EX_SOFTWARE);
    }
    if (debug_level > LOG_DEBUG &&
	smfi_setdbg(debug_level - LOG_DEBUG) != MI_SUCCESS)
    {
	logmsg(LOG_ERR, "could not set milter debug level");
	exit(EX_SOFTWARE);
    }
    if (mlfi_timeout > 0 && smfi_settimeout(mlfi_timeout) != MI_SUCCESS) {
	logmsg(LOG_ERR, "could not set milter timeout");
	exit(EX_SOFTWARE);
    }
    if (smfi_setconn(mlfi_socket) != MI_SUCCESS) {
	logmsg(LOG_ERR, "could not set milter socket");
	exit(EX_SOFTWARE);
    }
    if (smfi_register(smfilter) != MI_SUCCESS) {
	logmsg(LOG_ERR, "could not register milter");
	exit(EX_SOFTWARE);
    }

    /* Unlink old pid file */
    if (pid_file != NULL) {
	if (unlink(pid_file) != 0 && errno != ENOENT) {
	    logmsg(LOG_WARNING, "could not unlink old pid file %s: %s",
		pid_file, strerror(errno));
	}
    }

    /* Run in the background */
    if (daemonize) {
	if (daemon(1, 1) != -1) {
	    daemonized = 1;
	} else {
	    logmsg(LOG_ERR, "could not fork daemon process: %s",
		strerror(errno));
	    exit(EX_OSERR);
	}
    }

    /* Connect to milter socket */
#ifdef HAVE_SMFI_SETBACKLOG
    if (mlfi_socket_backlog > 0) {
	if (smfi_setbacklog(mlfi_socket_backlog) != MI_SUCCESS) {
	    logmsg(LOG_WARNING, "could not set milter socket backlog to %d",
		mlfi_socket_backlog);
	}
    }
#endif
#ifdef HAVE_SMFI_OPENSOCKET
    if (smfi_opensocket(false) != MI_SUCCESS) {
	logmsg(LOG_ERR, "could not open milter socket %s", mlfi_socket);
        exit(EX_SOFTWARE);
    }
#endif

    /* Greetings message */
    logmsg(LOG_WARNING, "starting %s %s on socket %s", progname, VERSION,
	mlfi_socket);

    /* Create pid file */
    if (pid_file != NULL) {
	save_umask = umask(022);
	fp = fopen(pid_file, "w");
	if (fp == NULL) {
	    logmsg(LOG_WARNING, "could not create pid file %s: %s",
		pid_file, strerror(errno));
	} else {
	    (void) fprintf(fp, "%ld\n", (long) getpid());
	    if (ferror(fp)) {
		logmsg(LOG_WARNING, "could not write to pid file %s: %s",
		    pid_file, strerror(errno));
		clearerr(fp);
		(void) fclose(fp);
	    } else if (fclose(fp) != 0) {
		logmsg(LOG_WARNING, "could not close pid file %s: %s",
		    pid_file, strerror(errno));
	    }
	}
	umask(save_umask);
    }

    /* Run milter */
    if ((rstat = smfi_main()) != MI_SUCCESS) {
	logmsg(LOG_ERR, "%s failed", progname);
    } else {
	logmsg(LOG_WARNING, "stopping %s %s on socket %s", progname, VERSION,
	    mlfi_socket);
    }

    /* Unlink pid file */
    if (pid_file != NULL) {
	if (unlink(pid_file) != 0) {
	    logmsg(LOG_WARNING, "could not unlink pid file %s: %s",
		pid_file, strerror(errno));
	}
    }

    /* Destroy amavisd connections semaphore */
    if (max_sem != NULL && sem_destroy(max_sem) == -1) {
	logmsg(errno == EBUSY ? LOG_ERR : LOG_WARNING,
	    "%s: could not destroy amavisd connections semaphore: %s",
	    progname, strerror(errno));
    }

    return rstat;
}
