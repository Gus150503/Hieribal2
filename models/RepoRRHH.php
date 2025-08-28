<?php
namespace Models;

use PDO;

final class RepoRRHH {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  /** Tarjeta: empleados con 1 año (o más) */
    public function empleadosConAnio(int $limit = 10): array {
        $sql = "SELECT id_usuario AS id,
                    CONCAT(nombres,' ',apellidos) AS nombre,
                    fecha_creacion AS desde
                FROM usuarios
                WHERE DATEDIFF(CURDATE(), fecha_creacion) >= 365
            ORDER BY fecha_creacion DESC
                LIMIT :lim";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


  /** KPIs */
  public function totalEmpleados(): int {
    return (int)$this->pdo->query("SELECT COUNT(*) FROM empleados")->fetchColumn();
  }
}
