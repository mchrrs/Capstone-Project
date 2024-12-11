<?php
include '../components/connect.php';

if (isset($_GET['conversation_id'])) {
    $conversation_id = $_GET['conversation_id'];

    // Fetch the latest messages
    $query = "SELECT m.*, u.name AS sender_name 
              FROM messages m 
              LEFT JOIN users u ON m.sender_id = u.id
              WHERE m.conversation_id = :conversation_id 
              ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute(['conversation_id' => $conversation_id]);
    $messages = $stmt->fetchAll();

    // Return the messages as JSON
    echo json_encode($messages);
    exit;
}
