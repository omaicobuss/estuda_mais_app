# BLUEPRINT TÉCNICO --- Estuda+

PWA educacional gamificada baseada em flashcards

Versão: 1.0\
Domínio: estudamais.app.br

------------------------------------------------------------------------

# 1. Visão Geral do Sistema

O **Estuda+** é uma plataforma de aprendizado gamificada baseada em
**flashcards e repetição espaçada**, voltada para estudantes do ensino
fundamental.

A aplicação será construída como **PWA (Progressive Web App)** com
backend em **Symfony API**.

## Objetivos técnicos

-   suportar até **1.000 usuários**
-   baixa latência
-   arquitetura simples e escalável
-   modularização para evolução futura

------------------------------------------------------------------------

# 2. Arquitetura do Sistema

Arquitetura monolítica modular baseada em **Clean Architecture / DDD
simplificado**.

PWA (Twig + Stimulus) ↓ Symfony API ↓ Application Layer ↓ Domain Layer ↓
Infrastructure Layer ↓ MySQL Database

Integrações externas:

-   MercadoPago API
-   Email Service
-   Push Notifications
-   Ads Network

------------------------------------------------------------------------

# 3. Stack Tecnológico

## Backend

-   PHP 8.3
-   Symfony 7
-   Doctrine ORM
-   Symfony Security

## Frontend

-   Twig
-   StimulusJS
-   Turbo (opcional)
-   PWA Service Worker

## Banco de dados

-   **MySQL 5.7**

## Infraestrutura

-   Nginx
-   PHP-FPM
-   Redis (cache opcional)

------------------------------------------------------------------------

# 4. Estrutura do Projeto Symfony

src/ ├ Domain │ ├ User │ ├ School │ ├ Deck │ ├ Flashcard │ ├ Study │ ├
XP │ ├ Ranking │ ├ Challenge │ ├ Marketplace │ ├ Application │ ├
Services │ ├ UseCases │ ├ DTO │ ├ Infrastructure │ ├ Persistence │ ├
MercadoPago │ ├ Notification │ ├ Ads │ ├ Interface │ ├ Controllers │ ├
API │ ├ Forms │ ├ Twig

------------------------------------------------------------------------

# 5. Módulos do Sistema

1.  Auth\
2.  Usuários\
3.  Escolas\
4.  Decks\
5.  Flashcards\
6.  Estudo\
7.  Gamificação\
8.  Rankings\
9.  Desafios\
10. Marketplace\
11. Estatísticas\
12. Administração

------------------------------------------------------------------------

# 6. Modelo de Banco de Dados

## users

id\
email\
password\
name\
birth_date\
role\
xp\
level\
streak\
avatar_id\
created_at

roles: - student - teacher - parent - admin

## schools

id\
name\
city\
state\
created_at

## classrooms

id\
school_id\
name\
code\
teacher_id

## classroom_users

classroom_id\
user_id

## disciplines

id\
name

## subjects

id\
discipline_id\
name

## decks

id\
title\
description\
discipline_id\
subject_id\
grade\
creator_id\
visibility\
price\
rating\
created_at

visibility: - private - public - paid

## flashcards

id\
deck_id\
type\
question\
answer\
options\
image

types: - QA - MULTIPLE - TRUE_FALSE

## study_sessions

id\
user_id\
deck_id\
started_at\
ended_at\
score

## card_reviews

id\
user_id\
flashcard_id\
repetition\
interval\
ease_factor\
next_review

## xp_history

id\
user_id\
xp\
reason\
created_at

## challenges

id\
title\
type\
start_date\
end_date\
reward_xp

## challenge_participants

id\
challenge_id\
user_id\
score

## rankings

id\
type\
scope\
scope_id\
period

## purchases

id\
user_id\
deck_id\
price\
status\
payment_gateway\
created_at

## reviews

id\
deck_id\
user_id\
rating\
comment

------------------------------------------------------------------------

