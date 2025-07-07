<?php

class SummaryAccount
{

  public $db = null;
  public $site = null;
  public $dateValid = null;



  public function getSummaryAccount()
  {
    try {
      $table = "positions_active";
      $where = " and dateValid <= cast(getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time' as datetime)";
      if ($this->dateValid !== null) {
        $date = new DateTime($this->dateValid); 
        $now = new DateTime(); 
        if($date < $now){
          $table = "positions";
        }

        $sql = ";WITH cte as (
                SELECT * from (
                  SELECT id, row_number() over(partition by name, siteid order by dateValid desc) as rowNum from floors
                    where scenario is null and siteid = :site and dateValid <= :dateValid
                  ) as x where rowNum = 1
                )

                SELECT Project as account, 
                      count(a.id) as seat_count 
                    FROM $table as a
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

                SELECT 'total' AS account,
                        count(a.id) AS seat_count
                    FROM $table AS a
                    INNER JOIN cte AS b ON b.Id = a.FloorId
                    WHERE Project IS NOT NULL and ltrim(rtrim(project)) <> ''
                    and tag in (
                                  'Dedicated'
                                  ,'Shared'
                                  ,'Blocked'
                                  ,'Support'
                                  ,'Non Prod'
                                  ,'Unassigned'
                              )";
      }else{
        $sql = " SELECT Project as account, 
                  count(a.id) as seat_count 
                  FROM $table as a
                  INNER JOIN floors as b ON b.Id = a.FloorId
                  WHERE siteId = :site1
                      and Project is not null and ltrim(rtrim(project)) <> ''
                      and scenario is null
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
                      FROM $table AS a
                      INNER JOIN floors AS b ON b.Id = a.FloorId
                      WHERE siteId = :site2
                        and Project IS NOT NULL and ltrim(rtrim(project)) <> ''
                        and Scenario IS NULL
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
      if ($this->dateValid !== null) {
        $stmt->bindParam(':dateValid', $this->dateValid, PDO::PARAM_STR);
        $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      }else{
        $stmt->bindParam(':site1', $this->site, PDO::PARAM_STR);
        $stmt->bindParam(':site2', $this->site, PDO::PARAM_STR);
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
