<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $entryId = $_POST['entry_id'];
  $dateTs = $_POST['date-ts'];

  $dataFile = 'data/ts-' . $dateTs . '.json';
  if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $timesheets = json_decode($jsonData, true);

    foreach ($timesheets as $key => $entry) {
      if ($entry['id'] == $entryId) {
        unset($timesheets[$key]);
        break;
      }
    }

    file_put_contents($dataFile, json_encode(array_values($timesheets), JSON_PRETTY_PRINT));
  }

  echo $dataFile;

  header('Location: view_work_record.php');
  exit;
}
?>