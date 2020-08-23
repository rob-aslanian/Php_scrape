<?php
   namespace CustomDB;

   use PDO;
  
   class MySQLDB {

      private static $_instance  = null;
      private $conn;

      private function __construct() {
          try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ];
             $this->$conn = new PDO("mysql:host={$_ENV['DB_HOST']};
                            dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $options);

            $query = file_get_contents('commands/create_table.sql' , true);
            $this->$conn->exec($query);

          } catch (PDOException $e) {
             die($e->getMessage());
          }
      }

     
      public static function getInstance() {
        if(!isset(self::$_instance)) {
          self::$_instance = new MySQLDB();
        }
        
        return self::$_instance;
      }

      public function getConnection() {
        return $this->$conn;
      }

      public function insert($data) {
         try {
            $sql = file_get_contents('commands/insert.sql' , true);
            $this->$conn->prepare($sql)->execute($data);
         } catch (PDOException $e) {
            throw $e;
         }
      }

      public function update($data) {
         try {
            $sql = file_get_contents('commands/update.sql' , true);
            $this->$conn->prepare($sql)->execute($data);
         } catch (PDOException $e) {
            throw $e;
         }
      }

      public function getFirst() {
         $first = $this->_getAndPrepareSQLFile('get_first');
         $first->execute();

         return $first->fetch();
      }

      public function getAll() {
         $posts = $this->_getAndPrepareSQLFile('get_all');
         $posts->execute();

         return $posts->fetchAll();
      }

      public function getByID($id) {
        $post =  $this->_getAndPrepareSQLFile('get_byID');
        $post->execute([$id]);

        return $post->fetch();
      }


      private  function _getAndPrepareSQLFile($filename) {
         $sql = file_get_contents('commands/' . $filename . '.sql' , true);
         return $this->$conn->prepare($sql);
      }

    }
?>