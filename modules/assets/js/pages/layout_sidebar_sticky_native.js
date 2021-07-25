/* ------------------------------------------------------------------------------
*
*  # Sticky sidebar with native scrollbar
*
*  Specific JS code additions for layout_sidebar_sticky_native.html blank page
*
*  Version: 1.0
*  Latest update: Aug 1, 2015
*
* ---------------------------------------------------------------------------- */

$(function() {


    // Mini sidebar
    // -------------------------

    // Setup
    function miniSidebar() {
        if ($('body').hasClass('sidebar-xs')) {
            $('.sidebar-main .sidebar-fixed .sidebar-content').on('mouseenter', function () {
                if ($('body').hasClass('sidebar-xs')) {

                    // Expand fixed navbar
                    $('body').removeClass('sidebar-xs').addClass('sidebar-fixed-expanded');
                }
            }).on('mouseleave', function () {
                if ($('body').hasClass('sidebar-fixed-expanded')) {

                    // Collapse fixed navbar
                    $('body').removeClass('sidebar-fixed-expanded').addClass('sidebar-xs');
                }
            });
        }
    }

    // Toggle mini sidebar
    $('.sidebar-main-toggle').on('click', function (e) {

        // Initialize mini sidebar 
        miniSidebar();
    });


    // Resize sidebar on scroll
    // ------------------------------

    // Resize detached sidebar vertically when bottom reached
    function resizeDetached() {
        $(window).on('scroll load', function() {
          if ($(window).scrollTop() > $(document).height() - $(window).height() - 70) {
            $('.sidebar-fixed').addClass('fixed-sidebar-space');
          }
          else {
            $('.sidebar-fixed').removeClass('fixed-sidebar-space');
          }
        });
    }


    // Resize sidebar on scroll
    // ------------------------------

    // Initialize scrollbar when affixed
    $('.sidebar-fixed').on('affix.bs.affix', function() {
        resizeDetached();
    });

    // Init mini sidebar functionality for main sidebar
    $('.sidebar-main .sidebar-fixed').on('affix.bs.affix', function() {
        miniSidebar();
    });

    // Attach BS affix component to the sidebar
    $('.sidebar-fixed').affix({
        offset: {
            top: $('.sidebar-fixed').offset().top - 20 // top offset - computed line height
        }
    });


    // Remove affix on mobile
    $(window).on('resize', function() {
        setTimeout(function() {            
            if($(window).width() <= 768) {

                // Remove affix on mobile
                $(window).off('.affix')
                $('.sidebar-fixed').removeData('affix').removeClass('affix affix-top affix-bottom');
            }
        }, 100);
    }).resize();

});
