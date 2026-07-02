(function () {
  if (!("serviceWorker" in navigator)) return;

  // The ROOT path differs between localhost and InfinityFree, so it's
  // passed in via a data attribute on this script's own tag rather than
  // hardcoded here — see header.php.
  var script = document.querySelector("script[data-root]");
  var root = script ? script.dataset.root : "";

  window.addEventListener("load", function () {
    navigator.serviceWorker.register(root + "/sw.js").catch(function (err) {
      console.warn("Service worker registration failed:", err);
    });
  });
})();
