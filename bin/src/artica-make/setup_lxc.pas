unit setup_lxc;
{$MODE DELPHI}
//{$mode objfpc}{$H+}
{$LONGSTRINGS ON}
//ln -s /usr/lib/libmilter/libsmutil.a /usr/local/lib/libsmutil.a
//apt-get install libmilter-dev
interface

uses
  Classes, SysUtils,strutils,RegExpr in 'RegExpr.pas',
  unix,IniFiles,setup_libs,distridetect,fetchmail,zsystem,BaseUnix,
  install_generic;

  type
  install_lxc=class


private
     libs:tlibs;
     mypid:string;
     distri:tdistriDetect;
     install:tinstall;
     source_folder,cmd:string;
     SYS:Tsystem;
    function WriteToFile(zText:string;TargetPath:string):boolean;



public
      constructor Create();
      procedure Free;
      procedure xinstall();
      procedure debian_template();
      procedure fedora_template();
END;

implementation

constructor install_lxc.Create();
begin
distri:=tdistriDetect.Create();
libs:=tlibs.Create;
install:=tinstall.Create;
source_folder:='';
sys:=Tsystem.Create();
 mypid:=IntToStr(fpgetpid);
end;
//#########################################################################################
procedure install_lxc.Free();
begin
  libs.Free;
end;
//#########################################################################################
procedure install_lxc.debian_template();
var
   LXCVpsDir:string;

begin

 if FileExists('/etc/artica-postfix/debian.template.install') then begin
    if SYS.PROCESS_EXIST(SYS.GET_PID_FROM_PATH('/etc/artica-postfix/debian.template.install')) then begin
       writeln('debian.template.install locked file');
        exit;
    end;
 end;
 WriteToFile(mypid,'/etc/artica-postfix/debian.template.install');
 if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
 if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('debian6-minimal-i386',true);
 LXCVpsDir:=SYS.GET_INFO('LXCVpsDir');
 if length(LXCVpsDir)<3 then LXCVpsDir:='/home/vps-servers';
 ForceDirectories(LXCVpsDir+'/templates');
 if not FileExists(source_folder) then begin
    writeln(source_folder,' no such file');
    fpsystem('/bin/rm /etc/artica-postfix/debian.template.install');
    exit;
 end;
 fpsystem('/bin/mv '+source_folder+' '+LXCVpsDir+'/templates/');
 fpsystem('/bin/rm /etc/artica-postfix/debian.template.install');
end;
//#########################################################################################
procedure install_lxc.fedora_template();
var
   LXCVpsDir:string;
begin
 if FileExists('/etc/artica-postfix/fedora.template.install') then begin
    if SYS.PROCESS_EXIST(SYS.GET_PID_FROM_PATH('/etc/artica-postfix/fedora.template.install')) then begin
       writeln('fedora.template.install locked file');
        exit;
    end;
 end;
 WriteToFile(mypid,'/etc/artica-postfix/fedora.template.install');

 if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);
 if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('debian6-minimal-i386',true);
 LXCVpsDir:=SYS.GET_INFO('LXCVpsDir');
 if length(LXCVpsDir)<3 then LXCVpsDir:='/home/vps-servers';
 ForceDirectories(LXCVpsDir+'/templates');
 if not FileExists(source_folder) then begin
    writeln(source_folder,' no such file');
    fpsystem('/bin/rm /etc/artica-postfix/fedora.template.install');
    exit;
 end;
 fpsystem('/bin/mv '+source_folder+' '+LXCVpsDir+'/templates/');
 fpsystem('/bin/rm /etc/artica-postfix/fedora.template.install');
end;
//#########################################################################################



procedure install_lxc.xinstall();
var
   ftech:tfetchmail;
   cmd:string;
   RegExpr:TRegExpr;
   kernM,KernMin,KernBuild:integer;
   l:Tstringlist;
   distri:tdistriDetect;
begin
if DirectoryExists(ParamStr(2)) then source_folder:=ParamStr(2);

