<?php
    include('dbHandle.php');
    $conn= connectDB();
    if($conn== NULL){
        echo "Error Server - Try later";
    }
    else{
        $query= "SELECT BusStopId, Starts FROM busstop ORDER BY BusStopId";
        $res= queryDB($conn, $query);
        if($res==NULL){
            echo "Error Server - Try later";
        }
        else{
            $row= mysqli_fetch_array($res);
			$prev[0]= $row[0];
            if($prev[0]==""){
                echo "<p style='text-align: left'>Empty Shuttle<img src='images/busReverse.png' id= 'logoBackground'></p>";
            }
            else{
				$prev[1]= mysqli_real_escape_string($conn, $row[1]);
                
                $row= mysqli_fetch_array($res);
                echo "<table id='route'><tr><th id='route'>&nbspFrom&nbsp</th><th id='route'>&nbspTo&nbsp</th><th id='route'>&nbspReserved&nbsp</th></tr>";
                do{
                    if($prev[1]!=0)
                        echo "<tr id='route'><td><span>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$row[0]."&nbsp</span></td><td><span>&nbsp".$prev[1]."&nbsp</span></td></tr>";
                    else
                        echo "<tr id='route'><td><span>&nbsp".$prev[0]."&nbsp</span></td><td><span>&nbsp".$row[0]."&nbsp</span></td><td><span>&nbspNo reservation&nbsp</span></td></tr>";
                    $prev[0]= $row[0];
                    $prev[1]= $row[1];
                    $row= mysqli_fetch_array($res);
                }while($row!=NULL);
                echo "</table>";   
            }
        }
        closeConnection($conn);
    }   
?>