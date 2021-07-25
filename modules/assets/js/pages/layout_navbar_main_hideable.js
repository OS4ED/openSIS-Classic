/* ------------------------------------------------------------------------------
*
*  # Page layout with hideable main navbar
*
*  Specific JS code additions for layout_navbar_main_hideable.html page
*
*  Version: 1.0
*  Latest update: Feb 25, 2016
*
* ---------------------------------------------------------------------------- */

$(function() {


    // Init headroom
    // ------------------------------

    // Hide navbar with Headroom.js library
    $(".navbar-fixed-top").headroom({
        classes: {
            pinned: "headroom-top-pinned",
            unpinned: "headroom-top-unpinned"
        },
        offset: $('.page-container').offset().top - $('.navbar-fixed-top').outerHeight(),

        // callback when unpinned, `this` is headroom object
        onUnpin: function() {
            $('.navbar .dropdown-menu').parent().removeClass('open');
        }
    });

});
