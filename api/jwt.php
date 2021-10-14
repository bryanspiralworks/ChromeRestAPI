<?php
define("users", [
  "bongbong" => ['name' => 'Bong Bong Marcos', 'password'=> 'password'],
  "leni" => ['name' => 'Leni Robredo', 'password'=> 'password'],
  "rastaman" => ['name' => 'Rastaman', 'password'=> 'password']
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $username = $_POST["username"] ?? '';
   $password = $_POST["password"] ?? '';
   $result = checkUser($username, $password); 
    if($result){
        $token = generateToken($result);
        echo json_encode(array( 'token' => $token));
        return true;
    }

    echo json_encode(array( 'statusCode'=>'401', 'message' => 'Invalid credentials'));


}else{
    echo json_encode(array( 'statusCode'=>'405', 'message' => 'HTTP Method not allowed'));
}


function checkUser($username, $password){
  
   if(users[$username]['password'] === $password){
       return json_encode(array(
            'sessionID' => bin2hex(random_bytes(5)),
            'userData' => users[$username]
       ));
   }

   return false;
}

function generateToken($data){

    return base64_encode($data);
}
?>