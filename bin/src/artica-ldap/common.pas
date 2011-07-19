unit Common;
{$IFDEF FREEPASCAL}
   {$mode objfpc}{$H+}
{$ENDIF}


{$INCLUDE SelectionLangue.inc}
{$IFDEF FREEPASCAL}
  {$PACKRECORDS 1}
{$ELSE}
  {$A-}
{$ENDIF}
// Fonctions communes


interface
uses
  SysUtils, Classes,
  Math,

  {$IFDEF MSWINDOWS}
    OpenGLx,
    Windows,
    Graphics,
    dlgProcessings;
  {$ENDIF}
  {$IFDEF LINUX}
    OpenGLx,
    QGraphics;
  {$ENDIF}
  {$IFDEF FREEPASCAL}
     Graphics;
  {$ENDIF}

//****************************
// types communs
//****************************

{$IFDEF LINUX}
  const NB_PIXELS_PER_INCH = 81;
{$ENDIF}

const
  DEFAULT_FONT_NAME = 'Arial';
  NAME_FOR_DEFAULT_FONT = 'font_default';
  ANNEE_PIVOT_4D=1950;
  ANNEE_PIVOT   = 50;
// constantes pout types de galeries
const
  tgDEFAULT   = 0;
  tgENTRANCE  = 1;
  tgFOSSILE   = 2;
  tgVADOSE    = 3;
  tgENNOYABLE = 4;
  tgSIPHON    = 5;
  tgFIXPOINT  = 6;
  tgSURFACE   = 7;
  tgTUNNEL    = 8;
  tgMINE      = 9;
//----------------------------------
const FMTSERST = '%d.%d';


const DFT_SPELEOMETRE  = 'CASSOU JP';
const DFT_SPELEOGRAPHE = 'CASSOU JP';
const DFT_REPORTER     = 'SiliconCavings';

// types de galerie

type TModeSaveTAB =(mtabEXTENDEDTAB,mtabTOPOROBOT);
type TTextFileFormat=(tfWINDOWS, tfUNIX, tfMAC);
type TFloat = type Double;

{$IFDEF FREEPASCAL}
  type GLFloat = single;
{$ENDIF}
// matrices et vecteurs
type TMatrix  = array of array of Double;
type TVector  = array of Double;


// couleurs OpenGL
type TGLColor = record
  R: GLFloat;
  G: GLFloat;
  B: GLFloat;
  A: GLFloat;
end;
// couleurs 24 bits
type TColor3b = record
  R: byte;
  G: byte;
  B: byte;
end;

// point2D
type TPoint2Df = record
  X: TFloat;
  Y: TFloat;
end;
// point 3D
type TPoint3Df = record
  X: TFloat;
  Y: TFloat;
  Z: TFloat;
  T: TFloat;
end;
// Couleurs Macintosh
type TMacintoshColor = record
  R : word;
  G : word;
  B : word;
end;
//****************************
// Types de données TOPOROBOT
//****************************
// réseau ou secteur spéléologique
// Section -8 du fichier xtb
// Une série ne peut faire partie de plusieurs réseaux.
type TReseau = record
  IdxReseau    : integer;
  ColorReseau  : TColor;
  TypeReseau   : integer; // type de réseau:
  NomReseau    : string;
  ObsReseau    : string;
end;

Type TStation = record
   Date                :TDateTime;
   Couleur             : TColor;
   TypeGalerie         : byte;
   NumPoint            : smallint;    //'Numéro du point
   PtDepart            : smallint;    //        'Départ visée
   PtArrivee           : smallint;    //        'Arrivée visée
   Longueur            : TFloat;      //         'Longueur
   Azimut              : TFloat;      //         'Azimut
   Pente               : TFloat;      //         'Pente
   LD                  : TFloat;      //         'Distance à droite
   LG                  : TFloat;      //         'Distance à gauche
   HZ                  : TFloat;      //         'Hauteur au-dessus
   HN                  : TFloat;      //         'Hauteur du point
   //Commentaire         : array[0..24] of char; // Commentaire
   //Commentaire         : array[0..79] of char; // Commentaire
   //IDTerrainStation    : array[0..20] of Char; // ID terrain de la station
   IDTerrainStation    : string;
   Commentaire         : string;
   X                   : TFloat;      //         'X
   Y                   : TFloat;      //         'Y cheminement
   Z                   : TFloat;      //         'Z
   // code et expé
   stCode              : integer;
   stExpe              : integer;
End;


