<?php

class Client
{
  public $db = null;
  public $site = null;


  public function getClient()
  {
    $response = [];

    try {

      $sql = "
          WITH Filtered AS (
            SELECT CLIENT_FULL_NAME, NAME_CAPACITYDASHBOARD, LOCATION_FULL_NAME
            FROM TPSREPO.DBO.VW_SC_DETAILS_LATEST_PVT WITH (NOLOCK)
            WHERE 
              [STATUS] = 'ACTIVE' AND 
              NAME_CAPACITYDASHBOARD != '--'
          )

          SELECT DISTINCT f.NAME_CAPACITYDASHBOARD AS project
          FROM Filtered f
          WHERE EXISTS (
            SELECT 1
            FROM Filtered f2
            WHERE 
              f2.CLIENT_FULL_NAME = f.CLIENT_FULL_NAME AND 
              f2.LOCATION_FULL_NAME = :site
          );
      ";
      $stmt = $this->db->prepare($sql);
      $locationParam = $this->site;
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
