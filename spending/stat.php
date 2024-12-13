<?php
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$dataFile = './spending/data/sp-' . $current_month . '.json';

if (!file_exists($dataFile)) {
  $spendings = [];
} else {
  $spendings = json_decode(file_get_contents($dataFile), true);
}


$totalAmount = array_sum(array_column($spendings, 'amount'));

$categories = [];
foreach ($spendings as $spending) {
  $categories[$spending['category']] = ($categories[$spending['category']] ?? 0) + $spending['amount'];
}

$paymentMethods = [];
foreach ($spendings as $spending) {
  $paymentMethods[$spending['payment_method']] = ($paymentMethods[$spending['payment_method']] ?? 0) + $spending['amount'];
}

$locations = [];
foreach ($spendings as $spending) {
  $locations[$spending['location']] = ($locations[$spending['location']] ?? 0) + $spending['amount'];
}

$dates = [];
foreach ($spendings as $spending) {
  $dates[$spending['date']] = ($dates[$spending['date']] ?? 0) + $spending['amount'];
}

$tagsCount = [];
foreach ($spendings as $spending) {
  foreach ($spending['tags'] as $tag) {
    $tagsCount[$tag] = ($tagsCount[$tag] ?? 0) + 1;
  }
}

$transactionsWithNotes = array_filter($spendings, fn($spending) => !empty ($spending['notes']));
$totalTransactions = count($spendings);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Spending Statistics</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .chart-container {
      max-height: 400px;
      margin: 0 auto;
      overflow: hidden;
    }

    canvas {
      height: 300px !important;
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
    }

    .container-title {
      margin-bottom: 0 !important;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-white border-bottom">
    <div class="container-fluid">
      <a class="navbar-brand" href="view">Spending Tracker</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="create">Add Spending</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap container-title">
      <h3 class="mb-4">Spending Statistics</h3>
      <form class="mb-4" method="GET">
        <div class="row align-items-center">
          <div class="col-auto">
            <input type="month" id="month" name="month" class="form-control" value="<?= $current_month; ?>">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary">Confirm</button>
          </div>
        </div>
      </form>
    </div>

    <hr>
    <?php if (count($spendings) > 0): ?>
      <div class="row">
        <div class="col-md-4">
          <div class="card text-center mb-4">
            <div class="card-body">
              <h5 class="card-title">Total Amount</h5>
              <p class="card-text"><?= number_format($totalAmount) ?> VND</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center mb-4">
            <div class="card-body">
              <h5 class="card-title">Total Transactions</h5>
              <p class="card-text"><?= $totalTransactions ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center mb-4">
            <div class="card-body">
              <h5 class="card-title">Transactions with Notes</h5>
              <p class="card-text"><?= count($transactionsWithNotes) ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 chart-container">
          <div class="card mb-4">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <h5 class="card-title">Spending by Category</h5>
              <canvas id="categoryChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6 chart-container">
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title">Spending by Payment Method</h5>
              <canvas id="paymentMethodChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 chart-container">
          <div class="card mb-4">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
              <h5 class="card-title">Spending by Location</h5>
              <canvas id="locationChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6 chart-container">
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title">Spending by Date</h5>
              <canvas id="dateChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info" role="alert">
      <h4 class="alert-heading">No spending data available.</h4>
      <p>Please add spending data to view statistics.</p>
      <a href="create" class="btn btn-primary">Add Spending</a>
    </div>
  <?php endif; ?>

  <script>
    const categoryData = <?= json_encode($categories) ?>;
    const paymentMethodData = <?= json_encode($paymentMethods) ?>;
    const locationData = <?= json_encode($locations) ?>;
    const dateData = <?= json_encode($dates) ?>;

    new Chart(document.getElementById('categoryChart'), {
      type: 'pie',
      data: {
        labels: Object.keys(categoryData),
        datasets: [{
          data: Object.values(categoryData),
          backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
        }]
      }
    });

    new Chart(document.getElementById('paymentMethodChart'), {
      type: 'bar',
      data: {
        labels: Object.keys(paymentMethodData),
        datasets: [{
          label: 'Amount',
          data: Object.values(paymentMethodData),
          backgroundColor: '#4BC0C0',
        }]
      }
    });

    new Chart(document.getElementById('locationChart'), {
      type: 'doughnut',
      data: {
        labels: Object.keys(locationData),
        datasets: [{
          data: Object.values(locationData),
          backgroundColor: ['#FF9F40', '#FF6384', '#36A2EB'],
        }]
      }
    });

    new Chart(document.getElementById('dateChart'), {
      type: 'line',
      data: {
        labels: Object.keys(dateData),
        datasets: [{
          label: 'Amount',
          data: Object.values(dateData),
          borderColor: '#9966FF',
          fill: false,
        }]
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>