<?php
  //Connect to my database
  class DB
  {
      private static $dbName = 'demo_db_hr' ;
      private static $dbHost = "";
      private static $dbUsername = "";
      private static $dbUserPassword = '';
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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "demo_db_hr");
          return $cont;
      }
  }

  //Connect to HR Database
  class HRDatabase
  {
      private static $dbName = "";
      private static $dbHost = "";
      private static $dbUsername = "";
      private static $dbUserPassword = '';

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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), (getenv('ZEN_DB_DATABASE_HRD2') ?: ""));
          return $cont;
      }
  }

  //Connect to demo_db_main database
  class MainDatabase
  {
      private static $dbName = 'demo_db_main' ;
      private static $dbHost = "";
      private static $dbUsername = "";
      private static $dbUserPassword = '';
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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "demo_db_main");
          return $cont;
      }
  }

  //Connect to HR1 Database
  class HR1Database
  {
      private static $dbName = 'demo_tngc_hrd' ;
      private static $dbHost = "";
      private static $dbUsername = "";
      private static $dbUserPassword = '';

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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "demo_tngc_hrd");
          return $cont;
      }
  }
   class DTRDatabase
  {
      private static $dbName = 'demo_db_dtr' ;
      private static $dbHost = "";
      private static $dbUsername = "";
      private static $dbUserPassword = '';

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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "demo_db_dtr");
          return $cont;
      }
  }

  class APPDatabase
  {
      private static $dbName = 'demo_db_applicants' ;
      private static $dbHost = "";
      private static $dbUsername = "";
      private static $dbUserPassword = '';

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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "demo_db_applicants");
          return $cont;
      }
  }
//------------------------------------------NEW------------------------------//
?>