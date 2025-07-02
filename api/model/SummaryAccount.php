<?php

class SummaryAccount
{

  public $db = null;
  public $site = null;
  public $dateValid = null;



  public function getSummaryAccount()
  {
    try {
      $table = "Positions_Active";
      if ($this->dateValid !== null) {
        $table = "Positions";
      }

      $sql1 = "SELECT Project as account, 
            count(a.id) as seat_count 
            FROM $table as a
            INNER JOIN floors as b ON b.Id = a.FloorId
            WHERE siteId = :site1 
              and Project is not null and ltrim(rtrim(project)) <> ''
              and scenario is null
              AND dateValid <= getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time'
              and tag in (
                            'Dedicated'
                            ,'Shared'
                            ,'Blocked'
                            ,'Support'
                            ,'Non Prod'
                            ,'Unassigned'
                         )";

      if ($this->dateValid !== null) {
        $sql1 .= " AND DateValid = :dateValid1";
      }

      $sql1 .= " GROUP BY Project";
      
      $sql2 = "SELECT 
                'total' AS account,
                count(a.id) AS seat_count
            FROM $table AS a
            INNER JOIN floors AS b ON b.Id = a.FloorId
            WHERE siteId = :site2
              AND Project IS NOT NULL and ltrim(rtrim(project)) <> ''
              AND Scenario IS NULL
              AND dateValid <= getutcdate() at time zone 'UTC' at time zone 'Singapore Standard Time'
              and tag in (
                            'Dedicated'
                            ,'Shared'
                            ,'Blocked'
                            ,'Support'
                            ,'Non Prod'
                            ,'Unassigned'
                         )";
      if ($this->dateValid !== null) {
        $sql2 .= " AND DateValid = :dateValid2";
      }

      // Combine both SELECTs
      $sql = $sql1 . " UNION ALL " . $sql2;

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site1', $this->site, PDO::PARAM_STR);
      $stmt->bindParam(':site2', $this->site, PDO::PARAM_STR);

      if ($this->dateValid !== null) {
        $stmt->bindParam(':dateValid1', $this->dateValid, PDO::PARAM_STR);
        $stmt->bindParam(':dateValid2', $this->dateValid, PDO::PARAM_STR);
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
