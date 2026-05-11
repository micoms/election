CREATE DATABASE IF NOT EXISTS election CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE election;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    student_id VARCHAR(40) NOT NULL UNIQUE,
    department VARCHAR(120) NOT NULL,
    year_level VARCHAR(40) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'voter') NOT NULL DEFAULT 'voter',
    finalized_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS positions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(80) NOT NULL UNIQUE,
    display_order TINYINT UNSIGNED NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS candidates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    position_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    department VARCHAR(120) NOT NULL,
    year_level VARCHAR(40) NOT NULL,
    slogan VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_candidates_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    position_id INT UNSIGNED NOT NULL,
    candidate_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_votes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_votes_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    CONSTRAINT fk_votes_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_position (user_id, position_id)
);

CREATE TABLE IF NOT EXISTS vote_audit (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    position_id INT UNSIGNED NOT NULL,
    old_candidate_id INT UNSIGNED NOT NULL,
    new_candidate_id INT UNSIGNED NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_audit_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_audit_old_candidate FOREIGN KEY (old_candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_audit_new_candidate FOREIGN KEY (new_candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

INSERT INTO users (full_name, email, student_id, department, year_level, password_hash, role)
VALUES ('System Admin', 'admin@university.edu', 'ADMIN001', 'Administration', 'Staff', '$2y$10$/INOaduUjjgQNI1AFvBIZeuqkYPYh7DG0fMjPXGaUXoEY/BfHEPC2', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO positions (title, display_order) VALUES
('President', 1),
('Vice President', 2),
('Secretary', 3),
('Treasurer', 4),
('Auditor', 5),
('PIO', 6),
('Business Manager', 7)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO candidates (position_id, full_name, department, year_level, slogan, bio, image_path) VALUES
((SELECT id FROM positions WHERE title='President'), 'Rendale Crisostomo', 'College of Computer Studies', '3rd Year', 'Binitawan pero hinding hindi ka bibitawan', 'Free helmet for all students.', '/images/MVIMG_20260416_102857_1_1776306731720edit.jpg'),
((SELECT id FROM positions WHERE title='President'), 'Jordan Lee', 'College of Business Administration', '4th Year', 'Transparency. Action. Results.', 'Focuses on budget transparency and student services.', '/images/jordan_lee.png'),
((SELECT id FROM positions WHERE title='President'), 'Sam Smith', 'College of Arts and Sciences', '3rd Year', 'Your voice, heard loud and clear.', 'Running on digital reform and stronger student feedback.', '/images/sam_smith.png'),
((SELECT id FROM positions WHERE title='Vice President'), 'Maria Santos', 'College of Engineering', '3rd Year', 'Support every student.', 'Plans to improve inter-college collaboration.', '/images/maria_santos.png'),
((SELECT id FROM positions WHERE title='Vice President'), 'Chris Reyes', 'College of Computer Studies', '4th Year', 'Lead with service.', 'Focuses on operations and student welfare.', '/images/chris_reyes.png'),
((SELECT id FROM positions WHERE title='Vice President'), 'Taylor Brooks', 'College of Arts and Sciences', '3rd Year', 'Together we rise.', 'Advocates for transparent communication.', '/images/taylor_brooks.png'),
((SELECT id FROM positions WHERE title='Secretary'), 'Emily Chen', 'College of Computer Studies', '2nd Year', 'Organized and accountable.', 'Will improve records and election transparency.', '/images/emily_chen.png'),
((SELECT id FROM positions WHERE title='Secretary'), 'Daniel Park', 'College of Engineering', '2nd Year', 'Clear records, clear goals.', 'Supports open reports and regular updates.', '/images/daniel_park.png'),
((SELECT id FROM positions WHERE title='Secretary'), 'Aisha Khan', 'College of Nursing', '3rd Year', 'Detail-driven leadership.', 'Focuses on accurate documentation.', '/images/aisha_khan.png'),
((SELECT id FROM positions WHERE title='Treasurer'), 'Ryan Garcia', 'College of Business Administration', '4th Year', 'Every peso matters.', 'Prioritizes transparent fund management.', '/images/ryan_garcia.png'),
((SELECT id FROM positions WHERE title='Treasurer'), 'Nina Flores', 'College of Education', '3rd Year', 'Budget with integrity.', 'Plans to publish regular budget summaries.', '/images/nina_flores.png'),
((SELECT id FROM positions WHERE title='Treasurer'), 'Jake Torres', 'College of Computer Studies', '3rd Year', 'Smart spending, strong programs.', 'Supports efficient budgeting.', '/images/jake_torres.png'),
((SELECT id FROM positions WHERE title='Auditor'), 'Liam Cruz', 'College of Arts and Sciences', '4th Year', 'Verify and protect.', 'Aims for accountable auditing practices.', '/images/liam_cruz.png'),
((SELECT id FROM positions WHERE title='Auditor'), 'Sophie Tan', 'College of Business Administration', '3rd Year', 'Trust through transparency.', 'Supports clear and timely audits.', '/images/sophie_tan.png'),
((SELECT id FROM positions WHERE title='Auditor'), 'Marcus Webb', 'College of Engineering', '2nd Year', 'Checks that matter.', 'Will strengthen oversight on student funds.', '/images/marcus_webb.png'),
((SELECT id FROM positions WHERE title='PIO'), 'Bianca Reyes', 'College of Arts and Sciences', '2nd Year', 'Keep everyone informed.', 'Focuses on timely campus-wide updates.', '/images/bianca_reyes.png'),
((SELECT id FROM positions WHERE title='PIO'), 'Carlo Mendoza', 'College of Computer Studies', '3rd Year', 'Communication first.', 'Will improve election communication channels.', '/images/carlo_mendoza.png'),
((SELECT id FROM positions WHERE title='PIO'), 'Hazel Lim', 'College of Education', '4th Year', 'Voice of the students.', 'Plans to increase student engagement.', '/images/hazel_lim.png'),
((SELECT id FROM positions WHERE title='Business Manager'), 'Adrian Co', 'College of Business Administration', '4th Year', 'Manage better, serve better.', 'Will optimize project operations.', '/images/adrian_co.png'),
((SELECT id FROM positions WHERE title='Business Manager'), 'Patricia Gomez', 'College of Engineering', '3rd Year', 'Structure and strategy.', 'Focuses on process improvements.', '/images/patricia_gomez.png'),
((SELECT id FROM positions WHERE title='Business Manager'), 'Vincent Ong', 'College of Computer Studies', '2nd Year', 'Results through planning.', 'Supports scalable student initiatives.', '/images/vincent_ong.png');
