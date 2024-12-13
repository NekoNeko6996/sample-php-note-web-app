<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $inputData = file_get_contents('php://input');
  $data = json_decode($inputData, true);

  if (isset($data['entry_id']) && isset($data['date_ts'])) {
    $entryId = $data['entry_id'];
    $dateTs = $data['date_ts'];

    $dataFile = './workrecord/data/ts-' . $dateTs . '.json';

    if (file_exists($dataFile)) {
      // Đọc dữ liệu từ file
      $jsonData = file_get_contents($dataFile);
      $timesheets = json_decode($jsonData, true);

      $found = false;
      foreach ($timesheets as $key => $entry) {
        if ($entry['id'] == $entryId) {
          unset($timesheets[$key]);
          $found = true;
          break;
        }
      }

      if ($found) {
        file_put_contents($dataFile, json_encode(array_values($timesheets), JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'success']);
      } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bản ghi để xóa.']);
      }
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Tệp dữ liệu không tồn tại.']);
    }
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
  }
}
?>