<?php
    include('dbHandle.php');
	$session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    if(empty($_SESSION['s253054_logged_in']) || $_SESSION['s253054_logged_in'] == false){
          $redirect='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../index.php';
          echo "redirect: ".$redirect;
          header('HTTP/1.1 301 Moved Permanently');
          header('Location: ' . $redirect);
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
    setcookie('dir', 'userHandle.php');
    if(!isset($_COOKIE['dir'])){
        header('Location: checkCookie.php');
        exit();
    }
    echo "<br>Hello ".$_SESSION['s253054_email']."!<br><br>";
    
    $conn= connectDB();
    if($conn== NULL){
       echo "Error Server - Try later";
    }
    else{
           $user= $_SESSION['s253054_email'];
           $query= mysqli_prepare($conn,"SELECT NSeats FROM user WHERE Email=?");
            mysqli_stmt_bind_param($query, 's', $user);
            $res= mysqli_stmt_execute($query);
            if ( !mysqli_stmt_execute($query) ) {
                die( 'stmt error: '.mysqli_stmt_error($query) );
            }
            else{
                mysqli_stmt_bind_result($query, $seats);
                mysqli_stmt_fetch($query);
                mysqli_stmt_close($query);
                if(isset($_SESSION['s253054_result']) && $_SESSION['s253054_result']!=""){
                        echo "<p style= 'color: red'>".$_SESSION['s253054_result']."</p>";
                        $_SESSION['s253054_result']= "";
                }
                if($seats>0){
                    $query= mysqli_prepare($conn,"SELECT Src, Dest FROM user WHERE Email=?");
                    mysqli_stmt_bind_param($query, 's', $user);
                    if ( !mysqli_stmt_execute($query) ) {
                        die( 'stmt error: '.mysqli_stmt_error($query) );
                    }
                    mysqli_stmt_bind_result($query, $srcUser, $destUser);
                    mysqli_stmt_fetch($query);
                    mysqli_stmt_close($query);
					$departure= $srcUser;
					$destination= $destUser;
      
                    $query= mysqli_prepare($conn,"SELECT BusStopId, Starts
                                                  FROM busstop
                                                  ORDER BY BusStopId");
                    
                    if ( !mysqli_stmt_execute($query) ) {
                        die( 'stmt error: '.mysqli_stmt_error($query) );
                    }
                    mysqli_stmt_bind_result($query, $station, $start);
					$n=0;
                    mysqli_stmt_fetch($query);
                    
					$prev[0]= $station;
                    $prev[1]= $start;
                    
                    while(mysqli_stmt_fetch($query)){
                        $all_station[$n]= $station;
                        $all_start[$n]= $start;
                        $n++;
                    }
                    mysqli_stmt_close($query);
                    
                    echo "<br><table id='route'><tr><th id='route'><span>&nbspFrom&nbsp</span></th><th id='route'><span>&nbspTo&nbsp</span></th><th id='route'><span>&nbspReserved&nbsp</span></th><th id='route'><span>&nbspUsers&nbsp</span></th></tr>";
                    for($i=0; $i<$n; $i++){
                        $query= mysqli_prepare($conn,"SELECT Email, NSeats
                                                      FROM user
                                                      WHERE Src<=? AND Dest>=?
                                                      ORDER BY Src");
                        mysqli_stmt_bind_param($query, 'ss', $prev[0], $all_station[$i]);
                        if ( !mysqli_stmt_execute($query) ) {
                            die( 'stmt error: '.mysqli_stmt_error($query) );
                        }
                        mysqli_stmt_bind_result($query, $email, $seats);
                        $string="";
                        while(mysqli_stmt_fetch($query)){                            
                            if($seats==1)
                                $string.= $email." (".$seats." passenger) ";
                            else
                                $string.= $email." (".$seats." passengers) "; 
                        }
                        mysqli_stmt_close($query);
                        if($prev[1]!=0){
							
                            if(strcmp($prev[0],$departure)==0 && strcmp($all_station[$i],$destination)==0)
                                echo "<tr id='route'><td><span id= 'stationOfUser'>&nbsp".$prev[0]."&nbsp</span></td><td><span id= 'stationOfUser'>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbsp".$prev[1]."&nbsp</span></td><td><span>&nbsp".$string."&nbsp</span></td></tr>";
                            else if(strcmp($prev[0],$departure)==0)
                                echo "<tr id='route'><td><span id= 'stationOfUser'>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbsp".$prev[1]."&nbsp</span></td><td><span>&nbsp".$string."&nbsp</span></td></tr>";
                            else if (strcmp($all_station[$i],$destination)==0 && strcmp($prev[0],$destination)!=0)
                                echo "<tr id='route'><td id='route'><span>&nbsp".$prev[0]."&nbsp</span></td><td><span id= 'stationOfUser'>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbsp".$prev[1]."&nbsp</span></td><td><span>&nbsp".$string."&nbsp</span></td></tr>";
                            else
                                echo "<tr id='route'><td id='route'><span>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbsp".$prev[1]."&nbsp</span></td><td><span>&nbsp".$string."&nbsp</span></td></tr>";
                        }
                        else
                            echo "<tr id='route'><td><span>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbspShuttle free&nbsp</span></td><td><span>&nbspNo passengers&nbsp</span></td></tr>";
                        
                        $prev[0]= $all_station[$i];
                        $prev[1]= $all_start[$i];
                    }
                    echo "</table>";
                    
                }
                else{
                    $query= mysqli_prepare($conn,"SELECT BusStopId, Starts
                                                  FROM busstop
                                                  ORDER BY BusStopId");
                    
                    if ( !mysqli_stmt_execute($query) ) {
                        die( 'stmt error: '.mysqli_stmt_error($query) );
                    }
                    mysqli_stmt_bind_result($query, $station, $start);
                    $n=0;
                    mysqli_stmt_fetch($query);
                    $prev[0]= $station;
                    $prev[1]= $start;
                    if($station==""){
                      echo "<p style='text-align: left'>Empty Shuttle<img src='images/busReverse.png' id= 'logoBackground'></p>";
                    }
                    else{
                      while(mysqli_stmt_fetch($query)){
                        $all_station[$n]= $station;
                        $all_start[$n]= $start;
                        $n++;
                      }
                      mysqli_stmt_close($query);
                      
                      echo "<br><table id='route'><tr><th id='route'>&nbspFrom&nbsp</th><th id='route'>&nbspTo&nbsp</th><th id='route'>&nbspReserved&nbsp</th><th id='route'>&nbspUsers&nbsp</th></tr>";
                      for($i=0; $i<$n; $i++){
                          $query= mysqli_prepare($conn,"SELECT Email, NSeats
                                                        FROM user
                                                        WHERE Src<=? AND Dest>=?
                                                        ORDER BY Src");
                          mysqli_stmt_bind_param($query, 'ss', $prev[0], $all_station[$i]);
                          if ( !mysqli_stmt_execute($query) ) {
                              die( 'stmt error: '.mysqli_stmt_error($query) );
                          }
                          mysqli_stmt_bind_result($query, $email, $seats);
                          $string="";
                          while(mysqli_stmt_fetch($query)){
                              
                              if($seats==1)
                                  $string.= $email." (".$seats." passenger) ";
                              else
                                  $string.= $email." (".$seats." passengers) "; 
                          }
                          mysqli_stmt_close($query);
                          
                          if($prev[1]!=0)
                              echo "<tr id='route'><td><span>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbsp".$prev[1]."&nbsp</span></td><td><span>&nbsp".$string."&nbsp</span></tr>";
                          else
                              echo "<tr id='route'><td><span>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$all_station[$i]."&nbsp</span></td><td><span>&nbspShuttle free&nbsp</span></td><td><span>&nbspNo passengers</span></td></tr>";
                              
                          $prev[0]= $all_station[$i];
                          $prev[1]= $all_start[$i];
                      }
                      echo "</table>"; 
                    }
                }
            }
             closeConnection($conn);
        }
    session_write_close();
?>