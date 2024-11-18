<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thêm Chấm Công</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="view_work_record.php">Quay Lại</a>
    </div>
  </nav>

  <div class="container mt-5">
    <h1 class="text-center">Thêm Chấm Công</h1>
    <form action="work_record.php" method="post" class="mt-4">
      <div class="mb-3">
        <label for="date" class="form-label">Ngày</label>
        <input type="date" id="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
      </div>
      <div class="mb-3">
        <label for="start_time" class="form-label">Giờ bắt đầu</label>
        <input type="time" id="start_time" name="start_time" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="end_time" class="form-label">Giờ kết thúc</label>
        <input type="time" id="end_time" name="end_time" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="note" class="form-label">Ghi chú</label>
        <textarea id="note" name="note" class="form-control" rows="3"></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-100">Lưu</button>
    </form>
  </div>
</body>

</html>