// stations
type
  pUneVisee = ^TUneVisee;
  TUneVisee = record
    NoVisee   : integer;
    NoViseeSer: integer;
    Code      : integer;
    Expe      : integer;
    Longueur  : TFloat;
    Azimut    : TFloat;
    Pente     : TFloat;
    LD        : TFloat;
    LG        : TFloat;
    HZ        : TFloat;
    HN        : TFloat;
    Commentaires    : string;
    IDTerrainStation: string;
    TypeGalerie     : byte;
    X         : TFloat;
    Y         : TFloat;
    Z         : TFloat;
end;
// entrées
type TEntrance = record
  eNumEntree: integer;
  eNomEntree: string;
  eXEntree  : TFloat;
  eYEntree  : TFloat;
  eZEntree  : TFloat;
  eDeltaX   : TFloat;
  eDeltaY   : TFloat;
  eDeltaZ   : TFloat;

  eRefSer   : integer;
  eRefSt    : integer;
  eObserv   : string;
end;

// expés
type
  pExpe = ^TExpe;
  TExpe = record
    IDExpe      : integer;
    JourExpe    : byte;
    MoisExpe    : byte;
    AnneeExpe   : word;
    Speleometre : String;
    Speleographe: string;
    ModeDecl    : byte;
    Declinaison : TFloat;
    Inclinaison : TFloat;
    Couleur     : Integer;
    Commentaire : string;
end;
// codes
type
  pCode = ^TCode;
  TCode = record
    IDCode      : integer;
    GradAz      : TFloat;
    GradInc     : TFloat;
    PsiL        : TFloat;
    PsiAz       : TFloat;
    PsiP        : TFloat;
    FactLong    : TFloat; // pour compatibilité ascendante
    AngLimite   : TFloat;
    TypeGalerie : byte;   // type de galerie
    Commentaire : string;
end;

// **********************************************
//  Types de données pour les outils graphiques
// **********************************************
// Fontes
type TFontPSProperties = record
  Name  : string;
  Size  : integer;
  Height: integer;
  Color : TColor;
  Style : TFontStyles;
end;
type TPenPSProperties = record
  Name    : string;
  Color   : TColor;
  fWidth  : double;
  nWidth  : integer;
end;
type TBrushPSProperties = record
  Color   : TColor;
  Alpha   : integer;
end;


// Entités
type TEntite = record
   //UID_Entite        : integer;                 // Couleur ID unique de l'entité
   ColorEntite       : TColor;
   Drawn             : boolean;                 // dessinée ?
   Type_Entite       : byte;                    // Type d'entité
   DateLeve          : TDateTime;
   // serie et point
   Entite_Serie      : integer;                 // Série
   Entite_Station    : integer;                 // Station
   Une_Station_1_X   : TFloat;                  // Extrémités des visées
   Une_Station_1_Y   : TFloat;
   Une_Station_1_Z   : TFloat;

   Une_Station_2_X   : TFloat;                // Extrémités des visées
   Une_Station_2_Y   : TFloat;
   Une_Station_2_Z   : TFloat;
   //LongVisee         : TFloat;
   X1PD                 : TFloat;      //         'X point droit contour
   Y1PD                 : TFloat;      //         'Y point gauche contour
   X1PG                 : TFloat;      //         'X point droit contour
   Y1PG                 : TFloat;      //         'Y point gauche contour

   X2PD                 : TFloat;      //         'X point droit contour
   Y2PD                 : TFloat;      //         'Y point gauche contour
   X2PG                 : TFloat;      //         'X point droit contour
   Y2PG                 : TFloat;      //         'Y point gauche contour

   Z1PH                 : TFloat;      //         'Z point haut contour
   Z1PB                 : TFloat;      //         'Z point bas contour
   Z2PH                 : TFloat;      //         'Z point haut contour
   Z2PB                 : TFloat;      //         'Z point bas contour

   ID_Litteral_Pt       : array[0..15] of char;    // ID alphanum. de l'entité
   // réseaux
   IdxReseau            : integer;
   ColorReseau          : TColor;

   // serie et point
   //eSerie  : integer;
   //eStation: integer;
   // code et expé
   eCode   : integer;
   eExpe   : integer;
end;
type TDatesTopo = record
  //Displayed: Boolean;
  DateTopo : TDateTime;
end;
type TReseauxTopo = record
  Couleur: TColor;
end;
type TColorGaleries = record
  //Displayed : boolean;
  Color     : TColor;
end;
// descro des couches
type TLayer = record
  Name    :  string;
  Color   : TColor;
