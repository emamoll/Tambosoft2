<?php

require_once __DIR__ . '../../databaseConnectionInterface.php';
require_once __DIR__ . '../../env.php';

class PostgresDatabaseConnection implements DatabaseConnectionInterface
{
  private $host;
  private $dbname;
  private $username;
  private $password;

  public function __construct()
  {
    $env = new Env();
    $this->host = $env->get('DB_HOST');
    $this->username = $env->get('DB_USERNAME');
    $this->password = $env->get('DB_PASSWORD');
    $this->dbname = $env->get('DB_NAME');
  }

  public function connect()
  {
    try {
      $conn = pg_connect("host=$this->host dbname=$this->dbname user=$this->username password=$this->password");
      if (!$conn) {
        die("Conexión fallida: " . pg_last_error());
      }

      return $conn;
    } catch (\Throwable $th) {
      die("Conexión fallida: " . pg_last_error());
    }
  }

  public function query($query)
  {
    $conn = $this->connect();
    $result = pg_query($conn, $query);
    pg_close($conn);

    return $result;
  }

  public function close()
  {
    // No es necesario por el momento
  }
}
