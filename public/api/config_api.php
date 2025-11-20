<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
// Evita warning si la sesión ya está abierta
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

use Core\Database;

// ===== Autorización (sólo admin) =====
$isAdmin =
  (($_SESSION['user']['rol']  ?? '') === 'admin') ||
  (($_SESSION['admin']['rol'] ?? '') === 'admin');

if (!$isAdmin) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
  exit;
}

// ===== Conexión PDO Core\Database =====
try {
  // Si por alguna razón $config no está disponible, lo cargamos aquí
  if (!isset($config)) {
    $appCfg = __DIR__ . '/../../config/app.php';
    $envCfg = __DIR__ . '/../../config/env.php';
    if (is_file($appCfg))      { $config = require $appCfg; }
    elseif (is_file($envCfg))  { $config = require $envCfg; }
    else                       { $config = []; }
  }

  $db = $config['db'] ?? [];

  if (!empty($db['dsn'])) {
    $dsn  = $db['dsn'];
    $user = $db['user'] ?? $db['username'] ?? 'root';
    $pass = $db['pass'] ?? $db['password'] ?? '';
  } else {
    $driver   = $db['driver']   ?? 'mysql';
    $host     = $db['host']     ?? '127.0.0.1';
    $dbname   = $db['database'] ?? 'hierbal';
    $charset  = $db['charset']  ?? 'utf8mb4';
    $user     = $db['username'] ?? $db['user'] ?? 'root';
    $pass     = $db['password'] ?? $db['pass'] ?? '';
    $dsn = sprintf('%s:host=%s;dbname=%s;charset=%s', $driver, $host, $dbname, $charset);
  }

  /** @var PDO $pdo */
  $pdo = Database::get([
    'dsn'  => $dsn,
    'user' => $user,
    'pass' => $pass,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
  exit;
}

// ===== Router de acciones =====
$action = $_GET['action'] ?? '';

try {

  // ---------- GET: devuelve todas las claves (o por prefijo) ----------
  if ($action === 'get') {
    $prefix = trim((string)($_GET['prefix'] ?? ''));
    $sql = $prefix
      ? "SELECT clave, valor, tipo FROM config WHERE clave LIKE :pfx"
      : "SELECT clave, valor, tipo FROM config";

    $st = $pdo->prepare($sql);
    if ($prefix) { $st->execute([':pfx' => $prefix.'%']); } else { $st->execute(); }

    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $data = [];

    foreach ($rows as $r) {
      $v = $r['valor'];
      $tipo = strtolower((string)$r['tipo']);
      if ($tipo === 'int')  { $v = (int)$v; }
      elseif ($tipo === 'bool') { $v = ($v === '1' || $v === 'true'); }
      elseif ($tipo === 'json') {
        $dec = json_decode($v, true);
        $v = is_array($dec) ? $dec : [];
      }
      $data[$r['clave']] = $v;
    }

    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // ---------- UPDATE: items[key] = value ----------
  if ($action === 'update') {
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
      throw new Exception('Datos inválidos');
    }

    // Normaliza algunas claves booleanas conocidas
    $boolKeys = ['correo_activo'];

    // UPSERT (MySQL): inserta o actualiza valor manteniendo "tipo"
    $sqlUpsert = "
      INSERT INTO config (clave, valor)
      VALUES (:clave, :valor)
      ON DUPLICATE KEY UPDATE valor = VALUES(valor)
    ";
    $st = $pdo->prepare($sqlUpsert);

    $pdo->beginTransaction();

    foreach ($_POST['items'] as $k => $v) {
      // Normaliza booleanos conocidos a '1' / '0'
      if (in_array($k, $boolKeys, true)) {
        $v = ($v === '1' || $v === 1 || $v === true || $v === 'true') ? '1' : '0';
      }

      // Si viene arreglo, guarda como JSON
      $valor = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v;

      $st->execute([':clave' => $k, ':valor' => $valor]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true, 'msg' => 'Configuración guardada'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  throw new Exception('Acción no soportada');

} catch (Throwable $e) {
  if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
