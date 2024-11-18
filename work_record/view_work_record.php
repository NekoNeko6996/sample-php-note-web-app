<?php
$filteredMonth = $_GET['month'] ?? date('Y-m');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chấm Công</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./css/styles.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light border-bottom">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Chấm Công</a>
      <div class="d-flex">
        <a href="new_work_record.php" class="btn btn-primary">Thêm Chấm Công</a>
      </div>
    </div>
  </nav>
  <div class="container mt-5">
    <h3>Danh sách chấm công</h3>
    <form method="get" class="mb-3">
      <label for="filter_month" class="form-label">Lọc theo tháng</label>
      <input type="month" id="filter_month" name="month" class="form-control"
        value="<?= $_GET['month'] ?? date('Y-m') ?>" onchange="this.form.submit()">
    </form>

    <ul class="list-group" id="timesheet-list">
      <?php
      $dataFile = 'data/ts-' . $filteredMonth . '.json';
      $totalHours = 0;

      if (file_exists($dataFile)) {
        $jsonData = file_get_contents($dataFile);
        $timesheets = json_decode($jsonData, true);
        usort($timesheets, function ($a, $b) {
          return strtotime($b['date']) - strtotime($a['date']);
        });

        $startFilterDate = date('Y-m-05', strtotime($filteredMonth));
        $endFilterDate = date('Y-m-05', strtotime($filteredMonth . ' +1 month'));

        foreach ($timesheets as $entry) {
          if ($entry['date'] < $startFilterDate || $entry['date'] >= $endFilterDate) {
            continue;
          }
          $start = new DateTime($entry['start_time']);
          $end = new DateTime($entry['end_time']);
          $interval = $start->diff($end);
          $hours = $interval->h + ($interval->i / 60);
          $totalHours += $hours;
        }

        echo "<li class='list-group-item d-flex flex-row justify-content-between'><strong>Tổng thời gian:</strong> " . number_format($totalHours, 2) . " giờ</li>";

        foreach ($timesheets as $entry) {
          if ($entry['date'] < $startFilterDate || $entry['date'] >= $endFilterDate) {
            continue;
          }

          $start = new DateTime($entry['start_time']);
          $end = new DateTime($entry['end_time']);
          $interval = $start->diff($end);
          $hours = $interval->h + ($interval->i / 60);

          echo "<li class='list-group-item position-relative'>
                  <div class='d-flex flex-row flex-wrap show-container'>
                    <div class='items div-date'><strong>Ngày:</strong> <span class='me-2'>{$entry['date']}</span></div> 
                    <div class='items'><strong>Bắt đầu:</strong> <span class='me-2'>{$entry['start_time']}</span></div> 
                    <div class='items'><strong>Kết thúc:</strong> <span class='me-2'>{$entry['end_time']}</span></div> "
            . (empty($entry['note']) ? '' : "<div class='items'><strong>Ghi chú:</strong> <span class='me-2'>{$entry['note']}</span></div>")
            . "</div>
                  <div class='items'><strong>Tổng: </strong><strong> " . number_format($hours, 2) . " giờ</strong></div>
                  <form method='POST' action='delete_work_record.php' class='position-absolute top-0 end-0'>
                    <input type='hidden' name='entry_id' value='{$entry['id']}'>
                    <input type='hidden' name='date-ts' value='{$filteredMonth}'>
                    <button type='submit' class='btn btn-danger btn-sm' onclick='confirmDelete(event)'>Xóa</button>
                  </form>
                </li>";
        }
      }
      ?>
    </ul>
  </div>

  <script>
    function confirmDelete(event) {
      const confirmation = confirm('Bạn có chắc chắn muốn xóa bản ghi này?');
      if (!confirmation) {
        event.preventDefault();
      }
    }
  </script>
</body>

</html>