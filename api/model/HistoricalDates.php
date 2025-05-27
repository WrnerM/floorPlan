<?php

class HistoricalDates
{
  public $db = null;
  public $name = null;
  public $siteId = null;

  public function getHistoricalDates()
  {

    try {

      $sql = "SELECT FloorId, dateValid, comment from (
              select id as FloorId, dateValid, comment, 
              row_number() over(partition by name, siteid order by dateValid desc) as rowNum
              from floors
              where dateValid >= '2024-01-01' and 
              dateValid <= getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time'

              and name = :name and siteId = :siteId

              ) as x where rowNum > 1
              ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindParam(':siteId', $this->siteId, PDO::PARAM_STR);
      $stmt->execute();

      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $response = $stmt->rowCount() > 0 ? $data : [];
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql ?? '';
    }

    return $response;
  }
}
