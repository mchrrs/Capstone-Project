<?php
include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:login.php');
    exit;
}

try {
    $sql = "SELECT 
                b.id, 
                b.property_id, 
                b.bill_type, 
                b.amount, 
                b.total, 
                b.due_date, 
                b.status 
            FROM bills b 
            WHERE b.user_id = :user_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching bills: " . $e->getMessage());
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bills</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'components/user_header.php'; ?>
    <br><br><br><br><br><br>
    <section class="bill-table-container">
        <h1 class="heading">My Bills</h1>

        <table class="bill-table">
            <thead>
                <tr>
                    <th>Bill Type</th>
                    <th>Amount</th>
                    <th>Total</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bills)): ?>
                    <?php foreach ($bills as $bill): ?>
                        <tr>
                            <td><?php echo ucfirst(str_replace('_', ' ', $bill['bill_type'])); ?></td>
                            <td>₱<?php echo number_format($bill['amount'], 2); ?></td>
                            <td>₱<?php echo number_format($bill['total'], 2); ?></td>
                            <td><?php echo date('F j, Y', strtotime($bill['due_date'])); ?></td>
                            <td><span class="status <?php echo strtolower($bill['status']); ?>"><?php echo ucfirst($bill['status']); ?></span></td>
                            <td><?php if ($bill['status'] === 'pending'): ?>
                                    <a href="payment.php?bill_id=<?php echo $bill['id']; ?>" class="btn btn-primary">Pay Here</a>
                                <?php else: ?>
                                    <span class="no-action">No actions available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No bills available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <a href="attach_receipts.php" style="font-style: italic;" class="btn btn-primary">Attach your receipts here</a>
    </section>
    <br><br><br><br><br><br><br><br><br>
    <?php include '../htdocs/components/footer.php';?>

</body>

</html>

<?php
$stmt = null;
$conn = null;
?>
