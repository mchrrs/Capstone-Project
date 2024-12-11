<?php
include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:login.php');
    exit;
}

// Fetch bill details based on the passed bill_id
$bill_id = filter_input(INPUT_GET, 'bill_id', FILTER_SANITIZE_NUMBER_INT);

try {
    $sql = "SELECT * FROM bills WHERE id = :bill_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':bill_id', $bill_id, PDO::PARAM_INT);
    $stmt->execute();
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching bill: " . $e->getMessage());
}

$alert_message = ''; // For SweetAlert feedback

// Fetch the user's name from the users table
try {
    $sql = "SELECT name FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_name = $user['name'];  // User's name to be stored in the receipts table
} catch (PDOException $e) {
    die("Error fetching user name: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment']) && isset($_FILES['receipt_file'])) {
    $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING);
    $receipt_file = $_FILES['receipt_file'];

    // File upload configuration
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/project/receipts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_ext = pathinfo($receipt_file['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array(strtolower($file_ext), $allowed_extensions)) {
        $alert_message = 'error_invalid_file';
    } else {
        // Generate a unique file name to avoid overwriting
        $file_name = uniqid('receipt_') . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($receipt_file['tmp_name'], $file_path)) {
            try {
                $conn->beginTransaction();

                // Insert into receipts with user_name and file path
                $sql = "INSERT INTO receipts (bill_id, user_id, name, receipt_file, remarks) 
                        VALUES (:bill_id, :user_id, :name, :receipt_file, :remarks)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':bill_id', $bill_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->bindParam(':name', $user_name, PDO::PARAM_STR);  // Store user name in the receipts table
                $stmt->bindParam(':receipt_file', $file_name, PDO::PARAM_STR); // Store file name, not the full path
                $stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
                $stmt->execute();

                // Update the status of the bill to pending
                $update_sql = "UPDATE bills SET status = 'pending' WHERE id = :bill_id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindParam(':bill_id', $bill_id, PDO::PARAM_INT);
                $update_stmt->execute();

                $conn->commit();
                $alert_message = 'success'; // Success message for SweetAlert

            } catch (PDOException $e) {
                $conn->rollBack();
                $alert_message = 'error';
                error_log("Error inserting receipt: " . $e->getMessage());
            }
        } else {
            $alert_message = 'error_upload';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attach Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'components/user_header.php'; ?>
    <br><br><br><br>
    <section class="payment-form">
        <h1 class="heading">Attach Receipt</h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>">
            <label for="receipt_file">Choose Receipt File</label>
            <input type="file" name="receipt_file" required><br>

            <label for="remarks">Remarks (Optional)</label>
            <textarea name="remarks" rows="4"></textarea><br>

            <button type="submit" name="submit_payment">Submit Payment</button>
        </form>
    </section>

    <?php if ($alert_message === 'success'): ?>
        <script>
            Swal.fire('Success', 'Payment submitted successfully.', 'success');
        </script>
    <?php elseif ($alert_message === 'error_upload'): ?>
        <script>
            Swal.fire('Error', 'Failed to upload receipt.', 'error');
        </script>
    <?php elseif ($alert_message === 'error_invalid_file'): ?>
        <script>
            Swal.fire('Error', 'Invalid file type.', 'error');
        </script>
    <?php elseif ($alert_message === 'error'): ?>
        <script>
            Swal.fire('Error', 'Something went wrong.', 'error');
        </script>
    <?php endif; ?>
    <br><br><br><br><br><br><br><br><br><br><br><br>
    <?php include 'components/footer.php';?>
</body>

</html>

<?php
$stmt = null;
$conn = null;
?>
