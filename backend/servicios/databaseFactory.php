<?php

require_once __DIR__ . '../../servicios/databases/mysqlDatabaseConnection.php';
// require_once __DIR__ . '../../servicios/databases/postgresDatabaseConnection.php';

class DatabaseFactory
{
  public static function createDatabaseConnection($type)
  {
    switch ($type) {
      case 'mysql':
        return new MySQLDatabaseConnection();
      // case 'postgres':
      //   return new PostgresDatabaseConnection();
      default:
        throw new Exception('Tipo de base de datos no soportado');
    }
  }
}
