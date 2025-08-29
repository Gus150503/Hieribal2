<?php
namespace Models;

use PDO;

final class RepoInventario {
  private PDO $pdo;

  public function __construct(PDO $pdo){
    $this->pdo = $pdo;
  }

  /** Tarjetas: inventario destacado (o por mayor stock si no hay flag) */
  public function destacados(int $limit = 10): array {
    // Si en el futuro tienes un flag "destacado", úsalo aquí. Por ahora: mayor stock_actual.
    $sql = "SELECT id, nombre, imagen AS img, stock_actual AS stock
              FROM productos
             WHERE LOWER(estado)='activo'
             ORDER BY stock_actual DESC
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** Tarjetas: agotados (stock_actual <= 0) */
  public function agotados(int $limit = 10): array {
    $sql = "SELECT id, nombre, imagen AS img
              FROM productos
             WHERE stock_actual <= 0
             ORDER BY nombre
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** Chart: productos por acabarse (0 < stock_actual <= umbral) */
  public function bajoStock(int $umbral = 5, int $limit = 10): array {
    $sql = "SELECT nombre, stock_actual AS stock
              FROM productos
             WHERE stock_actual > 0 AND stock_actual <= :u
             ORDER BY stock_actual ASC
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':u', $umbral, PDO::PARAM_INT);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** Chart: productos por pedir (stock_actual < stock_minimo) */
  public function porPedir(int $limit = 10): array {
    $sql = "SELECT nombre, (stock_minimo - stock_actual) AS faltante
              FROM productos
             WHERE stock_minimo IS NOT NULL AND stock_actual < stock_minimo
             ORDER BY faltante DESC
             LIMIT :lim";
    $st = $this->pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /** KPIs */
  public function totalProductos(): int {
    return (int)$this->pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
  }
}
