program rpmbuilder;

{$mode objfpc}{$H+}

uses
  Classes, SysUtils,strutils,baseunix,unix, builder,RegExpr;

var
   version      :string;
   DATE         :string;
   build        :Tbuilder;
   sourcefolder :string;
   cmdline      :string;
   FileList     :TStringList;
   i            :integer;
   SourcePath   :string;
   zRegExpr     :TRegExpr;
   spec         :TstringList;
   tmpdir       :string;
   FileName     :string;

begin
   build:=Tbuilder.Create;
   SourcePath:=ParamStr(1);
   if not FileExists(SourcePath) then begin
      writeln('Unable to stat ' + SourcePath);
   end;
   
   zRegExpr:=TRegExpr.Create;
   zRegExpr.Expression:='artica-([0-9]+)\.([0-9]+)\.([0-9]+)\.tgz';
   
      if not zRegExpr.Exec(ExtractFileName(SourcePath)) then begin
         writeln('Unable to extract version from ' +  ExtractFileName(SourcePath));
         exit;
      end;
   
    FileName:=zRegExpr.Match[1] + '.' + zRegExpr.Match[2] + zRegExpr.Match[3] + '.tar.bz2';
    writeln('version : ' + zRegExpr.Match[1] + '.' + zRegExpr.Match[2] + '(' + zRegExpr.Match[3]+ ')');
   spec:=TstringList.Create;
   tmpdir:='/tmp/' + zRegExpr.Match[1] + '.' + zRegExpr.Match[2] + '.' + zRegExpr.Match[3];
   forceDirectories(tmpdir);

spec.Add('Buildroot: /home/dtouzeau/source-export/artica-deb');
spec.Add('Name: artica-postfix');
spec.Add('Version: 1.1.020619');
spec.Add('Release: 2');
spec.Add('Summary: Web HTTPS interface for postfix administration.automatically install all Open Source eMail products within technical skills.Full features for postfix,cyrus,Openldap,mailman,fetchmail,users management with all securities features (anti-spam/rbl..).');
spec.Add('License: see /usr/share/doc/artica-postfix/copyright');
spec.Add('Distribution: Debian');
spec.Add('Group: Converted/base');
spec.Add('');
spec.Add('%define _rpmdir ../');
spec.Add('%define _rpmfilename %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm');
spec.Add('%define _unpackaged_files_terminate_build 0



cmdline:='/bin/cp '+ SourcePath +' /usr/src/rpm/SOURCES/';
writeln(cmdline);
fpsystem(cmdline);
cmdline:='tar -C '+ tmpdir  + ' -xf ' +SourcePath;
writeln(cmdline);
fpsystem(cmdline);
cmdline:='cd ' + tmpdir + ' && find .|sed ''s/\.\///;'' >/tmp/sources.list';
writeln(cmdline);
fpsystem(cmdline);
FileList:=TstringList.Create;
FileList.LoadFromFile('/tmp/sources.list');
for i:=0 to FileList.Count -1 do begin
  if FileList.Strings[i]<>'.' then begin
     FileList.Strings[i]:='"/' + FileList.Strings[i]  +'"';
     //spec.Add(FileList.Strings[i]);
  end;

end;


{fpsystem(cmdline);
cmdline:='tar -C '+ tmpdir  + ' -xf ' +SourcePath;
writeln(cmdline);
fpsystem(cmdline);
cmdline:='cd ' +tmpdir + ' && tar -cvf - * | bzip2 > /usr/src/rpm/SOURCES/' + FileName;
writeln(cmdline);
readln();
fpsystem(cmdline);}


spec.Add('%doc');
 spec.SaveToFile('/tmp/spc.spec');
 fpsystem(' rpmbuild -bb /tmp/spc.spec');

   
   //cmdline:='cd ' + sourcefolder + ' && find .|sed ''s/\.\///;'' >/tmp/sources.list';
   //writeln(cmdline);
   //fpsystem(cmdline);
   //FileList:=TstringList.Create;
   //FileList.LoadFromFile('/tmp/sources.list');
   
   writeln('Build files list........:OK');
   
   


end.

