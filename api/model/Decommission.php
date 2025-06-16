<?php

class Decommission
{
  public $db = null;
  public $floor = null;
  public $site = null;


  public function getDecommission()
  {
    $response = [];

    try {

      $sql = "SELECT * from Decommission where FLOOR = :floor and SITE = :site";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':floor', $this->floor, PDO::PARAM_STR);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if ($stmt->rowCount() > 0) {
        $response = $data;
      } else {
        $response['success'] = 0;
      }
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql;
    }

    return $response;
  }


  // public function addDecommission($params)
  // {
  //   $response = [];

  //   try {
  //     $this->db->beginTransaction();

  //     $sql = "INSERT INTO Decommission (FLOOR, SITE, PROJECT, DECOMISSIONED_DATE)
  //             values (:floor, :site, :project, :decomission_date)";
  //   } catch (\Throwable $th) {
  //     $response['success'] = 0;
  //     $response['error'] = $th->getMessage();
  //     $response['sql'] = $sql;
  //   }
  // }
}
