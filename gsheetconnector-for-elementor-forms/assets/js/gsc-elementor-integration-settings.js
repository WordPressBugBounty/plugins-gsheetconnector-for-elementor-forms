jQuery(document).ready(function ($) {
  // Verify the API code
  $(document).on("click", "#save-ele-code", function () {
    $(this).parent().children(".loading-sign").addClass("loading");
    var data = {
      action: "verify_gscelementor_integation",
      code: $("#ele-code").val(),
      security: $("#gs-ajax-nonce-ele").val(),
    };
    $.post(ajaxurl, data, function (response) {
      $(".loading-sign").removeClass("loading");

      if (response == -1) return;

      $("#gs-validation-message").empty();

      if (!response.success) {
        $(
          "<div class='gsc-msg gsc-error fw-400 text-dark text-center pt-10 pb-10 manual-margin'>Access code Can't be blank</div>",
          ).appendTo("#gs-validation-message");
      } else {
        $(
          "<div class='gsc-msg gsc-success fw-400 text-dark text-center pt-10 pb-10 manual-margin'>Your Google Access Code is Authorized and Saved</div>",
          ).appendTo("#gs-validation-message");
        setTimeout(function () {
          window.location.href = $("#redirect_auth_eleforms").val();
        }, 1000);
      }
    });
  });

  // Deactivate the API code
  // Open popup
  $(document).on("click", "#deactivate-log-ele", function (e) {
    e.preventDefault();
    $("#gselef-confirm-deactive-popup-free").removeClass("d-none");
  });

  // Cancel button
  $(document).on("click", "#gselef-deactive-popup-free-cancel", function () {
    $("#gselef-confirm-deactive-popup-free").addClass("d-none");
  });

  // Confirm deactivate
  $(document).on("click", "#gselef-deactive-popup-free-confirm", function () {
    $(".loading-sign-deactive").addClass("loading");

    var data = {
      action: "deactivate_gscelementor_integation",
      security: $("#gs-ajax-nonce-ele").val(),
    };

    $.post(ajaxurl, data, function (response) {
      $(".loading-sign-deactive").removeClass("loading");
      $("#deactivate-message").empty();

      if (response == -1 || !response.success) {
        alert("Error while deactivation");
      } else {
        $(
          "<div class='gsc-msg gsc-success fw-400 text-dark text-center pt-10 pb-10 manual-margin'>Your account is removed. Reauthenticate again to integrate Elementor Form with Google Sheet.</div>",
          ).appendTo("#deactivate-message");

        $("#gselef-confirm-deactive-popup-free").addClass("d-none");

        setTimeout(function () {
          location.reload();
        }, 1000);
      }
    });
  });

  // Sync Google account
  $(document).on("click", "#ele-sync", function () {
    $(this).parent().children(".loading-sign").addClass("loading");

    var data = {
      action: "sync_google_account_gscelementor", // unified
      isinit: $(this).data("init"),
      security: $("#gs-ajax-nonce-ele").val(),
    };

    $.post(ajaxurl, data, function (response) {
      $(".loading-sign").removeClass("loading");
      if (response == -1 || !response.success) return;

      $("#gs-validation-message").empty();

      if (response.data.success === "yes") {
        $(
          "<span class='gs-valid-message'>Fetched all sheet details.</span>",
          ).appendTo("#gs-validation-message");
        setTimeout(function () {
          location.reload();
        }, 1000);
      }
    });
  });

  // Get tab ID on tab name change
  $(document).on("change", "#gs-sheet-tab-name", function () {
    var tabname = $(this).val();
    var sheetname = $("#gs-sheet-name").val();
    $(this).parent().children(".loading-sign").addClass("loading");
    var data = {
      action: "get_sheet_id",
      tabname: tabname,
      sheetname: sheetname,
      security: $("#gs-ajax-nonce-ele").val(),
    };
    $.post(ajaxurl, data, function (response) {
      $(".loading-sign").removeClass("loading");
      if (response == -1) return;
      if (response.success) {
        $("#sheet-url").html(html_decode(response.data));
      }
    });
  });

  // Toggle manual sheet name input
  $(document).on("click", "#manual-name", function () {
    $(".loading-sign").addClass("loading");
    if ($(this).is(":checked")) {
      $(".sheet-details").addClass("hide");
      $(".manual-fields").removeClass("hide");
    } else {
      $(".sheet-details").removeClass("hide");
      $(".manual-fields").addClass("hide");
    }
  });

  // Toggle misc options (sorting/color)
  $("#enable-sorting-option, #enable-colors-option").change(function () {
    const inner = $(this)
    .parents(".misc-options-row")
    .find(".misc-options-inner");
    $(this).is(":checked") ? inner.show() : inner.hide();
  });

  // WP color picker
  if ($(".inline-colors input").length > 0) {
    $(".inline-colors input").wpColorPicker();
  }

  // Deactivate Service Auth
  $(document).on("click", "#ele-deactivate-auth", function (e) {
    e.preventDefault();
    $(".loading-sign").addClass("loading");
    var data = {
      action: "deactivate_auth_gscelementor",
      security: $("#gs-ajax-nonce-ele").val(),
    };
    $.post(ajaxurl, data, function (response) {
      $(".loading-sign").removeClass("loading");
      $("#ele-validation-message").empty();
      if (!response.success) {
        $(
          "<span class='error-message'>Access code can't be blank.</span>",
          ).appendTo("#ele-validation-message");
      } else {
        $(
          "<span class='ele-valid-message'>Your account is removed. Reauthenticate again to integrate Elementor Form with Google Sheet.</span>",
          ).appendTo("#ele-validation-message");
        setTimeout(function () {
          location.reload();
        }, 1000);
      }
    });
  });

  // Reset client credentials form
  $(document).on("click", "#save-ele-reset", function () {
    $("#ele-client-id, #ele-secret-id, #ele-client-token")
    .val("")
    .removeAttr("disabled");
    $("#save-ele-manual").removeAttr("disabled");
  });

  // Save Elementor feed
  $(document).on("click", ".elementor-gs-sub-btn", function () {
    var feed_name = $(".feedName").val().trim();
    var elementorForms = $(".elementorForms").val();
    var valid = true;

    // Feed name validation
    if (!feed_name) {
      $("#feed_name").siblings(".input-msg").removeClass("d-none");
      valid = false;
    }

    // Form select validation
    if (!elementorForms) {
      $("#elementor_form_select")
      .closest(".auto-select")
      .siblings(".input-msg")
      .removeClass("d-none");
      valid = false;
    }

    if (!valid) {
      return;
    }

    $(".fld-fetch-load").addClass("loading");

    var data = {
      action: "save_gscelementor_feed",
      security: $("#elementorform-ajax-nonce").val(),
      feed_name: feed_name,
      elementorForms: elementorForms,
    };

    $.post(
      ajaxurl,
      data,
      function (response) {
        $(".fld-fetch-load").removeClass("loading");

        if (response.success) {
          if (
            response.data ===
            "Feed name already exists in the list, Please enter unique name of feed."
            ) {
            $(".feed-error-message").html(response.data).show();
          $(".feed-success-message").hide();
          setTimeout(function () {
            location.reload();
          }, 1000);
        } else {
          $(".feed-success-message").html(response.data).show();
          $(".feed-error-message").hide();

          $(".feedName, .elementorForms").val("");

          setTimeout(function () {
            location.reload();
          }, 1000);
        }
      }
    },
    "json",
    );
  });
  // Remove message when typing feed name
  $(document).on("input", ".feedName", function () {
    $(this).siblings(".input-msg").addClass("d-none");
  });

  // Remove message when selecting form
  $(document).on("change", ".elementorForms", function () {
    $(this).closest(".auto-select").siblings(".input-msg").addClass("d-none");
  });
  // Delete Elementor feed
  $(".delete-feed").click(function () {
    var feedId = $(this).data("feed-id");
    if (confirm("Are you sure you want to delete this feed?")) {
      $(".loading-sign-delete-feed-elegs" + feedId).addClass("loading");
      $.post(
        ajaxurl,
        {
          action: "delete_feed",
          feed_id: feedId,
          security: $("#elementorform-ajax-nonce").val(),
        },
        function (response) {
          $(".loading-sign-delete-feed-elegs" + feedId).removeClass("loading");

          if (response.success) {

            $(".feed-success-message").html(response.data).show();
            $(".feed-error-message").hide();

            setTimeout(function () {
              location.reload();
            }, 1000);

          } else {

            $(".feed-error-message").html(response.data).show();
            $(".feed-success-message").hide();

          }
        },
        );
    }
  });

  // Promotion popup logic
  const promoBox = $(".main-promotion-box");
  const closeBtn = $(".close-link");
  const hoursToWait = 6;
  const now = new Date().getTime();
  const closedTime = localStorage.getItem("promoBoxClosedTime");

  if (
    !closedTime ||
    now - parseInt(closedTime) > hoursToWait * 60 * 60 * 1000
    ) {
    promoBox.fadeIn();
}

closeBtn.on("click", function (e) {
  e.preventDefault();
  promoBox.fadeOut();
  window.open("https://www.gsheetconnector.com/", "_blank");
  promoBox.addClass("hidden");
  localStorage.setItem("promoBoxClosedTime", new Date().getTime().toString());
  localStorage.setItem("isHidden", "true");
});

if (localStorage.getItem("isHidden") === "true") {
  promoBox.addClass("hidden");
}

$(window).on("beforeunload", function () {
  localStorage.setItem(
    "isHidden",
    promoBox.hasClass("hidden") ? "true" : "false",
    );
});

$(window).on("load", function () {
  localStorage.removeItem("isHidden");
  promoBox.removeClass("hidden");
});

  // Helper to decode HTML
  function html_decode(input) {
    return new DOMParser().parseFromString(input, "text/html").documentElement
    .textContent;
  }
});

