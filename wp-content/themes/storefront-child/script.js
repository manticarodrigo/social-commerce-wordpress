(function($) {
    // adding title to top bar
    //$('.woocommerce-products-header__title').prependTo(
    //    '.storefront-handheld-footer-bar .columns-3');
    $('.storefront-handheld-footer-bar .columns-3').prepend(
        '<h1 class="woocommerce-products-header__title page-title">Medco</h1>');

    // Changing copyright text
    let title = 'Medco';
    // let title = $('.woocommerce-products-header__title').text();
    
    $('.site-info').text('© ' + title);

    $('.quick_buy_container').each(function(index) {
        $(this).appendTo($(this).prev());
    });

})(jQuery);
