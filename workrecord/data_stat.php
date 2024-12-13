<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thống kê dữ liệu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<?php
$data = json_decode(file_get_contents(__DIR__ . '/data/ts-' . date("Y-m") . '.json'), true);

$dates = [];
$durations = [];
$totalHours = 0;
$maxHours = 0;
$maxDate = "";

foreach ($data as $entry) {
  $start = new DateTime($entry['start_time']);
  $end = new DateTime($entry['end_time']);
  $interval = $start->diff($end);
  $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);

  $dates[] = $entry['date'];
  $durations[] = $hours;
  $totalHours += $hours;

  if ($hours > $maxHours) {
    $maxHours = $hours;
    $maxDate = $entry['date'];
  }
}
?>

<body>
  <nav class="navbar navbar-expand-lg navbar-light border-bottom">
    <div class="container-fluid">
      <div class="d-flex">
        <a class="navbar-brand" href="view">Chấm Công</a>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <h1 class="text-center mb-4">Thống kê dữ liệu</h1>
    <div class="row">
      <div class="col-md-6">
        <canvas id="timeChart" width="400" height="400"></canvas>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-body">
            <h5 class="card-title">Tổng số giờ làm việc</h5>
            <p class="card-text" id="totalHours"></p>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Ngày làm nhiều nhất</h5>
            <p class="card-text" id="maxHours"></p>
          </div>
        </div>
        <br>
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Lương Dự Kiến Nhận Được: <?php echo number_format($totalHours * 15000, 0); ?> đồng
            </h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const totalHours = <?php echo $totalHours; ?>;
    const maxHours = <?php echo $maxHours; ?>;
    const maxDate = <?php echo json_encode($maxDate); ?>;

    document.getElementById('totalHours').textContent = `${totalHours.toFixed(2)} giờ`;
    document.getElementById('maxHours').textContent = `${maxDate} (${maxHours.toFixed(2)} giờ)`;

    const ctx = document.getElementById('timeChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_map(function ($day) {
                return explode('-', $day)[2];
            }, $dates)); ?>,
            datasets: [{
                label: '[Giờ]',
                data: <?php echo json_encode($durations); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true
                }
            },
            responsive: true
        },
        plugins: [{
            // Vẽ đường ngang
            id: 'averageLine',
            afterDatasetsDraw: (chart) => {
                const ctx = chart.ctx;
                const yScale = chart.scales.y;
                const avg = <?php echo array_sum($durations) / count($durations); ?>; // Tính trung bình
                const avgY = yScale.getPixelForValue(avg); // Tọa độ y của giá trị trung bình

                // Thiết lập kiểu vẽ
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(chart.chartArea.left, avgY); // Bắt đầu từ mép trái
                ctx.lineTo(chart.chartArea.right, avgY); // Đến mép phải
                ctx.lineWidth = 2;
                ctx.strokeStyle = 'rgba(255, 99, 132, 1)'; // Màu của đường
                ctx.stroke();

                // Thêm nhãn "Trung bình"
                ctx.fillStyle = 'rgba(255, 99, 132, 1)';
                ctx.font = '12px Arial';
                ctx.fillText('Trung bình: ' + avg.toFixed(2) + " giờ", chart.chartArea.left + 10, avgY - 10);
                ctx.restore();
            }
        }]
    });

  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>