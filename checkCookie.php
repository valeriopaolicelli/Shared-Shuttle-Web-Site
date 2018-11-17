<?php
        session_start();
        if(!isset($_COOKIE['dir']))
                echo "Please, enable cookies to visit shuttle website!";
        else
                header('Location: '.$_COOKIE['dir']);
        exit();
        session_write_close();        
?>