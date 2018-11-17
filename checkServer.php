<?php
function checkInputText($user, $pass, $repass){
        if(strcmp($user,"")==0 || strcmp($pass,"")== 0 || strcmp($repass,"")==0){
            setcookie("status","Missing credentials");
            header('Location: signin.php');
            exit();
        }
        
        if (!filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $mess = "Invalid email format";
        }
        else
            $mess= "true";
        
        $foundLower=0;
        $foundDigitOrUpper=0;
        $i=0;
        while ($i < strlen($pass)){
            $character = $pass[$i];
            if (is_numeric($character)){
                $foundDigitOrUpper++;
            }else{
                if ($character == strtoupper($character)) {
                    $foundDigitOrUpper++;
                }
                if ($character == strtolower($character)){
                    $foundLower++;
                }
            }
            $i++;
        }
        echo "<br>".$foundDigitOrUpper." ".$foundLower;
        
        if($foundDigitOrUpper>=1 && $foundLower>=1){
            if(strcmp($pass,$repass)!=0){
               if(strcmp($mess,"true")!=0)
                    $mess.=", and password inserted are not equal!";
                else
                    $mess= "Passwords inserted are not equal!";
            }
        }
        else{
            if(strcmp($mess,"true")!=0)
                    $mess.= ", and wrong password format (Rule: at least one lower-case character and, one upper-case character or digit!";
            else
                    $mess= "Wrong password format (Rule: at least one lower-case character and, one upper-case character or digit!";
        }
        
        setcookie("status","".$mess);
        if(strcmp($mess,"true")!=0){
            echo $mess;
            header('Location: signin.php');
            exit();
        }  

    }

?>