const CACHE_NAME = "estudamais-shell-v3";

function appShellUrls() {
  const appRoot = new URL("./", self.location.href);
  return [
    appRoot.href,
    new URL("index.html", appRoot).href,
    new URL("styles.css", appRoot).href,
    new URL("app.js", appRoot).href,
    new URL("manifest.webmanifest", appRoot).href,
    new URL("icons/icon-192.svg", appRoot).href,
    new URL("icons/icon-512.svg", appRoot).href,
  ];
}

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(appShellUrls()))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  const url = new URL(request.url);

  if (url.pathname.includes("/api/")) {
    return;
  }

  const appRootPath = new URL("./", self.location.href).pathname;
  if (!url.pathname.startsWith(appRootPath)) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then((networkResponse) => {
        if (request.method === "GET") {
          const clone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
        }
        return networkResponse;
      })
      .catch(() =>
        caches.match(request).then((cached) => cached || caches.match(new URL("index.html", new URL("./", self.location.href)).href))
      )
  );
});
