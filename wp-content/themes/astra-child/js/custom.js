jQuery(function($) {
    $('.events-list').owlCarousel({
        loop: true,
        margin: 10,
        nav: true,
        dots: false,
        responsive: {
            0: {
                items: 3
            }
        }
    });
});
