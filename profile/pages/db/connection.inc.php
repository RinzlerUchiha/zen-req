<?php
	session_start();
	$con=mysqli_connect("localhost","root","","tngc_hrd2");
	
//Connect to my database
  class Database
  {
      // private static $dbName = 'db_hr' ;
      private static $dbName = 'tngc_hrd2' ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'root';
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
          $cont = new mysqli("localhost", "root", "", "tngc_hrd2");
          return $cont;
      }
  }

  //Connect to HR Database
  class HRDatabase
  {
      private static $dbName = 'tngc_hrd2' ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'root';
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
          $cont = new mysqli("localhost", "root", "", "tngc_hrd2");
          return $cont;
      }
  }

  //Connect to db_main database
  class MainDatabase
  {
      private static $dbName = 'tngc_hrd2' ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'root';
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
          $cont = new mysqli("localhost", "root", "", "tngc_hrd2");
          return $cont;
      }
  }

  //Connect to HR1 Database
  class HR1Database
  {
      private static $dbName = 'tngc_hrd2' ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'root';
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
          $cont = new mysqli("localhost", "root", "", "tngc_hrd2");
          return $cont;
      }
  }
   class DTRDatabase
  {
      private static $dbName = 'ecom' ;
      private static $dbHost = 'localhost' ;
      private static $dbUsername = 'root';
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
          $cont = new mysqli("localhost", "root", "", "ecom");
          return $cont;
      }
  }

  // class APPDatabase
  // {
  //     private static $dbName = 'db_applicants' ;
  //     private static $dbHost = 'localhost' ;
  //     private static $dbUsername = 'root';
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
  //         $cont = new mysqli("localhost", "root", "", "db_applicants");
  //         return $cont;
  //     }
  // }
?>