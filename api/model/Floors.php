<?php


class Floor
{
  public $db;
  public $site = null;
  public $date = null;


  public function getFloor()
  {

    try {
      $sql = "SELECT 
                a.Id AS FloorId, 
                SiteId,
                DateValid,
                Name,
                Comment,
                Scenario,
                b.Id AS PositionId,
                Row,
                Col,
                Tool,
                Color,
                Tag,
                Label,
                AssignId,
                Project,
                Shift
              FROM Floors as a
              INNER JOIN Positions as b ON b.FloorId = a.Id
              WHERE SiteId = :site AND DateValid = :date
              ORDER BY a.Id, b.Id
              ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
      $stmt->bindParam(':date', $this->date, PDO::PARAM_STR);
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

  public function addFloor($floors)

  {
    $response = ['success' => 1];

    try {
      $this->db->beginTransaction();

      $insertFloorSql = "INSERT INTO Floors (SiteId, DateValid, Name, Comment, Scenario) 
                       VALUES (:siteId, :dateValid, :name, :comment, :scenario)";
      $insertFloorStmt = $this->db->prepare($insertFloorSql);

      $insertPositionSql = "INSERT INTO Positions (FloorId, Row, Col, Tool, Color, Tag, Label, AssignId, Project, Shift) 
                          VALUES (:floorId, :row, :col, :tool, :color, :tag, :label, :assignId, :project, :shift)";
      $insertPositionStmt = $this->db->prepare($insertPositionSql);

      foreach ($floors as $floor) {
        $insertFloorStmt->execute([
          ':siteId' => $floor['siteId'],
          ':dateValid' => $floor['dateValid'],
          ':name' => $floor['name'],
          ':comment' => $floor['comment'],
          ':scenario' => $floor['scenario']
        ]);

        $floorId = $this->db->lastInsertId();

        foreach ($floor['position'] as $pos) {
          $insertPositionStmt->execute([
            ':floorId' => $floorId,
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
      }

      $this->db->commit();
    } catch (\Throwable $th) {
      $this->db->rollBack();
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
    }

    return $response;
  }

  public function editFloor($floors)
  {
    $response = ['success' => 1];

    try {
      $this->db->beginTransaction();

      $checkSqlColumn = "IF COL_LENGTH('Floors', 'Active') IS NULL
                            BEGIN
                            ALTER TABLE Floors ADD Active BIT NULL;
                            END;
                            UPDATE Floors SET Active = 0 WHERE Active IS NULL;

                            IF COL_LENGTH('Floors', 'Date_inactive') IS NULL
                            BEGIN
                            ALTER TABLE Floors ADD Date_inactive DATETIME NULL;
                            END;";

      $this->db->exec($checkSqlColumn);

      $updateFloorSql = "UPDATE Floors 
                       SET SiteId = :siteId, DateValid = :dateValid, Name = :name, Comment = :comment, Scenario = :scenario
                       WHERE Id = :floorId";
      $updateFloorStmt = $this->db->prepare($updateFloorSql);

      $deletePositionsSql = "DELETE FROM Positions WHERE FloorId = :floorId";
      $deletePositionsStmt = $this->db->prepare($deletePositionsSql);

      $insertPositionSql = "INSERT INTO Positions (FloorId, Row, Col, Tool, Color, Tag, Label, AssignId, Project, Shift)
                          VALUES (:floorId, :row, :col, :tool, :color, :tag, :label, :assignId, :project, :shift)";
      $insertPositionStmt = $this->db->prepare($insertPositionSql);

      foreach ($floors as $floor) {
        // Ensure floorId is present
        if (!isset($floor['floorId'])) {
          throw new Exception("Missing floorId for edit.");
        }

        $updateFloorStmt->execute([
          ':siteId' => $floor['siteId'],
          ':dateValid' => $floor['dateValid'],
          ':name' => $floor['name'],
          ':comment' => $floor['comment'],
          ':scenario' => $floor['scenario'],
          ':floorId' => $floor['floorId']
        ]);

        // Delete old positions
        $deletePositionsStmt->execute([':floorId' => $floor['floorId']]);

        // Insert new positions
        foreach ($floor['position'] as $pos) {
          $insertPositionStmt->execute([
            ':floorId' => $floor['floorId'],
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
