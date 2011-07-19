unit uHashList;
{$MODE DELPHI}

interface

uses
   Classes;

type
  THashStringList = class(TObject)
  private
    InternalList: TStringList;
    function FindTag(Tag: Integer): Integer;
  protected
    function GetHashCodes(Index: Integer): string;
    function GetTag(Index: Integer): Integer;
    procedure SetTag(Index: Integer; Value: Integer);
    function GetHashValues(const HashCode: string): string;
    procedure SetHashValues(const HashCode: string; const Value: string);
    function GetTagValues(Tag: Integer): string;
    procedure SetTagValues(Tag: Integer; const Value: string);
    function GetHashTag(const HashCode: string): Integer;
    procedure SetHashTag(const HashCode: string; Value: Integer);
    function GetObjects(const HashCode: string): TObject;
    procedure SetObjects(const HashCode: string; Obj: TObject);
    function GetCount(): Integer;
  public
    constructor Create();
    destructor Destroy; override;
    procedure Clear();

    property HashCodes[Index: Integer]: string read GetHashCodes; // Liste Triée En Lecture Seule
    property Tag[Index: Integer]: Integer read GetTag write SetTag;
    property HashValues[const HashCode: string]: string read GetHashValues write SetHashValues; default;
    property HashTag[const HashCode: string]: Integer read GetHashTag write SetHashTag;
    property TagValues[Tag: Integer]: string read GetTagValues write SetTagValues;
    property Objects[const HashCode: string]: TObject read GetObjects write SetObjects;
    property Count: Integer read GetCount;
  end;

implementation

type
  THashStringItem = class
  public
     HashValue: string;
     Tag: Integer;
     SubObject: TObject;
     constructor Create(Value: string); overload;
     constructor Create(Value: Integer); overload;
  end;

resourcestring
  SDuplicateTag = 'Tag : %d déjà affecté !';

{ THashStringList }

constructor THashStringList.Create();
begin
  inherited;

  InternalList := TStringList.Create();
  InternalList.Sorted := True;
  // Impossible d'insérer/modifier deux fois le même élément, les accesseurs normalement sécurisent cela !
  InternalList.Duplicates := dupError;
end;

destructor THashStringList.Destroy;
begin
  Clear();

  InternalList.Free();
  InternalList := nil;

  inherited;
end;

procedure THashStringList.Clear();
var
  Index: Integer;
begin
  if Self <> nil then
  begin
    for Index := 0 to InternalList.Count - 1 do
    begin
       // Libère l'Objet Hash mais pas le SubObject
       THashStringItem(InternalList.Objects[Index]).Free();
    end;
    InternalList.Clear();
  end;
end;

function THashStringList.GetHashCodes(Index: Integer): string;
begin
  Result := InternalList.Strings[Index];
end;

function THashStringList.GetTag(Index: Integer): Integer;
begin
  Result := THashStringItem(InternalList.Objects[Index]).Tag;
end;

procedure THashStringList.SetTag(Index: Integer; Value: Integer);
begin
  THashStringItem(InternalList.Objects[Index]).Tag := Value;
end;

function THashStringList.GetHashValues(const HashCode: string): string;
begin
  Result := THashStringItem(InternalList.Objects[InternalList.IndexOf(HashCode)]).HashValue;
end;

procedure THashStringList.SetHashValues(const HashCode: string; const Value: string);
var
  Index: Integer;
begin
  Index := InternalList.IndexOf(HashCode);
  if Index >= 0 then
  begin
     THashStringItem(InternalList.Objects[Index]).HashValue := Value;
  end
  else
  begin
     InternalList.AddObject(HashCode, THashStringItem.Create(Value));
  end;
end;

function THashStringList.GetTagValues(Tag: Integer): string;
begin
   Result := THashStringItem(InternalList.Objects[FindTag(Tag)]).HashValue;
end;

procedure THashStringList.SetTagValues(Tag: Integer; const Value: string);
begin
   THashStringItem(InternalList.Objects[FindTag(Tag)]).HashValue := Value;
end;

function THashStringList.GetHashTag(const HashCode: string): Integer;
begin
  Result := THashStringItem(InternalList.Objects[InternalList.IndexOf(HashCode)]).Tag;
end;

procedure THashStringList.SetHashTag(const HashCode: string; Value: Integer);
var
  Index: Integer;
begin
  if FindTag(Value) >= 0 then
  begin
     raise EStringListError.CreateFmt(SDuplicateTag, [Value]);
  end;

  Index := InternalList.IndexOf(HashCode);
  if Index >= 0 then
  begin
     THashStringItem(InternalList.Objects[Index]).Tag := Value;
  end
  else
  begin
     InternalList.AddObject(HashCode, THashStringItem.Create(Value));
  end;
end;

function THashStringList.GetObjects(const HashCode: string): TObject;
begin
  Result :=  THashStringItem(InternalList.Objects[InternalList.IndexOf(HashCode)]).SubObject;
end;

procedure THashStringList.SetObjects(const HashCode: string; Obj: TObject);
begin
  THashStringItem(InternalList.Objects[InternalList.IndexOf(HashCode)]).SubObject := Obj;
end;

function THashStringList.GetCount(): Integer;
begin
  Result := InternalList.Count;
end;

function THashStringList.FindTag(Tag: Integer): Integer;
begin
  for Result := 0 to InternalList.Count - 1 do
  begin
     if THashStringItem(InternalList.Objects[Result]).Tag = Tag then
     begin
        Exit;
     end;
  end;
  Result := -1;
end;

{ THashStringItem }

constructor THashStringItem.Create(Value: string);
begin
  HashValue := Value;
end;

constructor THashStringItem.Create(Value: Integer);
begin
  HashValue := '';
  Tag := Value;
end;


end.
