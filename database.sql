-- ============================================================
-- Student Council Election 2026 - Database Setup
-- ============================================================
-- Run this file once to set up the database from scratch.
-- Import via: mysql -u root < database.sql
-- Or use phpMyAdmin > Import.
-- ============================================================

CREATE DATABASE IF NOT EXISTS election_db;
USE election_db;

-- ── Admin Users ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(100) NOT NULL,
    role       VARCHAR(10)  NOT NULL DEFAULT 'admin',
    full_name  VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (change password after first login)
INSERT IGNORE INTO users (email, password, role, full_name)
VALUES ('vote@vote.vt', 'admin123', 'admin', 'Admin User');


-- ── Departments ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS departments (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL UNIQUE,
    code       VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO departments (name, code) VALUES
('College of Computer Studies',        'CCS'),
('College of Engineering',             'COE'),
('College of Business Administration', 'CBA'),
('College of Arts and Sciences',       'CAS'),
('College of Education',               'COEd'),
('College of Nursing',                 'CON');


-- ── Positions ─────────────────────────────────────────────────
-- order_num controls the voting step order and sidebar order.
-- color is used on the review page ballot summary.
CREATE TABLE IF NOT EXISTS positions (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(50) NOT NULL UNIQUE,
    order_num  INT,
    color      VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO positions (name, order_num, color) VALUES
('President',        1, '#FF6B6B'),
('Vice President',   2, '#4ECDC4'),
('Secretary',        3, '#45B7D1'),
('Treasurer',        4, '#FFA07A'),
('Auditor',          5, '#98D8C8'),
('PIO',              6, '#F7DC6F'),
('Business Manager', 7, '#BB8FCE');


-- ── Registered Voters ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS voters (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(100) NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    student_id  VARCHAR(50)  NOT NULL UNIQUE,
    department  VARCHAR(100),
    year        INT,
    has_voted   INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ── Candidates ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS candidates (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    position    VARCHAR(50)  NOT NULL,
    name        VARCHAR(100) NOT NULL,
    image_url   VARCHAR(500),
    description TEXT,
    department  VARCHAR(100),
    year        INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ── Votes ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS votes (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    user_id      INT NOT NULL,
    position     VARCHAR(50) NOT NULL,
    candidate_id INT NOT NULL,
    voted_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_voter_position (user_id, position),
    FOREIGN KEY (user_id)      REFERENCES voters(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);


-- ── Developers ────────────────────────────────────────────────
-- Managed by admin via the Admin Dashboard > Developers tab.
-- Empty by default.
CREATE TABLE IF NOT EXISTS developers (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,
    role          VARCHAR(50),
    year          INT,
    department_id INT,
    order_num     INT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);


-- ── Settings ──────────────────────────────────────────────────
-- Key-value store for election-wide settings.
CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(50)  PRIMARY KEY,
    `value` VARCHAR(200) NOT NULL
);

-- election_open: '1' = voting is open, '0' = voting is closed
-- allow_registration: '1' = new voters can sign up, '0' = sign-up is closed
-- org_name, election_title, logo_emoji: branding shown on all voter-facing pages
INSERT IGNORE INTO settings (`key`, `value`) VALUES
('election_open',      '1'),
('allow_registration', '1'),
('election_title',     'Student Council Election 2026'),
('org_name',           'Student Vote'),
('election_year',      '2026'),
('logo_emoji',         '🗳️');
