# Estuda+ - Fase 2 (MVP) Execucao e Qualidade

## Escopo implementado

Itens do roadmap da Fase 2 entregues:

1. Avatar
2. Marketplace
3. Desafios

## Entregas funcionais

### Avatar

- `GET /api/v1/users/profile`
- `PUT /api/v1/users/avatar`
- persistencia de `avatar_id` no usuario

Arquivos principais:

- `src/Application/User/GetUserProfileHandler.php`
- `src/Application/User/UpdateAvatarHandler.php`
- `src/Domain/User/User.php`
- `src/Infrastructure/Persistence/Doctrine/Entity/UserRecord.php`

### Marketplace

- `GET /api/v1/marketplace/decks`
- `POST /api/v1/marketplace/buy`
- `GET /api/v1/marketplace/purchases`
- controle de acesso no estudo para decks `private/paid`

Arquivos principais:

- `src/Application/Marketplace/ListMarketplaceDecksHandler.php`
- `src/Application/Marketplace/BuyMarketplaceDeckHandler.php`
- `src/Application/Marketplace/ListPurchasesHandler.php`
- `src/Domain/Marketplace/Purchase.php`
- `src/Application/Study/StartStudyHandler.php`
- `src/Infrastructure/Persistence/Doctrine/Entity/PurchaseRecord.php`
- `src/Infrastructure/Persistence/Doctrine/DoctrinePurchaseRepository.php`

### Desafios

- `GET /api/v1/challenges`
- `POST /api/v1/challenges/join`
- `GET /api/v1/challenges/{id}`

Arquivos principais:

- `src/Application/Challenge/ListChallengesHandler.php`
- `src/Application/Challenge/JoinChallengeHandler.php`
- `src/Application/Challenge/GetChallengeDetailsHandler.php`
- `src/Domain/Challenge/Challenge.php`
- `src/Domain/Challenge/ChallengeParticipant.php`
- `src/Infrastructure/Persistence/Doctrine/Entity/ChallengeRecord.php`
- `src/Infrastructure/Persistence/Doctrine/Entity/ChallengeParticipantRecord.php`
- `src/Infrastructure/Persistence/Doctrine/DoctrineChallengeRepository.php`
- `src/Infrastructure/Persistence/Doctrine/DoctrineChallengeParticipantRepository.php`

## Mudancas estruturais de banco

Migration: `migrations/Version20260308043000.php`

- adiciona `users.avatar_id`
- adiciona `decks.price`
- cria `challenges`
- cria `challenge_participants`
- cria `purchases`
- insere desafio base `chl_mvp_weekly` para bootstrap de teste

## Cuidados de arquitetura e padroes

1. Repository Pattern
- contratos em `src/Application/Contracts`
- implementacoes Doctrine em `src/Infrastructure/Persistence/Doctrine`

2. Use Case Pattern
- regras em handlers de `Application`
- controller sem regra de negocio (somente orquestracao HTTP)

3. Separacao de camadas
- `Domain` sem dependencia de Symfony/Doctrine
- `Application` sem dependencia de request/response HTTP
- `Infrastructure` isolando persistencia

4. Regras de acesso explicitas
- `StartStudyHandler` protege acesso a decks `private` e `paid`
- compra duplicada bloqueada (`409`)
- inscricao duplicada em desafio bloqueada (`409`)

5. Contratos de erro padronizados
- `ApiException` + `ApiExceptionSubscriber`
- uso consistente de `401`, `403`, `404`, `409`, `422`

## Operacao e validacao

Scripts atualizados:

- `bin/reset_storage.php`
- `bin/seed_tester.php`
- `bin/smoke_test.php`

Comandos usados na validacao final:

```bash
php bin/reset_storage.php
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
php bin/seed_tester.php
php bin/smoke_test.php
php bin/console debug:router
php bin/console lint:container
```

Resultado final:

- schema em sync
- rotas publicadas
- smoke test Fase 2 aprovado

## Limites atuais do MVP

1. gateway de pagamento ainda simulado (`simulated`)
2. desafios sem atualizacao automatica de score
3. ausencia de testes unitarios em pipeline CI

