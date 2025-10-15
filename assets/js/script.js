/**
 * Menampilkan pesan alert kepada pengguna
 * Alert akan muncul di bagian atas container dan otomatis hilang setelah 3 detik
 * 
 * @param {string} message - Pesan yang akan ditampilkan
 * @param {string} type - Tipe alert (success, danger, warning, info)
 */
function showAlert(message, type = 'success') {
    // Buat elemen alert dengan template string
    const alertDiv = $(`
        <div class="alert alert-${type} show">
            ${message}
        </div>
    `);
    
    // Tambahkan alert ke awal container
    $('.container').first().prepend(alertDiv);
    
    // Hapus alert setelah 3 detik dengan animasi fade
    setTimeout(() => {
        alertDiv.fadeOut(() => alertDiv.remove());
    }, 3000);
}

/**
 * Format angka ke format Indonesia dengan pemisah ribuan
 * Mengkonversi string atau number menjadi format yang mudah dibaca
 * Contoh: 1000000 -> 1.000.000
 * 
 * @param {number|string} num - Angka yang akan diformat
 * @returns {string} Angka yang sudah diformat dengan locale Indonesia
 */
function formatNumber(num) {
    // Konversi ke number terlebih dahulu, default 0 jika gagal
    const number = parseFloat(num) || 0;
    return new Intl.NumberFormat('id-ID').format(number);
}

/**
 * Format tanggal ke format Indonesia yang mudah dibaca
 * Contoh: 2025-10-15 -> 15 Oktober 2025
 * 
 * @param {string} dateString - String tanggal yang akan diformat
 * @returns {string} Tanggal dalam format Indonesia
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

/**
 * Memuat semua kategori dari database dan menampilkannya
 * Fungsi ini dipanggil saat halaman kategori pertama kali dimuat
 * dan setelah operasi create/update/delete
 */
function loadCategories() {
    $.ajax({
        url: '../actions/category-action.php?action=read',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayCategories(response.data);
            }
        },
        error: function() {
            showAlert('Gagal memuat data kategori', 'danger');
        }
    });
}

/**
 * Menampilkan data kategori ke dalam tabel
 * Setiap baris dilengkapi dengan tombol Edit dan Hapus
 * 
 * @param {Array} categories - Array objek kategori dari database
 */
function displayCategories(categories) {
    let html = '';
    
    // Loop setiap kategori dan buat baris tabel
    categories.forEach(cat => {
        // Tentukan class badge berdasarkan tipe kategori
        const typeClass = cat.type === 'income' ? 'badge-income' : 'badge-expense';
        const typeLabel = cat.type === 'income' ? 'Pemasukan' : 'Pengeluaran';
        
        html += `
            <tr>
                <td>${cat.id}</td>
                <td>${cat.icon} ${cat.name}</td>
                <td><span class="badge ${typeClass}">${typeLabel}</span></td>
                <td>${formatDate(cat.created_at)}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editCategory(${cat.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteCategory(${cat.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        `;
    });
    
    // Update konten tabel
    $('#categoryTableBody').html(html);
}

/**
 * Menyimpan kategori baru atau update kategori yang sudah ada
 * Fungsi ini otomatis mendeteksi apakah mode create atau update
 * berdasarkan keberadaan categoryId
 */
function saveCategory() {
    // Ambil ID kategori, jika ada berarti mode update
    const id = $('#categoryId').val();
    const action = id ? 'update' : 'create';
    
    // Kumpulkan data dari form
    const formData = {
        action: action,
        id: id,
        name: $('#categoryName').val(),
        type: $('#categoryType').val(),
        icon: $('#categoryIcon').val()
    };
    
    // Kirim request AJAX ke server
    $.ajax({
        url: '../actions/category-action.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeModal('categoryModal'); // Tutup modal
                loadCategories(); // Reload data tabel
                $('#categoryForm')[0].reset(); // Reset form
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Terjadi kesalahan saat menyimpan data', 'danger');
        }
    });
}

/**
 * Memuat data kategori untuk diedit
 * Data yang dimuat akan mengisi form di modal edit
 * 
 * @param {number} id - ID kategori yang akan diedit
 */
