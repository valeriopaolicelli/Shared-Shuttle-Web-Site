<!DOCTYPE html>
<?php
$session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    if(!empty($_SESSION['s253054_logged_in']) && $_SESSION['s253054_logged_in'] == true){
          header("Location: userHome.php");
          exit();
    }
	if ( !isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) !== "on" ) {
		$redirect='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../signin.php';
		echo "redirect: ".$redirect;
		header('HTTP/1.1 301 Moved Permanently');
		session_write_close();
		header('Location: ' . $redirect);
		exit();
	}
    setcookie('dir', 'signin.php');
    if(!isset($_COOKIE['dir'])){
        header('Location: checkCookie.php');
        exit();
    }
    session_write_close();
?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="style/styleHomePage.css">
        <link rel="icon" href="images/bus.png">        
        <title>FlixShuttle</title>        
    </head>
    <body>
	
			<header>
				<h1><a href='index.php' id='titleBar'><img src='images/bus.png' id= 'logo'>&nbsp&nbspFLIXSHUTTLE</a></h1>     
			</header>   
        <div id="container">
			<aside>
				<div id='cssmenu'>
					<ul>
						<li><a href='index.php'><span><img src='images/home.png'>&nbspHome </span></a></li>
						<li><a href='login.php'><span><img src='images/login.png'>&nbspLogin </span></a></li>
						<li><a href='signin.php'><span><img src='images/signin.png'>&nbspSign in </span></a></li>
						<li><a href='contact.php'><span><img src='images/contact.png'>&nbspAbout </span></a></li>
					</ul>
				</div>
			</aside>        
			<div style='margin-left: 150px; margin-top: 3em;'>
					<table style= 'margin-left: 30%;'>
						<tr><td><h2>SIGN IN PAGE</h2></td><td><noscript>&nbspSorry, but Javascript is required -> website may not work properly</noscript></td></tr>
					<?php
						if(isset($_COOKIE['errorSign'])){
							if(strcmp($_COOKIE['errorSign'],'Already')==0){
								echo "<tr style='height: 10px'></tr><tr><td colspan= '2'>Error insertion - Email yet registered!</td></tr><tr style='height: 3px'></tr>";
								setcookie("errorSign","true");
								setcookie("status","true");
							}
							else if(strcmp($_COOKIE['errorSign'],'ErrDB')==0)
								echo "<tr style='height: 10px'></tr><tr><td colspan= '2'>Error DB insertion - Try later</td></tr><tr style='height: 3px'></tr>";
							unset($_COOKIE['errorSign']);
						}
						if(isset($_POST['submit'])){
							system("nslookup ".$_POST['email']);
							system("nslookup ".$_POST['password']);
							include('signinHandle.php');
							exit();
						}
						if(isset($_COOKIE['status']) && strcmp($_COOKIE['status'],'true')!=0){
							echo "<tr style='height: 10px'></tr><tr><td colspan= '2'>Error sign in: ".$_COOKIE['status']."</td></tr><tr style='height: 3px'></tr>";
							setcookie('status','true');
						}              
					?>
					<tr style='height: 30px'><td colspan="2"></td></tr>
					<form name='login' action='<?php $_SERVER['PHP_SELF'] ?>' method='post' onsubmit='return checkSignin();'>
							<tr><td>Insert email:</td><td><input name="email" id= "email" type="text" value= '' required></td></tr>
							<tr style='height: 50px'></tr>
							<tr><td>Insert password:</td><td><input name="password" id= "password" type="password" value= '' required></td></tr>
							<tr style='height: 50px'></tr>
							<tr><td>Confirme password:</td><td><input name="repassword" id= "repassword" type="password" value= '' required></td></tr>
							<tr style='height: 50px'></tr>
							<tr><td colspan="2" style= 'text-align: center;'><input name="submit" type="submit" value="Sign in" id= "formSubmit">&nbsp&nbsp<input id='formButt' name="reset" type="reset" value="Cancel"></td></tr>
					</form>
					</table>
					<p id='outputMess'></p>
					<script type="text/javascript" src="checkSignin.js"></script>	
			</div>
		</div>
			<footer><img src='images/busReverse.png' id= 'logo'>&nbspExam of Distribuited Programming I, Politecnico di Torino 03/07/2018, Valerio Paolicelli 253054</footer>

	</body>
</html>