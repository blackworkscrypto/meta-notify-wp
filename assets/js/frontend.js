import LazyScriptsLoader from "./components/lazy-script-loader.js";

/**
 * Nothing here. Just do lazy-loading for heavy scripts
 */
window.addEventListener("DOMContentLoaded", () => {
  const lazyScriptsLoader = new LazyScriptsLoader(
    [
      "load",
      "keydown",
      "mousemove",
      "touchmove",
      "touchstart",
      "touchend",
      "wheel",
    ],
    [
      {
        id: "ethers",
        uri: metanotify.pluginUri + "assets/js/vendor/ethers.min.js",
      },
      {
        id: "solana",
        uri: metanotify.pluginUri + "assets/js/vendor/solana.min.js",
      },
      {
        id: "wallet_connect",
        uri: metanotify.pluginUri + "assets/js/vendor/walletconnect.js",
      },
    ]
  );

  lazyScriptsLoader.init(lazyScriptsLoader);
});
