<?php

include '../components/connect.php';

// Initialize message arrays
$warning_msg = [];
$success_msg = [];

if (isset($_COOKIE['admin_id'])) {
   $user_id = $_COOKIE['admin_id'];
} else {
   $user_id = '';
   header('location:index.php');
}

// Fetch users for the dropdown
$users_query = $conn->prepare("SELECT id, name FROM users");
$users_query->execute();
$users = $users_query->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['post'])) {

   // Sanitize input values
   $id = create_unique_id();
   $property_name = filter_var($_POST['property_name'], FILTER_SANITIZE_STRING);
   $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
   $offer = filter_var($_POST['offer'], FILTER_SANITIZE_STRING);
   $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
   $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
   $furnished = filter_var($_POST['furnished'], FILTER_SANITIZE_STRING);
   $bedroom = filter_var($_POST['bedroom'], FILTER_SANITIZE_STRING);
   $bathroom = filter_var($_POST['bathroom'], FILTER_SANITIZE_STRING);
   $carpet = filter_var($_POST['carpet'], FILTER_SANITIZE_STRING);
   $age = filter_var($_POST['age'], FILTER_SANITIZE_STRING);
   $total_floors = filter_var($_POST['total_floors'], FILTER_SANITIZE_STRING);
   $room_floor = filter_var($_POST['room_floor'], FILTER_SANITIZE_STRING);
   $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

   // Handle contract file (if any)
   $contract = $_FILES['contract'] ?? null;  // Handle contract file

   // Handle image uploads
   $image_fields = ['image_01', 'image_02', 'image_03', 'image_04', 'image_05'];
   $uploaded_images = [];
   foreach ($image_fields as $image_field) {
      if (!empty($_FILES[$image_field]['name'])) {
         $image_name = filter_var($_FILES[$image_field]['name'], FILTER_SANITIZE_STRING);
         $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
         $rename_image = create_unique_id() . '.' . $image_ext;
         $image_tmp_name = $_FILES[$image_field]['tmp_name'];
         $image_size = $_FILES[$image_field]['size'];
         $image_folder = 'uploaded_files/' . $rename_image;

         if ($image_size > 2000000) {
            $warning_msg[] = "{$image_field} size is too large!";
         } else {
            move_uploaded_file($image_tmp_name, $image_folder);
            $uploaded_images[$image_field] = $rename_image;
         }
      } else {
         $uploaded_images[$image_field] = '';
      }
   }

   if (empty($warning_msg)) {
      // If the status is "Available", set occupied_by to NULL
      $occupied_by = ($status == 'Occupied' && isset($_POST['user_id'])) ? $_POST['user_id'] : NULL;
      $occupants = ($status == 'Occupied' && isset($_POST['occupants'])) ? $_POST['occupants'] : NULL;

      // Insert property record
      $insert_property = $conn->prepare("INSERT INTO `property`(id, user_id, property_name, address, price, type, offer, status, furnished, bedroom, bathroom, carpet, age, total_floors, room_floor, image_01, image_02, image_03, image_04, image_05, description, occupied_by, occupants) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $insert_property->execute([
         $id,
         $user_id,  // This should be the user who is posting the property
         $property_name,
         $address,
         $price,
         $type,
         $offer,
         $status,
         $furnished,
         $bedroom,
         $bathroom,
         $carpet,
         $age,
         $total_floors,
         $room_floor,
         $uploaded_images['image_01'],
         $uploaded_images['image_02'],
         $uploaded_images['image_03'],
         $uploaded_images['image_04'],
         $uploaded_images['image_05'],
         $description,
         $occupied_by,  // If the property is occupied, this will store the user_id
         $occupants     // If the property is occupied, this will store the number of occupants
      ]);

      // If the room is occupied, handle contract file upload
      if ($_FILES['contract']['error'] != UPLOAD_ERR_OK) {
         $warning_msg[] = 'Error in file upload: ' . $_FILES['contract']['error'];
      } else {
         // Continue with the file handling and saving process
         $contract_name = $_FILES['contract']['name'];
         $contract_tmp_name = $_FILES['contract']['tmp_name'];
         $contract_ext = pathinfo($contract_name, PATHINFO_EXTENSION);
         $contract_rename = create_unique_id() . '.' . $contract_ext;

         // Use absolute path for better reliability
         $contract_folder = $_SERVER['DOCUMENT_ROOT'] . '/admin/uploaded_contracts/' . $contract_rename;

         if ($_FILES['contract']['size'] > 2000000) {
            $warning_msg[] = "Contract file size is too large!";
         } else {
            if (move_uploaded_file($contract_tmp_name, $contract_folder)) {
               // Insert contract file path into the property table (if needed)
               $update_contract = $conn->prepare("UPDATE `property` SET contract = ? WHERE id = ?");
               $update_contract->execute([$contract_rename, $id]);
            } else {
               $warning_msg[] = "Failed to move the uploaded contract file!";
            }
         }
      }


      // Insert into occupied_properties table if the property is occupied
      if ($status == 'Occupied' && isset($_POST['user_id'])) {
         // Fetch the user details for the occupant
         $occupant_id = $_POST['user_id'];
         $select_user = $conn->prepare("SELECT name, number, email FROM users WHERE id = ?");
         $select_user->execute([$occupant_id]);
         $user_data = $select_user->fetch(PDO::FETCH_ASSOC);

         // Insert into occupied_properties table
         $insert_occupied_property = $conn->prepare("INSERT INTO `occupied_properties`(property_name, name, occupants, contract, number, email, status) VALUES(?,?,?,?,?,?,?)");
         $insert_occupied_property->execute([
            $property_name,
            $user_data['name'],
            $occupants,
            $contract_rename,  // If contract file is uploaded, pass the path
            $user_data['number'],
            $user_data['email'],
            $status
         ]);
      }

      $success_msg[] = 'Property posted successfully!';
   }
}

