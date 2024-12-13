<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = json_decode(file_get_contents('php://input'), true);

  if (!isset($body['id'], $body['file'])) {
    echo json_encode(["status" => false, "message" => "Invalid input data"]);
    exit;
  }

  $id = $body['id'];
  $file = $body['file'];
  $file_path = './spending/data/sp-' . $file . '.json';

  try {
    if (file_exists($file_path)) {
      $jsonData = file_get_contents($file_path);
      $spendingData = json_decode($jsonData, true);

      $index = array_search($id, array_column($spendingData, 'id'));

      if ($index !== false) {
        array_splice($spendingData, $index, 1);

        file_put_contents($file_path, json_encode($spendingData, JSON_PRETTY_PRINT));
        echo json_encode(["status" => true, "message" => "Spending deleted successfully!"]);
      } else {
        echo json_encode(["status" => false, "message" => "Spending not found!"]);
      }
    } else {
      echo json_encode(["status" => false, "message" => "File not found!"]);
    }
  } catch (Exception $e) {
    echo json_encode(["status" => false, "message" => "Exception: " . $e->getMessage()]);
  }
}
