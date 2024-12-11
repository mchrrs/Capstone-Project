<?php
include '../components/connect.php';

$alert_message = null; // Variable to store SweetAlert message

if (!isset($_GET['get_id']) || empty($_GET['get_id'])) {
    header('Location: listings.php');
    exit;
}

$property_id = filter_var($_GET['get_id'], FILTER_SANITIZE_STRING);

// Fetch users
$select_users = $conn->prepare("SELECT id, name, number FROM `users`");
$select_users->execute();
$users = $select_users->fetchAll(PDO::FETCH_ASSOC);

// Fetch property details
$select_property = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
$select_property->execute([$property_id]);
$property = $select_property->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: listings.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = $_POST['tenant_id'];
    $occupants = $_POST['occupants'];
    $status = $_POST['status'];
    $contract_image = $_FILES['contract_image'];

    // Handle contract image upload if property is set to "Occupied"
    $contract_image_new_name = null;
    if ($status == 'Occupied' && $contract_image['error'] == 0) {
        $contract_image_name = $contract_image['name'];
        $contract_image_tmp = $contract_image['tmp_name'];
        $contract_image_ext = pathinfo($contract_image_name, PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array(strtolower($contract_image_ext), $allowed_extensions)) {
            $contract_image_new_name = uniqid('', true) . '.' . $contract_image_ext;
            move_uploaded_file($contract_image_tmp, 'uploaded_contracts/' . $contract_image_new_name);
        } else {
            $alert_message = 'Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.';
        }
    }

    // Process when the property status is "Available" (reset all relevant fields)
    if ($status == 'Available') {
        // Update the property table to make the property available
        $update_property = $conn->prepare("UPDATE `property` SET 
            status = 'Available',
            occupied_by = NULL,  -- Clear the occupied_by field
            occupants = NULL,    -- Clear the occupants field
            contract = NULL      -- Remove the contract file
            WHERE id = ?");
        $update_property->execute([$property_id]);

        // Remove the property from occupied_properties table
        $delete_occupied_property = $conn->prepare("DELETE FROM `occupied_properties` WHERE property_name = ?");
        $delete_occupied_property->execute([$property['property_name']]);

        $alert_message = 'Property status updated to Available successfully!';
        header("Location: listings.php");
        exit;
    }

    // Process when the property status is "Occupied" (update property and insert into occupied_properties)
    if ($alert_message === null && $status == 'Occupied' && $tenant_id && $occupants && isset($contract_image_new_name)) {
        // Fetch tenant details
        $select_tenant = $conn->prepare("SELECT name, number, email FROM `users` WHERE id = ?");
        $select_tenant->execute([$tenant_id]);
        $tenant = $select_tenant->fetch(PDO::FETCH_ASSOC);

        // If the tenant exists, update the property status to "Occupied"
        if ($tenant) {
            // Update property table with new status and details
            $update_property = $conn->prepare("UPDATE `property` SET 
                status = 'Occupied',
                occupied_by = ?, 
                occupants = ?, 
                contract = ?
                WHERE id = ?");
            $update_property->execute([
                $tenant['name'], 
                $occupants, 
                $contract_image_new_name, 
                $property_id
            ]);

            // Insert into occupied_properties table
            $insert_occupied = $conn->prepare("
                INSERT INTO `occupied_properties` (property_name, name, occupants, contract, number, email, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insert_occupied->execute([
                $property['property_name'],
                $tenant['name'],
                $occupants,
                $contract_image_new_name,
                $tenant['number'],
                $tenant['email'],
                'Occupied'
            ]);

            $alert_message = 'Property status updated to Occupied successfully!';
            header("Location: occupied_properties.php");
            exit;
        } else {
            $alert_message = 'Invalid tenant selected.';
        }
    } else {
        $alert_message = 'Please fill in all required fields and upload a valid contract image (if occupied).';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Property</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../components/admin_header.php'; ?>
    <section class="set-property-form">
        <h1 class="heading">Set Property for Tenants</h1>

        <div class="property-details">
            <form action="" method="POST" enctype="multipart/form-data">
                <h3><?= htmlspecialchars($property['property_name']); ?></h3>
                <p><strong>Location:</strong> <?= htmlspecialchars($property['address']); ?></p>
                <p><strong>Price:</strong> <?= htmlspecialchars($property['price']); ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($property['status']); ?></p>
                <br><br><br>

                <label for="status">Change Property Status:</label>
                <select name="status" id="status" required>
                    <option value="" disabled selected>Select Status</option>
                    <option value="Occupied" <?= ($property['status'] == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                    <option value="Available" <?= ($property['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                </select>
                <br><br><br>

                <div id="tenant-details" style="display: <?= ($property['status'] == 'Occupied') ? 'block' : 'none'; ?>">
                    <label for="tenant_id">Select Tenant:</label>
                    <select name="tenant_id" id="tenant_id" <?= ($property['status'] == 'Available') ? 'disabled' : ''; ?> required>
                        <option value="" disabled selected>Select a tenant</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id']; ?>" <?= ($user['id'] == $property['occupied_by']) ? 'selected' : ''; ?>><?= htmlspecialchars($user['name']); ?> - <?= htmlspecialchars($user['number']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <br><br><br>

                    <label for="occupants">Number of Occupants:</label>
                    <input type="number" name="occupants" value="<?= htmlspecialchars($property['occupants']); ?>" min="1" max="99" required>
                    <br><br><br>

                    <label for="contract_image">Upload Contract (Image or PDF):</label>
                    <input type="file" name="contract_image" accept="image/*, .pdf" <?= ($property['status'] == 'Available') ? 'disabled' : ''; ?>>
                    <br><br><br>
                </div>

                <input type="submit" value="Update Property" class="btn">
            </form>
        </div>
    </section>

    <!-- SweetAlert script -->
    <script>
        <?php if ($alert_message !== null): ?>
            Swal.fire({
                title: '<?= $alert_message ?>',
                icon: '<?= strpos($alert_message, 'Invalid') !== false || strpos($alert_message, 'Please') !== false ? 'error' : 'success' ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>

    <script>
        // Show or hide tenant details and contract upload based on the selected status
        document.getElementById('status').addEventListener('change', function() {
            var status = this.value;
            var tenantDetails = document.getElementById('tenant-details');
            var tenantSelect = document.getElementById('tenant_id');
            var occupantsInput = document.querySelector('input[name="occupants"]');
            var contractInput = document.querySelector('input[name="contract_image"]');

            if (status == 'Occupied') {
                tenantDetails.style.display = 'block';
                tenantSelect.removeAttribute('disabled');
                occupantsInput.removeAttribute('disabled');
                contractInput.removeAttribute('disabled');
            } else {
                tenantDetails.style.display = 'none';
                tenantSelect.setAttribute('disabled', 'disabled');
                occupantsInput.setAttribute('disabled', 'disabled');
                contractInput.setAttribute('disabled', 'disabled');
            }
        });
    </script>
</body>

</html>
