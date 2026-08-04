<?php
	session_start();
	$con=mysqli_connect((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""),(getenv('ZEN_DB_DATABASE_HRD2') ?: ""));
	
//Connect to my database
  class Database
  {
      // private static $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "") ;
      private static $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "") ;
      private static $dbHost = (getenv('ZEN_DB_HOST') ?: "") ;
      private static $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
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

  //Connect to HR Database
  class HRDatabase
  {
      private static $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "") ;
      private static $dbHost = (getenv('ZEN_DB_HOST') ?: "") ;
      private static $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
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

  //Connect to db_main database
  class MainDatabase
  {
      private static $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "") ;
      private static $dbHost = (getenv('ZEN_DB_HOST') ?: "") ;
      private static $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
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

  //Connect to HR1 Database
  class HR1Database
  {
      private static $dbName = (getenv('ZEN_DB_DATABASE_HRD2') ?: "") ;
      private static $dbHost = (getenv('ZEN_DB_HOST') ?: "") ;
      private static $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
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
   class DTRDatabase
  {
      private static $dbName = 'ecom' ;
      private static $dbHost = (getenv('ZEN_DB_HOST') ?: "") ;
      private static $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
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
          $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "ecom");
          return $cont;
      }
  }

  // class APPDatabase
  // {
  //     private static $dbName = 'db_applicants' ;
  //     private static $dbHost = (getenv('ZEN_DB_HOST') ?: "") ;
  //     private static $dbUsername = (getenv('ZEN_DB_USERNAME') ?: "");
  //     private static $dbUserPassword = '';

  //     private static $cont  = null;

  //     public function __construct() {
  //         die('Init function is not allowed');
  //     }
      
  //     public static function connect()
  //     {
  //        // One connection through whole application
  //        if ( null == self::$cont )
  //        {     
  //         try
  //         {
  //           self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            
  //         }
  //         catch(PDOException $e)
  //         {
  //           die($e->getMessage()); 
  //         }
  //        }
  //        return self::$cont;
  //     }
       
  //     public static function disconnect()
  //     {
  //         self::$cont = null;
  //     }
  //     public static function mysqli()
  //     {
  //         $cont = new mysqli((getenv('ZEN_DB_HOST') ?: ""), (getenv('ZEN_DB_USERNAME') ?: ""), (getenv('ZEN_DB_PASSWORD') ?: ""), "db_applicants");
  //         return $cont;
  //     }
  // }
?>