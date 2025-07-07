<?php

class Summary
{

  public $db = null;
  public $dateValid = null;


  public function getSummary()
  {
    try {
      if ($this->dateValid !== null) {
        $sql = ";WITH cte as (
                SELECT * from (
                  SELECT id, SiteId, row_number() over(partition by name, siteid order by dateValid desc) as rowNum from floors
                    where scenario is null and dateValid <= :dateValid
                  ) as x where rowNum = 1
                )
                SELECT siteid, tag, sum(count) as count from (
                SELECT siteid, case when tag = 'Non Prod' then 'NonProd' else tag end as tag,
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
                      group by siteid, tag
                UNION 
                SELECT siteid, case when tag = 'Non Prod' then 'NonProd' else tag end as tag,
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
                      group by siteid, tag
                ) as x group by siteid, tag order by siteid";
      }else{
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
              and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)
              group by siteid, tag
              order by siteid";
      }

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
