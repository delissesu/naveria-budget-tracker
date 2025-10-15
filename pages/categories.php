<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Manajemen Kategori</h2>
            <button class="btn btn-primary" onclick="openModal('categoryModal'); $('#modalTitle').text('Tambah Kategori'); $('#categoryForm')[0].reset(); $('#categoryId').val('');">
                <i class="fas fa-plus"></i> Tambah Kategori
            </button>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    <tr>
                        <td colspan="5" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Category -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Kategori</h3>
            <button class="close-modal" onclick="closeModal('categoryModal')">&times;</button>
        </div>
        <form id="categoryForm" onsubmit="event.preventDefault(); saveCategory();">
            <input type="hidden" id="categoryId">
            
            <div class="form-group">
                <label for="categoryName">Nama Kategori *</label>
                <input type="text" class="form-control" id="categoryName" required>
            </div>
            
            <div class="form-group">
                <label for="categoryType">Tipe *</label>
                <select class="form-control" id="categoryType" required>
                    <option value="">Pilih Tipe</option>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="categoryIcon">Icon (Emoji)</label>
                <input type="text" class="form-control" id="categoryIcon" placeholder="💰">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-danger" onclick="closeModal('categoryModal')">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    loadCategories();
});
</script>

<?php include 'includes/footer.php'; ?>