function editCategory(id) {
    $.ajax({
        url: `../actions/category-action.php?action=read&id=${id}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const cat = response.data[0];
                $('#categoryId').val(cat.id);
                $('#categoryName').val(cat.name);
                $('#categoryType').val(cat.type);
                $('#categoryIcon').val(cat.icon);
                $('#modalTitle').text('Edit Kategori');
                openModal('categoryModal');
            }
        }
    });
}

/**
 * Menghapus kategori dari database dengan konfirmasi
 * Setelah berhasil dihapus, tabel akan di-reload otomatis
 * 
 * @param {number} id - ID kategori yang akan dihapus
 */
function deleteCategory(id) {
    if (confirm('Yakin ingin menghapus kategori ini?')) {
        $.ajax({
            url: '../actions/category-action.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    loadCategories();
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAlert('Gagal menghapus kategori', 'danger');
            }
        });
    }
}

/**
 * Memuat semua transaksi dari database
 * Data transaksi termasuk informasi kategori melalui JOIN
 */
function loadTransactions() {
    $.ajax({
        url: '../actions/transaction-action.php?action=read',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayTransactions(response.data);
            }
        },
        error: function() {
            showAlert('Gagal memuat data transaksi', 'danger');
        }
    });
}

/**
 * Menampilkan data transaksi ke dalam tabel
 * Dilengkapi dengan format tanggal dan nominal rupiah
 * 
 * @param {Array} transactions - Array objek transaksi dari database
 */
function displayTransactions(transactions) {
    let html = '';
    transactions.forEach(trans => {
        html += `
            <tr>
                <td>${trans.id}</td>
                <td>${formatDate(trans.transaction_date)}</td>
                <td>${trans.category_name}</td>
                <td>Rp ${formatNumber(trans.amount)}</td>
                <td>${trans.description || '-'}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editTransaction(${trans.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteTransaction(${trans.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        `;
    });
    $('#transactionTableBody').html(html);
}

/**
 * Memuat pilihan kategori untuk dropdown select
 * Digunakan di form transaksi dan budget
 */
function loadCategoryOptions() {
    $.ajax({
        url: '../actions/category-action.php?action=read',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Pilih Kategori</option>';
                response.data.forEach(cat => {
                    options += `<option value="${cat.id}">${cat.icon} ${cat.name}</option>`;
                });
                $('#transactionCategory, #budgetCategory').html(options);
            }
        }
    });
}

/**
 * Menyimpan transaksi (create/update) ke database
 * Mode ditentukan berdasarkan ada tidaknya ID transaksi
 */
function saveTransaction() {
    const id = $('#transactionId').val();
    const action = id ? 'update' : 'create';
    
    const formData = {
        action: action,
        id: id,
        category_id: $('#transactionCategory').val(),
        amount: $('#transactionAmount').val(),
        description: $('#transactionDescription').val(),
        transaction_date: $('#transactionDate').val()
    };
    
    $.ajax({
        url: '../actions/transaction-action.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeModal('transactionModal');
                loadTransactions();
                $('#transactionForm')[0].reset();
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Terjadi kesalahan saat menyimpan data', 'danger');
        }
    });
}

/**
 * Memuat data transaksi untuk diedit
 * Data akan mengisi form di modal edit transaksi
 * 
 * @param {number} id - ID transaksi yang akan diedit
 */
function editTransaction(id) {
    $.ajax({
        url: `../actions/transaction-action.php?action=read&id=${id}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const trans = response.data[0];
                $('#transactionId').val(trans.id);
                $('#transactionCategory').val(trans.category_id);
                $('#transactionAmount').val(trans.amount);
                $('#transactionDescription').val(trans.description);
                $('#transactionDate').val(trans.transaction_date);
                $('#transactionModalTitle').text('Edit Transaksi');
                openModal('transactionModal');
            }
        }
    });
}

/**
 * Menghapus transaksi dengan konfirmasi
 * Setelah berhasil, tabel transaksi akan di-reload
 * 
 * @param {number} id - ID transaksi yang akan dihapus
 */
function deleteTransaction(id) {
    if (confirm('Yakin ingin menghapus transaksi ini?')) {
        $.ajax({
            url: '../actions/transaction-action.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    loadTransactions();
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAlert('Gagal menghapus transaksi', 'danger');
            }
        });
    }
}
/**
 * Memuat semua data anggaran dari database
 * Data budget termasuk nama kategori melalui JOIN
 */
function loadBudgets() {
    $.ajax({
        url: '../actions/budget-action.php?action=read',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayBudgets(response.data);
            }
        },
        error: function() {
            showAlert('Gagal memuat data anggaran', 'danger');
        }
    });
}

