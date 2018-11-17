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
  
  if(!isset($_COOKIE['dir'])){
      header('Location: checkCookie.php');
      exit();
  }
  define("Capacity","4");
  $user= $_SESSION['s253054_email'];
  $src= $_SESSION['s253054_from'];
  $dest= $_SESSION['s253054_to'];
  $seats= $_SESSION['s253054_seats'];
  
  $alreadyReserved= 0;
  $reservationUser= 0;
  $seatsReserved= 0;
  $conn= connectDB();
  if($conn== NULL){
      echo "Error Server - Try later";
  }
  else{
      $user= strip_tags($user);
      $user= htmlentities($user);
      $user= stripslashes($user);
      $user= mysqli_real_escape_string($conn, $user);
          
      $src= strip_tags($src);
      $src= htmlentities($src);
      $src= stripslashes($src);
          
      $dest= strip_tags($dest);
      $dest= htmlentities($dest);
      $dest= stripslashes($dest);
      
      $seats= strip_tags($seats);
      $seats= htmlentities($seats);
      $seats= stripslashes($seats);
      $seats= mysqli_real_escape_string($conn, $seats);
      echo "<br>Start: ".$user." ".$src." ".$dest." ".$seats;
      //exit();
      if(strcmp($src,$dest)>=0 || $seats<=0 || strcmp($src,'From:')==0 || strcmp($dest,'To:')==0){
          setcookie("status","false");
          header('Location: reserve.php');
          exit();
      }
      //exit();
      try{
          mysqli_autocommit($conn,false);
          $query= mysqli_prepare($conn,"SELECT * FROM busstop b FOR UPDATE");
          mysqli_stmt_bind_param($query, 's', $user);
          $res= mysqli_stmt_execute($query);
          if ( !mysqli_stmt_execute($query) ) {
              die( 'stmt error: '.mysqli_stmt_error($query) );
          }
          mysqli_stmt_close($query);
          
          $query= mysqli_prepare($conn,"SELECT NSeats FROM user WHERE Email=?");
          mysqli_stmt_bind_param($query, 's', $user);
          $res= mysqli_stmt_execute($query);
          if ( !mysqli_stmt_execute($query) ) {
              die( 'stmt error: '.mysqli_stmt_error($query) );
          }
          else{
              mysqli_stmt_bind_result($query, $reservationUser);
              mysqli_stmt_fetch($query);
              mysqli_stmt_close($query);
              echo("Reservation user: ".$reservationUser);
              if($reservationUser>0){/******* user has already a reservation *********/
                  $_SESSION['s253054_result']= "You have already a reservation";
				  throw new Exception('You have already a reservation');
              }
              else{
                  $query= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId=?");
                  mysqli_stmt_bind_param($query, 's', $src);
                  if ( !mysqli_stmt_execute($query) ) {
                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                  }
                  mysqli_stmt_bind_result($query, $alreadySource);
                  mysqli_stmt_fetch($query);
                  mysqli_stmt_close($query);
                  echo("<br>Already source: ".$alreadySource);
                  
                  $query= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId=?");
                  mysqli_stmt_bind_param($query, 's', $dest);
                  if ( !mysqli_stmt_execute($query) ) {
                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                  }
                  mysqli_stmt_bind_result($query, $alreadyDest);
                  mysqli_stmt_fetch($query);
                  mysqli_stmt_close($query);
                  echo("<br>Already dest: ".$alreadyDest);
                  
                  $alreadyReserved= $alreadySource+$alreadyDest;
                  if($alreadyReserved==2){/********* existent route **********/
                              $query= mysqli_prepare($conn,"SELECT BusStopId, Arrived, Starts FROM busstop WHERE BusStopId>=? AND BusStopId<=? ORDER BY BusStopId");
                              mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                              if ( !mysqli_stmt_execute($query) ) {
                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                              }
                              mysqli_stmt_bind_result($query, $bid, $seatsArrived, $seatsReserved);
                              echo("<br>Existent route******Seats Reserved: ".$seatsReserved);
                              $available= 1;
                              while(mysqli_stmt_fetch($query)){
								  if(strcmp($bid,$src)==0){
									$tot= $seats+$seatsReserved;
									echo "<br>Existent route******Total: ".$tot.", Capacity: ".constant("Capacity");
									if($seats+$seatsReserved>constant("Capacity")){
									  $_SESSION['s253054_result']= "Shuttle already full in that route";
                                      throw new Exception("<br>Existent route******full");
                                      $available= 0;
									}  
								  }
								  else if(strcmp($bid,$dest)==0){
									$tot= $seats+$seatsArrived;
									echo "<br>Existent route******Total: ".$tot.", Capacity: ".constant("Capacity");
									if($seats+$seatsArrived>constant("Capacity")){
									  $_SESSION['s253054_result']= "Shuttle already full in that route";
                                      throw new Exception("<br>Existent route******full");
                                      $available= 0;
									}								  
								  }
								  else{
									if(($seats+$seatsArrived>constant("Capacity"))||($seats+$seatsReserved>constant("Capacity"))){
									  $_SESSION['s253054_result']= "Shuttle already full in that route";
                                      throw new Exception("<br>Existent route******full");
                                      $available= 0;
									}
								  }
                              }
                              mysqli_stmt_close($query);
                              
                              if($available== 1) {
                                      $query= mysqli_prepare($conn,"UPDATE user SET NSeats= ?, Src= ?, Dest= ? WHERE Email= ?");
                                      echo "<br>Existent route******Update seats= ".$seats.", from: ".$src.", to: ".$dest.", user= ".$user;
                                      mysqli_stmt_bind_param($query, 'isss', $seats, $src, $dest, $user);
                                      if ( !mysqli_stmt_execute($query) ) {
                                              throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                      }
                                      mysqli_stmt_close($query);
                                      
                                       $query= mysqli_prepare($conn,"SELECT BusStopId, Starts FROM busstop WHERE BusStopId>=? AND BusStopId<=?");
                                      mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                                      if ( !mysqli_stmt_execute($query) ) {
                                          throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                      }
                                      mysqli_stmt_bind_result($query, $busId, $seatsReserved);
                                      $n= 0;
                                      while(mysqli_stmt_fetch($query)){
                                          $stationId[$n]= $busId;
                                          $seatsRetrieved[$n]= $seatsReserved;
                                          $n++;
                                      }
                                      mysqli_stmt_close($query);
                                      
                                      for($i=0; $i<$n; $i++){
                                          echo "<br>Existent route******Bus Stop: ".$stationId[$i].", hold seats: ".$seatsReserved;
                                          $tot= $seats+$seatsRetrieved[$i];
                                          if($stationId[$i]==$src){
                                              echo "<br>Station: ".$stationId[$i].", Starts: ".$tot;
                                              $stmt= mysqli_prepare($conn,"UPDATE busstop SET Starts=? WHERE BusStopId=?");
                                              mysqli_stmt_bind_param($stmt,'is',$tot,$stationId[$i]);
                                              if ( !mysqli_stmt_execute($stmt) ) {
                                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($stmt) );
                                              }
                                              mysqli_stmt_close($stmt);
                                          }
                                          else if($stationId[$i]==$dest){
                                              echo "<br>Existent route******Station: ".$stationId[$i].", Arrived: ".$seats;
                                              $query1= mysqli_prepare($conn,"UPDATE busstop SET Arrived= Arrived+? WHERE BusStopId=?");
                                              mysqli_stmt_bind_param($query1, 'is', $seats, $dest);
                                              if ( !mysqli_stmt_execute($query1) ) {
                                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query1) );
                                              }
                                              mysqli_stmt_close($query1);
                                          }
                                          else{
                                              echo "<br>Existent route******Station: ".$busId.", new Arrived/Starts: ".$seats;
                                              $query1= mysqli_prepare($conn,"UPDATE busstop SET Arrived= Arrived+?, Starts= Starts+? WHERE BusStopId= ?");
                                              mysqli_stmt_bind_param($query1, 'iis', $seats, $seats, $stationId[$i]);
                                              if ( !mysqli_stmt_execute($query1) ) {
                                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query1) );
                                              }
                                              mysqli_stmt_close($query1);
                                          }
                                      }
                                      echo "<br>Existent route******Booked with yet existing stations";
                                      $_SESSION['s253054_result']= "Booked";                
                              }
                              else{
                                      echo "<br>Existent route******Shuttle already full in that route";
                                      $_SESSION['s253054_result']= "Shuttle already full in that route";
                              }               
                  }
              
              else{/********* New stops -> discover new src or dest or check if the route consists in a long one ***********/
                          $querySrc= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId=?");
                          mysqli_stmt_bind_param($querySrc, 's', $src);
                          if ( !mysqli_stmt_execute($querySrc) ) {
                              die( 'stmt error: '.mysqli_stmt_error($querySrc) );
                          }
                          mysqli_stmt_bind_result($querySrc, $rowSrc);
                          mysqli_stmt_fetch($querySrc);
                          mysqli_stmt_close($querySrc);
                          echo("<br>New route******Requested source presence number of times: ".$rowSrc);
                          
                          $queryDest= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId=?");
                          mysqli_stmt_bind_param($queryDest, 's', $dest);
                          if ( !mysqli_stmt_execute($queryDest) ) {
                              die( 'stmt error: '.mysqli_stmt_error($queryDest) );
                          }
                          mysqli_stmt_bind_result($queryDest, $rowDest);
                          mysqli_stmt_fetch($queryDest);
                          mysqli_stmt_close($queryDest);
                          echo("<br>New route******Requested source presence number of times: ".$rowDest);
                          
                  if($rowSrc==0 && $rowDest==0){ /****** both are new *****/
                              $query= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId<?");
                              mysqli_stmt_bind_param($query, 's', $src);
                              if ( !mysqli_stmt_execute($query) ) {
                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                              }
                              mysqli_stmt_bind_result($query, $nBeforeSource);
                              mysqli_stmt_fetch($query);
                              mysqli_stmt_close($query);
                              echo("<br>Both new******Number of stations after new ones: ".$nBeforeSource);
                              
                              $query= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId>?");
                              mysqli_stmt_bind_param($query, 's', $src);
                              if ( !mysqli_stmt_execute($query) ) {
                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                              }
                              mysqli_stmt_bind_result($query, $nAfterSource);
                              mysqli_stmt_fetch($query);
                              mysqli_stmt_close($query);
                              echo("<br>Both new******Numeber of stations after new ones: ".$nAfterSource);
                              
                              $query= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId>?");
                              mysqli_stmt_bind_param($query, 's', $dest);
                              if ( !mysqli_stmt_execute($query) ) {
                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                              }
                              mysqli_stmt_bind_result($query, $nAfterDest);
                              mysqli_stmt_fetch($query);
                              mysqli_stmt_close($query);
                              echo("<br>Both new******Number of stations after new ones: ".$nAfterDest);
                              
                              
                              if($nAfterSource==0 && $nAfterDest==0){/*** both new stations are the last ones ***/
                                  $passengersBefore=0;
                                  $passengersAfter=0;
                                  /******* ADD NEW SOURCE ********/
                                  $query= mysqli_prepare($conn,"INSERT INTO busstop VALUES (?, ?, ?)");
                                  mysqli_stmt_bind_param($query, 'sii', $src, $passengersBefore, $seats);
                                  if ( !mysqli_stmt_execute($query) ) {
                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                  }
                                  mysqli_stmt_close($query);
                                  /******* ADD NEW DEST ********/
                                  $query= mysqli_prepare($conn,"INSERT INTO busstop VALUES (?, ?, ?)");
                                  mysqli_stmt_bind_param($query, 'sii', $dest, $seats, $passengersAfter);
                                  if ( !mysqli_stmt_execute($query) ) {
                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                  }
                                  mysqli_stmt_close($query);
                                  /******* UPDATE USER PROFILE *******/
                                  $query= mysqli_prepare($conn,"UPDATE user SET NSeats= ?, Src= ?, Dest= ? WHERE Email= ?");
                                  mysqli_stmt_bind_param($query, 'isss', $seats, $src, $dest, $user);
                                  if ( !mysqli_stmt_execute($query) ) {
                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                  }
                                  mysqli_stmt_close($query);                               
                                  echo "<br>Both new******Booked";
                                  $_SESSION['s253054_result']= "Booked";            
                              }
                              else{
                                          if($nAfterDest!=0){
                                              $query= mysqli_prepare($conn,"SELECT Arrived
                                                                               FROM busstop
                                                                               WHERE BusStopId IN (SELECT MIN(BusStopId)
                                                                                                   FROM busstop
                                                                                                   WHERE BusStopId>?)");
                                              mysqli_stmt_bind_param($query, 's', $dest);
                                              if ( !mysqli_stmt_execute($query) ) {
                                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                              }
                                              mysqli_stmt_bind_result($query, $passengersAfter);
                                              mysqli_stmt_fetch($query);
                                              mysqli_stmt_close($query);
                                              echo "<br>NO both new******Passengers after new dest: ".$passengersAfter;
                                          }
                                          else{
                                              $passengersAfter= 0;
                                          }
                                          
                                          if($nBeforeSource!=0){
                                              $query= mysqli_prepare($conn,"SELECT Starts
                                                                               FROM busstop
                                                                               WHERE BusStopId IN (SELECT MAX(BusStopId)
                                                                                                   FROM busstop
                                                                                                   WHERE BusStopId<?)");
                                              mysqli_stmt_bind_param($query, 's', $src);
                                              if ( !mysqli_stmt_execute($query) ) {
                                                  throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                              }   
                                              mysqli_stmt_bind_result($query, $passengersBefore);
                                              mysqli_stmt_fetch($query);
                                              mysqli_stmt_close($query);
                                              echo "<br>NO both new******Passengers before new source: ".$passengersBefore;
                                          }
                                          else
                                              $passengersBefore= 0;
                                  
                                          $query= mysqli_prepare($conn,"SELECT Starts, BusStopId FROM busstop WHERE BusStopId>? AND BusStopId<?");
                                          mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                                          if ( !mysqli_stmt_execute($query) ) {
                                              throw new Exception( 'stmt error: '.mysqli_stmt_error($query));
                                          }
                                          mysqli_stmt_bind_result($query, $passengers, $busId);
                                          mysqli_stmt_fetch($query);
                                          $available= 1;
                                          mysqli_stmt_fetch($query);
                                          $alreadyReservedBetween= $passengers;
                                      
                                          do{
                                            echo "<br>NO both new YES between******tot will be: ".$passengers."+".$seats." in station: ".$busId;
                                              if(($passengers+$seats)>constant('Capacity')){
												  //exit();
                                                  $_SESSION['s253054_result']= "Shuttle already full in that route";
												  throw new Exception("<br>Existent route******full");
                                                  $available= 0;
                                              }
                                          }while(mysqli_stmt_fetch($query));
                                          mysqli_stmt_close($query);
                                          if($available== 1){
                                                  $tot= $passengersBefore+$seats;
                                                  /******* ADD NEW SOURCE ********/
                                                      $query= mysqli_prepare($conn,"INSERT INTO busstop VALUES (?, ?, ?)");
                                                      mysqli_stmt_bind_param($query, 'sii', $src, $passengersBefore, $tot);
                                                      if ( !mysqli_stmt_execute($query) ) {
                                                          throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                                  }
                                                  /******* ADD NEW DEST ********/
                                                  $tot= $passengersAfter+$seats;
                                                  $query= mysqli_prepare($conn,"INSERT INTO busstop VALUES (?, ?, ?)");
                                                  mysqli_stmt_bind_param($query, 'sii', $dest, $tot, $passengersAfter);
                                                      if ( !mysqli_stmt_execute($query) ) {
                                                          throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                                      }                                
                                                  /******* UPDATE USER PROFILE *******/
                                                  $query= mysqli_prepare($conn,"UPDATE user SET NSeats= ?, Src= ?, Dest= ? WHERE Email= ?");
                                                  mysqli_stmt_bind_param($query, 'isss', $seats, $src, $dest, $user);
                                                  if ( !mysqli_stmt_execute($query) ) {
                                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                                  }
                                      
                                      
                                                  $query= mysqli_prepare($conn,"SELECT BusStopId, Starts FROM busstop WHERE BusStopId>? AND BusStopId<?");
                                                  mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                                                  if ( !mysqli_stmt_execute($query) ) {
                                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                                  }
                                  
                                                  mysqli_stmt_bind_result($query, $busId, $passengers);
                                                  $n= 0;
                                                  while(mysqli_stmt_fetch($query)){
                                                      $stationId[$n]= $busId;
                                                      $seatsRetrieved[$n]= $seatsReserved;
                                                      $n++;
                                                  }
                                                  mysqli_stmt_close($query);
                                      
                                                  for($i=0; $i<$n; $i++){
                                                      echo "<br>NO both new YES between******Bus Stop: ".$stationId[$i].", hold seats: ".$seatsReserved;
                                                      $tot= $seats+$seatsRetrieved[$i];
                                                      echo "<br>NO both new YES between******Station: ".$stationId[$i];
                                                      $query1= mysqli_prepare($conn,"UPDATE busstop SET Arrived= Arrived+?, Starts= Starts+? WHERE BusStopId= ?");
                                                      mysqli_stmt_bind_param($query1, 'iis', $seats, $seats, $stationId[$i]);
                                                      if ( !mysqli_stmt_execute($query1) ) {
                                                          throw new Exception( 'stmt error: '.mysqli_stmt_error($query1) );
                                                      }
                                                      mysqli_stmt_close($query1);   
                                                  }                               
                                      
                                      
                                                  $query= mysqli_prepare($conn,"SELECT BusStopId, Starts FROM busstop WHERE BusStopId>=? AND BusStopId<=?");
                                                  mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                                                  if ( !mysqli_stmt_execute($query) ) {
                                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                                                  }
                                                  mysqli_stmt_bind_result($query, $busId, $seatsReserved);
                                                  $n= 0;
                                                  while(mysqli_stmt_fetch($query)){
                                                      $stationId[$n]= $busId;
                                                      $seatsRetrieved[$n]= $seatsReserved;
                                                      $n++;
                                                  }
                                                  mysqli_stmt_close($query);
                                                  
                                                  echo "<br>NO both new YES between******Booked";
                                                  $_SESSION['s253054_result']= "Booked";
                                          }
                                          else{
                                                 echo "<br>NO both new YES between******Shuttle already full in that route";
                                                  $_SESSION['s253054_result']= "Shuttle already full in that route";
                                          }
                              }
                  }
              
                  else if($rowSrc==0){/** only the source is new bus stop **/
                        $querySrc= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId<?");
                        mysqli_stmt_bind_param($querySrc, 's', $src);
                        if ( !mysqli_stmt_execute($querySrc) ) {
                                throw new Exception( 'stmt error: '.mysqli_stmt_error($querySrc) );
                        }
                        mysqli_stmt_bind_result($querySrc, $nBefore);
                        mysqli_stmt_fetch($querySrc);
                        mysqli_stmt_close($querySrc);
                        echo("<br>NO both only src new******Before new source: ".$nBefore);
                        if($nBefore>0){
                           $query= mysqli_prepare($conn,"SELECT Starts
                                                             FROM busstop
                                                             WHERE BusStopId IN (SELECT MAX(BusStopId)
                                                                                 FROM busstop
                                                                                 WHERE BusStopId<?)");
                            mysqli_stmt_bind_param($query, 's', $src);
                            if ( !mysqli_stmt_execute($query) ) {
                                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                            }
                            mysqli_stmt_bind_result($query, $passengersBefore);
                            mysqli_stmt_fetch($query);
                            mysqli_stmt_close($query);
                            echo("<br>NO both only src new******Passenger before new source: ".$passengersBefore);
                            if($passengersBefore+$seats>constant('Capacity')){
                                $_SESSION['s253054_result']= "Shuttle already full in that route";
                                throw new Exception("<br>Existent route******full");                                      
                            }
                            else{
                                $holdReserved= $passengersBefore;
                            }
                        }
                        else
                           $holdReserved= 0;
                                                                
                        $query= mysqli_prepare($conn,"SELECT BusStopId, Arrived, Starts FROM busstop WHERE BusStopId>? AND BusStopId<=? ORDER BY BusStopId");
                        mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                        if ( !mysqli_stmt_execute($query) ) {
                            throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                        }
                        mysqli_stmt_bind_result($query, $busId, $passArr, $passengers);                  
                        
                        $available= 1;
                        while(mysqli_stmt_fetch($query)){
							if(strcmp($busId,$dest)==0){
								if($seats+$passArr>constant('Capacity')){
									$_SESSION['s253054_result']= "Shuttle already full in that route";
                                    throw new Exception("<br>Existent route******full");
                                    $available= 0;
								}
							}
							else{
								if(($seats+$passengers>constant('Capacity')) || ($seats+$passArr>constant('Capacity'))){
									$_SESSION['s253054_result']= "Shuttle already full in that route";
                                    throw new Exception("<br>Existent route******full");
                                    $available= 0;
								}
							}
                        }
                        mysqli_stmt_close($query);        
                        if($available==1){
                            $tot= $holdReserved+$seats;
                            echo "<br>NO both only src new******Tot: ".$tot;
                            /******* ADD NEW SOURCE ********/
                            $query= mysqli_prepare($conn,"INSERT INTO busstop VALUES (?, ?, ?)");
                            mysqli_stmt_bind_param($query, 'sii', $src, $holdReserved, $tot);
                            if ( !mysqli_stmt_execute($query) ) {
                                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                            }
                            mysqli_stmt_close($query);   
                            
                            /******* UPDATE ALL DEST ********/
                            $query= mysqli_prepare($conn,"SELECT BusStopId, Starts FROM busstop WHERE BusStopId>? AND BusStopId<=? ORDER BY BusStopId");
                            mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                            if ( !mysqli_stmt_execute($query) ) {
                                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                            }
                            mysqli_stmt_bind_result($query, $busId, $passengers);
                            $n= 0;
                            while(mysqli_stmt_fetch($query)){
                                $stationId[$n]= $busId;
                                $seatsRetrieved[$n]= $seatsReserved;
                                $n++;
                            }
                            mysqli_stmt_close($query);
                        
                            for($i=0; $i<$n; $i++){
                                $tot= $seatsRetrieved[$i]+$seats;
                                if($stationId[$i]!=$dest){
                                    $query= mysqli_prepare($conn,"UPDATE busstop SET Arrived= Arrived+?, Starts= Starts+? WHERE BusStopId= ?");
                                    mysqli_stmt_bind_param($query, 'iis', $seats, $seats, $stationId[$i]);
                                }
                                else{
                                    $query= mysqli_prepare($conn,"UPDATE busstop SET Arrived= Arrived+? WHERE BusStopId= ?");
                                    mysqli_stmt_bind_param($query, 'is', $seats, $dest);
                                }                                        
                                $res= mysqli_stmt_execute($query);
                                if($res==NULL){
                                   echo "Error Server - Try later";
                                }
                                mysqli_stmt_close($query);
                            }
                            
                            $query= mysqli_prepare($conn,"SELECT BusStopId, Starts FROM busstop WHERE BusStopId>=? AND BusStopId<=?");
                            mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                            if ( !mysqli_stmt_execute($query) ) {
                                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                            }
                            mysqli_stmt_bind_result($query, $busId, $seatsReserved);
                            $n= 0;
                            while(mysqli_stmt_fetch($query)){
                                $stationId[$n]= $busId;
                                $seatsRetrieved[$n]= $seatsReserved;
                                $n++;
                            }
                            mysqli_stmt_close($query);
                            
                             /******* UPDATE USER PROFILE *******/
                            $query= mysqli_prepare($conn,"UPDATE user SET NSeats= ?, Src= ?, Dest= ? WHERE Email= ?");
                            mysqli_stmt_bind_param($query, 'isss', $seats, $src, $dest, $user);
                            if ( !mysqli_stmt_execute($query) ) {
                                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                            }
                            mysqli_stmt_close($query);
                            echo "<br>NO both only src new******Booked";
                            $_SESSION['s253054_result']= "Booked";
                        }
                        else{
                            echo "<br>NO both only src new******Shuttle already full in that route";
                            $_SESSION['s253054_result']= "Shuttle already full in that route";
                        }
                  }
                  else{ /** only the destionation is new one **/
                      $query= mysqli_prepare($conn,"SELECT COUNT(*) FROM busstop WHERE BusStopId>?");
                      mysqli_stmt_bind_param($query, 's', $dest);
                      if ( !mysqli_stmt_execute($query) ) {
                          throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                      }
                      mysqli_stmt_bind_result($query, $nAfter);
                      mysqli_stmt_fetch($query);
                      mysqli_stmt_close($query);
                      echo("<br>NO both only dest new******After new dest: ".$nAfter);
                      
                      if($nAfter>0){ /*** new dest is not the last one ***/
                          $query= mysqli_prepare($conn,"SELECT Arrived
                                                               FROM busstop
                                                               WHERE BusStopId IN (SELECT MIN(BusStopId)
                                                                                   FROM busstop
                                                                                   WHERE BusStopId>?)");
                          mysqli_stmt_bind_param($query, 's', $dest);
                          if ( !mysqli_stmt_execute($query) ) {
                              die( 'stmt error: '.mysqli_stmt_error($query) );
                          }
                          mysqli_stmt_bind_result($query, $passengersAfter);
                          mysqli_stmt_fetch($query);
                          mysqli_stmt_close($query);
                          echo("<br>NO both only dest new******Passengers after new dest: ".$passengersAfter);
                      }
                      else{/*** new dest is the last one ***/
                          $passengersAfter= 0;
                      }
                      $query= mysqli_prepare($conn,"SELECT BusStopId, Arrived, Starts FROM busstop WHERE BusStopId>=? AND BusStopId<?");
                      mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                      if ( !mysqli_stmt_execute($query) ) {
                          throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                      }
                      mysqli_stmt_bind_result($query, $busId, $passArr, $passengers);                  
                      
                      $available= 1;
                      while(mysqli_stmt_fetch($query)){
						  if(strcmp($busId,$src)==0){
							if($seats+$passengers>constant('Capacity')){
								$available= 0;
								$_SESSION['s253054_result']= "Shuttle already full in that route";
								throw new Exception("<br>Existent route******full");
							}
						  }
						  else{
							if(($seats+$passengers>constant('Capacity')) || ($seats+$passArr>constant('Capacity'))){
								$available= 0;
								$_SESSION['s253054_result']= "Shuttle already full in that route";
								throw new Exception("<br>Existent route******full");
							}
						  }
                      }
                      mysqli_stmt_close($query); 
                      if($available==1){
                          $query= mysqli_prepare($conn,"UPDATE user SET NSeats= ?, Src= ?, Dest= ? WHERE Email= ?");
                          mysqli_stmt_bind_param($query, 'isss', $seats, $src, $dest, $user);
                          if ( !mysqli_stmt_execute($query) ) {
                              throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                          }
                          mysqli_stmt_close($query);                       
                          
                          $query= mysqli_prepare($conn,"SELECT BusStopId, Starts FROM busstop WHERE BusStopId>=? AND BusStopId<?");
                          mysqli_stmt_bind_param($query, 'ss', $src, $dest);
                          if ( !mysqli_stmt_execute($query) ) {
                              throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
                          }
                          mysqli_stmt_bind_result($query, $busId, $passengers);
                          $n= 0;
                          while(mysqli_stmt_fetch($query)){
                              $stationId[$n]= $busId;
                              $seatsRetrieved[$n]= $seatsReserved;
                              $n++;
                          }
                          mysqli_stmt_close($query);
                      
                          for($i=0; $i<$n; $i++){
                              echo "<br>NO both only dest new******Bus Stop: ".$stationId[$i].", hold seats: ".$seatsReserved;
                              $tot= $seats+$seatsRetrieved[$i];
                              if($i==0){
                                  echo "<br>NO both only dest new******Station: ".$stationId[$i];
                                  $query1= mysqli_prepare($conn,"UPDATE busstop SET Starts= Starts+? WHERE BusStopId= ?");
                                  mysqli_stmt_bind_param($query1, 'is', $seats, $stationId[$i]);
                                  if ( !mysqli_stmt_execute($query1) ) {
                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query1) );
                                  }
                                  mysqli_stmt_close($query1);   
                              }
                              else{
                                  echo "<br>NO both only dest new******Station: ".$stationId[$i];
                                  $query1= mysqli_prepare($conn,"UPDATE busstop SET Arrived= Arrived+?, Starts= Starts+? WHERE BusStopId= ?");
                                  mysqli_stmt_bind_param($query1, 'iis', $seats, $seats, $stationId[$i]);
                                  if ( !mysqli_stmt_execute($query1) ) {
                                      throw new Exception( 'stmt error: '.mysqli_stmt_error($query1) );
                                  }
                                  mysqli_stmt_close($query1);   
                              }
                          }
                                                      
                          echo "<br>NO both only dest new******Last Station: ".$dest;
                          $tot= $passengersAfter+$seats;
                          $query1= mysqli_prepare($conn,"INSERT INTO busstop VALUES (?,?,?)");
                          mysqli_stmt_bind_param($query1, 'sii', $dest, $tot, $passengersAfter);
                          if ( !mysqli_stmt_execute($query1) ) {
                              throw new Exception( 'stmt error: '.mysqli_stmt_error($query1) );
                          }
                          mysqli_stmt_close($query1);
                          
                          echo "<br>NO both only dest new******Booked with new dest station";
                          $_SESSION['s253054_result']= "Booked";       
                      }
                      else{
                          mysqli_stmt_close($query);
                          echo "<br>NO both only dest new******Shuttle already full in that route";
                          $_SESSION['s253054_result']= "Shuttle already full in that route";
                      }                              
                    }
              }
            }
          }
          mysqli_commit($conn);
      }
      catch(Exception $e){
        echo "Rollback: ".$e->getMessage();
        mysqli_rollback($conn);
        mysqli_autocommit($conn,true);
      }
      mysqli_autocommit($conn,true);
      closeConnection($conn);
  }
  //exit();
  session_write_close();
  header('Location: userHome.php');
  exit();
?>