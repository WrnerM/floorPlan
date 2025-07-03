<?php

class Summary
{

  public $db = null;
  public $dateValid = null;


  public function getSummary()
  {
    try {
      $where = " and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)";
      if ($this->dateValid !== null) {
        $sql = "SELECT 1 from floors where scenario is null and dateValid = :dateValid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
          $sql = "SELECT TOP 1 dateValid from floors where
                  scenario is null and dateValid <= :dateValid 
                  order by dateValid desc";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
          $stmt->execute();
          $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if($stmt->rowCount() > 0){
            foreach($data as $row){
              $this->dateValid = $row['dateValid'];
            }
          }
        }
      }

      $sql = "SELECT siteid, case when tag = 'Non Prod' then 'NonProd' else tag end as tag, 
              count(a.id) as count from positions_active a 
                inner join floors b on a.FloorId = b.id 
                where scenario is null and project is not null 
              and ltrim(rtrim(project)) <> ''
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
      if ($this->dateValid !== null) {
        $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
      }
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