/**
 * Menampilkan data anggaran ke dalam tabel
 * Periode ditampilkan dengan badge berbeda (monthly/yearly)
 * 
 * @param {Array} budgets - Array objek budget dari database
 */
function displayBudgets(budgets) {
    let html = '';
    budgets.forEach(budget => {
        const periodClass = budget.period === 'monthly' ? 'badge-monthly' : 'badge-yearly';
        html += `
            <tr>
                <td>${budget.id}</td>
                <td>${budget.category_name}</td>
                <td>Rp ${formatNumber(budget.amount)}</td>
                <td><span class="badge ${periodClass}">${budget.period === 'monthly' ? 'Bulanan' : 'Tahunan'}</span></td>
                <td>${formatDate(budget.start_date)}</td>
                <td>${formatDate(budget.end_date)}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editBudget(${budget.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteBudget(${budget.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        `;
    });
    $('#budgetTableBody').html(html);
}

/**
 * Save budget (create or update)
 */
function saveBudget() {
    const id = $('#budgetId').val();
    const action = id ? 'update' : 'create';
    
    const formData = {
        action: action,
        id: id,
        category_id: $('#budgetCategory').val(),
        amount: $('#budgetAmount').val(),
        period: $('#budgetPeriod').val(),
        start_date: $('#budgetStartDate').val(),
        end_date: $('#budgetEndDate').val()
    };
    
    $.ajax({
        url: '../actions/budget-action.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeModal('budgetModal');
                loadBudgets();
                $('#budgetForm')[0].reset();
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Terjadi kesalahan saat menyimpan data', 'danger');
        }
    });
}

/**
 * Load budget data for editing
 * @param {number} id - Budget ID
 */
function editBudget(id) {
    $.ajax({
        url: `../actions/budget-action.php?action=read&id=${id}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const budget = response.data[0];
                $('#budgetId').val(budget.id);
                $('#budgetCategory').val(budget.category_id);
                $('#budgetAmount').val(budget.amount);
                $('#budgetPeriod').val(budget.period);
                $('#budgetStartDate').val(budget.start_date);
                $('#budgetEndDate').val(budget.end_date);
                $('#budgetModalTitle').text('Edit Anggaran');
                openModal('budgetModal');
            }
        }
    });
}

/**
 * Delete budget with confirmation
 * @param {number} id - Budget ID
 */
function deleteBudget(id) {
    if (confirm('Yakin ingin menghapus anggaran ini?')) {
        $.ajax({
            url: '../actions/budget-action.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    loadBudgets();
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                showAlert('Gagal menghapus anggaran', 'danger');
            }
        });
    }
}

/**
 * Open modal dialog
 * @param {string} modalId - ID of modal to open
 */
function openModal(modalId) {
    $('#' + modalId).addClass('active');
}

/**
 * Close modal dialog
 * @param {string} modalId - ID of modal to close
 */
function closeModal(modalId) {
    $('#' + modalId).removeClass('active');
}

/**
 * Load dashboard summary data
 */
function loadDashboard() {
    $.ajax({
        url: '../actions/dashboard-action.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const totalIncome = response.data.total_income || 0;
                const totalExpense = response.data.total_expense || 0;
                const totalBudget = response.data.total_budget || 0;
                
                $('#totalIncome').text('Rp ' + formatNumber(totalIncome));
                $('#totalExpense').text('Rp ' + formatNumber(totalExpense));
                $('#totalBudget').text('Rp ' + formatNumber(totalBudget));
                
                // Update recent transactions
                if (response.data.recent_transactions) {
                    displayRecentTransactions(response.data.recent_transactions);
                }
            } else {
                showAlert('Gagal memuat data dashboard: ' + (response.message || 'Unknown error'), 'danger');
            }
        },
        error: function(xhr, status, error) {
            showAlert('Gagal memuat data dashboard', 'danger');
        }
    });
}

/**
 * Display recent transactions in dashboard
 * @param {Array} transactions - Array of recent transaction objects
 */
function displayRecentTransactions(transactions) {
    let html = '';
    if (transactions.length === 0) {
        html = '<tr><td colspan="4" style="text-align:center">Belum ada transaksi</td></tr>';
    } else {
        transactions.forEach(trans => {
            html += `
                <tr>
                    <td>${formatDate(trans.transaction_date)}</td>
                    <td>${trans.category_name}</td>
                    <td>Rp ${formatNumber(trans.amount)}</td>
                    <td>${trans.description || '-'}</td>
                </tr>
            `;
        });
    }
    $('#recentTransactionsBody').html(html);
}