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
 * $Id: log.c,v 1.6 2006/12/19 18:29:26 reho Exp $
 */

#include "amavisd-milter.h"

#include <stdarg.h>


/*
** LOGMSG - Print log message
*/
void
logmsg(int priority, const char *fmt, ...)
{
    char	buf[MAXLOGBUF];
    va_list	ap;

    if (priority <= debug_level || priority <= LOG_WARNING) {

	/* Format message */
	va_start(ap, fmt);
	(void) vsnprintf(buf, sizeof(buf), fmt, ap);
	va_end(ap);

	/* Write message to syslog */
	syslog(priority, "%s", buf);

	/* Print message to terminal */
	if (!daemonized) {
	    (void) fprintf(stdout, "%s\n", buf);
	}
    }
}


/*
** LOGQIDMSG - Log message with mail queue id
*/
void
logqidmsg(struct mlfiCtx *mlfi, int priority, const char *fmt, ...)
{
    char	buf[MAXLOGBUF];
    const char *p;
    va_list	ap;

    /* Format message */
    va_start(ap, fmt);
    (void) vsnprintf(buf, sizeof(buf), fmt, ap);
    va_end(ap);

    /* Print log message */
    if (mlfi != NULL) {
	if (mlfi->mlfi_qid != NULL) {
	    p = mlfi->mlfi_qid;
	} else if (mlfi->mlfi_prev_qid != NULL) {
	    p = mlfi->mlfi_prev_qid;
	} else if (mlfi->mlfi_client_host != NULL) {
	    p = mlfi->mlfi_client_host;
	} else {
	    p = "UNKNOWN";
	}
    } else {
	p = "NOQUEUE";
    }
    logmsg(priority, "%s: %s", p, buf);
}
