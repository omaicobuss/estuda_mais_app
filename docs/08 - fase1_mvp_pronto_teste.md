# Estuda+ - Fase 1 MVP pronto para tester

## Objetivo

Concluir o necessario para operar o MVP da Fase 1 com baixo atrito por um usuario tester.

## Checklist de prontidao

- [x] API Fase 1 funcional (auth, flashcards, estudo, XP, streak, ranking)
- [x] Auth hardening Fase 1.1 (logout, expiracao, refresh, revogacao)
- [x] Migration versionada e reprodutivel
- [x] Smoke test ponta a ponta automatizado
- [x] Seed de usuario tester e deck demo
- [x] Endpoint de health
- [x] Home em `/` com instrucoes de uso rapido

## Comandos oficiais de operacao

```bash
composer install
php bin/reset_storage.php
php bin/seed_tester.php
php bin/smoke_test.php
php -S localhost:8080 -t public
```

## Acessos

- Home: `http://localhost:8080/`
- Health: `http://localhost:8080/api/v1/health`
- Credencial tester: `tester@estuda.local / 123456`

## Critério de aceite da Fase 1

O MVP e considerado pronto quando:

1. `bin/smoke_test.php` passa sem falhas
2. tester consegue autenticar e executar uma sessao de estudo
3. XP/streak/ranking refletem o estudo concluido
