<?php
$notesDir = './notes/';
$uploadsDir = './resources/img/';
$noteId = isset($_GET['id']) ? $_GET['id'] : null;
$noteFile = $notesDir . $noteId . '.json';
$note = [];

if ($noteId && file_exists($noteFile)) {
  $noteData = file_get_contents($noteFile);
  $note = json_decode($noteData, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $note['title'] = $_POST['title'];
  $note['content'] = $_POST['content'];
  $note['tags'] = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];

  // Xử lý tasks
  $tasks = isset($_POST['tasks']) ? $_POST['tasks'] : [];
  $completedTasks = isset($_POST['completed_tasks']) ? $_POST['completed_tasks'] : [];

  $note['tasks'] = [];
  foreach ($tasks as $index => $taskLabel) {
    if (!empty($taskLabel)) {
      $note['tasks'][] = [
        'label' => $taskLabel,
        'completed' => in_array($index, $completedTasks),
      ];
    }
  }

  $note['timestamp'] = date('Y-m-d H:i:s');

  // Xử lý upload ảnh mới nếu có
  if (!empty($_FILES['image']['name'])) {
    $fileName = basename($_FILES['image']['name']);
    $uploadFilePath = $uploadsDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFilePath)) {
      // Xóa ảnh cũ nếu có
      if (!empty($note['image']) && file_exists($note['image'])) {
        unlink($note['image']);
      }
      $note['image'] = $uploadFilePath;
    } else {
      echo "<div class='alert alert-danger'>Error uploading image.</div>";
    }
  }

  if (isset($_POST['delete_image']) && !empty($note['image'])) {
    if (file_exists($note['image'])) {
      unlink($note['image']);
    }
    $note['image'] = '';
  }

  file_put_contents($noteFile, json_encode($note, JSON_PRETTY_PRINT));
  header('Location: view_notes.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Note</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    .task-item {
      position: relative;
      display: flex;
      align-items: center;
    }

    .task-item input {
      flex: 1;
      padding-right: 30px;
    }

    .task-item button i {
      font-size: 12px;
    }

    .delete-task-btn {
      height: 38px !important;
      margin: 0 !important;
      aspect-ratio: 1/1 !important;
    }

    @media (max-width: 576px) {
      .task-item button {
        top: 0;
        right: 5px;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
      <a class="navbar-brand" href="view_notes.php">Notes App</a>
    </div>
  </nav>

  <div class="container">
    <h1 class="mb-4">Edit Note</h1>

    <?php if (!$note): ?>
      <div class="alert alert-danger">Note not found!</div>
    <?php else: ?>
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="title" class="form-label">Title</label>
          <input type="text" name="title" id="title" class="form-control"
            value="<?php echo htmlspecialchars($note['title']); ?>" required>
        </div>

        <div class="mb-3">
          <label for="content" class="form-label">Content</label>
          <textarea name="content" id="content" rows="5"
            class="form-control"><?php echo htmlspecialchars($note['content']); ?></textarea>
        </div>

        <div class="mb-3">
          <label for="tags" class="form-label">Tags (comma-separated)</label>
          <input type="text" name="tags" id="tags" class="form-control"
            value="<?php echo htmlspecialchars(implode(',', $note['tags'])); ?>">
        </div>

        <div class="mb-3">
          <label for="tasks" class="form-label">Tasks</label>
          <div id="taskList">
            <?php if (!empty($note['tasks'])): ?>
              <?php foreach ($note['tasks'] as $index => $task): ?>
                <div class="task-item mb-2 d-flex align-items-center">
                  <div class="input-group">
                    <input type="text" name="tasks[]" class="form-control"
                      value="<?php echo htmlspecialchars($task['label']); ?>">
                    <div class="input-group-text">
                      <input type="checkbox" name="completed_tasks[]" value="<?php echo $index; ?>" <?php echo $task['completed'] ? 'checked' : ''; ?> class="form-check-input">
                      <label class="form-check-label ms-1">Completed</label>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm ms-2 delete-task-btn" onclick="removeTask(this)">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="task-item mb-2 d-flex align-items-center">
                <div class="input-group">
                  <input type="text" name="tasks[]" class="form-control" placeholder="Enter task">
                  <div class="input-group-text">
                    <input type="checkbox" name="completed_tasks[]" class="form-check-input">
                    <label class="form-check-label ms-1">Completed</label>
                  </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm ms-2 delete-task-btn" onclick="removeTask(this)">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            <?php endif; ?>
          </div>
          <button type="button" class="btn btn-secondary mt-2" onclick="addTask()">Add Task</button>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Image</label>
          <input type="file" name="image" id="image" class="form-control" onchange="showImagePreview(event)">

          <?php if (!empty($note['image'])): ?>
            <div class="mt-3 position-relative">
              <button type="button" class="btn btn-danger position-absolute top-0 end-0" style="z-index: 10;"
                onclick="removeImage()">
                <i class="fas fa-trash"></i>
              </button>
              <img id="imagePreview" src="<?php echo htmlspecialchars($note['image']); ?>" alt="Note Image"
                style="max-width: 100%; height: auto;">
            </div>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Note</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    function addTask() {
      const taskList = document.getElementById('taskList');
      const taskItem = document.createElement('div');
      taskItem.className = 'task-item mb-2 d-flex align-items-center';
      taskItem.innerHTML = `
        <div class="input-group">
          <input type="text" name="tasks[]" class="form-control" placeholder="Enter task">
          <div class="input-group-text">
            <input type="checkbox" name="completed_tasks[]" class="form-check-input">
            <label class="form-check-label ms-1">Completed</label>
          </div>
          <button type="button" class="btn btn-danger btn-sm ms-2 delete-task-btn" onclick="removeTask(this)">
            <i class="fas fa-times"></i>
          </button>
        </div>`;
      taskList.appendChild(taskItem);
    }

    function removeTask(button) {
      const taskItem = button.closest('.task-item');
      taskItem.remove();
    }

    function showImagePreview(event) {
      const imagePreview = document.getElementById('imagePreview');
      if (imagePreview) {
        imagePreview.src = URL.createObjectURL(event.target.files[0]);
        imagePreview.style.display = 'block';
      }
    }

    function removeImage() {
      const deleteImageInput = document.createElement('input');
      deleteImageInput.type = 'hidden';
      deleteImageInput.name = 'delete_image';
      deleteImageInput.value = '1';
      document.querySelector('form').appendChild(deleteImageInput);

      const imagePreview = document.getElementById('imagePreview');
      imagePreview.src = '';
      imagePreview.style.display = 'none';
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>