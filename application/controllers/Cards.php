<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cards extends CI_Controller {
  public function __construct(){
    CI_Controller::__construct();

    $this->load->helper("cards_helper");
  }

  public function updateCardsName(){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $url        = "https://api.scryfall.com/catalog/card-names";
		$return     = readUrlApi($url);
    $jsonReturn = json_decode($return, true);

    $object     = $jsonReturn["object"] ?? "";
    if($object == "error"){
      //@todo tratar erros
    } elseif($object == "catalog"){
      $this->load->database();
      $data = $jsonReturn["data"] ?? array();

      // joga num array tds os cards names do BD
      $arrCardsName = [];

      $this->db->select('cdn_name');
      $this->db->from('tb_cards_name');
      $query    = $this->db->get();
      $arrCards = $query->result_array();

      foreach($arrCards as $arrCardName){
        $arrCardsName[] = $arrCardName["cdn_name"];
      }
      // ================================

      $arrBatchInsert = [];

      foreach($data as $cardName){
        $arrDados = array(
          "cdn_name" => $cardName ?? null,
        );

        $jaExiste = in_array($cardName, $arrCardsName);
        if($jaExiste == false){
          $arrBatchInsert[] = $arrDados;
        }
      }

      // executa os INSERTS
      if(count($arrBatchInsert) > 0){
        $retInsert = $this->db->insert_batch('tb_cards_name', $arrBatchInsert);
        if($retInsert === false){
          //@todo tratar erro
        }
      }
      // ==================
    }
  }

  // searchUri sample: https://api.scryfall.com/cards/search?order=set&q=e%3Aaer&unique=prints
  public function updateCardsBySet($setCode){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    list($retInsert, $retUpdate) = fncUpdateCardsBySet($setCode);
    // @todo tratar retorno
  }
}
