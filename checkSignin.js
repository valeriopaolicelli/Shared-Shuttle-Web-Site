function checkSignin(){
    var foundLower= 0;
    var foundDigitOrUpper= 0;
    var i=0;
    var character='';
    var user= document.getElementById("email").value;
    var password= document.getElementById("password").value;
    var repassword= document.getElementById("repassword").value;
    var mess= "true";
    if(user=== "" || password=== "" || repassword=== ""){
        document.cookie= "status=Missing credentials";
        alert("Missing credentials");
    }
    else{
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var testEmail= re.test(String(user).toLowerCase());
        if(testEmail=== false){
            mess= "Wrong email";
        }
        
        while (i <= password.length){
            character = password.charAt(i);
            if (!isNaN(parseInt(character))){
                foundDigitOrUpper++;
            }else{
                if (character == character.toUpperCase()) {
                    foundDigitOrUpper++;
                }
                if (character == character.toLowerCase()){
                    foundLower++;
                }
            }
            i++;
        }
        foundLower--;
        foundDigitOrUpper--;
        if(foundDigitOrUpper>=1 && foundLower>=1){
            if(password!== repassword){
               if(mess!= "true")
                    mess= mess+", and password inserted are not equal!";
                else
                    mess= "Passwords inserted are not equal!";
            }
        }
        else{
            if(mess!= "true")
                    mess= mess+", and wrong password format (Rule: at least one lower-case character and, one upper-case character or digit!";
            else
                    mess= "Wrong password format (Rule: at least one lower-case character and, one upper-case character or digit!";
        }
        
        document.cookie= "status="+mess;
    }
}