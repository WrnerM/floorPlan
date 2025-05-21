<?php

class Scenario
{
  public $db = null;
  public $site = null;

  public function getScenario()
  {

    try {
      $sql = "SELECT top 1
                Id,
                SiteId,
                DateValid,
                Name,
                Comment,
                Scenario
              FROM Floors
              WHERE SiteId = :site
              AND Scenario is NOT NULL
              ORDER BY Id DESC
              ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      $stmt->execute();
      $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // if ($stmt->rowCount() > 0) {
      //   $response = $data;
      // } else {
      //   $response = [];
      // }
    } catch (\Throwable $th) {
      $response["success"] = 0;
      $response["error"] = $th->getMessage();
      $response["sql"] = $sql;
    }

    return $response;
  }
}
