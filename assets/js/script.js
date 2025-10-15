// Global functions
function showAlert(message, type = 'success') {
    const alertDiv = $(`
        <div class="alert alert-${type} show">
            ${message}
        </div>
    `);
    
    $('.container').first().prepend(alertDiv);
    
    setTimeout(() => {
        alertDiv.fadeOut(() => alertDiv.remove());
    }, 3000);
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Category Functions
function loadCategories() {
    $.ajax({
        url: 'actions/category_action.php?action=read',
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

function displayCategories(categories) {
    let html = '';
    categories.forEach(cat => {
        const typeClass = cat.type === 'income' ? 'badge-income' : 'badge-expense';
        html += `
            <tr>
                <td>${cat.id}</td>
                <td>${cat.icon} ${cat.name}</td>
                <td><span class="badge ${typeClass}">${cat.type === 'income' ? 'Pemasukan' : 'Pengeluaran'}</span></td>
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
    $('#categoryTableBody').html(html);
}

function saveCategory() {
    const id = $('#categoryId').val();
    const action = id ? 'update' : 'create';
    
    const formData = {
        action: action,
        id: id,
        name: $('#categoryName').val(),
        type: $('#categoryType').val(),
        icon: $('#categoryIcon').val()
    };
    
    $.ajax({
        url: 'actions/category_action.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeModal('categoryModal');
                loadCategories();
                $('#categoryForm')[0].reset();
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Terjadi kesalahan saat menyimpan data', 'danger');
        }
    });
}

function editCategory(id) {
    $.ajax({
        url: `actions/category_action.php?action=read&id=${id}`,
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

function deleteCategory(id) {
    if (confirm('Yakin ingin menghapus kategori ini?')) {
        $.ajax({
            url: 'actions/category_action.php',
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
            }
        });
    }
}

// Transaction Functions
function loadTransactions() {
    $.ajax({
        url: 'actions/transaction_action.php?action=read',
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

function loadCategoryOptions() {
    $.ajax({
        url: 'actions/category_action.php?action=read',
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
        url: 'actions/transaction_action.php',
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

function editTransaction(id) {
    $.ajax({
        url: `actions/transaction_action.php?action=read&id=${id}`,
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

function deleteTransaction(id) {
    if (confirm('Yakin ingin menghapus transaksi ini?')) {
        $.ajax({
            url: 'actions/transaction_action.php',
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
            }
        });
    }
}

// Budget Functions
function loadBudgets() {
    $.ajax({
        url: 'actions/budget_action.php?action=read',
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
        url: 'actions/budget_action.php',
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

function editBudget(id) {
    $.ajax({
        url: `actions/budget_action.php?action=read&id=${id}`,
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

function deleteBudget(id) {
    if (confirm('Yakin ingin menghapus anggaran ini?')) {
        $.ajax({
            url: 'actions/budget_action.php',
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
            }
        });
    }
}

// Modal Functions
function openModal(modalId) {
    $('#' + modalId).addClass('active');
}

function closeModal(modalId) {
    $('#' + modalId).removeClass('active');
}

// Dashboard Functions
function loadDashboard() {
    $.ajax({
        url: 'actions/dashboard_action.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#totalIncome').text('Rp ' + formatNumber(response.data.total_income));
                $('#totalExpense').text('Rp ' + formatNumber(response.data.total_expense));
                $('#totalBudget').text('Rp ' + formatNumber(response.data.total_budget));
                
                displayRecentTransactions(response.data.recent_transactions);
            }
        }
    });
}

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