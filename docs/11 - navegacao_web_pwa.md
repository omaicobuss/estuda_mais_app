# Estuda+ - Navegacao Web e PWA (Desktop + Smartphone)

## Objetivo

Disponibilizar uma interface de navegacao completa no navegador para uso real do app, com adaptacao para smartphone via PWA.

## Plano executado

1. Criar uma SPA unica em `public/app/` para consumir a API `/api/v1`
2. Organizar navegacao por modulos do produto (auth, estudo, marketplace, desafios, tutor, analytics)
3. Implementar layout responsivo para desktop e mobile
4. Adicionar suporte PWA (manifest + service worker + icones)
5. Redirecionar `/` para `/app/` como ponto de entrada unico do frontend

## Arquivos implementados

- `public/app/index.html`
- `public/app/styles.css`
- `public/app/app.js`
- `public/app/manifest.webmanifest`
- `public/app/sw.js`
- `public/app/icons/icon-192.svg`
- `public/app/icons/icon-512.svg`
- `src/Controller/HomeController.php`

## Modulos da navegacao

1. Dashboard
2. Login/Cadastro
3. Perfil/Avatar
4. Decks e Flashcards
5. Estudo
6. Marketplace
7. Desafios
8. Tutor inteligente
9. Analytics avancado

## Detalhes tecnicos de front

- SPA em JavaScript puro (sem framework JS externo)
- sessao persistida em `localStorage`
- cliente HTTP com refresh automatico de token ao receber `401`
- carregamento por view com fetch em tempo real para endpoints existentes
- navegacao lateral (desktop) + barra inferior (mobile)

## Recursos PWA para smartphone

- `display: standalone` no manifest
- service worker com cache do app shell
- instalacao via evento `beforeinstallprompt`
- icones dedicados para homescreen

## Como usar

1. `php bin/reset_storage.php`
2. `php bin/seed_tester.php`
3. `php -S localhost:8080 -t public`
4. abrir `http://localhost:8080/`
5. autenticar com `tester@estuda.local / 123456`

