<!DOCTYPE html>
<?php
	if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
		$redirect='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../contact.php';
		echo "redirect: ".$redirect;
		header('HTTP/1.1 301 Moved Permanently');
		session_write_close();
		header('Location: ' . $redirect);
		exit();
	}
	$session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    setcookie('dir', 'contact.php');
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
					<table>
						<tr><td><h2>ABOUT</h2></td><td><noscript>&nbspSorry, but Javascript is required -> website may not work properly</noscript></td></tr>
					</table>
				<div>
					<?php include('contactHandle.php'); ?>
				</div>			
			</div>		
		</div>
			<footer><img src='images/busReverse.png' id= 'logo'>&nbspExam of Distribuited Programming I, Politecnico di Torino 03/07/2018, Valerio Paolicelli 253054</footer>
	</body>
</html>