<?php
//*******************************************************************
//ArrayManip version 0.1
//an easy Array manipulation tool
//*******************************************************************
//by Benjamin RICHARD
//ben_rich@caramail.com
//*******************************************************************
//copyright 2003 Benjamin RICHARD
//All rights reserved
//*******************************************************************
//Licensed under the GNU General Public License (GPL)
//
//This program is free software; you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation; either version 2 of the License, or
//(at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//*******************************************************************
//see GPL details at http://www.gnu.org/copyleft/gpl.html
//*******************************************************************


/**************************************************************/
  /*************  CLASS  *************/
/**************************************************************/
class ArrayManip{
	var $boolUserMode;
	var $arrArrayToManip=array();
	var $arrTempArray=array();
	var $strErrMSG=null;
/**************************************************************/
  /*************  CONSTRUCTOR  *************/
  /* 0 -> keep keys in place (if you work with associated array)
  *  1 -> reindex keys (if you work with only indexed array or if you want to replace keys by index)
  */
/**************************************************************/
  
  function ArrayManip( $boolUserMode = 1 )
  {
    /*************  Variables  *************/
    $this->boolUserMode = $boolUserMode;
    $this->arrArrayToManip = array();
    $this->arrTempArray = array();
    $this->strErrMSG = null;
    
    /*************  Processing  *************/
    $this->initArray();
  }

/**************************************************************/
  /*************  PRIVATE METHOD  *************/
/**************************************************************/
  
  function initArray(){
    /*************  Processing  *************/
    $this->arrTempArray = array();
    $this->arrArrayToManip = array();  
  }
  
  
  
  function initManipulation( $arrMyArray, $intOldPos, $intNewPos = null  )
  {
    /*************  Variables  *************/
    $intCountKeys = 0;
    $intCountIndexInArray = 0;
    $this->strErrMSG = null;  
    
    /*************  Processing  *************/
    $this->initArray();
    
    // Re-Index the Array
    $this->prepareArray( $arrMyArray );
    if(!is_array($this->arrArrayToManip)){return null;}
    // Count of number of index (no matter of keys, we del them)
    foreach( $this->arrArrayToManip as $strKey => $arrElement )
    {
      if( !is_int($strKey) )
      {
        $intCountKeys++;
      }      
    }
    reset( $this->arrArrayToManip );
    $intCountIndexInArray = count($this->arrArrayToManip) -$intCountKeys;
    
    // Test Validity of Param
    if( !is_array($this->arrArrayToManip) ) $this->errorMSG( 0 ) ;
    if( $intOldPos > $intCountIndexInArray-1 ) $this->errorMSG( 1 ) ;
    if( !is_null($intNewPos) && $intNewPos > $intCountIndexInArray-1 ) $this->errorMSG( 2 ) ;
    
  }
  
    
  function prepareArray( $arrToModify )
  {
    /*************  Variables  *************/
    $this->arrArrayToManip = $arrToModify;
	if( !is_array($this->arrArrayToManip)){return null;}
    /*************  Processing  *************/
    foreach( $this->arrArrayToManip as $strKey => $arrElement )
    {
      if( !is_int($strKey) )
      {
        if( $this->boolUserMode == 1 )
        {
          array_push( $this->arrTempArray, array_shift($this->arrArrayToManip) );  
        }elseif( $this->boolUserMode == 0 ){
          $this->arrTempArray[$strKey] = $arrElement ;
          array_shift($this->arrArrayToManip);
        }
      }else{
        array_push( $this->arrTempArray, array_shift($this->arrArrayToManip) );  
      }
      
    }
    
    // Store Array and Clear Temp Array
    $this->arrArrayToManip = $this->arrTempArray;
    $this->arrTempArray = array();     
  }
  
  function returnPrepareArray( $arrToModify )
  {
    /*************  Variables  *************/
    $arrArrayToManip = $arrToModify;
    $arrReturnArray = array();

    /*************  Processing  *************/
    foreach( $arrArrayToManip as $strKey => $arrElement )
    {
      array_push( $arrReturnArray, array_shift($arrArrayToManip) );  
    }
    
    return $arrReturnArray ;
    
  }
  
  
  function countKeysInArray( $int1stPosToManip )
  {
    /*************  Variables  *************/
    $intCountKeys = 0;
    if(!is_array($this->arrArrayToManip)){return 0;}
    
    /*************  Processing  *************/
    foreach( $this->arrArrayToManip as $strKey => $arrElement )
    {
      if( !is_int($strKey) && $this->boolUserMode == 0 )
      {
        $intCountKeys++;
      }elseif( $strKey == $int1stPosToManip ){
        break;
      }      
    }    
    // Reset Array because of the foreach
    if(is_array($this->arrArrayToManip)){reset( $this->arrArrayToManip );}
    
    return $intCountKeys;
  }
  
