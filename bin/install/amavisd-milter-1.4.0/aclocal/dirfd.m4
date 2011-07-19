# $Id: dirfd.m4,v 1.2 2008/10/28 23:27:28 reho Exp $

# Find out how to get the file descriptor associated with an open DIR*.

AC_DEFUN([AC_CHECK_DIRFD],
[
  AH_TEMPLATE([HAVE_DIRFD_AS_MACRO], [Define if dirfd() is a macro.])
  AC_REQUIRE([AC_PROG_CPP])
  AC_REQUIRE([AC_PROG_EGREP])
  AC_HEADER_DIRENT
  AC_CHECK_MEMBERS([DIR.d_fd, DIR.dd_fd, DIR.__dd_fd],
    [], [], [#include <dirent.h>])
  AC_CHECK_FUNCS([dirfd])
  AS_IF([test "$ac_cv_func_dirfd" = no],
    [AC_CACHE_CHECK([whether dirfd is a macro], [ac_cv_func_dirfd_macro],
      [AC_EGREP_CPP([func_dirfd_macro], [
#include <dirent.h>
#if defined(dirfd)
func_dirfd_macro
#endif],[ac_cv_func_dirfd_macro=yes],[ac_cv_func_dirfd_macro=no]
      )
    ])
  ])
  if test "$ac_cv_func_dirfd_macro" = yes; then
    AC_DEFINE([HAVE_DIRFD_AS_MACRO], [1])
  fi
])
