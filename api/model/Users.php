<?php

class Users
{

  public $db = null;

  public function getUsers()
  {
    try {
      $sql = "SELECT * FROM Users";
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::PARAM_STR);
    } catch (\Throwable $th) {
      $response['success'] = 0;
      $response['error'] = $th->getMessage();
      $response['sql'] = $sql;
    }

    return $data;
  }

  public function addUser($params)
  {
    try {
      // Validate user_role
      $allowedRoles = ['User', 'Admin'];
      if (!in_array($params['user_role'], $allowedRoles)) {
        return [
          'success' => 0,
          'error' => 'Invalid user_role. Only "User" or "Admin" are allowed.'
        ];
      }

      // Check if user already exists by bms_ident
      $checkSql = "SELECT COUNT(*) FROM Users WHERE bms_ident = :bms_ident";
      $checkStmt = $this->db->prepare($checkSql);
      $checkStmt->bindParam(':bms_ident', $params['bms_ident']);
      $checkStmt->execute();
      $userExists = $checkStmt->fetchColumn();

      if ($userExists > 0) {
        return [
          'success' => 0,
          'error' => 'User already exists'
        ];
      }

      // Prepare insert with current timestamp
      $insertSql = "INSERT INTO Users (
                    bms_ident, full_name, nt, user_email, user_role, inserted_date_time_ph
                  ) VALUES (
                    :bms_ident, :full_name, :nt, :user_email, :user_role, :inserted_date_time_ph
                  )";

      $stmt = $this->db->prepare($insertSql);

      $currentDateTime = date("Y-m-d H:i:s");

      $stmt->bindParam(':bms_ident', $params['bms_ident']);
      $stmt->bindParam(':full_name', $params['full_name']);
      $stmt->bindParam(':nt', $params['nt']);
      $stmt->bindParam(':user_email', $params['user_email']);
      $stmt->bindParam(':user_role', $params['user_role']);
      $stmt->bindParam(':inserted_date_time_ph', $currentDateTime);

      $stmt->execute();

      return [
        'success' => 1,
        'message' => 'User added successfully',
        'timestamp' => $currentDateTime
      ];
    } catch (\Throwable $th) {
      return [
        'success' => 0,
        'error' => $th->getMessage()
      ];
    }
  }
}
