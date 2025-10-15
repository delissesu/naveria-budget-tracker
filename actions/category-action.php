<?php
/**
 * Category Action Handler
 * File ini menangani semua operasi CRUD untuk kategori
 * 
 * Operasi yang tersedia:
 * - create: Menambah kategori baru
 * - read: Membaca data kategori (semua atau berdasarkan ID)
 * - update: Mengupdate data kategori
 * - delete: Menghapus kategori
 * 
 * @author Naveria Budget Tracker Team
 * @version 2.0
 */

// Set response header ke JSON
header('Content-Type: application/json');
require_once '../config/database.php';

// Inisialisasi koneksi database
$database = new Database();
$db = $database->getConnection();

// Ambil action dari request (GET atau POST)
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Routing ke fungsi yang sesuai berdasarkan action
switch($action) {
    case 'create':
        createCategory($db);
        break;
    case 'read':
        readCategories($db);
        break;
    case 'update':
        updateCategory($db);
        break;
    case 'delete':
        deleteCategory($db);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}

/**
 * Membuat kategori baru
 * 
 * Input yang dibutuhkan (POST):
 * - name: Nama kategori
 * - type: Tipe kategori (income/expense)
 * - icon: Icon emoji untuk kategori
 * 
 * @param PDO $db Koneksi database
 * @return JSON Response dengan status dan ID kategori baru
 */
function createCategory($db) {
    // Sanitasi input untuk keamanan
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $type = htmlspecialchars(strip_tags($_POST['type']));
    $icon = htmlspecialchars(strip_tags($_POST['icon']));
    
    // Query dengan RETURNING untuk mendapatkan ID yang baru dibuat
    $query = "INSERT INTO categories (name, type, icon) 
              VALUES (:name, :type::category_type, :icon) 
              RETURNING id";
    $stmt = $db->prepare($query);
    
    // Bind parameter untuk mencegah SQL injection
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':icon', $icon);
    
    if($stmt->execute()) {
        $id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        echo json_encode([
            'success' => true, 
            'message' => 'Kategori berhasil ditambahkan',
            'id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan kategori']);
    }
}

/**
 * Membaca data kategori dari database
 * 
 * Jika parameter ID diberikan, akan mengembalikan satu kategori
 * Jika tidak, akan mengembalikan semua kategori
 * 
 * Input (GET, opsional):
 * - id: ID kategori yang ingin dibaca
 * 
 * @param PDO $db Koneksi database
 * @return JSON Array kategori atau kategori tunggal
 */
function readCategories($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if($id) {
        // Query untuk satu kategori berdasarkan ID
        $query = "SELECT id, name, type::text, icon, created_at 
                  FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        // Query untuk semua kategori, diurutkan dari yang terbaru
        $query = "SELECT id, name, type::text, icon, created_at 
                  FROM categories ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $categories]);
}

/**
 * Mengupdate data kategori yang sudah ada
 * 
 * Input yang dibutuhkan (POST):
 * - id: ID kategori yang akan diupdate
 * - name: Nama kategori baru
 * - type: Tipe kategori baru (income/expense)
 * - icon: Icon baru untuk kategori
 * 
 * @param PDO $db Koneksi database
 * @return JSON Response dengan status operasi
 */
function updateCategory($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $type = htmlspecialchars(strip_tags($_POST['type']));
    $icon = htmlspecialchars(strip_tags($_POST['icon']));
    
    $query = "UPDATE categories 
              SET name = :name, type = :type::category_type, icon = :icon 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':icon', $icon);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Kategori berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate kategori']);
    }
}

/**
 * Menghapus kategori dari database
 * 
 * Menggunakan try-catch untuk menangani error foreign key constraint
 * karena kategori yang memiliki transaksi tidak bisa dihapus
 * 
 * @param PDO $db Koneksi database
 * @return JSON {success: boolean, message: string}
 */
function deleteCategory($db) {
    try {
        // Validasi dan sanitasi input
        $id = htmlspecialchars(strip_tags($_POST['id']));
        
        // Query untuk menghapus kategori berdasarkan ID
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            // Cek apakah ada data yang terhapus
            if($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kategori tidak ditemukan']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus kategori']);
        }
    } catch(PDOException $e) {
        // Tangani error khususnya untuk foreign key constraint
        // Jika kategori masih digunakan di transaksi, akan muncul error
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>