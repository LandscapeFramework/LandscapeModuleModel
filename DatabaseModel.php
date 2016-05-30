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
        protected $controler = false;

        public $keys; // This variable must be set by the user

        public final function getFields()
        {
            return $this->fields;
        }

        public final function getColumns()
        {
            $query = "PRAGMA table_info(".explode("\\",static::class)[1].");";
            return $this->execute($query, false);

        }

        public final function __construct($op="", $args=NULL) //op -> operation e.g. find ; args = arguments for op, e.g. 'name=test'
        {
            foreach($this->keys as $key => $value)
            {
                $temp = explode(':',$value);
                $arg  = array_slice($temp, 1);
                $typ  = $temp[0];
                $this->fields[$key] = new $typ($key, $arg);
            }
            $this->fields['ID'] = new NumberField("ID", []);
            $this->checkTable();
            switch($op)
            {
                case 'find':
                    $a = explode("=",$args);
                    $this->exists = true;
                    $this->load($a[0], $a[1]);
                    break;
                case 'controler':
                    $this->controler = true;
                    break;
            }
        }

        public final function set($key, $value)
        {
            if($this->controler == true)
                return;
            $this->fields[$key]->setValue($value);
        }
        public final function get($key, $real=false)
        {
            if($this->controler == true)
                return false;
            if($real)
                return $this->fields[$key]->getRealValue();
            else
                return $this->fields[$key]->getValue();
        }

        public final function save()
        {
            if($this->controler == true)
                return;
            if(!$this->exists)
            {
                $query = "INSERT INTO ".explode("\\",static::class)[1]."(";
                $first = true;
                foreach($this->keys as $key => $value)
                {
                    if($first == false)
                        $query = $query.",";
                    else
                        $first = false;
                    $query = $query.$key;
                }
                $query = $query.") VALUES(";
                $first = true;
                foreach($this->fields as $key => $value)
                {
                    if($key == 'ID')
                        continue;
                    if($first == false)
                        $query = $query.",";
                    else
                        $first = false;
                    $query = $query."'".$value->getValue()."'";
                }
                $query = $query.");";
                $this->execute($query);
                $this->set('ID', intval($this->db->lastInsertID()));
            }
            else
            {
                $query = "UPDATE ".explode("\\",static::class)[1]." SET ";
                $first = true;
                foreach($this->keys as $key => $value)
                {
                    if($first == false)
                        $query = $query.",";
                    else
                        $first = false;
                    $query = $query.$key."='".$this->fields[$key]->getValue()."'";
                }
                $query = $query." WHERE ID=".$this->fields['ID']->getValue().";";
                $this->execute($query);

            }
        }

        public final function findRelatives($type)
        {
            $test = new $type('controler');
            $fields = [];
            foreach($test->getFields() as $id => $field)
            {
                if($field instanceof LinkField)
                {
                    $t = $field->getType();
                    if($this instanceof $t)
                        $fields[] = $id;
                }
            }
            $ret = [];
            foreach($fields as $col)
            {
                $ret = array_merge($ret, $test->query($col, $this->fields['ID']->getValue()));
            }

            return $ret;

        }

        public final function delete()
        {
            if($this->controler == true)
                return;

            $query = "DELETE FROM ".explode("\\",static::class)[1]." WHERE ID='".$this->fields['ID']->getValue()."';";
            $this->execute($query);
        }

        public final function query($col, $val)
        {
            if($this->controler != true)
                return false;

            $query = "SELECT * FROM ".explode("\\",static::class)[1]." WHERE ".$col." LIKE '".$val."';";
            $temp = $this->execute($query, false);
            $ret = [];
            foreach($temp as $row)
            {
                $cn = static::class;
                $tempObj = new $cn('find', 'ID='.$row['ID']);
                $ret[] = $tempObj;
            }
            return $ret;
        }

        public final function queryAll()
        {
            if($this->controler != true)
                return false;

            $query = "SELECT * FROM ".explode("\\",static::class)[1].";";
            $temp = $this->execute($query, false);
            $ret = [];
            foreach($temp as $row)
            {
                $cn = static::class;
                $tempObj = new $cn('find', 'ID='.$row['ID']);
                $ret[] = $tempObj;
            }
            return $ret;
        }

        protected final function checkTable()
        {
            $query = "CREATE TABLE IF NOT EXISTS ".explode("\\",static::class)[1]." (\nID INTEGER PRIMARY KEY AUTOINCREMENT,\n";
            $first = true;
            foreach($this->keys as $key => $value)
            {
                if($first == false)
                {
                    $query = $query.",\n";
                }
                else
                    $first = false;

                $sql = $this->fields[$key]->getSQLDefinition();
                $query = $query." ".$key." ".$sql;
            }
            $query = $query."\n);";
            $this->execute($query);

            $accCols = $this->getColumns();
            foreach ($accCols as $column)
            {
                $key = $column['name'];
                if(!isset($this->fields[$key]))
                { // This should not exist anymore
                    $this->db->beginTransaction();
                    try
                    {
                        print("Dropping all unused columns\n");
                        $query = "CREATE TABLE IF NOT EXISTS ".explode("\\",static::class)[1]."___BU___ (\nID INTEGER PRIMARY KEY AUTOINCREMENT,\n";
                        $first = true;
                        foreach($this->keys as $key => $value)
                        {
                            if($first == false)
                            {
                                $query = $query.",\n";
                            }
                            else
                                $first = false;

                            $sql = $this->fields[$key]->getSQLDefinition();
                            $query = $query." ".$key." ".$sql;
                        }
                        $query = $query."\n);";
                        $this->execute($query);
                        $query = "INSERT INTO ".explode("\\",static::class)[1]."___BU___ SELECT ID";
                        foreach($this->keys as $key => $value)
                        {
                            if($first == false)
                            {
                                $query = $query.",";
                            }
                            else
                                $first = false;
                            $query = $query."".$key;
                        }
                        $query = $query." FROM ".explode("\\",static::class)[1].";";
                        $this->execute($query);

                        $query = "DROP TABLE ".explode("\\",static::class)[1].";";
                        $this->execute($query);

                        $query = "ALTER TABLE ".explode("\\",static::class)[1]."___BU___ RENAME TO ".explode("\\",static::class)[1].";";
                        $this->execute($query);
                        $this->db->commit();
                    }catch(Exception $e)
                    {
                        print("Dropping Columns failed: $e->message in $e->file : $e->line!\n");
                        print("REVERTING... ");
                        $this->db->rollBack();
                    }
                }
            }
            foreach($this->fields as $column => $field)
            {
                $found = false;
                foreach($accCols as $k)
                {
                    if($k['name'] == $column)
                    {
                        $found = true;
                        break;
                    }
                }
                if(!$found)
                {// We need to add column to table
                    $sql = "ALTER TABLE ".explode("\\",static::class)[1]." ADD ".$column." ".$field->getSQLDefinition().";";
                    print("Adding Column $column to table\n");
                    print($sql);
                    $this->execute($sql);
                }
            }

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
            if($this->controler == true)
                return;
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
