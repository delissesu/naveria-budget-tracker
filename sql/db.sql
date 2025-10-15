-- Active: 1748311330274@@127.0.0.1@5432@budget_tracker
CREATE TYPE category_type AS ENUM ('income', 'expense');
CREATE TYPE budget_period AS ENUM ('monthly', 'yearly');

-- Tabel Categories
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type category_type NOT NULL, 
    icon VARCHAR(50) DEFAULT 'default',
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Transactions
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY, 
    category_id INT NOT NULL,
    amount NUMERIC(15, 2) NOT NULL, 
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Tabel Budgets
CREATE TABLE budgets (
    id SERIAL PRIMARY KEY, 
    category_id INT NOT NULL,
    amount NUMERIC(15, 2) NOT NULL,
    period budget_period NOT NULL, 
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

INSERT INTO categories (name, type, icon) VALUES
('Gaji', 'income', '💰'),
('Makanan', 'expense', '🍔'),
('Transport', 'expense', '🚗'),
('Belanja', 'expense', '🛒'),
('Hiburan', 'expense', '🎬');

SELECT * FROM categories

CREATE INDEX idx_transactions_category_id ON transactions(category_id);
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_budgets_category_id ON budgets(category_id);
CREATE INDEX idx_budgets_period ON budgets(start_date, end_date);

INSERT INTO transactions (category_id, amount, description, transaction_date) VALUES
(1, 5000000, 'Gaji Bulan Oktober', '2025-10-01'),
(4, 150000, 'Makan siang', '2025-10-05'),
(5, 200000, 'Bensin motor', '2025-10-06'),
(6, 500000, 'Belanja bulanan', '2025-10-07'),
(7, 100000, 'Nonton film', '2025-10-08');

-- Data Sample untuk Budgets
INSERT INTO budgets (category_id, amount, period, start_date, end_date) VALUES
(4, 1500000, 'monthly', '2025-10-01', '2025-10-31'),
(5, 800000, 'monthly', '2025-10-01', '2025-10-31'),
(6, 2000000, 'monthly', '2025-10-01', '2025-10-31'),
(7, 500000, 'monthly', '2025-10-01', '2025-10-31');

-- Verify data
SELECT 'Categories' as "Nama Tabel", COUNT(*) as "Jumlah" FROM categories
UNION ALL
SELECT 'Transactions', COUNT(*) FROM transactions
UNION ALL
SELECT 'Budgets', COUNT(*) FROM budgets;