# 7. Estrutura da API

Base URL:

/api/v1

## Auth

POST /auth/register\
POST /auth/login\
GET /auth/me\
POST /auth/logout

## Usuários

GET /users/profile\
PUT /users/profile\
GET /users/stats

## Escolas

GET /schools\
POST /schools\
GET /schools/{id}

## Turmas

POST /classrooms\
GET /classrooms/{id}\
POST /classrooms/join

## Decks

GET /decks\
POST /decks\
GET /decks/{id}\
PUT /decks/{id}\
DELETE /decks/{id}

## Flashcards

POST /flashcards\
PUT /flashcards/{id}\
DELETE /flashcards/{id}

## Estudo

POST /study/start\
POST /study/answer\
POST /study/finish\
GET /study/review

## Rankings

GET /rankings/global\
GET /rankings/school/{id}\
GET /rankings/classroom/{id}

## Desafios

GET /challenges\
POST /challenges/join\
GET /challenges/{id}

## Marketplace

GET /marketplace/decks\
POST /marketplace/buy\
GET /marketplace/purchases

------------------------------------------------------------------------

# 8. Algoritmo de Repetição Espaçada (SM‑2)

Cada flashcard possui:

repetition\
interval\
ease_factor\
next_review

Valores iniciais:

repetition = 0\
interval = 1\
ease_factor = 2.5

Atualização:

Se resposta correta:

interval = interval \* ease_factor\
ease_factor = ease_factor + 0.1

Se errada:

repetition = 0\
interval = 1

Próxima revisão:

next_review = today + interval

------------------------------------------------------------------------

# 9. Sistema de XP

Completar deck → 50 XP\
Streak diário → 10 XP\
Desafio vencido → 100 XP\
Alta taxa de acerto → 20 XP

------------------------------------------------------------------------

# 10. Sistema de Níveis

level = sqrt(xp / 100)

------------------------------------------------------------------------

# 11. Sistema de Streak

Se estudou hoje → streak++\
Senão → streak reset

Recuperação por desafio especial.

------------------------------------------------------------------------

# 12. Sistema de Desafios

Tipos: - diário - semanal - amigo - turma - escola

Recompensas: - XP - medalhas - skins

------------------------------------------------------------------------

# 13. Marketplace

Fluxo:

Selecionar deck\
→ Pagamento MercadoPago\
→ Webhook confirma pagamento\
→ Liberar acesso ao deck

------------------------------------------------------------------------

# 14. Sistema de Moderação

Administrador pode:

-   aprovar decks
-   remover decks
-   banir usuários
-   analisar denúncias

------------------------------------------------------------------------

# 15. Segurança

-   bcrypt para senhas
-   proteção CSRF
-   rate limiting
-   validação de uploads

------------------------------------------------------------------------

# 16. Sistema de Notificações

Tipos:

-   push notification
-   email
-   notificações internas

Eventos:

-   revisão diária
-   desafio iniciado
-   conquista desbloqueada

------------------------------------------------------------------------

# 17. PWA

Arquivos principais:

manifest.json\
service-worker.js

Recursos:

-   instalação no dispositivo
-   cache de assets
-   funcionamento offline parcial

------------------------------------------------------------------------

# 18. Infraestrutura

Nginx\
PHP-FPM\
Symfony\
MySQL 5.7

Servidor sugerido:

2 vCPU\
4GB RAM\
40GB SSD

------------------------------------------------------------------------

# 19. Logs e Monitoramento

-   Monolog
-   Sentry (erros)
-   Grafana (futuro)

------------------------------------------------------------------------

# 20. Roadmap Técnico

## Fase 1 (MVP)

-   login
-   flashcards
-   estudo
-   XP
-   streak
-   ranking

## Fase 2

-   marketplace
-   desafios
-   avatar

## Fase 3

-   IA para geração de cards
-   tutor inteligente
-   analytics avançado
