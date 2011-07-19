dnl $Id: acinclude.m4,v 1.9 2008/11/10 00:45:09 reho Exp $

dnl Enables debug output and debug symbols
AC_DEFUN([ACX_ENABLE_DEBUG],
[
  dnl Configure --enable-debug option
  AC_ARG_ENABLE(debug,[  --enable-debug          enables debug output and debug symbols @<:@default=no@:>@],
  [
   if test $enableval = "no"
     then
       enable_debug="no"
     else
       enable_debug="yes"
   fi
  ],
  [
    enable_debug="no"
  ])

  dnl This prevents stupid AC_PROG_CC to add "-g" to the default CFLAGS
  CFLAGS=" $CFLAGS"

  AC_REQUIRE([AC_PROG_CC])

  if test "$GCC" = "yes";
    then
      if test "$enable_debug" = "yes"
        then
          CFLAGS="-g -O2 $CFLAGS"
          CFLAGS="-W -Wall -ansi -pedantic -Wbad-function-cast -Wcast-align $CFLAGS"
          CFLAGS="-Wcast-qual -Wchar-subscripts -Winline -Wmissing-prototypes $CFLAGS"
          CFLAGS="-Wmissing-declarations -Wnested-externs -Wpointer-arith $CFLAGS"
          CFLAGS="-Wredundant-decls -Wshadow -Wstrict-prototypes -Wwrite-strings $CFLAGS"
        else
          CFLAGS="-O2 -DNDEBUG $CFLAGS"
      fi
  fi

  AC_LANG_C
])

dnl Set local state directory
AC_DEFUN([AC_LOCAL_STATE_DIR],
[
  if test "$localstatedir" = '${prefix}/var';
    then
      AC_SUBST([localstatedir], ['/var/amavis'])
  fi

  test "x$prefix" = xNONE && prefix_NONE=yes && prefix="$ac_default_prefix"
  test "x$exec_prefix" = xNONE && exec_prefix_NONE=yes && exec_prefix="${prefix}"

  eval LOCALSTATEDIR=\""$localstatedir"\"
  dnl In Autoconf 2.60, ${datadir} refers to ${datarootdir}, which in turn
  dnl refers to ${prefix}.  Thus we have to use `eval' twice.
  eval LOCALSTATEDIR=\""$LOCALSTATEDIR"\"

  AH_TEMPLATE([LOCAL_STATE_DIR], [Default location to store state files.])
  AC_DEFINE_UNQUOTED([LOCAL_STATE_DIR], ["$LOCALSTATEDIR"])

  test "$prefix_NONE" && prefix=NONE
  test "$exec_prefix_NONE" && exec_prefix=NONE
])

dnl Set working dir
AC_DEFUN([AC_WORKING_DIR],
[
  AH_TEMPLATE([WORKING_DIR], [Default location of working directory.])
  AC_ARG_WITH([working-dir],
    [  --with-working-dir@<:@=<DIR>@:>@  set working directory (default tmp)
                              SUBDIR    subdirectry of local state dirertory
                              /DIR      absolute path to working directory],
    [
      case "$with_working_dir" in
        no)  AC_DEFINE_UNQUOTED([WORKING_DIR], [LOCAL_STATE_DIR]);;
        yes) AC_DEFINE_UNQUOTED([WORKING_DIR], [LOCAL_STATE_DIR] "/tmp");;
        /*)  AC_DEFINE_UNQUOTED([WORKING_DIR], ["$with_working_dir"]);;
        *)   AC_DEFINE_UNQUOTED([WORKING_DIR], [LOCAL_STATE_DIR "/$with_working_dir"]);;
       esac
    ],
    [AC_DEFINE_UNQUOTED([WORKING_DIR], [LOCAL_STATE_DIR])
  ])
])

dnl Checks for working POSIX semaphores
AC_DEFUN([AC_CHECK_POSIX_SEMAPHORES],
[
  AC_MSG_CHECKING(if POSIX semaphores are working)
  AC_TRY_RUN([
  #include <semaphore.h>
  int main() {
    sem_t sem;
    int rc;
    rc = sem_init(&sem, 0, 0);
    return rc;
  }],
    AC_MSG_RESULT(yes),
    AC_MSG_ERROR(no),
    AC_MSG_ERROR(no)
  )
])

dnl Checks for d_namlen in struct dirent
AC_DEFUN([AC_STRUCT_DIRENT_D_NAMLEN],
[
  AC_MSG_CHECKING(for d_namlen in struct dirent)
  AC_TRY_COMPILE([
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
  ], [
  struct dirent dp;
  int X = dp.d_namlen;
  ],
    AC_DEFINE([HAVE_STRUCT_DIRENT_D_NAMLEN], 1,
      [Define to 1 if `struct direct' has a d_namlen element])
    AC_MSG_RESULT(yes),
    AC_MSG_RESULT(no)
  )
])

dnl Checks for AF_INET6
AC_DEFUN([AC_CHECK_AF_INET6],
[
  AC_CHECK_DECLS([AF_INET6],[],[],[
    #include <sys/types.h>
    #include <sys/socket.h>
  ])
])

dnl Checks for INET6_ADDRSTRLEN
AC_DEFUN([AC_CHECK_INET6_ADDRSTRLEN],
[
  AC_CHECK_DECLS([INET6_ADDRSTRLEN],[],[],[
    #include <sys/types.h>
    #include <sys/socket.h>
    #include <netinet/in.h>
  ])
])

dnl Checks for struct sockaddr_in6
AC_DEFUN([AC_CHECK_STRUCT_SOCKADDR_IN6],
[
  AC_CHECK_TYPES([struct sockaddr_in6],[],[],[
    #include <sys/types.h>
    #include <sys/socket.h>
    #include <netinet/in.h>
  ])
])
