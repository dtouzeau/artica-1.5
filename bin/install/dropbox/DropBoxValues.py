#!/usr/bin/python

import ctypes, base64, pickle, sqlite3, os 

is_windows = True
try:
  # try to get Windows path first
  SHGetFolderPath = ctypes.windll.shell32.SHGetFolderPathW
except AttributeError:
  is_windows = False
  
if is_windows:
  from ctypes.wintypes import HWND, HANDLE, DWORD, LPCWSTR, MAX_PATH
  SHGetFolderPath.argtypes = [HWND, ctypes.c_int, HANDLE, DWORD, LPCWSTR]
  path_buffer = ctypes.wintypes.create_unicode_buffer(MAX_PATH)
  # 26 is CSIDL_APPDATA, the code for retrieving the user's Application Data folder
  SHGetFolderPath(0, 26, 0, 0, path_buffer)
  dropbox_db_path = path_buffer.value + '\Dropbox\dropbox.db'
else:
  dropbox_db_path = os.path.expanduser('~/.dropbox/dropbox.db')

db = sqlite3.connect(dropbox_db_path)
cur = db.cursor()
cur.execute('select key, value from config order by key')
for row in cur:
        print row[0], '=', pickle.loads(base64.b64decode(row[1])) if row[1] != None else row[1]
db.close()
