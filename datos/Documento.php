<?php
// ============================================================
// Clase Documento: todo lo que se hace con la tabla "documento"
// (alta, listado, edición y baja). Usa consultas preparadas:
// los "?" se completan al ejecutar, así nadie puede meter SQL
// escribiendo en los formularios (inyección SQL).
// ============================================================

class Documento {

    // Atributos del documento
    public $id;
    public $titulo;
    public $descripcion;
    public $archivo;
    public $token;
    public $fecha_emision;
    public $id_categoria;
    public $id_paciente;

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Guarda el documento: si ya tiene id hace UPDATE, si no hace INSERT.
    public function guardar() {
        if ($this->id) {
            $sql = "UPDATE documento
                    SET titulo = ?, descripcion = ?, archivo = ?, fecha_emision = ?,
                        id_categoria = ?, id_paciente = ?
                    WHERE id = ?";
            $this->pdo->prepare($sql)->execute([
                $this->titulo, $this->descripcion, $this->archivo, $this->fecha_emision,
                $this->id_categoria, $this->id_paciente, $this->id
            ]);
        } else {
            // El token es un código al azar que va dentro del QR (no se puede adivinar).
            $this->token = bin2hex(random_bytes(16));

            $sql = "INSERT INTO documento
                        (titulo, descripcion, archivo, token, fecha_emision, id_categoria, id_paciente)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $this->pdo->prepare($sql)->execute([
                $this->titulo, $this->descripcion, $this->archivo, $this->token,
                $this->fecha_emision, $this->id_categoria, $this->id_paciente
            ]);
            $this->id = $this->pdo->lastInsertId();
        }
    }

    // Baja lógica: el documento no se borra de la base, solo queda inactivo.
    public function darDeBaja($id) {
        $stmt = $this->pdo->prepare("UPDATE documento SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Consulta base: documentos activos junto con su categoría y su paciente.
    private function consultaBase() {
        return "SELECT d.id, d.titulo, d.descripcion, d.archivo, d.token, d.fecha_emision,
                       d.id_categoria, d.id_paciente,
                       c.nombre AS categoria,
                       u.nombre AS nombre_paciente,
                       p.ci
                FROM documento d
                JOIN categoria c ON c.id = d.id_categoria
                JOIN paciente  p ON p.id = d.id_paciente
                JOIN usuario   u ON u.id = p.id_usuario
                WHERE d.activo = 1";
    }

    // Lista los documentos (todos, o solo los del paciente indicado).
    public function listar($idPaciente = '') {
        $sql = $this->consultaBase();
        $valores = [];

        if ($idPaciente !== '') {
            $sql .= " AND d.id_paciente = ?";
            $valores[] = $idPaciente;
        }

        $sql .= " ORDER BY d.fecha_emision DESC, d.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($valores);
        return $stmt->fetchAll();
    }

    // Un documento por su id (para editar). Devuelve false si no existe.
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare($this->consultaBase() . " AND d.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Un documento por su token (para la vista del QR). Devuelve false si no existe.
    public function buscarPorToken($token) {
        $stmt = $this->pdo->prepare($this->consultaBase() . " AND d.token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    // Categorías (para los select del formulario).
    public function listarCategorias() {
        return $this->pdo->query("SELECT id, nombre FROM categoria ORDER BY nombre")->fetchAll();
    }

    // Pacientes con su nombre (para los select del formulario).
    public function listarPacientes() {
        $sql = "SELECT p.id, p.ci, u.nombre
                FROM paciente p
                JOIN usuario u ON u.id = p.id_usuario
                ORDER BY u.nombre";
        return $this->pdo->query($sql)->fetchAll();
    }

    // Comprueban que la categoría / el paciente existan (se usan al validar el formulario).
    public function categoriaExiste($id) {
        $stmt = $this->pdo->prepare("SELECT id FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
        return (bool) $stmt->fetch();
    }

    public function pacienteExiste($id) {
        $stmt = $this->pdo->prepare("SELECT id FROM paciente WHERE id = ?");
        $stmt->execute([$id]);
        return (bool) $stmt->fetch();
    }
}
