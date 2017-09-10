<?php
namespace app\helpers;

class Tools {
  
  /**
   * generates global unique id
   *
   * format: hhhhhhhh-hhhh-hhhh-hhhh-hhhhhhhhhhhh
   *
   * @return string GUID
   */
  static public function com_create_guid()
  {
    mt_srand((double)microtime()*10000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    return substr($charid, 0, 8).'-'.substr($charid, 8, 4).'-'.substr($charid,12, 4).'-'.substr($charid,16, 4).'-'.substr($charid,20,12);
  }
  
  
}