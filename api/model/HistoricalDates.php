<?php

class HistoricalDates
{
  public $db = null;
  public $name = null;
  public $siteId = null;

  public function getHistoricalDates()
  {

    try {

      $sql = "SELECT 
                    f.FloorId,
                    f.dateValid,
                    f.comment,
                    COALESCE(pa.ProjectDateDeprecated, pp.ProjectDateDeprecated) AS ProjectDateDeprecated
                FROM (
                    SELECT *,
                        ROW_NUMBER() OVER (
                            PARTITION BY CAST(dateValid AS DATE) 
                            ORDER BY FloorId DESC
                        ) AS rowPerDate
                    FROM (
                        SELECT 
                            id AS FloorId,
                            dateValid,
                            comment,
                            ROW_NUMBER() OVER (
                                PARTITION BY name, siteid 
                                ORDER BY dateValid DESC
                            ) AS rowNum
                        FROM floors
                        WHERE 
                            CAST(dateValid AS DATE) >= '2024-01-01'
                            AND scenario IS NULL
                            AND name = :name 
                            AND siteId = :siteId
                    ) AS x
                    WHERE rowNum > 1
                ) AS f

                LEFT JOIN Positions_Active pa
                    ON CAST(f.dateValid AS DATE) = pa.ProjectDateDeprecated
                    AND pa.ProjectDateDeprecated >= CAST(GETDATE() AS DATE)

                LEFT JOIN Positions pp
                    ON CAST(f.dateValid AS DATE) = pp.ProjectDateDeprecated
                    AND pp.ProjectDateDeprecated < CAST(GETDATE() AS DATE)

                WHERE f.rowPerDate = 1
                ORDER BY f.dateValid DESC;

        ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindParam(':siteId', $this->siteId, PDO::PARAM_STR);
      $stmt->execute();

      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $response = $stmt->rowCount() > 0 ? $data : [];
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql ?? '';
    }

    return $response;
  }
}
