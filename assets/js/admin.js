window.addEventListener("DOMContentLoaded", () => {
  const noticeEl = document.getElementById("messager");
  const defaultMsg = noticeEl.textContent;
  const activateBtn = document.getElementById("meta-plugin-activate-btn");

  if (activateBtn) {
    activateBtn.addEventListener("click", (e) => {
      e.preventDefault();

      e.currentTarget.textContent = "ACTIVATING...";
      e.currentTarget.setAttribute("disabled", true);

      const tosBox = document.getElementById("accept_tos");

      if (!tosBox.checked) {
        noticeEl.classList.add("err");
        noticeEl.textContent = metanotify.tosRequired;
        setTimeout(() => {
          noticeEl.textContent = defaultMsg;
          noticeEl.classList.remove("err");
          activateBtn.textContent = "ACTIVATE";
          activateBtn.removeAttribute("disabled");
        }, 3000);
        return;
      }

      const emailInput = document.querySelector("#registration_email");
      console.log("noncee");
      console.log(metanotify.nonce);
      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams({
          nonce: metanotify.nonce,
          email: emailInput.value,
          plugin: e.currentTarget.dataset.plugin,
          action: "metanotify_activate_site",
        }),
      })
        .then((res) => {
          return res.json();
        })
        .then((result) => {
          if (result.success) {
            noticeEl.classList.add("ok");
            noticeEl.textContent = result.message;
            activateBtn.textContent = "ACTIVATED";
            setTimeout(
              () => (window.location.href = metanotify.adminURL),
              3000
            );
          } else {
            noticeEl.classList.add("err");
            noticeEl.textContent = result.message;
            activateBtn.textContent = "ACTIVATE";
            activateBtn.removeAttribute("disabled");
          }
        })
        .catch((err) => {
          console.log(err);
        });
    });
  }
});
