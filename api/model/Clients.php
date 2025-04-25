<?php

class Client
{
  public $db = null;
  public $site = null;


  public function getClient()
  {
    $response = [];

    try {

      $sql = "SELECT client_ident, client_name FROM client_location_list WHERE location_ident=:site";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      //$response['success'] = 1;

      if ($stmt->rowCount() > 0) {
        $response = $data;
      } else {
        $response = [];
      }
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql;
    }

    return $response;
  }
}
