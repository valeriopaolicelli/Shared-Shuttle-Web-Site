<!DOCTYPE html>
<?php
	include('dbHandle.php');
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
		$redirect='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../reserve.php';
		echo "redirect: ".$redirect;
		header('HTTP/1.1 301 Moved Permanently');
		session_write_close();
		header('Location: ' . $redirect);
		exit();
	}
    setcookie('dir', 'reserve.php');
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
					<table>
						<ul>
							<li><a href='userHome.php'><span><img src='images/home.png'>&nbspHome </span></a></li>
							<li><a href='logoutHandle.php'><span><img src='images/logout.png'>&nbspLogout </span></a></li>
							<li><a href='reserve.php'><span><img src='images/ticket.png'>&nbspTickets </span></a></li>
							<li><a href='delete.php'><span><img src='images/delete.png'>&nbspDelete </span></a></li>
						</ul>
					</table>
				</div>
			</aside>    
			<div style='margin-left: 150px; margin-top: 3em;'>
					<table style='text-align:center;'>
					  <tr><td><h2>NEW TICKET</h2></td><td><noscript>&nbspSorry, but Javascript is required -> website may not work properly</noscript></td></tr>
					  <?php
						if(isset($_POST['reserve'])){                
							if($_COOKIE['status']== 'true'){
								$session_name= "s253054_user-session";
								session_name($session_name);      
								session_start();
								$_SESSION['s253054_from']= strtoupper($_POST['from']);
								$_SESSION['s253054_to']= strtoupper($_POST['to']);
								$_SESSION['s253054_seats']= $_POST['seats'];
								session_write_close();
								if ( !isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) !== "on" ) {
									$redirect='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../reserveHandle.php';
									echo "redirect: ".$redirect;
									header('HTTP/1.1 301 Moved Permanently');
									header('Location: ' . $redirect);
									exit();
								}
								else{
									header('Location: reserveHandle.php');
									exit();
								}
							}
							else{
								header('Location: reserve.php');
								exit();
							}
						}
					  ?>
					  <tr style='height: 30px'><td colspan="2"></td></tr>
					  <tr><td colspan="2" style='height: 50px;'><p>Select your stations from the already available, or insert new ones!</p></td></tr>
					  
					  <form name='reserve' action= '<?php $_SERVER['PHP_SELF'] ?>' onsubmit= 'checkStop();' method='post'>
						<tr><td style='height: 50px; text-align: center;'>
								<input type='text' name="from" id= 'from' value= "From:" onfocus='this.value="";' required autocomplete= 'off' onchange="setListSrc()">
							</td>
							<td style='height: 50px; text-align: center;'>
								<input type='text' name="to" id="to" value= 'To:' onfocus="this.value='';" required autocomplete= 'off' onchange="setListDest()">
							</td>
						</tr>
						<tr><td style='height: 50px;'>Departure:&nbsp <select style="width:auto;" id= 'listDep' onchange="setStationSrc()">				
							<?php
								$conn= connectDB();
								$query= "SELECT COUNT(DISTINCT BusStopId) FROM busstop";
								$query= mysqli_real_escape_string($conn, $query);
								$res= mysqli_query($conn,$query);
								if($res == FALSE){
									echo "<p>Error reading table of routes</p>";
								}
								$stations= mysqli_fetch_array($res);
								if($stations[0]==0){ 
									echo'<option value="" disabled>No stations yet inserted</option>';
								}
								else{
									echo'<option value="" disable>Select station</option>';
									$query= "SELECT BusStopId FROM busstop ORDER BY BusStopId";
									$query= mysqli_real_escape_string($conn, $query);
									$res= mysqli_query($conn,$query);
									if($res == FALSE){
										echo "<p>Error reading table of routes</p>";
									}
									else{
										$row= mysqli_fetch_array($res);
										do{
											echo'<option value="'.$row[0].'">'.$row[0].'</option>';  
											$row= mysqli_fetch_array($res);
										}while($row!=NULL);
										
										mysqli_close($conn);
									}   
									echo'<option value="" disabled>New Station</option>';
								}
							  ?>	
							</select></td>							  
							<td style='height: 50px;'>&nbsp&nbspDestination:&nbsp <select style="width:auto;" id= 'listDest' onchange="setStationDest()">
							<?php
								$conn = connectDB();
								$query= "SELECT COUNT(DISTINCT BusStopId) FROM busstop";
								$query= mysqli_real_escape_string($conn, $query);
								$res= mysqli_query($conn,$query);
								if($res == FALSE){
									echo "<p>Error reading table of routes</p>";
								}
								$stations= mysqli_fetch_array($res);
								if($stations[0]==0){
									echo'<option value="" disabled>No stations yet inserted</option>';
								}
								else{
									echo'<option value="">Select station</option>';
									$query= "SELECT BusStopId FROM busstop ORDER BY BusStopId";
									$query= mysqli_real_escape_string($conn, $query);
									$res= mysqli_query($conn,$query);
									if($res == FALSE){
										echo "<p>Error reading table of routes</p>";
									}
									else{
										$row= mysqli_fetch_array($res);
										do{
											echo'<option value="'.$row[0].'">'.$row[0].'</option>';   
											$row= mysqli_fetch_array($res);
										}while($row!=NULL);
										
										mysqli_close($conn);
									}   
									echo'<option value="" disabled>New Station</option>';
								}               
							?>
							</select></td></tr>
						<tr style='height: 50px'></tr>
						<tr><td colspan='2'>Number of passengers:&nbsp <select name= 'seats' id= 'seats' value= '1' style="width:50px;">
						<?php
							define('Capacity','4');
							for($i=0; $i<constant('Capacity'); $i++)
								echo'<option value="'.($i+1).'">'.($i+1).'</option>';
						?>
						</select></td></tr>
						<tr></tr>
						<tr style='height: 50px'></tr>
						<tr><td colspan="2" style= "text-align: center;"><input id= 'formSubmit' type="submit" value='Reserve' name='reserve'>&nbsp
						<input id= 'formButt' type="reset" value='Cancel' name='reset'></td></tr>
					  </form>
					  </table>
					  <script type="text/javascript" src="checkStop.js"></script>		
			</div>			
        </div>
			<footer><img src='images/busReverse.png' id= 'logoBackground'> Exam of Distribuited Programming I, Politecnico di Torino 03/07/2018, Valerio Paolicelli 253054</footer>
	</body>
</html>