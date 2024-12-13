<?php
$notesDir = './notes/notes/';
$notes = [];
$tags = [];
$selectedTags = isset($_GET['tags']) ? $_GET['tags'] : [];
$searchQuery = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// Đọc tất cả các ghi chú
if (is_dir($notesDir)) {
  $files = scandir($notesDir);

  foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
      $noteData = file_get_contents($notesDir . $file);
      $note = json_decode($noteData, true);

      if ($searchQuery && (stripos($note['title'], $searchQuery) === false) && (stripos($note['content'], $searchQuery) === false)) {
        continue;
      }

      $notes[] = $note;

      if (!empty($note['tags'])) {
        foreach ($note['tags'] as $tag) {
          if (!in_array($tag, $tags)) {
            $tags[] = $tag;
          }
        }
      }
    }
  }
}

if (!empty($selectedTags)) {
  $notes = array_filter($notes, function ($note) use ($selectedTags) {
    return !empty(array_intersect($note['tags'], $selectedTags));
  });
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_task') {
  $noteId = $_GET['id'] ?? null; // Lấy ID từ request
  $notesDir = './notes/notes/';
  $noteFile = $notesDir . $noteId . '.json';
  $taskId = $_POST['taskId'];

  if ($noteId && file_exists($noteFile)) {
    $noteData = file_get_contents($noteFile);
    $note = json_decode($noteData, true);
  } else {
    echo json_encode(['success' => false, 'message' => 'Note file not found']);
    exit();
  }

  if (isset($note['tasks'][$taskId])) {
    unset($note['tasks'][$taskId]);
    $note['tasks'] = array_values($note['tasks']); // Re-index task array
    file_put_contents($noteFile, json_encode($note, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
  }
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Notes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    .note-card {
      margin-bottom: 20px;
      height: 100%;
    }

    .note-card img {
      max-width: 100%;
      height: auto;
    }

    .card-title {
      color: coral;
      font-size: 1.5rem;
    }

    .tag {
      display: inline-block;
      background-color: #f1f1f1;
      padding: 5px 10px;
      border-radius: 15px;
      margin: 5px;
    }

    .tasks-list {
      list-style-type: none;
      padding-left: 0;
    }

    .tasks-list li {
      margin-bottom: 5px;
    }

    .task-completed {
      text-decoration: line-through;
    }

    .card-footer {
      display: flex;
      justify-content: end;
      gap: 20px;
    }

    #notes-container {
      margin-bottom: 20px;
    }

    #search-form-row {
      gap: 10px;
    }

    #task-container li {
      margin-bottom: 5px;
      list-style-type: none;
    }

    #task-container {
      margin: 0;
      padding: 0;
    }

    .task-completed {
      text-decoration: line-through;
      color: #6c757d;
    }

    .list-group-item {
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      margin-bottom: 5px;
      background-color: #f8f9fa;
      /* Màu nền nhẹ */
    }

    .delete-task-btn {
      height: 30px;
      width: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0;
    }

    .delete-task-btn i {
      font-size: 12px;
    }

    .form-check-input {
      transform: scale(1.2);
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Notes App</a>
      <a href="create" class="btn btn-primary">Add New Note</a>
    </div>
  </nav>

  <div class="container">
    <h1 class="mb-4">All Notes</h1>

    <!-- Search and Filter Form -->
    <form method="GET" action="view" class="mb-4">
      <div class="row" id="search-form-row">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search notes..."
            value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>
        <div class="col-md-4">
          <select name="tags[]" id="tags" class="form-select" multiple>
            <?php foreach ($tags as $tag): ?>
              <option value="<?php echo htmlspecialchars($tag); ?>" <?php echo in_array($tag, $selectedTags) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($tag); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary">Apply</button>
        </div>
      </div>
    </form>

    <hr>
    <!-- Display Notes -->
    <?php if (empty($notes)): ?>
      <p>No notes found!</p>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-3 g-4" id="notes-container">
        <?php foreach ($notes as $note): ?>
          <div class="col">
            <div class="card note-card shadow h-100">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($note['title']); ?></h5>
                <p><strong>Timestamp:</strong> <?php echo htmlspecialchars($note['timestamp']); ?></p>
                <hr>
                <div>
                  <strong>Tags:</strong>
                  <?php if (!empty($note['tags'])): ?>
                    <?php foreach ($note['tags'] as $tag): ?>
                      <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span>No tags</span>
                  <?php endif; ?>
                </div>

                <?php if (!empty($note['content'])): ?>
                  <div class="mt-3">
                    <strong>Content:</strong>
                    <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
                  </div>
                <?php endif; ?>

                <?php if (!empty($note['tasks'])): ?>
                  <hr>
                <?php endif; ?>
                <ul id="task-container" class="list-group">
                  <?php foreach ($note['tasks'] as $index => $task): ?>
                    <li class="list-group-item d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center">
                        <input type="checkbox" id="task-<?php echo $index; ?>" class="form-check-input me-2"
                          onchange="toggleTask(this, '<?php echo $note['id']; ?>', <?php echo $index; ?>)"
                          data-task-id="<?php echo $index; ?>" <?php echo $task['completed'] ? 'checked' : ''; ?>>
                        <span class="task-text <?php echo $task['completed'] ? 'task-completed' : ''; ?>">
                          <?php echo htmlspecialchars($task['label']); ?>
                        </span>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm delete-task-btn"
                        onclick="removeTask(this, '<?php echo $index; ?>', '<?php echo $note['id']; ?>')">
                        <i class="fas fa-times"></i>
                      </button>
                    </li>
                  <?php endforeach; ?>
                </ul>

                <?php if (!empty($note['image'])): ?>
                  <hr>
                  <div class="mt-3">
                    <strong>Image:</strong><br>
                    <a href="<?php echo htmlspecialchars($note['image']); ?>">
                      <img src="<?php echo htmlspecialchars($note['image']); ?>" alt="Note Image">
                    </a>
                  </div>
                <?php endif; ?>
              </div>

              <div class="card-footer">
                <a href="edit_note.php?id=<?php echo htmlspecialchars($note['id']); ?>"
                  class="btn btn-sm btn-warning">Edit</a>
                <button class="btn btn-sm btn-danger"
                  onclick="deleteNote('<?php echo htmlspecialchars($note['id']); ?>')">Delete</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function toggleTask(checkbox, noteId, taskIndex) {
      const taskText = checkbox.nextElementSibling;
      const taskStatus = checkbox.checked;

      taskText.classList.toggle('task-completed', taskStatus);
      // Update the task status on the server
      fetch('update_task', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          note_id: noteId,
          task_index: taskIndex,
          task_status: taskStatus
        })
      })
        .then(response => response.json())
        .then(data => {
          if (!data.success) {
            alert('Error updating task: ' + data.message);
          }
        })
        .catch(error => console.error('Error:', error));
    }

    function deleteNote(id) {
      if (!confirm('Are you sure you want to delete this note?')) {
        return;
      }
      location.href = `delete?id=${id}`;
    }

    function removeTask(button, taskId, noteId) {
      if (!confirm('Are you sure you want to delete this task?')) return;

      fetch(`?id=${noteId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'delete_task',
          taskId: taskId
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            button.closest('li').remove(); // Remove the task from the DOM
          } else {
            alert(data.message || 'Failed to delete task.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>