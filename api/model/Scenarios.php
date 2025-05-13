<?php

class Scenario
{
  public $db = null;
  public $site = null;

  public function getScenario()
  {

    try {
      $sql = "SELECT 
                a.Id AS FloorId,
                SiteId,
                DateValid,
                Name,
                Comment,
                Scenario,
                b.*
              FROM Floors as a
              INNER JOIN Positions_active as b ON b.FloorId = a.Id
              WHERE SiteId = :site
              AND Scenario is NOT NULL
              ORDER BY a.Id, b.Id
              ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if ($stmt->rowCount() > 0) {
        $response = $data;
      } else {
        $response = [];
      }
    } catch (\Throwable $th) {
      $response["success"] = 0;
      $response["error"] = $th->getMessage();
      $response["sql"] = $sql;
    }

    return $response;
  }
}
