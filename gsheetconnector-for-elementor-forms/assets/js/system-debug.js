/**
 * Copies formatted system information text from the .gselef-free-info-container element to clipboard.
 * It extracts all <h3> headings and <td> values inside .info-content, formats them,
 * and shows a temporary success message upon successful copy.
 */
 jQuery(document).ready(function ($) {
  /**
   * Copy formatted system info and show message below the button
   */
   function copySystemInfo(btn) {

    if (!btn) return;

    var wrapper = document.querySelector("#system-info-wrapper");

    if (!wrapper) return;

    var textToCopy = "";

    /* ===== LOOP ALL SECTIONS ===== */
    wrapper.querySelectorAll(".info-button").forEach(function (button) {

      /* ===== SECTION TITLE ===== */
      var sectionTitle = button.childNodes[0].textContent.trim();

      textToCopy += "\n====================================\n";
      textToCopy += sectionTitle + "\n";
      textToCopy += "====================================\n\n";

      /* ===== GET NEXT CONTENT DIV ===== */
      var contentDiv = button.parentElement.nextElementSibling;

      if (!contentDiv) return;

      /* ===== LOOP TABLE ROWS ===== */
      contentDiv.querySelectorAll("table tr").forEach(function (row) {

        var cols = row.querySelectorAll("td");

        if (cols.length >= 2) {

          var label = cols[0].innerText.trim();
          var value = cols[1].innerText.trim();

          textToCopy += label + ": " + value + "\n";
        }
      });

      textToCopy += "\n";
    });

    textToCopy = textToCopy.trim();

    if (!textToCopy) {
      console.error("Nothing to copy");
      return;
    }

    /* ===== MESSAGE ===== */
    var msgDiv = btn.parentNode.querySelector(".gsc-copy-msg");

    if (!msgDiv) {

      msgDiv = document.createElement("div");

     msgDiv.className = "gsc-copy-msg";

      msgDiv.style.display = "none";

      btn.parentNode.appendChild(msgDiv);
    }

    function showCopied() {

      msgDiv.innerHTML = "Copied successfully.";

      msgDiv.style.display = "block";

      setTimeout(function () {

        msgDiv.style.display = "none";

      }, 2000);
    }

    /* ===== COPY ===== */
    if (navigator.clipboard && window.isSecureContext) {

      navigator.clipboard.writeText(textToCopy)
      .then(showCopied)
      .catch(function (err) {
        console.error("Clipboard error:", err);
      });

    } else {

      var textarea = document.createElement("textarea");

      textarea.value = textToCopy;

      textarea.style.position = "fixed";

      textarea.style.left = "-9999px";

      document.body.appendChild(textarea);

      textarea.focus();

      textarea.select();

      try {

        document.execCommand("copy");

        showCopied();

      } catch (e) {

        console.error("Copy failed", e);
      }

      document.body.removeChild(textarea);
    }
  }

  /* ===== BUTTON CLICK ===== */
  $(document).on("click", "#gselef-free-system-copy", function () {

    copySystemInfo(this);

  });

  // 🔹 Ensure first section open on page load
  $("#gselef-free-info-container").show();

  function accordionToggle(button, container) {
    $(button).on("click", function () {
      if ($(container).is(":visible")) {
        // second click → close same section
        $(container).slideUp();
      } else {
        // open clicked, close others
        $(".info-content").slideUp();
        $(container).slideDown();
      }
    });
  }

  accordionToggle("#gselef-free-show-info-button", "#gselef-free-info-container");
  accordionToggle(
    "#gselef-free-show-wordpress-info-button",
    "#wordpress-gselef-free-info-container",
    );
  accordionToggle("#show-Drop-info-button", "#Drop-gselef-free-info-container");
  
  accordionToggle(
    "#gselef-free-show-active-theme-button",
    "#active-gselef-free-theme-info-container",
    );

  accordionToggle(
    "#gselef-free-show-active-info-button",
    "#active-gselef-free-info-container",
    );
  accordionToggle(
    "#gselef-free-show-netplug-info-button",
    "#netplug-gselef-free-info-container",
    );
  accordionToggle(
    "#gselef-free-show-acplug-info-button",
    "#acplug-gselef-free-info-container",
    );
  accordionToggle(
    "#gselef-free-show-server-info-button",
    "#server-gselef-free-info-container",
    );
  accordionToggle(
    "#gselef-free-show-database-info-button",
    "#database-gselef-free-info-container",
    );
  accordionToggle(
    "#gselef-free-show-wrcons-info-button",
    "#wrcons-gselef-free-info-container",
    );
  accordionToggle(
    "#gselef-free-show-ftps-info-button",
    "#ftps-gselef-free-info-container",
    );
});
/**
 * Adds event listener to the copy button to trigger error log copying
 * once the DOM content is fully loaded.
 */

 document.addEventListener("DOMContentLoaded", function () {
  /**
   * Copies the content of the error log textarea to the clipboard
   * and shows a temporary "Copied" confirmation message.
   */

   function copyErrorLog() {
    // Select the textarea containing the error log
    var textarea = document.querySelector(".errorlog");

    // Select the message div (button na niche no div)
    var copyMessage = document.querySelector(".gsc-copy-msg");

    if (textarea && copyMessage) {
      textarea.select();

      try {
        // Copy text
        document.execCommand("copy");

        // Show message
        copyMessage.classList.remove("d-none");

        // Hide message after 3 seconds
        setTimeout(function () {
          copyMessage.classList.add("d-none");
        }, 3000);
      } catch (err) {
        console.error("Unable to copy error log:", err);
      }

      textarea.blur();
    }
  }

  var copyButton = document.querySelector(".copy");

  if (copyButton) {
    copyButton.addEventListener("click", function (event) {
      event.preventDefault();
      copyErrorLog();
    });
  }
});
 jQuery(document).ready(function ($) {
  $("#gselef-free-copy-logs-info").on("click", function (e) {
    e.preventDefault();

    var rows = $(".gselef-free-error-table tr");
    var copyText = "";

    if (!rows.length) {
      alert("No error logs found.");
      return;
    }

    rows.each(function () {
      var cols = $(this).find("td");

      if (cols.length >= 4) {
        copyText += $(cols[0]).text().trim() + "\n"; // Date
        copyText += $(cols[1]).text().trim() + "\n"; // Type
        copyText += $(cols[2]).text().trim() + "\n"; // Message
        copyText += $(cols[3]).text().trim() + "\n"; // File
        copyText += "----------------------------------------\n\n";
      }
    });

    // Temporary textarea copy
    var tempTextarea = $("<textarea>");
    $("body").append(tempTextarea);
    tempTextarea.val(copyText).select();
    document.execCommand("copy");
    tempTextarea.remove();

    // Show success message
    var $msg = $(".gsc-copy-msg");

    $msg.text("Copied successfully").removeClass("d-none");

    setTimeout(function () {
      $msg.addClass("d-none");
    }, 3000);
  });
});
 jQuery(document).ready(function ($) {
  $("#gselef-free-csv-info").on("click", function (e) {
    e.preventDefault();

    var rows = $(".gselef-free-error-table tr");
    var csvContent = "";

    if (rows.length === 0) {
      alert("No error logs found.");
      return;
    }

    rows.each(function () {
      var cols = $(this).find("th, td");
      var rowData = [];

      cols.each(function () {
        var text = $(this).text().trim();

        // Escape quotes
        text = text.replace(/"/g, '""');

        rowData.push('"' + text + '"');
      });

      csvContent += rowData.join(",") + "\n";
    });

    // Create Blob
    var blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });

    var link = document.createElement("a");
    var url = URL.createObjectURL(blob);

    link.setAttribute("href", url);
    link.setAttribute("download", "error-log.csv");
    link.style.visibility = "hidden";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });
});
