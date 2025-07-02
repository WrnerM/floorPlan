<?php

class Site
{
  public $db = null;

  public function getSite()
  {
    $response = [];

    try {
      $sql = "SELECT *
              FROM (
                  SELECT 
                      S.location_ident,
                      S.location_name,
                      S.short_name,
                      F.Name AS floor_number,
                      F.comment,
                      F.Id AS floor_id,
                      ROW_NUMBER() OVER (
                          PARTITION BY S.short_name, F.Name
                          ORDER BY F.dateValid DESC
                      ) AS rowNum
                  FROM [Floorplan].[dbo].[Sites] AS S
                  LEFT JOIN [Floorplan].[dbo].[Floors] AS F
                      ON S.short_name = F.SiteId
                      AND F.scenario IS NULL
                      AND CONVERT(date, F.dateValid) <= CONVERT(date, GETUTCDATE() AT TIME ZONE 'UTC' AT TIME ZONE 'Singapore Standard Time')
              ) AS x
              WHERE rowNum = 1
              ORDER BY short_name, floor_number;
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
