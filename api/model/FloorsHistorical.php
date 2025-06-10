<?php

class FloorsHistorical
{

  public $db = null;
  public $floorId = null;

  public function editFloorsHistorical($positions)
  {
    $response = ['success' => 1];

    try {

      if ($this->floorId == null) {
        return array("success" => 0, "error" => "Missing Floor ID");
      }

      $this->db->beginTransaction();

      // Prepare SQL for inserting or updating a position
      $deleteStmt = $this->db->prepare("DELETE FROM Positions WHERE FloorId = :floorId");
      $deleteStmt->execute([
        ':floorId' => $this->floorId
      ]);

      $insertStmt = $this->db->prepare(
        "INSERT INTO Positions (FloorId, Row, Col, Tool, Color, Tag, Label, AssignId, Project, Shift)
       VALUES (:floorId, :row, :col, :tool, :color, :tag, :label, :assignId, :project, :shift)"
      );

      foreach ($positions as $pos) {
        // Insert the new/updated position
        $insertStmt->execute([
          ':floorId' => $this->floorId,
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
