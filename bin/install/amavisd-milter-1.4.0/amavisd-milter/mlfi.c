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
 * $Id: mlfi.c,v 1.55 2008/11/10 00:45:10 reho Exp $
 */

#include "amavisd-milter.h"

#include <arpa/inet.h>
#include <netinet/in.h>
#include <stdarg.h>


/*
** SMFILTER - Milter description
*/
struct smfiDesc smfilter =
{
    PACKAGE,			/* filter name */
    SMFI_VERSION,		/* version code -- do not change */
    SMFIF_ADDHDRS |
    SMFIF_CHGHDRS |
    SMFIF_ADDRCPT |
    SMFIF_DELRCPT,		/* filter actions */
    mlfi_connect,		/* connection info filter */
    mlfi_helo,			/* SMTP HELO command filter */
    mlfi_envfrom,		/* envelope sender filter */
    mlfi_envrcpt,		/* envelope recipient filter */
    mlfi_header,		/* header filter */
    mlfi_eoh,			/* end of header */
    mlfi_body,			/* body block filter */
    mlfi_eom,			/* end of message */
    mlfi_abort,			/* message aborted */
    mlfi_close			/* connection cleanup */
};


/*
** Dates and months abbreviations
*/
static const char *days[] =
{
    "Sun",
    "Mon",
    "Tue",
    "Wed",
    "Thu",
    "Fri",
    "Sat"
};

static const char *months[] =
{
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec"
};


/*
** SNPRINTFCAT - Append formatted string
*/
static size_t
snprintfcat(size_t length, char *buf, size_t size, const char *fmt, ...)
{
    va_list	ap;

    va_start(ap, fmt);
    if ((length += vsnprintf(buf + length, size - length, fmt, ap)) >= size) {
	length = size -1;
    }
    va_end(ap);

    return length;
}


/*
** MLFI_CLEANUP_MESSAGE - Cleanup message context
**
** mlfi_cleanup_message() close message file if open, unlink working directory
** and release message context
*/
static void
mlfi_cleanup_message(struct mlfiCtx *mlfi)
{
    FTS	       *fts;
    FTSENT     *ftsent;
    char       *wrkdir[] = { NULL, NULL };
    struct	mlfiAddress *rcpt;

    logqidmsg(mlfi, LOG_DEBUG, "CLEANUP MESSAGE CONTEXT");

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "mlfi_cleanup_message: context is not set");
	return;
    }

    /* Close amavisd connection */
    amavisd_close(mlfi);

    /* Close the message file */
    if (mlfi->mlfi_fp != NULL) {
	if (fclose(mlfi->mlfi_fp) != 0 && errno != EBADF) {
	    logqidmsg(mlfi, LOG_WARNING, "could not close message file %s: %s",
		mlfi->mlfi_fname, strerror(errno));
	} else {
	    logqidmsg(mlfi, LOG_DEBUG, "close message file %s",
		mlfi->mlfi_fname);
	}
	mlfi->mlfi_fp = NULL;
    }

    /* Remove working directory */
    if (mlfi->mlfi_wrkdir[0] != '\0') {
	wrkdir[0] = mlfi->mlfi_wrkdir;
	fts = fts_open(wrkdir, FTS_PHYSICAL | FTS_NOCHDIR, NULL);
	if (fts == NULL) {
	    logqidmsg(mlfi, LOG_WARNING, "could not open file hierarchy %s: %s",
		mlfi->mlfi_wrkdir, strerror(errno));
	} else {
	    while ((ftsent = fts_read(fts)) != NULL) {
		switch (ftsent->fts_info) {
		case FTS_ERR:
		    /*
		     * This is an error return, and the fts_errno
		     * field will be set to indicate what caused the
		     * error.
		     */
		    logqidmsg(mlfi, LOG_WARNING,
			"could not traverse file hierarchy %s: %s",
			ftsent->fts_path, strerror(ftsent->fts_errno));
		    break;
		case FTS_DNR:
		    /*
		     * Assume that since fts_read() couldn't read the
		     * directory, it can't be removed.
		     */
		    if (ftsent->fts_errno != ENOENT) {
			logqidmsg(mlfi, LOG_WARNING,
			    "could not remove directory %s: %s",
			    ftsent->fts_path, strerror(ftsent->fts_errno));
		    }
		    break;
		case FTS_NS:
		    /*
		     * Assume that since fts_read() couldn't stat the
		     * file, it can't be unlinked.
		     */
		    logqidmsg(mlfi, LOG_WARNING, "could not unlink file %s: %s",
			ftsent->fts_path, strerror(ftsent->fts_errno));
		    break;
		case FTS_D:
		    /*
		     * Skip pre-order directory.
		     */
		    break;
		case FTS_DP:
		    /*
		     * Remove post-order directory.
		     */
		    if (rmdir(ftsent->fts_accpath) != 0 && errno != ENOENT) {
			logqidmsg(mlfi, LOG_WARNING,
			    "could not remove directory %s: %s",
			    ftsent->fts_path, strerror(ftsent->fts_errno));
		    } else {
			logqidmsg(mlfi, LOG_DEBUG, "remove directory %s",
			    ftsent->fts_path);
		    }
		    break;
		default:
		    /*
		     * A regular file or symbolic link.
		     */
		    if (unlink(ftsent->fts_accpath) != 0 && errno != ENOENT) {
			logqidmsg(mlfi, LOG_WARNING, "could not unlink file %s: %s",
			    ftsent->fts_path, strerror(ftsent->fts_errno));
		    } else {
			logqidmsg(mlfi, LOG_DEBUG, "unlink file %s",
			    ftsent->fts_path);
		    }
		}
	    }
	    if (fts_close(fts) != 0) {
		logqidmsg(mlfi, LOG_WARNING,
		    "could not close file hirerachy %s: %s",
		    mlfi->mlfi_wrkdir, strerror(errno));
	    }
	}
	mlfi->mlfi_wrkdir[0] = '\0';
    }

    /* Reset CRLF detection flag */
    mlfi->mlfi_cr_flag = 0;

    /* Free memory */
    free(mlfi->mlfi_prev_qid);
    mlfi->mlfi_prev_qid = mlfi->mlfi_qid;
    mlfi->mlfi_qid = NULL;
    free(mlfi->mlfi_from);
    mlfi->mlfi_from = NULL;
    free(mlfi->mlfi_policy_bank);
    mlfi->mlfi_policy_bank = NULL;
    while(mlfi->mlfi_rcpt != NULL) {
	rcpt = mlfi->mlfi_rcpt;
	mlfi->mlfi_rcpt = rcpt->q_next;
	free(rcpt);
    }
}


