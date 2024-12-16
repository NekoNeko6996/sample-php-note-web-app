<?php
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$file_path = './spending/data/sp-' . $current_month . '.json';
$spendings = file_exists($file_path) ? json_decode(file_get_contents($file_path), true) : [];

$filtered_spendings = array_filter($spendings, function ($spending) use ($current_month) {
  return strpos($spending['date'], $current_month) === 0;
});

$items_per_page = 6;
$total_items = count($filtered_spendings);
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, (int) $_GET['page'])) : 1;
$start_index = ($current_page - 1) * $items_per_page;
$paginated_spendings = array_slice($filtered_spendings, $start_index, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Spending Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/view.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-white border-bottom">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Spending Tracker</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="create">Add Spending</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="stat">Statistics</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div id="alert-container"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-4">Spending</h3>
      <strong>Showing
        <?= $items_per_page * ($current_page - 1) + (count($paginated_spendings)) ?>/<?= count($spendings) ?></strong>
    </div>
    <!-- Bộ lọc tháng -->
    <form class="mb-4" method="GET">
      <div class="row align-items-center">
        <div class="col-auto">
          <label for="month" class="col-form-label">Select Month:</label>
        </div>
        <div class="col-auto">
          <input type="month" id="month" name="month" class="form-control"
            value="<?= htmlspecialchars($current_month) ?>">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
    <hr>
    <?php if (count($paginated_spendings) > 0): ?>
      <div class="row">
        <?php foreach ($paginated_spendings as $spending): ?>
          <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100">
              <div class="action-buttons">
                <button class="btn btn-warning"
                  onclick="window.location.href='edit?id=<?= $spending['id'] ?>&file=<?= $current_month ?>'">
                  Edit
                </button>
                <button class="btn btn-danger"
                  onclick="delete_spending('<?= $spending['id'] ?>', '<?= $current_month ?>')">Delete</button>
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-left gap-1 mb-2">
                  <?php if (!empty($spending['tags']) && !empty($spending['tags'][0])): ?>
                    <?php foreach ($spending['tags'] as $tag): ?>
                      <span class="badge bg-secondary"><span class="tag-content"><?= htmlspecialchars($tag) ?></span></span>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="badge bg-secondary"><span class="tag-content">Unknown Tag</span></span>
                  <?php endif; ?>
                </div>
                <hr>
                <h6 class="card-subtitle mb-2 text-muted">Date: <?= htmlspecialchars($spending['date']) ?></h6>
                <p class="card-text">
                  <strong>Description:</strong> <?= htmlspecialchars($spending['description']) ?><br>
                  <strong>Amount:</strong>
                  <?= number_format($spending['amount']) . ' ' . htmlspecialchars($spending['currency']) ?><br>
                  <strong>Payment Method:</strong> <?= htmlspecialchars($spending['payment_method']) ?><br>
                  <strong>Location:</strong> <?= htmlspecialchars($spending['location']) ?><br>
                  <strong>Notes:</strong> <?= htmlspecialchars($spending['notes']) ?><br>
                  <strong>Category:</strong> <?= htmlspecialchars($spending['category']) ?><br>
                  <strong>Attachment: </strong>

                  <?php if (!empty($spending['attachments'])): ?>
                    <?php foreach ($spending['attachments'] as $attachment): ?>
                      <a href="<?= htmlspecialchars($attachment) ?>" target="_blank"><?= htmlspecialchars($attachment) ?></a>
                    <?php endforeach; ?>
                  <?php else: ?>
                    No attachment available.
                  <?php endif; ?>
                </p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">No spending data available.</h4>
        <p>Please add spending data to view statistics.</p>
        <a href="create" class="btn btn-primary">Add Spending</a>
      </div>
    <?php endif; ?>


    <?php if ($total_pages > 1): ?>
      <nav>
        <ul class="pagination justify-content-center">
          <li class="page-item <?= ($current_page == 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page - 1 ?>">Prev</a>
          </li>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function delete_spending(id, file) {
      if (!confirm('Are you sure you want to delete this spending?')) {
        return;
      }

      fetch(`delete`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: id,
          file: file
        })
      })
        .then((response) => response.json())
        .then(data => {
          if (data.status) {
            window.location.reload();
          }
          else {
            document.getElementById("alert-container").innerHTML = `<div class='alert alert-danger'>Error deleting spending!</div>`
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById("alert-container").innerHTML = `<div class='alert alert-danger'>${error}</div>`
        });
    }
  </script>
</body>

</html>