end;
// annotations
type TAnnotation = record
  FichierNOT    : string;
  X             : Double;
  Y             : Double;
  Z             : Double;
  //Color         : TColor;
  FontColor     : TColor;  // Couleur de la fonte
  MaxLength     : integer; // Longueur maxi de la chaîne affichée
  Caption       : String;
  FontName      : String;
  FontSize      : Byte;
  Accrochage    : Byte;
  FontBold      : boolean;
  FontItalic    : boolean;
  FontUnderline : boolean;
end;
type TStringArray = array[0..63] of string;
//-----------------------------------------------
// définition des classes
//----------------------------------------------
// Classe pour la table des expés
type TTableExpes = class(TList);
// Classe pour la table des séries
type TTableSeries = class(TList);
//Classe pour la table des codes
type TTableCodes = class(TList);

//fonction de split
//procedure Split(const S: string; const Sep: string; var LS: TStringArray);

// fonction d'incrémentation lexicographique
function IncrementString(const S0: string): string;

//****************************
// fonctions communes
//****************************
procedure Swap(var V1, V2: integer); overload;
procedure Swap(var V1, V2: Double); overload;

function IsInRange(const Value: Extended;
                   const MinValue, MaxValue: Extended): Boolean; overload;
function IsInRange(const Value: integer;
                   const MinValue, MaxValue: integer): Boolean; overload;

function IIF(const Condition: boolean; const V1, V2: boolean): boolean; overload;
function IIF(const Condition: boolean; const V1, V2: integer): integer; overload;
function IIF(const Condition: boolean; const V1, V2: Extended): Extended; overload;
function IIF(const Condition: boolean; const V1, V2: String): String; overload;

function HasValue(const Value: integer; const ArrValues: array of integer): boolean;
function DateIsCorrect(const Y, M, D: word): Boolean;
//----------------------------------
function ProduitVectoriel(const Vect1, Vect2: TPoint3Df;
                          const Normalized: Boolean):TPoint3Df;
function CalculerAngles(const X1, Y1,
                              X2, Y2: TFloat): TFloat;
function Hypot2D(const DX, DY: Double): Double;
function Hypot3D(const DX, DY, DZ: Double): Double;

// définition des fonctions existant sous Linux mais absentes sous Zin
{$IFDEF MSWINDOWS}
  function StrToFloatDef(const S: string; const Default: Extended):Extended;
  function StrToIntDef  (const S: string; const Default: Integer): Integer;

{$ENDIF}

function GetAzimut(const dx, dy: Double; const Unite: double): double;

procedure GetBearingInc(const dx, dy, dz: double;
                        var Dist, Az, Inc: double;
                        const fUB, fUC: Double);
//*****************************************
//-------------------------------------
function RGB(const R, G, B: byte): integer;
function GetRValue(const Coul: TColor): Byte;
function GetGValue(const Coul: TColor): Byte;
function GetBValue(const Coul: TColor): Byte;
function GetFloatRValue(const C: TColor): TFloat;
function GetFloatGValue(const C: TColor): TFloat;
function GetFloatBValue(const C: TColor): TFloat;
function Acad2RGB(const n : integer) : tColor; // palette par défaut d'Autocad
function RGB2Acad(const C: TColor) : Byte;


//-------------------------------------
// routines de couleurs

function GetPCColor(const MC: TMacintoshColor): TColor; overload;
function GetPCColor(const mR, mG, mB: word):TColor; overload;



function GetMacColor(const PCC: TColor): TMacintoshColor;
function GetPASColor(const Coul: TGLColor): TColor;
function GetGLColor(const Coul: TColor): TGLColor;
function GetBYTEColor(const Coul: TColor): TColor3b;

function GetColorDegrade(const z: Double;
                         const zmin, zmax: Double;
                         const Coul1, Coul2: TColor): TColor;
function GetNTSCGrayScale(const C: TColor): Byte;
//*************
// routines de texte
function EnlevePerluete(const s: string): string;
function IndexOfString(const S: string; const Strs: array of string): integer;
function ChooseString(const Index: integer; const Strs: array of string): string;
function SafeTruncateString(const Str: string; const L: integer): String;

procedure AfficherMessage(const Msg: string);

procedure DrawTriangleWithDegrade(const V1, V2, V3: TPoint3DF;
                                  const C1, C2, C3: TColor);

function GetDeclimag(const InitialDate, CurrentDate: TDateTime;
                     const InitialDeclimag, Variation: Double):Double;



// Convertir un fichier texte vers le format désiré
function ConvertTextFile(const InputFileName, OutputFilename: string;
                         const InputFormat, OutputFormat: TTextFileFormat): boolean;

