<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fase 1.1: refresh token e rotação de sessão';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('ALTER TABLE auth_tokens ADD refresh_token VARCHAR(64) DEFAULT NULL, ADD refresh_expires_at DATETIME DEFAULT NULL');
            $this->addSql("UPDATE auth_tokens SET refresh_token = token, refresh_expires_at = DATE_ADD(created_at, INTERVAL 60 DAY) WHERE refresh_token IS NULL");
            $this->addSql('ALTER TABLE auth_tokens MODIFY refresh_token VARCHAR(64) NOT NULL, MODIFY refresh_expires_at DATETIME NOT NULL');
            $this->addSql('CREATE UNIQUE INDEX uniq_auth_tokens_refresh_token ON auth_tokens (refresh_token)');

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->addSql('ALTER TABLE auth_tokens ADD COLUMN refresh_token VARCHAR(64) DEFAULT NULL');
            $this->addSql('ALTER TABLE auth_tokens ADD COLUMN refresh_expires_at DATETIME DEFAULT NULL');
            $this->addSql("UPDATE auth_tokens SET refresh_token = token, refresh_expires_at = datetime(created_at, '+60 day') WHERE refresh_token IS NULL");

            $this->addSql(<<<'SQL'
                CREATE TEMPORARY TABLE __temp__auth_tokens AS
                SELECT token, user_id, created_at, expires_at, revoked_at, refresh_token, refresh_expires_at
                FROM auth_tokens
            SQL);
            $this->addSql('DROP TABLE auth_tokens');
            $this->addSql(<<<'SQL'
                CREATE TABLE auth_tokens (
                  token VARCHAR(64) NOT NULL,
                  user_id VARCHAR(40) NOT NULL,
                  created_at DATETIME NOT NULL,
                  expires_at DATETIME NOT NULL,
                  revoked_at DATETIME DEFAULT NULL,
                  refresh_token VARCHAR(64) NOT NULL,
                  refresh_expires_at DATETIME NOT NULL,
                  PRIMARY KEY (token)
                )
            SQL);
            $this->addSql('CREATE INDEX idx_auth_tokens_user_id ON auth_tokens (user_id)');
            $this->addSql('CREATE UNIQUE INDEX uniq_auth_tokens_refresh_token ON auth_tokens (refresh_token)');
            $this->addSql(<<<'SQL'
                INSERT INTO auth_tokens (token, user_id, created_at, expires_at, revoked_at, refresh_token, refresh_expires_at)
                SELECT token, user_id, created_at, expires_at, revoked_at, refresh_token, refresh_expires_at
                FROM __temp__auth_tokens
            SQL);
            $this->addSql('DROP TABLE __temp__auth_tokens');

            return;
        }

        $this->abortIf(true, sprintf('Platform "%s" nao suportada por esta migration.', $platform::class));
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('DROP INDEX uniq_auth_tokens_refresh_token ON auth_tokens');
            $this->addSql('ALTER TABLE auth_tokens DROP COLUMN refresh_token, DROP COLUMN refresh_expires_at');

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->addSql(<<<'SQL'
                CREATE TEMPORARY TABLE __temp__auth_tokens AS
                SELECT token, user_id, created_at, expires_at, revoked_at
                FROM auth_tokens
            SQL);
            $this->addSql('DROP TABLE auth_tokens');
            $this->addSql(<<<'SQL'
                CREATE TABLE auth_tokens (
                  token VARCHAR(64) NOT NULL,
                  user_id VARCHAR(40) NOT NULL,
                  created_at DATETIME NOT NULL,
                  expires_at DATETIME NOT NULL,
                  revoked_at DATETIME DEFAULT NULL,
                  PRIMARY KEY (token)
                )
            SQL);
            $this->addSql('CREATE INDEX idx_auth_tokens_user_id ON auth_tokens (user_id)');
            $this->addSql(<<<'SQL'
                INSERT INTO auth_tokens (token, user_id, created_at, expires_at, revoked_at)
                SELECT token, user_id, created_at, expires_at, revoked_at
                FROM __temp__auth_tokens
            SQL);
            $this->addSql('DROP TABLE __temp__auth_tokens');

            return;
        }

        $this->abortIf(true, sprintf('Platform "%s" nao suportada por esta migration.', $platform::class));
    }
}
