<?php
$notesDir = './notes/notes/';
$noteId = isset($_GET['id']) ? $_GET['id'] : null;

if ($noteId) {
  $noteFile = $notesDir . $noteId . '.json';

  if (file_exists($noteFile)) {
    $noteData = json_decode(file_get_contents($noteFile), true);

    // Xóa file note
    unlink($noteFile);

    // Xóa file ảnh nếu tồn tại
    if (isset($noteData['image']) && file_exists($noteData['image'])) {
      unlink($noteData['image']);
    }

    header('Location: view');
    exit();
  } else {
    echo "Note not found!";
  }
} else {
  echo "Invalid note ID!";
}