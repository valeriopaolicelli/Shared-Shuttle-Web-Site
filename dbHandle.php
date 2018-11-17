<?php
    function connectDB(){
        $conn = mysqli_connect('localhost', 'root', '', 'assignment');
        if (!$conn) {
            echo 'Connect error ('. mysqli_connect_errno() . ') '. mysqli_connect_error();
            return NULL;            
        }
        //mysqli_select_db($conn,"assignment");
        return $conn;
    }
    
    function queryDB($conn, $query){
        $res= mysqli_query($conn,$query);
        if($res == FALSE){
            echo "<p>Error reading table of routes</p>";
            return NULL;
        }
        else{
            return $res;
        }
    }
    
    function closeConnection($conn){
        mysqli_close($conn);
    }
?>