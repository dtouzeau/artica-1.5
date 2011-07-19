/*
 *
 * php_rrdtool.c
 *
 *	PHP interface to RRD Tool. (for php4/zend)
 *
 *
 *       Joe Miller, <joeym@ibizcorp.com>, <joeym@inficad.com> 
 *          iBIZ Technology Corp,  SkyLynx / Inficad Communications
 *          2/12/2000 & 7/18/2000
 *
 *       Jeffrey Wheat <jeff@cetlink.net> - 10/01/2002
 *       - Fixed to build with php-4.2.3
 *
 * See README, INSTALL, and USAGE files for more details.
 *
 * $Id: rrdtool.c,v 1.1.1.1 2002/02/26 10:21:20 oetiker Exp $
 *
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

/* PHP Includes */
#include "php.h"
#include "php_logos.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "SAPI.h"

/* rrdtool includes */
#include "php_rrdtool.h"
#include "rrdtool_logo.h"
#include <rrd.h>

#if HAVE_RRDTOOL

/* If you declare any globals in php_rrdtool.h uncomment this:
ZEND_DECLARE_MODULE_GLOBALS(rrdtool)
 */
 
function_entry rrdtool_functions[] = {
	PHP_FE(rrd_graph,				NULL)
	PHP_FE(rrd_fetch,				NULL)
	PHP_FE(rrd_error,				NULL)
	PHP_FE(rrd_clear_error,			NULL)
	PHP_FE(rrd_update,				NULL)
	PHP_FE(rrd_last,				NULL)
	PHP_FE(rrd_create,				NULL)
	PHP_FE(rrdtool_info,			NULL)
	PHP_FE(rrdtool_logo_guid,		NULL)
	{NULL, NULL, NULL}
};

zend_module_entry rrdtool_module_entry = {
	STANDARD_MODULE_HEADER,
	"rrdtool",
	rrdtool_functions,
	PHP_MINIT(rrdtool),
	PHP_MSHUTDOWN(rrdtool),
	NULL,
	NULL,
	PHP_MINFO(rrdtool),
	NO_VERSION_YET,
	STANDARD_MODULE_PROPERTIES,
};

#ifdef COMPILE_DL_RRDTOOL
ZEND_GET_MODULE(rrdtool)
#endif

#ifdef COMPILE_DL_RRDTOOL
#if HAVE_RRD_12X
#define PHP_RRD_VERSION_STRING "1.2.x extension"
#else
#define PHP_RRD_VERSION_STRING "1.0.x extension"
#endif
#else
#if HAVE_RRD_12X
#define PHP_RRD_VERSION_STRING "1.2.x bundled"
#else
#define PHP_RRD_VERSION_STRING "1.0.x bundled"
#endif
#endif

