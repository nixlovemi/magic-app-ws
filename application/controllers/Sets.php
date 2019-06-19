<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sets extends CI_Controller {
  public function __construct(){
    CI_Controller::__construct();

    $this->load->helper("utils_helper");
  }

	public function updateSets(){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $url        = "https://api.scryfall.com/sets";
		$return     = readUrlApi($url);
    $jsonReturn = json_decode($return, true);

    $object     = $jsonReturn["object"] ?? "";
    if($object == "error"){
      //@todo tratar erros
    } elseif($object == "list"){
      $this->load->database();
      $data = $jsonReturn["data"] ?? array();

      // joga num array tds os sets do BD
      $arrSetsUid = [];

      $this->db->select('set_id, set_uid');
      $this->db->from('tb_set');
      $query   = $this->db->get();
      $arrSets = $query->result_array();

      foreach($arrSets as $arrSet){
        $arrSetsUid[$arrSet["set_id"]] = $arrSet["set_uid"];
      }
      // ================================

      $arrBatchInsert = [];
      $arrBatchUpdate = [];

      foreach($data as $set){
        $arrDados = array(
          "set_uid"             => $set["id"] ?? null,
          "set_code"            => $set["code"] ?? null,
          "set_mtgo_code"       => $set["mtgo_code"] ?? null,
          "set_tcgplayer_id"    => $set["tcgplayer_id"] ?? null,
          "set_name"            => $set["name"] ?? null,
          "set_type"            => $set["set_type"] ?? null,
          "set_sty_id"          => 0, //sempre zero pq a trigger vai achar a FK certa
          "set_uri"             => $set["uri"] ?? null,
          "set_scryfall_uri"    => $set["scryfall_uri"] ?? null,
          "set_search_uri"      => $set["search_uri"] ?? null,
          "set_released_at"     => $set["released_at"] ?? null,
          "set_parent_set_code" => $set["parent_set_code"] ?? null,
          "set_card_count"      => $set["card_count"] ?? 0,
          "set_digital"         => $set["digital"] ?? null,
          "set_foil_only"       => $set["foil_only"] ?? null,
          "set_block_code"      => $set["block_code"] ?? null,
          "set_block"           => $set["block"] ?? null,
          "set_icon_svg_uri"    => $set["icon_svg_uri"] ?? null,
        );

        $jaExiste = in_array($set["id"], $arrSetsUid);
        if($jaExiste){
          $setId              = array_search ($set["id"], $arrSetsUid);
          $arrDados["set_id"] = $setId;
          $arrBatchUpdate[]   = $arrDados;
        } else {
          $arrBatchInsert[]   = $arrDados;
        }
      }

      // executa os INSERTS
      if(count($arrBatchInsert) > 0){
        $retInsert = $this->db->insert_batch('tb_set', $arrBatchInsert);
        if($retInsert === false){
          //@todo tratar erro
        }
      }
      // ==================

      // executa os UPDATES
      if(count($arrBatchUpdate) > 0){
        $retUpdate = $this->db->update_batch('tb_set', $arrBatchUpdate, 'set_id');
        if($retUpdate === false){
          //@todo tratar erro
        }
      }
      // ==================
    }
	}
}
