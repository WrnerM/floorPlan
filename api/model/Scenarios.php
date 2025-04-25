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
                  b.Id AS PositionId,
                  Row,
                  Col,
                  Tool,
                  Color,
                  Tag,
                  Label,
                  AssignId,
                  Project,
                  Shift
                FROM Floors as a
                INNER JOIN Positions as b ON b.FloorId = a.Id
                WHERE SiteId = :site
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
