-- blog_app Database Schema
-- Created: October 19, 2025

CREATE DATABASE IF NOT EXISTS blog_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_app;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Articles table
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Technology', 'Articles about latest technology trends'),
('Web Development', 'Web development tutorials and tips'),
('Programming', 'Programming languages and best practices'),
('Digital Marketing', 'Marketing strategies and tips'),
('Business', 'Business insights and news');

-- Insert sample articles
INSERT INTO articles (title, content, image, category_id) VALUES
('Getting Started with PHP', 'PHP is a popular server-side scripting language that is especially suited for web development. In this article, we will explore the basics of PHP programming...', 'uploads/php-basics.jpg', 3),
('Bootstrap 5 Features', 'Bootstrap 5 comes with many new features and improvements. Let''s explore what makes this version special and how to use it effectively...', 'uploads/bootstrap5.jpg', 2),
('Digital Marketing Trends 2025', 'The digital marketing landscape is constantly evolving. Here are the top trends to watch in 2025...', 'uploads/marketing-2025.jpg', 4),
('Building REST APIs', 'REST APIs are the backbone of modern web applications. Learn how to build robust and scalable APIs...', 'uploads/rest-api.jpg', 3),
('Modern Business Strategies', 'In today''s competitive market, businesses need to adapt and innovate. Here are some proven strategies...', 'uploads/business-strategy.jpg', 5);