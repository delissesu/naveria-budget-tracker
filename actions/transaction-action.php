<?php
/**
 * 
 * @version 1.0
 */

header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Routing berdasarkan parameter action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

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

/**
 * Menambah transaksi baru ke database
 * 
 * @param PDO $db Koneksi database
 * @return void Outputs JSON {success: boolean, message: string, id: number}
 */
function createTransaction($db): void {
    // Validasi dan sanitasi input
    $category_id = htmlspecialchars(strip_tags($_POST['category_id']));
    $amount = htmlspecialchars(strip_tags($_POST['amount']));
    $description = htmlspecialchars(strip_tags($_POST['description']));
    $transaction_date = htmlspecialchars(strip_tags($_POST['transaction_date']));
    
    // Insert data dan return ID transaksi yang baru dibuat
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

/**
 * Mengambil data transaksi dari database
 * Bisa untuk satu transaksi (dengan id) atau semua transaksi
 * 
 * @param PDO $db Koneksi database
 * @return void Outputs JSON {success: boolean, data: array}
 */
function readTransactions($db): void {
    $id = $_GET['id'] ?? null;
    
    if($id) {
        // Ambil satu transaksi berdasarkan ID dengan JOIN ke categories
        $query = "SELECT t.id, t.category_id, t.amount, t.description, 
                         t.transaction_date, t.created_at, c.name as category_name 
                  FROM transactions t 
                  LEFT JOIN categories c ON t.category_id = c.id 
                  WHERE t.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        // Ambil semua transaksi, diurutkan dari yang terbaru
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

/**
 * Mengupdate data transaksi yang sudah ada
 * 
 * @param PDO $db Koneksi database
 * @return void Outputs JSON {success: boolean, message: string}
 */
function updateTransaction($db): void {
    // Validasi dan sanitasi input
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

/**
 * Menghapus transaksi dari database
 * 
 * @param PDO $db Koneksi database
 * @return void Outputs JSON {success: boolean, message: string}
 */
function deleteTransaction($db): void {
    // Validasi dan sanitasi input
    $id = htmlspecialchars(strip_tags($_POST['id']));
    
    // Query untuk menghapus transaksi berdasarkan ID
    $query = "DELETE FROM transactions WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        // Cek apakah ada data yang terhapus
        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi']);
    }
}