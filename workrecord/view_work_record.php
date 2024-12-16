<?php
$filteredMonth = $_GET['month'] ?? date('Y-m');
$base_salary = 15000;

$search_suggestions = file_get_contents('search_suggest.json');
$search_suggestions = json_decode($search_suggestions, true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chấm Công</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/view.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script type="module" src="js/view.js" defer></script>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-white border-bottom p-2">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-outline-primary d-lg-none" id="toggle-aside">☰ Menu</button>
      <a class="navbar-brand" href="#">Chấm Công</a>
    </div>
    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- search bar  -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a href="stat" class="nav-link">Thống Kê</a>
        </li>
        <li class="nav-item">
          <a href="create" class="nav-link">Thêm Chấm Công</a>
        </li>
        <li class="nav-item">
          <input class="form-control me-2" type="search" placeholder="Nhập [/] để tìm kiếm" aria-label="Search"
            id="search-bar">
        </li>
      </ul>
    </div>
    </div>
  </nav>

  <aside id="aside-menu">
    <div class="p-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Menu</h5>
        <button class="btn btn-sm btn-danger" id="close-aside">Close</button>
      </div>
      <hr>
      <!-- search bar  -->
      <form class="d-flex ms-auto" role="search">
        <input class="form-control me-2" type="search" placeholder="Search ..." aria-label="Search">
      </form>
      <hr>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a href="stat" class="nav-link">Thống Kê</a>
        </li>
        <li class="nav-item">
          <a href="create" class="nav-link">Thêm Chấm Công</a>
        </li>
      </ul>
      <hr>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a href="/notes/view" class="nav-link">Note App</a>
        </li>
        <li class="nav-item">
          <a href="/spending/view" class="nav-link">Spending App</a>
        </li>
      </ul>
    </div>
  </aside>

  <div class="container mt-5">
    <h3>Danh sách chấm công</h3>
    <form method="get" class="mb-3">
      <label for="filter_month" class="form-label">Lọc theo tháng</label>
      <input type="month" id="filter_month" name="month" class="form-control"
        value="<?= $_GET['month'] ?? date('Y-m') ?>" onchange="this.form.submit()">
    </form>

    <?php
    $dataFile = './workrecord/data/ts-' . $filteredMonth . '.json';
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
          echo "<div class='card'><div class='card-body'>Invalid date format</div></div>";
          continue;
        }

        $durationInSeconds = $end->getTimestamp() - $start->getTimestamp();
        $hours = $durationInSeconds / 3600;
        $totalHours += $hours;
      }
    }
    ?>

    <div class='input-group mb-2 align-items-center'>
      <span class='input-group-text'><strong>Tổng thời gian</strong></span>
      <div class='form-control text-end'><?= number_format($totalHours, 2) ?> giờ</div>
    </div>
    <div class='input-group mb-2 align-items-center'>
      <span class='input-group-text'><strong>Lương cơ bản</strong></span>
      <div class='form-control text-end'><?= number_format($base_salary, 0) ?> đồng/giờ</div>
    </div>
    <div class='input-group align-items-center mb-3'>
      <span class='input-group-text'><strong>Tổng lương dự kiến</strong></span>
      <div class='form-control text-end'><?= number_format($totalHours * $base_salary, 0) ?> đồng</div>
    </div>

    <?php
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $itemsPerPage = 8;
    $totalItems = count($timesheets);
    $totalPages = ceil($totalItems / $itemsPerPage);

    $start = ($page - 1) * $itemsPerPage;
    $end = min($start + $itemsPerPage, $totalItems);
    ?>

    <div class="d-flex justify-content-between mt-4">
      <div>
        <strong>Tổng số bản ghi: <?= $totalItems ?></strong>
      </div>
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>&month=<?= $filteredMonth ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&month=<?= $filteredMonth ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&month=<?= $filteredMonth ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="grid-container">
      <?php
      $totalHours = 0;
      for ($i = $start; $i < $end; $i++) {
        $entry = $timesheets[$i];
        $d_start = DateTime::createFromFormat('Y-m-d\TH:i', $entry['start_time']);
        $d_end = DateTime::createFromFormat('Y-m-d\TH:i', $entry['end_time']);

        if ($d_start && $d_end) {
          $durationInSeconds = $d_end->getTimestamp() - $d_start->getTimestamp();
          $hours = $durationInSeconds / 3600;
          $totalHours += $hours;
        } else {
          $invalidDate = true;
        }
        ?>
        <?php if (!empty($invalidDate)) { ?>
          <div class="card">
            <div class="card-body">Invalid date format</div>
          </div>
        <?php } else { ?>
          <div class="card">
            <div class="card-header">
              Ngày: <?= $entry['date'] ?>
              <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  Tùy chọn
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#"
                      onclick='confirmEdit(`<?= $entry["id"] ?>`, `<?= $filteredMonth ?>`)'>Sửa</a></li>
                  <li><a class="dropdown-item text-danger" href="#"
                      onclick='confirmDelete(`<?= $entry["id"] ?>`, `<?= $filteredMonth ?>`)'>Xóa</a></li>
                </ul>
              </div>
            </div>
            <div class="card-body">
              <p><strong>Bắt đầu:</strong> <?= $entry['start_time'] ?></p>
              <p><strong>Kết thúc:</strong> <?= $entry['end_time'] ?></p>
              <?php if (!empty($entry['note'])) { ?>
                <p><strong>Ghi chú:</strong> <?= $entry['note'] ?></p>
              <?php } ?>
              <p><strong>Tổng:</strong> <?= number_format($hours, 2) ?> giờ</p>
            </div>
          </div>
        <?php } ?>
        <?php
      }
      ?>

    </div>
  </div>
  <br>
  <br>
  <br>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function confirmDelete(id, date_ts) {
      Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa bản ghi này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('delete', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              entry_id: id,
              date_ts: date_ts
            })
          })
            .then(response => {
              if (response.ok) {
                window.location.reload();
              } else {
                Swal.fire('Lỗi', 'Không thể xóa bản ghi.', 'error');
              }
            })
            .catch(error => {
              Swal.fire('Lỗi', 'Đã xảy ra lỗi khi xóa bản ghi.', 'error');
              console.error('Lỗi khi xóa bản ghi:', error);
            });
        }
      });
    }

    function confirmEdit(id, date_ts) {
      window.location.href = `edit?id=${id}&date_ts=${date_ts}`;
    }

    document.getElementById('toggle-aside').addEventListener('click', function () {
      document.getElementById('aside-menu').classList.toggle('show');
    });

    document.getElementById('close-aside').addEventListener('click', function () {
      document.getElementById('aside-menu').classList.remove('show');
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === '/') {
        event.preventDefault();
        const searchBar = document.getElementById('search-bar');
        if (searchBar) {
          searchBar.focus();
        }
      }
    });
  </script>
</body>

</html>