<?php
/**
 * - Total Pemasukan (income)
 * - Total Pengeluaran (expense)
 * - Total Anggaran (budget)
 * - 5 Transaksi Terakhir
 * 
 * @version 1.0
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Inisialisasi koneksi database
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Hitung total pemasukan dari transaksi dengan kategori income
    // COALESCE mengembalikan 0 jika SUM menghasilkan NULL
    $queryIncome = "SELECT COALESCE(SUM(t.amount), 0) as total 
                    FROM transactions t 
                    LEFT JOIN categories c ON t.category_id = c.id 
                    WHERE c.type = 'income'::category_type";
    $stmtIncome = $db->prepare($queryIncome);
    $stmtIncome->execute();
    $totalIncome = $stmtIncome->fetch(PDO::FETCH_ASSOC)['total'];

    // Hitung total pengeluaran dari transaksi dengan kategori expense
    $queryExpense = "SELECT COALESCE(SUM(t.amount), 0) as total 
                     FROM transactions t 
                     LEFT JOIN categories c ON t.category_id = c.id 
                     WHERE c.type = 'expense'::category_type";
    $stmtExpense = $db->prepare($queryExpense);
    $stmtExpense->execute();
    $totalExpense = $stmtExpense->fetch(PDO::FETCH_ASSOC)['total'];

    // Hitung total anggaran dari semua budget
    $queryBudget = "SELECT COALESCE(SUM(amount), 0) as total FROM budgets";
    $stmtBudget = $db->prepare($queryBudget);
    $stmtBudget->execute();
    $totalBudget = $stmtBudget->fetch(PDO::FETCH_ASSOC)['total'];

    // Ambil 5 transaksi terakhir untuk ditampilkan di dashboard
    // Diurutkan berdasarkan tanggal transaksi dan waktu dibuat (terbaru dulu)
    $queryRecent = "SELECT t.id, t.amount, t.description, t.transaction_date, 
                           t.created_at, c.name as category_name 
                    FROM transactions t 
                    LEFT JOIN categories c ON t.category_id = c.id 
                    ORDER BY t.transaction_date DESC, t.created_at DESC 
                    LIMIT 5";
    $stmtRecent = $db->prepare($queryRecent);
    $stmtRecent->execute();
    $recentTransactions = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'data' => [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_budget' => $totalBudget,
            'recent_transactions' => $recentTransactions
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => true
    ]);
}
?>