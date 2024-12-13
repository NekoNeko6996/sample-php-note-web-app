<?php
$entryId = $_GET['id'];
$dateTs = $_GET['date_ts'];

$dataFile = './workrecord/data/ts-' . $dateTs . '.json';
if (file_exists($dataFile)) {
  $jsonData = file_get_contents($dataFile);
  $timesheets = json_decode($jsonData, true);

  $entry = null;
  foreach ($timesheets as $item) {
    if ($item['id'] == $entryId) {
      $entry = $item;
      break;
    }
  }

  if (!$entry) {
    echo "Bản ghi không tồn tại!";
    exit;
  }
} else {
  echo "File dữ liệu không tồn tại!";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chỉnh Sửa Chấm Công</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light border-bottom">
    <div class="container-fluid">
      <a class="navbar-brand" href="view">Quay Lại</a>
    </div>
  </nav>

  <div class="container mt-5">
    <h1 class="text-center">Chỉnh Sửa Chấm Công</h1>
    <form action="edit?id=<?= $entryId ?>&date_ts=<?= $dateTs ?>" method="post" class="mt-4">
      <input type="hidden" name="entry_id" value="<?= $entry['id'] ?>">
      <input type="hidden" name="date_ts" value="<?= $dateTs ?>">

      <div class="mb-3">
        <label for="date" class="form-label">Ngày</label>
        <input type="date" id="date" name="date" class="form-control" value="<?= $entry['date'] ?>" required>
      </div>
      <div class="mb-3">
        <label for="start_time" class="form-label">Giờ bắt đầu</label>
        <input type="datetime-local" id="start_time" name="start_time" class="form-control"
          value="<?= date('Y-m-d\TH:i', strtotime($entry['start_time'])) ?>" required>
      </div>
      <div class="mb-3">
        <label for="end_time" class="form-label">Giờ kết thúc</label>
        <input type="datetime-local" id="end_time" name="end_time" class="form-control"
          value="<?= date('Y-m-d\TH:i', strtotime($entry['end_time'])) ?>" required>
      </div>
      <div class="mb-3">
        <label for="note" class="form-label">Ghi chú</label>
        <textarea id="note" name="note" class="form-control" rows="3"><?= $entry['note'] ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-100">Lưu</button>
    </form>
  </div>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedEntry = [
      'id' => $_POST['entry_id'],
      'date' => $_POST['date'],
      'start_time' => $_POST['start_time'],
      'end_time' => $_POST['end_time'],
      'note' => $_POST['note']
    ];

    foreach ($timesheets as $key => $item) {
      if ($item['id'] == $updatedEntry['id']) {
        $timesheets[$key] = $updatedEntry;
        break;
      }
    }

    // Lưu lại dữ liệu vào file
    file_put_contents($dataFile, json_encode(array_values($timesheets), JSON_PRETTY_PRINT));

    header('Location: view');
    exit;
  }
  ?>

</body>

</html>