/* {{{ PHP_MINIT_FUNCTION */
PHP_MINIT_FUNCTION(rrdtool)
{
	if (INI_INT("expose_php") == 1) {
		php_register_info_logo(RRDTOOL_LOGO_GUID   , "image/gif", rrdtool_logo   , sizeof(rrdtool_logo));
	}	
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION */
PHP_MSHUTDOWN_FUNCTION(rrdtool)
{
	php_unregister_info_logo(RRDTOOL_LOGO_GUID);
	
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION */
PHP_MINFO_FUNCTION(rrdtool)
{
	php_info_print_box_start(1);
	if (INI_INT("expose_php") == 1) {
		PUTS("<a href=\"http://people.ee.ethz.ch/~oetiker/webtools/rrdtool/\" target=\"rrdtool\"><img border=\"0\" src=\"");
		if (SG(request_info).request_uri) {
			PUTS(SG(request_info).request_uri);
		}
		PUTS("?="RRDTOOL_LOGO_GUID"\" alt=\"ClamAV logo\" /></a>\n");
	}
	php_printf("<h1 class=\"p\">rrdtool Version %s</h1>\n", PHP_RRD_VERSION_STRING);
	php_info_print_box_end();
	php_info_print_table_start();
	php_info_print_table_row(2, "rrdtool support", "enabled");
	php_info_print_table_end();
}
/* }}} */

/* {{{ proto mixed rrd_graph(string file, array args_arr, int argc)
	Creates a graph based on options passed via an array */
PHP_FUNCTION(rrd_graph)
{
    char *file;
    zval *args, *entry, *p_calcpr;
    HashTable *args_arr;
    char **argv, **calcpr;
    long php_argc, argc;
    uint file_len;
    int xsize, ysize, i = 0;
    double ymin = 0.0, ymax = 0.0;
    

	if ( rrd_test_error() )
		rrd_clear_error();
    
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sal", &file, &file_len, &args, &php_argc) == FAILURE)
    {
        return;
    }
		if ( args->type != IS_ARRAY )
		{ 
			php_error(E_WARNING, "2nd Variable passed to rrd_graph is not an array!\n");
			RETURN_FALSE;
		}
        
    args_arr = args->value.ht;
    argc = php_argc + 3;
    argv = (char **) emalloc(argc * sizeof(char *));

    argv[0] = estrdup("dummy");
    argv[1] = estrdup("graph");
    argv[2] = estrndup(file, file_len);


		for (i = 3; i < argc; i++) 
		{
			zval **dataptr;

			if ( zend_hash_get_current_data(args_arr, (void *) &dataptr) == FAILURE )
				continue;

			entry = *dataptr;

			if ( entry->type != IS_STRING )
				convert_to_string(entry);

			argv[i] = estrdup(entry->value.str.val);

			if ( i < argc )
				zend_hash_move_forward(args_arr);
		}
   
		optind = 0; opterr = 0; 
#if HAVE_RRD_12X
		if ( rrd_graph(argc-1, &argv[1], &calcpr, &xsize, &ysize, NULL, &ymin, &ymax) != -1 )
		{
#else
		if ( rrd_graph(argc-1, &argv[1], &calcpr, &xsize, &ysize) != -1 )
		{
#endif
			array_init(return_value);
			add_assoc_long(return_value, "xsize", xsize);
			add_assoc_long(return_value, "ysize", ysize);

			MAKE_STD_ZVAL(p_calcpr);
			array_init(p_calcpr);
    
			if (calcpr)
			{
				for (i = 0; calcpr[i]; i++)
				{
					add_next_index_string(p_calcpr, calcpr[i], 1);
					free(calcpr[i]);
				}
				free(calcpr);
			}
			zend_hash_update(return_value->value.ht, "calcpr", sizeof("calcpr"), 
							(void *)&p_calcpr, sizeof(zval *), NULL);
		}
		else
		{
			RETVAL_FALSE;
		}
		for (i = 1; i < argc; i++)
			efree(argv[i]);

		efree(argv);
	return;
}
/* }}} */

/* {{{ proto mixed rrd_fetch(string file, array args_arr, int php_argc)
	Fetch info from an RRD file */
PHP_FUNCTION(rrd_fetch)
{
	char *file;
	zval *entry, *args;
	long php_argc;
	zval *p_start, *p_end, *p_step, *p_ds_cnt;
	HashTable *args_arr;
	zval *p_ds_namv, *p_data;
	int i, j, argc;
	time_t start, end;
	unsigned long step, ds_cnt;
	char **argv, **ds_namv; 
    uint file_len;
	rrd_value_t *data, *datap;
    
	if ( rrd_test_error() )
		rrd_clear_error();
    
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sal", &file, &file_len, &args, &php_argc) == FAILURE)
    {
        return;
    }

	if ( args->type != IS_ARRAY )
	{ 
		php_error(E_WARNING, "2nd Variable passed to rrd_fetch is not an array!\n");
		RETURN_FALSE;
	}

    args_arr = args->value.ht;

    argc = php_argc + 3;
    argv = (char **) emalloc(argc * sizeof(char *));

	argv[0] = "dummy";
	argv[1] = estrdup("fetch");
	argv[2] = estrndup(file, file_len);

	for (i = 3; i < argc; i++) 
	{
		zval **dataptr;

		if ( zend_hash_get_current_data(args_arr, (void *) &dataptr) == FAILURE )
			continue;

		entry = *dataptr;

		if ( entry->type != IS_STRING )
			convert_to_string(entry);

		argv[i] = estrdup(entry->value.str.val);

		if ( i < argc )
			zend_hash_move_forward(args_arr);
	}
  
	optind = 0; opterr = 0; 

	if ( rrd_fetch(argc-1, &argv[1], &start,&end,&step,&ds_cnt,&ds_namv,&data) != -1 )
	{
		array_init(return_value);
		add_assoc_long(return_value, "start", start);
		add_assoc_long(return_value, "end", end);
		add_assoc_long(return_value, "step", step);
		add_assoc_long(return_value, "ds_cnt", ds_cnt);

		MAKE_STD_ZVAL(p_ds_namv);
		MAKE_STD_ZVAL(p_data);
		array_init(p_ds_namv);
		array_init(p_data);
   
		if (ds_namv)
		{
			for (i = 0; i < ds_cnt; i++)
			{
				add_next_index_string(p_ds_namv, ds_namv[i], 1);
				free(ds_namv[i]);
			}
			free(ds_namv);
		}

		if (data)
		{
			datap = data;
 			for (i = start; i <= end; i += step)
				for (j = 0; j < ds_cnt; j++)
					add_next_index_double(p_data, *(datap++));
 
			free(data);
		}

		zend_hash_update(return_value->value.ht, "ds_namv", sizeof("ds_namv"), 
						(void *)&p_ds_namv, sizeof(zval *), NULL);
		zend_hash_update(return_value->value.ht, "data", sizeof("data"), 
						(void *)&p_data, sizeof(zval *), NULL);
	}
	else
	{
		RETVAL_FALSE;
	}
	for (i = 1; i < argc; i++)
		efree(argv[i]);

	efree(argv);

	return;
}
/* }}} */

/* {{{ proto string rrd_error(void)
	Get the error message set by the last rrd tool function call */
PHP_FUNCTION(rrd_error)
{
	char *msg;

	if ( rrd_test_error() )
	{
		msg = rrd_get_error();        

		RETVAL_STRING(msg, 1);
		rrd_clear_error();
	}
	else
		return;
}
/* }}} */

/* {{{ proto void rrd_clear_error(void)
	Clear the error set by the last rrd tool function call */
PHP_FUNCTION(rrd_clear_error)
{
	if ( rrd_test_error() )
		rrd_clear_error();

	return;
}
/* }}} */

/* {{{ proto int rrd_update(string file, string opt) 
	Update an RRD file with values specified */
PHP_FUNCTION(rrd_update)
{
	
	char *file, *opt;
    uint file_len, opt_len;
	char **argv;

	if ( rrd_test_error() )
		rrd_clear_error();

	if ( zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &file, &file_len, &opt, &opt_len) == SUCCESS )
    {
        return;
    }

	argv = (char **) emalloc(4 * sizeof(char *));

	argv[0] = "dummy";
	argv[1] = estrdup("update");
	argv[2] = estrndup(file, file_len);
	argv[2] = estrndup(opt, opt_len);

	optind = 0; opterr = 0;
	if ( rrd_update(3, &argv[1]) != -1 )
	{
		RETVAL_TRUE;
	}
	else
	{
		RETVAL_FALSE;
	}

	efree(argv[1]); efree(argv[2]); efree(argv[3]);
	efree(argv);

	return;
}
/* }}} */

/* {{{ proto int rrd_last(string file)
	Gets last update time of an RRD file */
PHP_FUNCTION(rrd_last)
{
	char *file;
	unsigned long retval;
    uint file_len;

	char **argv = (char **) emalloc(3 * sizeof(char *));
    
	if ( rrd_test_error() )
		rrd_clear_error();
    
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &file, &file_len) == SUCCESS)
	{

		argv[0] = "dummy";
		argv[1] = estrdup("last");
		argv[2] = estrndup(file, file_len);

		optind = 0; opterr = 0;
		retval = rrd_last(2, &argv[1]);

		efree(argv[2]);
		efree(argv[1]);
		efree(argv);
		RETVAL_LONG(retval);
	}
	else
	{
		WRONG_PARAM_COUNT;
	}
	return;
}
/* }}} */

