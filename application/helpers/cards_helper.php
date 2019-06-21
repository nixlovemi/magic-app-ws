<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// passa o array retornado pelo scryfall e devolve um array pra inserir no BD
function cardScryfallToBd($arrCardScryFall){
  $arrCard = array(
    "car_arena_id"          => $arrCardScryFall["arena_id"] ?? null,
    "car_uid"               => $arrCardScryFall["id"] ?? null,
    "car_lang"              => $arrCardScryFall["lang"] ?? null,
    "car_mtgo_id"           => $arrCardScryFall["mtgo_id"] ?? null,
    "car_mtgo_foil_id"      => $arrCardScryFall["mtgo_foil_id"] ?? null,
    "car_multiverse_ids"    => (isset($arrCardScryFall["multiverse_ids"])) ? json_encode($arrCardScryFall["multiverse_ids"]): null,
    "car_tcgplayer_id"      => $arrCardScryFall["tcgplayer_id"] ?? null,
    "car_oracle_id"         => $arrCardScryFall["oracle_id"] ?? null,
    "car_prints_search_uri" => $arrCardScryFall["prints_search_uri"] ?? null,
    "car_rulings_uri"       => $arrCardScryFall["rulings_uri"] ?? null,
    "car_scryfall_uri"      => $arrCardScryFall["scryfall_uri"] ?? null,
    "car_uri"               => $arrCardScryFall["uri"] ?? null,
    "car_all_parts"         => (isset($arrCardScryFall["all_parts"])) ? json_encode($arrCardScryFall["all_parts"]): null,
    "car_card_faces"        => (isset($arrCardScryFall["card_faces"])) ? json_encode($arrCardScryFall["card_faces"]): null,
    "car_cmc"               => $arrCardScryFall["cmc"] ?? null,
    "car_colors"            => (isset($arrCardScryFall["colors"])) ? json_encode($arrCardScryFall["colors"]): null,
    "car_color_identity"    => (isset($arrCardScryFall["color_identity"])) ? json_encode($arrCardScryFall["color_identity"]): null,
    "car_color_indicator"   => (isset($arrCardScryFall["color_indicator"])) ? json_encode($arrCardScryFall["color_indicator"]): null,
    "car_edhrec_rank"       => $arrCardScryFall["edhrec_rank"] ?? null,
    "car_foil"              => $arrCardScryFall["foil"] ?? null,
    "car_hand_modifier"     => $arrCardScryFall["hand_modifier"] ?? null,
    "car_layout"            => $arrCardScryFall["layout"] ?? null,
    "car_legalities"        => (isset($arrCardScryFall["legalities"])) ? json_encode($arrCardScryFall["legalities"]): null,
    "car_life_modifier"     => $arrCardScryFall["life_modifier"] ?? null,
    "car_loyalty"           => $arrCardScryFall["loyalty"] ?? null,
    "car_mana_cost"         => $arrCardScryFall["mana_cost"] ?? null,
    "car_name"              => $arrCardScryFall["name"] ?? null,
    "car_cdn_id"            => 0, //sempre zero pq trigger atualiza
    "car_nonfoil"           => $arrCardScryFall["nonfoil"] ?? null,
    "car_oracle_text"       => $arrCardScryFall["oracle_text"] ?? null,
    "car_oversized"         => $arrCardScryFall["oversized"] ?? null,
    "car_power"             => $arrCardScryFall["power"] ?? null,
    "car_reserved"          => $arrCardScryFall["reserved"] ?? null,
    "car_toughness"         => $arrCardScryFall["toughness"] ?? null,
    "car_type_line"         => $arrCardScryFall["type_line"] ?? null,
    "car_artist"            => $arrCardScryFall["artist"] ?? null,
    "car_booster"           => $arrCardScryFall["booster"] ?? null,
    "car_border_color"      => $arrCardScryFall["border_color"] ?? null,
    "car_card_back_id"      => $arrCardScryFall["card_back_id"] ?? null,
    "car_collector_number"  => $arrCardScryFall["collector_number"] ?? null,
    "car_digital"           => $arrCardScryFall["digital"] ?? null,
    "car_flavor_text"       => $arrCardScryFall["flavor_text"] ?? null,
    "car_frame_effect"      => $arrCardScryFall["frame_effect"] ?? null,
    "car_frame"             => $arrCardScryFall["frame"] ?? null,
    "car_full_art"          => $arrCardScryFall["full_art"] ?? null,
    "car_games"             => (isset($arrCardScryFall["games"])) ? json_encode($arrCardScryFall["games"]): null,
    "car_highres_image"     => $arrCardScryFall["highres_image"] ?? null,
    "car_illustration_id"   => $arrCardScryFall["illustration_id"] ?? null,
    "car_image_uris"        => (isset($arrCardScryFall["image_uris"])) ? json_encode($arrCardScryFall["image_uris"]): null,
    "car_prices"            => (isset($arrCardScryFall["prices"])) ? json_encode($arrCardScryFall["prices"]): null,
    "car_printed_name"      => $arrCardScryFall["printed_name"] ?? null,
    "car_printed_text"      => $arrCardScryFall["printed_text"] ?? null,
    "car_printed_type_line" => $arrCardScryFall["printed_type_line"] ?? null,
    "car_promo"             => $arrCardScryFall["promo"] ?? null,
    "car_promo_types"       => (isset($arrCardScryFall["promo_types"])) ? json_encode($arrCardScryFall["promo_types"]): null,
    "car_purchase_uris"     => (isset($arrCardScryFall["purchase_uris"])) ? json_encode($arrCardScryFall["purchase_uris"]): null,
    "car_rarity"            => $arrCardScryFall["rarity"] ?? null,
    "car_related_uris"      => (isset($arrCardScryFall["related_uris"])) ? json_encode($arrCardScryFall["related_uris"]): null,
    "car_released_at"       => $arrCardScryFall["released_at"] ?? null,
    "car_reprint"           => $arrCardScryFall["reprint"] ?? null,
    "car_scryfall_set_uri"  => $arrCardScryFall["scryfall_set_uri"] ?? null,
    "car_set_name"          => $arrCardScryFall["set_name"] ?? null,
    "car_set_id"            => 0, //sempre zero pq trigger atualiza
    "car_set_search_uri"    => $arrCardScryFall["set_search_uri"] ?? null,
    "car_set_type"          => $arrCardScryFall["set_type"] ?? null,
    "car_set_uri"           => $arrCardScryFall["set_uri"] ?? null,
    "car_set"               => $arrCardScryFall["set"] ?? null,
    "car_story_spotlight"   => $arrCardScryFall["story_spotlight"] ?? null,
    "car_textless"          => $arrCardScryFall["textless"] ?? null,
    "car_variation"         => $arrCardScryFall["car_variation"] ?? null,
    "car_variation_of"      => $arrCardScryFall["variation_of"] ?? null,
    "car_watermark"         => $arrCardScryFall["watermark"] ?? null,
  );
  return $arrCard;
}

