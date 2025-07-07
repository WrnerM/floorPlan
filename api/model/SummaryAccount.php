<?php

class SummaryAccount
{

  public $db = null;
  public $site = null;
  public $dateValid = null;



  public function getSummaryAccount()
  {
    try {
      if ($this->dateValid !== null) {
        $sql = ";WITH cte as (
                SELECT * from (
                  SELECT id, row_number() over(partition by name, siteid order by dateValid desc) as rowNum from floors
                    where scenario is null and siteid = :site and cast(dateValid as date) <= :dateValid
                  ) as x where rowNum = 1
                )
                SELECT account, sum(seat_count) as seat_count from (
                SELECT * FROM (
                SELECT Project as account, 
                  count(a.id) as seat_count 
                  FROM positions as a
                  INNER JOIN cte as b ON b.Id = a.FloorId
                  WHERE Project is not null and ltrim(rtrim(project)) <> ''
                      and tag in (
                                      'Dedicated'
                                      ,'Shared'
                                      ,'Blocked'
                                      ,'Support'
                                      ,'Non Prod'
                                      ,'Unassigned'
                                  )
                  GROUP BY Project 
                
                UNION 

                SELECT 
                        'total' AS account,
                        count(a.id) AS seat_count
                    FROM positions AS a
                    INNER JOIN cte AS b ON b.Id = a.FloorId
                    WHERE Project IS NOT NULL and ltrim(rtrim(project)) <> ''
                      and tag in (
                                    'Dedicated'
                                    ,'Shared'
                                    ,'Blocked'
                                    ,'Support'
                                    ,'Non Prod'
                                    ,'Unassigned'
                                )

                    ) as x 
              UNION 

                SELECT * FROM (
                SELECT Project as account, 
                  count(a.id) as seat_count 
                  FROM positions_active as a
                  INNER JOIN cte as b ON b.Id = a.FloorId
                  WHERE Project is not null and ltrim(rtrim(project)) <> ''
                      and tag in (
                                      'Dedicated'
                                      ,'Shared'
                                      ,'Blocked'
                                      ,'Support'
                                      ,'Non Prod'
                                      ,'Unassigned'
                                  )
                  GROUP BY Project 
                  
                  UNION 

                  SELECT 
                          'total' AS account,
                          count(a.id) AS seat_count
                      FROM positions_active AS a
                      INNER JOIN cte AS b ON b.Id = a.FloorId
                      WHERE Project IS NOT NULL and ltrim(rtrim(project)) <> ''
                        and tag in (
                                      'Dedicated'
                                      ,'Shared'
                                      ,'Blocked'
                                      ,'Support'
                                      ,'Non Prod'
                                      ,'Unassigned'
                                  )

                      ) as y 
                  ) as z group by account";
      }else{
        $sql = ";WITH cte as (
                SELECT * from (
                  SELECT id, row_number() over(partition by name, siteid order by dateValid desc) as rowNum from floors
                    where scenario is null and siteid = :site
                    and cast(dateValid as date) <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as date)
                  ) as x where rowNum = 1
                )
                SELECT Project as account, 
                  count(a.id) as seat_count 
                  FROM positions_active as a
                  INNER JOIN cte as b ON b.Id = a.FloorId
                  WHERE Project is not null and ltrim(rtrim(project)) <> ''
                      and tag in (
                                      'Dedicated'
                                      ,'Shared'
                                      ,'Blocked'
                                      ,'Support'
                                      ,'Non Prod'
                                      ,'Unassigned'
                                  )
                        and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)
                  GROUP BY Project

                  UNION 

                  SELECT 
                          'total' AS account,
                          count(a.id) AS seat_count
                      FROM positions_active AS a
                      INNER JOIN cte AS b ON b.Id = a.FloorId
                      WHERE Project IS NOT NULL and ltrim(rtrim(project)) <> ''
                        and tag in (
                                      'Dedicated'
                                      ,'Shared'
                                      ,'Blocked'
                                      ,'Support'
                                      ,'Non Prod'
                                      ,'Unassigned'
                                  )
                  and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)";
      }

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      if ($this->dateValid !== null) {
        $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
      }
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $response = $stmt->rowCount() > 0 ? $data : [];
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql;
    }

    return $response;
  }
}
