-- Estuda+ MVP (Fase 1) - MySQL 5.7
-- Modulos: auth, flashcards, estudo, XP, streak, ranking

CREATE TABLE users (
    id VARCHAR(40) PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(120) NOT NULL,
    role ENUM('student', 'teacher', 'parent', 'admin') NOT NULL DEFAULT 'student',
    xp INT NOT NULL DEFAULT 0,
    level INT NOT NULL DEFAULT 0,
    streak INT NOT NULL DEFAULT 0,
    last_study_date DATE NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE decks (
    id VARCHAR(40) PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    creator_id VARCHAR(40) NOT NULL,
    visibility ENUM('private', 'public', 'paid') NOT NULL DEFAULT 'public',
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_decks_creator FOREIGN KEY (creator_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE flashcards (
    id VARCHAR(40) PRIMARY KEY,
    deck_id VARCHAR(40) NOT NULL,
    type ENUM('QA', 'MULTIPLE', 'TRUE_FALSE') NOT NULL DEFAULT 'QA',
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    options JSON NULL,
    CONSTRAINT fk_flashcards_deck FOREIGN KEY (deck_id) REFERENCES decks (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE study_sessions (
    id VARCHAR(40) PRIMARY KEY,
    user_id VARCHAR(40) NOT NULL,
    deck_id VARCHAR(40) NOT NULL,
    total_questions INT NOT NULL DEFAULT 0,
    answered_questions INT NOT NULL DEFAULT 0,
    correct_answers INT NOT NULL DEFAULT 0,
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    CONSTRAINT fk_study_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_study_deck FOREIGN KEY (deck_id) REFERENCES decks (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE card_reviews (
    id VARCHAR(40) PRIMARY KEY,
    user_id VARCHAR(40) NOT NULL,
    flashcard_id VARCHAR(40) NOT NULL,
    repetition INT NOT NULL DEFAULT 0,
    interval_days INT NOT NULL DEFAULT 1,
    ease_factor DECIMAL(4,2) NOT NULL DEFAULT 2.50,
    next_review DATE NOT NULL,
    CONSTRAINT uk_user_flashcard UNIQUE (user_id, flashcard_id),
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_review_flashcard FOREIGN KEY (flashcard_id) REFERENCES flashcards (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE xp_history (
    id VARCHAR(40) PRIMARY KEY,
    user_id VARCHAR(40) NOT NULL,
    xp INT NOT NULL,
    reason VARCHAR(80) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_xp_user FOREIGN KEY (user_id) REFERENCES users (id),
    INDEX idx_xp_history_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auth_tokens (
    token VARCHAR(64) PRIMARY KEY,
    user_id VARCHAR(40) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_auth_token_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ranking global (consulta base)
-- SELECT id, name, xp, level, streak
-- FROM users
-- ORDER BY xp DESC, streak DESC, name ASC
-- LIMIT 50;
