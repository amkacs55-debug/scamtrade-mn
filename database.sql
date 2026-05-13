-- GameVault Database Schema
CREATE DATABASE IF NOT EXISTS gamevault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gamevault;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
    bio TEXT,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    game ENUM('Mobile Legends','PUBG Mobile','Standoff2') NOT NULL,
    rank_name VARCHAR(50),
    skin_count INT DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('pending','approved','rejected','sold') DEFAULT 'approved',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    listing_id INT,
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_fav (user_id, listing_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    listing_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','completed','cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150),
    body TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    buyer_id INT NOT NULL,
    listing_id INT,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo data (password for all demo users: password123)
INSERT INTO users (username, email, password, is_admin, bio) VALUES
('admin', 'admin@gamevault.gg', '$2y$10$e0NRrWZbB3W3JYx0Ej9J1eO6lYgVQv7aDqQpQ9c8OqUe3Q9VeZ8Jq', 1, 'GameVault administrator'),
('NeonShadow', 'neon@gamevault.gg', '$2y$10$e0NRrWZbB3W3JYx0Ej9J1eO6lYgVQv7aDqQpQ9c8OqUe3Q9VeZ8Jq', 0, 'Pro ML player. Selling stacked accounts.'),
('PhantomAce', 'phantom@gamevault.gg', '$2y$10$e0NRrWZbB3W3JYx0Ej9J1eO6lYgVQv7aDqQpQ9c8OqUe3Q9VeZ8Jq', 0, 'PUBG conqueror grinder.'),
('GlitchKing', 'glitch@gamevault.gg', '$2y$10$e0NRrWZbB3W3JYx0Ej9J1eO6lYgVQv7aDqQpQ9c8OqUe3Q9VeZ8Jq', 0, 'Standoff2 Legendary main.');

INSERT INTO listings (user_id, title, game, rank_name, skin_count, price, description, image, views) VALUES
(2,'Mythic Glory ML — 320 Skins','Mobile Legends','Mythic Glory',320,499.00,'Stacked Mythic Glory account with all collector and legend skins. Linked Moonton only — easy transfer.','https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800', 1240),
(2,'Mythical Honor — 180 Skins','Mobile Legends','Mythical Honor',180,259.00,'Clean account, no bans, full collection of epic skins.','https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=800', 870),
(3,'Conqueror PUBG Asia — Rare M416','PUBG Mobile','Conqueror',95,389.00,'Conqueror Asia server. Glacier M416 + Pharaoh X-Suit.','https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800', 2103),
(3,'Ace Tier — Mythic Outfits','PUBG Mobile','Ace',60,179.00,'Ace tier with rare mythic sets and gun skins.','https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=800', 540),
(4,'Legendary Standoff2 — Knife Karambit','Standoff2','Legendary',45,229.00,'Legendary rank with Karambit Fade and AWP Dragon.','https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=800', 980),
(4,'Master Standoff2 Starter','Standoff2','Master',22,89.00,'Great starter Master account, clean inventory.','https://images.unsplash.com/photo-1606318801954-d46d46d3360a?w=800', 320);