/* {{{ proto int rrd_create(string file, array args_arr, int argc)
	Create an RRD file with the options passed (passed via array) */ 
PHP_FUNCTION(rrd_create)
{
	char *file;
	zval *entry, *args;
	long php_argc;
	char **argv;
	HashTable *args_arr;
	int argc, i;
    uint file_len;

	if ( rrd_test_error() )
		rrd_clear_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sal", &file, &file_len, &args, &php_argc) == FAILURE)
    {
        return;
    }

	if ( args->type != IS_ARRAY )
	{ 
		php_error(E_WARNING, "2nd Variable passed to rrd_create is not an array!\n");
		RETURN_FALSE;
	}

    args_arr = args->value.ht;

    argc = php_argc + 3;
    argv = (char **) emalloc(argc * sizeof(char *));

	zend_hash_internal_pointer_reset(args_arr);

	argv[0] = "dummy";
	argv[1] = estrdup("create");
	argv[2] = estrndup(file, file_len);

	for (i = 3; i < argc; i++) 
	{
		zval **dataptr;

		if ( zend_hash_get_current_data(args_arr, (void *) &dataptr) == FAILURE )
			continue;

		entry = *dataptr;

		if ( entry->type != IS_STRING )
			convert_to_string(entry);

		argv[i] = estrdup(entry->value.str.val);

		if ( i < argc )
			zend_hash_move_forward(args_arr);
	}
  
	optind = 0;  opterr = 0;

	if ( rrd_create(argc-1, &argv[1]) != -1 )
	{
		RETVAL_TRUE;
	}
	else
	{
		RETVAL_FALSE;
	}

	for (i = 1; i < argc; i++)
		efree(argv[i]);

	efree(argv);

	return;
}
/* }}} */

