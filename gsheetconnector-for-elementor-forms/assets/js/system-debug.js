jQuery(document).ready(function ($) {
   
    // Clear Error Log
    function clearErrorLog() {
        $('.errorlog').val('');
    }

    // Bind toggle buttons
    $('#show-info-button').on('click', function () {
        $('#info-container').slideToggle();
    });
    $('#show-wordpress-info-button').on('click', function () {
        $('#wordpress-info-container').slideToggle();
    });
    $('#show-Drop-info-button').on('click', function () {
        $('#Drop-info-container').slideToggle();
    });
    $('#show-active-info-button').on('click', function () {
        $('#active-info-container').slideToggle();
    });
    $('#show-netplug-info-button').on('click', function () {
        $('#netplug-info-container').slideToggle();
    });
    $('#show-acplug-info-button').on('click', function () {
        $('#acplug-info-container').slideToggle();
    });
    $('#show-server-info-button').on('click', function () {
        $('#server-info-container').slideToggle();
    });
    $('#show-database-info-button').on('click', function () {
        $('#database-info-container').slideToggle();
    });
    $('#show-wrcons-info-button').on('click', function () {
        $('#wrcons-info-container').slideToggle();
    });
    $('#show-ftps-info-button').on('click', function () {
        $('#ftps-info-container').slideToggle();
    });

    // Bind copy and clear buttons
    $('.copy-system-info').on('click', function (e) {
        e.preventDefault();
        copySystemInfo();
    });

    $('.copy-error-log').on('click', function (e) {
        e.preventDefault();
        copyErrorLog();
    });

    $('.wcgsc-clear-content-logs').on('click', function (e) {
        e.preventDefault();
        clearErrorLog();
    });
});


 // Copy System Info to Clipboard
function copySystemInfo() {
    const $container = $('.info-container');
    const $elements = $container.find('.info-content h3, .info-content td');
    let systemInfoText = '';
    let currentRow = '';

    $elements.each(function () {
        const $el = $(this);
        const tagName = $el.prop('tagName').toLowerCase();

        if ($el.text().trim()) {
            if (tagName === 'h3') {
                if (currentRow !== '') {
                    systemInfoText += currentRow.trim() + '\n\n';
                }
                systemInfoText += `**${$el.text().trim()}**\n\n`;
                currentRow = '';
            } else if (tagName === 'td') {
                const $labelEl = $el.prev('td');
                if ($labelEl.length && $labelEl.text().trim()) {
                    const label = $labelEl.text().trim();
                    currentRow += `${label}: ${$el.text().trim()}\n`;
                }
            }
        }
    });

    systemInfoText += currentRow.trim();

    navigator.clipboard.writeText(systemInfoText.trim()).then(function () {
        const $msg = $('<div class="copy-success-message">System info copied!</div>');
        $('body').append($msg);
        setTimeout(() => $msg.remove(), 3000);
    }).catch(function (err) {
        console.error('Unable to copy system info:', err);
    });
}

// Copy Error Log
function copyErrorLog() {
    const $textarea = $('.errorlog');
    const $copyMessage = $('.copy-message');

    if ($textarea.length && $copyMessage.length) {
        $textarea.select();
        try {
            document.execCommand('copy');
            $copyMessage.show();
            setTimeout(() => $copyMessage.hide(), 3000);
        } catch (err) {
            console.error('Unable to copy error log:', err);
            alert('Error log copy failed. Please copy it manually.');
        }
        $textarea.blur();
    } else {
        alert('Error log textarea or copy message not found.');
    }
}