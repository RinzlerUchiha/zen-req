<?php

  require_once("config.php");

  //Connect to my database
  class ATD
  {
      private static $dbName = DB_ATD ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'admin';
      private static $dbUserPassword = 'Administr@t0r';
      private static $cont  = null;
      
      public function __construct() {
          die('Init function is not allowed');
      }

      public static function connect()
      {
         // One connection through whole application
         if ( null == self::$cont )
         {     
          try
          {
            self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
          }
          catch(PDOException $e)
          {
            die($e->getMessage()); 
          }
         }
         return self::$cont;
      }
       
      public static function disconnect()
      {
          self::$cont = null;
      }

      public static function mysqli()
      {
          $cont = new mysqli("localhost", "admin", "Administr@t0r", DB_ATD);
          return $cont;
      }
  }

    class DB
  {
      private static $dbName = 'demo_db_hr' ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'admin';
      private static $dbUserPassword = 'Administr@t0r';
      private static $cont  = null;
      
      public function __construct() {
          die('Init function is not allowed');
      }

      public static function connect()
      {
         // One connection through whole application
         if ( null == self::$cont )
         {     
          try
          {
            self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
          }
          catch(PDOException $e)
          {
            die($e->getMessage()); 
          }
         }
         return self::$cont;
      }
       
      public static function disconnect()
      {
          self::$cont = null;
      }

      public static function mysqli()
      {
          $cont = new mysqli("localhost", "admin", "Administr@t0r", "demo_db_hr");
          return $cont;
      }
  }

  //Connect to HR Database
  class ZenDatabase
  {
      private static $dbName = DB_ZEN ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'admin';
      private static $dbUserPassword = 'Administr@t0r';

      private static $cont  = null;

      public function __construct() {
          die('Init function is not allowed');
      }
      
      public static function connect()
      {
         // One connection through whole application
         if ( null == self::$cont )
         {     
          try
          {
            self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            
          }
          catch(PDOException $e)
          {
            die($e->getMessage()); 
          }
         }
         return self::$cont;
      }
       
      public static function disconnect()
      {
          self::$cont = null;
      }
      public static function mysqli()
      {
          $cont = new mysqli("localhost", "admin", "Administr@t0r", DB_ZEN);
          return $cont;
      }
  }

  //Connect to HR Database
  class HRDatabase
  {
      private static $dbName = DB_HRD ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'admin';
      private static $dbUserPassword = 'Administr@t0r';

      private static $cont  = null;

      public function __construct() {
          die('Init function is not allowed');
      }
      
      public static function connect()
      {
         // One connection through whole application
         if ( null == self::$cont )
         {     
          try
          {
            self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            
          }
          catch(PDOException $e)
          {
            die($e->getMessage()); 
          }
         }
         return self::$cont;
      }
       
      public static function disconnect()
      {
          self::$cont = null;
      }
      public static function mysqli()
      {
          $cont = new mysqli("localhost", "admin", "Administr@t0r", DB_HRD);
          return $cont;
      }
  }

  //Connect to db_main database
  class MainDatabase
  {
      private static $dbName = DB_MAIN ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'admin';
      private static $dbUserPassword = 'Administr@t0r';
      private static $cont  = null;
      
      public function __construct() {
          die('Init function is not allowed');
      }

      public static function connect()
      {
         // One connection through whole application
         if ( null == self::$cont )
         {     
          try
          {
            self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
          }
          catch(PDOException $e)
          {
            die($e->getMessage()); 
          }
         }
         return self::$cont;
      }
       
      public static function disconnect()
      {
          self::$cont = null;
      }

      public static function mysqli()
      {
          $cont = new mysqli("localhost", "admin", "Administr@t0r", DB_MAIN);
          return $cont;
      }
  }
//------------------------------------------NEW------------------------------//
?>