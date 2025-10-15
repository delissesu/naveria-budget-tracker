<?php
/**
 * - create: Tambah budget baru
 * - read: Ambil data budget
 * - update: Update data budget
 * - delete: Hapus budget
 * 
 * @version 1.0
 */

header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Routing berdasarkan parameter action
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

/**
 * Menambah budget baru ke database
 * Period menggunakan ENUM: 'monthly' atau 'yearly'
 * 
 * @param PDO $db Koneksi database
 * @return JSON {success: boolean, message: string, id: number}
 */
function createBudget($db) {
    // Validasi dan sanitasi input
    $category_id = htmlspecialchars(strip_tags($_POST['category_id']));
    $amount = htmlspecialchars(strip_tags($_POST['amount']));
    $period = htmlspecialchars(strip_tags($_POST['period']));
    $start_date = htmlspecialchars(strip_tags($_POST['start_date']));
    $end_date = htmlspecialchars(strip_tags($_POST['end_date']));
    
    // Insert data dengan cast ENUM untuk period
    $query = "INSERT INTO budgets (category_id, amount, period, start_date, end_date) 
              VALUES (:category_id, :amount, :period::budget_period, :start_date, :end_date)
              RETURNING id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    
    if($stmt->execute()) {
        $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        echo json_encode([
            'success' => true, 
            'message' => 'Anggaran berhasil ditambahkan',
            'id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan anggaran']);
    }
}

function readBudgets($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if($id) {
        $query = "SELECT b.id, b.category_id, b.amount, b.period::text, 
                         b.start_date, b.end_date, b.created_at, c.name as category_name 
                  FROM budgets b 
                  LEFT JOIN categories c ON b.category_id = c.id 
                  WHERE b.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        $query = "SELECT b.id, b.category_id, b.amount, b.period::text, 
                         b.start_date, b.end_date, b.created_at, c.name as category_name 
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
              SET category_id = :category_id, amount = :amount, 
                  period = :period::budget_period, start_date = :start_date, 
                  end_date = :end_date 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    
    if($stmt->execute()) {
        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Anggaran berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate anggaran']);
    }
}

function deleteBudget($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    
    $query = "DELETE FROM budgets WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Anggaran berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Anggaran tidak ditemukan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus anggaran']);
    }
}
?>