PHP_FUNCTION(rrdtool_info)
{

	if (ZEND_NUM_ARGS()!=0) {
		ZEND_WRONG_PARAM_COUNT();
		RETURN_FALSE;
	}

	PUTS("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"DTD/xhtml1-transitional.dtd\">\n");
	PUTS("<html>");
	PUTS("<head>\n");
	PUTS("<style type=\"text/css\"><!--");
	PUTS("body {background-color: #ffffff; color: #000000;}");
	PUTS("body, td, th, h1, h2 {font-family: sans-serif;}");
	PUTS("pre {margin: 0px; font-family: monospace;}");
	PUTS("a:link {color: #000099; text-decoration: none; background-color: #ffffff;}");
	PUTS("a:hover {text-decoration: underline;}");
	PUTS("table {border-collapse: collapse;}");
	PUTS(".center {text-align: center;}");
	PUTS(".center table { margin-left: auto; margin-right: auto; text-align: left;}");
	PUTS(".center th { text-align: center !important; }");
	PUTS("td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}");
	PUTS("h1 {font-size: 150%;}");
	PUTS("h2 {font-size: 125%;}");
	PUTS(".p {text-align: left;}");
	PUTS(".e {background-color: #ccccff; font-weight: bold; color: #000000;}");
	PUTS(".h {background-color: #9999cc; font-weight: bold; color: #000000;}");
	PUTS(".v {background-color: #cccccc; color: #000000;}");
	PUTS("i {color: #666666; background-color: #cccccc;}");
	PUTS("img {float: right; border: 0px;}");
	PUTS("hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}");
	PUTS("//--></style>");
	PUTS("<title>rrdtool_info()</title>");
	PUTS("</head>\n");
	PUTS("<body><div class=\"center\">\n");

	php_info_print_box_start(1);
	PUTS("<a href=\"http://people.ee.ethz.ch/~oetiker/webtools/rrdtool/\" target=\"rrdtool\"><img border=\"0\" src=\"");
	if (SG(request_info).request_uri) {
		PUTS(SG(request_info).request_uri);
	}
	PUTS("?="RRDTOOL_LOGO_GUID"\" alt=\"ClamAV logo\" /></a>\n");
	php_printf("<h1 class=\"p\">rrdtool Version %s</h1>\n", PHP_RRD_VERSION_STRING);
	php_info_print_box_end();
	php_info_print_table_start();
	php_info_print_table_row(2, "System", PHP_UNAME );
	php_info_print_table_row(2, "Build Date", __DATE__ " " __TIME__ );
	php_info_print_table_row(2, "rrdtool Support","Enabled");
	php_info_print_table_end();

	PUTS("<h2>RRDTOOL Copyright</h2>\n");
	php_info_print_box_start(0);
	PUTS("COPYRIGHT STATEMENT FOLLOWS THIS LINE</p>\n<blockquote>\n");
	PUTS("<p>Portions copyright 2005 - 2007 by Dale Walsh (buildsmart@daleenterprise.com).</p>\n");
	PUTS("<p>Portions relating to rrdtool 1999 - 2007 by Tobias Oetiker.</p>\n");
	php_info_print_box_end();
	PUTS("<h2>RRDTOOL License</h2>\n");
	php_info_print_box_start(0);
	PUTS("<p><b>Permission has been granted to copy, distribute and modify rrd in any context without fee, including a commercial application, provided that this notice is present in user-accessible supporting documentation. </b></p>");
	PUTS("<p>This does not affect your ownership of the derived work itself, and the intent is to assure proper credit for the authors of rrdtool, not to interfere with your productive use of rrdtool. If you have questions, ask. \"Derived works\" ");
	PUTS("includes all programs that utilize the library. Credit must be given in user-accessible documentation.</p>\n");
	PUTS("<p><b>This software is provided \"AS IS.\"</b> The copyright holders disclaim all warranties, either express or implied, including but not limited to implied warranties of merchantability and fitness for a particular purpose, ");
	PUTS("with respect to this code and accompanying documentation.</p>\n");
	php_info_print_box_end();
	PUTS("<h2>Special Thanks</h2>\n");
	php_info_print_box_start(0);
	PUTS("<p>Perl by Larry Wall");
	PUTS("<p>gd library by Thomas Boutell");
	PUTS("<p>gifcode from David Koblas");
	PUTS("<p>libpng by Glenn Randers-Pehrson / Andreas Eric Dilger / Guy Eric Schalnat");
	PUTS("<p>cgilib by Martin Schulze");
	PUTS("<p>zlib by Jean-loup Gailly and Mark Adler");
	PUTS("<p>Portions relating to php4 and php5 bindings, Dale Walsh (buildsmart@daleenterprise.com)");
	php_info_print_box_end();

	PUTS("</div></body></html>");
}
/* }}} */

PHP_FUNCTION(rrdtool_logo_guid)
{
	if (ZEND_NUM_ARGS() != 0) {
		WRONG_PARAM_COUNT;
	}

	RETURN_STRINGL(RRDTOOL_LOGO_GUID, sizeof(RRDTOOL_LOGO_GUID)-1, 1);
}
/* }}} */

#endif	/* HAVE_RRDTOOL */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
