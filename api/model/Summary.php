<?php

class Summary
{

  public $db = null;
  public $site = null;
  public $dateValid = null;

  public function getLatestDateValid()
  {
    $sql = "SELECT MAX(dateValid) AS latestDateValid FROM Floors WHERE siteID = :site";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':site', $this->site);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['latestDateValid'] : null;
  }


  public function getSummary()
  {
    $sql = null;

    try {

      if (!$this->dateValid) {
        $this->dateValid = $this->getLatestDateValid();
      }

      if (!$this->dateValid || !$this->site) {
        return ['success' => 0, 'error' => 'Missing dateValid or site parameter'];
      }

      $sql = "exec [usp_floor_summary] @dateValid = '{$this->dateValid}', @site = '{$this->site}'";
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // if ($stmt->rowCount() > 0) {
      //   $response = $data;
      // } else {
      //   $response = [];
      // }

      if ($stmt->rowCount() > 0) {
        // Group data by "name"
        $grouped = [];

        foreach ($data as $row) {
          $name = $row['name'];

          if (!isset($grouped[$name])) {
            $grouped[$name] = [
              'name' => $name,
              'total' => [],
              'details' => []
            ];
          }

          // Sum up values for "total"
          foreach ($row as $key => $value) {
            if ($key === 'name' || $key === 'project') continue;
            if (!isset($grouped[$name]['total'][$key])) {
              $grouped[$name]['total'][$key] = 0;
            }

            $grouped[$name]['total'][$key] += (int) $value;
          }

          // Add project details to details[]
          $grouped[$name]['details'][] = $row;
        }

        // Reset array keys to be numeric (optional)
        $response = $grouped;
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
