# Estuda+ - Arquitetura, Qualidade e Padroes (MVP Fase 1)

## Direcao arquitetural adotada

Arquitetura modular em camadas:

1. `Domain`: regras de negocio puras
2. `Application`: casos de uso e contratos
3. `Infrastructure`: adaptadores Doctrine (persistencia) e seguranca
4. `Interface`: API HTTP (Symfony Controller + Subscriber)

Runtime consolidado em Symfony 7.4 com DI/autowiring.

## Padroes de projeto usados

### 1) Repository Pattern

- Interfaces em `src/Application/Contracts`
- Implementacoes ativas em `src/Infrastructure/Persistence/Doctrine/Doctrine*Repository.php`
- Mapeamento dominio <-> record via `RecordMapper`

### 2) Use Case / Application Service

- Handlers por fluxo (`RegisterUserHandler`, `StartStudyHandler`, `FinishStudyHandler` etc.)
- Controllers apenas orquestram request/response

### 3) Policy Pattern

- `XpPolicy` centraliza regras de bonificacao

### 4) Exception Translation

- `ApiExceptionSubscriber` traduz excecoes de negocio para resposta JSON padronizada

## Qualidade tecnica aplicada

### Coesao de camadas

- dominio sem dependencia de framework
- casos de uso sem dependencia de HTTP/Doctrine
- acoplamento a Symfony/Doctrine isolado na infraestrutura/interface

### Persistencia e consistencia

- entidades Doctrine mapeadas com atributos
- migrations versionadas em `migrations/`
- validacao de schema com `doctrine:schema:validate`

### Seguranca basica

- hash de senha com bcrypt
- token Bearer em tabela `auth_tokens`
- validacao centralizada de autenticacao via `AuthenticatedUserResolver`
- expiracao por TTL (`expires_at`)
- revogacao imediata no logout (`revoked_at`)
- refresh token com rotacao de sessão (`refresh_token`, `refresh_expires_at`)

### Resiliencia de API

- validações com `422`
- conflitos com `409`
- autenticacao com `401`
- fallback para erro interno `500`

## Trade-offs atuais

1. Nao ha limite de sessoes simultaneas por usuario.
2. Ranking calculado em consulta de aplicacao (sem cache Redis).
3. RBAC detalhado por papel ainda nao implementado.

## Pronto para evolucao

1. introduzir Redis para ranking/cache
2. adicionar testes unitarios e integracao HTTP no CI
3. ativar observabilidade (logs estruturados + Sentry)
4. adicionar blacklist/limite por dispositivo para sessões
