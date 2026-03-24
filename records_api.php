<?php
header('Content-Type: application/json');
$file = __DIR__ . '/records.json';
if (!file_exists($file)) { file_put_contents($file, json_encode([])); }

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
  $input = file_get_contents('php://input');
  $data = json_decode($input, true);
  if (!is_array($data)) { http_response_code(400); echo json_encode(['error'=>'invalid']); exit; }
  $list = json_decode(file_get_contents($file), true);
  if (!is_array($list)) { $list = []; }
  $list[] = [
    'visit' => $data['visit'] ?? '',
    'age' => $data['age'] ?? '',
    'weight' => $data['weight'] ?? '',
    'head' => $data['head'] ?? '',
    'chest' => $data['chest'] ?? '',
    'length' => $data['length'] ?? '',
    'instructions' => $data['instructions'] ?? '',
    'next' => $data['next'] ?? ''
  ];
  file_put_contents($file, json_encode($list));
  echo json_encode(['ok'=>true]);
  exit;
}

if ($method === 'GET') {
  $list = json_decode(file_get_contents($file), true);
  if (!is_array($list)) { $list = []; }
  $date = $_GET['date'] ?? '';
  if ($date) {
    $list = array_values(array_filter($list, function($r) use ($date){ return ($r['visit'] ?? '') === $date; }));
  }
  echo json_encode($list);
  exit;
}

http_response_code(405);
echo json_encode(['error'=>'method_not_allowed']);
?>