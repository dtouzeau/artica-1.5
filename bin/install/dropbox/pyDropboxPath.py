#!/usr/bin/env python
helpMsg = '''DropboxPath - Change the name or move your DropBox folder.

Supported databases/schemas:
Database v0 "dropbox.db" schema v0:
 "stable" v0.7.x and below
Database v1 "config.db"  schema v1:
 "forum build" until v0.8.83 (may work with newer versions)
Of course, NO WARRANTIES, you CAN LOSE files, yadda yadda... Backup first.

Instructions:
- BACKUP YOUR DROPBOX
- Close the dropbox client (right click it, select exit)
- Use your OS tools to change the name of the DropBox Folder or
  move it to another location
- Check for errors on the status bar after any of these steps:
1-Check if the autodetected locations of DropBox database
  AND DropBox folder are right!
2-Put your new location of the DropBox folder in the 'New Location' field
3-Press the 'Save location' button
4-Close this script and open dropbox again.'''

version = '0.8.3'

'''CHANGELOG
2009-05-25.0.6
	Added darwin; should require someone to test
2009-05-29.0.7
	Using pickles as Arash mentioned
	Changed binascii to base64 module
	Using wx
	Minor misc changes
2009-06-01.0.8
	Working around a possible bug in sqlite3 by opening db after chdir()
2009-06-02.0.8.1
	The workaround was not complete, still opening sqlite with full path
2009-06-02.0.8.2
	Brown paper bag on me :(
	Three times is the charm for doing a simple bug fix.
2010-06-29.0.8.3
	Supporting 0.8 database version at least... copying from pyDropConflicts
'''

import os, sys, sqlite3
from base64 import b64encode, b64decode
from pickle import dumps, loads

try:
	import wx
except ImportError:
	print '''You do not have wxPyhton installed.
In Ubuntu:
	Search package manager and install,
	or use 'sudo apt-get install python-wxgtk2.8' if you feel advanced.
In Windows and OSX:
	Go to the wxpython website:
		http://www.wxpython.org/download.php#binaries
	And select the binary package relative to your arch and python version.
	(Please select one of the unicode versions)
'''
	raw_input('Press ENTER to exit...')
	sys.exit(1)


# dropbox path finder borrowed from my pyDropConflicts
def GetDbFolder():
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


def GetConn(dbfile):
	lastdir = os.getcwd()
	os.chdir(os.path.dirname(dbfile))
	connection = sqlite3.connect(os.path.basename(dbfile), isolation_level=None)
	os.chdir(lastdir)
	return connection


def ReadDbLocation(dbfile, dbfnver):
	connection = GetConn(dbfile)
	cursor = connection.cursor()
	if dbfnver == 0: # dropbox.db, old-style
		dbver = 0
	elif dbfnver == 1: # config.db, can be upgraded, lets check schema
		cursor.execute('SELECT value FROM config WHERE key="config_schema_version"')
		row = cursor.fetchone()
		dbver = row[0]
	# dup code now, but maybe someday it will be confusing
	if dbver == 0:
		cursor.execute('SELECT value FROM config WHERE key="dropbox_path"')
	elif dbver == 1:
		cursor.execute('SELECT value FROM config WHERE key="dropbox_path"')
	else:
		raise Exception('Unhandled DB schema version %d' % dbver)

	row = cursor.fetchone()
	cursor.close()
	connection.close()
	if row is None:
		if sys.platform == 'win32':
			import ctypes
			dll = ctypes.windll.shell32
			buf = ctypes.create_string_buffer(300)
			dll.SHGetSpecialFolderPathA(None, buf, 0x0005, False)
			dbfolder = os.path.join(buf.value,'My Dropbox')
		elif sys.platform in ('linux2','darwin'):
			dbfolder = os.path.join(os.environ['HOME'],'Dropbox')
		else:
			raise Exception('platform %s not known, please report' % sys.platform)
		#print 'No dropbox path defined in config, using default location %s' % dbfolder
	else:
		if dbver == 0: # always b64encoded
			dbfolder = loads(b64decode(row[0]))
		elif dbver == 1: # normal
			dbfolder = row[0]
		else:
			raise Exception('Unhandled DB schema version %d' % dbver)
	return (dbfolder, dbver)


def WriteDbLocation(dbfile, dbver, newloc):
	if dbver == 0:
		newpath = b64encode(dumps(newloc))
	elif dbver == 1:
		newpath = os.path.abspath(newloc)
	else:
		raise Exception('Unhandled DB schema version %d' % dbver)
	connection = GetConn(dbfile)
	cursor = connection.cursor()
	cursor.execute('REPLACE INTO config (key,value) VALUES ("dropbox_path",?)', (newpath,))
	cursor.close()
	connection.close()


