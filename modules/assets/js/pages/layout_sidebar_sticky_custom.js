/* ------------------------------------------------------------------------------
*
*  # Sticky sidebar with custom scrollbar
*
*  Specific JS code additions for layout_sidebar_sticky_custom.html page
*
*  Version: 1.1
*  Latest update: Feb 25, 2016
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


    // Nice scroll
    // ------------------------------

	// Setup
	function initScroll() {
	    $(".sidebar-fixed .sidebar-content").niceScroll({
	        mousescrollstep: 100,
	        cursorcolor: '#ccc',
	        cursorborder: '',
	        cursorwidth: 3,
	        hidecursordelay: 100,
	        autohidemode: 'scroll',
	        horizrailenabled: false,
	        preservenativescrolling: false,
	        railpadding: {
	        	right: 0.5,
	        	top: 1.5,
	        	bottom: 1.5
	        }
	    });
	}

	// Resize
	function resizeScroll() {
		$('.sidebar-fixed .sidebar-content').getNiceScroll().resize();
	}

	// Remove
	function removeScroll() {
		$(".sidebar-fixed .sidebar-content").getNiceScroll().remove();
		$(".sidebar-fixed .sidebar-content").removeAttr('style').removeAttr('tabindex');
	}


    // Resize sidebar on scroll
    // ------------------------------

	// Resize detached sidebar vertically when bottom reached
    function resizeDetached() {
		$(window).on('scroll', function() {
		  if ($(window).scrollTop() > $(document).height() - $(window).height() - 70) {
		    $('.sidebar-fixed').addClass('fixed-sidebar-space');
		    resizeScroll();
		  }
		  else {
		    $('.sidebar-fixed').removeClass('fixed-sidebar-space');
		    resizeScroll();
		  }
		});
    }


    // Affix detached sidebar
    // ------------------------------

    // Init nicescroll when sidebar affixed
	$('.sidebar-fixed').on('affix.bs.affix', function() {
		initScroll();
		resizeDetached();
		miniSidebar();
	});

    // Init mini sidebar functionality for main sidebar
    $('.sidebar-main .sidebar-fixed').on('affix.bs.affix', function() {
        miniSidebar();
    });

    // When effixed top, remove scrollbar and its data
    $('.sidebar-fixed').on('affix-top.bs.affix', function() {
		removeScroll();

        $(".sidebar-fixed .sidebar-content").removeAttr('style').removeAttr('tabindex');
    });

	// Attach BS affix component to the sidebar
	$('.sidebar-fixed').affix({
		offset: {
			top: $('.sidebar-fixed').offset().top - 20 // top offset - computed line height
		}
	});


    // Remove affix and scrollbar on mobile
    $(window).on('resize', function() {
        setTimeout(function() {            
            if($(window).width() <= 768) {

                // Remove nicescroll on mobiles
                removeScroll();

                // Remove affix on mobile
                $(window).off('.affix')
                $('.sidebar-fixed').removeData('affix').removeClass('affix affix-top affix-bottom');
            }
        }, 100);
    }).resize();

});
