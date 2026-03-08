function cleanPath(path) {
  if (!path || path === "/") {
    return "";
  }

  const normalized = path.replace(/\/+/g, "/").replace(/\/$/, "");
  if (normalized === "" || normalized === "/") {
    return "";
  }

  return normalized.startsWith("/") ? normalized : `/${normalized}`;
}

function appBasePath() {
  const pathname = window.location.pathname || "/";
  const match = pathname.match(/^(.*)\/app(?:\/index\.html)?\/?$/);
  if (match) {
    return cleanPath(match[1]);
  }

  return cleanPath(pathname.replace(/\/$/, ""));
}

function buildApiCandidates(basePath) {
  const prefixes = [];
  let current = cleanPath(basePath);

  while (true) {
    if (!prefixes.includes(current)) {
      prefixes.push(current);
    }

    if (current === "") {
      break;
    }

    current = cleanPath(current.replace(/\/[^/]+$/, ""));
  }

  const candidates = [];
  for (const prefix of prefixes) {
    const apiPath = cleanPath(`${prefix}/api/v1`) || "/api/v1";
    const apiIndexPath = cleanPath(`${prefix}/index.php/api/v1`) || "/index.php/api/v1";

    if (!candidates.includes(apiPath)) {
      candidates.push(apiPath);
    }
    if (!candidates.includes(apiIndexPath)) {
      candidates.push(apiIndexPath);
    }
  }

  return candidates;
}

const APP_BASE_PATH = appBasePath();
const API_CANDIDATES = buildApiCandidates(APP_BASE_PATH);
let apiBase = API_CANDIDATES[0] || "/api/v1";
const SESSION_KEY = "estudamais_session";

const state = {
  session: null,
  decks: [],
  study: null,
  currentView: "dashboard",
  deferredPrompt: null,
};

const viewMeta = {
  dashboard: { title: "Dashboard", subtitle: "Visao geral do seu progresso" },
  auth: { title: "Autenticacao", subtitle: "Login e cadastro de conta" },
  profile: { title: "Perfil", subtitle: "Dados da conta e avatar" },
  decks: { title: "Decks", subtitle: "Criacao de conteudo e IA de cards" },
  study: { title: "Estudo", subtitle: "Sessoes, respostas e finalizacao" },
  marketplace: { title: "Marketplace", subtitle: "Decks pagos e compras" },
  challenges: { title: "Desafios", subtitle: "Inscricao e ranking de desafios" },
  tutor: { title: "Tutor", subtitle: "Orientacao inteligente por contexto" },
  analytics: { title: "Analytics", subtitle: "Indicadores avancados de aprendizado" },
};

const authRequiredViews = new Set([
  "dashboard",
  "profile",
  "decks",
  "study",
  "marketplace",
  "challenges",
  "tutor",
  "analytics",
]);

function byId(id) {
  return document.getElementById(id);
}

function safeJson(value) {
  try {
    return JSON.stringify(value, null, 2);
  } catch (_error) {
    return "{}";
  }
}

function setStatus(message, type = "ok") {
  const box = byId("status-box");
  if (!message) {
    box.classList.add("hidden");
    box.classList.remove("error");
    box.textContent = "";
    return;
  }

  box.classList.remove("hidden");
  box.classList.toggle("error", type === "error");
  box.textContent = message;
}

function loadSession() {
  const raw = localStorage.getItem(SESSION_KEY);
  if (!raw) {
    return null;
  }

  try {
    return JSON.parse(raw);
  } catch (_error) {
    return null;
  }
}

function saveSession(session) {
  state.session = session;
  localStorage.setItem(SESSION_KEY, JSON.stringify(session));
  renderUserChip();
}

function clearSession() {
  state.session = null;
  localStorage.removeItem(SESSION_KEY);
  renderUserChip();
}

function hasSession() {
  return Boolean(state.session?.access_token);
}

function renderUserChip() {
  const chip = byId("user-chip");
  if (!hasSession()) {
    chip.textContent = "Nao autenticado";
    applyAuthUiState();
    return;
  }

  const user = state.session.user || {};
  const name = user.name || user.email || "Usuario";
  chip.textContent = `${name} | XP ${user.xp ?? 0}`;
  applyAuthUiState();
}

function applyAuthUiState() {
  const authenticated = hasSession();

  for (const button of document.querySelectorAll('[data-view="auth"]')) {
    button.classList.toggle("hidden", authenticated);
  }

  const logoutButton = byId("logout-btn");
  if (logoutButton) {
    logoutButton.classList.toggle("hidden", !authenticated);
  }
}

