<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
  public function __construct(){
    CI_Controller::__construct();

    $this->load->helper("cards_helper");
  }

  public function updateCards(){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $this->load->database();
    $this->db->select('set_id, set_code');
    $this->db->from('tb_set');
    $this->db->where('set_next_cards_update <=', date("Y-m-d H:i:s"));
    $this->db->order_by('set_released_at', 'DESC');
    $this->db->limit(3);
    $query = $this->db->get();
    $arrRs = $query->result_array();

    foreach($arrRs as $rs){
      $vSetId   = $rs["set_id"];
      $vSetCode = $rs["set_code"];

      list($retInsert, $retUpdate) = fncUpdateCardsBySet($vSetCode);
      // @todo tratar retorno

      // atualiza os sets pro proximo update
      $home       = strtotime(date("Y-m-d H:i:s"));
      $nextUpdate = date("Y-m-d H:i:s", strtotime("+1 month", $home));

      $data = array(
        'set_next_cards_update' => $nextUpdate,
      );
      $this->db->update('tb_set', $data, "set_id = $vSetId");
      // ===================================
    }
  }

  public function updateDwldCardsImg(){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $this->load->database();
    $this->db->select('car_id, car_uid, car_name, car_image_uris, car_card_faces');
    $this->db->from('tb_card');
    $this->db->join('tb_card_images', 'cim_car_id = car_id', 'left');
    $this->db->where('cim_id IS NULL');
    $this->db->order_by('car_released_at', 'DESC');
    $this->db->limit(30);

    $query = $this->db->get();
    $arrRs = $query->result_array();

    $arrBatchInsert = [];
    foreach($arrRs as $rs){
      $carId        = $rs["car_id"];
      $carUid       = $rs["car_uid"];
      $carName      = $rs["car_name"];
      $carImageUris = $rs["car_image_uris"];
      $carCardFaces = $rs["car_card_faces"];

      $arrImageUris = json_decode($carImageUris, true);
      $arrCardFaces = json_decode($carCardFaces, true);
      $arrLoop      = [];

      if(isset($arrImageUris) && count($arrImageUris) > 0){
        $urlSmall  = $arrImageUris["small"] ?? null;
        $urlNormal = $arrImageUris["normal"] ?? null;
        $urlLarge  = $arrImageUris["large"] ?? null;

        $arrLoop[] = array(
          "cim_car_id"     => $carId,
          "cim_name"       => $carName,
          "cim_url_small"  => $urlSmall,
          "cim_url_normal" => $urlNormal,
          "cim_url_large"  => $urlLarge,
        );
      } else {
        foreach($arrCardFaces as $cardFace){
          $carName   = $cardFace["name"];
          $urlSmall  = $cardFace["image_uris"]["small"] ?? null;
          $urlNormal = $cardFace["image_uris"]["normal"] ?? null;
          $urlLarge  = $cardFace["image_uris"]["large"] ?? null;

          $arrLoop[] = array(
            "cim_car_id"     => $carId,
            "cim_name"       => $carName,
            "cim_url_small"  => $urlSmall,
            "cim_url_normal" => $urlNormal,
            "cim_url_large"  => $urlLarge,
          );
        }
      }

      $i = 1;
      foreach($arrLoop as $infoLoop){
        $urlSmall  = $infoLoop["cim_url_small"] ?? null;
        $urlNormal = $infoLoop["cim_url_normal"] ?? null;
        $urlLarge  = $infoLoop["cim_url_large"] ?? null;

        $newUrlSmall  = 'cards_images/small/'.$carUid.'-'.$i.'.jpg';
        $newUrlNormal = 'cards_images/normal/'.$carUid.'-'.$i.'.jpg';
        $newUrlLarge  = 'cards_images/large/'.$carUid.'-'.$i.'.jpg';

        copy($urlSmall, FCPATH.$newUrlSmall);
        copy($urlNormal, FCPATH.$newUrlNormal);
        copy($urlLarge, FCPATH.$newUrlLarge);

        $infoLoop["cim_url_small"]  = $newUrlSmall;
        $infoLoop["cim_url_normal"] = $newUrlNormal;
        $infoLoop["cim_url_large"]  = $newUrlLarge;

        $arrBatchInsert[] = $infoLoop;
      }
    }

    // executa os INSERTS
    if(count($arrBatchInsert) > 0){
      $retInsert = $this->db->insert_batch('tb_card_images', $arrBatchInsert);
      if($retInsert === false){
        //@todo tratar erro
      }
    }
    // ==================
  }
}
