<?php 
/**
 * Halaman Dashboard
 * 
 * Menampilkan ringkasan keuangan:
 * - Total pemasukan
 * - Total pengeluaran  
 * - Total anggaran
 * - 5 transaksi terakhir
 * 
 * Data dimuat secara dinamis menggunakan AJAX
 */
include '../includes/header.php'; 
?>

<div class="container">
    <h1>📊 Dashboard</h1>
    
    <!-- Kartu statistik ringkasan -->
    <div class="dashboard-cards">
        <div class="stat-card">
            <div class="stat-icon income">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-info">
                <h3>Total Pemasukan</h3>
                <p id="totalIncome">Rp 0</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon expense">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <h3>Total Pengeluaran</h3>
                <p id="totalExpense">Rp 0</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon budget">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-info">
                <h3>Total Anggaran</h3>
                <p id="totalBudget">Rp 0</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Transaksi Terbaru</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody id="recentTransactionsBody">
                    <tr>
                        <td colspan="4" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    loadDashboard();
});
</script>