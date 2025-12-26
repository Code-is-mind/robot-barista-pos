<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$orderId = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? 'Paid';
$transactionData = $_POST['transaction_data'] ?? null;

if ($orderId) {
    try {
        // Get current notes
        $sql = "SELECT notes FROM orders WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $orderId]);
        $currentNotes = $stmt->fetchColumn() ?: '';
        
        // Add transaction info to notes if available
        if ($transactionData && is_string($transactionData)) {
            $txData = json_decode($transactionData, true);
            if ($txData && isset($txData['data'])) {
                $txInfo = $txData['data'];
                $additionalNote = "\nTransaction ID: " . ($txInfo['transactionId'] ?? 'N/A');
                $additionalNote .= " | Bank: " . ($txInfo['bankName'] ?? 'N/A');
                $additionalNote .= " | Time: " . date('Y-m-d H:i:s');
                $currentNotes .= $additionalNote;
            }
        }
        
        $sql = "UPDATE orders SET payment_status = :status, order_status = 'Preparing', notes = :notes WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':notes' => $currentNotes,
            ':id' => $orderId
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Order ID required']);
}
