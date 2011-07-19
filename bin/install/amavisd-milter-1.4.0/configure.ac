dnl $Id: configure.ac,v 1.45 2008/12/05 17:22:19 reho Exp $

AC_INIT(amavisd-milter, 1.4.0)
AC_PREREQ(2.62)

AC_CONFIG_SRCDIR(aclocal/acinclude.m4)
AC_CONFIG_AUX_DIR(aclocal)
AC_CONFIG_MACRO_DIR(aclocal)
AC_CONFIG_HEADERS([config.h])

AC_CANONICAL_BUILD
AC_CANONICAL_HOST
AC_CANONICAL_TARGET

AM_INIT_AUTOMAKE([-Wall -Werror foreign])
dnl AM_MAINTAINER_MODE

# Kludge to keep autoconf from adding -g -O2
CFLAGS=" $CFLAGS"

AC_PROG_INSTALL
AC_PROG_MAKE_SET
AC_PROG_RANLIB

AC_PROG_CC
AM_PROG_CC_C_O
ACX_ENABLE_DEBUG
ACX_PTHREAD([LIBS="$PTHREAD_LIBS $LIBS" CFLAGS="$CFLAGS $PTHREAD_CFLAGS"
  CC="$PTHREAD_CC"] AC_DEFINE(HAVE_PTHREAD, 1),
  AC_MSG_ERROR([no usable pthreads library found]))
AC_CHECK_FUNCS([sem_timedwait])

AC_TYPE_MODE_T
AC_TYPE_SIZE_T
AC_HEADER_DIRENT
AC_STRUCT_DIRENT_D_NAMLEN
AC_HEADER_STDC
AC_HEADER_STDBOOL
AC_HEADER_TIME
AC_STRUCT_TIMEZONE
AC_CHECK_HEADERS([arpa/inet.h ctype.h errno.h fcntl.h limits.h \
  netinet/in.h stdarg.h stdio.h stdlib.h string.h sys/param.h sys/time.h \
  sys/types.h sys/socket.h sys/stat.h sys/un.h syslog.h sysexits.h unistd.h],[],
  AC_MSG_ERROR([unable to find required header files]))

AC_CHECK_LIB(rt, sem_init, LIBS="$LIBS -lrt")
AC_CHECK_POSIX_SEMAPHORES

AC_CHECK_HEADERS([fts.h])
AC_CHECK_FUNCS([arc4random])
AC_REPLACE_FUNCS([daemon fts_open mkdtemp strlcpy])

AC_CHECK_FUNC([inet_ntop], [], [AC_SEARCH_LIBS(inet_ntop, [nsl])])

AC_CHECK_DIRFD

AX_PATH_MILTER([8.12], [LIBS="$MILTER_LIBS $LIBS"
  LDFLAGS="$MILTER_LDFLAGS $LDFLAGS" CPPFLAGS="$CPPFLAGS $MILTER_CPPFLAGS"],
  AC_MSG_ERROR([required milter library and header not found]))
AC_CHECK_FUNCS([smfi_insheader smfi_opensocket smfi_progress smfi_quarantine \
  smfi_setbacklog])

AC_EILSEQ
gl_EOVERFLOW

AC_CHECK_AF_INET6
AC_CHECK_INET6_ADDRSTRLEN
AC_CHECK_STRUCT_SOCKADDR_IN6

AC_CONFIG_FILES([Makefile amavisd-milter/Makefile compat/Makefile])
AC_CONFIG_FILES([autoconf.sh], [chmod +x autoconf.sh])

AC_LOCAL_STATE_DIR
AC_WORKING_DIR

AC_OUTPUT
