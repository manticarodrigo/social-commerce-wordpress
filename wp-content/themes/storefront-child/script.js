(function($) {
    // Changing copyright text
    let title = 'Heyshopper';
    let $title = $('h1');
    
    if ($title.length == 1 && $('.single-product').length == 0) {
        title = $title.text();
        $title.hide();
    }
    
    // Top Bar
    $('.storefront-handheld-footer-bar ul').prepend(
        `<span class="woocommerce-products-header__title alpha page-title">${title}</span>`);

    // Quantity Buttons
    $('.qty-btn-add').click(function() {
        var $input = $('input.qty', $(this).parent());
        var $qtybtn = $(this).parents('.cart').find('.add_to_cart_button');
        var current_value = parseInt($input.val());
        $input.val(current_value + 1);
        $qtybtn.data('quantity', current_value + 1);
    });

    $('.qty-btn-sub').click(function() {
        var $input = $('input.qty', $(this).parent());
        var $qtybtn = $(this).parents('.cart').find('.add_to_cart_button');
        var current_value = parseInt($input.val());
        if (current_value > 0) {
            $input.val(current_value - 1);
            $qtybtn.data('quantity', current_value - 1);
        }
    });

    // Mark shop now
    $(document).ready(function(){
        // listen if someone clicks 'Buy Now' button
        $('.buy_now_button').click(function(){
            // set value to 1
            $('.is_buy_now_input', $(this).parent()).val('1');
            $(this).parents('form.cart').submit();
        });
    });

})(jQuery);

// Flip Card
(function($) {
    $('.card-flip').flip();
})(jQuery);