jQuery(document).ready(function ($) {
  // Ensure $ alias is used within this function scope

  // Hide the close-feed button initially
  $("#close-feed").hide();
  $(".add-feed-form").hide();

  // Show the add-feed form and toggle buttons
  $(document).on("click", "#add-new-feed", function () {
    $(".add-feed-form").show();
    $("#add-new-feed").hide();
    $("#close-feed").show();
  });

  // Hide the add-feed form and toggle buttons
  $(document).on("click", "#close-feed", function (event) {
    event.preventDefault(); // Prevent default link behavior
    $("#add-new-feed").show();
    $("#close-feed").hide();
    $(".add-feed-form").hide();
  });
});

/**
 * Display Error logs
 */

 jQuery(document).ready(function ($) {
  // Hide .wp-system-Error-logs initially
  $(".elemnt-system-Error-logs").hide();

  // Add a variable to track the state
  var isOpen = false;

  // Function to toggle visibility and button text
  function toggleLogs() {
    $(".elemnt-system-Error-logs").toggle();
    // Change button text based on visibility
    $(".elemnt-logs").text(isOpen ? "View" : "Close");
    isOpen = !isOpen; // Toggle the state
  }

  // Toggle visibility and button text when clicking .wpgsc-logs button
  $(".elemnt-logs").on("click", function () {
    toggleLogs();
  });

  // Prevent clicks inside the .elemnt-system-Error-logs div from closing it
  $(".elemnt-system-Error-logs").on("click", function (e) {
    e.stopPropagation(); // Prevents the div from closing when clicked inside
  });

  // Only close the .elemnt-system-Error-logs when the "Close" button is clicked
  $(".close-button").on("click", function () {
    $(".elemnt-system-Error-logs").hide();
    $(".elemnt-logs").text("View");
    isOpen = false;
  });
});

