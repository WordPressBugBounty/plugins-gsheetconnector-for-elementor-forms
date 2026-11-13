document.addEventListener("DOMContentLoaded", function () {
  if (typeof gselefFreeGlobal === "undefined") return;

  document
    .querySelectorAll(".gselef-global-notice")
    .forEach(function (wrap) {
      wrap.querySelectorAll("[data-do]").forEach(function (btn) {
        btn.addEventListener("click", function () {
          var data = new FormData();
          data.append("action", "gselef_free_global_notice_action");
          data.append("nonce", gselefFreeGlobal.nonce);
          data.append("do", btn.getAttribute("data-do"));
          data.append("notice", btn.getAttribute("data-notice") || "auth");

          fetch(gselefFreeGlobal.ajax_url, {
            method: "POST",
            body: data,
            credentials: "same-origin",
            keepalive: true, // lets the request finish when the review link opens a new tab
          });

          wrap.style.display = "none";
        });
      });
    });
});
