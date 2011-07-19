dnl Helper Functions for the RRDtool configure.in script
dnl 
dnl this file gets included into aclocal.m4 when runnning aclocal
dnl

define(upcase,`echo $1 | tr '[a-z]' '[A-Z]'`)dnl

dnl CONFIGURE_PART(MESSAGE)
AC_DEFUN([CONFIGURE_PART],[
case $TERM in
	#   for the most important terminal types we directly know the sequences
	xterm|xterm*|vt220|vt220*)
		T_MD=`awk 'BEGIN { printf("%c%c%c%c", 27, 91, 49, 109); }' </dev/null 2>/dev/null`
		T_ME=`awk 'BEGIN { printf("%c%c%c", 27, 91, 109); }' </dev/null 2>/dev/null`
	;;
	vt100|vt100*|cygwin)
		T_MD=`awk 'BEGIN { printf("%c%c%c%c%c%c", 27, 91, 49, 109, 0, 0); }' </dev/null 2>/dev/null`
		T_ME=`awk 'BEGIN { printf("%c%c%c%c%c", 27, 91, 109, 0, 0); }' </dev/null 2>/dev/null`
	;;
	*)
		T_MD=''
		T_ME=''
	;;
esac

  AC_MSG_RESULT()
  AC_MSG_RESULT([${T_MD}$1${T_ME}])
])

AC_DEFUN([AS_CONFIG_NICE],[
  test -f $1 && mv $1 $1.old
  rm -f $1.old
  cat >$1<<EOF
#! /bin/sh
#
# Created by configure

EOF

  for var in CFLAGS CXXFLAGS CPPFLAGS LDFLAGS LIBS CC CXX; do
    eval val=\$$var
    if test -n "$val"; then
      echo "$var='$val' \\" >> $1
    fi
  done

  for arg in [$]0 "[$]@"; do
    echo "'[$]arg' \\" >> $1
  done
  echo '"[$]@"' >> $1
  chmod +x $1
])

dnl PHP_SHARED_MODULE(module-name, object-var, build-dir, cxx)
dnl
dnl Basically sets up the link-stage for building module-name
dnl from object_var in build-dir.
dnl
AC_DEFUN([PHP_SHARED_MODULE],[
  install_modules="install-modules"

  case $host_alias in
    *darwin*[)]
      suffix=so
      link_cmd='ifelse($4,,[$(CC)],[$(CXX)]) -dynamic -flat_namespace -bundle -undefined suppress $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS) $(LDFLAGS) -o [$]@ $(EXTRA_LDFLAGS) $($2) $(translit($1,a-z_-,A-Z__)_SHARED_LIBADD)'
      ;;
    *netware*[)]
      suffix=nlm
      link_cmd='$(LIBTOOL) --mode=link ifelse($4,,[$(CC)],[$(CXX)]) $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS) $(LDFLAGS) -o [$]@ -shared -export-dynamic -avoid-version -prefer-pic -module -rpath $(phplibdir) $(EXTRA_LDFLAGS) $($2) ifelse($1, php4lib, , -L$(top_builddir)/netware -lphp4lib) $(translit(ifelse($1, php4lib, $1, m4_substr($1, 3)),a-z_-,A-Z__)_SHARED_LIBADD)'
      ;;
    *[)]
      suffix=la
      link_cmd='$(LIBTOOL) --mode=link ifelse($4,,[$(CC)],[$(CXX)]) $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS) $(LDFLAGS) -o [$]@ -export-dynamic -avoid-version -prefer-pic -module -rpath $(phplibdir) $(EXTRA_LDFLAGS) $($2) $(translit($1,a-z_-,A-Z__)_SHARED_LIBADD)'
      ;;
  esac

  PHP_MODULES="$PHP_MODULES \$(phplibdir)/$1.$suffix"
  PHP_SUBST($2)
  cat >>Makefile.objects<<EOF
\$(phplibdir)/$1.$suffix: $3/$1.$suffix
	\$(LIBTOOL) --mode=install cp $3/$1.$suffix \$(phplibdir)

$3/$1.$suffix: \$($2) \$(translit($1,a-z_-,A-Z__)_SHARED_DEPENDENCIES)
	$link_cmd

EOF
])

dnl
dnl Set libtool variable
dnl
AC_DEFUN([PHP_SET_LIBTOOL_VARIABLE],[
  if test -z "$LIBTOOL"; then
    LIBTOOL='$(SHELL) $(top_builddir)/libtool $1'
  else
    LIBTOOL="$LIBTOOL $1"
  fi
])

