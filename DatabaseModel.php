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

        public $keys; // This variable must be set by the user

        public final function __construct($op="", $args=[]) //op -> operation e.g. find ; args = arguments for op, e.g. { name => test }
        {
            foreach($keys as $key => $value)
            {
                $fields[$key] = new $value();
            }
        }

    }

?>
