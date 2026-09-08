<?php
class Conexion {

    private $host     = "localhost";
    private $puerto   = "49672"; 
    private $dbName   = "sigsm";
    private $usuario  = "root"; 
    private $clave    = "";    
    private $charset  = "utf8mb4";

    private $pdo;

    public function __construct() {

    $dsn = "mysql:host={$this->host};port={$this->puerto};dbname={$this->dbName};charset={$this->charset}";

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->usuario, $this->clave, $opciones);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public function getConexion() {
        return $this->pdo;
    }
}