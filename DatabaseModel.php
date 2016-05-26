<?php namespace Landscape;

    if(file_exists("vendor/landscape/landscape.php.model/fields.php") === false)
    {
        print("## Using local fileds file\n");
        require_once('fields.php');
    }
    else
        require_once("vendor/landscape/landscape.php.model/fields.php");

    abstract class DatabaseModel
    {
        protected $fields  = [];
        protected $version = 1;
        protected $database = "landscape.db";

        public $keys; // This variable must be set by the user

        public final function __construct($op="", $args=NULL) //op -> operation e.g. find ; args = arguments for op, e.g. { name => test }
        {
            foreach($this->keys as $key => $value)
            {
                $this->fields[$key] = new $value($key, []);
            }
            $this->checkTable();
        }

        protected final function checkTable()
        {
            $querry = "CREATE TABLE IF NOT EXISTS ".explode("\\",static::class)[1]." (\nID INTEGER PRIMARY KEY AUTOINCREMENT,\n";
            $first = true;
            foreach($this->keys as $key => $value)
            {
                if($first == false)
                {
                    $querry = $querry.",\n";
                }
                else
                    $first = false;

                $sql = $this->fields[$key]->getSQLDefinition();
                $querry = $querry." ".$key." ".$sql;
            }
            $querry = $querry."\n);";
            print($querry);
        }
    }

?>
