<?php  
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
}

$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){
    // Get user input and sanitize it
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $number = htmlspecialchars($_POST['number']);
    $old_pass = htmlspecialchars($_POST['old_pass']);
    $new_pass = htmlspecialchars($_POST['new_pass']);
    $c_pass = htmlspecialchars($_POST['c_pass']);

    // Flag to track if the password needs to be updated
    $password_updated = false;

    // Check if the email or name or number is different and update if needed
    $update_query = "UPDATE `users` SET `name` = ?, `email` = ?, `number` = ? WHERE `id` = ?";
    $update_values = [$name, $email, $number, $user_id];

    // Handle password change if user entered a new password
    if(!empty($new_pass) && !empty($c_pass)){
        // Verify the old password
        if(password_verify($old_pass, $fetch_user['password'])){
            // Check if the new password matches the confirmation
            if($new_pass === $c_pass){
                // Hash the new password
                $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                // Add password update to the query
                $update_query = "UPDATE `users` SET `name` = ?, `email` = ?, `number` = ?, `password` = ? WHERE `id` = ?";
                $update_values = [$name, $email, $number, $hashed_new_pass, $user_id];
                $password_updated = true;
            } else {
                // Passwords do not match
                echo "<script>swal('Error', 'New password and confirmation do not match', 'error');</script>";
            }
        } else {
            // Old password is incorrect
            echo "<script>swal('Error', 'Old password is incorrect', 'error');</script>";
        }
    }

    // Execute the query to update user information
    if(!$password_updated || (!empty($new_pass) && !empty($c_pass))) {
        $update_user = $conn->prepare($update_query);
        $update_user->execute($update_values);
        echo "<script>swal('Success', 'Your account has been updated successfully!', 'success');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Account</title>

   <!-- Font Awesome for Icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- Tailwind CSS for Styling -->
   <script src="https://cdn.tailwindcss.com"></script>

   <!-- SweetAlert for Popup Notifications -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50">

<?php include 'components/user_header.php'; ?>
<br><br><br><br><br><br><br><br>
<section class="max-w-5xl mx-auto p-12 bg-white rounded-lg shadow-lg mt-10">
   <form action="" method="post" class="space-y-8">
      <h3 class="text-4xl font-semibold text-center text-teal-600">Update Your Account</h3>

      <div class="space-y-6">
         <!-- Name Input -->
         <input type="text" name="name" maxlength="50" value="<?= $fetch_user['name']; ?>" class="w-full px-6 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-lg">

         <!-- Email Input -->
         <input type="email" name="email" maxlength="50" value="<?= $fetch_user['email']; ?>" class="w-full px-6 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-lg">

         <!-- Number Input -->
         <input type="tel" name="number" maxlength="11" value="<?= $fetch_user['number']; ?>" class="w-full px-6 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-lg">

         <!-- Password Fields -->
         <input type="password" name="old_pass" maxlength="20" placeholder="Enter your old password" class="w-full px-6 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-lg">
         <input type="password" name="new_pass" maxlength="20" placeholder="Enter your new password" class="w-full px-6 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-lg">
         <input type="password" name="c_pass" maxlength="20" placeholder="Confirm your new password" class="w-full px-6 py-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-lg">
      </div>

      <!-- Submit Button -->
      <div class="flex justify-center">
         <input type="submit" value="Update Now" name="submit" class="px-8 py-4 bg-teal-600 text-white font-semibold rounded-lg shadow-lg hover:bg-teal-700 transition duration-300 ease-in-out text-xl">
      </div>
   </form>
</section>
<br><br><br><br><br><br><br><br><br>

<?php include 'components/footer.php'; ?>

<!-- Custom JS File -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>
