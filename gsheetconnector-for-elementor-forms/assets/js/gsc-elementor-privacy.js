/*
 * GDPR & Privacy Policy tab: notice editors, live preview and save.
 */
jQuery(function ($) {
  "use strict";

  if (!$("#gselef_privacy_save").length) {
    return;
  }

  // MetForm draws its form with its own scripts, so it is previewed in a same-origin iframe.
  // The notices are then edited inside that frame's document; every other form is in the page itself.
  function getPreviewRoot() {
    var $wrap = $("#gselef_form_preview");
    var $frame = $wrap.find("iframe.gselef-preview-frame");
    if (!$frame.length) {
      return $wrap;
    }
    var doc = null;
    try {
      doc = $frame[0].contentDocument;
    } catch (e) {
      doc = null;
    }
    return doc && doc.body ? $(doc.body).find(".gselef-preview-canvas").first() : $();
  }

  function initNoticeBlock(prefix, editorId, cssClass) {
    var $editorTextarea = $("#" + editorId);
    var $enable = $("#" + prefix + "_enable");
    if (!$editorTextarea.length) {
      return null;
    }

    function getPreviewNode() {
      if (!cssClass) {
        return $();
      }
      var $wrap = getPreviewRoot();
      var $node = $wrap.find("." + cssClass);
      if (!$node.length && $wrap.length) {
        $node = $('<div class="' + cssClass + '"></div>').appendTo($wrap);
      }
      return $node;
    }

    // The Visual tab is TinyMCE; the Code tab edits the textarea directly.
    function getContent() {
      if (typeof tinymce !== "undefined") {
        var ed = tinymce.get(editorId);
        if (ed && !ed.isHidden()) {
          return ed.getContent();
        }
      }
      return $editorTextarea.val();
    }

    function applyText() {
      getPreviewNode().html(getContent());
    }
    function applyEnabled() {
      var isOn = !$enable.length || $enable.is(":checked");
      getPreviewNode().toggle(isOn);
    }

    $editorTextarea.on("input", applyText);
    $(document).on("tinymce-editor-init", function (e, editor) {
      if (editor.id === editorId) {
        editor.on("input change keyup SetContent Undo Redo", applyText);
      }
    });
    $enable.on("change", applyEnabled);

    applyEnabled();

    return {
      editorId: editorId,
      enable: $enable,
      applyAll: applyEnabled,
      getContent: getContent,
    };
  }

  function syncSectionVisibility() {
    $(".gsc-branding-section").toggleClass(
      "d-none",
      !$("#gselef_branding_enable").is(":checked"),
    );
    $(".gdpr-section").toggleClass(
      "d-none",
      !$("#gselef_gdpr_enable").is(":checked"),
    );
    $(".gdpr-privacy-section").toggleClass(
      "d-none",
      !$("#gselef_privacy_enable").is(":checked"),
    );
  }

  // A TinyMCE editor that initialises while hidden never renders correctly, so
  // it is re-created once its section becomes visible.
  function refreshHiddenEditor(editorId) {
    if (typeof tinymce === "undefined" || !editorId) {
      return;
    }
    var editor = tinymce.get(editorId);
    if (!editor || !editor.initialized) {
      return;
    }
    tinymce.execCommand("mceRemoveEditor", false, editorId);
    tinymce.execCommand("mceAddEditor", false, editorId);
  }

  $("#gselef_gdpr_enable").on("change", function () {
    syncSectionVisibility();
    if ($(this).is(":checked")) {
      refreshHiddenEditor("gselefgdprtext");
    }
  });
  $("#gselef_privacy_enable").on("change", function () {
    syncSectionVisibility();
    if ($(this).is(":checked")) {
      refreshHiddenEditor("gselefprivacytext");
    }
  });
  $("#gselef_branding_enable").on("change", syncSectionVisibility);

  syncSectionVisibility();

  // The Privacy Policy notice only appears on the designated Privacy Policy
  // page on the front end, so it is not live-previewed on the sample form.
  var privacyBlock = initNoticeBlock("gselef_privacy", "gselefprivacytext");
  var gdprBlock = initNoticeBlock(
    "gselef_gdpr",
    "gselefgdprtext",
    "gselef-frontend-gdpr-notice",
  );

  $("#gselef_privacy_save").on("click", function () {
    var $btn = $(this);
    var $loader = $(".gselef_privacy_loder");
    var $msg = $("#gselef-privacy-popup");

    $loader.addClass("loading");
    $btn.prop("disabled", true);

    var data = {
      action: "gselef_free_save_privacy_setting",
      nonce: $("#gselef-privacy-setting-ajax-nonce").val(),
    };
    if ($("#gselef_branding_enable").length) {
      data.gselef_branding_enable = $("#gselef_branding_enable").is(":checked")
        ? 1
        : 0;
      data.gselef_branding_display =
        $('input[name="gselef_branding_display"]:checked').val() || "visible";
      data.gselef_branding_nofollow = $(
        'input[name="gselef_branding_nofollow"]',
      ).is(":checked")
        ? 1
        : 0;
    }
    if (privacyBlock) {
      data.gselef_privacy_enable = privacyBlock.enable.is(":checked") ? 1 : 0;
      data.gselef_privacy_text = privacyBlock.getContent();
    }
    if (gdprBlock) {
      data.gselef_gdpr_enable = gdprBlock.enable.is(":checked") ? 1 : 0;
      data.gselef_gdpr_text = gdprBlock.getContent();
    }

    $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: data,
      success: function () {
        $msg.removeClass("d-none");
      },
      error: function () {
        $msg.removeClass("d-none gsc-success");
      },
      complete: function () {
        $loader.removeClass("loading");
        $btn.prop("disabled", false);
        setTimeout(function () {
          $msg.addClass("d-none");
          location.reload();
        }, 2500);
      },
    });
  });

  var previewXhr = null;

  // The display-only preview must never submit a form (Enter key, form scripts).
  // Capture phase on document, so it runs before any handler the form's own script attached.
  document.addEventListener(
    "submit",
    function (e) {
      if (e.target && e.target.closest && e.target.closest("#gselef_form_preview")) {
        e.preventDefault();
        e.stopPropagation();
      }
    },
    true
  );

  // An iframe preview (MetForm) loads after the markup is inserted: apply the live notice state then.
  function bindPreviewFrame() {
    $("#gselef_form_preview iframe.gselef-preview-frame").on("load", function () {
      if (gdprBlock) {
        gdprBlock.applyAll();
      }
    });
  }
  bindPreviewFrame();

  // Load the selected form into the preview, like the Fluent Forms tab.
  $("#gselef_preview_form").on("change", function () {
    var formId = $(this).val();
    var $preview = $("#gselef_form_preview");

    if (previewXhr) {
      previewXhr.abort();
      previewXhr = null;
    }
    if (!formId) {
      $preview.html("");
      return;
    }

    $preview.addClass("loading").html("");

    previewXhr = $.ajax({
      url: ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "gselef_free_preview_form",
        form_id: formId,
        nonce: $("#gselef-privacy-setting-ajax-nonce").val(),
      },
      success: function (response) {
        if (response && response.success && response.data && response.data.html) {
          $preview.html(response.data.html);
        } else {
          var msg =
            response && response.data && response.data.message
              ? response.data.message
              : "Unable to load form preview.";
          $preview.text(msg);
        }
        bindPreviewFrame();
        if (gdprBlock) {
          gdprBlock.applyAll();
        }
      },
      error: function (xhr, status) {
        if (status === "abort") return;
        $preview.text("Unable to load form preview.");
      },
      complete: function () {
        $preview.removeClass("loading");
        previewXhr = null;
      },
    });
  });
});
