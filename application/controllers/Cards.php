<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cards extends CI_Controller {
  public function __construct(){
    CI_Controller::__construct();

    $this->load->helper("cards_helper");
    $this->load->helper("utils_helper");
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
      $this->db->reset_query();

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

      // atualiza todas as cartas que não tinham card_name
      $this->db->distinct();
      $this->db->select('car_id, car_uid');
      $this->db->from('tb_card');
      $this->db->where('car_cdn_id = 0');
      $this->db->where("car_layout NOT IN ('token', 'emblem', 'double_faced_token')");

      $query = $this->db->get();
      $arrRs = $query->result_array();
      $this->db->reset_query();
      foreach($arrRs as $row){
        $carId  = $row["car_id"];
        $carUid = $row["car_uid"];
        $this->updateCardByUid($carUid);

        $this->db->delete('tb_card_images', array('cim_car_id' => $carId));
      }
      // =================================================
    }
  }

  // searchUri sample: https://api.scryfall.com/cards/search?order=set&q=e%3Aaer&unique=prints
  public function updateCardsBySet($setCode){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    list($retInsert, $retUpdate) = fncUpdateCardsBySet($setCode);
    // @todo tratar retorno
  }

  public function updateCardByUid($uid){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $url        = "https://api.scryfall.com/cards/$uid";
		$return     = readUrlApi($url);
    $jsonReturn = json_decode($return, true);

    $object     = $jsonReturn["object"] ?? "";
    if($object == "error"){
      //@todo tratar erros
    } elseif($object == "card"){
      // verifica se carta ja existe
      $this->load->database();
      $this->db->select('car_id');
      $this->db->from('tb_card');
      $this->db->where('car_uid=', $uid);
      $query    = $this->db->get();
      $arrCard  = $query->result_array();
      $jaExiste = count($arrCard) > 0;
      $this->db->reset_query();
      // ===========================

      $card = cardScryfallToBd($jsonReturn);
      if($jaExiste){
        $this->db->where('car_id', $arrCard[0]["car_id"] ?? -1);
        $this->db->update('tb_card', $card);
      } else {
        $this->db->insert('tb_card', $card);
      }
    }
  }

  public function updateLocalCardsName(){
    set_time_limit (180);
    header('Content-disposition: attachment; filename=cards_name.json');
    header('Content-type: application/json');
    $postVars = proccessPost();

    $this->load->database();

    $this->db->select("cdn_id, cdn_name, car_card_faces, car_cmc, car_colors
                      ,car_color_identity, car_color_indicator, car_legalities
                      ,car_loyalty, car_mana_cost, car_power, car_toughness
                      ,car_type_line, car_rarity, car_id");
    $this->db->from("
      (
        SELECT cdn_id
                ,cdn_name
                ,car_card_faces
                ,car_cmc
                ,car_colors
                ,car_color_identity
                ,car_color_indicator
                ,car_legalities
                ,car_loyalty
                ,car_mana_cost
                ,car_power
                ,car_toughness
                ,car_type_line
                ,car_rarity
                ,car_id
                ,RANK() OVER (PARTITION BY cdn_id ORDER BY car_released_at DESC, car_id DESC)
        FROM tb_cards_name
        INNER JOIN tb_card ON car_cdn_id = cdn_id
        WHERE car_released_at < '2019-06-28 23:59:59'
        AND cdn_id <> 0
      )t
    ");
    $this->db->where('rank =', 1);
    $this->db->order_by('cdn_name', 'ASC');

    $query = $this->db->get();
    $arrRs = $query->result_array();

    $arrJson = [];
    foreach($arrRs as $row){
      $cdnId           = $row["cdn_id"];
      $arrJson[$cdnId] = $row;
    }

    echo json_encode($arrJson);
  }

  public function updateLocalCards(){
    set_time_limit (180);
    header('Content-disposition: attachment; filename=cards.json');
    header('Content-type: application/json');
    #$postVars = proccessPost();

    $this->load->database();

    $fldNames = "car_id, car_uid, car_lang, car_scryfall_uri,
                 car_foil, car_name, car_nonfoil, car_oracle_text,
                 car_collector_number, car_games, car_prices, car_printed_name,
                 car_printed_text, car_printed_type_line, car_released_at,
                 car_set_name, car_set_type, car_set, car_cdn_id, cim_url_app";
    $this->db->select($fldNames);
    $this->db->from('tb_card');
    $this->db->join('tb_card_images', 'cim_car_id = car_id', 'left');
    $this->db->where('car_released_at <=', date("Y-m-d H:i:s"));
    $this->db->order_by('car_released_at', 'DESC');
    $this->db->order_by('car_set', 'ASC');
    $this->db->order_by('car_collector_number', 'ASC');

    $query = $this->db->get();
    $arrRs = $query->result_array();

    $arrJson = [];
    foreach($arrRs as $row){
      $carId              = $row["car_id"];
      $row["cim_url_app"] = basename($row["cim_url_app"]);
      $arrJson[$carId]    = $row;
    }

    echo json_encode($arrJson);
  }
}
