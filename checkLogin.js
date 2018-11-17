function checkLogin(){
    var user= document.getElementById("email").value;
    var password= document.getElementById("password").value;
    if(user=== "" || password=== ""){
        document.cookie= "status=false";
        alert("Missing credentials");
    }
    else
        document.cookie= "status=true";
}