?>

<!-- HTML Form Code remains the same as in your original code -->


<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Post Property</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <section class="property-form">
      <form action="" method="POST" enctype="multipart/form-data">
         <h3>Property Details</h3>

         <div class="box">
            <p>Property Name <span>*</span></p>
            <input type="text" name="property_name" required maxlength="50" placeholder="Enter property name" class="input">
         </div>

         <div class="flex">
            <div class="box">
               <p>Property Price <span>*</span></p>
               <input type="number" name="price" required min="0" max="9999999999" maxlength="10" placeholder="Enter property price" class="input">
            </div>

            <div class="box">
               <p>Property Address <span>*</span></p>
               <input type="text" name="address" required maxlength="100" placeholder="Enter property full address" class="input">
            </div>
         </div>

         <div class="flex">
            <div class="box">
               <p>Offer Type <span>*</span></p>
               <select name="offer" required class="input">
                  <option value="">Select an Option</option>
                  <option value="Sale">Sale</option>
                  <option value="Resale">Resale</option>
                  <option value="Rent">Rent</option>
               </select>
            </div>

            <div class="box">
               <p>Property Type <span>*</span></p>
               <select name="type" required class="input">
                  <option value="">Select an Option</option>
                  <option value="House">House</option>
                  <option value="Shop">Shop</option>
               </select>
            </div>

            <div class="box">
               <p>Property Status <span>*</span></p>
               <select name="status" id="status" required class="input">
                  <option value="">Select an Option</option>
                  <option value="Occupied">Occupied</option>
                  <option value="Available">Available</option>
               </select>
            </div>
         </div>

         <div class="flex">
            <div class="box">
               <p>Furnished Status <span>*</span></p>
               <select name="furnished" required class="input">
                  <option value="">Select an Option</option>
                  <option value="furnished">Furnished</option>
                  <option value="semi-furnished">Semi-furnished</option>
                  <option value="unfurnished">Unfurnished</option>
               </select>
            </div>

            <div class="box">
               <p>How many Bedrooms <span>*</span></p>
               <select name="bedroom" required class="input">
                  <option value="0">0 Bedroom</option>
                  <option value="1" selected>1 Bedroom</option>
                  <option value="2">2 Bedrooms</option>
                  <option value="3">3 Bedrooms</option>
                  <option value="4">4 Bedrooms</option>
                  <option value="5">5 Bedrooms</option>
                  <option value="6">6 Bedrooms</option>
                  <option value="7">7 Bedrooms</option>
                  <option value="8">8 Bedrooms</option>
                  <option value="9">9 Bedrooms</option>
               </select>
            </div>

            <div class="box">
               <p>How many Bathrooms <span>*</span></p>
               <select name="bathroom" required class="input">
                  <option value="1">1 Bathroom</option>
                  <option value="2">2 Bathrooms</option>
                  <option value="3">3 Bathrooms</option>
                  <option value="4">4 Bathrooms</option>
                  <option value="5">5 Bathrooms</option>
                  <option value="6">6 Bathrooms</option>
                  <option value="7">7 Bathrooms</option>
                  <option value="8">8 Bathrooms</option>
                  <option value="9">9 Bathrooms</option>
               </select>
            </div>
         </div>

         <div class="flex">
            <div class="box">
               <p>Carpet Area (in sqft) <span>*</span></p>
               <input type="number" name="carpet" required min="1" max="9999" placeholder="Enter Carpet Area" class="input">
            </div>

            <div class="box">
               <p>Age of Property (in years) <span>*</span></p>
               <input type="number" name="age" required min="0" max="100" placeholder="Enter Age of Property" class="input">
            </div>
         </div>

         <div class="box">
            <p>Total Floors <span>*</span></p>
            <input type="number" name="total_floors" required min="1" max="50" placeholder="Enter Total Floors" class="input">
         </div>

         <div class="box">
            <p>Room Floor (if applicable) <span>*</span></p>
            <input type="number" name="room_floor" required min="1" max="50" placeholder="Enter Room Floor" class="input">
         </div>

         <!-- Additional fields for Occupied Properties -->
         <div id="occupiedFields" style="display: none;">
            <div class="box">
               <p>Select Occupant <span>*</span></p>
               <select name="user_id" class="input">
                  <option value="">Select a User</option>
                  <?php foreach ($users as $user) : ?>
                     <option value="<?= $user['id'] ?>"><?= $user['name'] ?></option>
                  <?php endforeach; ?>
               </select>
            </div>

            <div class="box">
               <p>Upload Contract <span>*</span></p>
               <input type="file" name="contract" class="input" accept="application/pdf,application/msword">
            </div>

            <div class="box">
               <p>Number of Occupants <span>*</span></p>
               <input type="number" name="occupants" class="input" min="1" placeholder="Number of Occupants">
            </div>
         </div>
         <div class="box">
            <p>Image 01 <span>*</span></p>
            <input type="file" name="image_01" class="input" accept="image/*" required>
         </div>

         <div class="box">
            <p>Image 02</p>
            <input type="file" name="image_02" class="input" accept="image/*">
         </div>

         <div class="box">
            <p>Image 03 </p>
            <input type="file" name="image_03" class="input" accept="image/*">
         </div>

         <div class="box">
            <p>Image 04</p>
            <input type="file" name="image_04" class="input" accept="image/*">
         </div>
         <div class="box">
            <p>Image 05</p>
            <input type="file" name="image_05" class="input" accept="image/*">
         </div>

         <div class="box">
            <p>Property Description <span>*</span></p>
            <textarea name="description" required class="input" placeholder="Enter Property Description"></textarea>
         </div>

         <input type="submit" value="Post Property" class="btn" name="post">
      </form>
   </section>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
   <script src="../js/admin_script.js"></script>

   <script>
      document.getElementById('status').addEventListener('change', function() {
         var status = this.value;
         var occupiedFields = document.getElementById('occupiedFields');
         if (status === 'Occupied') {
            occupiedFields.style.display = 'block';
         } else {
            occupiedFields.style.display = 'none';
            document.querySelector('select[name="user_id"]').value = ''; // Reset user selection for Available status
         }
      });
   </script>

   <?php include '../components/message.php'; ?>
</body>

</html>