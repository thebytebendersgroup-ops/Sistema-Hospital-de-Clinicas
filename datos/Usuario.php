<?php
// ============================================================
// CAPA DE DATOS - Usuario
// Representa a una persona que entra al sistema (admin o paciente).
// Acá están las consultas del login, para que la capa de negocio no tenga SQL.
// ============================================================

class Usuario {

    // Atributos (se llenan cuando el login es correcto)
    public $id;
    public $nombre;
    public $email;
    public $rol;

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Busca el email en la base y compara la contraseña con password_verify().
    // Devuelve true si el login es correcto (y llena los atributos), false si no.
    public function iniciarSesion($email, $contrasenia) {
        $stmt = $this->pdo->prepare(
            "SELECT id, nombre, email, contrasenia, rol
             FROM usuario
             WHERE email = ? AND activo = 1"
        );
        $stmt->execute([$email]);
        $fila = $stmt->fetch();

        // Si el usuario no existe o la contraseña está mal, el resultado es el mismo: false.
        if (!$fila || !password_verify($contrasenia, $fila['contrasenia'])) {
            return false;
        }

        $this->id     = $fila['id'];
        $this->nombre = $fila['nombre'];
        $this->email  = $fila['email'];
        $this->rol    = $fila['rol'];
        return true;
    }

    // Devuelve el id de paciente que corresponde a este usuario (o null si no es paciente).
    public function buscarIdPaciente() {
        $stmt = $this->pdo->prepare("SELECT id FROM paciente WHERE id_usuario = ?");
        $stmt->execute([$this->id]);
        $fila = $stmt->fetch();
        return $fila ? $fila['id'] : null;
    }
}
