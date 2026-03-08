# Estuda+ - Migracao para Symfony + Doctrine

## Objetivo

Trocar runtime custom por Symfony e persistencia JSON por Doctrine, preservando contratos dos casos de uso do MVP.

## Resultado da migracao

1. Runtime HTTP consolidado em Symfony 7.4 (`public/index.php`, `src/Kernel.php`).
2. Endpoints centralizados em `src/Controller/Api/MvpController.php`.
3. Erros de negocio padronizados em JSON via `ApiExceptionSubscriber`.
4. Persistencia migrada para Doctrine ORM (`Entity` + `Doctrine*Repository`).
5. Migrations versionadas e incrementais por fase:
   - `Version20260308005037` (Fase 1)
   - `Version20260308021000` (Fase 1.1 - expiracao/revogacao)
   - `Version20260308030000` (Fase 1.1 - refresh token)
   - `Version20260308043000` (Fase 2 - avatar/marketplace/desafios)

## Compatibilidade de arquitetura

Os pacotes de dominio e application foram mantidos como base da regra de negocio.

- `Domain`: entidades e regras puras
- `Application`: use cases/handlers e contratos
- `Infrastructure`: Doctrine + mapeamentos
- `Interface`: controllers HTTP e resolucao de autenticacao

## Banco e ambiente

Default local: SQLite

```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
```

Alvo blueprint: MySQL 5.7

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/estuda_mais_app?serverVersion=5.7&charset=utf8mb4"
```

## Validacao da migracao

```bash
php bin/reset_storage.php
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
php bin/smoke_test.php
```

## Riscos remanescentes

1. Marketplace usa gateway simulado.
2. Sem suite formal de testes unitarios no CI.
3. Sem cache para ranking em escala maior.

