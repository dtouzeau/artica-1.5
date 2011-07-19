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
 * $Id: amavisd-milter.h,v 1.26 2008/11/10 00:45:10 reho Exp $
 */

#ifndef _AMAVISD_MILTER_H
#define _AMAVISD_MILTER_H

#include "compat.h"

/* AM.PDP protocol version */
#define AMPDP_VERSION	2

/* Maximum message buffers length */
#define MAXLOGBUF	1024	/* syslog message buffer */
#define MAXAMABUF	65536	/* amavisd communication buffer */
#define AMABUFCHUNK	2048	/* amavisd buffer reallocation step */

/* Timeouts */
#define SMFI_PROGRESS_TRIGGER	60	/* smfi_progress trigger */

struct mlfiCtx;

/* Address list */
struct mlfiAddress {
    struct	mlfiAddress *q_next;	/* next recipient */
    char	q_paddr[1];		/* recipient */
};

/* Milter private data structure */
struct mlfiCtx {
    char       *mlfi_hostname;		/* sendmail hostname */
    char       *mlfi_client_addr;	/* remote host address */
    char       *mlfi_client_host;	/* remote host name */
    char       *mlfi_helo;		/* remote host helo */
    char       *mlfi_protocol;		/* communication protocol */
    char       *mlfi_qid;		/* queue id */
    char       *mlfi_prev_qid;		/* previous queue id */
    char       *mlfi_from;		/* mail sender */
    struct	mlfiAddress *mlfi_rcpt;	/* mail recipients */
    char	mlfi_wrkdir[MAXPATHLEN];/* working directory */
    char	mlfi_fname[MAXPATHLEN]; /* mail file name */
    FILE       *mlfi_fp;		/* mail file handler */
    int		mlfi_max_sem_locked;	/* connections semaphore locked */
    char       *mlfi_amabuf;		/* amavisd communication buffer */
    size_t	mlfi_amabuf_length;	/* amavisd buffer length */
    int		mlfi_amasd;		/* amavisd socket descriptor */
    char       *mlfi_policy_bank;	/* policy bank names */
    int		mlfi_cr_flag;		/* CR at the end of the body chunk */
};

/* Get private data from libmilter */
#define		MLFICTX(ctx) ((struct mlfiCtx *)smfi_getpriv(ctx))

/* Milter description */
extern struct	smfiDesc smfilter;

/* Milter functions */
extern sfsistat	mlfi_connect(SMFICTX *, char *, _SOCK_ADDR *);
extern sfsistat	mlfi_helo(SMFICTX *, char *);
extern sfsistat	mlfi_envfrom(SMFICTX *, char **);
extern sfsistat	mlfi_envrcpt(SMFICTX *, char **);
extern sfsistat	mlfi_header(SMFICTX *, char *, char *);
extern sfsistat	mlfi_eoh(SMFICTX *);
extern sfsistat	mlfi_body(SMFICTX *, unsigned char *, size_t);
extern sfsistat	mlfi_eom(SMFICTX *);
extern sfsistat	mlfi_close(SMFICTX *);
extern sfsistat	mlfi_abort(SMFICTX *);

/* Global variables */
extern int	daemonize;		/* run as daemon */
extern int	daemonized;		/* is daemon */
extern int	debug_level;		/* max debug level */
extern int	max_conns;		/* max amavisd connections */
extern int	max_wait;		/* max wait for connection */
extern sem_t   *max_sem;		/* amavisd connections semaphore */
extern const char *pid_file;		/* pid file name */
extern const char *mlfi_socket;		/* sendmail milter socket */
#ifdef HAVE_SMFI_SETBACKLOG
extern int	mlfi_socket_backlog;	/* milter socket backlog */
#endif
extern long	mlfi_timeout;		/* connection timeout */
extern const char *amavisd_socket;	/* amavisd socket */
extern long	amavisd_timeout;	/* connection timeout */
extern const char *working_dir;		/* working ditectory name */
extern const char *delivery_care_of;	/* delivery mechanism */

/* Amavisd communication */
extern int	amavisd_connect(struct mlfiCtx *, struct sockaddr_un *,
		    time_t timeout);
extern int	amavisd_request(struct mlfiCtx *, const char *, const char *);
extern int	amavisd_response(struct mlfiCtx *);
extern void	amavisd_close(struct mlfiCtx *);

/* errno value if amavisd_connect() timed out. */
#ifdef HAVE_SEM_TIMEDWAIT
# define AMAVISD_CONNECT_TIMEDOUT_ERRNO	ETIMEDOUT
#else
# define AMAVISD_CONNECT_TIMEDOUT_ERRNO	EAGAIN
#endif

/* Log message */
extern void	logmsg(int, const char *, ...);
extern void	logqidmsg(struct mlfiCtx *, int, const char *, ...);

/* Macros */

#ifndef MAX
#define MAX(a,b) (((a)>(b))?(a):(b))
#endif

#ifndef MIN
#define MIN(a,b) (((a)<(b))?(a):(b))
#endif

#endif /* _AMAVISD_MILTER_H */
