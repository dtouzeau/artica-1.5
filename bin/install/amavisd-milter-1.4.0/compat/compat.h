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
 * $Id: compat.h,v 1.10 2008/10/28 23:27:28 reho Exp $
 */

#ifndef _AMAVISD_COMPAT_H
#define _AMAVISD_COMPAT_H

#ifdef HAVE_CONFIG_H
# include <config.h>
#endif

#include <errno.h>
#include <fcntl.h>
#include <libmilter/mfapi.h>
#include <libmilter/mfdef.h>
#include <limits.h>
#include <semaphore.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/param.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <sys/un.h>
#include <syslog.h>
#include <unistd.h>

#if HAVE_STDBOOL_H
# include <stdbool.h>
#else
# if ! HAVE__BOOL
#  ifdef __cplusplus
typedef bool _Bool;
#  else
typedef unsigned char _Bool;
#  endif
# endif
# define bool _Bool
# define false 0
# define true 1
# define __bool_true_false_are_defined 1
#endif

#if HAVE_DIRENT_H
# include <dirent.h>
#else
# define dirent direct
# if HAVE_SYS_NDIR_H
#  include <sys/ndir.h>
# endif
# if HAVE_SYS_DIR_H
#  include <sys/dir.h>
# endif
# if HAVE_NDIR_H
#  include <ndir.h>
# endif
#endif

#if !defined(HAVE_DIRFD) && !defined(HAVE_DIRFD_AS_MACRO)
# if defined(HAVE_DIR_D_FD)
#  define dirfd(_d) ((_d)->d_fd)
# elif defined(HAVE_DIR_DD_FD)
#  define dirfd(_d) ((_d)->dd_fd)
# elif defined(HAVE_DIR___DD_FD)
#  define dirfd(_d) ((_d)->__dd_fd)
# else
#  error cannot figure out how to turn a DIR * into a fd
# endif
#endif

#if ! defined(MIN)
# define MIN(a, b)	((a) < (b) ? (a) : (b))
#endif
#if ! defined(MAX)
# define MAX(a, b)	((a) < (b) ? (b) : (a))
#endif

#if ! HAVE_DAEMON
# ifndef _PATH_DEVNULL
#  define _PATH_DEVNULL "/dev/null"
# endif
/* Run detached from the controlling terminal */
extern int     daemon(int, int);
#endif

#if HAVE_FTS_H
# include <fts.h>
#else
# include "fts_compat.h"
#endif

#if TIME_WITH_SYS_TIME
# include <sys/time.h>
# include <time.h>
#else
# if HAVE_SYS_TIME_H
#  include <sys/time.h>
# else
#  include <time.h>
# endif
#endif

#if ! HAVE_MKDTEMP
/* Make temporary directory */
extern char    *mkdtemp(char *);
#endif

#if ! HAVE_STRLCPY
/* String copy */
extern size_t	strlcpy(char *, const char *, size_t);
#endif

/* Secure socket handling */
extern ssize_t	read_sock(int, void *, size_t, long);
extern ssize_t	write_sock(int, void *, size_t, long);

#endif /* _AMAVISD_COMPAT_H */
