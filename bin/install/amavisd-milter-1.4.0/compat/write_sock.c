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
 * $Id: write_sock.c,v 1.2 2006/10/17 20:35:03 reho Exp $
 */

#include "compat.h"

#include <errno.h>


/*
** WRITE_SOCK - write N bytes to socket
*/
ssize_t
write_sock(int sd, void *buf, size_t nbytes, long timeout)
{
    int		ret;
    char       *b = (char *) buf;
    fd_set	wfds;
    size_t	n = 0;
    ssize_t	m = 0;
    struct	timeval tv;

    /* Set timeout */
    tv.tv_sec = timeout;
    tv.tv_usec = 0;

    /* Check socket descriptor */
    if (sd >= (int) FD_SETSIZE) {
	/* sd is larger than FD_SETSIZE */
	errno = EBADF;
	return -1;
    }

    /* Write N bytes to socket */
    while (n < nbytes) {
	FD_ZERO(&wfds);
	FD_SET((unsigned int)sd, &wfds);

	/* Wait for socket */
	ret = select(sd + 1, NULL, &wfds, NULL, &tv);
	if (ret == -1) {
	    if (errno == EINTR) {
		/* A signal was delivered, continue */
		continue;
	    } else {
		/* An error occured */
		return -1;
	    }
	} else if (ret == 0) {
	    /* Timeout */
	    errno = ETIMEDOUT;
	    return -1;
	}

	/* Write data to socket */
	m = write(sd, b, nbytes - n);
	if (m == -1) {
	    if (errno == EINTR) {
		/* A signal was delivered, continue */
		continue;
	    } else {
		/* An error occured */
		return -1;
	    }
	} else {
	    /* Write data */
	    n += m;
	    b += m;
	}
    }

    /* Return number of bytes */
    return nbytes;
}