function fncUpdateCardsBySet($setCode){
  $ci =& get_instance();
  $ci->load->helper("utils_helper");

  $url        = "https://api.scryfall.com/cards/search?order=set&q=e%3A$setCode&unique=prints";
  $return     = readUrlApi($url);
  $jsonReturn = json_decode($return, true);

  $object     = $jsonReturn["object"] ?? "";
  if($object == "error"){
    //@todo tratar erros
  } elseif($object == "list"){
    // pega todas as cartas que tem nesse set
    $arrCardsUid = [];

    $ci->load->database();
    $ci->db->select('car_id, car_uid');
    $ci->db->from('tb_card');
    $ci->db->where('car_set=', $setCode);
    $query    = $ci->db->get();
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
        $arrCard = cardScryfallToBd($card);

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
    $retInsert = false;
    if(count($arrBatchInsert) > 0){
      $retInsert = $ci->db->insert_batch('tb_card', $arrBatchInsert);
      if($retInsert === false){
        //@todo tratar erro
      }
    }
    // ==================

    // executa os UPDATES
    $retUpdate = false;
    if(count($arrBatchUpdate) > 0){
      $retUpdate = $ci->db->update_batch('tb_card', $arrBatchUpdate, 'car_id');
      if($retUpdate === false){
        //@todo tratar erro
      }
    }
    // ==================
  }
}
