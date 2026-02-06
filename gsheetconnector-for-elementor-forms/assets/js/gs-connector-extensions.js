jQuery(document).ready(function (jQuery) {
  jQuery('.gsheetconnector-addons-list').each(function () {
    if (jQuery(this).html().trim().length === 0) {
      jQuery(this).addClass('blank_div');
      jQuery(this).prev('h2').hide();
    }
  });
  jQuery(".ele-install-plugin-btn-pro").on("click", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var downloadUrl = button.data("download");
    var loaderSpan = button
      .closest(".button-bar")
      .find(".loading-sign-install");

    loaderSpan.addClass("loading");

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "gsele_install_plugin",
        plugin_slug: pluginSlug,
        download_url: downloadUrl,
        security: jQuery("#gsele_ajax_nonce").val(),
      },
      success: function (response) {
        loaderSpan.removeClass("loading");
        if (response.success) {
          button.hide();
          button.closest(".button-bar").find(".ele-activate-plugin-btn-pro-pro").show();
        } else {
          button.html("Install").prop("disabled", false);
        }
      },
      error: function () {
        loaderSpan.removeClass("loading");
        button.html("Install").prop("disabled", false);
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

  jQuery(document).on("click", ".ele-activate-plugin-btn-pro", function () {
    var button = jQuery(this);
    var pluginSlug = button.data("plugin");
    var loaderSpan = button.siblings(".loading-sign-active");
    loaderSpan.addClass("loading");
    // button.prop("disabled", true);
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "gsele_activate_plugin",
        plugin_slug: pluginSlug,
        security: jQuery("#gsele_ajax_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          button.text("Activated"); // Show "Activated"
          button.prop("disabled", true);
          location.reload();
        } else {
          loaderSpan.removeClass("loading"); // Clear loader
          button.prop("disabled", false);
        }
      },
      error: function () {
        loaderSpan.removeClass("loading").text(""); // Clear loader
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

  jQuery(".ele-deactivate-plugin-pro").on("click", function () {
    var pluginSlug = jQuery(this).data("plugin");
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json", // Ensure JSON response
      data: {
        action: "gsele_deactivate_plugin",
        plugin_slug: pluginSlug,
        security: jQuery("#gsele_ajax_nonce").val(),
      },
      success: function (response) {
        if (response.success) {
          alert(response.data); // Display success message
          location.reload();
        }
      },
      error: function (xhr, status, error) {
        alert("AJAX error: " + error);
      },
    });
  });

});
