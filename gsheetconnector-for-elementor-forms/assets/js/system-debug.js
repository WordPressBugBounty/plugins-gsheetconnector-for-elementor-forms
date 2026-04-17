/**
 * Copies formatted system information text from the .gselef-free-info-container element to clipboard.
 * It extracts all <h3> headings and <td> values inside .info-content, formats them,
 * and shows a temporary success message upon successful copy.
 */
 jQuery(document).ready(function ($) {
  jQuery(document).on("click", "#gselef-free-system-copy", function (e) {
    e.preventDefault();
    alert('hello');
    copySystemInfo();
  });

  function copySystemInfo() {
    const systemInfoContainer = document.querySelector(
      ".info-container",
      );

    if (!systemInfoContainer) {
      return;
    }

    const systemInfoElements = systemInfoContainer.querySelectorAll(
      ".info-content h3, .info-content td",
      );

    let systemInfoText = "";
    let currentRow = "";

    systemInfoElements.forEach((element) => {
      if (element.innerText) {
        const tagName = element.tagName.toLowerCase();

        // Section headings
        if (tagName === "h3") {
          if (currentRow !== "") {
            systemInfoText += currentRow.trim() + "\n\n";
          }

          systemInfoText += element.innerText + "\n\n";
          currentRow = "";
        }

        // Table data
        else if (tagName === "td") {
          const labelElement = element.previousElementSibling;

          if (labelElement && labelElement.innerText) {
            let label = labelElement.innerText.trim();

            currentRow += label + ": " + element.innerText.trim() + "\n";
          }
        }
      }
    });

    systemInfoText += currentRow.trim();

    // Copy to clipboard
    var tempTextarea = document.createElement("textarea");
    tempTextarea.value = systemInfoText.trim();
    document.body.appendChild(tempTextarea);

    tempTextarea.select();
    document.execCommand("copy");

    document.body.removeChild(tempTextarea);

    // Show success message
    jQuery(".gsc-copy-msg").removeClass("d-none").hide().fadeIn();

    setTimeout(function () {
      jQuery(".gsc-copy-msg").fadeOut();
    }, 2000);
  }

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

  accordionToggle("#show-info-button", "#gselef-free-info-container");
  accordionToggle(
    "#show-wordpress-info-button",
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
