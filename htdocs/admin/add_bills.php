<?php
include '../components/connect.php';

// Check if admin is logged in
if (!isset($_COOKIE['admin_id'])) {
    header('Location: index.php');
    exit;
}

$admin_id = $_COOKIE['admin_id'];

// Fetch users (tenants) for dropdown selection
$select_users = $conn->prepare("SELECT id, name, number FROM users");
$select_users->execute();
$users = $select_users->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding a bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bill'])) {
    $tenant_id = $_POST['tenant_id'];
    $bill_type = $_POST['bill_type'];
    $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $due_date = $_POST['due_date'];

    if (empty($tenant_id) || empty($bill_type) || empty($amount) || empty($due_date)) {
        $message = "Please select a tenant, bill type, and due date.";
        $message_type = "error";
    } elseif ($amount <= 0) {
        $message = "Amount must be greater than zero.";
        $message_type = "error";
    } else {
        try {
            $query = "INSERT INTO bills (user_id, bill_type, amount, due_date, status) 
                      VALUES (:user_id, :bill_type, :amount, :due_date, 'pending')";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':user_id' => $tenant_id,
                ':bill_type' => $bill_type,
                ':amount' => $amount,
                ':due_date' => $due_date
            ]);

            $message = "Bill added successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bill</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <?php include '../components/admin_header.php'; ?>
    
    <section class="add-bills">
        <h1 class="heading">Manage Bills</h1>

        <form class="add-bills-form" action="" method="POST">
            <div class="form-group">
                <label for="tenant_id">Select Tenant:</label>
                <select name="tenant_id" id="tenant_id" required>
                    <option value="" disabled selected>Select a tenant</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['name']); ?> (<?= htmlspecialchars($user['number']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="bill_type">Select Bill Type:</label>
                <select name="bill_type" id="bill_type" required>
                    <option value="" disabled selected>Select a bill type</option>
                    <option value="house_rent">House Rent</option>
                    <option value="water_bill">Water Bill</option>
                    <option value="electricity_bill">Electricity Bill</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" min="0" required>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="date" name="due_date" required>
            </div>

            <button type="submit" name="add_bill">Add Bill</a></button>
        </form>
    </section>

    <?php if (isset($message)): ?>
        <script>
            Swal.fire({
                icon: '<?= $message_type; ?>',
                title: '<?= htmlspecialchars($message); ?>',
                showConfirmButton: true,
            });
        </script>
    <?php endif; ?>

    <?php include '../components/message.php'; ?>
</body>
</html>
