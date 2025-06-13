<?php

class Client
{
  public $db = null;
  public $site = null;


  public function getClient()
  {
    $response = [];

    try {

      $sql = "SELECT DISTINCT 
                    NAME_CAPACITYDASHBOARD,CLIENT_FULL_NAME
                FROM 
                    MCKWFMDB01.TPSREPO.DBO.VW_SC_DETAILS_LATEST_PVT
                WHERE CLIENT_FULL_NAME IN 
                    (SELECT CLIENT_FULL_NAME FROM MCKWFMDB01.TPSREPO.DBO.VW_SC_DETAILS_LATEST_PVT 
                WHERE  LOCATION_FULL_NAME LIKE :site AND STATUS = 'ACTIVE' AND NAME_CAPACITYDASHBOARD != '--') 
                    AND [STATUS] = 'ACTIVE' AND NAME_CAPACITYDASHBOARD != '--'";
      $stmt = $this->db->prepare($sql);
      $locationParam = '%' . $this->site . '%';
      $stmt->bindParam(':site', $locationParam, PDO::PARAM_STR);
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
