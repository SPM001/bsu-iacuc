// Bump this version whenever CSS/JS/images change so old caches get
// cleared out and users pick up fresh files instead of stale ones.
const CACHE_VERSION = "bsu-iacuc-v3";

// Small, explicit "app shell" — just what's needed to render the page
// chrome (header/footer/nav) and a usable offline fallback. Deliberately
// NOT trying to precache every page or every asset.
const PRECACHE_URLS = [
  "offline.html",
  "assets/css/header.css",
  "assets/css/body.css",
  "assets/css/modals.css",
  "assets/css/footer.css",
  "assets/css/navigation.css",
  "assets/css/404.css",
  "assets/css/action-queue.css",
  "assets/js/header.js",
  "assets/js/modals.js",
  "assets/js/action-queue.js",
  "assets/images/bsu.webp",
  "assets/images/ccard.webp",
  "assets/images/favicon.ico",
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) => cache.addAll(PRECACHE_URLS)),
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== CACHE_VERSION)
            .map((key) => caches.delete(key)),
        ),
      ),
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only ever handle our own GET requests. POSTs, and anything
  // cross-origin (Google Fonts, etc.), go straight to the network
  // untouched — we don't want to get in the way of those.
  if (request.method !== "GET" || url.origin !== self.location.origin) {
    return;
  }

  // PDF files: serve from cache when offline, refresh cache when online.
  // We do cache these because reviewers need to read protocols offline.
  if (url.pathname.includes("/apply/file/")) {
    event.respondWith(networkFirst(request));
    return;
  }

  if (request.mode === "navigate") {
    event.respondWith(navigationHandler(request));
  } else if (url.pathname.includes("/assets/")) {
    event.respondWith(staticAssetHandler(request));
  } else {
    event.respondWith(networkFirst(request));
  }
});

// Full page loads: try the network, cache whatever comes back, and if
// the network fails, serve the last cached copy of that exact page —
// or the offline fallback page if we've never seen it before.
async function navigationHandler(request) {
  const cache = await caches.open(CACHE_VERSION);
  try {
    const response = await fetch(request);
    cache.put(request, response.clone());
    return response;
  } catch (err) {
    return (await cache.match(request)) || cache.match("offline.html");
  }
}

// CSS/JS/images: serve instantly from cache so the page renders right
// away, then quietly refresh the cache in the background for next time.
async function staticAssetHandler(request) {
  const cache = await caches.open(CACHE_VERSION);
  const cached = await cache.match(request);

  const refresh = fetch(request)
    .then((response) => {
      cache.put(request, response.clone());
      return response;
    })
    .catch(() => null);

  return cached || (await refresh) || Response.error();
}

// Everything else (e.g. fetch/AJAX calls for live data): always prefer
// fresh data from the network. Only fall back to a cached copy if one
// exists — otherwise let the failure surface so the page's own JS can
// handle it.
async function networkFirst(request) {
  const cache = await caches.open(CACHE_VERSION);
  try {
    const response = await fetch(request);
    cache.put(request, response.clone());
    return response;
  } catch (err) {
    const cached = await cache.match(request);
    if (cached) return cached;
    throw err;
  }
}
