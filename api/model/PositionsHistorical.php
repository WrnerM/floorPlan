<?php

class PositionsHistorical
{

  public $db = null;
  public $floorId = null;

  public function getPositionsHistorical()
  {

    try {
      $data = array();
      $sql = "SELECT id, floorId, row, col, tool, color, tag, label, assignId, project, shift 
              FROM Positions WHERE floorId = :floorId and project is not null
              and tag in (
                                    'Dedicated'
                                    ,'Shared'
                                    ,'Blocked'
                                    ,'Support'
                                    ,'NonProd'
                                    ,'Unassigned'
                                )";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':floorId', $this->floorId, PDO::PARAM_STR);
      $stmt->execute();

      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if($stmt->rowCount() <= 0){
        $sql = "SELECT id, floorId, row, col, tool, color, tag, label, assignId, project, shift 
              FROM positions_active WHERE floorId = :floorId and project is not null
              and tag in (
                                    'Dedicated'
                                    ,'Shared'
                                    ,'Blocked'
                                    ,'Support'
                                    ,'NonProd'
                                    ,'Unassigned'
                                )";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floorId', $this->floorId, PDO::PARAM_STR);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      $response = count($data) > 0 ? $data : [];
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql ?? '';
    }
    return $response;
  }
}