class MainWindow(wx.Frame):
	DatabaseText = None
	CurrentFolderText = None
	NewFolderText = None
	NewFolderBtn = None
	SaveBtn = None
	dbfile = ''
	dbfnver = None
	dbver = None
	dbhost = ''
	curdbfolder = ''
	newdbfolder = ''
	def __init__(self, parent, title):
		wx.Frame.__init__(self, parent, wx.ID_ANY, title)
		self.CreateStatusBar()
		self.StatusBar.SetStatusText('Waiting.')

		panel = wx.Panel(self, wx.ID_ANY)
		self.panel = panel
		topsizer = wx.BoxSizer(wx.VERTICAL)
		colsizer = wx.GridBagSizer(hgap=5, vgap=5)

		w = wx.StaticText(panel, wx.ID_ANY, 'Dropbox Path Changer v%s' % version)
		w.SetFont(wx.Font(10, wx.SWISS, wx.NORMAL, wx.BOLD))
		topsizer.Add(w, 0, wx.CENTER|wx.ALL, 5)
		w = wx.StaticText(panel, wx.ID_ANY, 'READ THIS CAREFULLY')
		w.SetForegroundColour(wx.RED)
		topsizer.Add(w, 0, wx.CENTER|wx.ALL, 5)
		w = wx.TextCtrl(panel, wx.ID_ANY, helpMsg, size=(500,280), style=wx.TE_MULTILINE|wx.TE_READONLY)
		topsizer.Add(w, 0, wx.CENTER, 5)

		w = wx.StaticText(panel, wx.ID_ANY, 'Database location:')
		colsizer.Add(w, pos=(0,0), border=5)
		w = wx.StaticText(panel, wx.ID_ANY, 'None', style=wx.TE_READONLY)
		self.DatabaseText = w
		colsizer.Add(w, pos=(0,1), border=5)

		w = wx.StaticText(panel, wx.ID_ANY, 'Current location:')
		colsizer.Add(w, pos=(1,0), border=5)
		w = wx.StaticText(panel, wx.ID_ANY, 'None', style=wx.TE_READONLY)
		self.CurrentFolderText = w
		colsizer.Add(w, pos=(1,1), border=5)

		w = wx.StaticText(panel, wx.ID_ANY, 'New folder location:')
		colsizer.Add(w, pos=(2,0), border=5)
		w = wx.StaticText(panel, wx.ID_ANY, 'None', style=wx.TE_READONLY)
		self.NewFolderText = w
		colsizer.Add(w, pos=(2,1), border=5)
		w = wx.Button(panel, wx.ID_ANY, "Browse...")
		w.Disable()
		self.NewFolderBtn = w
		self.Bind(wx.EVT_BUTTON, self.OnBrowseDBFolder, w)
		colsizer.Add(w, pos=(2,2), border=5)

		topsizer.Add(colsizer, 0, wx.CENTER|wx.ALL, 5)
		w = wx.Button(panel, wx.ID_ANY, "Save new dropbox folder location")
		w.Disable()
		self.SaveBtn = w
		self.Bind(wx.EVT_BUTTON, self.OnSave, w)
		topsizer.Add(w, 0, wx.CENTER|wx.ALL, 5)
		self.DatabaseText.SetLabel('None')
		self.ReadDatabase()
		self.SetSize(wx.Size(510, 550))
		#panel.SetAutoLayout(True)
		panel.SetSizer(topsizer)
		#topsizer.Fit(panel)
		#topsizer.SetSizeHints(panel)
		panel.Layout()
		self.SetClientSize(panel.GetBestSize())


	def ReadDatabase(self):
		try:
			self.dbfile, self.dbfnver = GetDbFolder()
			self.dbhost = os.path.join(os.path.dirname(self.dbfile), 'host.db')
			self.curdbfolder, self.dbver = ReadDbLocation(self.dbfile, self.dbfnver)
			self.DatabaseText.SetLabel(self.dbfile + ' (v%d/%d)' % (self.dbfnver,self.dbver))
			self.CurrentFolderText.SetLabel(self.curdbfolder)
			self.StatusBar.SetStatusText('Database read successfully. Select new location.')
			self.NewFolderBtn.Enable()
			self.NewFolderBtn.SetFocus()
			self.NewFolderText.SetLabel('None')
			if not self.IsMaximized():
				self.SetClientSize(self.panel.GetBestSize())
		except Exception, e:
			self.StatusBar.SetStatusText('Got exception: '+e.message)
			self.NewFolderBtn.Disable()
			return


	def OnBrowseDBFolder(self, event):
		dlg = wx.DirDialog(self.panel, message='Select NEW Dropbox Folder location (move first!)',
			style=wx.DD_DIR_MUST_EXIST)
		if dlg.ShowModal() != wx.ID_OK:
			return
		self.newdbfolder = dlg.GetPath()
		dlg.Destroy()
		if os.path.isdir(self.newdbfolder):
			self.NewFolderText.SetLabel(self.newdbfolder)
			self.StatusBar.SetStatusText('New folder selected. Press the save button to change dropbox.')
			self.SaveBtn.Enable()
		else:
			self.SaveBtn.Disable()
		if not self.IsMaximized():
			self.SetClientSize(self.panel.GetBestSize())


	def OnSave(self, event):
		try:
			WriteDbLocation(self.dbfile, self.dbver, self.newdbfolder)
			self.curdbfolder, self.newdbfolder = self.newdbfolder, ''
			self.CurrentFolderText.SetLabel(self.curdbfolder)
			self.NewFolderText.SetLabel('None')
			self.NewFolderBtn.SetFocus()
			self.SaveBtn.Disable()
			msg = 'Location changed successfully!'
			if os.path.isfile(self.dbhost):
				try:
					os.unlink(self.dbhost)
					msg += ' (host.db removed)'
				except Exception, e:
					msg += ' (could not remove host.db)'
				self.dbhost = ''
			self.StatusBar.SetStatusText(msg)
		except Exception, e:
			self.StatusBar.SetStatusText('Got exception: '+e.message)


class MyApp(wx.App):
	def OnInit(self):
		frame = MainWindow(None, 'DropboxPath')
		self.SetTopWindow(frame)
		frame.Show(True)
		return True


app = MyApp(0)
#app = MyApp(redirect=True)
app.MainLoop()
