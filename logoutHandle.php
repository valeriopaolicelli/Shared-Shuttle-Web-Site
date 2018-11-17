<?php
    $session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    
    if(empty($_SESSION['s253054_logged_in']) || $_SESSION['s253054_logged_in'] == false){
          header("Location: login.php");
          exit();
    }
    $_SESSION['s253054_logged_in']= false;
    session_destroy();
         // outside from session, there should be http (no private information to protect).
        $redirect='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../index.php';
        echo "redirect: ".$redirect;
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
?>