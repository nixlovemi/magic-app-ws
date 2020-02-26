<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH.'/application/third_party/meyfa-php-svg/autoloader.php';
use SVG\SVG;

class Sets extends CI_Controller {
  public function __construct(){
    CI_Controller::__construct();

    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    $method = $_SERVER['REQUEST_METHOD'];
    if($method == "OPTIONS") {
      die();
    }

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

  public function getSetImage(){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    $this->load->database();

    $this->db->select('set_id, set_icon_svg_uri, set_code');
    $this->db->from('tb_set');
    $this->db->join('tb_set_images', 'set_id = sim_set_id', 'left');
    $this->db->where('sim_id IS NULL');
    $this->db->where("set_icon_svg_uri <> ''");
    $this->db->where('set_released_at <=', date("Y-m-d H:i:s"));
    $this->db->limit(50);

    $query = $this->db->get();
    $arrRs = $query->result_array();

    $arrBatchInsert = [];
    foreach($arrRs as $rs){
      $urlSvg    = $rs["set_icon_svg_uri"];
      $setCode   = $rs["set_code"];
      $setId     = $rs["set_id"];
      $newGifUrl = 'sets_images/'.$setId.'-'.$setCode.'.svg';

      usleep(250000); //0.25 seg

      $dom = new DOMDocument('1.0', 'utf-8');
      $dom->load($urlSvg);
      $svg = $dom->documentElement;

      if ( ! $svg->hasAttribute('viewBox') ) { // viewBox is needed to establish
                                               // userspace coordinates
           $pattern = '/^(\d*\.\d+|\d+)(px)?$/'; // positive number, px unit optional

           $interpretable =  preg_match( $pattern, $svg->getAttribute('width'), $width ) &&
                             preg_match( $pattern, $svg->getAttribute('height'), $height );

           if ( $interpretable ) {
              $view_box = implode(' ', [0, 0, $width[0], $height[0]]);
              $svg->setAttribute('viewBox', $view_box);
          } else { // this gets sticky
              throw new Exception("viewBox is dependent on environment");
          }
      }

      $svg->setAttribute('width', '150');
      $svg->setAttribute('height', '150');
      $dom->save(FCPATH . $newGifUrl);

      $arrBatchInsert[] = array(
        "sim_set_id" => $setId,
        "sim_url"    => $newGifUrl,
      );
    }

    // executa os INSERTS
    if(count($arrBatchInsert) > 0){
      $retInsert = $this->db->insert_batch('tb_set_images', $arrBatchInsert);
      if($retInsert === false){
        //@todo tratar erro
      }
    }
    // ==================
  }

  public function updateLocalSets(){
    set_time_limit (180);
    header('Content-disposition: attachment; filename=sets.json');
    header('Content-type: application/json');
    $postVars = proccessPost();

    $this->load->database();

    $this->db->select('set_id, set_code, set_name, set_scryfall_uri, set_released_at, set_card_count, set_block_code, set_block, set_type');
    $this->db->from('tb_set');
    $this->db->where('set_released_at <=', date("Y-m-d H:i:s"));
    $this->db->order_by('set_released_at', 'DESC');

    $query = $this->db->get();
    $arrRs = $query->result_array();

    $arrJson = [];
    foreach($arrRs as $row){
      $setId           = $row["set_id"];
      $arrJson[$setId] = $row;
    }

    echo json_encode($arrJson);
  }

  public function updateLocalSetsCards(){
    set_time_limit (180);
    header('Content-disposition: attachment; filename=set_cards.json');
    header('Content-type: application/json');
    $postVars = proccessPost();

    $this->load->database();

    $dt  = date("Y-m-d H:i:s");
    $sql = "
      SELECT car_id, car_set_id
      FROM tb_card
      WHERE car_released_at <= '$dt'
      ORDER BY car_released_at DESC, car_set_id ASC, NULLIF(regexp_replace(car_collector_number, '\D', '', 'g'), '')::int ASC
    ";
    $query = $this->db->query($sql);
    $arrRs = $query->result_array();

    $arrJson = [];
    foreach($arrRs as $row){
      $setId           = $row["car_set_id"];
      if(!array_key_exists($setId, $arrJson)){
        $arrJson[$setId] = [];
      }

      $arrJson[$setId][] = $row["car_id"];
    }

    echo json_encode($arrJson);
  }
}
