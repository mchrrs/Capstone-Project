<?php
include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
   $user_id = $_COOKIE['user_id'];
} else {
   $user_id = '';
   header('location:index.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Dashboard</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- Custom CSS File Link -->
   <link rel="stylesheet" href="css/style.css">
   
</head>

<body>

   <?php include 'components/user_header.php'; ?>
   <br><br><br><br><br>
   <section class="dashboard">
      <h1 class="heading">Welcome to Your Dashboard</h1>
      <br><br>

      <div class="box-container">
         <!-- Profile Box -->
         <div class="box">
            <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
            $select_profile->execute([$user_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            ?>
            <h3>Welcome!</h3>
            <p><?= $fetch_profile['email']; ?></p>
            <a href="update.php" class="btn">Update Profile</a>
         </div>

         <!-- Payments Box -->
         <div class="box">
            <?php
            $count_bills = $conn->prepare("SELECT * FROM `bills` WHERE user_id = ?");
            $count_bills->execute([$user_id]);
            $total_bills = $count_bills->rowCount();
            ?>
            <h3><?= $total_bills; ?></h3>
            <p>My Bills</p>
            <a href="my_bills.php" class="btn">Pay Rent</a>
         </div>

         <!-- Owned Property Box -->
         <div class="box">
            <?php
            $count_owned = $conn->prepare("SELECT * FROM `occupied_properties` WHERE email = ?");
            $count_owned->execute([$fetch_profile['email']]);
            $total_owned = $count_owned->rowCount();
            ?>
            <h3><?= $total_owned; ?></h3>
            <p>Owned Properties</p>
            <a href="occupied_properties.php" class="btn">Access Your Room</a>
         </div>

         <!-- Tickets Box -->
         <div class="box">
            <?php
            // Query to count the complaints by the user's ID
            $count_complaints = $conn->prepare("SELECT * FROM `complaints` WHERE user_id = ?");
            $count_complaints->execute([$fetch_profile['id']]);
            $total_complaints = $count_complaints->rowCount();
            ?>
            <!-- Display the total complaints count -->
            <h3><?= $total_complaints; ?></h3>
            <p>Your Complaints</p>
            <a href="view_complaints.php" class="btn">View your tickets</a>
         </div>

      </div>

   </section>
   <br><br><br><br><br><br><br><br><br><br><br><br><br>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

   <?php include 'components/footer.php'; ?>

   <!-- Custom JS File Link -->
   <script src="js/script.js"></script>

   <?php include 'components/message.php'; ?>

</body>

</html>