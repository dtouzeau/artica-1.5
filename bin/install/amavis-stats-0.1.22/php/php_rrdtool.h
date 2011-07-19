/*
 * php_rrdtool.h
 *
 * php4/5 rrdtool module.  
 *
 * Dale Walsh, <buildsmart@daleenterprise.com>,<buildsmart@daleenterprise.com>, 10/19/2005
 *
 * $Id: php_rrdtool.h,v 1.1.1.1 2006/02/06 10:21:20 walsh Exp $
 *
 */

#ifndef PHP_RRDTOOL_H
#define PHP_RRDTOOL_H

#ifdef PHP_WIN32
#ifdef PHP_RRDTOOL_EXPORTS
#define PHP_RRDTOOL_API __declspec(dllexport)
#else
#define PHP_RRDTOOL_API __declspec(dllimport)
#endif
#else
#define PHP_RRDTOOL_API
#endif

#if HAVE_RRDTOOL

extern zend_module_entry rrdtool_module_entry;
#define rrdtool_module_ptr &rrdtool_module_entry

#ifdef ZTS
#include "TSRM.h"
#endif

#define RRDTOOL_LOGO_GUID		"PHP25B1F7E8-916B-11D9-9A54-000A95AE92DA"

/* 
  	Declare any global variables you may need between the BEGIN
	and END macros here:     

ZEND_BEGIN_MODULE_GLOBALS(rrdtool)
	long  global_value;
	char *global_string;
ZEND_END_MODULE_GLOBALS(rrdtool)

 */

PHP_MINIT_FUNCTION(rrdtool);
PHP_MSHUTDOWN_FUNCTION(rrdtool);
PHP_MINFO_FUNCTION(rrdtool);

PHP_FUNCTION(rrd_graph);
PHP_FUNCTION(rrd_fetch);
PHP_FUNCTION(rrd_error);
PHP_FUNCTION(rrd_clear_error);
PHP_FUNCTION(rrd_update);
PHP_FUNCTION(rrd_last);
PHP_FUNCTION(rrd_create);
PHP_FUNCTION(rrdtool_info);
PHP_FUNCTION(rrdtool_logo_guid);

#ifdef ZTS
#define RRDTOOL_G(v) TSRMG(rrdtool_globals_id, zend_rrdtool_globals *, v)
#else
#define RRDTOOL_G(v) (rrdtool_globals.v)
#endif

#else

#define rrdtool_module_ptr NULL

#endif /* HAVE_RRDTOOL */

#define phpext_rrdtool_ptr rrdtool_module_ptr

#endif  /* PHP_RRDTOOL_H */
