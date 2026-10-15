<?php
// ============================================================
// Clase Encuesta: encuesta de satisfacción ANÓNIMA.
// No se guarda quién la responde: la tabla encuestas_satisfaccion
// no está relacionada con pacientes ni con documentos.
// ============================================================

class Encuesta {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Guarda una respuesta (calificación de 1 a 5 y comentario opcional).
    public function guardar($calificacion, $comentarios) {
        $stmt = $this->pdo->prepare("INSERT INTO encuestas_satisfaccion (calificacion, comentarios) VALUES (?, ?)");
        $stmt->execute([$calificacion, $comentarios !== '' ? $comentarios : null]);
    }

    // Devuelve el total de respuestas, el promedio y cuántas hay de cada nota.
    public function resumen() {
        $resumen = ['total' => 0, 'promedio' => 0, 'por_nota' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]];

        $filas = $this->pdo->query(
            "SELECT calificacion, COUNT(*) AS cantidad FROM encuestas_satisfaccion GROUP BY calificacion"
        )->fetchAll();

        $suma = 0;
        foreach ($filas as $fila) {
            $nota     = (int) $fila['calificacion'];
            $cantidad = (int) $fila['cantidad'];
            $resumen['por_nota'][$nota] = $cantidad;
            $resumen['total'] += $cantidad;
            $suma += $nota * $cantidad;
        }
        if ($resumen['total'] > 0) {
            $resumen['promedio'] = round($suma / $resumen['total'], 1);
        }
        return $resumen;
    }

    // Últimos comentarios escritos (se ignoran las respuestas sin comentario).
    public function ultimosComentarios($cantidad = 10) {
        $cantidad = (int) $cantidad;   // se convierte a número para poder usarlo en LIMIT
        return $this->pdo->query(
            "SELECT calificacion, comentarios, fecha
             FROM encuestas_satisfaccion
             WHERE comentarios IS NOT NULL
             ORDER BY id DESC
             LIMIT $cantidad"
        )->fetchAll();
    }
}
