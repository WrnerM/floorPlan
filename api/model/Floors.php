<?php


class Floor
{
  public $db;
  public $site = null;
  public $date = null;


  public function getFloor()
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
              WHERE SiteId = :site AND DateValid = :date
              ORDER BY a.Id, b.Id
              ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      $stmt->bindParam(':date', $this->date, PDO::PARAM_STR);
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

  public function addFloor()

     
  {
    return "hello addFloor";
  }
}
