<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* valida e retorna as variaveis
* $return->nome, por exemplo
*/
function proccessPost(){
  $postdata = file_get_contents("php://input");
  if($postdata != ""){
    $_SESSION["postData"] = $postdata;
    $jsonVars             = json_decode($postdata);
  } else {
    $jsonStr              = json_encode($_REQUEST);
    $_SESSION["postData"] = $jsonStr;
    $jsonVars             = json_decode($jsonStr);
  }

  if(!isset($jsonVars->appkey) || $jsonVars->appkey != APP_KEY){
    $arrRet         = [];
    $arrRet["erro"] = true;
    $arrRet["msg"]  = "Key de acesso inválida!" ;

    echo json_encode($arrRet);
    die();
  } else {
    return $jsonVars;
  }
}

/**
* executa o retorno padrão do WS
* $arrRet = array com as informações de retorno
*/
function printaRetorno($arrRet){
  if(!is_array($arrRet)){
    $arrRet         = [];
    $arrRet["erro"] = true;
    $arrRet["msg"]  = "Variável do retorno deve ser um array";

    echo json_encode($arrRet);
    die();
  } else {
    echo json_encode($arrRet);
  }
}

function is_date($str){
  if (strpos($str, "/") !== false) {
    $str = Util::acerta_data($str);
  }

  $stamp = strtotime($str);
  if (!$stamp != "") {
    $str   = substr($str, 1, 2)."/".substr($str, 4, 2)."/".substr($str, 7, 4);
    $stamp = strtotime($str);
  }
  if (!$stamp != "") {
    return false;
  } else {
    return true;
  }
}

function readUrlApi($url){
  //  Initiate curl
  $ch = curl_init();
  // Will return the response, if false it print the response
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // Set the url
  curl_setopt($ch, CURLOPT_URL,$url);
  // Execute
  $result = curl_exec($ch);
  // Closing
  curl_close($ch);

  return $result;
}
