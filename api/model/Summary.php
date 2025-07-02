<?php

class Summary
{

  public $db = null;
  public $dateValid = null;


  public function getSummary()
  {
    $sql = null;

    try {
      // $where = "";
      // if ($this->dateValid !== null) {
      //   $where .= " AND DateValid = :dateValid";
      // }

      $sql = "SELECT siteid, case when tag = 'Non Prod' then 'NonProd' else tag end as tag, 
              count(a.id) as count from positions_active a 
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
              $where
              group by siteid, tag
              ";
      $stmt = $this->db->prepare($sql);
      // if ($this->dateValid !== null) {
      //   $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
      // }
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
