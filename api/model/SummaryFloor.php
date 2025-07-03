<?php

class SummaryFloor
{

  public $db = null;
  public $site = null;
  public $dateValid = null;

  public function getSummaryFloor()
  {

    try {
      $table = "positions_active";
      $where = " and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)";
      if ($this->dateValid !== null) {
        $where = " and dateValid = :dateValid";
        $date = new DateTime($this->dateValid); 
        $now = new DateTime(); 
        if($date < $now){
          $table = "positions";
        }
        $sql = "SELECT 1 from floors where scenario is null 
                and dateValid = :dateValid and siteId = :site";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
        $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
          $sql = "SELECT TOP 1 dateValid from floors where
                  scenario is null and dateValid <= :dateValid 
                  and siteId = :site
                  order by dateValid desc";
          $stmt = $this->db->prepare($sql);
          $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
          $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
          $stmt->execute();
          $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if($stmt->rowCount() > 0){
            foreach($data as $row){
              $this->dateValid = $row['dateValid'];
            }
          }
        }
      }

      $sql = "SELECT name, project, case when tag = 'Non Prod' then 'NonProd' else tag end as tag, 
                count(a.id) as count from  $table a 
                inner join floors b on a.FloorId = b.id 
                where scenario is null and project is not null 
              and ltrim(rtrim(project)) <> ''
              and siteid = :site
              and tag in ('Dedicated'
                          ,'Shared'
                          ,'Blocked'
                          ,'Support'
                          ,'Non Prod'
                          ,'Unassigned')
              $where
              group by name, project, tag";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
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