function setView(view) {
  const target = viewMeta[view] ? view : "dashboard";
  const requiresAuth = authRequiredViews.has(target);
  if (requiresAuth && !hasSession()) {
    setStatus("Faça login para acessar esta area.", "error");
    state.currentView = "auth";
  } else {
    state.currentView = target;
  }

  for (const element of document.querySelectorAll(".view")) {
    element.classList.toggle("active", element.id === `view-${state.currentView}`);
  }
  for (const button of document.querySelectorAll("[data-view]")) {
    button.classList.toggle("active", button.dataset.view === state.currentView);
  }

  const meta = viewMeta[state.currentView];
  byId("view-title").textContent = meta.title;
  byId("view-subtitle").textContent = meta.subtitle;

  window.location.hash = `#${state.currentView}`;
  loadViewData(state.currentView);
}

async function refreshSession() {
  const refreshToken = state.session?.refresh_token;
  if (!refreshToken) {
    clearSession();
    throw new Error("Sessao expirada. Faca login novamente.");
  }

  const response = await fetch(`${apiBase}/auth/refresh`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ refresh_token: refreshToken }),
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    clearSession();
    throw new Error(payload.error || "Sessao expirada. Faca login novamente.");
  }

  const data = payload.data || {};
  saveSession({
    ...state.session,
    access_token: data.access_token,
    refresh_token: data.refresh_token,
    expires_at: data.expires_at,
    refresh_expires_at: data.refresh_expires_at,
  });
}

