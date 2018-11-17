<?php
    include('checkServer.php');
    $session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    if(!empty($_SESSION['s253054_logged_in']) && $_SESSION['s253054_logged_in'] == true){
          header("Location: userHome.php");
          exit();
    }
    session_write_close();
    include('dbHandle.php');
       
    $conn= connectDB();
    if($conn== NULL){
        echo "Error Server - Try later";
    }
    else{
        $user= strtolower($_POST['email']);    
        $pass= $_POST['password'];
        $repass= $_POST['repassword'];
                           
        $user= strip_tags($user);
        $user= htmlentities($user);
        $user= stripslashes($user);
  
        $pass= strip_tags($pass);
        $pass= htmlentities($pass);
        $pass= stripslashes($pass); 
        
        $repass= strip_tags($repass);
        $repass= htmlentities($repass);
        $repass= stripslashes($repass);
        
        checkInputText($user, $pass, $repass);
        //echo $user." ".$pass." ".$repass;
        try{
            mysqli_autocommit($conn,false);
            $query= "SELECT Email FROM user FOR UPDATE";
            $res= queryDB($conn, $query);
            if($res==NULL){
                echo "Error Server - Try later";
                header('Location: signin.php');
                exit();
            }
            else{
                $row= mysqli_fetch_array($res);
                $found= 0;
                do{
                     if($row[0] == $user){
                        $found= 1;
                     }
                     $row= mysqli_fetch_array($res);
                }while($row!=NULL && $found!=1);
            
                if($found==1){
                    closeConnection($conn);
                    setcookie("errorSign","Already");
                    header('Location: signin.php');
                    exit();
                }
                else{
                    $query= mysqli_prepare($conn,"INSERT INTO user VALUES (?, ?, ?, ?, ?)");     
                    $pass= md5($pass);
                    $Reserved= 0;
                    $Src= NULL;
                    $Dst= NULL;
                    echo $user." ".$pass." ".$Src." ".$Dst." ".$Reserved;
                    mysqli_stmt_bind_param($query, 'ssssi', $user, $pass, $Src, $Dst, $Reserved);
                    
                    try{
                      mysqli_autocommit($conn, false);
                      if(!mysqli_stmt_execute($query))
                        throw new Exception("Error sign in");
                      mysqli_commit($conn);
                    }
                    catch(Exception $e){
                      mysqli_autocommit($conn, true);
                      echo "Rollback: ".$e->getMessage();
                      mysqli_rollback($conn);
                      setcookie("errorSign","ErrDB");
                      header('Location: signin.php');
                      exit();
                    }
                    mysqli_autocommit($conn, true);
                    mysqli_stmt_close($query);
                    closeConnection($conn);
                    unset($_COOKIE["errorSign"]);
                    $session_name= "s253054_user-session";
                    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
                    session_name($session_name);
                    session_start();
                    $_SESSION['s253054_email']= $user;
                    $_SESSION['s253054_logged_in']= true;
                    $_SESSION['s253054_timestamp'] = time();
                    session_write_close();
                    if ( !isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) !== "on" ) {
                        $redirect='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../userHome.php';
                        echo "redirect: ".$redirect;
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Location: ' . $redirect);
                        exit();
                    }
                    else{
                        header('Location: userHome.php');
                        exit();
                    }
                }   
            }
            mysqli_commit($conn);
        }
        catch(Exception $e){
            mysqli_rollback($conn);
            mysqli_autocommit($conn,true);
            echo "Rollback: ".$e->getMessage();
        }
        mysqli_autocommit($conn,true);
    }
?>