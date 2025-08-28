<?php
namespace Models;

use PDO;

final class RepoInventario {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  /** Tarjetas: inventario destacado (o por mayor stock si no tienes flag) */
  public function destacados(int $limit = 10): array {
    $sql = "SELECT id, nombre, img, stock
              FROM productos
             WHERE destacado = 1
             ORDER BY id DESC
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) { // fallback si no usas 'destacado'
      $sql2 = "SELECT id, nombre, img, stock FROM productos ORDER BY stock DESC LIMIT :lim";
      $st2 = $this->pdo->prepare($sql2);
      $st2->bindValue(':lim', $limit, PDO::PARAM_INT);
      $st2->execute();
      return $st2->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    return $rows;
  }

  /** Tarjeta: agotados */
  public function agotados(int $limit = 10): array {
    $st = $this->pdo->prepare("SELECT id, nombre, img FROM productos WHERE stock <= 0 ORDER BY nombre LIMIT :lim");
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** Chart: productos por acabarse (stock <= umbral) */
  public function bajoStock(int $umbral = 5, int $limit = 10): array {
    $st = $this->pdo->prepare("SELECT nombre, stock FROM productos WHERE stock <= :u AND stock > 0 ORDER BY stock ASC LIMIT :lim");
    $st->bindValue(':u', $umbral, PDO::PARAM_INT);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** Chart: productos por pedir (stock < min_stock) */
  public function porPedir(int $limit = 10): array {
    // requiere columna min_stock en productos
    $st = $this->pdo->prepare("SELECT nombre, (min_stock - stock) AS faltante FROM productos WHERE min_stock IS NOT NULL AND stock < min_stock ORDER BY faltante DESC LIMIT :lim");
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** KPIs */
  public function totalProductos(): int {
    return (int)$this->pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
  }
}
