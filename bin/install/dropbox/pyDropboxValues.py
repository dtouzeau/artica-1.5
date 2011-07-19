#!/usr/bin/env python
# Python code to read dropbox config values for both 0.7.x and 0.8.x
# Code blatantly 'stolen' from pyDropboxPath.py 0.8.3  :-)
# This file written by Andrew Scheller, 2010-10-14

import os
import sys
import sqlite3
from base64 import b64decode
from pickle import loads


def GetConfigDbFilename():
	if sys.platform == 'win32':
		assert os.environ.has_key('APPDATA'), Exception('APPDATA env variable not found')
		dbpath = os.path.join(os.environ['APPDATA'],'Dropbox')
	elif sys.platform in ('linux2','darwin'):
		assert os.environ.has_key('HOME'), Exception('HOME env variable not found')
		dbpath = os.path.join(os.environ['HOME'],'.dropbox')
	else: # FIXME other archs?
		raise Exception('platform %s not known, please report' % sys.platform)
	if os.path.isfile(os.path.join(dbpath,'config.db')):
		dbfn, dbfnver = os.path.join(dbpath,'config.db'), 1
	elif os.path.isfile(os.path.join(dbpath, 'dropbox.db')):
		dbfn, dbfnver = os.path.join(dbpath,'dropbox.db'), 0
	else:
		raise Exception('Dropbox database not found, is dropbox installed?')
	return (dbfn, dbfnver)


def GetConnection(dbfile):
	lastdir = os.getcwd()
	os.chdir(os.path.dirname(dbfile))
	connection = sqlite3.connect(os.path.basename(dbfile), isolation_level=None)
	os.chdir(lastdir)
	return connection


def GetDbVersion(dbfnver, connection):
	if dbfnver == 0: # dropbox.db, old-style
		dbver = 0
	elif dbfnver == 1: # config.db, can be upgraded, lets check schema
		cursor = connection.cursor()
		cursor.execute('SELECT value FROM config WHERE key="config_schema_version"')
		row = cursor.fetchone()
		cursor.close()
		dbver = row[0]
	return dbver


def GetDbKeys(connection):
	cursor = connection.cursor()
	cursor.execute('SELECT key FROM config')
	allkeys = set()
	for row in cursor:
		allkeys.add(row[0])
	cursor.close()
	return allkeys


def ReadDbValue(connection, dbver, dbkey):
	cursor = connection.cursor()
	# dup code now, but maybe someday it will be different
	if dbver == 0:
		cursor.execute('SELECT value FROM config WHERE key=?', (dbkey,))
	elif dbver == 1:
		cursor.execute('SELECT value FROM config WHERE key=?', (dbkey,))
	else:
		raise Exception('Unhandled DB schema version %d' % dbver)

	row = cursor.fetchone()
	cursor.close()
	if row is None:
		raise Exception('key %s not found in dropbox config database' % dbkey)
	else:
		dbvalue = row[0]
		if dbvalue:
			if dbver == 0: # always pickled then b64encoded
				value = loads(b64decode(dbvalue))
			elif dbver == 1: # some (non-string) values are still pickled
				if dbkey in set(('ns_p2p_key_map', 'recently_changed3', 'sandboxes', 'shadowed_proxy_password')):
					value = loads(dbvalue)
				else:
					value = dbvalue
			else:
				raise Exception('Unhandled DB schema version %d' % dbver)
		else:
			value = dbvalue
	return value


if __name__ == '__main__':
	dbfile, dbfnver = GetConfigDbFilename()
	connection = GetConnection(dbfile)
	dbver = GetDbVersion(dbfnver, connection)
	try:
		if len(sys.argv) == 1:
			for key in sorted(GetDbKeys(connection)):
				value = ReadDbValue(connection, dbver, key)
			 	print key, '=', value
		elif len(sys.argv) == 2:
			if sys.argv[1] == '_printkeys': # hopefully there's never a config value with the same name!
				print sorted(GetDbKeys(connection))
			elif sys.argv[1] == '_linkurl': # hopefully there's never a config value with the same name!
				value = ReadDbValue(connection, dbver, 'host_id')
				print "https://www.dropbox.com/cli_link?host_id=%s" % value
			else:
				value = ReadDbValue(connection, dbver, sys.argv[1])
				print value
	except Exception as detail:
		print "An error occured: %s" % detail
	finally:
		connection.close()

