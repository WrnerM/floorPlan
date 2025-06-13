<?php

class UserDetails
{
  public $db = null;
  public $bms_ident = null;


  public function getUserDetails()
  {
    $response = [];

    try {

      $sql = "SELECT employee_ident as bms_ident, employee_full_name, nt, email from onboard360.dbo.ccms_employee_lists where employee_ident = :bms_ident";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':bms_ident', $this->bms_ident, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if ($stmt->rowCount() > 0) {
        $response = $data;
      } else {
        $response['success'] = 0;
        $response['message'] = "BMS ident not found";
      }
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql;
    }

    return $response;
  }
}
