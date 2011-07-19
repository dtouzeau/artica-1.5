#!/usr/bin/python
import base64, pickle, sqlite3, os, string

dropbox_db_path = os.path.expanduser('~/.dropbox/dropbox.db')

db = sqlite3.connect(dropbox_db_path)
cur = db.cursor()
cur.execute('select key, value from config where key ="host_id" order by key')
for row in cur:
        print 'https://www.dropbox.com/cli_link?host_id=' + string.lstrip(pickle.loads(base64.b64decode(row[1]))) if row[1] != None else row[1]
db.close()
