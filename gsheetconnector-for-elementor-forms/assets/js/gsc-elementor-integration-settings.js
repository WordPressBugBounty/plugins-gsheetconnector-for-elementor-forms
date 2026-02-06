jQuery(document).ready(function ($) {

    // Verify the API code
    $(document).on('click', '#save-ele-code', function () {
        $(this).parent().children(".loading-sign").addClass("loading");
        var data = {
            action: 'verify_gscelementor_integation',
            code: $('#ele-code').val(),
            security: $('#gs-ajax-nonce-ele').val()
        };
        $.post(ajaxurl, data, function (response) {
            $(".loading-sign").removeClass("loading");

            if (response == -1) return;

            $("#gs-validation-message").empty();

            if (!response.success) {
                $("<span class='error-message'>Invalid Access code entered.</span>").appendTo('#gs-validation-message');
            } else {
                $("<span class='gs-valid-message'>Your Google Access Code is Authorized and Saved.</span><br/><br/><span class='wp-valid-notice'>Note: If you are getting any errors or not showing sheet in dropdown, then check the debug log. To contact us, send your debug log.</span>").appendTo('#gs-validation-message');
                setTimeout(function () {
                    window.location.href = $("#redirect_auth_eleforms").val();
                }, 1000);
            }
        });
    });

    // Deactivate the API code
    $(document).on('click', '#deactivate-log-ele', function () {
        $(".loading-sign-deactive").addClass("loading");
        if (confirm("Are You sure you want to deactivate Google Integration ?")) {
            var data = {
                action: 'deactivate_gscelementor_integation',
                security: $('#gs-ajax-nonce-ele').val()
            };
            $.post(ajaxurl, data, function (response) {
                $(".loading-sign-deactive").removeClass("loading");
                $("#deactivate-message").empty();

                if (response == -1 || !response.success) {
                    alert('Error while deactivation');
                } else {
                    $("<span class='gs-valid-message'>Your account is removed. Reauthenticate again to integrate Elementor Form with Google Sheet.</span>").appendTo('#deactivate-message');
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            });
        } else {
            $(".loading-sign-deactive").removeClass("loading");
        }
    });

    // Sync Google account
    $(document).on('click', '#ele-sync', function () {
    $(this).parent().children(".loading-sign").addClass("loading");

    var data = {
        action: 'sync_google_account_gscelementor', // unified
        isinit: $(this).data("init"),
        security: $('#gs-ajax-nonce-ele').val()
    };

    $.post(ajaxurl, data, function (response) {
        $(".loading-sign").removeClass("loading");
        if (response == -1 || !response.success) return;

        $("#gs-validation-message").empty();

        if (response.data.success === "yes") {
            $("<span class='gs-valid-message'>Fetched all sheet details.</span>").appendTo('#gs-validation-message');
            setTimeout(function () { location.reload(); }, 1000);
        }
    });
});

    // Get tab ID on tab name change
    $(document).on("change", "#gs-sheet-tab-name", function () {
        var tabname = $(this).val();
        var sheetname = $("#gs-sheet-name").val();
        $(this).parent().children(".loading-sign").addClass("loading");
        var data = {
            action: 'get_sheet_id',
            tabname: tabname,
            sheetname: sheetname,
            security: $('#gs-ajax-nonce-ele').val()
        };
        $.post(ajaxurl, data, function (response) {
            $(".loading-sign").removeClass("loading");
            if (response == -1) return;
            if (response.success) {
                $('#sheet-url').html(html_decode(response.data));
            }
        });
    });

    // Toggle manual sheet name input
    $(document).on("click", "#manual-name", function () {
        $(".loading-sign").addClass("loading");
        if ($(this).is(":checked")) {
            $(".sheet-details").addClass('hide');
            $(".manual-fields").removeClass('hide');
        } else {
            $(".sheet-details").removeClass('hide');
            $(".manual-fields").addClass('hide');
        }
    });

    // Toggle misc options (sorting/color)
    $("#enable-sorting-option, #enable-colors-option").change(function () {
        const inner = $(this).parents(".misc-options-row").find(".misc-options-inner");
        $(this).is(":checked") ? inner.show() : inner.hide();
    });

    // WP color picker
    if ($(".inline-colors input").length > 0) {
        $(".inline-colors input").wpColorPicker();
    }

    // Deactivate Service Auth
    $(document).on('click', '#ele-deactivate-auth', function (e) {
        e.preventDefault();
        $(".loading-sign").addClass("loading");
        var data = {
            action: 'deactivate_auth_gscelementor',
            security: $('#gs-ajax-nonce-ele').val()
        };
        $.post(ajaxurl, data, function (response) {
            $(".loading-sign").removeClass("loading");
            $("#ele-validation-message").empty();
            if (!response.success) {
                $("<span class='error-message'>Access code can't be blank.</span>").appendTo('#ele-validation-message');
            } else {
                $("<span class='ele-valid-message'>Your account is removed. Reauthenticate again to integrate Elementor Form with Google Sheet.</span>").appendTo('#ele-validation-message');
                setTimeout(function () { location.reload(); }, 1000);
            }
        });
    });

    // Reset client credentials form
    $(document).on('click', '#save-ele-reset', function () {
        $("#ele-client-id, #ele-secret-id, #ele-client-token").val('').removeAttr('disabled');
        $("#save-ele-manual").removeAttr('disabled');
    });

    // Save Elementor feed
    $(document).on('click', '.elementor-gs-sub-btn', function () {
        var feed_name = $('.feedName').val();
        var elementorForms = $('.elementorForms').val();

        if (feed_name && elementorForms) {
            $(".fld-fetch-load").addClass("loading");
            var data = {
                action: 'save_gscelementor_feed',
                security: $('#elementorform-ajax-nonce').val(),
                feed_name: feed_name,
                elementorForms: elementorForms
            };
            $.post(ajaxurl, data, function (response) {
                $(".fld-fetch-load").removeClass("loading");
                if (response.success) {
                    if (response.data === 'Feed name already exists in the list, Please enter unique name of feed.') {
                        $('.feed-error-message').html(response.data).show();
                        $(".feed-success-message").hide();
                    } else {
                        $('.feed-success-message').html(response.data).show();
                        $(".feed-error-message").hide();
                        $('.feedName, .elementorForms').val('');
                        setTimeout(function () { location.reload(); }, 1000);
                    }
                }
            }, 'json');
        } else {
            alert('Please Enter Feed Name and Select Form.');
        }
    });

    // Delete Elementor feed
    $('.delete-feed').click(function () {
        var feedId = $(this).data('feed-id');
        if (confirm('Are you sure you want to delete this feed?')) {
            $(".loading-sign-delete-feed-elegs" + feedId).addClass("loading");
            $.post(ajaxurl, {
                action: 'delete_feed',
                feed_id: feedId,
                security: $('#elementorform-ajax-nonce').val()
            }, function (response) {
                $(".loading-sign-delete-feed-elegs" + feedId).removeClass("loading");
                if (response === 'success') {
                    location.reload();
                } else {
                    console.log('Error deleting feed');
                }
            });
        }
    });

    // Promotion popup logic
    const promoBox = $('.main-promotion-box');
    const closeBtn = $('.close-link');
    const hoursToWait = 6;
    const now = new Date().getTime();
    const closedTime = localStorage.getItem('promoBoxClosedTime');

    if (!closedTime || now - parseInt(closedTime) > hoursToWait * 60 * 60 * 1000) {
        promoBox.fadeIn();
    }

    closeBtn.on('click', function (e) {
        e.preventDefault();
        promoBox.fadeOut();
        window.open('https://www.gsheetconnector.com/', '_blank');
        promoBox.addClass('hidden');
        localStorage.setItem('promoBoxClosedTime', new Date().getTime().toString());
        localStorage.setItem('isHidden', 'true');
    });

    if (localStorage.getItem('isHidden') === 'true') {
        promoBox.addClass('hidden');
    }

    $(window).on('beforeunload', function () {
        localStorage.setItem('isHidden', promoBox.hasClass('hidden') ? 'true' : 'false');
    });

    $(window).on('load', function () {
        localStorage.removeItem('isHidden');
        promoBox.removeClass('hidden');
    });

    // Helper to decode HTML
    function html_decode(input) {
        return new DOMParser().parseFromString(input, "text/html").documentElement.textContent;
    }

});

jQuery(document).ready(function($) {
    // Ensure $ alias is used within this function scope

    // Hide the close-feed button initially
    $("#close-feed").hide();

    // Show the add-feed form and toggle buttons
    $(document).on('click', '#add-new-feed', function() {
        $(".add-feed-form").show();
        $("#add-new-feed").hide();
        $("#close-feed").show();
    });

    // Hide the add-feed form and toggle buttons
    $(document).on('click', '#close-feed', function(event) {
        event.preventDefault(); // Prevent default link behavior
        $("#add-new-feed").show();
        $("#close-feed").hide();
        $(".add-feed-form").hide();
    });
});

/**
 * Display Error logs
 */

jQuery(document).ready(function($) {
  // Hide .wp-system-Error-logs initially
  $('.elemnt-system-Error-logs').hide();

  // Add a variable to track the state
  var isOpen = false;

  // Function to toggle visibility and button text
  function toggleLogs() {
      $('.elemnt-system-Error-logs').toggle();
      // Change button text based on visibility
      $('.elemnt-logs').text(isOpen ? 'View' : 'Close');
      isOpen = !isOpen; // Toggle the state
  }

  // Toggle visibility and button text when clicking .wpgsc-logs button
  $('.elemnt-logs').on('click', function() {
      toggleLogs();
  });

  // Prevent clicks inside the .elemnt-system-Error-logs div from closing it
  $('.elemnt-system-Error-logs').on('click', function(e) {
      e.stopPropagation(); // Prevents the div from closing when clicked inside
  });

  // Only close the .elemnt-system-Error-logs when the "Close" button is clicked
  $('.close-button').on('click', function() {
      $('.elemnt-system-Error-logs').hide();
      $('.elemnt-logs').text('View');
      isOpen = false;
  });
});

/**
 * Clear debug for integration page
 */
jQuery(document).on('click', '.debug-clear-elementor', function () {
    jQuery(".clear-loading-sign").addClass("loading");
    var data = {
        action: 'gscelementor_clear_debug_log',
        security: jQuery('#gs-ajax-nonce-ele').val()
    };
    jQuery.post(ajaxurl, data, function (response) {
         var clear_msg = response.data;
        if (response == -1) {
            return false; // Invalid nonce
        }

        if (response.success) {
            jQuery(".clear-loading-sign").removeClass("loading");
            jQuery("#gs-validation-message").empty();
            jQuery("<span class='gs-valid-message'>"+clear_msg+"</span>").appendTo('#gs-validation-message');
            setTimeout(function () {
                    location.reload();
                }, 1000);
        }
    });
});

/**
* Clear debug for system status tab
*/
jQuery(document).on('click', '.clear-content-logs-elemnt', function () {

  jQuery(".clear-loading-sign-logs-elemnt").addClass("loading");
  var data = {
     action: 'gscelementor_log_elementor_systeminfo',
     security: jQuery('#gs-ajax-nonce-ele').val()
  };
  jQuery.post(ajaxurl, data, function ( response ) {
     if (response == -1) {
        return false; // Invalid nonce
     }
     
     if (response.success) {
        jQuery(".clear-loading-sign-logs-elemnt").removeClass("loading");
        jQuery('.clear-content-logs-msg-elemnt').html('Logs are cleared.');
        setTimeout(function () {
                    location.reload();
                }, 1000);
     }
  });
});
