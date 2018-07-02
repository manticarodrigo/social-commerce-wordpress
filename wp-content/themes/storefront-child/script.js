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

    // Quantity form
    $('form.cart').on('change', 'input.qty', function() {
        $(this.form).find('[data-quantity]').data('quantity', this.value);
    });
    $(document.body).on('adding_to_cart', function() {
        $('a.added_to_cart').remove();
    });
    $(document.body).on('added_to_cart', function( data ) {
		$('.added_to_cart').after("<p class=\'confirm_add\'>Item Added</p>");
    });

    $('.qty-btn-add').click(function() {
        var current_value = parseInt($('input.qty').val());
        $('input.qty').val(current_value + 1);
    });

    $('.qty-btn-sub').click(function() {
        var current_value = parseInt($('input.qty').val());
        if (current_value > 0) {
            $('input.qty').val(current_value - 1);
        }
    });

})(jQuery);

// Flip Card
(function($) {
    $('.card-flip').flip();
})(jQuery);