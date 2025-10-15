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