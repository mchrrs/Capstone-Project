<?php
include 'components/connect.php';

if (isset($_GET['conversation_id'])) {
    $conversation_id = $_GET['conversation_id'];

    // Fetch the latest messages for this conversation (messages sent by admins)
    $query = "SELECT m.*, a.name AS sender_name 
              FROM messages m 
              LEFT JOIN admins a ON m.sender_id = a.id
              WHERE m.conversation_id = :conversation_id 
              ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute(['conversation_id' => $conversation_id]);
    $messages = $stmt->fetchAll();

    // Return the messages as JSON
    echo json_encode($messages);
    exit;
}
