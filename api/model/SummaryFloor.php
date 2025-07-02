<?php

class SummaryFloor
{

  public $db = null;
  public $site = null;
  public $dateValid = null;

  public function getSummaryFloor()
  {

    try {
      $sql = "SELECT name, project, tag, count(a.id) as count from  positions_active a 
                inner join floors b on a.FloorId = b.id 
                where scenario is null and project is not null 
              and ltrim(rtrim(project)) <> ''
              AND dateValid <= getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time'
              and siteid = :site
              and tag in ('Dedicated'
                          ,'Shared'
                          ,'Blocked'
                          ,'Support'
                          ,'Non Prod'
                          ,'Unassigned')
              group by name, project, tag";
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
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql;
    }

    return $response;
  }
}
