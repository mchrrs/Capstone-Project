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

// Fetch all bills with tenant details
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$tenant_id_filter = isset($_GET['tenant_id']) ? $_GET['tenant_id'] : '';
$bill_type_filter = isset($_GET['bill_type']) ? $_GET['bill_type'] : ''; // Filter by bill type

// Start the base query for fetching bills
$query = "SELECT bills.id, users.name, users.number, bills.bill_type, bills.amount, bills.due_date, bills.status
          FROM bills 
          JOIN users ON bills.user_id = users.id";

// Handle filters
$conditions = [];
$params = [];

if ($tenant_id_filter) {
    $conditions[] = "bills.user_id = :tenant_id";
    $params[':tenant_id'] = $tenant_id_filter;
}
if ($status_filter) {
    $conditions[] = "bills.status = :status";
    $params[':status'] = $status_filter;
}
if ($bill_type_filter) {
    $conditions[] = "bills.bill_type = :bill_type";
    $params[':bill_type'] = $bill_type_filter;
}

// Combine conditions with AND
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY bills.due_date ASC";

// Execute the query with the parameters
$select_bills = $conn->prepare($query);
$select_bills->execute($params);
$bills = $select_bills->fetchAll(PDO::FETCH_ASSOC);

// Handle status update (for AJAX)
if (isset($_POST['action']) && isset($_POST['bill_id'])) {
    $bill_id = $_POST['bill_id'];
    $action = $_POST['action'];
    $new_status = ($action === 'approve') ? 'paid' : 'pending';

    $update_status = $conn->prepare("UPDATE bills SET status = :status WHERE id = :id");
    $update_status->execute([':status' => $new_status, ':id' => $bill_id]);

    // Return the updated status as a response
    echo json_encode(['status' => $new_status]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bills</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include '../components/admin_header.php'; ?>
    <section class="table">
        <h2 class="heading">Bill Payment Status</h2>

        <!-- Filter forms for tenant, status, and bill type -->
        <form method="GET" class="filter-form">
            <label for="tenant_id">Select Tenant:</label>
            <select name="tenant_id" id="tenant_id">
                <option value="">All Tenants</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id']; ?>" <?= $tenant_id_filter == $user['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($user['name']); ?> (<?= htmlspecialchars($user['number']); ?>)</option>
                <?php endforeach; ?>
            </select>

            <label for="bill_type">Filter by Bill Type:</label>
            <select name="bill_type" id="bill_type">
                <option value="">All Bill Types</option>
                <option value="house_rent" <?= $bill_type_filter === 'house_rent' ? 'selected' : '' ?>>House Rent</option>
                <option value="water_bill" <?= $bill_type_filter === 'water_bill' ? 'selected' : '' ?>>Water Bill</option>
                <option value="electricity_bill" <?= $bill_type_filter === 'electricity_bill' ? 'selected' : '' ?>>Electricity Bill</option>
            </select>

            <label for="status">Filter by Status:</label>
            <select name="status" id="status">
                <option value="">All</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="overdue" <?= $status_filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
            </select>

            <!-- Filter Button with Font Awesome Icon -->
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i>
            </button>
            <a href="add_bills.php" style="background-color: rgb(46, 204, 113);" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
        </form>

        <div class="bill-table-wrapper">
            <table class="bill-table">
                <thead>
                    <tr>
                        <th>Tenant Name</th>
                        <th>Tenant Number</th>
                        <th>Bill Type</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bills) > 0): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr id="bill-<?= $bill['id']; ?>">
                                <td><?= htmlspecialchars($bill['name']); ?></td>
                                <td><?= htmlspecialchars($bill['number']); ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $bill['bill_type'])); ?></td>
                                <td>₱<?= number_format($bill['amount'], 2); ?></td>
                                <td><?= htmlspecialchars($bill['due_date']); ?></td>
                                <td><span class="status <?= strtolower($bill['status']); ?>" id="status-<?= $bill['id']; ?>"><?= ucfirst($bill['status']); ?></span></td>
                                <td>
                                    <?php if ($bill['status'] === 'pending'): ?>
                                        <form method="POST" class="status-update-form" data-bill-id="<?= $bill['id']; ?>" style="display:inline;">
                                            <input type="hidden" name="bill_id" value="<?= $bill['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success"><i class="fas fa-check"></i></button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger"><i class="fas fa-times"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="no-action">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No bills available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="validate_payment.php" class="btn btn primary">Verify Payment</a>
    </section>

    <script>
        $(document).ready(function() {
            $('.status-update-form').submit(function(e) {
                e.preventDefault(); // Prevent form from submitting normally
                var form = $(this);
                var bill_id = form.data('bill-id');
                var action = form.find('button[name="action"]:focus').val();

                $.ajax({
                    type: 'POST',
                    url: '', // The current page
                    data: {
                        bill_id: bill_id,
                        action: action
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status) {
                            $('#status-' + bill_id).text(data.status).removeClass().addClass('status ' + data.status);
                        }
                    },
                    error: function() {
                        alert("Error updating status.");
                    }
                });
            });
        });
    </script>
</body>

</html>
