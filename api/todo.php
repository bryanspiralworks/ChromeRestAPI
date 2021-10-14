<?php 
require 'database.php';
define("users", [
    "bongbong" => 'password',
    "leni" => 'password',
    "rastaman" => 'password'
]);

$auth_username = $_SERVER['PHP_AUTH_USER'] ?? '';
$auth_password = $_SERVER['PHP_AUTH_PW'] ?? '';

$todoController = new TodoController( new DummyDB());
$todoController->handle();



/**
 * Class TodoController
 * Author: Bryan Espino
 */

class TodoController {

     /**
     * @var DummyDB
     */
    private $dbConnect;
    

    function __construct( $dbConnect){
        $this->dbConnect = $dbConnect;
    }

    public function handle(){
        if($this->checkAuthentication($auth_username, $auth_password)){
            switch ($_SERVER['REQUEST_METHOD']){
                case 'POST':
                    $this->createTODO();
                    break;
                case 'GET':
                    $user = $_GET['user'] ?? null;
                    if($user){
                        $this->getTODOByUser($user);
                    }else{
                        $this->getAllTODO();
                    }
                    break;
                case 'PATCH':
                    $id = $_GET['id'] ?? null;
                    $this->updateTODO($id);
                    break;
                case 'DELETE':
                    $id = $_GET['id'] ?? null;
                    $this->deleteTODO($id);
                    break;
        
                default:
                    break;
            }
        }else{
            echo json_encode(array( 'statusCode'=>'401', 'message' => 'Unauthorized'));
        }
    }


    public function checkAuthentication($username, $password){
        return users[$username] === $password;
    }
    
    public function createTODO(){
        $user = $_POST['user'] ?? null;
        $todo = $_POST['todo'] ?? null;
    
        if($user && $todo){
            if($this->insertTODO($user, $todo)){
                echo json_encode(array( 'message' => 'TODO has been added successfully.'));
                return true;
            }
    
            echo json_encode(array( 'statusCode'=>'409', 'message' => 'Failed to add todo in the database'));
    
        }
    
        echo json_encode(array( 'statusCode'=>'422', 'message' => 'Incomplete parameters todo and user is required'));
    
    }
    
    public function insertTODO($user, $todo){
        $todoObj =  bin2hex(random_bytes(5)).','.$todo.','.$user;
        return $this->dbConnect->insertRow($todoObj);
    }
    
    public function getAllTODO(){
      
       echo $this->dbConnect->todoTbl();
    }
    
    public function getTODOByUser($user){
      
       $todoTbl = json_decode($this->dbConnect->todoTbl());
       $userTodo = [];
      
        foreach($todoTbl as $key => $value){
           if($value->user === $user){
             array_push($userTodo,$value);
           }
        }
        echo json_encode($userTodo);
    
    }
    
    public function updateTODO($id){
    
        parse_str(file_get_contents('php://input'), $_PATCH);
    
        $params=[];
        if (is_array($_PATCH)) {
            foreach ($_PATCH as $key => $value) {
                $params[$key] = $value;
            } 
        }
        
        $todoTbl = json_decode($this->dbConnect->todoTbl());
        $updated = false;
    
        foreach($todoTbl as $key => $value){
          
            if($value->id === $id){
             
                try{
                    $todoTbl[$key]->todo = $params['todo'] ?? $value->todo;
                    $todoTbl[$key]->user = $params['user'] ?? $value->user;
                    $this->dbConnect->updateDB($todoTbl);
                    $updated = true;
                    
                }catch(Exception $e){
                    echo json_encode(array( 'statusCode'=>'400', 'message' => 'Failed to update todo in the database'));
                }
               break;
            }
         }
         
         if($updated){
            echo json_encode(array( 'message' => 'TODO has been updated successfully.'));
            return true;
         }
    
         echo json_encode(array( 'statusCode'=> '404', message => 'Resource does not exist'));
         return false;
    }
    
    public function deleteTODO($id){
    
        $todoTbl = json_decode($this->dbConnect->todoTbl());
        $deleted = false;
        foreach($todoTbl as $key => $value){
          
            if($value->id === $id){
             
                try{
                    array_splice($todoTbl, $key, 1);
                    $this->dbConnect->updateDB($todoTbl);
                    $deleted = true;
                }catch(Exception $e){
                    echo json_encode(array( 'statusCode'=>'424', 'message' => 'Failed to delete todo in the database'));
                }
               break;
            }
         }
         
         if($deleted){
            echo json_encode(array( 'message' => 'TODO has been deleted successfully.'));
            return true;
         }
    
         echo json_encode(array( 'statusCode'=> '404', message => 'Resource does not exist'));
         return false;
    }
    

}

?>