/*
** MLFI_CLEANUP - Cleanup connection context
**
** mlfi_cleanup() cleanup message context and relese connection context
*/
static void
mlfi_cleanup(struct mlfiCtx *mlfi)
{
    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "CLEANUP CONNECTION CONTEXT");
	logqidmsg(mlfi, LOG_DEBUG, "mlfi_cleanup: context is not set");
	return;
    }

    /* Cleanup the message context */
    mlfi_cleanup_message(mlfi);

    logqidmsg(mlfi, LOG_DEBUG, "CLEANUP CONNECTION CONTEXT");

    /* Cleanup the connection context */
    free(mlfi->mlfi_hostname);
    free(mlfi->mlfi_client_addr);
    free(mlfi->mlfi_client_host);
    free(mlfi->mlfi_helo);
    free(mlfi->mlfi_protocol);
    free(mlfi->mlfi_amabuf);
    free(mlfi->mlfi_prev_qid);

    /* Free context */
    free(mlfi);
}


/*
** MLFI_SETREPLY_TEMPFAIL - Set SMFIS_TEMPFAIL reply
*/
static void
mlfi_setreply_tempfail(SMFICTX *ctx)
{
    const char *rcode = "451";
    const char *xcode = "4.6.0";
    const char *reason = "Content scanner malfunction";

    struct	mlfiCtx *mlfi = MLFICTX(ctx);

    if (smfi_setreply(ctx, rcode, xcode, reason) != MI_SUCCESS) {
	logqidmsg(mlfi, LOG_WARNING, "could not set SMTP reply: %s %s %s",
	    rcode, xcode, reason);
    } else {
	logqidmsg(mlfi, LOG_DEBUG, "set reply %s %s %s", rcode, xcode, reason);
    }
}


