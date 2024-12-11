<?php

include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
   $user_id = $_COOKIE['user_id'];
} else {
   $user_id = '';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About Us</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <!-- about section starts  -->

   <section class="about">

      <div class="row">
         <div class="image">
            <img src="images/Croods_-_Keeping_in_Touch.png" alt="">
         </div>
         <div class="content" style="background-color: #ffff; width: 80%; max-width: 1200px; margin: 0 auto; padding: 2rem; border-radius: 10px; box-sizing: border-box;">
            <h3>why choose us?</h3>
            <br>
            <p>We simplify the process of finding, renting, and selling homes. Our platform connects you with quality listings, offering a seamless experience for renters, buyers, and sellers. With user-friendly tools and reliable support, we make home management easier for everyone.</p>
         </div>
      </div>

   </section>

   <!-- about section ends -->
   <br>
   <br>
   <!-- steps section starts  -->

   <section class="steps">

      <h1 class="heading">3 simple steps</h1>
      <br>
      <br>
      <div class="box-container">

         <div class="box">
            <img src="images/step-1.png" alt="">
            <h3>Search room</h3>
            <p>Find your ideal living space effortlessly! explore available units that match your preferences and discover your perfect home today.</p>
         </div>

         <div class="box">
            <img src="images/step-2.png" alt="">
            <h3>Contact admin</h3>
            <p>Have questions or need help? Connect with our trusted admin for personalized assistance. We're here to guide you through every step!</p>
         </div>

         <div class="box">
            <img src="images/step-3.png" alt="">
            <h3>Enjoy room</h3>
            <p>Move in with confidence! Discover your perfect home, settle in, and enjoy a comfortable living experience tailored to your needs.</p>
         </div>

      </div>
      <br>
      <br>
      <br>
   </section>

   <!-- contact section starts  -->

   <section class="contact">

      <div class="row">
         <div class="image">
            <img src="images/contact-img.svg" alt="">
         </div>
      </div>

   </section>

   <!-- contact section ends -->

   <!-- faq section starts  -->

   <section class="faq" id="faq">

      <h1 class="heading">FAQ</h1>

      <div class="box-container">
         <div class="box">
            <h3><span>Where can I pay the rent?</span><i class="fas fa-angle-down"></i></h3>
            <p>You can pay your rent directly through our platform by visiting the "Payments" section in your account. Choose your preferred payment method for a quick and secure transaction.</p>
         </div>

         <div class="box">
            <h3><span>How to contact with the landlord?</span><i class="fas fa-angle-down"></i></h3>
            <p>Simply click on the landlord’s contact information within the property listing. You can reach out via email or phone for any inquiries.</p>
         </div>

         <div class="box">
            <h3><span>Is it possible to create our own accounts?</span><i class="fas fa-angle-down"></i></h3>
            <p>You have two options We can provide a default account for you and you can just update the details yourself, or you can share your details with us,
               and we’ll create the account for you. This helps us avoid unnecessary multiple accounts.</p>
         </div>

         <div class="box">
            <h3><span>How to submit a Ticket in maintenance</span><i class="fas fa-angle-down"></i></h3>
            <p>Once you have finally logged into your account, you can submit a ticket through accessing your room.</p>
         </div>
      </div>
      <br>
      <br>
      <br>
   </section>

   <!-- steps section ends -->











   <?php include 'components/footer.php'; ?>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>