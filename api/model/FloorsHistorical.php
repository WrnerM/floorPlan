<?php

class FloorsHistorical
{

  public $db = null;

  public function editFloorsHistorical($positions)
  {
    $response = ['success' => 1];

    try {
      $this->db->beginTransaction();

      // Prepare SQL for inserting or updating a position
      $deleteStmt = $this->db->prepare("DELETE FROM Positions WHERE FloorId = :floorId");

      $insertStmt = $this->db->prepare(
        "INSERT INTO Positions (FloorId, Row, Col, Tool, Color, Tag, Label, AssignId, Project, Shift)
       VALUES (:floorId, :row, :col, :tool, :color, :tag, :label, :assignId, :project, :shift)"
      );
      $currentFloorId = null;
      foreach ($positions as $pos) {
        // Basic validation
        if (!isset($pos['floorId'])) {
          $this->db->rollBack();
          return array("success" => 0, "error" => "Missing Floor ID");
        }

        // Delete any existing position at that floor/row/col
        $floorid = $pos['floorId'];
        if($floorid != $currentFloorId){
          $currentFloorId = $floorid;
          $deleteStmt->execute([
            ':floorId' => $pos['floorId']
          ]);
        }

        // Insert the new/updated position
        $insertStmt->execute([
          ':floorId' => $pos['floorId'],
          ':row' => $pos['row'],
          ':col' => $pos['col'],
          ':tool' => $pos['tool'],
          ':color' => $pos['color'],
          ':tag' => $pos['tag'],
          ':label' => $pos['label'],
          ':assignId' => $pos['assignId'],
          ':project' => $pos['project'],
          ':shift' => $pos['shift']
        ]);
      }

      $this->db->commit();
    } catch (\Throwable $th) {
      $this->db->rollBack();
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
    }

    return $response;
  }
}
