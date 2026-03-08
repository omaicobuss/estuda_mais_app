# Estuda+ - Contratos de API (Fase 1 + 1.1 + 2 + 3)

Base URL: `/api/v1`

## Auth

### POST `/auth/register`

```json
{
  "name": "Alice",
  "email": "alice@estuda.local",
  "password": "123456"
}
```

### POST `/auth/login`

```json
{
  "email": "alice@estuda.local",
  "password": "123456"
}
```

### POST `/auth/refresh`

```json
{
  "refresh_token": "refresh_token"
}
```

### GET `/auth/me`

Header: `Authorization: Bearer <access_token>`

### POST `/auth/logout`

Header: `Authorization: Bearer <access_token>`

## Decks e flashcards

### POST `/decks`

Header: `Authorization: Bearer <access_token>`

Body minimo:

```json
{
  "title": "Matematica Basica",
  "description": "Operacoes fundamentais"
}
```

Body para deck pago:

```json
{
  "title": "Deck Premium Geografia",
  "description": "Conteudo premium",
  "visibility": "paid",
  "price": 29.9
}
```

`visibility` aceita: `private`, `public`, `paid`.

### GET `/decks`

Lista decks nao privados.

### POST `/flashcards`

Header: `Authorization: Bearer <access_token>`

```json
{
  "deck_id": "dek_x",
  "type": "QA",
  "question": "2 + 2",
  "answer": "4",
  "options": []
}
```

## Estudo

### POST `/study/start`

Header: `Authorization: Bearer <access_token>`

```json
{
  "deck_id": "dek_x"
}
```

Regras de acesso:

- deck `private`: somente criador
- deck `paid`: criador ou usuario com compra aprovada

### POST `/study/answer`

Header: `Authorization: Bearer <access_token>`

```json
{
  "session_id": "ses_x",
  "flashcard_id": "crd_x",
  "user_answer": "4"
}
```

Tambem aceita `correct: true|false` para clientes legados.

### POST `/study/finish`

Header: `Authorization: Bearer <access_token>`

```json
{
  "session_id": "ses_x"
}
```

## Ranking

### GET `/rankings/global`

Retorna `items` ordenados por `xp desc`, `streak desc`, `name asc`.

## Fase 2 - Avatar

### GET `/users/profile`

Header: `Authorization: Bearer <access_token>`

### PUT `/users/avatar`

Header: `Authorization: Bearer <access_token>`

```json
{
  "avatar_id": "robot_blue"
}
```

## Fase 2 - Marketplace

### GET `/marketplace/decks`

Lista decks com `visibility = paid` e `price > 0`.

### POST `/marketplace/buy`

Header: `Authorization: Bearer <access_token>`

```json
{
  "deck_id": "dek_paid_x"
}
```

### GET `/marketplace/purchases`

Header: `Authorization: Bearer <access_token>`

## Fase 2 - Desafios

### GET `/challenges`

Header: `Authorization: Bearer <access_token>`

### POST `/challenges/join`

Header: `Authorization: Bearer <access_token>`

```json
{
  "challenge_id": "chl_x"
}
```

### GET `/challenges/{id}`

Header: `Authorization: Bearer <access_token>`

## Fase 3 - IA de cards

### POST `/ai/cards/generate`

Header: `Authorization: Bearer <access_token>`

```json
{
  "deck_id": "dek_x",
  "topic": "Fracoes",
  "count": 5,
  "source_text": "Texto de apoio opcional",
  "persist": true
}
```

Regras:

- somente criador do deck pode gerar cards
- `count` entre 1 e 20
- `persist=false` gera apenas preview

## Fase 3 - Tutor inteligente

### POST `/tutor/assist`

Header: `Authorization: Bearer <access_token>`

```json
{
  "deck_id": "dek_x",
  "question": "Como melhorar minha memorizacao?"
}
```

Retorna:

- diagnostico do aluno (`stage`, `avg_accuracy`, `reviews_due`)
- cards relacionados ao contexto informado
- plano de acao objetivo (`next_actions`)

## Fase 3 - Analytics avancado

### GET `/analytics/overview`

Header: `Authorization: Bearer <access_token>`

Retorna:

- consolidado de sessoes, acuracia, XP e revisoes
- producao de conteudo (decks/cards criados)
- dados de economia/social (compras e desafios)
- serie diaria dos ultimos 7 dias

## Utilidade

### GET `/health`

```json
{
  "data": {
    "status": "ok",
    "service": "estuda-plus-api",
    "version": "fase3"
  }
}
```

## Status codes usados

- `200` sucesso
- `201` criado
- `401` nao autenticado
- `403` sem permissao no recurso
- `404` recurso nao encontrado
- `409` conflito de estado
- `422` validacao
- `500` erro interno

