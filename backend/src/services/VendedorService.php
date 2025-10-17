<?php

namespace App\Services;

class VendedorService
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  /**
   * Obtiene KPIs para el vendedor autenticado.
   */
  public function obtenerKPIsVendedor($idVendedor)
  {
    $kpis = [];

    // 1. Total Productos
    $sql = "SELECT COUNT(*) AS total FROM producto WHERE id_vendedor = :id_vendedor AND estado != 'inactivo'";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id_vendedor', $idVendedor);
    $stmt->execute();
    $kpis['total_productos'] = (int) $stmt->fetchColumn();

    // 2. Órdenes Pendientes (pendiente o pagada)
    $sql = "SELECT COUNT(*) AS total 
                FROM venta 
                WHERE id_vendedor = :id_vendedor 
                  AND estado IN ('pendiente', 'pagada')";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id_vendedor', $idVendedor);
    $stmt->execute();
    $kpis['ordenes_pendientes'] = (int) $stmt->fetchColumn();

    // 3. Ingresos del Mes
    $primerDia = date('Y-m-01');
    $hoy = date('Y-m-d');
    $sql = "SELECT COALESCE(SUM(total_venta), 0) AS total 
                FROM venta 
                WHERE id_vendedor = :id_vendedor 
                  AND estado IN ('pagada', 'enviada', 'entregada')
                  AND DATE(fecha) BETWEEN :inicio AND :fin";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id_vendedor', $idVendedor);
    $stmt->bindParam(':inicio', $primerDia);
    $stmt->bindParam(':fin', $hoy);
    $stmt->execute();
    $kpis['ingresos_mes'] = (float) $stmt->fetchColumn();

    // 4. Productos Bajo Stock
    $sql = "SELECT COUNT(*) AS total 
                FROM producto 
                WHERE id_vendedor = :id_vendedor 
                  AND estado = 'activo' 
                  AND stock < 10";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id_vendedor', $idVendedor);
    $stmt->execute();
    $kpis['productos_bajo_stock'] = (int) $stmt->fetchColumn();

    // 5. Tasa de Conversión (visitas → órdenes del mes)
    /* Órdenes del mes
        $sqlOrdenes = "SELECT COUNT(*) AS total 
                       FROM venta 
                       WHERE id_vendedor = :id_vendedor 
                         AND estado IN ('pagada', 'enviada', 'entregada')
                         AND DATE(fecha) BETWEEN :inicio AND :fin";
        $stmt = $this->db->prepare($sqlOrdenes);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->bindParam(':inicio', $primerDia);
        $stmt->bindParam(':fin', $hoy);
        $stmt->execute();
        $ordenesMes = (int) $stmt->fetchColumn();

        // Visitas del mes
        $sqlVisitas = "SELECT COUNT(*) AS total 
                       FROM visita_producto vp
                       JOIN producto p ON vp.id_producto = p.id_producto
                       WHERE p.id_vendedor = :id_vendedor
                         AND DATE(vp.fecha) BETWEEN :inicio AND :fin";
        $stmt = $this->db->prepare($sqlVisitas);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->bindParam(':inicio', $primerDia);
        $stmt->bindParam(':fin', $hoy);
        $stmt->execute();
        $visitasMes = (int) $stmt->fetchColumn();

        $kpis['tasa_conversion'] = $visitasMes > 0 ? round(($ordenesMes / $visitasMes) * 100, 2) : 0;
        */
    $kpis['tasa_conversion'] = 0;
    // 6. Órdenes Entregadas (últimos 7 días)
    $hace7Dias = date('Y-m-d', strtotime('-7 days'));
    $sql = "SELECT COUNT(*) AS total 
                FROM venta 
                WHERE id_vendedor = :id_vendedor 
                  AND estado = 'entregada'
                  AND DATE(fecha) >= :fecha_inicio";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id_vendedor', $idVendedor);
    $stmt->bindParam(':fecha_inicio', $hace7Dias);
    $stmt->execute();
    $kpis['ordenes_entregadas_semana'] = (int) $stmt->fetchColumn();

    return $kpis;
  }
  /**
   * Obtiene datos para el gráfico de ventas mensuales (últimos 12 meses).
   */
  public function obtenerVentasMensuales($idVendedor)
  {
    // Generar los últimos 12 meses
    $meses = [];
    $ventas = [];
    for ($i = 11; $i >= 0; $i--) {
      $fecha = new \DateTime();
      $fecha->modify("-$i months");
      $mes = $fecha->format('Y-m');
      $meses[] = $fecha->format('M Y'); // Ej: "Oct 2024"

      // Sumar ventas del mes
      $inicio = $fecha->format('Y-m-01');
      $fin = $fecha->format('Y-m-t'); // Último día del mes

      $sql = "SELECT COALESCE(SUM(total_venta), 0) AS total
                FROM venta
                WHERE id_vendedor = :id_vendedor
                  AND estado IN ('pagada', 'enviada', 'entregada')
                  AND DATE(fecha) BETWEEN :inicio AND :fin";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':id_vendedor', $idVendedor);
      $stmt->bindParam(':inicio', $inicio);
      $stmt->bindParam(':fin', $fin);
      $stmt->execute();
      $ventas[] = (float) $stmt->fetchColumn();
    }

    return [
      'labels' => $meses,
      'data' => $ventas
    ];
  }

  /**
   * Obtiene órdenes por categoría.
   */
  public function obtenerOrdenesPorCategoria($idVendedor)
  {
    $sql = "SELECT 
                c.nombre AS categoria,
                COUNT(v.id_venta) AS total_ordenes
            FROM venta v
            JOIN detalle_venta dv ON v.id_venta = dv.id_venta
            JOIN producto p ON dv.id_producto = p.id_producto
            JOIN subcategoria sc ON p.id_subcategoria = sc.id_subcategoria
            JOIN categoria c ON sc.id_categoria = c.id_categoria
            WHERE v.id_vendedor = :id_vendedor
              AND v.estado IN ('pagada', 'enviada', 'entregada')
            GROUP BY c.id_categoria, c.nombre
            ORDER BY total_ordenes DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id_vendedor', $idVendedor);
    $stmt->execute();
    $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $categorias = [];
    $totales = [];
    foreach ($resultados as $fila) {
      $categorias[] = $fila['categoria'];
      $totales[] = (int) $fila['total_ordenes'];
    }

    return [
      'labels' => $categorias,
      'data' => $totales
    ];
  }

  /**
   * Obtiene distribución de estados de órdenes.
   */
  public function obtenerDistribucionEstados($idVendedor)
  {
    $estadosValidos = ['pendiente', 'pagada', 'enviada', 'entregada', 'cancelada'];
    $labels = [];
    $data = [];

    foreach ($estadosValidos as $estado) {
      $sql = "SELECT COUNT(*) AS total FROM venta WHERE id_vendedor = :id_vendedor AND estado = :estado";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':id_vendedor', $idVendedor);
      $stmt->bindParam(':estado', $estado);
      $stmt->execute();
      $total = (int) $stmt->fetchColumn();

      // Solo incluir si hay órdenes
      if ($total > 0) {
        // Formatear nombre para mostrar (ej: "Pendiente")
        $labels[] = ucfirst($estado);
        $data[] = $total;
      }
    }

    return [
      'labels' => $labels,
      'data' => $data
    ];
  }
}
