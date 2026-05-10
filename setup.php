<?php
require_once 'config.php';

echo "<h2>Database Setup</h2>";

try {
    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $db_name");
    echo "Database created/selected successfully.<br>";

    // Create Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'client') DEFAULT 'client',
        phone VARCHAR(20),
        joined DATE,
        avatar VARCHAR(5)
    )");

    // Create Bookings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id VARCHAR(20) PRIMARY KEY,
        client_id INT NOT NULL,
        event VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        guests INT NOT NULL,
        package VARCHAR(50),
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        venue VARCHAR(255),
        notes TEXT,
        created DATE,
        FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Create Menu Items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        dietary TEXT, -- JSON array
        description TEXT,
        image_path VARCHAR(255),
        emoji VARCHAR(10),
        available BOOLEAN DEFAULT TRUE
    )");

    // Create Packages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        min_guests INT DEFAULT 1,
        description TEXT,
        includes TEXT, -- JSON array
        color VARCHAR(20),
        featured BOOLEAN DEFAULT FALSE
    )");

    // Create Staff table
    $pdo->exec("CREATE TABLE IF NOT EXISTS staff (
        id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(100) NOT NULL,
        status ENUM('active', 'on-leave', 'inactive') DEFAULT 'active',
        phone VARCHAR(20),
        experience VARCHAR(50),
        speciality VARCHAR(100),
        salary DECIMAL(10,2) NOT NULL
    )");

    // Create Inventory table
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory (
        id VARCHAR(20) PRIMARY KEY,
        item VARCHAR(100) NOT NULL,
        category VARCHAR(50),
        stock INT DEFAULT 0,
        min_stock INT DEFAULT 0,
        unit VARCHAR(20),
        cost_per_unit DECIMAL(10,2) NOT NULL,
        supplier VARCHAR(100)
    )");

    echo "Tables created successfully.<br>";

    // Insert Default Data (only if users table is empty)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $client_pass = password_hash('client123', PASSWORD_DEFAULT);
        
        // Users
        $pdo->exec("INSERT INTO users (id, name, email, password, role, phone, joined, avatar) VALUES 
            (1, 'Admin User', 'admin@luxecater.com', '$admin_pass', 'admin', '555-0100', '2023-01-15', 'A'),
            (2, 'Sarah Johnson', 'client@example.com', '$client_pass', 'client', '555-0101', '2024-03-10', 'S'),
            (3, 'Michael Chen', 'mchen@email.com', '$client_pass', 'client', '555-0102', '2024-05-22', 'M')
        ");

        // Bookings
        $pdo->exec("INSERT INTO bookings (id, client_id, event, date, guests, package, amount, status, venue, notes, created) VALUES 
            ('BK001', 2, 'Wedding Reception', '2025-02-14', 150, 'Premium', 8500, 'confirmed', 'Grand Ballroom', 'Vegetarian options needed', '2024-12-01'),
            ('BK002', 3, 'Corporate Gala', '2025-01-28', 300, 'Elite', 22000, 'confirmed', 'Convention Center', 'Open bar required', '2024-12-05')
        ");

        // Menu
        $diet_veg = json_encode(['vegetarian']);
        $pdo->exec("INSERT INTO menu_items (id, name, category, price, dietary, description, emoji, available) VALUES 
            ('M001', 'Truffle Bruschetta', 'Appetizers', 18, '$diet_veg', 'Wild mushroom & truffle on sourdough crostini', '🍄', 1),
            ('M002', 'Beef Wellington', 'Main Course', 58, '[]', 'Tender beef wrapped in puff pastry with mushroom duxelle', '🥩', 1)
        ");

        // Packages
        $inc_std = json_encode(['3-Course Meal', 'Standard Bar', 'Basic Setup']);
        $inc_prm = json_encode(['4-Course Meal', 'Premium Bar', 'Floral Centerpieces', 'Dedicated Staff']);
        $pdo->exec("INSERT INTO packages (id, name, price, min_guests, description, includes, color, featured) VALUES 
            ('P001', 'Standard', 45, 20, 'Perfect for intimate gatherings', '$inc_std', 'blue', 0),
            ('P002', 'Premium', 75, 50, 'Our most popular choice for weddings', '$inc_prm', 'gold', 1)
        ");

        // Staff
        $pdo->exec("INSERT INTO staff (id, name, role, status, phone, experience, speciality, salary) VALUES 
            ('S001', 'Chef Marco Rossi', 'Executive Chef', 'active', '555-0201', '15 years', 'Italian Cuisine', 8500),
            ('S002', 'Lisa Park', 'Pastry Chef', 'active', '555-0202', '8 years', 'French Pastry', 5500)
        ");

        // Inventory
        $pdo->exec("INSERT INTO inventory (id, item, category, stock, min_stock, unit, cost_per_unit, supplier) VALUES 
            ('I001', 'Premium Beef', 'Proteins', 45, 20, 'kg', 28, 'Metro Foods'),
            ('I002', 'Fresh Salmon', 'Proteins', 12, 15, 'kg', 22, 'Ocean Fresh')
        ");

        echo "Default data inserted successfully.<br>";
    } else {
        echo "Database already contains data. Skipping initial inserts.<br>";
    }

    echo "<h3>Setup Complete! <a href='index.php'>Go to Homepage</a></h3>";

} catch (PDOException $e) {
    echo "Setup Error: " . $e->getMessage();
}
?>
