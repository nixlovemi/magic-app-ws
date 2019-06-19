<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cards extends CI_Controller {
  public function __construct(){
    CI_Controller::__construct();

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

    $url        = "https://api.scryfall.com/cards/search?order=set&q=e%3A$setCode&unique=prints";
		$return     = readUrlApi($url);
    $jsonReturn = json_decode($return, true);

    $object     = $jsonReturn["object"] ?? "";
    if($object == "error"){
      //@todo tratar erros
    } elseif($object == "list"){
      // pega todas as cartas que tem nesse set
      $arrCardsUid = [];

      $this->load->database();
      $this->db->select('car_id, car_uid');
      $this->db->from('tb_card');
      $query   = $this->db->get();
      $arrCards = $query->result_array();

      foreach($arrCards as $arrCard){
        $arrCardsUid[$arrCard["car_id"]] = $arrCard["car_uid"];
      }
      // ======================================

      $arrBatchInsert = [];
      $arrBatchUpdate = [];

      do{

        $hasMore  = $jsonReturn["has_more"] ?? false;
        $nextPage = $jsonReturn["next_page"] ?? "";
        $data     = $jsonReturn["data"] ?? array();

        foreach($data as $card){
          $arrCard = array(
            "car_arena_id"          => $card["arena_id"] ?? null,
            "car_uid"               => $card["id"] ?? null,
            "car_lang"              => $card["lang"] ?? null,
            "car_mtgo_id"           => $card["mtgo_id"] ?? null,
            "car_mtgo_foil_id"      => $card["mtgo_foil_id"] ?? null,
            "car_multiverse_ids"    => (isset($card["multiverse_ids"])) ? json_encode($card["multiverse_ids"]): null,
            "car_tcgplayer_id"      => $card["tcgplayer_id"] ?? null,
            "car_oracle_id"         => $card["oracle_id"] ?? null,
            "car_prints_search_uri" => $card["prints_search_uri"] ?? null,
            "car_rulings_uri"       => $card["rulings_uri"] ?? null,
            "car_scryfall_uri"      => $card["scryfall_uri"] ?? null,
            "car_uri"               => $card["uri"] ?? null,
            "car_all_parts"         => (isset($card["all_parts"])) ? json_encode($card["all_parts"]): null,
            "car_card_faces"        => (isset($card["card_faces"])) ? json_encode($card["card_faces"]): null,
            "car_cmc"               => $card["cmc"] ?? null,
            "car_colors"            => (isset($card["colors"])) ? json_encode($card["colors"]): null,
            "car_color_identity"    => (isset($card["color_identity"])) ? json_encode($card["color_identity"]): null,
            "car_color_indicator"   => (isset($card["color_indicator"])) ? json_encode($card["color_indicator"]): null,
            "car_edhrec_rank"       => $card["edhrec_rank"] ?? null,
            "car_foil"              => $card["foil"] ?? null,
            "car_hand_modifier"     => $card["hand_modifier"] ?? null,
            "car_layout"            => $card["layout"] ?? null,
            "car_legalities"        => (isset($card["legalities"])) ? json_encode($card["legalities"]): null,
            "car_life_modifier"     => $card["life_modifier"] ?? null,
            "car_loyalty"           => $card["loyalty"] ?? null,
            "car_mana_cost"         => $card["mana_cost"] ?? null,
            "car_name"              => $card["name"] ?? null,
            "car_cdn_id"            => 0, //sempre zero pq trigger atualiza
            "car_nonfoil"           => $card["nonfoil"] ?? null,
            "car_oracle_text"       => $card["oracle_text"] ?? null,
            "car_oversized"         => $card["oversized"] ?? null,
            "car_power"             => $card["power"] ?? null,
            "car_reserved"          => $card["reserved"] ?? null,
            "car_toughness"         => $card["toughness"] ?? null,
            "car_type_line"         => $card["type_line"] ?? null,
            "car_artist"            => $card["artist"] ?? null,
            "car_booster"           => $card["booster"] ?? null,
            "car_border_color"      => $card["border_color"] ?? null,
            "car_card_back_id"      => $card["card_back_id"] ?? null,
            "car_collector_number"  => $card["collector_number"] ?? null,
            "car_digital"           => $card["digital"] ?? null,
            "car_flavor_text"       => $card["flavor_text"] ?? null,
            "car_frame_effect"      => $card["frame_effect"] ?? null,
            "car_frame"             => $card["frame"] ?? null,
            "car_full_art"          => $card["full_art"] ?? null,
            "car_games"             => (isset($card["games"])) ? json_encode($card["games"]): null,
            "car_highres_image"     => $card["highres_image"] ?? null,
            "car_illustration_id"   => $card["illustration_id"] ?? null,
            "car_image_uris"        => (isset($card["image_uris"])) ? json_encode($card["image_uris"]): null,
            "car_prices"            => (isset($card["prices"])) ? json_encode($card["prices"]): null,
            "car_printed_name"      => $card["printed_name"] ?? null,
            "car_printed_text"      => $card["printed_text"] ?? null,
            "car_printed_type_line" => $card["printed_type_line"] ?? null,
            "car_promo"             => $card["promo"] ?? null,
            "car_promo_types"       => (isset($card["promo_types"])) ? json_encode($card["promo_types"]): null,
            "car_purchase_uris"     => (isset($card["purchase_uris"])) ? json_encode($card["purchase_uris"]): null,
            "car_rarity"            => $card["rarity"] ?? null,
            "car_related_uris"      => (isset($card["related_uris"])) ? json_encode($card["related_uris"]): null,
            "car_released_at"       => $card["released_at"] ?? null,
            "car_reprint"           => $card["reprint"] ?? null,
            "car_scryfall_set_uri"  => $card["scryfall_set_uri"] ?? null,
            "car_set_name"          => $card["set_name"] ?? null,
            "car_set_id"            => 0, //sempre zero pq trigger atualiza
            "car_set_search_uri"    => $card["set_search_uri"] ?? null,
            "car_set_type"          => $card["set_type"] ?? null,
            "car_set_uri"           => $card["set_uri"] ?? null,
            "car_set"               => $card["set"] ?? null,
            "car_story_spotlight"   => $card["story_spotlight"] ?? null,
            "car_textless"          => $card["textless"] ?? null,
            "car_variation"         => $card["car_variation"] ?? null,
            "car_variation_of"      => $card["variation_of"] ?? null,
            "car_watermark"         => $card["watermark"] ?? null,
          );

          $jaExiste = in_array($card["id"], $arrCardsUid);
          if($jaExiste){
            $carId              = array_search($card["id"], $arrCardsUid);
            $arrCard["car_id"]  = $carId;
            $arrBatchUpdate[]   = $arrCard;
          } else {
            $arrBatchInsert[]   = $arrCard;
          }
        }

        if($hasMore){
          $url        = $nextPage;
      		$return     = readUrlApi($url);
          $jsonReturn = json_decode($return, true);

          $object     = $jsonReturn["object"] ?? "";
          if($object == "error"){
            $hasMore  = false;
            $nextPage = "";
          }
        } else {
          $nextPage = "";
        }

      } while($hasMore && $nextPage != "");

      // executa os INSERTS
      if(count($arrBatchInsert) > 0){
        $retInsert = $this->db->insert_batch('tb_card', $arrBatchInsert);
        if($retInsert === false){
          //@todo tratar erro
        }
      }
      // ==================

      // executa os UPDATES
      if(count($arrBatchUpdate) > 0){
        $retUpdate = $this->db->update_batch('tb_card', $arrBatchUpdate, 'car_id');
        if($retUpdate === false){
          //@todo tratar erro
        }
      }
      // ==================
    }
  }
}
