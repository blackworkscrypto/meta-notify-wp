window.addEventListener("DOMContentLoaded", () => {
  const visitorAddBtn = document.getElementById("meta-notify-visitor-add-btn");

  if (visitorAddBtn) {
    visitorAddBtn.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("clicked addd visitor");

      const walletAddress = document.getElementById(
        "meta-notify-wallet-address"
      ).value;
      console.log(walletAddress);
      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams({
          walletAddress: walletAddress,
          plugin: e.currentTarget.dataset.plugin,
          action: "metanotify_add_visitor",
        }),
      })
        .then((res) => {
          return res.json();
        })
        .then((result) => {
          if (result.siteStatus) {
            window.location.reload();
          }
          console.log(result);
        })
        .catch((err) => {
          console.log(err);
        });
    });
  }
});
