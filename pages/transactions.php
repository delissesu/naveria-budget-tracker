<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Manajemen Transaksi</h2>
            <button class="btn btn-primary" onclick="openModal('transactionModal'); $('#transactionModalTitle').text('Tambah Transaksi'); $('#transactionForm')[0].reset(); $('#transactionId').val(''); loadCategoryOptions();">
                <i class="fas fa-plus"></i> Tambah Transaksi
            </button>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="transactionTableBody">
                    <tr>
                        <td colspan="6" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Transaction -->
<div id="transactionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="transactionModalTitle">Tambah Transaksi</h3>
            <button class="close-modal" onclick="closeModal('transactionModal')">&times;</button>
        </div>
        <form id="transactionForm" onsubmit="event.preventDefault(); saveTransaction();">
            <input type="hidden" id="transactionId">
            
            <div class="form-group">
                <label for="transactionCategory">Kategori *</label>
                <select class="form-control" id="transactionCategory" required>
                    <option value="">Pilih Kategori</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="transactionAmount">Jumlah (Rp) *</label>
                <input type="number" class="form-control" id="transactionAmount" required min="0" step="0.01">
            </div>
            
            <div class="form-group">
                <label for="transactionDate">Tanggal *</label>
                <input type="date" class="form-control" id="transactionDate" required>
            </div>
            
            <div class="form-group">
                <label for="transactionDescription">Deskripsi</label>
                <textarea class="form-control" id="transactionDescription" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-danger" onclick="closeModal('transactionModal')">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    loadTransactions();
    
    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    $('#transactionDate').val(today);
});
</script>