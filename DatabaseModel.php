<?php namespace Landscape;

    use \PDO;

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

        protected $exists = false;
        protected $db     = NULL;

        public $keys; // This variable must be set by the user

        public final function __construct($op="", $args=NULL) //op -> operation e.g. find ; args = arguments for op, e.g. 'name=test'
        {
            foreach($this->keys as $key => $value)
            {
                $this->fields[$key] = new $value($key, []);
            }
            $this->fields['ID'] = new NumberField("ID", []);
            $this->checkTable();
            switch($op)
            {
                case 'find':
                    $a = explode("=",$args);
                    $this->exists = true;
                    $this->load($a[0], $a[1]);
            }
        }

        public final function set($key, $value)
        {
            $this->fields[$key]->setValue($value);
        }
        public final function get($key)
        {
            return $this->fields[$key]->getValue();
        }

        public final function save()
        {
            if(!$this->exists)
            {
                $querry = "INSERT INTO ".explode("\\",static::class)[1]."(";
                $first = true;
                foreach($this->keys as $key => $value)
                {
                    if($first == false)
                        $querry = $querry.",";
                    else
                        $first = false;
                    $querry = $querry.$key;
                }
                $querry = $querry.") VALUES(";
                $first = true;
                foreach($this->fields as $key => $value)
                {
                    if($first == false)
                        $querry = $querry.",";
                    else
                        $first = false;
                    $querry = $querry."'".$value->getValue()."'";
                }
                $querry = $querry.");";
                $this->execute($querry);
            }
            else
            {
                $querry = "UPDATE ".explode("\\",static::class)[1]." SET ";
                $first = true;
                foreach($this->keys as $key => $value)
                {
                    if($first == false)
                        $querry = $querry.",";
                    else
                        $first = false;
                    $querry = $querry.$key."='".$this->fields[$key]->getValue()."'";
                }
                $querry = $querry." WHERE ID=".$this->fields['ID']->getValue().";";
                var_dump($querry);
                $this->execute($querry);

            }
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
            $this->execute($querry);
        }

        protected final function execute($sql, $nofatch=true)
        {
            if($this->db == NULL)
                $this->db = new PDO("sqlite:".$this->database);

            if($nofatch == false)
                $ret =  $this->db->query($sql)->fetchAll();
            else
                $ret =  $this->db->query($sql);
            return $ret;
        }

        protected final function load($field, $value)
        {
            $query = "SELECT * from ".explode("\\",static::class)[1]." WHERE ".$field." IS '".$value."';";
            $result = $this->execute($query, false);
            if(sizeof($result) != 1)
            {
                $this->exists = false;
                return false;
            }
            $result = $result[0];
            foreach($this->fields as $key => $value)
            {
                $this->fields[$key]->setValue($result[$key]);
            }
        }

    }

?>
