SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS eventhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eventhub;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'moderator', 'admin', 'superadmin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description LONGTEXT,
    date DATETIME NOT NULL,
    capacity INT NOT NULL,
    location VARCHAR(200) NOT NULL,
    image VARCHAR(100),
    approved TINYINT(1) DEFAULT 0,
    user_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_tag (
    event_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (event_id, tag_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    status TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testovací data
INSERT INTO users (username, email, password, role) VALUES
('superadmin', 'superadmin@eventhub.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

INSERT INTO users (username, email, password, role) VALUES
('moderator', 'mod@eventhub.cz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderator'),
('admin',     'admin@eventhub.cz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('pepa',      'pepa@example.cz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('jana',      'jana@example.cz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO events (title, description, date, capacity, location, image, approved, user_id) VALUES
('PHP Konference 2025', 'Největší česká PHP konference roku!', '2025-12-15 09:00:00', 200, 'Praha', 'event1.jpg', 1, 4),
('Hackathon ZČU', '48hodinový hackathon na téma AI.', '2025-11-25 18:00:00', 50, 'Plzeň', 'event2.webp', 1, 5),
('Webové technologie – přednáška', 'Moderní trendy KIV/WEB.', '2025-12-01 14:00:00', 80, 'UJEP', 'event3.jpg', 0, 4),
('Konference o AI v Praze', 'Diskuse o umělé inteligenci a jejím budoucím vlivu.', '2025-11-20 10:00:00', 150, 'Praha', 'event4.webp', 1, 4),
('Workshop programování v Pythonu', 'Praktický workshop pro začátečníky v Pythonu.', '2025-12-05 14:00:00', 50, 'Brno', 'event5.webp', 0, 5),
('Přednáška o webovém designu', 'Trendy v UI/UX designu pro rok 2026.', '2026-01-15 16:00:00', 80, 'Plzeň', 'event6.jpeg', 1, 2),
('Hackathon pro studenty IT', '24hodinový hackathon s tématem IoT.', '2025-12-10 09:00:00', 100, 'Ostrava', 'event7.png', 1, 3),
('Seminář o kyberbezpečnosti', 'Základy ochrany dat v digitálním světě.', '2025-11-25 13:00:00', 120, 'Praha', 'event8.webp', 0, 4),
('Konference o zelených technologiích', 'Ekologické inovace v IT sektoru.', '2026-02-01 10:00:00', 200, 'Brno', 'event9.webp', 1, 5),
('Workshop JavaScript pro pokročilé', 'Moderní frameworky a best practices.', '2025-12-20 15:00:00', 40, 'Liberec', 'event10.webp', 0, 2),
('Přednáška o data science', 'Analýza dat s využitím ML.', '2026-01-10 11:00:00', 90, 'České Budějovice', NULL, 1, 3),
('Meetup vývojářů v Praze', 'Setkání pro sdílení zkušeností.', '2025-11-30 18:00:00', 60, 'Praha', NULL, 0, 4),
('Kurz SQL pro začátečníky', 'Základy databází a query.', '2026-01-05 09:00:00', 30, 'Olomouc', NULL, 1, 5),
('Konference o blockchainu', 'Budoucnost kryptoměn a DLT.', '2025-12-15 10:00:00', 180, 'Brno', NULL, 0, 2),
('Workshop o DevOps', 'Automatizace a CI/CD pipeline.', '2026-02-10 14:00:00', 70, 'Hradec Králové', NULL, 1, 3),
('Přednáška o mobilním vývoji', 'Android a iOS app development.', '2025-11-28 16:00:00', 100, 'Praha', NULL, 0, 4),
('Hackathon na téma VR/AR', 'Virtuální a rozšířená realita.', '2026-01-20 09:00:00', 50, 'Plzeň', NULL, 1, 5),
('Seminář o cloud computingu', 'AWS, Azure a GCP porovnání.', '2025-12-08 13:00:00', 140, 'Ostrava', NULL, 0, 2),
('Meetup o open source', 'Přínosy komunity pro projekty.', '2026-02-05 18:00:00', 80, 'Liberec', NULL, 1, 3),
('Kurz C# pro .NET', 'Základy programování v C#.', '2025-11-22 15:00:00', 40, 'České Budějovice', NULL, 0, 4),
('Konference o e-commerce', 'Trendy v online prodeji.', '2026-01-25 10:00:00', 250, 'Praha', NULL, 1, 5),
('Workshop o testování softwaru', 'Automatizované testy a TDD.', '2025-12-12 14:00:00', 60, 'Olomouc', NULL, 0, 2),
('Přednáška o big data', 'Řešení pro velké objemy dat.', '2026-02-15 11:00:00', 110, 'Hradec Králové', NULL, 1, 3);

-- Hardcoded tagy
INSERT INTO tags (name) VALUES
('PHP'),
('JavaScript'),
('Python'),
('AI'),
('konference'),
('workshop'),
('hackathon'),
('meetup'),
('backend'),
('frontend'),
('data science'),
('security'),
('cloud'),
('design'),
('DevOps');

-- Přiřazení tagů k ukázkovým akcím (event_id odpovídá pořadí INSERTů výše)
INSERT INTO event_tag (event_id, tag_id) VALUES
(1, 1), (1, 5), (1, 9),
(2, 4), (2, 7),
(3, 5), (3, 10),
(4, 4), (4, 5),
(5, 3), (5, 6),
(6, 14),
(7, 7),
(8, 12),
(9, 13),
(10, 2), (10, 6),
(11, 11),
(12, 8),
(13, 11),
(14, 4),
(15, 15),
(16, 2),
(17, 7), (17, 4),
(18, 13),
(19, 8),
(20, 1),
(21, 5),
(22, 6),
(23, 11);

INSERT INTO newsletter_emails (email) VALUES
('test@example.cz'),
('news@eventhub.cz');

INSERT INTO comments (event_id, user_id, content, status) VALUES
(1, 4, 'Super akce, těším se.', 1),
(1, 5, 'Bude k dispozici záznam přednášek?', 0),

(2, 5, 'Hackathon zní dobře, ale 48 hodin je masakr.', 1),
(2, 3, 'Máte nějaké omezení na technologie?', 0),

(3, 4, 'Tahle přednáška by se mi hodila k předmětu na škole.', 1),

(4, 3, 'AI už je všude, tohle bude zajímavé.', 1),
(4, 2, 'Bude část věnovaná etice AI?', 0),

(5, 4, 'Python pro začátečníky, beru hned.', 1),

(6, 2, 'UI/UX mě moc nebere, ale tohle by mohlo změnit názor.', 1),

(7, 3, 'IoT hackathon vypadá dost našlapaně.', 1),

(8, 5, 'Kyberbezpečnost je teď úplný základ.', 1),
(8, 4, 'Budete řešit i phishing a sociální inženýrství?', 0),

(9, 4, 'Zelené technologie v IT, to mě docela zajímá.', 1),

(10, 2, 'Pokročilý JavaScript, snad tam nebude jen framework cirkus.', 1),

(11, 5, 'Data science mě láká už dlouho.', 1),

(12, 4, 'Meetup v Praze je fajn, aspoň něco neformálního.', 1),

(13, 5, 'SQL kurz by se hodil začátečníkům u nás v týmu.', 1),

(14, 2, 'Blockchain je pořád hype, ale rád si poslechnu novinky.', 1),
(14, 3, 'Bude zmínka i o regulaci kryptoměn?', 0),

(15, 3, 'DevOps workshop by mohl pomoct nastavit CI/CD u nás.', 1),

(16, 4, 'Mobilní vývoj mě baví, zajímá mě hlavně Android.', 1),

(17, 5, 'VR/AR hackathon zní dost dobře.', 1),

(18, 2, 'Zajímá mě hlavně porovnání cloud providerů.', 1),

(19, 3, 'Open source meetup je ideální na navázání kontaktů.', 1),

(20, 5, 'C# kurz by mohl pomoct připravit se na projekty v .NET.', 1),

(21, 4, 'E-commerce konference se mi hodí k práci.', 1),

(22, 2, 'Testování softwaru je podceňované, tohle by měl vidět každý dev.', 1),

(23, 3, 'Big data řešíme v práci, jdu pro inspiraci.', 1);
