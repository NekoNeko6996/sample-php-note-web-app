<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Lấy dữ liệu từ form
  $category = $_POST['category'] ?? '';
  $amount = $_POST['amount'] ?? 0;
  $currency = $_POST['currency'] ?? 'VND';
  $date = $_POST['date'] ?? date('Y-m-d');
  $description = $_POST['description'] ?? '';
  $payment_method = $_POST['payment_method'] ?? '';
  $location = $_POST['location'] ?? '';
  $tags = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
  $notes = $_POST['notes'] ?? '';

  $attachments = [];
  if (isset($_FILES['attachments'])) {
    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
      if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
        $attachments[] = $_FILES['attachments']['name'][$key];
        move_uploaded_file($tmp_name, './spending/attachments/' . $_FILES['attachments']['name'][$key]);
      }
    }
  }

  $uid = uniqid();

  // Dữ liệu spending
  $new_spending = [
    "id" => $uid,
    "category" => $category,
    "amount" => (float) $amount,
    "currency" => $currency,
    "date" => $date,
    "description" => $description,
    "payment_method" => $payment_method,
    "location" => $location,
    "tags" => $tags,
    "notes" => $notes,
    "attachments" => $attachments
  ];


  $dataFile = './spending/data/sp-' . date('Y-m') . '.json';

  if (!file_exists('data')) {
    mkdir('data', 0777, true);
  }

  $final = [];
  if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $final = json_decode($jsonData, true);
  }

  $final[] = $new_spending;

  file_put_contents($dataFile, json_encode($final, JSON_PRETTY_PRINT));
  header('Location: view');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Spending</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a class="nav-link" href="#">Statistics</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="container mt-5">
    <h1 class="mb-4">Add New Spending</h1>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="category" class="form-label">Category</label>
        <input type="text" id="category" name="category" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" id="amount" name="amount" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="currency" class="form-label">Currency</label>
        <input type="text" id="currency" name="currency" class="form-control" value="VND" required>
      </div>
      <div class="mb-3">
        <label for="date" class="form-label">Date</label>
        <input type="date" id="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label for="payment_method" class="form-label">Payment Method</label>
        <input type="text" id="payment_method" name="payment_method" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" id="location" name="location" class="form-control">
      </div>
      <div class="mb-3">
        <label for="tags" class="form-label">Tags (comma separated)</label>
        <input type="text" id="tags" name="tags" class="form-control">
      </div>
      <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label for="attachments" class="form-label">Attachments</label>
        <input type="file" id="attachments" name="attachments[]" class="form-control" multiple disabled>
      </div>
      <button type="submit" class="btn btn-primary">Add Spending</button>
    </form>
  </div>
  <br>
  <br>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>