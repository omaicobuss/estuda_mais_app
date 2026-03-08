# Estuda+ MVP - Fase 3

Implementacao executavel do MVP do Estuda+ com as fases:

- Fase 1: login, flashcards, estudo, XP, streak, ranking
- Fase 1.1: logout, refresh com rotacao, TTL e revogacao de token
- Fase 2: marketplace, desafios e avatar
- Fase 3: IA para geracao de cards, tutor inteligente e analytics avancado

## Stack

- PHP 8.3
- Symfony 7.4
- Doctrine ORM + Doctrine Migrations
- Arquitetura modular (Domain/Application/Infrastructure/Interface)
- Banco default local: SQLite
- Banco alvo do blueprint: MySQL 5.7 (via `DATABASE_URL`)

## Como executar

1. Instalar dependencias:

```bash
composer install
```

2. Resetar banco e aplicar migrations:

```bash
php bin/reset_storage.php
```

3. Seed de ambiente de teste:

```bash
php bin/seed_tester.php
```

4. Validacao ponta a ponta:

```bash
php bin/smoke_test.php
```

5. Subir API local:

```bash
php -S localhost:8080 -t public
```

6. Validacoes opcionais:

```bash
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
php bin/console debug:router
php bin/console lint:container
```

## Acesso rapido no navegador

- `http://localhost:8080/` (redireciona para a interface web)
- `http://localhost:8080/app/` (SPA de navegacao do app)
- `http://localhost:8080/api/v1/health`

Credencial seed:

- `tester@estuda.local / 123456`

## Navegacao Web (Desktop + Smartphone)

Interface web implementada em:

- `public/app/index.html`
- `public/app/styles.css`
- `public/app/app.js`

Fluxos disponiveis na navegacao:

- login/cadastro/logout
- perfil/avatar
- decks e flashcards (manual + geracao por IA)
- estudo (start/answer/finish)
- marketplace (comprar e listar compras)
- desafios (listar, entrar, detalhes)
- tutor inteligente
- analytics avancado

Suporte mobile/PWA:

- manifest: `public/app/manifest.webmanifest`
- service worker: `public/app/sw.js`
- icones: `public/app/icons/`
- botao "Instalar App" habilitado quando o navegador permitir

## Endpoints do MVP

Auth:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/refresh`
- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`

Decks e flashcards:

- `POST /api/v1/decks`
- `GET /api/v1/decks`
- `POST /api/v1/flashcards`

Estudo e ranking:

- `POST /api/v1/study/start`
- `POST /api/v1/study/answer`
- `POST /api/v1/study/finish`
- `GET /api/v1/rankings/global`

Fase 2:

- `GET /api/v1/users/profile`
- `PUT /api/v1/users/avatar`
- `GET /api/v1/marketplace/decks`
- `POST /api/v1/marketplace/buy`
- `GET /api/v1/marketplace/purchases`
- `GET /api/v1/challenges`
- `POST /api/v1/challenges/join`
- `GET /api/v1/challenges/{id}`

Fase 3:

- `POST /api/v1/ai/cards/generate`
- `POST /api/v1/tutor/assist`
- `GET /api/v1/analytics/overview`

Utilidade:

- `GET /api/v1/health`

## Documentacao

- [Blueprint tecnico](docs/01%20-%20estuda_plus_blueprint_tecnico.md)
- [Execucao MVP Fase 1](docs/02%20-%20mvp_fase1_execucao.md)
- [Arquitetura e qualidade Fase 1](docs/03%20-%20mvp_fase1_arquitetura_qualidade.md)
- [Contratos de API (Fase 1 + 1.1 + 2 + 3)](docs/04%20-%20mvp_fase1_api_contratos.md)
- [Rastreabilidade e backlog](docs/05%20-%20mvp_fase1_rastreabilidade_backlog.md)
- [Migracao Symfony + Doctrine](docs/06%20-%20migracao_symfony_doctrine.md)
- [Fase 1.1 Auth Hardening](docs/07%20-%20fase_1_1_auth_hardening.md)
- [Fase 1 pronta para teste](docs/08%20-%20fase1_mvp_pronto_teste.md)
- [Fase 2 implementacao e qualidade](docs/09%20-%20fase2_mvp_execucao_qualidade.md)
- [Fase 3 implementacao e qualidade](docs/10%20-%20fase3_mvp_execucao_qualidade.md)
- [Navegacao web e PWA](docs/11%20-%20navegacao_web_pwa.md)
