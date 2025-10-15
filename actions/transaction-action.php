<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch($action) {
    case 'create':
        createTransaction($db);
        break;
    case 'read':
        readTransactions($db);
        break;
    case 'update':
        updateTransaction($db);
        break;
    case 'delete':
        deleteTransaction($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createTransaction($db) {
    $category_id = htmlspecialchars(strip_tags($_POST['category_id']));
    $amount = htmlspecialchars(strip_tags($_POST['amount']));
    $description = htmlspecialchars(strip_tags($_POST['description']));
    $transaction_date = htmlspecialchars(strip_tags($_POST['transaction_date']));
    
    $query = "INSERT INTO transactions (category_id, amount, description, transaction_date) 
              VALUES (:category_id, :amount, :description, :transaction_date)
              RETURNING id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':transaction_date', $transaction_date);
    
    if($stmt->execute()) {
        $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        echo json_encode([
            'success' => true, 
            'message' => 'Transaksi berhasil ditambahkan',
            'id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan transaksi']);
    }
}

function readTransactions($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if($id) {
        $query = "SELECT t.id, t.category_id, t.amount, t.description, 
                         t.transaction_date, t.created_at, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  WHERE t.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        $query = "SELECT t.id, t.category_id, t.amount, t.description, 
                         t.transaction_date, t.created_at, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  ORDER BY t.transaction_date DESC, t.created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $transactions]);
}

function updateTransaction($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    $category_id = htmlspecialchars(strip_tags($_POST['category_id']));
    $amount = htmlspecialchars(strip_tags($_POST['amount']));
    $description = htmlspecialchars(strip_tags($_POST['description']));
    $transaction_date = htmlspecialchars(strip_tags($_POST['transaction_date']));
    
    $query = "UPDATE transactions 
              SET category_id = :category_id, amount = :amount, 
                  description = :description, transaction_date = :transaction_date 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':transaction_date', $transaction_date);
    
    if($stmt->execute()) {
        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaksi berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate transaksi']);
    }
}

function deleteTransaction($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    
    $query = "DELETE FROM transactions WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi']);
    }
}
?>