install.INSTALL_STATUS('APP_LXC',10);
fpsystem('uname -r >/tmp/uname.r');
RegExpr:=TRegExpr.Create;
RegExpr.Expression:='^([0-9]+)\.([0-9]+)\.([0-9]+)';
l:=Tstringlist.Create;
l.LoadFromFile('/tmp/uname.r');
if not RegExpr.Exec(l.Text) then begin
         install.INSTALL_PROGRESS('APP_LXC','{failed}');
         install.INSTALL_STATUS('APP_LXC',110);
         Writeln('Unable to preg_match >'+ l.Text);
         L.free;
         exit;
end;

  TryStrToInt(RegExpr.Match[1],kernM);
  TryStrToInt(RegExpr.Match[2],KernMin);
  TryStrToInt(RegExpr.Match[3],KernBuild);
  writeln('Kernel:',kernM,'.', KernMin,'.',KernBuild);

  if (kernM<2) then begin
         install.INSTALL_PROGRESS('APP_LXC','{failed}');
         install.INSTALL_STATUS('APP_LXC',110);
         Writeln('Wrong kernel version...');
         exit;
end;

  if (KernMin<6) then begin
         install.INSTALL_PROGRESS('APP_LXC','{failed}');
         install.INSTALL_STATUS('APP_LXC',110);
         Writeln('Wrong kernel version...');
         exit;
end;


   if (KernBuild<26) then begin
         install.INSTALL_PROGRESS('APP_LXC','{failed}');
         install.INSTALL_STATUS('APP_LXC',110);
         Writeln('Wrong kernel version...');
         exit;
end;
  distri:=tdistriDetect.Create;
  if distri.DISTRINAME_CODE='DEBIAN' then fpsystem('/usr/bin/apt-get install linux-headers-`uname -r` -y');
  if distri.DISTRINAME_CODE='UBUNTU' then fpsystem('/usr/bin/apt-get install linux-headers-`uname -r` -y');

  SetCurrentDir('/root');
  install.INSTALL_STATUS('APP_LXC',30);
  install.INSTALL_PROGRESS('APP_LXC','{downloading}');

  if length(source_folder)=0 then source_folder:=libs.COMPILE_GENERIC_APPS('lxc');
  if not DirectoryExists(source_folder) then begin
     writeln('Install LXC failed...');
     install.INSTALL_STATUS('APP_LXC',110);
     exit;
  end;
  writeln('Install fetchmail extracted on "'+source_folder+'"');
  install.INSTALL_STATUS('APP_LXC',50);
    install.INSTALL_PROGRESS('APP_LXC','{compiling}');
    SetCurrentDir(source_folder);
    cmd:='./configure  --prefix=/usr --includedir="\${prefix}/include" --mandir="\${prefix}/share/man" --infodir="\${prefix}/share/info"';
    cmd:=cmd+' --sysconfdir=/etc --localstatedir=/var --libexecdir="\${prefix}/lib/lxc" --disable-maintainer-mode --disable-dependency-tracking';
    cmd:=cmd+' --disable-silent-rules --srcdir=.  --libdir="\${prefix}/lib/lxc" --with-rootfs-path="\${prefix}/lib/lxc"';
    writeln(cmd);
    fpsystem(cmd);
    fpsystem('make');


    install.INSTALL_PROGRESS('APP_LXC','{installing}');
    fpsystem('make install');

    if FileExists(SYS.LOCATE_GENERIC_BIN('lxc-create')) then begin
         install.INSTALL_STATUS('APP_LXC',100);
         install.INSTALL_PROGRESS('APP_LXC','{installed}');
         install.EMPTY_CACHE();
         SYS.Free;
    end else begin
         install.INSTALL_PROGRESS('APP_LXC','{failed}');
         install.INSTALL_STATUS('APP_LXC',110);
    end;

    SetCurrentDir('/root');

end;
//#########################################################################################
function install_lxc.WriteToFile(zText:string;TargetPath:string):boolean;
      var
        F : Text;
      BEGIN
      result:=true;

      TRY
      forcedirectories(ExtractFilePath(TargetPath));
       EXCEPT


        END;

        TRY
           Assign (F,TargetPath);
           Rewrite (F);
           Write(F,zText);
           Close(F);
          exit(true);
        EXCEPT


        END;

exit(false);
      END;
//#############################################################################


end.
