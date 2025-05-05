<?php

class SummaryFloor
{

  public $db = null;
  public $site = null;
  public $dateValid = null;

  public function getSummaryFloor()
  {

    try {
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

          $processedRow = [];

          foreach ($row as $key => $value) {
            if ($key === 'name' || $key === 'project') {
              $processedRow[$key] = $value;
            } else {
              $processedRow[$key] = is_numeric($value) ? (int)$value : $value;
            }
          }
          // Add project details to details[]
          $grouped[$name]['details'][] = $processedRow;
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
