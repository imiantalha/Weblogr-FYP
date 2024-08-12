<?php
        
        $server = "localhost";
        $username = "root";
        $password = "";
        $dbname = "weblogr";
    
        $con = mysqli_connect($server, $username, $password, $dbname);
    
        if (!$con) {
            die("Connection failed due to " . mysqli_connect_error());
        }

?>