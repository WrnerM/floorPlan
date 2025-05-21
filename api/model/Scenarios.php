<?php

class Scenario
{
  public $db = null;
  public $site = null;

  public function getScenario()
  {

    try {
      $sql = "SELECT floorId,
                siteId,
                dateValid,
                name,
                comment,
                scenario
              FROM (
                SELECT 
                  Id as floorId,
                  SiteId,
                  DateValid,
                  Name,
                  Comment,
                  Scenario,
                  row_number() over(partition by name, siteid order by datevalid desc) as rowNum
                FROM Floors
                WHERE SiteId = :site
                AND Scenario is not null
              ) as x where rowNum = 1
              order by name";

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