AC_DEFUN([PHP_SHLIB_SUFFIX_NAME],[
  PHP_SUBST(SHLIB_SUFFIX_NAME)
  SHLIB_SUFFIX_NAME=so
  case $host_alias in
  *hpux*[)]
	SHLIB_SUFFIX_NAME=sl
	;;
  *darwin*[)]
	SHLIB_SUFFIX_NAME=dylib
	;;
  esac
])

AC_DEFUN([PHP_ARG_ANALYZE_EX],[
ext_output="yes, shared"
ext_shared=yes
case [$]$1 in
shared,*[)]
  $1=`echo "[$]$1"|sed 's/^shared,//'`
  ;;
shared[)]
  $1=yes
  ;;
no[)]
  ext_output=no
  ext_shared=no
  ;;
*[)]
  ext_output=yes
  ext_shared=no
  ;;
esac

])

AC_DEFUN([PHP_ARG_ANALYZE],[
ifelse([$3],yes,[PHP_ARG_ANALYZE_EX([$1])],[ext_output=ifelse([$]$1,,no,[$]$1)])
ifelse([$2],,,[AC_MSG_RESULT([$ext_output])])
])

dnl
dnl PHP_ARG_WITH(arg-name, check message, help text[, default-val[, extension-or-not]])
dnl Sets PHP_ARG_NAME either to the user value or to the default value.
dnl default-val defaults to no.  This will also set the variable ext_shared,
dnl and will overwrite any previous variable of that name.
dnl If extension-or-not is yes (default), then do the ENABLE_ALL check and run
dnl the PHP_ARG_ANALYZE_EX.
dnl
AC_DEFUN([PHP_ARG_WITH],[
AS_REAL_ARG_WITH([$1],[$2],[$3],[$4],PHP_[]translit($1,a-z0-9-,A-Z0-9_),[ifelse($5,,yes,$5)])
])

AC_DEFUN([AS_REAL_ARG_WITH],[
ifelse([$2],,,[AC_MSG_CHECKING([$2])])
AC_ARG_WITH($1,[$3],$5=[$]withval,
[
  $5=ifelse($4,,no,$4)

])
PHP_ARG_ANALYZE($5,[$2],$6)
])

dnl
dnl AS_CHECK_FUNC(func, ...)
dnl This macro checks whether 'func' or '__func' exists
dnl in the default libraries and as a fall back in the specified library.
dnl Defines HAVE_func and HAVE_library if found and adds the library to LIBS.
dnl
AC_DEFUN([AS_CHECK_FUNC],[
  unset ac_cv_func_$1
  unset ac_cv_func___$1
  unset found
  
  AC_CHECK_FUNC($1, [found=yes],[ AC_CHECK_FUNC(__$1,[found=yes],[found=no]) ])

  case $found in
  yes[)] 
    AS_DEF_HAVE($1)
    ac_cv_func_$1=yes
  ;;
  ifelse($#,1,,[
    *[)] AS_CHECK_FUNC_LIB($@) ;;
  ])
  esac
])

dnl -------------------------------------------------------------------------
dnl Library/function existance and build sanity checks
dnl -------------------------------------------------------------------------

dnl
dnl PHP_CHECK_LIBRARY(library, function [, action-found [, action-not-found [, extra-libs]]])
dnl
dnl Wrapper for AC_CHECK_LIB
dnl
AC_DEFUN([PHP_CHECK_LIBRARY], [
  save_old_LDFLAGS=$LDFLAGS
  ac_stuff="$5"
  
  save_ext_shared=$ext_shared
  ext_shared=yes
  PHP_EVAL_LIBLINE([$]ac_stuff, LDFLAGS)
  AC_CHECK_LIB([$1],[$2],[
    LDFLAGS=$save_old_LDFLAGS
    ext_shared=$save_ext_shared
    $3
  ],[
    LDFLAGS=$save_old_LDFLAGS
    ext_shared=$save_ext_shared
    unset ac_cv_lib_$1[]_$2
    $4
  ])dnl
])

dnl PHP_DEFINE(WHAT[, value[, directory]])
dnl
dnl Creates builddir/include/what.h and in there #define WHAT value
dnl
AC_DEFUN([PHP_DEFINE],[
  [echo "#define ]$1[]ifelse([$2],,[ 1],[ $2])[" > ]ifelse([$3],,[include],[$3])[/php_]translit($1,A-Z,a-z)[.h]
])

