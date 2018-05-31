/* ------------------------------------------------------------------------------
 *
 *  # Styled checkboxes, radios and file input
 *
 *  Specific JS code additions for form_checkboxes_radios.html page
 *
 *  Version: 1.0
 *  Latest update: Aug 1, 2015
 *
 * ---------------------------------------------------------------------------- */

$(function () {
// Checkboxes/radios (Uniform)
    // ------------------------------

    // Default initialization
    if ($(".styled, .multiselect-container input").length > 0) {
        $(".styled, .multiselect-container input").uniform({
            radioClass: 'choice'
        });
    }

    // File input
    if ($(".file-styled").length > 0) {
        $(".file-styled").uniform({
            wrapperClass: 'bg-blue',
            fileButtonHtml: '<i class="icon-file-plus"></i>'
        });
    }

    // Switchery
    // ------------------------------

    // Initialize multiple switches
    if (Array.prototype.forEach) {
        var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
        elems.forEach(function (html) {
            var switchery = new Switchery(html);
        });
    }
    else {
        var elems = document.querySelectorAll('.switchery');
        for (var i = 0; i < elems.length; i++) {
            var switchery = new Switchery(elems[i]);
        }
    }


    //
    // Contextual colors
    //

    // Primary
    if ($(".control-primary").length > 0) {
        $(".control-primary").uniform({
            radioClass: 'choice',
            wrapperClass: 'border-primary-600 text-primary-800'
        });
    }

    // Danger
    if ($(".control-danger").length > 0) {
        $(".control-danger").uniform({
            radioClass: 'choice',
            wrapperClass: 'border-danger-600 text-danger-800'
        });
    }

    // Success
    if ($(".control-success").length > 0) {
        $(".control-success").uniform({
            radioClass: 'choice',
            wrapperClass: 'border-success-600 text-success-800'
        });
    }

    // Warning
    if ($(".control-warning").length > 0) {
        $(".control-warning").uniform({
            radioClass: 'choice',
            wrapperClass: 'border-warning-600 text-warning-800'
        });
    }

    // Info
    if ($(".control-info").length > 0) {
        $(".control-info").uniform({
            radioClass: 'choice',
            wrapperClass: 'border-info-600 text-info-800'
        });
    }

    // Custom color
    if ($(".control-custom").length > 0) {
        $(".control-custom").uniform({
            radioClass: 'choice',
            wrapperClass: 'border-indigo-600 text-indigo-800'
        });
    }



    // Bootstrap switch
    // ------------------------------

    if ($(".switch").length > 0) {
        $(".switch").bootstrapSwitch();
    }

});
