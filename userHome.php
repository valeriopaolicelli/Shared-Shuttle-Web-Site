<!DOCTYPE html>
<?php
	$session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    if(empty($_SESSION['s253054_logged_in']) || $_SESSION['s253054_logged_in'] == false){
          header("Location: login.php");
          exit();
    }
    if(time() - $_SESSION['s253054_timestamp'] > 120) { //subtract new timestamp from the old one
        unset($_SESSION['s253054_email'], $_SESSION['s253054_password'], $_SESSION['s253054_timestamp']);
        $_SESSION['s253054_logged_in'] = false;
		$_COOKIE['errorLogin']= "false";
		$_COOKIE['status']= "true";
        session_destroy();
          $redirect='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../index.php';
          echo "redirect: ".$redirect;
          header('HTTP/1.1 301 Moved Permanently');
          header('Location: ' . $redirect);
          exit();
    } else {
        $_SESSION['s253054_timestamp'] = time(); //set new timestamp
    }
	if ( !isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) !== "on" ) {
		$redirect='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../userHome.php';
		echo "redirect: ".$redirect;
		header('HTTP/1.1 301 Moved Permanently');
		session_write_close();
		header('Location: ' . $redirect);
		exit();
	}
    setcookie('dir', 'userHome.php');
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
				<h1><a href='userHome.php' id='titleBar'><img src='images/bus.png' id= 'logo'>&nbsp&nbspFLIXSHUTTLE</a></h1>      
			</header>  
        <div id="container"> 
			<aside>
				<div id='cssmenu'>
						<ul>
							<li><a href='userHome.php'><span><img src='images/home.png'>&nbspHome </span></a></li>
							<li><a href='logoutHandle.php'><span><img src='images/logout.png'>&nbspLogout </span></a></li>
							<li><a href='reserve.php'><span><img src='images/ticket.png'>&nbspTickets </span></a></li>
							<li><a href='delete.php'><span><img src='images/delete.png'>&nbspDelete </span></a></li>
						</ul>
				</div>
			</aside>        
			<div style='margin-left: 150px; margin-top: 3em;'>
					<table>
					  <tr><td><h2>USER HOME</h2></td><td><noscript>&nbspSorry, but Javascript is required -> website may not work properly</noscript></td></tr>
					</table>
				<div>
				<?php include('userHandle.php'); ?>
				</div>
			</div>		
		</div>		
			<footer><img src='images/busReverse.png' id= 'logo'>&nbspExam of Distribuited Programming I, Politecnico di Torino 03/07/2018, Valerio Paolicelli 253054</footer>
	</body>
</html>