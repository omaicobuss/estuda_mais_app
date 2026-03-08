<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308021000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fase 1.1: expiração e revogação de token de autenticação';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql("ALTER TABLE auth_tokens ADD expires_at DATETIME DEFAULT NULL, ADD revoked_at DATETIME DEFAULT NULL");
            $this->addSql("UPDATE auth_tokens SET expires_at = DATE_ADD(created_at, INTERVAL 30 DAY) WHERE expires_at IS NULL");
            $this->addSql("ALTER TABLE auth_tokens MODIFY expires_at DATETIME NOT NULL");

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->addSql('ALTER TABLE auth_tokens ADD COLUMN expires_at DATETIME DEFAULT NULL');
            $this->addSql('ALTER TABLE auth_tokens ADD COLUMN revoked_at DATETIME DEFAULT NULL');
            $this->addSql("UPDATE auth_tokens SET expires_at = datetime(created_at, '+30 day') WHERE expires_at IS NULL");

            $this->addSql('CREATE TEMPORARY TABLE __temp__auth_tokens AS SELECT token, user_id, created_at, expires_at, revoked_at FROM auth_tokens');
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

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('ALTER TABLE auth_tokens DROP COLUMN revoked_at, DROP COLUMN expires_at');

            return;
        }

        if ($platform instanceof SQLitePlatform) {
            $this->addSql('CREATE TEMPORARY TABLE __temp__auth_tokens AS SELECT token, user_id, created_at FROM auth_tokens');
            $this->addSql('DROP TABLE auth_tokens');
            $this->addSql(<<<'SQL'
                CREATE TABLE auth_tokens (
                  token VARCHAR(64) NOT NULL,
                  user_id VARCHAR(40) NOT NULL,
                  created_at DATETIME NOT NULL,
                  PRIMARY KEY (token)
                )
            SQL);
            $this->addSql('CREATE INDEX idx_auth_tokens_user_id ON auth_tokens (user_id)');
            $this->addSql('INSERT INTO auth_tokens (token, user_id, created_at) SELECT token, user_id, created_at FROM __temp__auth_tokens');
            $this->addSql('DROP TABLE __temp__auth_tokens');

            return;
        }

        $this->abortIf(true, sprintf('Platform "%s" nao suportada por esta migration.', $platform::class));
    }
}
