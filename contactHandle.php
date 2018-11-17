<!DOCTYPE html>
<?php
    $session_name= "s253054_user-session";
    ini_set('session.use_only_cookies', 1); //make sure, that the session id is sent only with a cookie, and not for example in the url.
    session_name($session_name);
    session_start();
    setcookie('dir', 'contactHandle.php');
    if(!isset($_COOKIE['dir'])){
        header('Location: checkCookie.php');
        exit();
    }
    session_write_close();
?>
<html>
    <head>
        <link rel="stylesheet" href="styleHomePage.css">
    </head>
    <p>Database structure (with random example): </p>
    <table>
        <tr><td>
    <table id="route" style= 'width: 20em; height: auto;'>
        <tr><th id="route" colspan="5">user</th></tr>
        <tr><th id="route">&nbspEmail&nbsp</th><th id="route">&nbspPassword&nbsp</th><th id="route">&nbspSrc&nbsp</th><th id="route">&nbspDest&nbsp</th><th id="route">&nbspNSeats&nbsp</tr>
        <tr id="route"><td>&nbspu1@p.it&nbsp</td><th>&nbsp******&nbsp</th><th>&nbspAA&nbsp</th><th>&nbspBB&nbsp</th><th>&nbsp2&nbsp</th></tr>
        <tr id="route"><td>&nbspu2@p.it&nbsp</td><th>&nbsp******&nbsp</th><th>&nbspBB&nbsp</th><th>&nbspCC&nbsp</th><th>&nbsp1&nbsp</th></tr>
        <tr id="route"><td>&nbspu3@p.it&nbsp</td><th>&nbsp******&nbsp</th><th>&nbspBB&nbsp</th><th>&nbspCC&nbsp</th><th>&nbsp1&nbsp</th></tr>
    </table>
    </td>
        <td><table id="route" style= 'width: 20em; height: auto;'>
        <tr><th id="route" colspan="3">busstop</th></tr>
        <tr><th id="route">&nbspBusStopId&nbsp</th><th id="route">&nbspArrived&nbsp</th><th id="route">&nbspStart&nbsp</th></tr>
        <tr id="route"><td>&nbspAA&nbsp</td><th>&nbsp0&nbsp</th><th>&nbsp2&nbsp</th></tr>
        <tr id="route"><td>&nbspBB&nbsp</td><th>&nbsp2&nbsp</th><th>&nbsp2&nbsp</th></tr>
        <tr id="route"><td>&nbspCC&nbsp</td><th>&nbsp2&nbsp</th><th>&nbsp0&nbsp</th></tr>
    </table></td></tr>
    </table>
	<br><br><br>
    <p>Website developed by Valerio Paolicelli (matr. 253054), Email: s253054@studenti.polito.it</p>
    <p>Assignment of Networking/Web Programming course: Distribuited Programming I</p>
    <p>Exam of 03/07/2018, Politecnico di Torino</p>
</html>
