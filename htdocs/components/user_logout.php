<?php

include '../components/connect.php';

setcookie('user_id', '', time() - 1, '/');

header('location:/index.php');

?>