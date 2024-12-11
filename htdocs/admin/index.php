<?php

include '../components/connect.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); 
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING); 

   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ? LIMIT 1");
   $select_admins->execute([$name, $pass]);
   $row = $select_admins->fetch(PDO::FETCH_ASSOC);

   if($select_admins->rowCount() > 0){
      setcookie('admin_id', $row['id'], time() + 60*60*24*30, '/');
      header('location:dashboard.php');
   }else{
      $warning_msg[] = 'Incorrect username or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Login</title>

   <!-- Google Font -->
   <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      /* General Body Styling */
      body {
         margin: 0;
         font-family: 'Roboto', sans-serif;
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh;
         padding: 0;
         box-sizing: border-box;
         position: relative;
         overflow: hidden;
      }

      /* Animated Background */
      .area {
         background: #6495ED;
         background: -webkit-linear-gradient(to left, #CD5C5C, #FFA07A);
         width: 100%;
         height: 100vh;
         position: absolute;
         top: 0;
         left: 0;
         z-index: -1;
         /* Place background behind the form */
      }

      .circles {
         position: absolute;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         overflow: hidden;
      }

      .circles li {
         position: absolute;
         display: block;
         list-style: none;
         width: 20px;
         height: 20px;
         background: rgba(255, 255, 255, 0.2);
         animation: animate 25s linear infinite;
         bottom: -150px;
      }

      .circles li:nth-child(1) {
         left: 25%;
         width: 80px;
         height: 80px;
         animation-delay: 0s;
      }

      .circles li:nth-child(2) {
         left: 10%;
         width: 20px;
         height: 20px;
         animation-delay: 2s;
         animation-duration: 12s;
      }

      .circles li:nth-child(3) {
         left: 70%;
         width: 20px;
         height: 20px;
         animation-delay: 4s;
      }

      .circles li:nth-child(4) {
         left: 40%;
         width: 60px;
         height: 60px;
         animation-delay: 0s;
         animation-duration: 18s;
      }

      .circles li:nth-child(5) {
         left: 65%;
         width: 20px;
         height: 20px;
         animation-delay: 0s;
      }

      .circles li:nth-child(6) {
         left: 75%;
         width: 110px;
         height: 110px;
         animation-delay: 3s;
      }

      .circles li:nth-child(7) {
         left: 35%;
         width: 150px;
         height: 150px;
         animation-delay: 7s;
      }

      .circles li:nth-child(8) {
         left: 50%;
         width: 25px;
         height: 25px;
         animation-delay: 15s;
         animation-duration: 45s;
      }

      .circles li:nth-child(9) {
         left: 20%;
         width: 15px;
         height: 15px;
         animation-delay: 2s;
         animation-duration: 35s;
      }

      .circles li:nth-child(10) {
         left: 85%;
         width: 150px;
         height: 150px;
         animation-delay: 0s;
         animation-duration: 11s;
      }

      @keyframes animate {
         0% {
            transform: translateY(0) rotate(0deg);
            opacity: 2;
            border-radius: 0;
         }

         100% {
            transform: translateY(-1000px) rotate(720deg);
            opacity: 0;
            border-radius: 50%;
         }
      }
   </style>

</head>

<body>

   <!-- Animated Background -->
   <div class="area">
      <ul class="circles">
         <li></li>
         <li></li>
         <li></li>
         <li></li>
         <li></li>
         <li></li>
         <li></li>
         <li></li>
         <li></li>
         <li></li>
      </ul>
   </div>

   <!-- Login Form -->
   <section class="admin-login-container">
      <form action="" method="POST">
         <h1>Pagkalinawan AMS</h1>
         <br>
         <h3>Welcome Back!</h3>
         <input type="text" name="name" placeholder="Enter Username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" placeholder="Enter Password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Login Now" name="submit" class="btn">
      </form>
   </section>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

   <?php include '../components/message.php'; ?>

</body>

</html>