dnl
dnl PHP_ADD_LIBRARY_WITH_PATH(library, path[, shared-libadd])
dnl
dnl add a library to the link line and path to linkpath/runpath.
dnl if shared-libadd is not empty and $ext_shared is yes,
dnl shared-libadd will be assigned the library information
dnl
AC_DEFUN([PHP_ADD_LIBRARY_WITH_PATH],[
ifelse($3,,[
  if test -n "$2"; then
    PHP_ADD_LIBPATH($2)
  fi
  PHP_ADD_LIBRARY($1)
],[
  if test "$ext_shared" = "yes"; then
    $3="-l$1 [$]$3"
    if test -n "$2"; then
      PHP_ADD_LIBPATH($2,$3)
    fi
  else
    PHP_ADD_LIBRARY_WITH_PATH($1,$2)
  fi
])
])

dnl
dnl PHP_ADD_LIBRARY(library[, append[, shared-libadd]])
dnl
dnl add a library to the link line
dnl
AC_DEFUN([PHP_ADD_LIBRARY],[
  _PHP_ADD_LIBRARY_SKELETON([$1],[$2],[$3],[PHP_ADD_LIBRARY],[LIBS])
])

dnl
dnl PHP_ADD_INCLUDE(path [,before])
dnl
dnl add an include path. 
dnl if before is 1, add in the beginning of INCLUDES.
dnl
AC_DEFUN([PHP_ADD_INCLUDE],[
  if test "$1" != "/usr/include"; then
    PHP_EXPAND_PATH($1, ai_p)
    PHP_RUN_ONCE(INCLUDEPATH, $ai_p, [
      if test "$2"; then
        INCLUDES="-I$ai_p $INCLUDES"
      else
        INCLUDES="$INCLUDES -I$ai_p"
      fi
    ])
  fi
])

dnl internal, don't use
AC_DEFUN([_PHP_X_ADD_LIBRARY],[dnl
  ifelse([$2],,$3="-l$1 [$]$3", $3="[$]$3 -l$1") dnl
])dnl
dnl
dnl internal, don't use
AC_DEFUN([_PHP_ADD_LIBRARY_SKELETON],[
  case $1 in
  c|c_r|pthread*[)] ;;
  *[)] ifelse($3,,[
    _PHP_X_ADD_LIBRARY($1,$2,$5)
  ],[
    if test "$ext_shared" = "yes"; then
      _PHP_X_ADD_LIBRARY($1,$2,$3)
    else
      $4($1,$2)
    fi
  ]) ;;
  esac
])dnl
dnl
dnl
dnl PHP_RUN_ONCE(namespace, variable, code)
dnl
dnl execute code, if variable is not set in namespace
dnl
AC_DEFUN([PHP_RUN_ONCE],[
  changequote({,})
  unique=`echo $2|$SED 's/[^a-zA-Z0-9]/_/g'`
  changequote([,])
  cmd="echo $ac_n \"\$$1$unique$ac_c\""
  if test -n "$unique" && test "`eval $cmd`" = "" ; then
    eval "$1$unique=set"
    $3
  fi
])

dnl
dnl PHP_EXPAND_PATH(path, variable)
dnl
dnl expands path to an absolute path and assigns it to variable
dnl
AC_DEFUN([PHP_EXPAND_PATH],[
  if test -z "$1" || echo "$1" | grep '^/' >/dev/null ; then
    $2=$1
  else
    changequote({,})
    ep_dir="`echo $1|$SED 's%/*[^/][^/]*/*$%%'`"
    changequote([,])
    ep_realdir="`(cd \"$ep_dir\" && pwd)`"
    $2="$ep_realdir/`basename \"$1\"`"
  fi
])

dnl
dnl PHP_EVAL_LIBLINE(libline, SHARED-LIBADD)
dnl
dnl Use this macro, if you need to add libraries and or library search
dnl paths to the PHP build system which are only given in compiler
dnl notation.
dnl
AC_DEFUN([PHP_EVAL_LIBLINE],[
  for ac_i in $1; do
    case $ac_i in
    -pthread[)]
      if test "$ext_shared" = "yes"; then
        $2="[$]$2 -pthread"
      else
        PHP_RUN_ONCE(EXTRA_LDFLAGS, [$ac_i], [EXTRA_LDFLAGS="$EXTRA_LDFLAGS $ac_i"])
      fi
    ;;
    -l*[)]
      ac_ii=`echo $ac_i|cut -c 3-`
      PHP_ADD_LIBRARY($ac_ii,1,$2)
    ;;
    -L*[)]
      ac_ii=`echo $ac_i|cut -c 3-`
      PHP_ADD_LIBPATH($ac_ii,$2)
    ;;
    esac
  done
])

