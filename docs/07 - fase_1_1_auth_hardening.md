# Estuda+ - Fase 1.1 (Auth Hardening)

## Escopo executado

1. logout de sessão (`POST /api/v1/auth/logout`)
2. expiração de token (`expires_at`)
3. revogação de token (`revoked_at`)
4. refresh token com rotação (`POST /api/v1/auth/refresh`)
5. TTL de token por variável de ambiente (`AUTH_TOKEN_TTL_SECONDS` e `AUTH_REFRESH_TOKEN_TTL_SECONDS`)

## Mudanças técnicas

- contrato atualizado: `TokenRepositoryInterface::revokeToken()`
- novo caso de uso: `LogoutUserHandler`
- novo caso de uso: `RefreshSessionHandler`
- resolução de token extraída para `AuthenticatedUserResolver::requireToken()`
- controller atualizado para endpoint de logout
- repositório Doctrine valida token por:
  - existência
  - não revogado
  - não expirado
- repositório Doctrine rotaciona sessão no refresh:
  - revoga token atual
  - emite novo par access/refresh

## Mudança de banco

Migration adicionada:

- `migrations/Version20260308021000.php`
- `migrations/Version20260308030000.php`

Campos novos em `auth_tokens`:

- `expires_at` (`DATETIME NOT NULL`)
- `revoked_at` (`DATETIME NULL`)
- `refresh_token` (`VARCHAR(64) NOT NULL`)
- `refresh_expires_at` (`DATETIME NOT NULL`)

## Evidência de validação

Comandos:

```bash
php bin/reset_storage.php
php bin/smoke_test.php
php bin/console doctrine:schema:validate
```

Resultado:

- smoke test validando `login -> refresh -> me(token antigo=401) -> logout -> me(401)` com sucesso
- schema em sincronia com mapeamentos Doctrine
