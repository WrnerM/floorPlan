<?php

class HistoricalDates
{
  public $db = null;
  public $name = null;
  public $siteId = null;

  public function getHistoricalDates()
  {

    try {

      $sql = ";with cte as (

                select name,SiteId, dateValid as DateTimeValid, FloorId, Comment, cast(dateValid as date) as dateValid from (

                select name, siteId, dateValid, id as FloorId, Comment
                , ROW_NUMBER() over (partition by name, SiteId order by dateValid desc) a 
                from Floors where DateValid <= cast(getutcdate() at time zone 'utc' at time zone 'Singapore Standard Time' as datetime) 
                and YEAR(dateValid) >= 2024 and name is not null and LTRIM(RTRIM(name)) <> ''
                ) as b where a <> 1

                union


                select name, SiteId, dateValid as DateTimeValid, id as FloorId, Comment, cast(dateValid as date) as datevalid
                from floors where dateValid >= cast(getutcdate() at time zone 'utc' at time zone 'Singapore Standard Time' as datetime) 
                and name is not null and LTRIM(RTRIM(name)) <> ''

                )

                select FloorId, dateValid, comment from (

                select *, ROW_NUMBER() over(partition by name, siteId, dateValid order by dateTimeValid desc) d from cte

                ) as e where d = 1";

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