  function errorMSG( $intErrCode )
  {
    switch( $intErrCode )
    {
      case 0:
        $strErrMSG = "Error: the first element is not an array";
        break;
      case 1:
        $strErrMSG = "Error: you want to change the position of an element which old index is out of the array (oldPosition)";
        break;
      case 2:
        $strErrMSG = "Error: you want to change the position of an element which new index is out of the array (newPosition)";
        break;
      default:
        break;    
    }
    
    $this->strErrMSG = $strErrMSG;
  }
  
/**************************************************************/
  /*************  PUBLIC METHOD  *************/
/**************************************************************/  

  function moveElement( $arrMyArray, $intOldPos, $intNewPos )
  {
    /*************  Variables  *************/
    $arrTempElement = array();
  
    /*************  Processing  *************/
    // Init & Test of Validity & Transform or not the keys to index
    $this->initArray();
    $this->initManipulation( $arrMyArray, $intOldPos, $intNewPos  );
    if( !is_null( $this->strErrMSG ) ) return $this->strErrMSG;

    // Begin of Manipulation
    if( $intOldPos != $intNewPos )
    {    
      // the element to change pos
      $arrTempElement = $this->arrArrayToManip[$intOldPos];
      // delete the element to change pos
      $this->deleteElement( $this->arrArrayToManip, $intOldPos, 0);
        
      // 1 Push the values that are before the new pos
      $i = 0;
      if(!is_array($this->arrArrayToManip)){return array();}
      foreach( $this->arrArrayToManip as $strKey => $arrElement )
      {
        // Case where we have reindexed the array
        if(  $this->boolUserMode == 1 )
        {
          if( $i != $intNewPos ) // We stop process when we reach the new pos
          {
            $i++;
          }else{
            break;
          }  
        }
        
        // Process   
        if( !is_int($strKey) )
        {
          $this->arrTempArray[$strKey] = $arrElement ;
          array_shift($this->arrArrayToManip);
        }else{
          array_push( $this->arrTempArray, array_shift($this->arrArrayToManip) );  
        }
        
        // Case where we have not reindexed the array
        if( $this->boolUserMode == 0 )
        {
          if( $i != $intNewPos+1 ) // We stop process when the next foreach reach the new pos
          {
            $i++;
          }else{
            break;
          }        
        }


      }

      // 2 Push the value to its new pos
      array_push( $this->arrTempArray, $arrTempElement);

      // 3 Push the values that are after the new pos
      foreach( $this->arrArrayToManip as $strKey => $arrElement )
      {
        if( !is_int($strKey) )
        {
          $this->arrTempArray[$strKey] = $arrElement ;
          array_shift($this->arrArrayToManip);
        }else{
          array_push( $this->arrTempArray, array_shift($this->arrArrayToManip) );  
        }
      }

      // return the new array
      return $this->arrTempArray;     
      
    }else{
      // 1 Do nothing
      return $this->arrArrayToManip;
    }
  }
  
  function deleteElement( $arrMyArray, $intPosToDelete, $intDoInit = 1 )
  {
    /*************  Variables  *************/
    $intCountKeys = 0 ;
    
    /*************  Processing  *************/
    if( $intDoInit == 1 )
    {
      // Init & Test of Validity & Transform or not the keys to index
      $this->initArray();
      $this->initManipulation( $arrMyArray, $intPosToDelete );
      if( !is_null( $this->strErrMSG ) ) return $this->strErrMSG;  
    }
    
    // Counter for strKeys
    $intCountKeys = $this->countKeysInArray( $intPosToDelete );
    
    // New Pos to del
    $intPosToDelete += $intCountKeys;
    
    // Delete modifying the index with the strKeys counter
    if(is_array($this->arrArrayToManip)){
    	array_splice( $this->arrArrayToManip, $intPosToDelete, 1 );
    }
    return $this->arrArrayToManip;
  }
  
  function insertElement( $arrMyArray, $intPosToInsert, $arrElementToAdd )
  {
    /*************  Variables  *************/
    $intCountKeys = 0 ;
    
    /*************  Processing  *************/
    // Init & Test of Validity & Transform or not the keys to index
    $this->initArray();
    $this->initManipulation( $arrMyArray, $intPosToInsert );
    if( !is_null( $this->strErrMSG ) ) return $this->strErrMSG;

    // Counter for strKeys
    $intCountKeys = $this->countKeysInArray( $intPosToDelete );
    
    // New Pos to insert
    $intPosToInsert += $intCountKeys;
   
    array_splice( $this->arrArrayToManip, $intPosToInsert, 0, $arrElementToAdd );
    return $this->arrArrayToManip;
  }

}