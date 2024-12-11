<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $noteId = $_POST['note_id'] ?? '';
  $taskIndex = $_POST['task_index'] ?? '';
  $taskStatus = $_POST['task_status'] ?? '';

  $notesDir = './notes/notes/';
  $filePath = $notesDir . $noteId . '.json';

  if (file_exists($filePath)) {
    $noteData = file_get_contents($filePath);
    $note = json_decode($noteData, true);

    if (isset($note['tasks'][$taskIndex])) {
      $note['tasks'][$taskIndex] = [
        'label' => $note['tasks'][$taskIndex]['label'],
        'completed' => $taskStatus === 'true'
      ];

      file_put_contents($filePath, json_encode($note, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Task not found']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Note not found']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}