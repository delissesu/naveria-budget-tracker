<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Manajemen Anggaran</h2>
            <button class="btn btn-primary" onclick="openModal('budgetModal'); $('#budgetModalTitle').text('Tambah Anggaran'); $('#budgetForm')[0].reset(); $('#budgetId').val(''); loadCategoryOptions();">
                <i class="fas fa-plus"></i> Tambah Anggaran
            </button>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Periode</th>
                        <th>Mulai</th>
                        <th>Berakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="budgetTableBody">
                    <tr>
                        <td colspan="7" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Budget -->
<div id="budgetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="budgetModalTitle">Tambah Anggaran</h3>
            <button class="close-modal" onclick="closeModal('budgetModal')">&times;</button>
        </div>
        <form id="budgetForm" onsubmit="event.preventDefault(); saveBudget();">
            <input type="hidden" id="budgetId">
            
            <div class="form-group">
                <label for="budgetCategory">Kategori *</label>
                <select class="form-control" id="budgetCategory" required>
                    <option value="">Pilih Kategori</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="budgetAmount">Jumlah (Rp) *</label>
                <input type="number" class="form-control" id="budgetAmount" required min="0" step="0.01">
            </div>
            
            <div class="form-group">
                <label for="budgetPeriod">Periode *</label>
                <select class="form-control" id="budgetPeriod" required>
                    <option value="">Pilih Periode</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="budgetStartDate">Tanggal Mulai *</label>
                <input type="date" class="form-control" id="budgetStartDate" required>
            </div>
            
            <div class="form-group">
                <label for="budgetEndDate">Tanggal Berakhir *</label>
                <input type="date" class="form-control" id="budgetEndDate" required>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-danger" onclick="closeModal('budgetModal')">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    loadBudgets();
});
</script>

<?php include 'includes/footer.php'; ?>