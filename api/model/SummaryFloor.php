<?php

class SummaryFloor
{

  public $db = null;
  public $site = null;
  public $dateValid = null;

  public function getSummaryFloor()
  {

    try {
      if ($this->dateValid !== null) {
        $sql = ";WITH cte as (
                SELECT * from (
                  SELECT id, name, row_number() over(partition by name, siteid order by dateValid desc) as rowNum from floors
                    where scenario is null and siteid = :site and dateValid <= :dateValid
                  ) as x where rowNum = 1
                )
                SELECT name, project, tag, sum(count) as count from (
                SELECT name, project, case when tag = 'Non Prod' then 'NonProd' else tag end as tag,
                        count(a.id) as count from positions_active a 
                        inner join cte b on a.FloorId = b.id 
                        where project is not null 
                      and ltrim(rtrim(project)) <> ''
                      and tag in ('Dedicated'
                                  ,'Shared'
                                  ,'Blocked'
                                  ,'Support'
                                  ,'Non Prod'
                                  ,'Unassigned')
                      group by name, project, tag
                UNION 
                  
                SELECT name, project, case when tag = 'Non Prod' then 'NonProd' else tag end as tag,
                        count(a.id) as count from positions a 
                        inner join cte b on a.FloorId = b.id 
                        where project is not null 
                      and ltrim(rtrim(project)) <> ''
                      and tag in ('Dedicated'
                                  ,'Shared'
                                  ,'Blocked'
                                  ,'Support'
                                  ,'Non Prod'
                                  ,'Unassigned')
                      group by name, project, tag
                ) as x group by name, project, tag";
      }else{
        $sql = "SELECT name, project, case when tag = 'Non Prod' then 'NonProd' else tag end as tag, 
                count(a.id) as count from positions_active a 
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
              and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)
              group by name, project, tag
              order by name";
      }


      
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
