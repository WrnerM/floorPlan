<?php

class Client
{
  public $db = null;
  public $location_ident = null;


  public function getClient()
  {
    $response = [];

    try {

      $sql = "SELECT distinct account_full_name
                from TPSWEB.[dbo].SC_CONFIG
              where status <>'Dark' and ccms_location_ident = :location_ident
              order by account_full_name";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':location_ident', $this->location_ident, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
