<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch($action) {
    case 'create':
        createBudget($db);
        break;
    case 'read':
        readBudgets($db);
        break;
    case 'update':
        updateBudget($db);
        break;
    case 'delete':
        deleteBudget($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createBudget($db) {
    $category_id = htmlspecialchars(strip_tags($_POST['category_id']));
    $amount = htmlspecialchars(strip_tags($_POST['amount']));
    $period = htmlspecialchars(strip_tags($_POST['period']));
    $start_date = htmlspecialchars(strip_tags($_POST['start_date']));
    $end_date = htmlspecialchars(strip_tags($_POST['end_date']));
    
    $query = "INSERT INTO budgets (category_id, amount, period, start_date, end_date) 
              VALUES (:category_id, :amount, :period, :start_date, :end_date)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Anggaran berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan anggaran']);
    }
}

function readBudgets($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if($id) {
        $query = "SELECT b.*, c.name as category_name 
                  FROM budgets b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  WHERE b.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
    } else {
        $query = "SELECT b.*, c.name as category_name 
                  FROM budgets b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  ORDER BY b.created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $budgets]);
}

function updateBudget($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    $category_id = htmlspecialchars(strip_tags($_POST['category_id']));
    $amount = htmlspecialchars(strip_tags($_POST['amount']));
    $period = htmlspecialchars(strip_tags($_POST['period']));
    $start_date = htmlspecialchars(strip_tags($_POST['start_date']));
    $end_date = htmlspecialchars(strip_tags($_POST['end_date']));
    
    $query = "UPDATE budgets 
              SET category_id = :category_id, amount = :amount, period = :period, 
                  start_date = :start_date, end_date = :end_date 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Anggaran berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate anggaran']);
    }
}

function deleteBudget($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    
    $query = "DELETE FROM budgets WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Anggaran berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus anggaran']);
    }
}
?>