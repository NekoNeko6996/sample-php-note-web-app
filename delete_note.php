<?php
$notesDir = './notes/';
$noteId = isset($_GET['id']) ? $_GET['id'] : null;
$noteFile = $notesDir . $noteId . '.json';

if ($noteId && file_exists($noteFile)) {
  unlink($noteFile);

  header('Location: view_notes.php');
  exit();
} else {
  echo "Note not found!";
}