<?php

// get route request
$requested_app = $_GET['app'] ?? 'notes';
$requestedRoute = $_GET['route'] ?? 'view';


// route list
$NoCheckedAllowedRoutes = ['notes', 'workrecord', 'spending'];

$routes = [
  'notes' => [
    'view' => 'notes/view_notes.php',
    'create' => 'notes/create_new_note.php',
    'edit' => 'notes/edit_note.php',
    'delete' => 'notes/delete_note.php',
    'update_task' => 'notes/update_task_status.php'
  ],
  'workrecord' => [
    'view' => 'workrecord/view_work_record.php',
    'create' => 'workrecord/new_work_record.php',
    'add' => 'workrecord/work_record.php',
    'edit' => 'workrecord/edit_work_record.php',
    'delete' => 'workrecord/delete_work_record.php',
    'stat' => 'workrecord/data_stat.php',
  ],
  'spending' => [
    'view' => 'spending/view.php',
    'create' => 'spending/create.php',
    'delete' => 'spending/delete.php',
    'edit' => 'spending/edit.php',
    'stat' => 'spending/stat.php',
  ]
];


if (in_array($requested_app, $NoCheckedAllowedRoutes)) {
  // no include check token route
  $filePath = __DIR__ . "/" . $routes[$requested_app][$requestedRoute];
  if (file_exists($filePath)) {
    include $filePath;
  } else {
    http_response_code(404);
    echo "Error: File not found!";
  }
} else {
  http_response_code(404);
  echo "Error: Invalid route!";
}