// extraction de paramètres d'une chaine
function Split(const Str: string; const Sep: string):TStringArray;

// formater un filtre de fichier
// Arguments pairs du tableau: Nom du filtre
// Arguments impairs: Filtre
function FormatFileFilters(const FF: array of string; const WithAll: boolean): string;

// proposer une équidistance en fonction de l'étendue du réseau
function ProposerEquidistance(const C1, C2: TPoint3Df): Double;

implementation
{$IFDEF MSWINDOWS}
uses
  MainForm;  // pour la console de contrôle
{$ENDIF}

// Détermine si la valeur figure dans la liste
function HasValue(const Value: integer; const ArrValues: array of integer): boolean;
var
  i: integer;
begin
  Result:=False;
  for i:=Low(arrValues) to High(ArrValues) do begin
    if Value = ArrValues[i] then begin
      Result:=True;
      Exit;
    end;
  end;
end;
// vérifie si la date est correcte
function DateIsCorrect(const Y, M, D: word): Boolean;
var
  D1: TDateTime;
begin
  Result:=False;
  try
    D1:=EncodeDate(Y,M,D);
    Result:=True;
  except
    Result:=False;
  end;
end;
//fonction de split
function Split(const Str: string; const Sep: string):TStringArray;
var
  pn   : integer;
  ps   : integer;
  S    : string;

begin
  for pn:=0 to High(Result) do Result[pn]:='';
  S:=Str;
  ps:=0;
  try
    pn:=0;
    repeat
     if pn>High(Result) then Break;
     ps:=Pos(Sep, S);
     //s:=Copy(s,0, ps-1);
     Result[pn]:=Trim(Copy(s,0, ps-1));
     Inc(pn);
     s:=Copy(s, 1+ps, Length(s));
    until ps=0;
    Result[pn-1]:=Trim(s);
  except
  end;
end;

// Fonctions d'change
procedure Swap(var V1, V2: integer); overload;
var Tmp: integer;
begin
  Tmp:=V1;
  V1:=V2;
  V2:=Tmp;
end;
procedure Swap(var V1, V2: Double); overload;
var Tmp: Double;
begin
  Tmp:=V1;
  V1:=V2;
  V2:=Tmp;
end;


// calcul de déclinaison magnétique
function GetDeclimag(const InitialDate, CurrentDate: TDateTime;
                     const InitialDeclimag, Variation: Double):Double;
const INTERVALYEARS=10;
var
  y,m,d: word;
  d10a: TDateTime;
  d10d: double;
  p: double;
begin
  AfficherMessage(Format('GetDeclimag(%s, %s, %f, %f)',
                         [DateToStr(InitialDate),
                          DateToStr(CurrentDate),
                          InitialDeclimag, Variation]));
  DecodeDate(InitialDate,Y,M,D);
  d10a:=Encodedate(Y+INTERVALYEARS, M, D);
  d10a:=d10a - InitialDate;
  //d:=CurrentDate - InitialDate;
  d10d:=INTERVALYEARS * Variation;
  p:=d10d / d10a;


  Result:=InitialDeclimag + p * (CurrentDate - InitialDate);
end;

// définition des fonctions existant sous Linux mais absentes sous Zin
{$IFDEF MSWINDOWS}
function StrToFloatDef(const S: string; const Default: Extended):Extended;
begin
  try
    Result:=StrToFloat(S);
  except
    Result:=Default;
  end;
end;
function StrToIntDef(const S: string; const Default: Integer): Integer;
begin
  try
    Result:=StrToInt(S);
  except
    Result:=Default;
  end;
end;
{$ENDIF}


// afficher un message de contrôle
procedure AfficherMessage(const Msg: string);
begin
 {$IFDEF MSWINDOWS}
  try
   with dlgProcessing.ListBox1 do begin
      if Items.Count > 250 then
        Items.Delete(0);
      Items.Add(Msg);
      ItemIndex:=dlgProcessing.ListBox1.Items.Count-1;
      Refresh;
    end;
   except
   end;
 {$ENDIF}
 {$IFDEF LINUX}
    WriteLn(Msg);
 {$ENDIF}
 {$IFDEF FREEPASCAL}
    WriteLn(Msg);
 {$ENDIF}
end;

// choisir une chaine en fonction d'une valeur
function ChooseString(const Index: integer; const Strs: array of string): string;
begin
  try
    if (Index<0) or (Index>High(Strs)) then begin
      Result:=Format('** Erroneous index: %d **',[Index]);
      Exit;
    end;
    Result:=Strs[Index];
  finally
  end;
