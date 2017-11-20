<?php

define('DATABASE', 'dvg9');
define('USERNAME', 'dvg9');
define('PASSWORD', 'yVij2cGe');
define('CONNECTION', 'sql2.njit.edu');

class Manage {
    public static function autoload($class) {
        //you can put any file name or directory here
        include $class . '.php';
    }
}
spl_autoload_register(array('Manage', 'autoload'));

$obj=new main();

class dbConn{
    //variable to hold connection object.
    protected static $db;
    //private construct - class cannot be instatiated externally.
    private function __construct() {
        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
    }
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        //
        //return connection.
        return self::$db;
    }
}



abstract class collection {

protected $html;

    static public function create() {
      $model = new static::$modelName;
      return $model;
    }
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id =' . $id;
        $statement = $db->prepare($sql);
        $statement->execute();
        $class = static::$modelName;
        $statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $recordsSet =  $statement->fetchAll();

        //print_r($recordsSet);
        return $recordsSet[0];
    }
}

class accounts extends collection {
    protected static $modelName = 'account';
}

class todos extends collection {
    protected static $modelName = 'todo';
}



abstract class model {

protected $tableName;

public function save()
    {
        if ($this->id != '') {
            $sql = $this->update();
        } else {
           $sql = $this->insert();
        }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $array = get_object_vars($this);
        foreach (array_flip($array) as $key=>$value){
            $statement->bindParam(":$value", $this->$value);
        }
        $statement->execute();
        $id = $db->lastInsertId();
        return $id;
    }
private function insert() 
    {      
        $modelName=get_called_class();
        $tableName = $modelName::getTablename();
        $arr = get_object_vars($this);
        $columnString = implode(',', array_flip($array));
        $valueString = ':'.implode(',:', array_flip($array));
        //print_r($columnString);
        $sql =  'INSERT INTO '.$tableName.' ('.$columnString.') VALUES ('.$valueString.')';
        return $sql;
    }
private function update() 
    {  
    $modelName=get_called_class();
    $tableName = $modelName::getTablename();
    $array = get_object_vars($this);
    $comma = " ";
    $sql = 'UPDATE '.$tableName.' SET ';
    foreach ($array as $key=>$value)
    {
        if( ! empty($value)) {
            $sql .= $comma . $key . ' = "'. $value .'"';
            $comma = ", ";
            }
        }
        $sql .= ' WHERE id='.$this->id;
    return $sql;
    }
    
public function delete() 
    {
    //echo"In delete";
    $db = dbConn::getConnection();
    $modelName=get_called_class();
    $tableName = $modelName::getTablename();
    $sql = 'DELETE FROM '.$tableName.' WHERE id ='.$this->id;
    $statement = $db->prepare($sql);
    //print_r($sql);
    $statement->execute();
    }
}


class account extends model {
    public $id;
    public $email;
    public $fname;
    public $lname;
    public $phone;
    public $birthday;
    public $gender;
    public $password;
    public static function getTablename(){
        $tableName='accounts';
        return $tableName;
    }
}


class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public static function getTablename(){
        $tableName='todos';
        return $tableName;
    }
} 


class main
{
   public function __construct()
   {


    $form = '<form method="post" enctype="multipart/form-data">';
    $records = accounts::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $form .=$html; 


    $id = 4;
    $records = accounts::findOne($id);
    $html = displayHtml::tableDisplayFunction_1($records);
    $form .="<h3>Record fetched with id - <i>".$id."</i></h3>";
    $form .= $html;


    $record = new account();
    $record->email="test@njit.edu";
    $record->fname="hh";
    $record->lname="hhhh";
    $record->phone="66697";
    $record->birthday="06-09-1992";
    $record->gender="male";
    $record->password="123@#45hn";
    $lstId=$record->save();
    $records = accounts::findAll();
    //print_r($lstId);
    $form .="<h3>Record inserted with id - <i>".$lstId."</i></h3>";
    $html = displayHtml::tableDisplayFunction($records);
     $form .=$html;


    //$id=30;
    $records = accounts::findOne($lstId);
    $record = new account();
    $record->id=$records->id;
    $record->email="email_Update@njit.edu";
    $record->fname="fname_Update";
    $record->lname="lname_Update";
    $record->gender="gender_Update";
    $record->save();
    $records = accounts::findAll();
    $form .="<h3>Updateing record having id: <i>".$lstId."</i></h3>";
    $html = displayHtml::tableDisplayFunction($records);
    $form .=$html;


    $records = accounts::findOne($lstId);
    $record= new account();
    $record->id=$records->id;
    //print_r($records);
    $records->delete();
    $form .='<h3>Record with id: <i>'.$records->id.'</i> is deleted</h3>';
    $records = accounts::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $form .=$html;
    //print_r($form);
 
    //$form .= '<br><hr></br>';
    $form .= '<h1> 2) Todos Table</h1>';
    $records = todos::findAll();
    $html = displayHtml::tableDisplayFunction($records); 
    $form .= $html;


    $id = 7;
    $records = todos::findOne($id);
    $html = displayHtml::tableDisplayFunction_1($records);
    $form .='<h2>B) Select One Record</h2>';
    $form .='<h3> Record fetched with id: <i>'.$id.'</i></h3>';
    $form .=$html;


    $record = new todo();
    $record->owneremail="dvg9@njit.edu";
    $record->ownerid=06;
    $record->createddate="06-01-2017";
    $record->duedate="07-13-2017";
    $record->message="PDO AR";
    $record->isdone=1;
    $lstId=$record->save();
    $records = todos::findAll();
    //echo"<h3>After Inserting</h3>";
    $form .="<h3>Record inserted with id - <i>".$lstId."</i></h3>";
    $html = displayHtml::tableDisplayFunction($records);
    $form .='<h3>After inserting the record - </h3>';
    $form .= $html;


    //$id=41;
    $records = todos::findOne($lstId);
    $record = new todo();
    $record->id=$records->id;
    $record->owneremail="test@njit.edu";
    $record->message=" Active record update ";
    $record->save();
    $records = todos::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $form .=$html;



    $records = todos::findOne($lstId);
    $record= new todo();
    $record->id=$records->id;
    //print_r($records);
    $records->delete();
    //echo "After Delete";
    $records = todos::findAll();
    $html = displayHtml::tableDisplayFunction($records);
    $form .="<h3>After Deleteing</h3>";
    $form .=$html;
    print_r($form);
    }
}
