<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = $_POST['date'];
  $start_time = $_POST['start_time'];
  $end_time = $_POST['end_time'];
  $note = $_POST['note'];

  $entry = [
    'id' => uniqid(),
    'date' => $date,
    'start_time' => $start_time,
    'end_time' => $end_time,
    'note' => $note
  ];

  $dataFile = './workrecord/data/ts-' . date('Y-m') . '.json';

  if (!file_exists('data')) {
    mkdir('data', 0777, true);
  }

  $timesheets = [];
  if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $timesheets = json_decode($jsonData, true);
  }

  $timesheets[] = $entry;

  file_put_contents($dataFile, json_encode($timesheets, JSON_PRETTY_PRINT));

  header('Location: view');
}
?>