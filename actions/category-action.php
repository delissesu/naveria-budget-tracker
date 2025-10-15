<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

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
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createCategory($db) {
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $type = htmlspecialchars(strip_tags($_POST['type']));
    $icon = htmlspecialchars(strip_tags($_POST['icon']));
    
    // PostgreSQL: Use RETURNING clause to get inserted ID
    $query = "INSERT INTO categories (name, type, icon) 
              VALUES (:name, :type::category_type, :icon) 
              RETURNING id";
    $stmt = $db->prepare($query);
    
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

function readCategories($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if($id) {
        $query = "SELECT id, name, type::text, icon, created_at 
                  FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        $query = "SELECT id, name, type::text, icon, created_at 
                  FROM categories ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $categories]);
}

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

function deleteCategory($db) {
    $id = htmlspecialchars(strip_tags($_POST['id']));
    
    $query = "DELETE FROM categories WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        if($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kategori tidak ditemukan']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus kategori']);
    }
}
?>