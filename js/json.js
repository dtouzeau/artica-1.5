
var page=CurrentPageName();
var Folerstruct = [
{
'id':'root',
'txt':'Disk',
'img':'database-16.png', // Image s'il n'a pas d'enfants
'imgopen':'database-16.png', // Image s'il a des enfants et qu'il est ouvert
'imgclose':'database-16.png', // Image s'il a des enfants et qu'il est fermé
'onopenpopulate' : YahooTreeFoldersPopulate,
"openlink" : "yahoo.tree.populate.php?p="+page,
"canhavechildren" : true
}
]; 