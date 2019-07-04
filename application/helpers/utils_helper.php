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

//@todo ver pq essa MERDA de app_key nao ta funfanfo
if(!isset($jsonVars->appkey) || $jsonVars->appkey != "960b8735446c07f53e9b90d4202a4e0d"/*APP_KEY*/){
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

  usleep(250000); //0.25 seg
  return $result;
}

function resize($newWidth, $targetFile, $originalFile) {

    $info = getimagesize($originalFile);
    $mime = $info['mime'];

    switch ($mime) {
            case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_save_func = 'imagejpeg';
                    $new_image_ext = 'jpg';
                    break;

            case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    $image_save_func = 'imagepng';
                    $new_image_ext = 'png';
                    break;

            case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_save_func = 'imagegif';
                    $new_image_ext = 'gif';
                    break;

            default:
                    throw new Exception('Unknown image type.');
    }

    $img = $image_create_func($originalFile);
    list($width, $height) = getimagesize($originalFile);

    $newHeight = ($height / $width) * $newWidth;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (file_exists($targetFile)) {
            unlink($targetFile);
    }
    $image_save_func($tmp, "$targetFile.$new_image_ext");
}
