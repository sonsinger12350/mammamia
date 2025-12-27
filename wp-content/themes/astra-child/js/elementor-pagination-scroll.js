/**
 * Custom scroll behavior for Elementor AJAX Pagination
 * Scroll to top of page when pagination is clicked
 * 
 * File: wp-content/themes/astra-child/js/elementor-pagination-scroll.js
 */
(function($) {
    'use strict';

    // Function to scroll to top of page
    function scrollToTop() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    }

    // Wait for DOM ready
    $(document).ready(function() {
        // Listen for clicks on Elementor pagination links
        $(document).on('click', '.elementor-pagination a.page-numbers:not(.current)', function(e) {
            // Scroll to top when pagination is clicked
            scrollToTop();
        });
    });

})(jQuery);