async function api(path, { method = "GET", body = null, auth = false, retry = true } = {}) {
  const headers = { "Content-Type": "application/json" };
  if (auth) {
    if (!hasSession()) {
      throw new Error("Voce precisa fazer login.");
    }
    const bearer = `Bearer ${state.session.access_token}`;
    headers.Authorization = bearer;
    headers["X-Authorization"] = bearer;
  }

  const response = await fetch(`${apiBase}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : null,
  });
  const payload = await response.json().catch(() => ({}));

  if (response.status === 401 && auth && retry) {
    await refreshSession();
    return api(path, { method, body, auth, retry: false });
  }
  if (!response.ok) {
    throw new Error(payload.error || `Erro HTTP ${response.status}`);
  }

  return payload.data ?? payload;
}

async function resolveApiBase() {
  for (const candidate of API_CANDIDATES) {
    try {
      const response = await fetch(`${candidate}/health`, { method: "GET" });
      if (!response.ok) {
        continue;
      }

      const payload = await response.json().catch(() => null);
      const isExpectedHealth =
        payload &&
        payload.data &&
        payload.data.status === "ok" &&
        payload.data.service === "estuda-plus-api";

      if (isExpectedHealth) {
        apiBase = candidate;
        return;
      }
    } catch (_error) {
      // tenta proximo candidato
    }
  }

  throw new Error("Nao foi possivel localizar a API. Verifique se o servidor PHP/Apache esta ativo.");
}

function createListItem(title, subtitle, actions = []) {
  const wrapper = document.createElement("article");
  wrapper.className = "list-item";

  const titleNode = document.createElement("strong");
  titleNode.textContent = title;
  wrapper.appendChild(titleNode);

  const subtitleNode = document.createElement("small");
  subtitleNode.textContent = subtitle;
  wrapper.appendChild(subtitleNode);

  if (actions.length > 0) {
    const actionRow = document.createElement("div");
    actionRow.className = "actions";
    for (const action of actions) {
      actionRow.appendChild(action);
    }
    wrapper.appendChild(actionRow);
  }

  return wrapper;
}

function fillDeckSelects() {
  const selectIds = [
    "flashcard-deck-select",
    "study-deck-select",
    "ai-deck-select",
    "tutor-deck-select",
  ];

  for (const id of selectIds) {
    const select = byId(id);
    if (!select) {
      continue;
    }
    const keepFirst = id === "tutor-deck-select";
    const firstOption = keepFirst ? select.querySelector("option") : null;
    select.innerHTML = "";
    if (firstOption) {
      select.appendChild(firstOption);
    }
    for (const deck of state.decks) {
      const option = document.createElement("option");
      option.value = deck.id;
      option.textContent = `${deck.title} (${deck.visibility})`;
      select.appendChild(option);
    }
  }
}

async function loadProfile() {
  const profile = await api("/users/profile", { auth: true });
  byId("profile-json").textContent = safeJson(profile);
  if (state.session?.user) {
    state.session.user = {
      ...state.session.user,
      ...profile,
    };
    saveSession(state.session);
  }
}

async function loadDecks() {
  const response = await api("/decks");
  state.decks = response.items || [];
  fillDeckSelects();

  const list = byId("decks-list");
  list.innerHTML = "";
  for (const deck of state.decks) {
    const item = createListItem(
      deck.title,
      `ID ${deck.id} | ${deck.visibility} | R$ ${Number(deck.price || 0).toFixed(2)}`
    );
    list.appendChild(item);
  }
}

function renderCurrentStudyCard() {
  const box = byId("study-card-box");
  const optionsRow = byId("study-options");
  const stateBox = byId("study-session-state");
  optionsRow.innerHTML = "";

  if (!state.study) {
    box.textContent = "Nenhuma sessao em andamento.";
    stateBox.textContent = "{}";
    return;
  }

  const card = state.study.cards[state.study.index];
  if (!card) {
    box.textContent = "Todos os cards enviados. Clique em finalizar sessao.";
    stateBox.textContent = safeJson({
      session_id: state.study.session_id,
      progress: `${state.study.index}/${state.study.cards.length}`,
    });
    return;
  }

  box.innerHTML = `<strong>Card ${state.study.index + 1}/${state.study.cards.length}</strong><p>${card.question}</p><small>Tipo: ${card.type}</small>`;
  stateBox.textContent = safeJson({
    session_id: state.study.session_id,
    card_id: card.id,
    deck_id: state.study.deck_id,
    progress: `${state.study.index + 1}/${state.study.cards.length}`,
  });

  if (Array.isArray(card.options) && card.options.length > 0) {
    for (const option of card.options) {
      const button = document.createElement("button");
      button.type = "button";
      button.textContent = option;
      button.addEventListener("click", () => {
        byId("study-answer-input").value = option;
      });
      optionsRow.appendChild(button);
    }
  }
}

async function loadMarketplace() {
  const marketplace = await api("/marketplace/decks");
  const list = byId("marketplace-list");
  list.innerHTML = "";

  for (const item of marketplace.items || []) {
    const buy = document.createElement("button");
    buy.type = "button";
    buy.textContent = "Comprar";
    buy.addEventListener("click", async () => {
      try {
        await api("/marketplace/buy", { method: "POST", auth: true, body: { deck_id: item.id } });
        setStatus("Compra realizada.");
        await loadPurchases();
      } catch (error) {
        setStatus(error.message, "error");
      }
    });

    list.appendChild(
      createListItem(
        item.title,
        `ID ${item.id} | R$ ${Number(item.price || 0).toFixed(2)}`,
        [buy]
      )
    );
  }
}

async function loadPurchases() {
  const purchases = await api("/marketplace/purchases", { auth: true });
  const list = byId("purchases-list");
  list.innerHTML = "";
  for (const item of purchases.items || []) {
    list.appendChild(
      createListItem(
        `Compra ${item.id}`,
        `Deck ${item.deck_id} | R$ ${Number(item.price || 0).toFixed(2)} | ${item.status}`
      )
    );
  }
}

async function loadChallenges() {
  const data = await api("/challenges", { auth: true });
  const list = byId("challenges-list");
  list.innerHTML = "";

  for (const item of data.items || []) {
    const detail = document.createElement("button");
    detail.type = "button";
    detail.textContent = "Detalhes";
    detail.addEventListener("click", async () => {
      try {
        const response = await api(`/challenges/${item.id}`, { auth: true });
        byId("challenge-details").textContent = safeJson(response);
      } catch (error) {
        setStatus(error.message, "error");
      }
    });

    const actions = [detail];
    if (!item.joined && item.status === "active") {
      const join = document.createElement("button");
      join.type = "button";
      join.textContent = "Entrar";
      join.addEventListener("click", async () => {
        try {
          await api("/challenges/join", {
            method: "POST",
            auth: true,
            body: { challenge_id: item.id },
          });
          setStatus("Inscricao concluida.");
          await loadChallenges();
        } catch (error) {
          setStatus(error.message, "error");
        }
      });
      actions.push(join);
    }

    list.appendChild(
      createListItem(
        `${item.title} (${item.status})`,
        `Periodo ${item.start_date} ate ${item.end_date} | reward_xp ${item.reward_xp}`,
        actions
      )
    );
  }
}

function renderAnalytics(analytics) {
  const metrics = byId("analytics-metrics");
  const bars = byId("analytics-bars");
  metrics.innerHTML = "";
  bars.innerHTML = "";

  const overview = analytics.overview || {};
  const metricPairs = [
    ["Sessoes", overview.sessions_finished ?? 0],
    ["Acuracia", `${overview.avg_accuracy ?? 0}%`],
    ["XP total", overview.xp_total ?? 0],
    ["XP 7 dias", overview.xp_last_7_days ?? 0],
    ["Reviews vencidas", overview.reviews_due ?? 0],
    ["Ranking", analytics.student?.ranking_position ?? "-"],
  ];

  for (const [label, value] of metricPairs) {
    const card = document.createElement("article");
    card.className = "card metric";
    card.innerHTML = `<p>${label}</p><strong>${value}</strong>`;
    metrics.appendChild(card);
  }

  for (const item of analytics.daily_last_7_days || []) {
    const accuracy = Number(item.accuracy || 0);
    const row = document.createElement("div");
    row.className = "bar-row";
    row.innerHTML = `
      <small>${item.date} | sessoes ${item.sessions} | acuracia ${accuracy.toFixed(1)}%</small>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.max(0, Math.min(100, accuracy))}%"></div></div>
    `;
    bars.appendChild(row);
  }

  byId("dash-sessions").textContent = String(overview.sessions_finished ?? 0);
  byId("dash-xp").textContent = String(overview.xp_total ?? 0);
  byId("dash-accuracy").textContent = `${overview.avg_accuracy ?? 0}%`;
}

async function loadAnalytics() {
  const analytics = await api("/analytics/overview", { auth: true });
  renderAnalytics(analytics);
}

async function loadViewData(view) {
  try {
    if (view === "auth") {
      return;
    }
    if (!hasSession()) {
      return;
    }

    if (view === "profile") {
      await loadProfile();
      return;
    }
    if (view === "decks") {
      await loadDecks();
      return;
    }
    if (view === "study") {
      await loadDecks();
      renderCurrentStudyCard();
      return;
    }
    if (view === "marketplace") {
      await loadMarketplace();
      await loadPurchases();
      return;
    }
    if (view === "challenges") {
      await loadChallenges();
      return;
    }
    if (view === "tutor") {
      await loadDecks();
      return;
    }
    if (view === "analytics" || view === "dashboard") {
      await loadAnalytics();
      return;
    }
  } catch (error) {
    setStatus(error.message, "error");
  }
}

async function logout() {
  try {
    if (hasSession()) {
      await api("/auth/logout", { method: "POST", auth: true });
    }
  } catch (_error) {
    // logout local mesmo se token ja invalido
  }
  clearSession();
  state.decks = [];
  state.study = null;
  setStatus("Sessao encerrada.");
  setView("auth");
}

function bindNavigation() {
  for (const button of document.querySelectorAll("[data-view]")) {
    button.addEventListener("click", () => {
      setStatus("");
      setView(button.dataset.view);
    });
  }

  for (const button of document.querySelectorAll("[data-go]")) {
    button.addEventListener("click", () => {
      setView(button.dataset.go);
    });
  }
}

function bindForms() {
  byId("login-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    try {
      const data = await api("/auth/login", {
        method: "POST",
        body: {
          email: formData.get("email"),
          password: formData.get("password"),
        },
      });
      saveSession(data);
      setStatus("Login realizado com sucesso.");
      await loadDecks();
      setView("dashboard");
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("register-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    try {
      await api("/auth/register", {
        method: "POST",
        body: {
          name: formData.get("name"),
          email: formData.get("email"),
          password: formData.get("password"),
        },
      });
      setStatus("Cadastro concluido. Agora faca login.");
      event.currentTarget.reset();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("avatar-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    try {
      await api("/users/avatar", {
        method: "PUT",
        auth: true,
        body: { avatar_id: formData.get("avatar_id") },
      });
      setStatus("Avatar atualizado.");
      await loadProfile();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("deck-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    try {
      const visibility = formData.get("visibility");
      const priceValue = Number(formData.get("price") || 0);
      await api("/decks", {
        method: "POST",
        auth: true,
        body: {
          title: formData.get("title"),
          description: formData.get("description"),
          visibility,
          price: Number.isFinite(priceValue) ? priceValue : 0,
        },
      });
      setStatus("Deck criado.");
      event.currentTarget.reset();
      await loadDecks();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("flashcard-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const optionsRaw = String(formData.get("options") || "");
    const options = optionsRaw
      .split(";")
      .map((item) => item.trim())
      .filter(Boolean);

    try {
      await api("/flashcards", {
        method: "POST",
        auth: true,
        body: {
          deck_id: formData.get("deck_id"),
          type: formData.get("type"),
          question: formData.get("question"),
          answer: formData.get("answer"),
          options,
        },
      });
      setStatus("Flashcard criado.");
      event.currentTarget.reset();
      await loadDecks();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("ai-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const resultBox = byId("ai-result");

    try {
      const data = await api("/ai/cards/generate", {
        method: "POST",
        auth: true,
        body: {
          deck_id: formData.get("deck_id"),
          topic: formData.get("topic"),
          count: Number(formData.get("count") || 5),
          source_text: formData.get("source_text"),
          persist: formData.get("persist") === "on",
        },
      });
      resultBox.textContent = safeJson(data);
      setStatus(`IA gerou ${data.generated_count} cards.`);
    } catch (error) {
      resultBox.textContent = "";
      setStatus(error.message, "error");
    }
  });

  byId("study-start-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    try {
      const data = await api("/study/start", {
        method: "POST",
        auth: true,
        body: { deck_id: formData.get("deck_id") },
      });
      state.study = {
        session_id: data.session_id,
        deck_id: data.deck_id,
        cards: data.cards || [],
        index: 0,
      };
      setStatus("Sessao iniciada.");
      renderCurrentStudyCard();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("study-answer-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!state.study) {
      setStatus("Inicie uma sessao antes de responder.", "error");
      return;
    }
    const card = state.study.cards[state.study.index];
    if (!card) {
      setStatus("Todos os cards ja foram enviados. Finalize a sessao.", "error");
      return;
    }

    const formData = new FormData(event.currentTarget);
    const answer = String(formData.get("user_answer") || "").trim();
    if (!answer) {
      setStatus("Informe uma resposta.", "error");
      return;
    }

    try {
      const data = await api("/study/answer", {
        method: "POST",
        auth: true,
        body: {
          session_id: state.study.session_id,
          flashcard_id: card.id,
          user_answer: answer,
        },
      });
      state.study.index += 1;
      byId("study-answer-input").value = "";
      byId("study-result").textContent = safeJson(data);
      renderCurrentStudyCard();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("study-finish-btn").addEventListener("click", async () => {
    if (!state.study) {
      setStatus("Nenhuma sessao ativa.", "error");
      return;
    }

    try {
      const data = await api("/study/finish", {
        method: "POST",
        auth: true,
        body: { session_id: state.study.session_id },
      });
      state.study = null;
      byId("study-result").textContent = safeJson(data);
      renderCurrentStudyCard();
      setStatus("Sessao finalizada com sucesso.");
      await loadAnalytics();
      await loadProfile();
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("refresh-purchases-btn").addEventListener("click", async () => {
    try {
      await loadPurchases();
      setStatus("Compras atualizadas.");
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("tutor-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    try {
      const data = await api("/tutor/assist", {
        method: "POST",
        auth: true,
        body: {
          deck_id: formData.get("deck_id"),
          question: formData.get("question"),
        },
      });
      byId("tutor-result").textContent = safeJson(data);
      setStatus("Tutor respondeu com base no seu historico.");
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("analytics-refresh-btn").addEventListener("click", async () => {
    try {
      await loadAnalytics();
      setStatus("Analytics atualizados.");
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  byId("logout-btn").addEventListener("click", logout);
}

function setupPwa() {
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register(`${APP_BASE_PATH}/app/sw.js`, {
      scope: `${APP_BASE_PATH}/app/`,
    }).catch(() => {});
  }

  window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    state.deferredPrompt = event;
    byId("install-btn").classList.remove("hidden");
  });

  byId("install-btn").addEventListener("click", async () => {
    if (!state.deferredPrompt) {
      return;
    }
    state.deferredPrompt.prompt();
    await state.deferredPrompt.userChoice;
    state.deferredPrompt = null;
    byId("install-btn").classList.add("hidden");
  });

  window.addEventListener("appinstalled", () => {
    byId("install-btn").classList.add("hidden");
    setStatus("App instalado com sucesso.");
  });
}

async function bootstrap() {
  state.session = loadSession();
  renderUserChip();
  bindNavigation();
  bindForms();
  setupPwa();

  try {
    await resolveApiBase();
  } catch (error) {
    setStatus(error.message, "error");
    return;
  }

  const initialView = window.location.hash.replace("#", "") || (hasSession() ? "dashboard" : "auth");
  setView(initialView);

  if (hasSession()) {
    try {
      await loadProfile();
      await loadDecks();
      await loadAnalytics();
    } catch (error) {
      setStatus(error.message, "error");
    }
  } else {
    setStatus("Use a credencial de seed: tester@estuda.local / 123456");
  }
}

bootstrap();
