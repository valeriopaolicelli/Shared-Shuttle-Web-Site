<?php
    $session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    if(!empty($_SESSION['s253054_logged_in']) && $_SESSION['s253054_logged_in'] == true){
          header("Location: userHome.php");
          exit();
    }
    
    session_write_close();
    $_SESSION['s253054_timestamp']= time();
    include('dbHandle.php');
    $_SESSION['s253054_logged_in'] = true;
    $user= $_POST['email'];
    $pass= $_POST['password'];
    if(strcmp($user,"")==0 || strcmp($pass,"")==0){
        setcookie("status","false");
        header('Location: login.php');
        exit();
    }
    $user= strtolower($user);
    $found= 0;
    echo $user." ".$pass;
    $conn= connectDB();
    if($conn== NULL){
        echo "Error Server - Try later";
    }
    else{
        $user= strip_tags($user);
        $user= htmlentities($user);
        $user= stripslashes($user);
            
        $pass= strip_tags($pass);
        $pass= htmlentities($pass);
        $pass= stripslashes($pass);
            
        $pass= md5($pass);
             
        $query= "SELECT Email, Password FROM user";
    
        $res= queryDB($conn, $query);
        if($res==NULL){
            echo "Error Server - Try later";
        }
        else{
            $row= mysqli_fetch_array($res);
            do{
               if($row[0] == $user && $row[1]== $pass){
                  $found= 1;
               }
               $row= mysqli_fetch_array($res);
            }while($row!=NULL && $found!=1);
            closeConnection($conn);
            if($found==1){
                $session_name= "s253054_user-session";
                ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
                session_name($session_name);
                session_start();
                setcookie("errorLogin","false");
                $_SESSION['s253054_email']= $user;
                $_SESSION['s253054_timestamp'] = time();
                $_SESSION['s253054_logged_in']= true;
                if ( !isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) !== "on" ) {
                    $redirect='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../userHome.php';
                    echo "redirect: ".$redirect;
                    header('HTTP/1.1 301 Moved Permanently');
                    session_write_close();
                    header('Location: ' . $redirect);
                    exit();
                }
                else{
                    session_write_close();
                    header('Location: userHome.php');
                    exit();
                }
            }
            else{
                setcookie("errorLogin","true");
                header('Location: login.php');
                exit();
            }   
        }        
    }
    session_write_close();
?>