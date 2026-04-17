jQuery(document).ready(function (jQuery) {
  /**
   * Hide empty addon sections and mark them with a CSS class on page load.
   */

   jQuery(".gsheetconnector-addons-list").each(function () {
    if (jQuery(this).html().trim().length === 0) {
      jQuery(this).addClass("blank_div");
      jQuery(this).prev("div").hide();
    }
  });

  /**
   * Handle plugin install button click via AJAX.
   *
   * - Shows loading spinner
   * - Sends plugin slug and download URL to server
   * - On success, hides install button and shows activate button
   * - On error or failure, resets button state
   */

   jQuery(".gselef-install-plugin-btn").on("click", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var downloadUrl = button.data("download");
    var loaderSpan = button
    .closest(".button-bar")
    .find(".loading-sign-install");

    loaderSpan.addClass("loading");
    button.prop("disabled", true);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "gselef_install_plugin",
        plugin_slug: pluginSlug,
        download_url: downloadUrl,
        security: jQuery("#gselef-ajax-nonce").val(),
      },

      success: function (response) {
        loaderSpan.removeClass("loading");

        if (response.success) {
          // ✅ Install success
          button.hide();

          button
          .closest(".button-bar")
          .find(".gselef-activate-plugin-btn")
          .show();
        } else {
          // ❌ Permission or other error → open popup
          jQuery(".popup-actions-active-msg-free").text(
            response.data.message ||
            "You do not have permission to install this plugin.",
            );

          jQuery("#gselef-confirm-active-popup-free").removeClass("d-none");

          button.prop("disabled", false);
        }
      },

      error: function () {
        loaderSpan.removeClass("loading");

        jQuery(".popup-actions-active-msg-free").text(
          "Something went wrong. Please try again.",
          );

        jQuery("#gselef-confirm-active-popup-free").removeClass("d-none");

        button.prop("disabled", false);
      },
    });
  });

  /**
   * Handle plugin activation button click via AJAX.
   *
   * - Shows loading spinner
   * - Sends plugin slug to server for activation
   * - On success, updates button to "Activated" and reloads page
   * - On error or failure, resets button and removes loading state
   */

   jQuery(document).on("click", ".gselef-activate-plugin-btn", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var loaderSpan = button.siblings(".loading-sign-active");

    loaderSpan.addClass("loading");
    button.prop("disabled", true);

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "gselef_activate_plugin",
        plugin_slug: pluginSlug,
        security: jQuery("#gselef-ajax-nonce").val(),
      },

      success: function (response) {
        loaderSpan.removeClass("loading");

        if (response.success) {
          // ✅ Success → reload only
          location.reload();
        } else {
          // ❌ Permission denied → open popup
          jQuery(".popup-actions-active-msg-free").text(
            response.data.message ||
            "You do not have permission to activate this plugin.",
            );

          jQuery("#gselef-confirm-active-popup-free").removeClass("d-none");

          button.prop("disabled", false);
        }
      },

      error: function () {
        loaderSpan.removeClass("loading");

        jQuery(".popup-actions-active-msg-free").text(
          "Something went wrong. Please try again.",
          );

        jQuery("#gselef-confirm-active-popup-free").removeClass("d-none");

        button.prop("disabled", false);
      },
    });
  });

  /**
   * Handle plugin deactivation button click via AJAX.
   *
   * - Sends plugin slug to server for deactivation
   * - On success, shows alert and reloads the page
   * - On error, shows AJAX error alert
   */

   let selectedPluginSlug = "";
  // Open popup on deactivate click
  jQuery(".gselef-deactivate-plugin").on("click", function (e) {
    selectedPluginSlug = jQuery(this).data("plugin");
    jQuery("#gselef-confirm-dective-popup-free").removeClass("d-none");
  });
  // Cancel button
  jQuery("#gselef-dective-popup-cancel-free").on("click", function () {
    jQuery("#gselef-confirm-dective-popup-free").addClass("d-none");
    selectedPluginSlug = "";
  });
  // Confirm deactivate
  jQuery("#gselef-deactive-popup-confirm-free").on("click", function () {
    if (!selectedPluginSlug) return;
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "gselef_deactivate_plugin",
        plugin_slug: selectedPluginSlug,
        security: jQuery("#gselef-ajax-nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          jQuery("#gselef-confirm-dective-popup-free").addClass("d-none");
          location.reload();
        }
      },
    });
  });
  // filter
  document.querySelectorAll(".market-tab").forEach((tab) => {
    tab.addEventListener("click", function () {
      let filter = this.dataset.filter;

      document
      .querySelectorAll(".market-tab")
      .forEach((t) => t.classList.remove("active"));

      this.classList.add("active");

      document.querySelectorAll(".gsc-market-item").forEach((card) => {
        if (filter === "all") {
          card.style.display = "block";
        } else {
          card.style.display = card.classList.contains(filter)
          ? "block"
          : "none";
        }
      });
    });
  });
});
