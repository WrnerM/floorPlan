<?php

class Summary
{

  public $db = null;
  public $dateValid = null;


  public function getSummary()
  {
    $sql = null;

    try {

      $sql = "SELECT siteid, tag, count(a.id) as count from positions_active a 
                inner join floors b on a.FloorId = b.id 
                where scenario is null and project is not null 
              and ltrim(rtrim(project)) <> ''
              and dateValid <= getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time'
              and tag in ('Dedicated'
                            ,'Shared'
                            ,'Blocked'
                            ,'Support'
                            ,'Non Prod'
                            ,'Unassigned')
              group by siteid, tag
              ";
      $stmt = $this->db->prepare($sql);
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