dnl
dnl PHP_EVAL_INCLINE(headerline)
dnl
dnl Use this macro, if you need to add header search paths to the PHP
dnl build system which are only given in compiler notation.
dnl
AC_DEFUN([PHP_EVAL_INCLINE],[
  for ac_i in $1; do
    case $ac_i in
    -I*[)]
      ac_ii=`echo $ac_i|cut -c 3-`
      PHP_ADD_INCLUDE($ac_ii)
    ;;
    esac
  done
])

dnl internal, don't use
AC_DEFUN([_PHP_ADD_LIBPATH_GLOBAL],[
  PHP_RUN_ONCE(LIBPATH, $1, [
    test -n "$ld_runpath_switch" && LDFLAGS="$LDFLAGS $ld_runpath_switch$1"
    LDFLAGS="$LDFLAGS -L$1"
    PHP_RPATHS="$PHP_RPATHS $1"
  ])
])dnl
dnl
dnl
dnl PHP_ADD_LIBPATH(path [, SHARED-LIBADD])
dnl
dnl Adds a path to linkpath/runpath (LDFLAGS)
dnl
AC_DEFUN([PHP_ADD_LIBPATH],[
  if test "$1" != "/usr/$PHP_LIBDIR" && test "$1" != "/usr/lib"; then
    PHP_EXPAND_PATH($1, ai_p)
    ifelse([$2],,[
      _PHP_ADD_LIBPATH_GLOBAL([$ai_p])
    ],[
      if test "$ext_shared" = "yes"; then
        $2="$ld_runpath_switch$ai_p -L$ai_p [$]$2"
      else
        _PHP_ADD_LIBPATH_GLOBAL([$ai_p])
      fi
    ])
  fi
])

dnl
dnl PHP_SUBST(varname)
dnl
dnl Adds variable with it's value into Makefile, e.g.:
dnl CC = gcc
dnl
AC_DEFUN([PHP_SUBST],[
  PHP_VAR_SUBST="$PHP_VAR_SUBST $1"
])

dnl
dnl PHP_SUBST_OLD(varname)
dnl
dnl Same as PHP_SUBST() but also substitutes all @VARNAME@
dnl instances in every file passed to AC_OUTPUT()
dnl
AC_DEFUN([PHP_SUBST_OLD],[
  PHP_SUBST($1)
  AC_SUBST($1)
])

dnl -------------------------------------------------------------------------
dnl Build system base macros
dnl -------------------------------------------------------------------------

dnl
dnl PHP_CANONICAL_HOST_TARGET
dnl
AC_DEFUN([PHP_CANONICAL_HOST_TARGET],[
  AC_REQUIRE([AC_CANONICAL_HOST])dnl
  AC_REQUIRE([AC_CANONICAL_TARGET])dnl
  dnl Make sure we do not continue if host_alias is empty.
  if test -z "$host_alias" && test -n "$host"; then
    host_alias=$host
  fi
  if test -z "$host_alias"; then
    AC_MSG_ERROR([host_alias is not set!])
  fi
])

dnl -------------------------------------------------------------------------
dnl Platform characteristics checks
dnl -------------------------------------------------------------------------

dnl
dnl PHP_SHLIB_SUFFIX_NAMES
dnl
dnl Determines link library suffix SHLIB_SUFFIX_NAME
dnl which can be: .so, .sl or .dylib
dnl
dnl Determines shared library suffix SHLIB_DL_SUFFIX_NAME
dnl suffix can be: .so or .sl
dnl
AC_DEFUN([PHP_SHLIB_SUFFIX_NAMES],[
 AC_REQUIRE([PHP_CANONICAL_HOST_TARGET])dnl
 PHP_SUBST_OLD(SHLIB_SUFFIX_NAME)
 PHP_SUBST_OLD(SHLIB_DL_SUFFIX_NAME)
 SHLIB_SUFFIX_NAME=so
 SHLIB_DL_SUFFIX_NAME=$SHLIB_SUFFIX_NAME
 case $host_alias in
 *hpux*[)]
   SHLIB_SUFFIX_NAME=sl
   SHLIB_DL_SUFFIX_NAME=sl
   ;;
 *darwin*[)]
   SHLIB_SUFFIX_NAME=dylib
   SHLIB_DL_SUFFIX_NAME=so
   ;;
 esac
])