/**
 * Clear debug for integration page
 */
 jQuery(document).on("click", ".debug-clear-elementor", function () {
  jQuery(".clear-loading-sign").addClass("loading");
  var data = {
    action: "gscelementor_clear_debug_log",
    security: jQuery("#gs-ajax-nonce-ele").val(),
  };
  jQuery.post(ajaxurl, data, function (response) {
    var clear_msg = response.data;
    if (response == -1) {
      return false; // Invalid nonce
    }

    if (response.success) {
      jQuery(".clear-loading-sign").removeClass("loading");
      jQuery("#gs-validation-message").empty();
      jQuery(
        "<span class='gs-valid-message'>" + clear_msg + "</span>",
        ).appendTo("#gs-validation-message");
      setTimeout(function () {
        location.reload();
      }, 1000);
    }
  });
});

/**
 * Clear debug for system status tab
 */
 jQuery(document).on("click", ".clear-content-logs-elemnt", function () {
  jQuery(".clear-loading-sign-logs-elemnt").addClass("loading");
  var data = {
    action: "gscelementor_log_elementor_systeminfo",
    security: jQuery("#gs-ajax-nonce-ele").val(),
  };
  jQuery.post(ajaxurl, data, function (response) {
    if (response == -1) {
      return false; // Invalid nonce
    }

    if (response.success) {
      jQuery(".clear-loading-sign-logs-elemnt").removeClass("loading");
      jQuery(".clear-content-logs-msg-elemnt").html("Logs are cleared.");
      setTimeout(function () {
        location.reload();
      }, 1000);
    }
  });
});

 /* Select box JS Mitesh */
 document.addEventListener("DOMContentLoaded", function () {
  // ONLY select with class "gsc-select"
  document.querySelectorAll("select.gsc-select").forEach((select) => {
    // skip already processed
    if (select.classList.contains("auto-processed")) return;
    select.classList.add("auto-processed");

    // hide original select
    select.style.display = "none";

    const wrapper = document.createElement("div");
    wrapper.className = "auto-select";

    const display = document.createElement("div");
    display.className = "auto-select-display";
    display.innerText = select.options[select.selectedIndex]?.text || "Select";

    const optionsBox = document.createElement("div");
    optionsBox.className = "auto-select-options";

    [...select.options].forEach((opt, index) => {
      const item = document.createElement("div");
      item.className = "auto-select-option";
      item.innerText = opt.text;

      item.addEventListener("click", () => {
        select.selectedIndex = index;
        display.innerText = opt.text;
        optionsBox.style.display = "none";

        // trigger change event (WordPress + plugins compatible)
        select.dispatchEvent(new Event("change", { bubbles: true }));
      });

      optionsBox.appendChild(item);
    });

    display.addEventListener("click", (e) => {
      e.stopPropagation();
      optionsBox.style.display =
      optionsBox.style.display === "block" ? "none" : "block";
    });

    wrapper.appendChild(display);
    wrapper.appendChild(optionsBox);

    // insert before select & move select inside
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
  });

  // close dropdown on outside click
  document.addEventListener("click", function (e) {
    document.querySelectorAll(".auto-select-options").forEach((box) => {
      if (!box.parentElement.contains(e.target)) {
        box.style.display = "none";
      }
    });
  });
});

 document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".gsc-slider-wrapper").forEach(function (wrapper) {
    const slider = wrapper.querySelector(".gsc-slider");
    const slides = wrapper.querySelectorAll(".gsc-slide");
    const prevBtn = wrapper.querySelector(".gsc-nav.prev");
    const nextBtn = wrapper.querySelector(".gsc-nav.next");

    let current = 0;

    function updateSlider() {
      slider.style.transform = "translateX(" + -current * 100 + "%)";
    }

    nextBtn.addEventListener("click", function () {
      current = (current + 1) % slides.length;
      updateSlider();
    });

    prevBtn.addEventListener("click", function () {
      current = (current - 1 + slides.length) % slides.length;
      updateSlider();
    });
  });
});

 /* Popup Code for Choose API Setting */
 jQuery(document).ready(function ($) {
  $("#ele_dro_option").on("change", function () {
    var selectedValue = $(this).val();

    if (selectedValue === "elegs_manual") {
      $("#gselef-confirm-manual-popup-pro").removeClass("d-none");

      // reset dropdown
      $(this).val("elegs_html_existing");
    }

    if (selectedValue === "elegs_service") {
      $("#gselef-confirm-service-popup-pro").removeClass("d-none");

      // reset dropdown
      $(this).val("elegs_html_existing");
    }
  });
});
 jQuery(document).ready(function ($) {
  $(".gselef-popup-close-pro, .gselef-popup-service-close-pro").on(
    "click",
    function () {
      $(".gselef-popup-overlay").addClass("d-none");
    },
    );

  $(".gselef-popup-overlay").on("click", function (e) {
    if ($(e.target).is(".gselef-popup-overlay")) {
      $(this).addClass("d-none");
    }
  });
});

 jQuery(document).ready(function ($) {
  jQuery(document).on("click", "#execute-reset-free", function (e) {
    e.preventDefault();
    jQuery(".loading-sign-reset").addClass("loading");
    var feed_id = jQuery("#feed-id-sync").val();
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "gs_elementor_reset_feed",
        feed_id: feed_id,
        security: jQuery("#gs-ajax-nonce").val(),
      },
      success: function (response) {
        jQuery(".loading-sign-reset").removeClass("loading");

        if (response.success) {
          location.reload();
        }
      },
    });
  });
  if ($("#elementorformtable tr").length === 0) {
    $(".elementor-feeds-list").removeClass("mt-30");
  }

  jQuery(document).on("change", ".gselef-status-toggle", function () {
    var feed_id = jQuery(this).data("feed-id");
    var status = jQuery(this).is(":checked") ? 1 : 0;
    var nonce = jQuery("#elementorform-ajax-nonce").val();

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "gselef_update_status",
        feed_id: feed_id,
        status: status,
        security: nonce,
      },
      success: function (response) {
        if (status == 0) {
          jQuery("#feed-" + feed_id).addClass("row-disabled");
        } else {
          jQuery("#feed-" + feed_id).removeClass("row-disabled");
        }
      },
    });
  });
});
 
 document.addEventListener("DOMContentLoaded", function () {
  const inputs = document.querySelectorAll(
    "#edit-sheet-name, #edit-sheet-id, #edit-tab-name, #edit-tab-id"
    );

  const saveBtn = document.getElementById("gsele-execute-save");

  function checkInputs() {
    if (!saveBtn) return; // prevent error if button not found

    const allFilled = [...inputs].every(
      (input) => input.value.trim() !== ""
      );

    saveBtn.disabled = !allFilled;
    saveBtn.classList.toggle("common-disable", !allFilled);
  }

  inputs.forEach((input) =>
    input.addEventListener("input", checkInputs)
    );

  checkInputs();
});

 jQuery(document).ready(function ($) {
  const $checkbox = $("#gscele_elementor_uninstall_settings_free");
  const $saveBtn = $(".uninstall-settings-save-free");
  const $msg = $("#gselef-free-uninstall-msg-free");
  const $loader = $(".loading-uninstall-free");

  // page load → disable button
  $saveBtn.prop("disabled", true).addClass("common-disable");

  // enable button when toggle changes
  $checkbox.on("change", function () {
    $saveBtn.prop("disabled", false).removeClass("common-disable");
  });

  // save click
  $saveBtn.on("click", function (e) {
    e.preventDefault();

    var value = $checkbox.is(":checked") ? "Yes" : "No";

    $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "gscele_save_uninstall_settings",
        uninstall_setting: value,
        security: $("input[name='gscele-elementor-setting-ajax-nonce']").val(),
      },

      beforeSend: function () {
        $loader.addClass("loading");
        $saveBtn.prop("disabled", true).addClass("common-disable");
      },

      success: function (response) {
        if (!response.success) return;

        $msg.removeClass("gsc-success gsc-error d-none");

        $msg
        .addClass("gsc-success")
        .text("Plugin preferences updated successfully");

        setTimeout(function () {
          $msg.addClass("d-none").text("");
        }, 2000);
      },
      complete: function () {
        $loader.removeClass("loading");
      },
    });
  });
});
 jQuery(document).ready(function ($) {
  const noticeKey = "gselef-free_notice_hidden_until";
  const oneDay = 24 * 60 * 60 * 1000;
  //  const oneDay = 2 * 60 * 1000;

  // ❌ Close click ONLY
  $("#pro-dismiss-header-notice").on("click", function () {
    const now = new Date().getTime();

    // Hide notice
    $(".gselef-free #pro-notice-bar").slideUp(200);

    // Save hide time for 1 day
    localStorage.setItem(noticeKey, now + oneDay);
  });

  // ⏳ On page load: check if 1 day passed
  const hideUntil = localStorage.getItem(noticeKey);
  const now = new Date().getTime();

  if (hideUntil && now < hideUntil) {
    $(".gselef-free #pro-notice-bar").hide();
  }
});
 document.addEventListener("DOMContentLoaded", function () {
  const copyBtn = document.getElementById("gscgff-copy-logs-info");
  const msgDiv = document.querySelector(".gsc-copy-msg");

  if (!copyBtn || !msgDiv) return;

  copyBtn.addEventListener("click", function (e) {
    e.preventDefault();

    const tbody = document.querySelector(".error-log-table tbody");
    if (!tbody) return;

    const rows = tbody.querySelectorAll("tr");

    if (!rows.length) {
      showMessage("No logs found to copy.", "error");
      return;
    }

    let output = "";

    rows.forEach((tr) => {
      const cols = tr.querySelectorAll("td");
      if (cols.length < 5) return;

      const date = (cols[0].innerText || "").trim();
      const errorId = (cols[1].innerText || "").trim();
      const code = (cols[2].innerText || "").trim();
      const message = (cols[3].innerText || "").trim();

      // 🔥 Proper details extraction
      const detailsCell = cols[4];
      const details = detailsCell.querySelector("pre")
      ? detailsCell.querySelector("pre").innerText.trim()
      : (detailsCell.innerText || "").trim();

      output += `Date: ${date}\n`;
      output += `Error ID: ${errorId}\n`;
      output += `Code: ${code}\n`;
      output += `Message: ${message}\n`;
      output += `Details: ${details}\n`;
      output += `\n==========================\n\n`;
    });

    // Modern Clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
      .writeText(output)
      .then(function () {
        showMessage("Copied successfully", "success");
      })
      .catch(function () {
        fallbackCopy(output);
      });
    } else {
      fallbackCopy(output);
    }

    // Fallback Method
    function fallbackCopy(text) {
      const ta = document.createElement("textarea");
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand("copy");
        showMessage("Copied successfully", "success");
      } catch (err) {
        showMessage("Copy failed. Please copy manually.", "error");
      }
      document.body.removeChild(ta);
    }

    function showMessage(text, type) {
      msgDiv.innerText = text;
      msgDiv.classList.remove("d-none");

      setTimeout(function () {
        msgDiv.classList.add("d-none");
      }, 2000);
    }
  });
});
