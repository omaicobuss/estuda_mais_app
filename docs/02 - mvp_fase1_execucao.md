# Estuda+ - Execucao do MVP (Fase 1)

## Objetivo da entrega

Executar a Fase 1 do roadmap tecnico do blueprint para disponibilizar um fluxo funcional de:

1. autenticacao (register/login/me)
2. criacao de decks e flashcards
3. sessao de estudo com respostas
4. regras de XP e streak
5. ranking global

## Escopo implementado

### Auth

- cadastro de usuario com senha hash bcrypt
- login com emissao de token Bearer persistido em `auth_tokens`
- endpoint para leitura do usuario autenticado

### Flashcards

- criacao de deck
- listagem de decks
- criacao de flashcards vinculados ao deck

### Estudo (SM-2 simplificado)

- inicio de sessao de estudo por deck
- resposta de cards com avaliacao correta/incorreta (calculada no servidor quando `user_answer` e enviado)
- atualizacao de `card_reviews` com:
  - `repetition`
  - `interval_days`
  - `ease_factor`
  - `next_review`

### Gamificacao (XP e streak)

Regras aplicadas na finalizacao da sessao:

- deck completo: +50 XP
- acuracia >= 80%: +20 XP
- primeiro estudo do dia: +10 XP

Streak:

- se estudar em dias consecutivos: incrementa
- se quebrar sequencia: reinicia em 1 no proximo dia estudado
- multiplas sessoes no mesmo dia nao acumulam bonus de streak

### Ranking

- ranking global ordenado por `xp desc`, `streak desc`, `name asc`

## Runtime e persistencia atuais

- runtime HTTP via Symfony 7.4 (`public/index.php` + `App\Kernel`)
- persistencia via Doctrine ORM
- migrations versionadas em `migrations/`
- banco local default: SQLite (`var/data_dev.db`)
- banco alvo blueprint: MySQL 5.7 via `DATABASE_URL`

## Evolucao Fase 1.1 implementada

- endpoint `POST /api/v1/auth/logout`
- endpoint `POST /api/v1/auth/refresh`
- revogação de token com `revoked_at`
- expiração de token com `expires_at`
- rotação de sessão no refresh (token antigo invalida)
- TTL configurável por `AUTH_TOKEN_TTL_SECONDS` e `AUTH_REFRESH_TOKEN_TTL_SECONDS`
- migration incremental: `Version20260308021000`
- migration incremental: `Version20260308030000`

## Validacao executada

Comandos executados:

```bash
php bin/reset_storage.php
php bin/smoke_test.php
php bin/seed_tester.php
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
```

Resultado observado:

- smoke test concluido com sucesso
- fluxo ponta a ponta valido com usuario ganhando XP, streak e aparecendo no ranking
- seed de usuário tester concluido com deck demo
- migrations aplicadas com sucesso
- schema em sincronia com metadados Doctrine

## Fluxo minimo para tester

1. executar `php bin/reset_storage.php`
2. executar `php bin/seed_tester.php`
3. subir servidor `php -S localhost:8080 -t public`
4. acessar `http://localhost:8080/`
5. autenticar com `tester@estuda.local / 123456`

## Limitacoes conscientes desta fase

- token sem expiracao/revogacao
- sem RBAC detalhado por papel
- sem notificacoes push/email
- sem testes unitarios por modulo (ha smoke test ponta a ponta)

## Proximo passo tecnico sugerido (Fase 1.1)

Adicionar expiracao/revogacao de token e testes de integracao HTTP automatizados em pipeline CI.