end;
function IndexOfString(const S: string; const Strs: array of string): integer;
var
  i: integer;
begin
  Result:=-1;
  try
    for i:=Low(Strs) to High(Strs) do
      if Pos(S, Strs[i])>0 then begin
        Result:=i;
        Exit;
      end;

  except
    Result:=-1;
  end;
end;
function Hypot3D(const DX, DY, DZ: Double): Double;
begin
  Result:=Sqrt(dx*dx+dy*dy+dz*dz);
end;
function Hypot2D(const DX, DY: Double): Double;
begin
  Result:=Sqrt(dx*dx+dy*dy);
end;
// retourne un azimut
function GetAzimut(const dx, dy: Double; const Unite: double): double;
var
  a: double;
begin
  a:=ArcTan2(dy, dx+1e-12);
  if a<0 then a:=a+2*PI;
  a:=0.50*PI-a;
  if a<0 then a:=a+2*PI;
  Result:=a*0.50*Unite/pi;
end;


// retourne la longueur, direction et pente pour dx, dy, dz
procedure GetBearingInc(const dx, dy, dz: double;
                        var Dist, Az, Inc: double;
                        const fUB, fUC: Double);
var
  dp: Double;
begin;
  dp  :=Hypot2D(dx, dy);
  Dist:=Hypot2D(dp,dz);
  Inc :=ArcTan2(dz, dp)*0.5*fUC/pi;
  //Az  :=(0.5*PI-ArcTan2(dY,dx))*0.5*fUB/pi;
  Az:=GetAzimut(dx,dy, fUB);
end;

// enlève les perluettes
function EnlevePerluete(const s: string): string;
var
  p: integer;
  st: string;
begin
  st:=s;
  p:=Pos('&',st);
  if p=0 then
  begin
    Result:=st;
    Exit;
  end;
  Delete(st,p,1);
  Result:=st;
end;
function ProduitVectoriel(const Vect1, Vect2: TPoint3Df;
                          const Normalized: Boolean):TPoint3Df;
var
  v: TPoint3Df;
  r: Extended;
begin
  v.X:=Vect1.Y*Vect2.Z-Vect1.Z*Vect2.Y;
  v.Y:=Vect1.Z*Vect2.X-Vect1.X*Vect2.Z;
  v.Z:=Vect1.X*Vect2.Y-Vect1.Y*Vect2.X;
  if Normalized then begin
    r:=sqrt(Sqr(v.x)+sqr(v.y)+sqr(v.z))+1e-12;
    v.X:=v.x/r;
    v.y:=v.y/r;
    v.z:=v.z/r;

  end;
  Result:=v;

end;
function CalculerAngles(const X1, Y1,
                              X2, Y2: TFloat): TFloat;
var
  V1, V2, W: TPoint3Df;
begin
  // vecteur V1           vecteur V2        vecteur w
  V1.X:=X1;               V2.X:=X2;         W.X :=0;
  V1.Y:=Y1;               V2.Y:=Y2;         W.Y :=0;
  V1.Z:=0;                V2.Z:=0;          W.Z :=1;
  // produits vectoriels
  v1:=ProduitVectoriel(v1,w,True);
  v2:=ProduitVectoriel(v2,w,True);
  //composition vectorielle
  w.x:=v1.x+v2.X;
  w.y:=v1.y+v2.Y;
  w.z:=v1.z+v2.z;
  // angles
  Result:=ArcTan2(w.y+1e-12, w.x+1e-12);
end;
//****************************************************************************
// fonctions de couleurs
function GetFloatRValue(const C: TColor): TFloat;
begin
  Result:=GetRValue(C) / 256;
end;
function GetFloatGValue(const C: TColor): TFloat;
begin
  Result:=GetGValue(C) / 256;
end;
function GetFloatBValue(const C: TColor): TFloat;
begin
  Result:=GetBValue(C) / 256;
end;
//*****************************************
// Conversions couleurs PC<>Mac
function GetPCColor(const MC: TMacintoshColor): TColor; overload;
begin
  Result:=RGB(MC.R shr 8,
              MC.G shr 8,
              MC.B shr 8);

end;
function GetPCColor(const mR, mG, mB: word):TColor; overload;
begin
  Result:=RGB(mR shr 8,
              mG shr 8,
              mB shr 8);
end;

function GetMacColor(const PCC: TColor): TMacintoshColor;
var M: TMacintoshColor;
begin
  M.R:=GetRValue(PCC) * 256;
  M.G:=GetGValue(PCC) * 256;
  M.B:=GetBValue(PCC) * 256;
  Result:=M;
