<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308043000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fase 2: avatar, marketplace e desafios';
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

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->downMysql();

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->downSqlite();

            return;
        }

        $this->abortIf(true, sprintf('Platform "%s" nao suportada por esta migration.', $platform::class));
    }

    private function upMysql(): void
    {
        $this->addSql('ALTER TABLE users ADD avatar_id VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE decks ADD price DOUBLE PRECISION NOT NULL DEFAULT 0');

        $this->addSql(<<<'SQL'
            CREATE TABLE challenges (
              id VARCHAR(40) NOT NULL,
              title VARCHAR(180) NOT NULL,
              type VARCHAR(30) NOT NULL,
              start_date DATE NOT NULL,
              end_date DATE NOT NULL,
              reward_xp INT NOT NULL DEFAULT 0,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE challenge_participants (
              id VARCHAR(40) NOT NULL,
              challenge_id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              score INT NOT NULL DEFAULT 0,
              joined_at DATETIME NOT NULL,
              PRIMARY KEY(id),
              UNIQUE KEY uk_challenge_participant (challenge_id, user_id),
              KEY idx_challenge_participants_challenge_score (challenge_id, score)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE purchases (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              deck_id VARCHAR(40) NOT NULL,
              price DOUBLE PRECISION NOT NULL DEFAULT 0,
              status VARCHAR(30) NOT NULL,
              payment_gateway VARCHAR(40) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY(id),
              UNIQUE KEY uk_purchases_user_deck (user_id, deck_id),
              KEY idx_purchases_user_created (user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO challenges (id, title, type, start_date, end_date, reward_xp, created_at)
            VALUES ('chl_mvp_weekly', 'Desafio MVP Semanal', 'weekly', '2020-01-01', '2035-12-31', 100, NOW())
        SQL);
    }

    private function upSqlite(): void
    {
        $this->addSql('ALTER TABLE users ADD COLUMN avatar_id VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE decks ADD COLUMN price DOUBLE PRECISION NOT NULL DEFAULT 0');

        $this->addSql(<<<'SQL'
            CREATE TABLE challenges (
              id VARCHAR(40) NOT NULL,
              title VARCHAR(180) NOT NULL,
              type VARCHAR(30) NOT NULL,
              start_date DATE NOT NULL,
              end_date DATE NOT NULL,
              reward_xp INTEGER NOT NULL DEFAULT 0,
              created_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE challenge_participants (
              id VARCHAR(40) NOT NULL,
              challenge_id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              score INTEGER NOT NULL DEFAULT 0,
              joined_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uk_challenge_participant ON challenge_participants (challenge_id, user_id)');
        $this->addSql('CREATE INDEX idx_challenge_participants_challenge_score ON challenge_participants (challenge_id, score)');

        $this->addSql(<<<'SQL'
            CREATE TABLE purchases (
              id VARCHAR(40) NOT NULL,
              user_id VARCHAR(40) NOT NULL,
              deck_id VARCHAR(40) NOT NULL,
              price DOUBLE PRECISION NOT NULL DEFAULT 0,
              status VARCHAR(30) NOT NULL,
              payment_gateway VARCHAR(40) NOT NULL,
              created_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uk_purchases_user_deck ON purchases (user_id, deck_id)');
        $this->addSql('CREATE INDEX idx_purchases_user_created ON purchases (user_id, created_at)');

        $this->addSql(<<<'SQL'
            INSERT INTO challenges (id, title, type, start_date, end_date, reward_xp, created_at)
            VALUES ('chl_mvp_weekly', 'Desafio MVP Semanal', 'weekly', '2020-01-01', '2035-12-31', 100, datetime('now'))
        SQL);
    }

    private function downMysql(): void
    {
        $this->addSql('DROP TABLE purchases');
        $this->addSql('DROP TABLE challenge_participants');
        $this->addSql('DROP TABLE challenges');
        $this->addSql('ALTER TABLE decks DROP COLUMN price');
        $this->addSql('ALTER TABLE users DROP COLUMN avatar_id');
    }

    private function downSqlite(): void
    {
        $this->addSql('DROP TABLE purchases');
        $this->addSql('DROP TABLE challenge_participants');
        $this->addSql('DROP TABLE challenges');

        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__users AS
            SELECT id, email, password_hash, name, role, xp, level, streak, last_study_date, created_at
            FROM users
        SQL);
        $this->addSql('DROP TABLE users');
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
            INSERT INTO users (id, email, password_hash, name, role, xp, level, streak, last_study_date, created_at)
            SELECT id, email, password_hash, name, role, xp, level, streak, last_study_date, created_at
            FROM __temp__users
        SQL);
        $this->addSql('DROP TABLE __temp__users');

        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__decks AS
            SELECT id, title, description, creator_id, visibility, created_at
            FROM decks
        SQL);
        $this->addSql('DROP TABLE decks');
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
            INSERT INTO decks (id, title, description, creator_id, visibility, created_at)
            SELECT id, title, description, creator_id, visibility, created_at
            FROM __temp__decks
        SQL);
        $this->addSql('DROP TABLE __temp__decks');
    }
}
