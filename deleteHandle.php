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
        session_destroy();
          $redirect='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/../index.php';
          echo "redirect: ".$redirect;
          header('HTTP/1.1 301 Moved Permanently');
          header('Location: ' . $redirect);
          exit();
    } else {
        $_SESSION['s253054_timestamp'] = time(); //set new timestamp
    }
    
    echo "Hello ".$_SESSION['s253054_email']."!<br>";
    
    $conn= connectDB();
    if($conn== NULL){
       echo "Error Server - Try later";
    }
    else{ 
        $user= $_SESSION['s253054_email'];
        try{
            mysqli_autocommit($conn, false);
            $query= mysqli_prepare($conn,"SELECT * FROM user u, busstop b FOR UPDATE");
            mysqli_stmt_bind_param($query, 's', $user);
            if ( !mysqli_stmt_execute($query) ) {
              die( 'stmt error: '.mysqli_stmt_error($query) );
            }
            mysqli_stmt_close($query);
            
            $query= mysqli_prepare($conn,"SELECT NSeats, Src, Dest FROM user WHERE Email=?");
            mysqli_stmt_bind_param($query, 's', $user);
            if ( !mysqli_stmt_execute($query) ) {
                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
            }
            else{
                mysqli_stmt_bind_result($query, $seats, $src, $dest);
                mysqli_stmt_fetch($query);
				$realSrc= $src;
				$realDest= $dest;
                $realSeats= $seats;
                mysqli_stmt_close($query);
            }
			
			          
            $srcEmpty= NULL;
            $destEmpty= NULL;
            $seatsEmpty= 0;
            echo $realSrc." ".$realDest." ".$realSeats." ";			
			
            $query= mysqli_prepare($conn,"UPDATE user SET Src=?, Dest=?, NSeats=? WHERE Email= ?");
            mysqli_stmt_bind_param($query, 'ssis', $srcEmpty, $dstEmpty, $seatsEmpty, $user);
            if ( !mysqli_stmt_execute($query) ) {
                throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
            }
            else{				                
                mysqli_stmt_close($query);
				
				$query= mysqli_prepare($conn,"UPDATE busstop SET Starts=Starts-? WHERE BusStopId=?");
				mysqli_stmt_bind_param($query, 'is', $realSeats, $realSrc);
				if ( !mysqli_stmt_execute($query) ) {
					throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
				}
				mysqli_stmt_close($query);
				
				$query= mysqli_prepare($conn,"UPDATE busstop SET Arrived=Arrived-?, Starts=Starts-? WHERE (BusStopId>? AND BusStopId<?)");
				mysqli_stmt_bind_param($query, 'iiss', $realSeats, $realSeats, $realSrc, $realDest);
				if ( !mysqli_stmt_execute($query) ) {
					throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
				}
				mysqli_stmt_close($query);
				
				$query= mysqli_prepare($conn,"UPDATE busstop SET Arrived=Arrived-? WHERE BusStopId=?");
				mysqli_stmt_bind_param($query, 'is', $realSeats, $realDest);
				if ( !mysqli_stmt_execute($query) ) {
					throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
				}
				mysqli_stmt_close($query);
				
				$query= mysqli_prepare($conn,"SELECT COUNT(*) FROM user WHERE Src=? OR Dest=?");
				mysqli_stmt_bind_param($query, 'ss', $realSrc, $realSrc);
				if ( !mysqli_stmt_execute($query) ) {
					throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
				}
				mysqli_stmt_bind_result($query, $counterOtherInRealSrc);
                mysqli_stmt_fetch($query);
				mysqli_stmt_close($query);
				
				$query= mysqli_prepare($conn,"SELECT COUNT(*) FROM user WHERE Src=? OR Dest=?");
				mysqli_stmt_bind_param($query, 'ss', $realDest, $realDest);
				if ( !mysqli_stmt_execute($query) ) {
					throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
				}
				mysqli_stmt_bind_result($query, $counterOtherInRealDest);
                mysqli_stmt_fetch($query);
				mysqli_stmt_close($query);
                            
                if($counterOtherInRealSrc==0){
                    $query= mysqli_prepare($conn,"DELETE FROM busstop WHERE BusStopId=? AND Arrived=Starts");
					mysqli_stmt_bind_param($query, 's', $realSrc);
					if ( !mysqli_stmt_execute($query) ) {
						throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
					}
					mysqli_stmt_close($query);                                       
                }
                if($counterOtherInRealDest==0){
					$query= mysqli_prepare($conn,"DELETE FROM busstop WHERE BusStopId=? AND Arrived=Starts");
					mysqli_stmt_bind_param($query, 's', $realDest);
					if ( !mysqli_stmt_execute($query) ) {
						throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
					}
					mysqli_stmt_close($query);                              
                }               
            }
            $deleted= 1;
            do{
                 $query= "SELECT COUNT(*) FROM busstop";
                 $res= mysqli_query($conn,$query);
                 $row= mysqli_fetch_array($res);
                 if($row[0]>0){
					$query= mysqli_prepare($conn,"SELECT BusStopId, Starts, Arrived FROM busstop WHERE BusStopId IN (SELECT MIN(BusStopId) FROM busstop ORDER BY BusStopId)");
				    mysqli_stmt_bind_param($query, 's', $user);
					if ( !mysqli_stmt_execute($query) ) {
						throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
					}
					else{
						mysqli_stmt_bind_result($query, $bid, $seatss, $seatsd);
						mysqli_stmt_fetch($query);
						mysqli_stmt_close($query);
					}				 
					$toDelete= $bid;			 
                    echo "<br>".$toDelete. " ".$seatss." ".$seatsd;
                    if($seatss<=0 && $seatsd<=0){ 
						$query= mysqli_prepare($conn,"DELETE FROM busstop WHERE BusStopId=?");
						mysqli_stmt_bind_param($query, 's', $toDelete);
						if ( !mysqli_stmt_execute($query) ) {
							throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
						}
						mysqli_stmt_close($query);
                    }
                    else{
                        $deleted=0;
                    }
                }
                else
                    break;
            }while($deleted==1);
            
            $deleted= 1;
            do{
                 $query= "SELECT COUNT(*) FROM busstop";
                 $res= mysqli_query($conn,$query);
                 $row= mysqli_fetch_array($res);
                 if($row[0]>0){
                     $query= "SELECT BusStopId, Starts, Arrived FROM busstop WHERE BusStopId IN (SELECT MAX(BusStopId) FROM busstop ORDER BY BusStopId)";
                     $res= mysqli_query($conn,$query);
                     $row= mysqli_fetch_array($res);
					 $toDelete= $row[0];
                     echo "<br>".$toDelete. " ".$row[1]." ".$row[2];
                     if($row[1]<=0 && $row[2]<=0){              
                        $query= mysqli_prepare($conn,"DELETE FROM busstop WHERE BusStopId=?");
						mysqli_stmt_bind_param($query, 's', $toDelete);
						if ( !mysqli_stmt_execute($query) ) {
							throw new Exception( 'stmt error: '.mysqli_stmt_error($query) );
						}
						mysqli_stmt_close($query);                       
                     }    
                     else
                         $deleted=0;
                 }
                 else
                     break;
            }
            while($deleted==1);
            
            mysqli_commit($conn);
        }
        catch(Exception $e){
            mysqli_rollback($conn);
            echo "<script> alert('".$e->getMessage()."') </script>";
            mysqli_autocommit($conn, true);
            header('Location: delete.php');
            exit();
        }
        mysqli_autocommit($conn, true);
                  
        closeConnection($conn);
    }
    session_write_close();
    header('Location: userHome.php');
    exit();
?>