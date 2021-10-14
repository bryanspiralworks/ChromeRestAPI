<?php
define('todo_db', 'todolist.csv');
/**
 * Class DummyDB
 * Author: Bryan Espino
 */

class DummyDB{
    
    public function todoTbl(){
        $todo = array_filter(explode( PHP_EOL, file_get_contents(todo_db)));
        $result = array_map('str_getcsv', $todo);
        $headers = $result[0];
    
        $json = [];
    
        foreach ($result as $row_index => $row_data) {
            if($row_index === 0) continue;
    
            foreach ($row_data as $col_idx => $col_val) {
                $label = $headers[$col_idx];
                $json[$row_index-1][$label] = $col_val;
            }
        }
    
        return json_encode($json, JSON_PRETTY_PRINT);
    }

    public function updateDB($updatedRecords){
        try{
            file_put_contents(todo_db,'id,todo,user'.PHP_EOL);
       
            foreach($updatedRecords as $value){
                $record = $value->id.','.$value->todo.','.$value->user;
                file_put_contents(todo_db,$record.PHP_EOL, FILE_APPEND);
            }
    
            return true;
        }catch(Exception $e){
            return false;
        }
       
      
    }

    public function insertRow($row){
        return file_put_contents(todo_db,$row.PHP_EOL, FILE_APPEND);
    }
}


?>