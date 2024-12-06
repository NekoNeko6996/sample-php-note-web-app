<?php
$filteredMonth = $_GET['month'] ?? date('Y-m');
$base_salary = 15000;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chấm Công</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* CSS giữ nguyên như cũ */
    .list-group-item {
      position: relative;
    }

    .list-group-item .btn-danger {
      position: absolute;
      top: 5px;
      right: 10px;
    }

    .show-container {
      display: flex;
      flex-direction: column;
    }

    .items {
      display: flex;
      flex-grow: 1;
      flex-direction: row;
      justify-content: space-between;
    }

    .delete-work-record {
      display: none;
    }

    .list-group-item:hover {
      background-color: #f1f1f1;
    }

    .list-group-item:hover .delete-work-record {
      display: block;
    }

    @media (max-width: 576px) {
      .show-container {
        flex-direction: column !important;
      }

      .items {
        display: flex;
        flex-grow: 1;
        flex-direction: column;
        justify-content: flex-start;
      }

      .div-date {
        border-bottom: 1px solid rgb(175, 175, 175);
      }

      .info-item {
        margin-bottom: 5px;
      }

      .list-group-item.flex-wrap .info-item {
        flex: 1 1 30%;
      }

      @media (max-width: 576px) {
        .list-group-item.flex-wrap .info-item {
          flex: 1 1 100%;
          margin-bottom: 10px;
        }
      }

    }
  </style>
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

        $startFilterDate = date('Y-m-01', strtotime($filteredMonth));
        $endFilterDate = date('Y-m-01', strtotime($filteredMonth . ' +1 month'));

        foreach ($timesheets as $entry) {
          if ($entry['date'] < $startFilterDate || $entry['date'] >= $endFilterDate) {
            continue;
          }

          $start = DateTime::createFromFormat('Y-m-d\TH:i', $entry['start_time']);
          $end = DateTime::createFromFormat('Y-m-d\TH:i', $entry['end_time']);

          if (!$start || !$end) {
            echo "Invalid date format: start={$entry['start_time']}, end={$entry['end_time']}<br>";
            continue;
          }

          $durationInSeconds = $end->getTimestamp() - $start->getTimestamp();
          $hours = $durationInSeconds / 3600;
          $totalHours += $hours;
        }



        echo "<li class='list-group-item d-flex flex-row justify-content-between align-items-center flex-wrap'>
          <div class='info-item'><strong>Tổng thời gian:</strong> " . number_format($totalHours, 2) . " giờ</div>
          <div class='info-item'><strong>Lương cơ bản:</strong> " . number_format($base_salary, 0) . " đồng/giờ</div>
          <div class='info-item'><strong>Tổng lương dự kiến:</strong> " . number_format($totalHours * $base_salary, 0) . " đồng</div>
        </li>";



        foreach ($timesheets as $entry) {
          if ($entry['date'] < $startFilterDate || $entry['date'] >= $endFilterDate) {
            continue;
          }

          $start = DateTime::createFromFormat('Y-m-d\TH:i', $entry['start_time']);
          $end = DateTime::createFromFormat('Y-m-d\TH:i', $entry['end_time']);

          if (!$start || !$end)
            continue;

          $durationInSeconds = $end->getTimestamp() - $start->getTimestamp();
          $hours = $durationInSeconds / 3600;

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
                    <button type='submit' class='btn btn-danger btn-sm delete-work-record' onclick='confirmDelete(event)'>Xóa</button>
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