end;
//-------------------------------------
//-------------------------------------
function GetBYTEColor(const Coul: TColor): TColor3b;
var
  c: TColor3b;
begin
  c.R:=GetRValue(Coul);
  c.G:=GetGValue(Coul);
  c.B:=GetBValue(Coul);
  Result:=c;

end;
// Retourne sous forme de couleur OpenGL la couleur passée en argument
function GetGLColor(const Coul: TColor): TGLColor;
const
  m = 1/256;
var
  c: TGLColor;
begin
  c.R:=GetRValue(Coul)* m;
  c.G:=GetGValue(Coul)* m;
  c.B:=GetBValue(Coul)* m;
  c.A:=1.00;
  Result:=c;
end;

//-------------------------------------
// Conversion Couleur OPENGL>Couleur Pascal
function GetPASColor(const Coul: TGLColor): TColor;
const
  m = 256;
var
  c: TColor;
begin
  c:=RGB(Round(Coul.R * m),
         Round(Coul.G * m),
         Round(Coul.B * m));
  Result:=c;
end;
//----------------------------------------------------------------
// dégradé de couleurs
function GetColorDegrade(const z: Double;
                         const zmin, zmax: Double;
                         const Coul1, Coul2: TColor): TColor;
var
  D: Double;
  H: Double;
  C1, C2, C: TColor3b;
  //DC       : TColor3b;
  DR, DG, DB : SmallInt;
begin
  D:=zmax-zmin;
  if Abs(D)<1e-8 then
  begin
    Result:=Coul1;
    Exit;
  end;
  H:=(z-zmin)/D;


  c1:=GetBYTEColor(Coul1);
  c2:=GetBYTEColor(Coul2);
  DR:=C2.R-C1.R;
  DG:=C2.G-C1.G;
  DB:=C2.B-C1.B;

  C.R:=Round(C1.R + H * DR);
  C.G:=Round(C1.G + H * DG);
  C.B:=Round(C1.B + H * DB);

  Result:=RGB(C.R, C.G, C.B);
end;
//-------------------------------------------------------
//Convertit une couleur en niveaux de gris; méthode NTSC.
function GetNTSCGrayScale(const C: TColor): Byte;
var
  cl: TColor3b;
begin
  cl:=GetBYTEColor(C);
  Result:=round(0.30 * cl.R + 0.59 * cl.G + 0.11 * cl.B);
end;



//-------------------------------------
function RGB(const R, G, B: byte): integer;
begin
  Result:=(R or (G shl 8) or (B shl 16));
end;
function GetRValue(const Coul: TColor): Byte;
begin
  Result:= Coul;
end;
function GetGValue(const Coul: TColor): Byte;
begin
  Result:= Coul shr 8;
end;
function GetBValue(const Coul: TColor): Byte;
begin
  Result:= Coul shr 16;
end;

function RGB2Acad(const C: TColor): Byte;
var drmin,dgmin,dbmin,
    CouleurR,
    CouleurG,
    CouleurB,
    ACcolor,
    ACcolorR,
    ACcolorG,
    ACcolorB,
    dColor,
    res    : integer;
begin
    Result:=0;

    dRmin := 99999999;
    dGmin := 99999999;
    dBmin := 99999999;

    CouleurR := GetRValue(C);
    CouleurG := GetGValue(C);
    CouleurB := GetBValue(C);
    for res := 1 to 255 do begin
        ACcolor := Acad2RGB(res);
        ACcolorR := GetRValue(ACcolor);
        ACcolorG := GetRValue(ACcolor);
        ACcolorB := GetBValue(ACcolor);
        dColor := abs(ACcolorR-CouleurR)+
                  abs(ACcolorG-CouleurG)+
                  abs(ACcolorB-CouleurB);
        if dColor<dRmin then begin
            dRmin := dColor;
            result := res;
        end;
    end;
end;

function Acad2RGB(const n : integer) : tColor; // palette par défaut d'Autocad
var r,g,b,
    c,d,u : integer;
    C1 : Tcolor;  // RGB(r, g ,b)
    const StandCol : array[0..9] of tcolor =
        (clBlack ,clRed,$0001E1F3{clYellow},$0000C800{clLime},$00FDE302{clAqua},
         clBlue,clFuchsia,clBlack,clGray,clLtGray);
    const FullPalete : array[0..23] of tcolor =
        ($0000FF,$0040FF,$0080FF,$00C0FF,
         $00FFFF,$00FFC0,$00FF80,$00FF40,
         $00FF00,$40FF00,$80FF00,$C0FF00,
         $FFFF00,$FFC000,$FF8000,$FF4000,
         $FF0000,$FF0040,$FF0080,$FF00C0,
         $FF00FF,$C000FF,$8000FF,$4000FF);//..$0000FF (retour au début)