/*
** MLFI_CONNECT - Handle incomming connection
** 
** mlfi_connect() is called once, at the start of each SMTP connection
*/
sfsistat
mlfi_connect(SMFICTX *ctx, char *hostname, _SOCK_ADDR * hostaddr)
{
    struct	mlfiCtx *mlfi = NULL;
    const void *addr;
    const char *prefix;
    int		len, plen;

    logmsg(LOG_DEBUG, "%s: CONNECT", hostname);

    /* Allocate memory for private data */
    mlfi = malloc(sizeof(*mlfi));
    if (mlfi == NULL) {
	logmsg(LOG_ERR, "%s: could not allocate private data", hostname);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Initialize context */
    (void) memset(mlfi, '\0', sizeof(*mlfi));
    mlfi->mlfi_amasd = -1;

    /* Save connection informations */
    if (hostname != NULL && *hostname != '\0') {
	if ((mlfi->mlfi_client_host = strdup(hostname)) == NULL) {
	    logmsg(LOG_ERR, "%s: could not allocate memory", hostname);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }
    addr = NULL;
    if (hostaddr != NULL) {
	switch(hostaddr->sa_family) {
	case AF_INET:
	    addr = &((struct sockaddr_in *)hostaddr)->sin_addr;
	    len = INET_ADDRSTRLEN;
	    prefix = NULL;
	    plen = 0;		/* prefix length */
	    break;
#if HAVE_DECL_AF_INET6 && HAVE_DECL_INET6_ADDRSTRLEN && HAVE_STRUCT_SOCKADDR_IN6
	case AF_INET6:
	    addr = &((struct sockaddr_in6 *)hostaddr)->sin6_addr;
	    len = INET6_ADDRSTRLEN;
	    prefix = "IPv6:";
	    plen = 5;		/* prefix length */
	    break;
#endif
	default:
	    logqidmsg(mlfi, LOG_WARNING, "unrecognized address family %d for "
		"host %s", (int)hostaddr->sa_family, hostname);
	    break;
	}
    }
    if (addr != NULL) {
	if ((mlfi->mlfi_client_addr = malloc(len + plen)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
	if (prefix != NULL) {
	    (void) strlcpy(mlfi->mlfi_client_addr, prefix, len + plen);
	}
	if (inet_ntop(hostaddr->sa_family, addr, mlfi->mlfi_client_addr + plen,
	    len) == NULL)
	{
	    free(mlfi->mlfi_client_addr);
	    mlfi->mlfi_client_addr = NULL;
	    logqidmsg(mlfi, LOG_WARNING, "could not convert host address to "
		"string for host %s", hostname);
	} else {
	    logqidmsg(mlfi, LOG_DEBUG, "host address: %s",
		mlfi->mlfi_client_addr);
	}
    }

    /* Allocate amavisd communication buffer */
    mlfi->mlfi_amabuf_length = AMABUFCHUNK;
    if ((mlfi->mlfi_amabuf = malloc(mlfi->mlfi_amabuf_length)) == NULL) {
	logqidmsg(mlfi, LOG_ERR,
	    "could not allocate amavisd communication buffer");
	mlfi_setreply_tempfail(ctx);
	mlfi_cleanup(mlfi);
	return SMFIS_TEMPFAIL;
    }

    /* Save the private data */
    if (smfi_setpriv(ctx, mlfi) != MI_SUCCESS) {
	logqidmsg(mlfi, LOG_ERR, "could not set milter context");
	mlfi_setreply_tempfail(ctx);
	mlfi_cleanup(mlfi);
	return SMFIS_TEMPFAIL;
    }

    /* Save hostname */
    if ((hostname = smfi_getsymval(ctx, "j")) != NULL) {
	if ((mlfi->mlfi_hostname = strdup(hostname)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_HELO - Handle the HELO/EHLO command
**
** mlfi_helo() is called whenever the client sends a HELO/EHLO command.
** It may therefore be called between zero and three times.
*/
sfsistat
mlfi_helo(SMFICTX *ctx, char* helohost)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_helo: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "HELO: %s", helohost);

    /* Save helo hostname */
    if (helohost != NULL && *helohost != '\0') {
	free(mlfi->mlfi_helo);
	if ((mlfi->mlfi_helo = strdup(helohost)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_ENVFORM - Handle the envelope FROM command
** 
** mlfi_envfrom() is called once at the beginning of each message, before
** mlfi_envrcpt()
*/
sfsistat
mlfi_envfrom(SMFICTX *ctx, char **envfrom)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);
    char	buf[64];
    const char *auth_type, *auth_authen, *auth_ssf;
    const char *date, *qid, *wrkdir;
    const char *protocol = NULL;
    size_t	l;
    time_t	t;
    struct	tm gt, lt;
    int		gmtoff;

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_envfrom: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Save communication protocol name */
    /* XXX: sendmail's macro $r is not available in mlfi_helo stage */
    if (mlfi->mlfi_protocol == NULL &&
	(protocol = smfi_getsymval(ctx, "r")) != NULL)
    {
	logqidmsg(mlfi, LOG_DEBUG, "protocol: %s", protocol);
	if ((mlfi->mlfi_protocol = strdup(protocol)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Cleanup message data */
    mlfi_cleanup_message(mlfi);

    /* Save queue id */
    if ((qid = smfi_getsymval(ctx, "i")) != NULL) {
	if ((mlfi->mlfi_qid = strdup(qid)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    logqidmsg(mlfi, LOG_DEBUG, "MAIL FROM: %s", *envfrom);

    /* Save from mail address */
    if (*envfrom != NULL && **envfrom != '\0') {
	if ((mlfi->mlfi_from = strdup(*envfrom)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Create working directory */
    if (mlfi->mlfi_qid != NULL) {
	(void) snprintf(mlfi->mlfi_wrkdir, sizeof(mlfi->mlfi_wrkdir) - 1,
	    "%s/af%s", working_dir, mlfi->mlfi_qid);
	if (mkdir(mlfi->mlfi_wrkdir, S_IRWXU|S_IRGRP|S_IXGRP) != 0) {
	    mlfi->mlfi_wrkdir[0] = '\0';
	}
    }
    if (mlfi->mlfi_wrkdir[0] == '\0') {
	(void) snprintf(mlfi->mlfi_wrkdir, sizeof(mlfi->mlfi_wrkdir) - 1,
	    "%s/afXXXXXXXXXX", working_dir);
	if ((wrkdir = mkdtemp(mlfi->mlfi_wrkdir)) != NULL) {
	    (void) strlcpy(mlfi->mlfi_wrkdir, wrkdir,
		sizeof(mlfi->mlfi_wrkdir));
	} else {
	    logqidmsg(mlfi, LOG_ERR, "could not create working directory: %s",
		strerror(errno));
	    mlfi->mlfi_wrkdir[0] = '\0';
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
	if (chmod(mlfi->mlfi_wrkdir, S_IRWXU|S_IRGRP|S_IXGRP) == -1) {
	    logqidmsg(mlfi, LOG_ERR,
		"could not change mode of directory %s: %s",
		mlfi->mlfi_wrkdir, strerror(errno));
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }
    logqidmsg(mlfi, LOG_DEBUG, "create working directory %s", mlfi->mlfi_wrkdir);

    /* Open file to store this message */
    (void) snprintf(mlfi->mlfi_fname, sizeof(mlfi->mlfi_fname) - 1,
	"%s/email.txt", mlfi->mlfi_wrkdir);
    if ((mlfi->mlfi_fp = fopen(mlfi->mlfi_fname, "w+")) == NULL) {
	logqidmsg(mlfi, LOG_ERR, "could not create message file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }
    if (fchmod(fileno(mlfi->mlfi_fp), S_IRUSR|S_IWUSR|S_IRGRP) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not change mode of file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }
    logqidmsg(mlfi, LOG_DEBUG, "create message file %s", mlfi->mlfi_fname);

    /* Get transaction date */
    if ((date = smfi_getsymval(ctx, "b")) == NULL) {
	(void) time(&t);
	gt = *gmtime(&t);
	lt = *localtime(&t);
	gmtoff = (lt.tm_hour - gt.tm_hour) * 60 + lt.tm_min - gt.tm_min;
	if (lt.tm_year < gt.tm_year) {
	    gmtoff -= 24 * 60;
	} else if (lt.tm_year > gt.tm_year) {
	    gmtoff += 24 * 60;
	} else if (lt.tm_yday < gt.tm_yday) {
	    gmtoff -= 24 * 60;
	} else if (lt.tm_yday > gt.tm_yday) {
	    gmtoff += 24 * 60;
	}
	if (lt.tm_sec <= gt.tm_sec - 60) {
	    gmtoff -= 1;
	} else if (lt.tm_sec >= gt.tm_sec + 60) {
	    gmtoff += 1;
	}
	if (snprintf(buf, sizeof(buf),
#ifdef HAVE_STRUCT_TM_TM_ZONE
	    "%s, %d %s %d %02d:%02d:%02d %+03d%02d (%s)",
#else
	    "%s, %d %s %d %02d:%02d:%02d %+03d%02d",
#endif
	    days[lt.tm_wday], lt.tm_mday, months[lt.tm_mon],
	    lt.tm_year + 1900, lt.tm_hour, lt.tm_min, lt.tm_sec,
	    (int) gmtoff / 60, (int) abs(gmtoff) % 60
#ifdef HAVE_STRUCT_TM_TM_ZONE
	    , lt.tm_zone
#endif
	    ) < sizeof(buf))
	{
	    date = buf;
	}
    }

    /* Write synthesized received header to the file as the first header:*/
    /* Received: from <hello> (<rdns> [<ip>]) (authenticated bits=<bits>)*/
    /*		by <hostname> (<rdns> [<ip>])				 */
    /*		with <protocol> (authenticated as <user>) id <qid>;	 */
    /*		<date>							 */
    /*		(envelope-from <sender>)				 */
    *mlfi->mlfi_amabuf = '\0';
    l = snprintfcat(0, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	"Received: from %s", mlfi->mlfi_helo != NULL && *mlfi->mlfi_helo != '\0'
	    ? mlfi->mlfi_helo : "unknown");
    if ((mlfi->mlfi_client_host != NULL && *mlfi->mlfi_client_host != '\0')
	|| (mlfi->mlfi_client_addr != NULL && *mlfi->mlfi_client_addr != '\0'))
    {
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length, " (");
	if (mlfi->mlfi_client_host != NULL && *mlfi->mlfi_client_host != '\0') {
	    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
		"%s", mlfi->mlfi_client_host);
	}
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length, " ");
	if (mlfi->mlfi_client_addr != NULL && *mlfi->mlfi_client_addr != '\0') {
	    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
		"[%s]", mlfi->mlfi_client_addr);
	}
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length, ")");
    }
    if ((auth_type = smfi_getsymval(ctx, "{auth_type}")) != NULL) {
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	    " (authenticated");
	auth_ssf = smfi_getsymval(ctx, "{auth_ssf}");
	if (auth_ssf != NULL && *auth_ssf != '\0') {
	    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
		" bits=%s", auth_ssf);
	}
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length, ")");
    }
    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	"\n\tby %s (" PACKAGE ")",
	mlfi->mlfi_hostname != NULL && *mlfi->mlfi_hostname != '\0'
	    ? mlfi->mlfi_hostname : "localhost");
    if (mlfi->mlfi_protocol != NULL && *mlfi->mlfi_protocol != '\0') {
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	    " with %s", mlfi->mlfi_protocol);
    }
    auth_authen = smfi_getsymval(ctx, "{auth_authen}");
    if (auth_type != NULL && auth_authen != NULL && *auth_authen != '\0' ) {
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	    " (authenticated as %s)", auth_authen);
    }
    if (mlfi->mlfi_qid != NULL && *mlfi->mlfi_qid != '\0') {
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	    " id %s", mlfi->mlfi_qid);
    }
    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length, ";\n");
    if (date != NULL && *date != '\0') {
	l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	    "\t%s\n", date);
    }
    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	"\t(envelope-from %s)\n",
	mlfi->mlfi_from != NULL && *mlfi->mlfi_from != '\0'
	    ? mlfi->mlfi_from : "<>");
    logqidmsg(mlfi, LOG_DEBUG, "ADDHDR: %s", mlfi->mlfi_amabuf);
    (void) fputs(mlfi->mlfi_amabuf, mlfi->mlfi_fp);
    if (ferror(mlfi->mlfi_fp)) {
	logqidmsg(mlfi, LOG_ERR, "could not write to message file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Policy bank names */
    if (auth_type != NULL) {
	*mlfi->mlfi_amabuf = '\0';
	l = snprintfcat(0, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
	    "SMTP_AUTH,SMTP_AUTH_%s", auth_type);
	if (auth_ssf != NULL && *auth_ssf != '\0') {
	    l = snprintfcat(l, mlfi->mlfi_amabuf, mlfi->mlfi_amabuf_length,
		",SMTP_AUTH_%s_%s", auth_type, auth_ssf);
	}
	if ((mlfi->mlfi_policy_bank = strdup(mlfi->mlfi_amabuf)) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_ENVRCPT - Handle the envelope RCPT command
** 
** mlfi_envrcpt() is called once per recipient, hence one or more times
** per message, immediately after mlfi_envfrom()
*/
sfsistat
mlfi_envrcpt(SMFICTX *ctx, char **envrcpt)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);
    struct	mlfiAddress *rcpt, *r;
    int		rcptlen;

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_envrcpt: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "RCPT TO: %s",  *envrcpt);

    /* Store recipient address */
    rcptlen = strlen(*envrcpt);
    if ((rcpt = malloc(sizeof(*rcpt) + rcptlen)) == NULL) {
	logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }
    (void) strlcpy(rcpt->q_paddr, *envrcpt, rcptlen + 1);
    rcpt->q_next = NULL;
    if (mlfi->mlfi_rcpt == NULL) {
	mlfi->mlfi_rcpt = rcpt;
    } else {
	r = mlfi->mlfi_rcpt;
	while (r->q_next != NULL) {
	    r = r->q_next;
	}
	r->q_next = rcpt;
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_HEADER - Handle a message header
** 
** mlfi_header() is called zero or more times between mlfi_envrcpt() and
** mlfi_eoh(), once per message header
*/
sfsistat
mlfi_header(SMFICTX *ctx, char *headerf, char *headerv)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_header: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "HEADER: %s: %s", headerf, headerv);

    /* Write the header to the message file */
    (void) fprintf(mlfi->mlfi_fp, "%s: %s\n", headerf, headerv);
    if (ferror(mlfi->mlfi_fp)) {
	logqidmsg(mlfi, LOG_ERR, "could not write to message file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_EOH - Handle the end of message headers
** 
** mlfi_eoh() is called once after all headers have been sent and processed
*/
sfsistat
mlfi_eoh(SMFICTX *ctx)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_eoh: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "MESSAGE BODY");

    /* Write the blank line between the header and the body */
    (void) fprintf(mlfi->mlfi_fp, "\n");
    if (ferror(mlfi->mlfi_fp)) {
	logqidmsg(mlfi, LOG_ERR, "could not write to message file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_BODY - Handle a piece of a message's body
** 
** mlfi_body() is called zero or more times between mlfi_eoh() and mlfi_eom()
*/
sfsistat
mlfi_body(SMFICTX *ctx, unsigned char * bodyp, size_t bodylen)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);
    unsigned char *b, *c;

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_body: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "body chunk: %ld", (long)bodylen);

    /* Check if previous chunk ends with CR */
    if (mlfi->mlfi_cr_flag != 0) {
	mlfi->mlfi_cr_flag = 0;
	if (*bodyp != '\n') {
	    (void) fprintf(mlfi->mlfi_fp, "\r");
	    if (ferror(mlfi->mlfi_fp)) {
		logqidmsg(mlfi, LOG_ERR, "could not write to message file "
		    "%s: %s", mlfi->mlfi_fname, strerror(errno));
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	}
    }

    /* Convert CRLF to LF */
    b = c = bodyp;
    while (c < bodyp + bodylen) {
	if (mlfi->mlfi_cr_flag != 0) {
	    mlfi->mlfi_cr_flag = 0;
	    if (*c != '\n') {
		*b++ = '\r';
	    }
	    *b++ = *c++;
	} else if (*c == '\r') {
	    mlfi->mlfi_cr_flag = 1;
	    c++;
	} else {
	    *b++ = *c++;
	}
    }

    bodylen = b - bodyp;
    logqidmsg(mlfi, LOG_DEBUG, "after CRLF to LF conversion: %ld)",
	(long)(bodylen));

    /* Write the body chunk to the message file */
    if (fwrite(bodyp, bodylen, 1, mlfi->mlfi_fp) < 1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to message file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_EOM - Handle the end of a message
** 
** mlfi_eom() is called once after all calls to mlfi_body()
** for a given message
*/
sfsistat
mlfi_eom(SMFICTX *ctx)
{
    int		i;
    char       *idx, *header, *rcode, *xcode, *name, *value;
    const char *qid;
    sfsistat	rstat;
    struct	mlfiCtx *mlfi = MLFICTX(ctx);
    struct	mlfiAddress *rcpt;
    struct	sockaddr_un amavisd_sock;
    time_t	start_counter;
    int		wait_counter;

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_ERR, "mlfi_eom: context is not set");
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "CONTENT CHECK");

    /* Close the message file */
    if (mlfi->mlfi_fp == NULL) {
	logqidmsg(mlfi, LOG_ERR, "message file %s is not opened",
	    mlfi->mlfi_fname);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }
    if (fclose(mlfi->mlfi_fp) == -1) {
	mlfi->mlfi_fp = NULL;
	logqidmsg(mlfi, LOG_ERR, "could not close message file %s: %s",
	    mlfi->mlfi_fname, strerror(errno));
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }
    mlfi->mlfi_fp = NULL;
    logqidmsg(mlfi, LOG_DEBUG, "close message file %s", mlfi->mlfi_fname);

    /* Connect to amavisd */
    if (max_sem != NULL) {
	start_counter = time(NULL);
	wait_counter = MIN(SMFI_PROGRESS_TRIGGER, max_wait);
	while (amavisd_connect(mlfi, &amavisd_sock,
	    start_counter + wait_counter) == -1)
	{
	    if (errno != AMAVISD_CONNECT_TIMEDOUT_ERRNO) {
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    if (wait_counter >= max_wait) {
		logqidmsg(mlfi, LOG_WARNING,
		    "amavisd connection is not available for %d sec, giving up",
		    wait_counter);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
#ifdef HAVE_SMFI_PROGRESS
	    logqidmsg(mlfi, LOG_DEBUG,
		"amavisd connection is not available for %d sec, "
		"triggering sendmail", wait_counter);
	    if (smfi_progress(ctx) != MI_SUCCESS) {
		logqidmsg(mlfi, LOG_ERR,
		   "could not notify MTA that an operation is still in "
		   "progress");
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
#else
	    logqidmsg(mlfi, LOG_DEBUG,
		"amavisd connection not available for %d sec, waiting",
		wait_counter);
#endif
	    wait_counter += MIN(SMFI_PROGRESS_TRIGGER, max_wait - wait_counter);
	}
	logqidmsg(mlfi, LOG_DEBUG, "got amavisd connection for %d sec",
	    (int)(time(NULL) - start_counter));
#ifdef HAVE_SMFI_PROGRESS
	if (smfi_progress(ctx) != MI_SUCCESS) {
	    logqidmsg(mlfi, LOG_ERR,
		"could not notify MTA that an operation is still in progress");
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
#endif
    } else {
	if (amavisd_connect(mlfi, &amavisd_sock, 0) == -1) {
	    logqidmsg(mlfi, LOG_ERR,
		"could not connect to amavisd socket %s: %s",
		amavisd_socket, strerror(errno));
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	}
    }

    logqidmsg(mlfi, LOG_DEBUG, "AMAVISD REQUEST");

    /* AM.PDP protocol prologue */
    logqidmsg(mlfi, LOG_DEBUG, "request=AM.PDP");
    if (amavisd_request(mlfi, "request", "AM.PDP") == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Get queue id (Postfix does give information about */
    /* the queue-number only after the RCPT-TO-phase */
    if (mlfi->mlfi_qid == NULL) {
	if ((qid = smfi_getsymval(ctx, "i")) != NULL) {
	    if ((mlfi->mlfi_qid = strdup(qid)) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "could not allocate memory");
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	}
    }

    /* MTA queue id */
    if (mlfi->mlfi_qid != NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "queue_id=%s", mlfi->mlfi_qid);
	if (amavisd_request(mlfi, "queue_id", mlfi->mlfi_qid) == -1) {
	    logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
		amavisd_socket, strerror(errno));
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Communication protocol */
    if (mlfi->mlfi_protocol != NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "protocol_name=%s", mlfi->mlfi_protocol);
	if (amavisd_request(mlfi, "protocol_name", mlfi->mlfi_protocol) == -1) {
	    logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
		amavisd_socket, strerror(errno));
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Envelope sender address */
    logqidmsg(mlfi, LOG_DEBUG, "sender=%s", mlfi->mlfi_from);
    if (amavisd_request(mlfi, "sender", mlfi->mlfi_from) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Envelope recipient addresses */
    rcpt = mlfi->mlfi_rcpt;
    while (rcpt != NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "recipient=%s", rcpt->q_paddr);
	if (amavisd_request(mlfi, "recipient", rcpt->q_paddr) == -1) {
	    logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
		amavisd_socket, strerror(errno));
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
	rcpt = rcpt->q_next;
    }

    /* Working directory */
    logqidmsg(mlfi, LOG_DEBUG, "tempdir=%s", mlfi->mlfi_wrkdir);
    if (amavisd_request(mlfi, "tempdir", mlfi->mlfi_wrkdir) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Who is responsible for removing the working directory */
    logqidmsg(mlfi, LOG_DEBUG, "tempdir_removed_by=client");
    if (amavisd_request(mlfi, "tempdir_removed_by", "client") == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* File containing the original mail */
    logqidmsg(mlfi, LOG_DEBUG, "mail_file=%s", mlfi->mlfi_fname);
    if (amavisd_request(mlfi, "mail_file", mlfi->mlfi_fname) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* Who is responsible for forwarding the message */
    logqidmsg(mlfi, LOG_DEBUG, "delivery_care_of=%s", delivery_care_of);
    if (amavisd_request(mlfi, "delivery_care_of", delivery_care_of) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* IP address of the original SMTP client */
    logqidmsg(mlfi, LOG_DEBUG, "client_address=%s", mlfi->mlfi_client_addr);
    if (amavisd_request(mlfi, "client_address", mlfi->mlfi_client_addr) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    /* DNS name of the original SMTP client */
    if (mlfi->mlfi_client_host != NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "client_name=%s", mlfi->mlfi_client_host);
	if (amavisd_request(mlfi, "client_name", mlfi->mlfi_client_host) == -1) {
	    logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
		amavisd_socket, strerror(errno));
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* The value of the HELO or EHLO specified by the original SMTP client */
    if (mlfi->mlfi_helo != NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "helo_name=%s", mlfi->mlfi_helo);
	if (amavisd_request(mlfi, "helo_name", mlfi->mlfi_helo) == -1) {
	    logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
		amavisd_socket, strerror(errno));
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Policy bank names */
    if (mlfi->mlfi_policy_bank != NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "policy_bank=%s", mlfi->mlfi_policy_bank);
	if (amavisd_request(mlfi, "policy_bank", mlfi->mlfi_policy_bank) == -1)
	{
	    logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
		amavisd_socket, strerror(errno));
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* End of amavisd request */
    if (amavisd_request(mlfi, NULL, NULL) == -1) {
	logqidmsg(mlfi, LOG_ERR, "could not write to socket %s: %s",
	    amavisd_socket, strerror(errno));
	amavisd_close(mlfi);
	mlfi_setreply_tempfail(ctx);
	return SMFIS_TEMPFAIL;
    }

    logqidmsg(mlfi, LOG_DEBUG, "AMAVISD RESPONSE");

    /* Process response from amavisd */
    rstat = SMFIS_TEMPFAIL;
    while (amavisd_response(mlfi) != -1) {
	name = mlfi->mlfi_amabuf;

	/* Last response */
	if (*name == '\0') {
	    amavisd_close(mlfi);
	    return rstat;
	}

	/* Get name and value */
	/* <name>=<value> */
	if ((value = strchr(name, '=')) == NULL) {
	    logqidmsg(mlfi, LOG_ERR, "malformed line: %s", name);
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
	*value++ = '\0';

	/* AM.PDP protocol version */
	/* version_server=<value> */
	if (strcmp(name, "version_server") == 0) {
	    logqidmsg(mlfi, LOG_DEBUG, "%s=%s", name, value);
	    i = (int) strtol(value, &header, 10);
	    if (header != NULL && *header != '\0') {
		logqidmsg(mlfi, LOG_ERR, "malformed line %s=%s", name, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    if (i > AMPDP_VERSION) {
		logqidmsg(mlfi, LOG_ERR,
		   "incompatible AM.PDP protocol version %s", value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Add recipient */
	/* addrcpt=<value> */
	} else if (strcmp(name, "addrcpt") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    if (smfi_addrcpt(ctx, value) != MI_SUCCESS) {
		logqidmsg(mlfi, LOG_ERR, "could not add recipient %s", value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Delete recipient */
	/* delrcpt=<value> */
	} else if (strcmp(name, "delrcpt") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    if (smfi_delrcpt(ctx, value) != MI_SUCCESS) {
		logqidmsg(mlfi, LOG_ERR, "could not delete recipient %s",
		    value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Append header */
	/* addheader=<header> <value> */
	} else if (strcmp(name, "addheader") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    header = value;
	    if ((value = strchr(header, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s", name, header);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
#ifdef HAVE_SMFI_INSHEADER
	    if (smfi_insheader(ctx, INT_MAX, header, value) != MI_SUCCESS) {
#else
	    if (smfi_addheader(ctx, header, value) != MI_SUCCESS) {
#endif
		logqidmsg(mlfi, LOG_ERR, "could not append header %s: %s",
		    header, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Insert header */
	/* insheader=<index> <header> <value> */
	} else if (strcmp(name, "insheader") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    idx = value;
	    if ((value = strchr(idx, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s", name, idx);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
	    i = (int) strtol(idx, &header, 10);
	    if (header != NULL && *header != '\0') {
		logqidmsg(mlfi, LOG_ERR, "malformed line %s=%s %s",
		    name, idx, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    header = value;
	    if ((value = strchr(header, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s %s",
		    name, idx, header);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
#ifdef HAVE_SMFI_INSHEADER
	    if (smfi_insheader(ctx, i, header, value) != MI_SUCCESS) {
#else
	    if (smfi_addheader(ctx, header, value) != MI_SUCCESS) {
#endif
		logqidmsg(mlfi, LOG_ERR, "could not insert header %s %s: %s",
		    idx, header, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Change header */
	/* chgheader=<index> <header> <value> */
	} else if (strcmp(name, "chgheader") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    idx = value;
	    if ((value = strchr(idx, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s", name, idx);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
	    i = (int) strtol(idx, &header, 10);
	    if (header != NULL && *header != '\0') {
		logqidmsg(mlfi, LOG_ERR, "malformed line %s=%s %s",
		    name, idx, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    header = value;
	    if ((value = strchr(header, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s %s",
		    name, idx, header);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
	    if (smfi_chgheader(ctx, header, i, value) != MI_SUCCESS) {
		logqidmsg(mlfi, LOG_ERR, "could not change header %s %s: %s",
		    idx, header, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Delete header */
	/* delheader=<index> <header> */
	} else if (strcmp(name, "delheader") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    idx = value;
	    if ((value = strchr(idx, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s", name, idx);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
	    i = (int) strtol(idx, &header, 10);
	    if (header != NULL && *header != '\0') {
		logqidmsg(mlfi, LOG_ERR, "malformed line %s=%s %s",
		    name, idx, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    if (smfi_chgheader(ctx, value, i, NULL) != MI_SUCCESS) {
		logqidmsg(mlfi, LOG_ERR, "could not delete header %s %s:",
		    idx, value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

#ifdef HAVE_SMFI_QUARANTINE
	/* Quarantine message */
	/* quarantine=<reason> */
	} else if (strcmp(name, "quarantine") == 0) {
	    logqidmsg(mlfi, LOG_INFO, "%s=%s", name, value);
	    if (smfi_quarantine(ctx, value) != MI_SUCCESS) {
		logqidmsg(mlfi, LOG_ERR, "could not quarantine message (%s)",
		    value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
#endif

	/* Set response code */
	/* return_value=<value> */
	} else if (strcmp(name, "return_value") == 0) {
	    logqidmsg(mlfi, LOG_NOTICE, "%s=%s", name, value);
	    if (strcmp(value, "continue") == 0) {
		rstat = SMFIS_CONTINUE;
	    } else if (strcmp(value, "accept") == 0) {
		rstat = SMFIS_ACCEPT;
	    } else if (strcmp(value, "reject") == 0) {
		rstat = SMFIS_REJECT;
	    } else if (strcmp(value, "discard") == 0) {
		rstat = SMFIS_DISCARD;
	    } else if (strcmp(value, "tempfail") == 0) {
		rstat = SMFIS_TEMPFAIL;
	    } else {
		logqidmsg(mlfi, LOG_ERR, "unknown return value %s", value);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }

	/* Set SMTP reply */
	/* setreply=<rcode> <xcode> <value> */
	} else if (strcmp(name, "setreply") == 0) {
	    rcode = value;
	    if ((value = strchr(rcode, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s",
		    name, rcode);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
	    xcode = value;
	    if ((value = strchr(xcode, ' ')) == NULL) {
		logqidmsg(mlfi, LOG_ERR, "malformed line: %s=%s %s",
		    name, rcode, xcode);
		amavisd_close(mlfi);
		mlfi_setreply_tempfail(ctx);
		return SMFIS_TEMPFAIL;
	    }
	    *value++ = '\0';
	    /* smfi_setreply accept only 4xx and 5XX codes */
	    if (*rcode == '4' || *rcode == '5') {
		logqidmsg(mlfi, LOG_INFO, "%s=%s %s %s", name, rcode, xcode,
		    value);
		if (smfi_setreply(ctx, rcode, xcode, value) != MI_SUCCESS) {
		    logqidmsg(mlfi, LOG_ERR, "could not set reply %s %s %s",
			rcode, xcode, value);
		    amavisd_close(mlfi);
		    mlfi_setreply_tempfail(ctx);
		    return SMFIS_TEMPFAIL;
		}
	    } else {
		logqidmsg(mlfi, LOG_DEBUG, "%s=%s %s %s", name, rcode, xcode,
		    value);
	    }

	/* Exit code */
	/* exit_code=<value> */
	} else if (strcmp(name, "exit_code") == 0) {
	    /* ignore legacy exit_code */
	    logqidmsg(mlfi, LOG_DEBUG, "%s=%s", name, value);

	/* Unknown response */
	} else {
	    logqidmsg(mlfi, LOG_ERR, "unknown amavisd response %s=%s",
		name, value);
	    amavisd_close(mlfi);
	    mlfi_setreply_tempfail(ctx);
	    return SMFIS_TEMPFAIL;
	}
    }

    /* Amavisd response fail */
    logqidmsg(mlfi, LOG_ERR, "could not read from amavisd socket %s: %s",
	amavisd_socket, strerror(errno));
    logqidmsg(mlfi, LOG_DEBUG, "amavisd response line %s", mlfi->mlfi_amabuf);
    amavisd_close(mlfi);
    mlfi_setreply_tempfail(ctx);
    return SMFIS_TEMPFAIL;
}


/*
** MLFI_ABORT - Handle the current message's being aborted
**
** mlfi_abort() must reclaim any resources allocated on a per-message
** basis, and must be tolerant of being called between any two
** message-oriented callbacks
*/
sfsistat
mlfi_abort(SMFICTX *ctx)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);

    logqidmsg(mlfi, LOG_DEBUG, "ABORT");

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "mlfi_abort: context is not set");
	return SMFIS_CONTINUE;
    }

    /* Cleanup message data */
    mlfi_cleanup_message(mlfi);

    /* Continue processing */
    return SMFIS_CONTINUE;
}


/*
** MLFI_CLOSE - The current connection is being closed
** 
** mlfi_close() is always called once at the end of each connection
*/
sfsistat
mlfi_close(SMFICTX *ctx)
{
    struct	mlfiCtx *mlfi = MLFICTX(ctx);

    logqidmsg(mlfi, LOG_DEBUG, "CLOSE");

    /* Check milter private data */
    if (mlfi == NULL) {
	logqidmsg(mlfi, LOG_DEBUG, "mlfi_close: context is not set");
	return SMFIS_CONTINUE;
    }

    /* Release private data */
    mlfi_cleanup(mlfi);
    if (smfi_setpriv(ctx, NULL) != MI_SUCCESS) {
	/* NOTE: smfi_setpriv return MI_FAILURE when ctx is NULL */
	/* logqidmsg(NULL, LOG_ERR, "could not release milter context"); */
    }

    /* Continue processing */
    return SMFIS_CONTINUE;
}
