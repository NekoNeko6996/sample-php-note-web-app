<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $tags = $_POST['tags'] ?? [];
  $content = $_POST['content'] ?? '';
  $tasks = $_POST['task_labels'] ?? [];

  $readDir = 'resources/img/';
  $uploadDir = __DIR__ . '/resources/img/';
  $uploadedFilePath = '';

  if (!empty($_FILES['image']['name'])) {
    $fileName = basename($_FILES['image']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = uniqid('image_', true) . '.' . $fileExtension;

    $uploadFilePath = $uploadDir . $uniqueFileName;
    $readDir .= $uniqueFileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFilePath)) {
      $uploadedFilePath = $uploadFilePath;
    } else {
      echo "<script>alert('Error during upload file')</script>";
    }
  }


  $id_note = uniqid();

  $note = [
    'id' => $id_note,
    'title' => $title,
    'tags' => $tags,
    'content' => $content,
    'tasks' => [],
    'image' => $readDir,
    'timestamp' => date('Y-m-d H:i:s')
  ];

  // Xử lý tasks
  foreach ($tasks as $index => $task) {
    $taskLabel = trim($task);
    if (!empty($taskLabel)) {
      $note['tasks'][] = [
        'label' => $taskLabel,
        'completed' => false
      ];
    }
  }

  // Lưu JSON vào file
  $noteData = json_encode($note, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  file_put_contents('./notes/notes/' . $id_note . '.json', $noteData);

  echo "<div class='alert alert-success'>Note saved successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Note</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .task-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .tag-item {
      display: inline-block;
      margin-right: 5px;
      background-color: #f1f1f1;
      padding: 5px 10px;
      border-radius: 15px;
    }

    .tag-item span {
      cursor: pointer;
      margin-left: 8px;
      color: red;
    }

    .container {
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Notes App</a>
      <a class="nav-link active" href="view">Home</a>
    </div>
  </nav>

  <div class="container mt-5">
    <h1 class="mb-4">Create Note</h1>
    <form action="create" method="POST" enctype="multipart/form-data" class="card p-4 shadow">
      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" id="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="tags" class="form-label">Tags</label>
        <div class="d-flex">
          <select name="tags[]" id="tags" class="form-select me-2" multiple>
            <option value="tag1">Tag 1</option>
            <option value="tag2">Tag 2</option>
            <option value="tag3">Tag 3</option>
          </select>
          <button type="button" class="btn btn-outline-primary" onclick="addTag()">New Tag</button>
        </div>
        <div id="tagList" class="mt-2">
          <!-- Tags will be added here -->
        </div>
      </div>

      <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" name="image" id="image" class="form-control" onchange="showImagePreview(event)">
        <img id="imagePreview" src="#" alt="Preview"
          style="display: none; max-width: 100%; height: auto; margin-top: 10px;">
      </div>

      <div id="textContent" class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea name="content" id="content" rows="4" class="form-control"></textarea>
      </div>

      <div id="taskContent" class="mb-3">
        <label class="form-label">Tasks</label>
        <div id="taskList" class="mb-2">
          <!-- Dynamic tasks will be added here -->
        </div>
        <button type="button" class="btn btn-outline-primary" onclick="addTask()">Add New Task</button>
      </div>

      <button type="submit" class="btn btn-primary mt-3">Save Note</button>
    </form>
  </div>

  <script>
    function addTask() {
      const taskList = document.getElementById('taskList');
      const taskId = 'task' + Date.now();
      const taskItem = document.createElement('div');
      taskItem.className = 'task-item';
      taskItem.innerHTML = `
        <input type="checkbox" name="tasks[]" id="${taskId}" class="form-check-input">
        <input type="text" name="task_labels[]" placeholder="Enter task label" class="form-control d-inline-block" style="width: auto;">
        <button type="button" class="btn btn-sm btn-danger" onclick="removeTask(this)">X</button>
      `;
      taskList.appendChild(taskItem);
    }

    function removeTask(button) {
      button.parentElement.remove();
    }

    function showImagePreview(event) {
      const imagePreview = document.getElementById('imagePreview');
      imagePreview.src = URL.createObjectURL(event.target.files[0]);
      imagePreview.style.display = 'block';
    }

    function addTag() {
      const tagInput = prompt('Enter tag name:');
      if (tagInput) {
        const tagList = document.getElementById('tagList');

        const tagItem = document.createElement('label');
        tagItem.className = 'tag-item';
        tagItem.innerHTML = `${tagInput} <span onclick="removeTag(this, '${tagInput}')">x</span>`;
        tagList.appendChild(tagItem);

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tags[]';
        hiddenInput.value = tagInput;
        hiddenInput.id = `hidden-${tagInput}`;
        tagList.appendChild(hiddenInput);
      }
    }

    function removeTag(span, tagValue) {
      span.parentElement.remove();
      const hiddenInput = document.getElementById(`hidden-${tagValue}`);
      if (hiddenInput) {
        hiddenInput.remove();
      }
    }

  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>