begin
    c := n mod 256; // au cas ou ?
    if c<10 then C1 := StandCol[c]
    else begin
        d := ((c-10) div 10);// 0..23
        // Couleur de base à corriger
        C1 :=FullPalete[d mod 24];
        // Correction:--------------------------------
        d := c div 10; // dizaines
        u := c-d*10; // unités
        // séparation des couleurs RGB
        b := (C1 and $FF0000) shr 16;
        g := (C1 and $00FF00) shr 8;
        r := (C1 and $0000FF);
        //Plus clair pour les impairs
        if ((u div 2)*2<>u) then begin
            b := b + ((255-b) div 2);
            g := g + ((255-g) div 2);
            r := r + ((255-r) div 2);
        end;
        // Plus foncé si u grand
        b := b*4 div (u+4);
        g := g*4 div (u+4);
        r := r*4 div (u+4);
        // Couleur corrigée:---------------------------
        C1 := RGB(r,g,b);

    end;
    result := C1;
end;

//-------------------------------------
function IsInRange(const Value: Extended;
                   const MinValue, MaxValue: Extended): Boolean; overload;
begin
  if (Value >= MinValue) and
     (Value <= MaxValue) then
    Result:=True
  else
    Result:=False;
  //AfficherMessage(Format('%f %f %f %d',[Value, MinValue, MaxValue,
  //                                      IIf(Result, 1, 0)]));
end;
function IsInRange(const Value: integer;
                   const MinValue, MaxValue: integer): Boolean; overload;
begin
  if (Value >= MinValue) and
     (Value <= MaxValue) then
    Result:=True
  else
    Result:=False;
end;


//-------------------------------------
function IIF(const Condition: boolean; const V1, V2: integer): integer; overload;
begin
  if Condition then Result:=V1 else Result:=V2;
end;
function IIF(const Condition: boolean; const V1, V2: Extended): Extended; overload;
begin
  if Condition then Result:=V1 else Result:=V2;
end;
function IIF(const Condition: boolean; const V1, V2: boolean): boolean; overload;
begin
  if Condition then Result:=V1 else Result:=V2;
end;
function IIF(const Condition: boolean; const V1, V2: String): String; overload;
begin
  if Condition then Result:=V1 else Result:=V2;
end;

// dessiner un triangle dégradé
procedure DrawTriangleWithDegrade(const V1, V2, V3: TPoint3DF;
                                  const C1, C2, C3: TColor);

begin
;
end;

// Convertir un fichier texte vers le format désiré
// cette variante utilise un TStringList
function ConvertTextFile(const InputFileName, OutputFilename: string;
                         const InputFormat, OutputFormat: TTextFileFormat): boolean;
var

  FO  : TextFile;
  ENDL: string;   // fin de ligne
  ALine: string;
  i: integer;
begin
  Result:=False;
  if Not(FileExists(InputFileName)) then Exit;
  case OutputFormat of
    tfWINDOWS: ENDL:=#13+#10;
    tfUNIX   : ENDL:=#10;
    tfMAC    : ENDL:=#13;
  end;
  with TStringList.Create do begin
  try
    Clear;
    LoadFromFile(InputFileName);
    AssignFile(FO, OutputFilename);
    ReWrite(FO);
    try
      try
        for i:=0 to Count-1 do begin
          ALine:=Trim(Strings[i])+ENDL;
          Write(FO, ALine);
        end;
        Result:=True;
      except
      end;
    finally
      CloseFile(FO);
    end;
  finally // with TStringList
    Clear;
    Free;
  end;
  end;
