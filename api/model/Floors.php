<?php


class Floor
{
  public $db;
  public $site = null;
  public $date = null;
  public $floorId = null;



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
                b.*
              FROM Floors as a
              INNER JOIN Positions_active as b ON b.FloorId = a.Id
              WHERE SiteId = :site
              ORDER BY a.Id, b.Id
              ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':site', $this->site, PDO::PARAM_STR);
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

      $insertPositionSql = "INSERT INTO Positions_Active (FloorId, Row, Col, Tool, Color, Tag, Label, AssignId, Project, Shift) 
                          VALUES (:floorId, :row, :col, :tool, :color, :tag, :label, :assignId, :project, :shift)";
      $insertPositionStmt = $this->db->prepare($insertPositionSql);

      foreach ($floors as $floor) {
        $siteID = $floor['siteId'];
        $name = $floor['name'];

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

        //DELETE Floor ID not included on the latest dateValid.
        $sql = "WITH floorCTE AS (
                    SELECT * 
                    FROM (
                        SELECT 
                            id AS floorIdent, 
                            siteid, 
                            name, 
                            ROW_NUMBER() OVER(PARTITION BY name, siteid ORDER BY datevalid DESC) AS rowNum 
                        FROM floors 
                        WHERE siteid = :siteName AND name = :floorname AND scenario IS NULL
                    ) AS z 
                    WHERE rowNum > 1
                )
                DELETE a
                FROM positions_active a
                INNER JOIN floorCTE b ON a.FloorId = b.floorIdent;";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':siteName', $siteID, PDO::PARAM_STR);
        $stmt->bindParam(':floorname', $name, PDO::PARAM_STR);
        $stmt->execute();
      }


      $this->db->commit();
    } catch (\Throwable $th) {
      $this->db->rollBack();
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
    }

    return $response;
  }

  // public function editFloor($floors)
  // {
  //   $response = ['success' => 1];

  //   try {
  //     $this->db->beginTransaction();

  //     $checkSqlColumn = "IF COL_LENGTH('Floors', 'Active') IS NULL
  //                           BEGIN
  //                           ALTER TABLE Floors ADD Active BIT NULL;
  //                           END;
  //                           UPDATE Floors SET Active = 0 WHERE Active IS NULL;

  //                           IF COL_LENGTH('Floors', 'Date_inactive') IS NULL
  //                           BEGIN
  //                           ALTER TABLE Floors ADD Date_inactive DATETIME NULL;
  //                           END;";

  //     $this->db->exec($checkSqlColumn);

  //     $updateFloorSql = "UPDATE Floors 
  //                      SET SiteId = :siteId, DateValid = :dateValid, Name = :name, Comment = :comment, Scenario = :scenario
  //                      WHERE Id = :floorId";
  //     $updateFloorStmt = $this->db->prepare($updateFloorSql);

  //     $deletePositionsSql = "DELETE FROM Positions WHERE FloorId = :floorId";
  //     $deletePositionsStmt = $this->db->prepare($deletePositionsSql);


  //     $insertPositionSql = "INSERT INTO Positions (FloorId, Row, Col, Tool, Color, Tag, Label, AssignId, Project, Shift)
  //                         VALUES (:floorId, :row, :col, :tool, :color, :tag, :label, :assignId, :project, :shift)";
  //     $insertPositionStmt = $this->db->prepare($insertPositionSql);

  //     foreach ($floors as $floor) {
  //       // Ensure floorId is present
  //       if (!isset($floor['floorId'])) {
  //         throw new Exception("Missing floorId for edit.");
  //       }

  //       $updateFloorStmt->execute([
  //         ':siteId' => $floor['siteId'],
  //         ':dateValid' => $floor['dateValid'],
  //         ':name' => $floor['name'],
  //         ':comment' => $floor['comment'],
  //         ':scenario' => $floor['scenario'],
  //         ':floorId' => $floor['floorId']
  //       ]);

  //       // Delete old positions
  //       $deletePositionsStmt->execute([':floorId' => $floor['floorId']]);

  //       // Insert new positions
  //       foreach ($floor['position'] as $pos) {
  //         $insertPositionStmt->execute([
  //           ':floorId' => $floor['floorId'],
  //           ':row' => $pos['row'],
  //           ':col' => $pos['col'],
  //           ':tool' => $pos['tool'],
  //           ':color' => $pos['color'],
  //           ':tag' => $pos['tag'],
  //           ':label' => $pos['label'],
  //           ':assignId' => $pos['assignId'],
  //           ':project' => $pos['project'],
  //           ':shift' => $pos['shift']
  //         ]);
  //       }
  //     }

  //     $this->db->commit();
  //   } catch (\Throwable $th) {
  //     $this->db->rollBack();
  //     $response['success'] = 0;
  //     $response['error'] = $th->getMessage();
  //   }

  //   return $response;
  // }

  // public function deleteFloor()
  // {


  //   try {


  //     $this->db->beginTransaction();
  //     $sql = "DELETE FROM Positions_Active WHERE floorId = :floorId";
  //     $stmt = $this->db->prepare($sql);
  //     $stmt->bindParam(':floorId', $this->floorId, PDO::PARAM_STR);
  //     $stmt->execute();

  //     $this->db->commit();

  //     $response = ['success' => 1];
  //   } catch (\Throwable $th) {
  //     $response['success'] = 0;
  //     $response['error'] = $th->getMessage();
  //     $response['sql'] = $sql ?? '';

  //     $this->db->rollBack();
  //   }

  //   return $response;
  // }
}
