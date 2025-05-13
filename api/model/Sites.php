<?php

class Site
{
  public $db = null;

  public function getSite()
  {
    $response = [];

    try {
      $sql = "SELECT * from (
              SELECT 
                    S.[location_ident]
                    ,S.[location_name]
                    ,S.[short_name]
                    ,F.[Name] as floor_number
                    ,F.[comment]
                    ,F.[Id] as floor_id
                  ,row_number() over(partition by name, short_name order by datevalid desc) as rowNum
                FROM [Floorplan].[dbo].[Sites] AS S 
                LEFT JOIN [Floorplan].[dbo].[Floors] AS F ON S.short_name = F.SiteId
                ) as x where rowNum = 1";
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