end;
(*
for w1:=0 to Length(Lin) do if Lin[w1]=#136 then Lin[w1]:='à';
    for w1:=0 to Length(Lin) do if Lin[w1]=#142 then Lin[w1]:='é';
    for w1:=0 to Length(Lin) do if Lin[w1]=#143 then Lin[w1]:='è';
    for w1:=0 to Length(Lin) do if Lin[w1]=#148 then Lin[w1]:='î';
    for w1:=0 to Length(Lin) do if Lin[w1]=#137 then Lin[w1]:='â';
    for w1:=0 to Length(Lin) do if Lin[w1]=#144 then Lin[w1]:='ê';
    for w1:=0 to Length(Lin) do if Lin[w1]=#144 then Lin[w1]:='ë';
    for w1:=0 to Length(Lin) do if Lin[w1]=#141 then Lin[w1]:='ç';
//*)
function SafeTruncateString(const Str: string; const L: integer): String;
begin
 if Length(Str)>L then
   Result:=Trim(Copy(Str, 1, L))
 else
   Result:=Trim(Str);
end;
// fonction d'incrémentation lexicographique
// Si le string ne comporte que des lettres, on ajoute un indice à la fin
// Si le string comporte un préfixe et un indice, on incrémente l'indice
function IncrementString(const S0: string): string;
var
  i,q: integer;
  Groupe: string;
  Prefix: string;
  Reste : string;
  Index : integer;

  procedure DecomposeLitteral;
  const
    Seps =':./-_,;';
  var
    a,b: integer;

  begin
    Groupe:='';
    for a:=1 to length(Seps) do begin
      b:=Pos(Seps[a], S0);
      if b>0 then begin // si le séparateur est trouvé, on sort le résultat
        Groupe:=Copy(S0, 1, b-1)+Seps[a];
        Break;
      end;
    end;
    if Groupe='' then
      Reste:=S0
    else
      Reste:=Copy(S0, b+1, Length(S0));

  end;
begin
  // Si chaine vide, on renvoie rien
  if S0='' then begin Result:=''; exit; end;
  // rechercher un groupe (série, etc ...)
  // séparé par un ":", ".", "/", "-", "_"
  DecomposeLitteral;
  // rechercher un chiffre
  Q:=-1;
  for i:=1 to Length(Reste) do begin
    if IsInRange(Ord(Reste[i]), Ord('0'), Ord('9')+1) then begin
      Q:=i;
      Break;
    end;
  end;
  // Si chiffre trouvé:
  if Q>0 then begin
    Prefix:=Copy(Reste, 1, Q-1);
    Index :=StrToIntDef(Copy(Reste, Q, Length(Reste)-Q+1),0);

  end else begin
    Prefix:=Trim(Reste);
    Index:=0;
  end;
  AfficherMessage(Format('IncrementString: G=%s S=%s - P=%s - I=%d',[Groupe, Reste, Prefix, Index]));
  Result:=Format('%s%s%d',[Groupe, Prefix, Index+1]);
end;


// formater un filtre de fichier
// Arguments pairs du tableau: Nom du filtre
// Arguments impairs: Filtre
function FormatFileFilters(const FF: array of string; const WithAll: boolean): string;
var
  i: integer;
begin
  Result:='';
  for i:=0 to High(FF) div 2 do
    Result:=Result + Format('%s (%s)|%s|',[FF[i shl 1], FF[1+i shl 1], FF[1+i shl 1]]);
  // supprimer le 'pipe' de fin
  Delete(Result, Length(Result),1);
  if WithAll then
    {$IFDEF FRENCH_MESSAGES}  Result:=Result+'|Tous (*.*)|*.*'; {$ENDIF}
    {$IFDEF ENGLISH_MESSAGES} Result:=Result+'|All (*.*)|*.*'; {$ENDIF}
    {$IFDEF SPANISH_MESSAGES} Result:=Result+'|Tous (*.*)|*.*'; {$ENDIF}
end;

// Suggestion d'une équidistance en fonction de l'etendue totale du réseau
function ProposerEquidistance(const C1, C2: TPoint3Df): Double;
var
  d: double;
begin
  Result:=50.00;
  try

    d:=Hypot3D(C2.X - C1.X,
               C2.Y - C1.Y,
               C2.Z - C1.Z);
    d:=d/10;
    Result:=d;
    if IsInRange(d,    0.00,   10.00) then Result:=10.00;
    if IsInRange(d,   10.00,   25.00) then Result:=25.00;
    if IsInRange(d,   25.00,   50.00) then Result:=50.00;
    if IsInRange(d,   50.00,  100.00) then Result:=100.00;
    if IsInRange(d,  100.00,  200.00) then Result:=200.00;
    if IsInRange(d,  200.00,  250.00) then Result:=250.00;
    if IsInRange(d,  250.00,  500.00) then Result:=500.00;
    if IsInRange(d,  500.00, 1000.00) then Result:=1000.00;
    if IsInRange(d, 1000.00, 2000.00) then Result:=2000.00;
    if IsInRange(d, 2000.00, 5000.00) then Result:=5000.00;
    if IsInRange(d, 5000.00,10000.00) then Result:=10000.00;
    if IsInRange(d,10000.00,20000.00) then Result:=20000.00;
  except
    Result:=50.00;
  end;
end;


end.


