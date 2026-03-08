# Estuda+ - Rastreabilidade do MVP (Fase 1, 1.1, 2 e 3)

## Matriz de rastreabilidade

| Roadmap | Item | Status | Implementacao principal |
| --- | --- | --- | --- |
| Fase 1 | login | Entregue | `RegisterUserHandler`, `LoginUserHandler`, `MvpController` |
| Fase 1 | flashcards | Entregue | `CreateDeckHandler`, `CreateFlashcardHandler`, `DoctrineDeckRepository`, `DoctrineFlashcardRepository` |
| Fase 1 | estudo | Entregue | `StartStudyHandler`, `AnswerStudyHandler`, `FinishStudyHandler` |
| Fase 1 | XP | Entregue | `XpPolicy`, `FinishStudyHandler`, `DoctrineXpHistoryRepository` |
| Fase 1 | streak | Entregue | `User::markStudyOn`, `FinishStudyHandler` |
| Fase 1 | ranking | Entregue | `GetGlobalRankingHandler`, `GET /api/v1/rankings/global` |
| Fase 1.1 | logout | Entregue | `LogoutUserHandler`, `POST /api/v1/auth/logout` |
| Fase 1.1 | expiracao/revogacao | Entregue | `DoctrineTokenRepository`, `AUTH_TOKEN_TTL_SECONDS`, `revoked_at` |
| Fase 1.1 | refresh com rotacao | Entregue | `RefreshSessionHandler`, `POST /api/v1/auth/refresh` |
| Fase 2 | avatar | Entregue | `GetUserProfileHandler`, `UpdateAvatarHandler` |
| Fase 2 | marketplace | Entregue | `ListMarketplaceDecksHandler`, `BuyMarketplaceDeckHandler`, `ListPurchasesHandler` |
| Fase 2 | desafios | Entregue | `ListChallengesHandler`, `JoinChallengeHandler`, `GetChallengeDetailsHandler` |
| Fase 3 | IA para cards | Entregue | `GenerateAiFlashcardsHandler`, `TemplateAiCardGenerator`, `POST /ai/cards/generate` |
| Fase 3 | tutor inteligente | Entregue | `AssistTutorHandler`, `POST /tutor/assist` |
| Fase 3 | analytics avancado | Entregue | `GetAnalyticsOverviewHandler`, `DoctrineAnalyticsReadModel`, `GET /analytics/overview` |

## Mudancas de dados por fase

Fase 1:

- `Version20260308005037` cria tabelas base do MVP

Fase 1.1:

- `Version20260308021000` adiciona `expires_at`, `revoked_at` em `auth_tokens`
- `Version20260308030000` adiciona `refresh_token`, `refresh_expires_at`

Fase 2:

- `Version20260308043000` adiciona:
  - `users.avatar_id`
  - `decks.price`
  - `challenges`
  - `challenge_participants`
  - `purchases`

Fase 3:

- sem novas tabelas
- leitura analitica baseada em dados ja persistidos de estudo, XP, revisoes, compras e desafios

## Evidencias de validacao

```bash
php bin/reset_storage.php
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
php bin/seed_tester.php
php bin/smoke_test.php
php bin/console debug:router
php bin/console lint:container
```

Resultado:

- migrations aplicadas ate `Version20260308043000`
- schema em sincronia com metadados Doctrine
- smoke test cobrindo Fase 1, 1.1, 2 e 3 com sucesso
- rotas da Fase 3 publicadas no router

## Backlog tecnico (pos-Fase 3)

1. Integracao de LLM externa com controle de custo para geracao de cards
2. Memoria de conversa do tutor por aluno e por deck
3. Dashboard web para analytics (frontend)
4. Testes unitarios por caso de uso no CI
5. Cache de ranking/analytics com Redis

