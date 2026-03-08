<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308005037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MVP fase 1: auth, flashcards, estudo, XP, streak e ranking';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->upMysql();

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->upSqlite();

            return;
        }

        $this->abortIf(true, sprintf('Platform "%s" nao suportada por esta migration.', $platform::class));
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(
            !($platform instanceof AbstractMySQLPlatform || $platform instanceof SQLitePlatform),
            sprintf('Platform "%s" nao suportada.', $platform::class)
        );

        $this->addSql('DROP TABLE auth_tokens');
        $this->addSql('DROP TABLE card_reviews');
        $this->addSql('DROP TABLE decks');
        $this->addSql('DROP TABLE flashcards');
        $this->addSql('DROP TABLE study_sessions');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE xp_history');
    }

    private function upMysql(): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
              id VARCHAR(40) NOT NULL,
              email VARCHAR(190) NOT NULL,
              password_hash VARCHAR(255) NOT NULL,
              name VARCHAR(120) NOT NULL,
              role VARCHAR(20) NOT NULL DEFAULT 'student',
              xp INT NOT NULL DEFAULT 0,
              level INT NOT NULL DEFAULT 0,
              streak INT NOT NULL DEFAULT 0,
              last_study_date DATE DEFAULT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(id),
              UNIQUE KEY uniq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE decks (
              id VARCHAR(40) NOT NULL,
              title VARCHAR(180) NOT NULL,
              description TEXT DEFAULT NULL,
              creator_id VARCHAR(40) NOT NULL,
              visibility VARCHAR(20) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(id),
              KEY idx_decks_creator_id (creator_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE flashcards (
              id VARCHAR(40) NOT NULL,
              deck_id VARCHAR(40) NOT NULL,
              type VARCHAR(20) NOT NULL,
              question TEXT NOT NULL,
              answer TEXT NOT NULL,
              options JSON NOT NULL,
              PRIMARY KEY(id),
              KEY idx_flashcards_deck_id (deck_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE study_sessions (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              deck_id VARCHAR(40) NOT NULL,
              total_questions INT NOT NULL DEFAULT 0,
              answered_questions INT NOT NULL DEFAULT 0,
              correct_answers INT NOT NULL DEFAULT 0,
              started_at DATETIME NOT NULL,
              ended_at DATETIME DEFAULT NULL,
              PRIMARY KEY(id),
              KEY idx_study_sessions_user_id (user_id),
              KEY idx_study_sessions_deck_id (deck_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE card_reviews (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              flashcard_id VARCHAR(40) NOT NULL,
              repetition INT NOT NULL DEFAULT 0,
              interval_days INT NOT NULL DEFAULT 1,
              ease_factor DOUBLE PRECISION NOT NULL DEFAULT 2.5,
              next_review DATE NOT NULL,
              PRIMARY KEY(id),
              UNIQUE KEY uk_user_flashcard (user_id, flashcard_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE xp_history (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              xp INT NOT NULL,
              reason VARCHAR(80) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(id),
              KEY idx_xp_history_user_created (user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE auth_tokens (
              token VARCHAR(64) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(token),
              KEY idx_auth_tokens_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    private function upSqlite(): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
              id VARCHAR(40) NOT NULL,
              email VARCHAR(190) NOT NULL,
              password_hash VARCHAR(255) NOT NULL,
              name VARCHAR(120) NOT NULL,
              role VARCHAR(20) NOT NULL DEFAULT 'student',
              xp INTEGER NOT NULL DEFAULT 0,
              level INTEGER NOT NULL DEFAULT 0,
              streak INTEGER NOT NULL DEFAULT 0,
              last_study_date DATE DEFAULT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_users_email ON users (email)');

        $this->addSql(<<<'SQL'
            CREATE TABLE decks (
              id VARCHAR(40) NOT NULL,
              title VARCHAR(180) NOT NULL,
              description CLOB DEFAULT NULL,
              creator_id VARCHAR(40) NOT NULL,
              visibility VARCHAR(20) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_decks_creator_id ON decks (creator_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE flashcards (
              id VARCHAR(40) NOT NULL,
              deck_id VARCHAR(40) NOT NULL,
              type VARCHAR(20) NOT NULL,
              question CLOB NOT NULL,
              answer CLOB NOT NULL,
              options CLOB NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_flashcards_deck_id ON flashcards (deck_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE study_sessions (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              deck_id VARCHAR(40) NOT NULL,
              total_questions INTEGER NOT NULL,
              answered_questions INTEGER NOT NULL,
              correct_answers INTEGER NOT NULL,
              started_at DATETIME NOT NULL,
              ended_at DATETIME DEFAULT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_study_sessions_user_id ON study_sessions (user_id)');
        $this->addSql('CREATE INDEX idx_study_sessions_deck_id ON study_sessions (deck_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE card_reviews (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              flashcard_id VARCHAR(40) NOT NULL,
              repetition INTEGER NOT NULL,
              interval_days INTEGER NOT NULL,
              ease_factor DOUBLE PRECISION NOT NULL,
              next_review DATE NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uk_user_flashcard ON card_reviews (user_id, flashcard_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE xp_history (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              xp INTEGER NOT NULL,
              reason VARCHAR(80) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_xp_history_user_created ON xp_history (user_id, created_at)');

        $this->addSql(<<<'SQL'
            CREATE TABLE auth_tokens (
              token VARCHAR(64) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY (token)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_auth_tokens_user_id ON auth_tokens (user_id)');
    }
}
