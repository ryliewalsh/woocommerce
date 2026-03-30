/**
 * Automatically add free heater when a greenhouse is added to cart
 */
add_action('woocommerce_add_to_cart', 'add_free_heater_with_greenhouse', 10, 6);

function add_free_heater_with_greenhouse($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    
    // Define your free heater product ID
    $free_heater_id = 12345; // REPLACE WITH ACTUAL HEATER PRODUCT ID
    
    // Define your greenhouse category ID or slug
    $greenhouse_category = 'greenhouses'; // OR use category ID: 123
    
    // Check if the added product is a greenhouse
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return;
    }
    
    // Check if product is in greenhouse category
    $is_greenhouse = has_term($greenhouse_category, 'product_cat', $product_id);
    
    if ($is_greenhouse) {
        // Check if heater is already in cart
        $heater_in_cart = false;
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] == $free_heater_id) {
                $heater_in_cart = true;
                break;
            }
        }
        
        // Add heater if not already in cart
        if (!$heater_in_cart) {
            // Add with custom data to mark as free gift
            WC()->cart->add_to_cart($free_heater_id, 1, 0, array(), array(
                'free_gift' => true,
                'free_gift_label' => 'FREE with Greenhouse'
            ));
        }
    }
}

/**
 * Set heater price to $0 when it's a free gift
 */
add_action('woocommerce_before_calculate_totals', 'set_free_heater_price', 10, 1);

function set_free_heater_price($cart) {
    
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }
    
    $free_heater_id = 12345; // REPLACE WITH ACTUAL HEATER PRODUCT ID
    
    // Check if there's a greenhouse in cart
    $has_greenhouse = false;
    $greenhouse_category = 'greenhouses';
    
    foreach ($cart->get_cart() as $cart_item) {
        if (has_term($greenhouse_category, 'product_cat', $cart_item['product_id'])) {
            $has_greenhouse = true;
            break;
        }
    }
    
    // Set heater to free if greenhouse is in cart
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $free_heater_id) {
            if ($has_greenhouse) {
                // Set price to 0
                $cart_item['data']->set_price(0);
            }
        }
    }
}

/**
 * Display "FREE" label in cart for the heater
 */
add_filter('woocommerce_cart_item_name', 'add_free_gift_label_to_cart', 10, 3);

function add_free_gift_label_to_cart($product_name, $cart_item, $cart_item_key) {
    
    $free_heater_id = 12345; // REPLACE WITH ACTUAL HEATER PRODUCT ID
    
    if ($cart_item['product_id'] == $free_heater_id) {
        
        // Check if greenhouse is in cart
        $has_greenhouse = false;
        $greenhouse_category = 'greenhouses';
        
        foreach (WC()->cart->get_cart() as $item) {
            if (has_term($greenhouse_category, 'product_cat', $item['product_id'])) {
                $has_greenhouse = true;
                break;
            }
        }
        
        if ($has_greenhouse) {
            $product_name .= ' <span class="free-gift-label" style="background: #d4773c; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; margin-left: 8px;">🎁 FREE GIFT</span>';
        }
    }
    
    return $product_name;
}

/**
 * Remove heater from cart if all greenhouses are removed
 */
add_action('woocommerce_cart_item_removed', 'remove_free_heater_if_no_greenhouse', 10, 2);

function remove_free_heater_if_no_greenhouse($cart_item_key, $cart) {
    
    $free_heater_id = 12345; // REPLACE WITH ACTUAL HEATER PRODUCT ID
    $greenhouse_category = 'greenhouses';
    
    // Check if any greenhouses remain in cart
    $has_greenhouse = false;
    foreach ($cart->get_cart() as $cart_item) {
        if (has_term($greenhouse_category, 'product_cat', $cart_item['product_id'])) {
            $has_greenhouse = true;
            break;
        }
    }
    
    // If no greenhouse in cart, remove the heater
    if (!$has_greenhouse) {
        foreach ($cart->get_cart() as $item_key => $cart_item) {
            if ($cart_item['product_id'] == $free_heater_id) {
                $cart->remove_cart_item($item_key);
                break;
            }
        }
    }
}

/**
 * Prevent the heater from being removed manually from cart when it's a free gift
 */
add_filter('woocommerce_cart_item_remove_link', 'disable_free_heater_removal', 10, 2);

function disable_free_heater_removal($remove_link, $cart_item_key) {
    
    $free_heater_id = 12345; // REPLACE WITH ACTUAL HEATER PRODUCT ID
    $greenhouse_category = 'greenhouses';
    
    $cart_item = WC()->cart->get_cart()[$cart_item_key];
    
    if ($cart_item['product_id'] == $free_heater_id) {
        
        // Check if greenhouse is in cart
        $has_greenhouse = false;
        foreach (WC()->cart->get_cart() as $item) {
            if (has_term($greenhouse_category, 'product_cat', $item['product_id'])) {
                $has_greenhouse = true;
                break;
            }
        }
        
        if ($has_greenhouse) {
            // Replace remove link with a message
            return '<span class="remove" style="color: #999; cursor: not-allowed;" title="Included free with greenhouse">✓</span>';
        }
    }
    
    return $remove_link;
}

/**
 * Prevent quantity changes for free heater
 */
add_filter('woocommerce_cart_item_quantity', 'lock_free_heater_quantity', 10, 3);

function lock_free_heater_quantity($product_quantity, $cart_item_key, $cart_item) {
    
    $free_heater_id = 12345; // REPLACE WITH ACTUAL HEATER PRODUCT ID
    $greenhouse_category = 'greenhouses';
    
    if ($cart_item['product_id'] == $free_heater_id) {
        
        // Check if greenhouse is in cart
        $has_greenhouse = false;
        foreach (WC()->cart->get_cart() as $item) {
            if (has_term($greenhouse_category, 'product_cat', $item['product_id'])) {
                $has_greenhouse = true;
                break;
            }
        }
        
        if ($has_greenhouse) {
            // Display quantity as text instead of input
            return '<span class="quantity">1</span>';
        }
    }
    
    return $product_quantity;
}
