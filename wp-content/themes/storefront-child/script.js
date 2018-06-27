(function($) {
    // Changing copyright text
    let title = 'Medco';
    let $title = $('h1');
    
    if ($title.length == 1 && $('.single-product').length == 0) {
        title = $title.text();
        $title.hide();
    }
    
    $('.storefront-handheld-footer-bar .columns-3').prepend(
        `<span class="woocommerce-products-header__title alpha page-title">${title}</span>`);

    $('.quick_buy_container').each(function(index) {
        $(this).appendTo($(this).prev());
    });

})(jQuery);

// Flip Card
(function($) {
    $('.card-flip').flip();
})(jQuery);