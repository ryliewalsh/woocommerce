<?php
/**
 * Twenty Twenty-Five Child Theme Functions
 * 
 * @package WordPress
 * @subpackage Twenty_Twenty_Five_Child
 */

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Enqueue parent and child theme styles
 */
function twentytwentyfive_child_enqueue_styles() {
    // Parent theme stylesheet
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Child theme stylesheet (loads after parent)
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'twentytwentyfive_child_enqueue_styles');

/**
 * Enqueue Greenhouse Product Page Styles
 * Only loads for greenhouse category products
 */
function grandio_greenhouse_product_styles() {
    if (is_product()) {
        global $post;
        
        // Check if product is in greenhouse categories (matching your router logic)
        $categories = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs'));
        $greenhouses = array('element', 'elite', 'ascent', 'summit','extension-kits');
        
        $is_greenhouse = false;
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $cat_slug) {
                if (in_array($cat_slug, $greenhouses)) {
                    $is_greenhouse = true;
                    break;
                }
            }
        }
        
        // Load CSS only for greenhouse products
        if ($is_greenhouse) {
            wp_enqueue_style(
                'grandio-greenhouse-product',
                get_stylesheet_directory_uri() . '/css/product-page.css',
                array('child-style'), // Load after child theme styles
                '1.0.13' // Update version number when you modify CSS
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'grandio_greenhouse_product_styles', 20);
/**
 * Force Bread Finance to load on greenhouse products
 */
function grandio_load_bread_on_greenhouse() {
    if (is_product()) {
        global $post;
        
        // Check if product is in greenhouse categories
        $categories = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs'));
        $greenhouses = array('element', 'elite', 'ascent', 'summit', 'extension-kits');
        
        $is_greenhouse = false;
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $cat_slug) {
                if (in_array($cat_slug, $greenhouses)) {
                    $is_greenhouse = true;
                    break;
                }
            }
        }
        
        if ($is_greenhouse) {
            // Force load ALL WooCommerce payment gateway scripts
            add_action('wp_enqueue_scripts', function() {
                // Manually enqueue Bread's scripts
                $plugin_url = plugins_url('bread-finance');
                
                // Enqueue Bread's main scripts (adjust paths if needed)
                wp_enqueue_script('bread-ko', $plugin_url . '/assets/js/knockout-3.5.1.js', array('jquery'), null, true);
                wp_enqueue_script('bread-main', $plugin_url . '/assets/js/bread.js', array('jquery', 'bread-ko'), null, true);
                wp_enqueue_style('bread-css', $plugin_url . '/assets/css/bread.css');
                
            }, 99);
        }
    }
}
add_action('wp', 'grandio_load_bread_on_greenhouse');

/**
 * FORCE CLASSIC WOOCOMMERCE TEMPLATES
 * Disable block theme mode for WooCommerce
 */
// Tell WooCommerce this is NOT a block theme
add_filter('woocommerce_is_block_theme', '__return_false', 999);
add_filter('wc_current_theme_is_fse_theme', '__return_false', 999);

// Disable block templates for products
add_filter('woocommerce_has_block_template', function($has_block_template, $slug) {
    if ($slug === 'single-product') {
        return false;
    }
    return $has_block_template;
}, 999, 2);

// WooCommerce support
add_action('after_setup_theme', function() {
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width' => 600,
    ));
}, 999);

/**
 * SINGLE TEMPLATE ROUTER
 * Use different product templates based on category
 * This should be the ONLY template_include filter for products
 */
add_filter('template_include', 'custom_product_template_router', 999);
function custom_product_template_router($template) {
    if (is_product()) {
        global $post;
        
        // Check if product is in greenhouse categories
        $categories = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'slugs'));
        $greenhouses = array('element', 'elite', 'ascent', 'summit');
        
        $is_greenhouse = false;
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $cat_slug) {
                if (in_array($cat_slug, $greenhouses)) {
                    $is_greenhouse = true;
                    break;
                }
            }
        }
        
        // Determine which template to use
        if ($is_greenhouse) {
            // Try to load greenhouse template
            $greenhouse_template = get_stylesheet_directory() . '/woocommerce/single-product-greenhouse.php';
            if (file_exists($greenhouse_template)) {
                return $greenhouse_template;
            }
        }
        
        // Default: Load regular product template
        $default_template = get_stylesheet_directory() . '/woocommerce/single-product.php';
        if (file_exists($default_template)) {
            return $default_template;
        }
    }
    
    return $template;
}
// WooCommerce support
add_action('after_setup_theme', function() {
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width' => 600,
    ));
}, 999);

/**
 * AJAX Handler: Add Package to Cart
 */
add_action('wp_ajax_grandio_add_package_to_cart', 'grandio_add_package_to_cart');
add_action('wp_ajax_nopriv_grandio_add_package_to_cart', 'grandio_add_package_to_cart');

function grandio_add_package_to_cart() {
    // Verify nonce
    check_ajax_referer('grandio_package_nonce', 'nonce');
    
    // Get products and quantity
    $products = isset($_POST['products']) ? json_decode(stripslashes($_POST['products']), true) : array();
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (empty($products)) {
        wp_send_json_error(array('message' => 'No products selected'));
        return;
    }
    
    $added_products = array();
    $failed_products = array();
    
    // Add each product to cart
    foreach ($products as $product_data) {
        $product_id = intval($product_data['id']);
        $product = wc_get_product($product_id);
        
        if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
            $failed_products[] = array(
                'id' => $product_id,
                'name' => isset($product_data['name']) ? $product_data['name'] : 'Unknown',
                'reason' => 'Not available'
            );
            continue;
        }
        
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
        
        if ($cart_item_key) {
            $added_products[] = array(
                'id' => $product_id,
                'name' => $product->get_name(),
                'quantity' => $quantity
            );
        } else {
            $failed_products[] = array(
                'id' => $product_id,
                'name' => $product->get_name(),
                'reason' => 'Failed to add'
            );
        }
    }
    
    // Calculate cart totals
    WC()->cart->calculate_totals();
    
    // Prepare response
    $response = array(
        'added_count' => count($added_products),
        'failed_count' => count($failed_products),
        'added_products' => $added_products,
        'failed_products' => $failed_products,
        'checkout_url' => wc_get_checkout_url()
    );
    
    if (count($failed_products) > 0 && count($added_products) === 0) {
        wp_send_json_error($response);
    } else {
        wp_send_json_success($response);
    }
}

add_action( 'template_redirect', 'my_mobile_redirect' );
function my_mobile_redirect() {
    // Don't redirect bots/crawlers
    if ( is_front_page() && wp_is_mobile() && !is_bot() ) {
        nocache_headers();
        wp_redirect( home_url('/mobile_home'), 302 );
        exit;
    }
}

// Function to detect if visitor is a bot/crawler
function is_bot() {
    if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
        return false;
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $bots = array(
        'googlebot',
        'bingbot',
        'slurp',          // Yahoo
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'facebookexternalhit',
        'twitterbot',
        'rogerbot',       // Moz
        'linkedinbot',
        'embedly',
        'quora link preview',
        'showyoubot',
        'outbrain',
        'pinterest',
        'developers.google.com/+/web/snippet',
        'crawler',
        'spider',
        'bot',
        'crawling'
    );
    
    foreach ( $bots as $bot ) {
        if ( stripos( $user_agent, $bot ) !== false ) {
            return true;
        }
    }
    
    return false;
}




/**
 * Add Subcategory Dropdown to WooCommerce Category Pages
 * Add this to your theme's functions.php file
 */

// Add dropdown before the product loop
function add_subcategory_dropdown_filter() {
    if (!is_product_category()) {
        return;
    }
    
    $current_category = get_queried_object();
    
    $args = array(
        'taxonomy'   => 'product_cat',
        'parent'     => $current_category->term_id,
        'hide_empty' => true,
    );
    
    $subcategories = get_terms($args);
    
    if (empty($subcategories) || is_wp_error($subcategories)) {
        return;
    }
    
    $selected_cat = isset($_GET['filter_cat']) ? intval($_GET['filter_cat']) : $current_category->term_id;
    ?>
    <div class="subcategory-dropdown-filter">
        <label for="category-filter">Filter by Category:</label>
        <select id="category-filter">
            <option value="<?php echo $current_category->term_id; ?>" <?php selected($selected_cat, $current_category->term_id); ?>>
                All Products
            </option>
            <?php foreach ($subcategories as $subcat) : ?>
                <option value="<?php echo $subcat->term_id; ?>" <?php selected($selected_cat, $subcat->term_id); ?>>
                    <?php echo esc_html($subcat->name); ?> (<?php echo $subcat->count; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <style>
        .subcategory-dropdown-filter {
            margin: 20px 0 30px;
        }
        
        .subcategory-dropdown-filter label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 16px;
        }
        
        #category-filter {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            cursor: pointer;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        $('#category-filter').on('change', function() {
            var categoryId = $(this).val();
            var currentUrl = window.location.href.split('?')[0];
            window.location.href = currentUrl + '?filter_cat=' + categoryId;
        });
    });
    </script>
    <?php
}
add_action('woocommerce_before_shop_loop', 'add_subcategory_dropdown_filter', 15);


// Filter the product query based on selected category
function filter_category_products_query($q) {
    if ($q->is_main_query() && is_product_category() && isset($_GET['filter_cat'])) {
        $filter_cat = intval($_GET['filter_cat']);
        $current_category = get_queried_object();
        
        // Only filter if different from current category
        if ($filter_cat != $current_category->term_id) {
            // Get child categories
            $cat_ids = array($filter_cat);
            $child_cats = get_term_children($filter_cat, 'product_cat');
            if (!empty($child_cats) && is_array($child_cats)) {
                $cat_ids = array_merge($cat_ids, $child_cats);
            }
            
            // Modify the tax query
            $tax_query = $q->get('tax_query');
            if (!is_array($tax_query)) {
                $tax_query = array();
            }
            
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_ids,
                'operator' => 'IN',
            );
            
            $q->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'filter_category_products_query', 20);


// Hide subcategory tiles from displaying in product grid
function hide_subcategories_in_product_loop() {
    if (is_product_category()) {
        return false;
    }
}
add_filter('woocommerce_product_subcategories_hide_empty', 'hide_subcategories_in_product_loop', 999);
function add_livechat_widget() {
    // Use WordPress mobile detection if available
    if (function_exists('wp_is_mobile')) {
        $is_mobile = wp_is_mobile();
    } else {
        // Fallback mobile detection
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent);
    }
    
    // Only load LiveChat if NOT mobile
    if (!$is_mobile) {
        ?>
        <!-- Start of LiveChat (www.livechat.com) code -->
        <script>
            window.__lc = window.__lc || {};
            window.__lc.license = 1766371;
            window.__lc.integration_name = "manual_channels";
            window.__lc.product_name = "livechat";
            ;(function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
        </script>
        <noscript><a href="https://www.livechat.com/chat-with/1766371/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>
        <!-- End of LiveChat code -->
        <?php
    }
}
// Add to the footer of every page
add_action('wp_footer', 'add_livechat_widget');

// Make store notice appear for each new session
add_action('init', 'reset_store_notice_per_session');
function reset_store_notice_per_session() {
    if (!session_id()) {
        session_start();
    }
    
    // If it's a new session, clear the notice dismissal
    if (!isset($_SESSION['store_notice_shown'])) {
        // Clear the dismiss cookie
        setcookie('store_notice[notice_id]', '', time() - 3600, '/');
        $_SESSION['store_notice_shown'] = true;
    }
}
// Add short description to shop/category loops
//add_action('woocommerce_after_shop_loop_item_title', 'add_short_description_to_loop', 15);

function add_short_description_to_loop() {
    global $product;
    
    $short_desc = $product->get_short_description();
    if ($short_desc) {
        echo '<div class="loop-product-excerpt" style="color: red;">' . wp_trim_words($short_desc, 15) . '</div>';
    }
}

/**
 * Add these functions to your theme's functions.php file
 * Complete secure product management system with bulk update functionality
 */

// AJAX handler to get WooCommerce products
function handle_get_wc_products() {
    // Security check
    if (!wp_verify_nonce($_POST['_wpnonce'], 'wc_products_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }
    
    try {
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        
        $products = wc_get_products(array(
            'limit' => $per_page,
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $product_data = array();
        
        foreach ($products as $product) {
            // Get product categories
            $categories = array();
            $category_terms = get_the_terms($product->get_id(), 'product_cat');
            if ($category_terms && !is_wp_error($category_terms)) {
                foreach ($category_terms as $term) {
                    $categories[] = array('name' => $term->name);
                }
            }
            
            // Get product images
            $images = array();
            $image_ids = $product->get_gallery_image_ids();
            $featured_image = $product->get_image_id();
            
            // Add featured image first
            if ($featured_image) {
                $image_url = wp_get_attachment_url($featured_image);
                if ($image_url) {
                    $images[] = array('src' => $image_url);
                }
            }
            
            // Add gallery images
            foreach ($image_ids as $image_id) {
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    $images[] = array('src' => $image_url);
                }
            }
            
            $product_data[] = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'sku' => $product->get_sku() ?: '',
                'price' => $product->get_price() ?: '0',
                'stock_quantity' => $product->get_stock_quantity(),
                'stock_status' => $product->get_stock_status(),
                'date_modified' => $product->get_date_modified()->format('c'),
                'categories' => $categories,
                'images' => $images
            );
        }
        
        wp_send_json_success($product_data);
        
    } catch (Exception $e) {
        wp_send_json_error('Error loading products: ' . $e->getMessage());
    }
}

// Hook for logged-in users only (admin required)
add_action('wp_ajax_get_wc_products', 'handle_get_wc_products');

// AJAX handler to delete a single product
function handle_delete_wc_product() {
    // Security check
    if (!wp_verify_nonce($_POST['_wpnonce'], 'wc_products_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }
    
    $product_id = intval($_POST['product_id']);
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
    }
    
    try {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            wp_send_json_error('Product not found');
        }
        
        // Move to trash instead of permanent delete for safety
        $result = wp_trash_post($product_id);
        
        if ($result) {
            wp_send_json_success('Product deleted successfully');
        } else {
            wp_send_json_error('Failed to delete product');
        }
        
    } catch (Exception $e) {
        wp_send_json_error('Error deleting product: ' . $e->getMessage());
    }
}

add_action('wp_ajax_delete_wc_product', 'handle_delete_wc_product');

// AJAX handler for bulk update
function handle_bulk_update_wc_products() {
    // Security check
    if (!wp_verify_nonce($_POST['_wpnonce'], 'wc_products_nonce') || !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }
    
    $product_ids = json_decode(stripslashes($_POST['product_ids']), true);
    $update_data = json_decode(stripslashes($_POST['update_data']), true);
    
    if (!is_array($product_ids) || empty($product_ids)) {
        wp_send_json_error('Invalid product IDs');
    }
    
    if (!is_array($update_data) || empty($update_data)) {
        wp_send_json_error('No update data provided');
    }
    
    try {
        $updated_count = 0;
        
        foreach ($product_ids as $product_id) {
            $product_id = intval($product_id);
            $product = wc_get_product($product_id);
            
            if (!$product) {
                continue;
            }
            
            // Update various product fields based on what's provided
            if (isset($update_data['price']) && $update_data['price'] !== '') {
                $product->set_regular_price($update_data['price']);
            }
            
            if (isset($update_data['sale_price']) && $update_data['sale_price'] !== '') {
                $product->set_sale_price($update_data['sale_price']);
            }
            
            if (isset($update_data['stock_status'])) {
                $product->set_stock_status($update_data['stock_status']);
            }
            
            if (isset($update_data['stock_quantity']) && $update_data['stock_quantity'] !== '') {
                $product->set_stock_quantity(intval($update_data['stock_quantity']));
                $product->set_manage_stock(true);
            }
            
            if (isset($update_data['category_ids']) && is_array($update_data['category_ids'])) {
                $product->set_category_ids($update_data['category_ids']);
            }
            
            if (isset($update_data['status'])) {
                $product->set_status($update_data['status']);
            }
            
            if (isset($update_data['featured'])) {
                $product->set_featured($update_data['featured']);
            }
            
            // Save the product
            $product->save();
            $updated_count++;
        }
        
        wp_send_json_success("Successfully updated {$updated_count} products");
        
    } catch (Exception $e) {
        wp_send_json_error('Error updating products: ' . $e->getMessage());
    }
}
// Exempt bots from Cookiebot - MUST RUN FIRST
add_action('wp_head', 'exempt_bots_from_cookiebot', -999); // ← Changed to -999 (very early)
function exempt_bots_from_cookiebot() {
    // Check if it's a bot
    if (is_bot_crawler()) {
        ?>
        <script data-cookieconsent="ignore">
        // Override consent for bots BEFORE Cookiebot loads
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('consent', 'default', {
            'ad_storage': 'denied',
            'ad_user_data': 'denied', 
            'ad_personalization': 'denied',
            'analytics_storage': 'granted', // ← ALLOW for bots
            'functionality_storage': 'granted',
            'personalization_storage': 'denied',
            'security_storage': 'granted'
        });
        console.log('Bot detected - GA4 consent granted');
        </script>
        <?php
        // ALSO remove Cookiebot script for bots
        remove_action('wp_head', 'cookiebot_output_cookiebot_declaration', 1);
    }
}

// Bot detection function
function is_bot_crawler() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $bots = array(
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'facebookexternalhit',
        'twitterbot',
        'crawler',
        'spider',
        'bot'
    );
    
    foreach ($bots as $bot) {
        if (strpos($user_agent, $bot) !== false) {
            return true;
        }
    }
    
    return false;
}

add_action('wp_ajax_bulk_update_wc_products', 'handle_bulk_update_wc_products');

// Enqueue scripts and localize data
function enqueue_product_management_scripts() {
    // Only enqueue on the product management page
    if (is_page_template('page-product-management.php') || 
        (is_page() && get_post_field('post_name') === 'product-management')) {
        
        wp_enqueue_script('jquery');
        
        // Localize script with AJAX data
        wp_localize_script('jquery', 'wpAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_products_nonce'),
            'admin_url' => admin_url()
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_product_management_scripts');

// Add custom admin menu item (optional - creates a button in WordPress admin)
function add_product_management_menu() {
    add_menu_page(
        'Product Management',           // Page title
        'Product Manager',              // Menu title
        'manage_options',               // Capability required
        'product-management-dashboard', // Menu slug
        'product_management_page',      // Function to display the page
        'dashicons-cart',               // Icon
        30                              // Position
    );
}
add_action('admin_menu', 'add_product_management_menu');

// Function to display the admin page (redirects to front-end page)
function product_management_page() {
    // Get the URL of the product management page
    $page = get_page_by_path('product-management');
    $page_url = $page ? get_permalink($page->ID) : home_url('/product-management/');
    
    echo '<div class="wrap">';
    echo '<h1>Product Management Dashboard</h1>';
    echo '<p>The product management dashboard is available as a secure front-end interface.</p>';
    echo '<a href="' . esc_url($page_url) . '" class="button button-primary" target="_blank">Open Product Manager</a>';
    echo '<p><em>Note: Only administrators can access the product management dashboard.</em></p>';
    echo '</div>';
}

// Optional: Add capability check for WooCommerce
function check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Product Management Dashboard requires WooCommerce to be active.</p></div>';
        });
    }
}
add_action('admin_init', 'check_woocommerce_active');
// Add public endpoint for nonce generation (for HTML version)
function get_product_management_nonce() {
    // Only allow if user is admin and logged in
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    wp_send_json_success(array(
        'nonce' => wp_create_nonce('wc_products_nonce'),
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_ajax_get_pm_nonce', 'get_product_management_nonce');

// Allow nopriv access for the nonce endpoint only
add_action('wp_ajax_nopriv_get_pm_nonce', function() {
    wp_send_json_error('Please log in as administrator');
});

//Custom Pricing for single item page  
function custom_starting_price_shortcode($atts) {
    global $product;
    
    if (!$product) {
        return '';
    }
    $sku = $product->get_sku();


    $min_price = '';
    
    if ($product->is_type('variable')) {
        $min_price = wc_price($product->get_variation_price('min'));
    } elseif ($product->is_type('bundle')) {
        $min_price = wc_price($product->get_bundle_price('min'));
    } else {
        $min_price = wc_price($product->get_price());
	
    }
	// Check SKU AND category
if (  ! has_term( 'greenhouse-kit', 'product_cat', $product->get_id())  ) {
    return '<div class="custom-price-display"> '. $min_price .' </div>'  ; // Exit early if not matching
}
    
    return '<div class="custom-price-display">Starting at ' . $min_price . '</div>';
}

add_shortcode('starting_price', 'custom_starting_price_shortcode');

//custom pricing for category pages 
// For WooCommerce Blocks - properly fetch product data
// Automatically modify price display on category/shop pages
add_filter('woocommerce_get_price_html', 'custom_category_price_format', 20, 2);
function custom_category_price_format($price, $product) {
    // Only run on shop/category pages, not single product pages
    if (is_shop() || is_product_category() || is_product_tag()) {
        if ($product->is_type('variable')) {
            $min_price = $product->get_variation_price('min');
            return 'From ' . wc_price($min_price);
        } elseif ($product->is_type('bundle')) {
            $min_price = $product->get_bundle_price('min');
            return 'Starting at ' . wc_price($min_price);
        }
    }
    return $price;
}

function add_countdown_banner(){
    ?>
    <div class="sale-banner" id="saleBanner">
        <div class="sale-banner-content">
            <div class="sale-text">
                <span class="sale-text"> LAST CHANCE OCT. DISCOUNTS </span>
                Sale ends in: <span id="countdown-display">Loading...</span> | 
                Save $200-$1,250 off ALL greenhouses!
            </div>
        </div>
        <button class="close-banner" onclick="closeBanner()" aria-label="Close banner">
            ×
        </button>
    </div>
    <script>
    // Add this to your JavaScript
    (function () {
        const countDownDate = new Date("2025-11-01T00:00:00").getTime();
        const x = setInterval(function () {
            const countdownElement = document.getElementById("countdown-display");
            if (!countdownElement) return;
            
            const now = new Date().getTime();
            const distance = countDownDate - now;
            
            if (distance <= 0) {
                clearInterval(x);
                countdownElement.innerHTML = "Sale expired!";
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdownElement.innerHTML = 
                String(days).padStart(2, '0') + "d " +
                String(hours).padStart(2, '0') + "h " +
                String(minutes).padStart(2, '0') + "m " +
                String(seconds).padStart(2, '0') + "s";
        }, 1000);
    })();
    
    function closeBanner() {
        const banner = document.getElementById('saleBanner');
        banner.classList.add('hidden');
        
        // Optional: Remember the user's choice with a cookie
        // This prevents the banner from showing again during this session
        sessionStorage.setItem('saleBannerClosed', 'true');
    }
    // Optional: Check if user previously closed the banner
    document.addEventListener('DOMContentLoaded', function() {
        if (sessionStorage.getItem('saleBannerClosed') === 'true') {
            document.getElementById('saleBanner').classList.add('hidden');
        }
    });
    </script>
    <?php
}
//add_action('wp_body_open', 'add_countdown_banner');


// Sale Banner - Fully Inline CSS Version
function add_sale_banner() {
    ?>
    <div id="saleBanner" style="position: fixed; top: 0; left: 0; width: 100%; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 15px 20px; z-index: 99998; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; position: relative;">
            <div style="text-align: center; flex: 1;">
                <span style="font-size: 22px; font-weight: 900; display: inline-block; margin-right: 15px; text-transform: uppercase; letter-spacing: 0.5px; word-spacing: 8px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
					 Black Friday Sale LIVE NOW - Use code FREEUPGRADE to recieve a premium kit for basic price!
                </span>
                <button id="emailPopupBtn" style="background: #fff; color: #e74c3c; border: 3px solid #fff; padding: 12px 30px; border-radius: 8px; font-weight: 900; cursor: pointer; font-size: 16px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);" onmouseover="this.style.background='#ffe5e5'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='#fff'; this.style.transform='scale(1)'">
                     SHOP DEAL NOW
                </button>
            </div>
            <button onclick="closeBanner()" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); border: none; color: white; font-size: 28px; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0; font-weight: bold;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ×
            </button>
        </div>
    </div>

  <!-- Email Popup -->
<div id="emailPopup" 
     style="
        display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
        background:rgba(0,0,0,0.7); z-index:99999; 
        align-items:center; justify-content:center;
     ">

    <div style="
        background:white; padding:40px; border-radius:10px; 
        max-width:500px; width:90%; position:relative; 
        text-align:center; box-shadow:0 5px 30px rgba(0,0,0,0.3);
    ">

        <!-- Close X -->
        <span class="close-popup"
              style="position:absolute; top:15px; right:20px; font-size:30px; cursor:pointer; color:#999; line-height:1;"
              onmouseover="this.style.color='#333'"
              onmouseout="this.style.color='#999'">
            ×
        </span>

        <!-- Headline -->
        <h2 style="margin:0 0 10px 0; color:#333; font-size:28px;">
            Buy A Greenhouse Get a FREE Accessory Package!
        </h2>

        <p style="margin:0 0 25px 0; color:#666; font-size:16px;">
            EXCLUSIVE Savings $220 to $1,310 - Receive Pre-Black Friday Code NOW! Valid while supplies last.
        </p>

        <!-- Email Form -->
        <form id="emailForm">
            <input 
                type="email" 
                id="emailInput" 
                placeholder="Enter your email"
                required
                style="
                    width:100%; padding:14px; margin-bottom:15px; 
                    border:2px solid #ddd; border-radius:5px; 
                    font-size:16px; box-sizing:border-box;
                "
                onfocus="this.style.borderColor='#4CAF50'"
                onblur="this.style.borderColor='#ddd'"
            />

            <button type="submit"
                    style="
                        width:100%; padding:14px; background:#4CAF50; 
                        color:white; border:none; border-radius:5px; 
                        font-size:16px; cursor:pointer; font-weight:bold;
                        transition:background 0.3s ease;
                    "
                    onmouseover="this.style.background='#45a049'"
                    onmouseout="this.style.background='#4CAF50'">
                Get Your Code
            </button>
        </form>

        <!-- SUCCESS -->
        <div id="successMessage" style="display:none; margin-top:20px;">

            <p style="color:#4CAF50; font-weight:bold; font-size:18px; margin-bottom:20px;">
                Your Code: FREEUPGRADE
            </p>

            <p style="color:#4CAF50; font-weight:bold; font-size:12px; margin-bottom:20px;">
                Apply after adding your choice of basic Greenhouse kit
            </p>

            <button id="closeAfterSuccess"
                    style="
                        width:100%; padding:14px; background:#4CAF50; 
                        color:white; border:none; border-radius:5px; 
                        font-size:16px; cursor:pointer; font-weight:bold;
                    "
                    onmouseover="this.style.background='#45a049'"
                    onmouseout="this.style.background='#4CAF50'">
                Close
            </button>
        </div>

        <!-- ERROR -->
        <div id="errorMessage" style="display:none; color:red; margin-top:15px;"></div>

    </div>
</div>


    <script>
    function closeBanner() {
        var banner = document.getElementById('saleBanner');
        if (banner) {
            banner.style.transform = 'translateY(-100%)';
            setTimeout(function() {
                banner.style.display = 'none';
            }, 300);
            sessionStorage.setItem('saleBannerClosed', 'true');
        }
    }

    (function() {
        // Check if previously closed
        if (sessionStorage.getItem('saleBannerClosed') === 'true') {
            var banner = document.getElementById('saleBanner');
            if (banner) {
                banner.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var popup = document.getElementById('emailPopup');
            var btn = document.getElementById('emailPopupBtn');
            var closeBtn = document.querySelector('.close-popup');

            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    popup.style.display = 'flex';
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    popup.style.display = 'none';
                    resetForm();
                });
            }

            popup.addEventListener('click', function(e) {
                if (e.target === popup) {
                    popup.style.display = 'none';
                    resetForm();
                }
            });

            function resetForm() {
                document.getElementById('emailForm').style.display = 'block';
                document.getElementById('successMessage').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'none';
                document.getElementById('emailForm').reset();
            }

            var form = document.getElementById('emailForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    var email = document.getElementById('emailInput').value;
                    var submitBtn = this.querySelector('button[type="submit"]');
                    
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Subscribing...';
                    
                    var formData = new FormData();
                    formData.append('action', 'subscribe_banner_email');
                    formData.append('email', email);
                    formData.append('nonce', '<?php echo wp_create_nonce('banner_subscribe_nonce'); ?>');
                    
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            document.getElementById('emailForm').style.display = 'none';
                            document.getElementById('successMessage').style.display = 'block';
                        } else {
                            document.getElementById('errorMessage').textContent = '⚠ Error: ' + (data.data || 'Subscription failed');
                            document.getElementById('errorMessage').style.display = 'block';
                        }
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Subscribe';
                    })
                    .catch(function(error) {
                        document.getElementById('errorMessage').textContent = '⚠ Error: ' + error.message;
                        document.getElementById('errorMessage').style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Subscribe';
                    });
                });
            }

            var closeSuccess = document.getElementById('closeAfterSuccess');
            if (closeSuccess) {
                closeSuccess.addEventListener('click', function() {
                    popup.style.display = 'none';
                    resetForm();
                });
            }
        });
    })();
    </script>

    <!-- Mobile responsive styles -->
    <style>
    @media (max-width: 768px) {
        #saleBanner > div {
            padding-right: 40px !important;
        }
        #saleBanner span {
            font-size: 13px !important;
            display: block !important;
            margin-right: 0 !important;
            margin-bottom: 8px !important;
        }
        #emailPopupBtn {
            width: 100% !important;
            margin-top: 8px !important;
        }
        .email-popup-content {
            padding: 30px 20px !important;
        }
    }
    </style>
    <?php
}
//add_action('wp_footer', 'add_sale_banner', 1);

// AJAX handler for banner email subscription
//add_action('wp_ajax_subscribe_banner_email', 'handle_banner_email_subscription');
//add_action('wp_ajax_nopriv_subscribe_banner_email', 'handle_banner_email_subscription');

function handle_banner_email_subscription() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'banner_subscribe_nonce')) {
        wp_send_json_error('Invalid security token');
        return;
    }
    
    $email = sanitize_email($_POST['email'] ?? '');
    
    if (empty($email) || !is_email($email)) {
        wp_send_json_error('Invalid email address');
        return;
    }
    
    // Klaviyo Configuration - UPDATE THESE
    $klaviyo_api_key = 'pk_14200bdef2c06e18463b3a1df4bea24ce9'; // Your private API key
    $klaviyo_list_id = 'VNw3jZ'; // Your banner list ID
    
    // Subscribe to Klaviyo using Bulk Import API
    $result = subscribe_to_klaviyo_banner($email, $klaviyo_api_key, $klaviyo_list_id);
    
    if ($result['success']) {
        wp_send_json_success('Subscribed successfully');
    } else {
        wp_send_json_error($result['message']);
    }
}

function subscribe_to_klaviyo_banner($email, $api_key, $list_id) {
    try {
        $profileAttributes = [
            'email' => $email,
            'properties' => [
                'Source' => 'Sale Banner Popup',
                'Banner Campaign' => 'Pre-Black Friday Sale',
                'Clicked Banner' => true,
                'Sign Up Date' => date('Y-m-d H:i:s')
            ]
        ];
        
        $payload = [
            'data' => [
                'type' => 'profile-bulk-import-job',
                'attributes' => [
                    'profiles' => [
                        'data' => [
                            [
                                'type' => 'profile',
                                'attributes' => $profileAttributes
                            ]
                        ]
                    ]
                ],
                'relationships' => [
                    'lists' => [
                        'data' => [
                            [
                                'type' => 'list',
                                'id' => $list_id
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $url = 'https://a.klaviyo.com/api/profile-bulk-import-jobs/';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Klaviyo-API-Key ' . $api_key,
            'Content-Type: application/json',
            'revision: 2024-10-15'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        error_log("Klaviyo Banner Subscribe - HTTP: $httpCode - Response: $response");
        
        if (in_array($httpCode, [200, 201, 202])) {
            return ['success' => true, 'message' => 'Subscribed successfully'];
        } else {
            return ['success' => false, 'message' => "HTTP $httpCode: $response"];
        }
        
    } catch (Exception $e) {
        error_log("Klaviyo Banner Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

//add_action('wp_body_open', 'add_sale_banner');
function add_back_to_top_button_single_page() {
    
   
    
  
    ?>
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top" title="Back to top">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>

    <style>
        /* Back to Top Button Styles */
        .back-to-top {
            position: fixed;
            top: 10%;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2e7d5a 0%, #4a9b6e 100%) !important; /* Green background */
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(46, 125, 90, 0.4) !important;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 9999;
            padding: 0;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            
            transform: translateY(-3px);
        
        }

        .back-to-top:active {
            transform: translateY(-1px);
        }

        .back-to-top svg {
            width: 24px;
            height: 24px;
        }

        /* Mobile optimization */
        @media (max-width: 768px) {
            .back-to-top {
                width: 45px;
                height: 45px;
                bottom: 20px;
                right: 20px;
            }

            .back-to-top svg {
                width: 20px;
                height: 20px;
            }
        }
    </style>

    <script>
        (function() {
            const backToTopButton = document.getElementById('backToTop');
            
            if (!backToTopButton) return;

            function toggleBackToTop() {
                if (window.scrollY > 300) {
                    backToTopButton.classList.add('visible');
                } else {
                    backToTopButton.classList.remove('visible');
                }
            }

            function scrollToTop(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (scrollTimeout) {
                    window.cancelAnimationFrame(scrollTimeout);
                }
                scrollTimeout = window.requestAnimationFrame(toggleBackToTop);
            });
            
            backToTopButton.addEventListener('click', scrollToTop);
            toggleBackToTop();
        })();
    </script>
    <?php
}
add_action('wp_footer', 'add_back_to_top_button_single_page');


// Display savings on bundle products with variable items
// Display savings on shop/category pages
// Temporary debug - will show on dashboard
add_action('wp_dashboard_setup', 'temp_shipstation_debug');

function temp_shipstation_debug() {
    wp_add_dashboard_widget('temp_debug', 'ShipStation Debug', function() {
        echo '<pre style="font-size: 11px; max-height: 200px; overflow: scroll;">';
        
        // Check recent orders
        $orders = wc_get_orders(array('limit' => 5));
        foreach ($orders as $order) {
            echo "Order #" . $order->get_order_number() . "\n";
            echo "Status: " . $order->get_status() . "\n";
            echo "ShipStation Meta: " . get_post_meta($order->get_id(), '_shipstation_exported', true) . "\n";
            echo "All Meta Keys: " . implode(', ', array_keys(get_post_meta($order->get_id()))) . "\n";
            echo "---\n";
        }
        
        echo '</pre>';
    });
}
// Show savings ABOVE price on category/shop pages only
add_filter('woocommerce_get_price_html', 'custom_category_price_with_savings', 20, 2);
function custom_category_price_with_savings($price, $product) {
    // Only run on shop/category pages, not single product pages
    if (is_shop() || is_product_category() || is_product_tag()) {
        
        // Variable products
        if ($product->is_type('variable')) {
            $min_regular = $product->get_variation_regular_price('min');
            $min_sale = $product->get_variation_sale_price('min');
            
            if ($min_sale && $min_sale < $min_regular) {
                $savings = $min_regular - $min_sale;
                return '<div class="category-price-display">' .
                       '<span class="savings-badge">Save $' . number_format($savings, 0) . '</span>' .
                       '<div class="price-text">From ' . wc_price($min_sale) . '</div>' .
                       '</div>';
            }
            return 'From ' . wc_price($product->get_variation_price('min'));
        }
        
        // Bundle products
        if ($product->is_type('bundle')) {
            $min_regular = $product->get_bundle_regular_price('min');
            $min_sale = $product->get_bundle_price('min');
            
            if ($min_sale && $min_sale < $min_regular) {
                $savings = $min_regular - $min_sale;
                return '<div class="category-price-display">' .
                       '<span class="savings-badge">Save $' . number_format($savings, 0) . '</span>' .
                       '<div class="price-text">Starting at ' . wc_price($min_sale) . '</div>' .
                       '</div>';
            }
            return 'Starting at ' . wc_price($min_sale);
        }
        
        // Simple products on sale
        if ($product->is_on_sale()) {
            $regular_price = floatval($product->get_regular_price());
            $sale_price = floatval($product->get_sale_price());
            $savings = $regular_price - $sale_price;
            
            return '<div class="category-price-display">' .
                   '<span class="savings-badge">Save $' . number_format($savings, 0) . '</span>' .
                   '<div class="price-text">' . wc_price($sale_price) . '</div>' .
                   '</div>';
        }
    }
    
    return $price;
}

/**
 * Add UET Purchase Conversion Tracking (WooCommerce) - IMPROVED
 */
function add_uet_woocommerce_conversion($order_id) {
    if (!$order_id) return;
    
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    // Only track completed/processing orders (NOT pending)
    if (!in_array($order->get_status(), ['completed', 'processing'])) {
        return;
    }
    
    // Prevent duplicate tracking on page refresh
    if ($order->get_meta('_uet_tracked')) return;
    
    $order_total = $order->get_total();
    $currency = $order->get_currency();
    $order_id_num = $order->get_id();
    ?>
    <script>
    // Wait for UET to be ready before sending conversion
    (function() {
        var maxAttempts = 30; // Try for 3 seconds
        var attempts = 0;
        
        function sendUETConversion() {
            if (typeof window.uetq !== 'undefined' && window.uetq.push) {
                window.uetq = window.uetq || [];
                window.uetq.push('event', 'purchase', {
                    'revenue_value': <?php echo esc_js($order_total); ?>,
                    'currency': '<?php echo esc_js($currency); ?>',
                    'transaction_id': '<?php echo esc_js($order_id_num); ?>'
                });
                console.log('✅ UET Purchase Conversion Fired', {
                    order: '<?php echo $order_id_num; ?>',
                    revenue: <?php echo $order_total; ?>,
                    currency: '<?php echo $currency; ?>',
                    transaction_id: '<?php echo $order_id_num; ?>'
                });
                return true;
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(sendUETConversion, 100);
            } else {
                console.error('❌ UET not loaded after ' + (maxAttempts * 100) + 'ms');
            }
        }
        
        // Start trying immediately, but with retry logic
        if (document.readyState === 'complete') {
            sendUETConversion();
        } else {
            window.addEventListener('load', sendUETConversion);
        }
    })();
    </script>
    <?php
    
    // Mark as tracked
    $order->update_meta_data('_uet_tracked', true);
    $order->save();
}
add_action('woocommerce_thankyou', 'add_uet_woocommerce_conversion', 10, 1);

/**
 * Fix Product Bundle Schema - Use Correct Bundle Price Method
 * WooCommerce Brands breaks bundle prices by using get_price() which returns 0
 * We need to use get_bundle_price() instead
 */
add_filter('woocommerce_structured_data_product', 'fix_bundle_product_schema', 30, 2);
function fix_bundle_product_schema($markup, $product) {
    
    // Only process bundle products
    if (!$product->is_type('bundle')) {
        return $markup;
    }
    
    // Get bundle prices using the correct methods
    $price = $product->get_bundle_price();
    $regular_price = $product->get_bundle_regular_price();
    
    // If no price found, return original markup
    if (!$price || $price <= 0) {
        error_log('Bundle Product ID ' . $product->get_id() . ' has no valid price');
        return $markup;
    }
    
    // Fix the offers array
    if (isset($markup['offers']) && is_array($markup['offers'])) {
        foreach ($markup['offers'] as $key => $offer) {
            
            // Fix UnitPriceSpecification format (what's currently breaking)
            if (isset($offer['priceSpecification']) && is_array($offer['priceSpecification'])) {
                foreach ($offer['priceSpecification'] as $spec_key => $spec) {
                    $markup['offers'][$key]['priceSpecification'][$spec_key]['price'] = number_format($price, 2, '.', '');
                }
            }
            
            // Add direct price property (Google's preferred format)
            $markup['offers'][$key]['price'] = number_format($price, 2, '.', '');
            $markup['offers'][$key]['priceCurrency'] = get_woocommerce_currency();
            
            // If there's a sale (regular price differs from sale price)
            if ($regular_price && $regular_price > $price) {
                // Keep the priceValidUntil if it exists
                if (!isset($markup['offers'][$key]['priceValidUntil'])) {
                    $markup['offers'][$key]['priceValidUntil'] = date('Y-12-31', strtotime('+1 year'));
                }
            }
        }
    }
    
    return $markup;
}


/**
 * Automatically add matching accessory package based on greenhouse SKU
 * PRODUCTION VERSION - Works for all customers
 * Example: ascent-88 or ascent-88-p → adds ascent-88-ACS
 */

// Add accessory package when coupon is applied
add_action('woocommerce_applied_coupon', 'add_matching_accessory_package');

function add_matching_accessory_package($coupon_code) {
    // Your promotion coupon codes
    $promo_codes = array('FREEUPGRADE','FREEKIT');
    
    if (!in_array(strtoupper($coupon_code), $promo_codes)) {
        return;
    }
    
    $accessory_skus = array();
    
    // Loop through cart to find greenhouses and determine which accessory packages to add
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        $sku = $product->get_sku();
        
        if (empty($sku)) {
            continue;
        }
        
        // Determine base SKU and matching accessory SKU
        $accessory_sku = get_matching_accessory_sku($sku);
        
        if ($accessory_sku && !in_array($accessory_sku, $accessory_skus)) {
            $accessory_skus[] = $accessory_sku;
        }
    }
    
    // Add each matching accessory package
    foreach ($accessory_skus as $accessory_sku) {
        add_accessory_by_sku($accessory_sku);
    }
    /*
    if (!empty($accessory_skus)) {
        $count = count($accessory_skus);
        $message = $count === 1 
            ? '🎁 FREE Premium Accessory Package added to your cart!' 
            : "🎁 {$count} Free Premium Accessory Packages added to your cart!";
        wc_add_notice($message, 'success');
    }
	*/
}

// Determine matching accessory SKU based on greenhouse SKU
function get_matching_accessory_sku($greenhouse_sku) {
    // Convert to uppercase for consistency
    $greenhouse_sku = strtoupper($greenhouse_sku);
    
    // Remove any variant suffix like -P, -BASE, etc. (but keep size)
    // Pattern: model-size-variant → model-size
    // Example: ASCENT-8X20-P → ASCENT-8X20
    $base_sku = preg_replace('/-[A-Z]+$/', '', $greenhouse_sku);
    
    // If the sku didn't have a variant, it's already the base
    if ($base_sku === '') {
        $base_sku = $greenhouse_sku;
    }
    
    // Check if this is a greenhouse product (contains model name)
    $greenhouse_models = array('ASCENT', 'ELITE', 'SUMMIT', 'ELEMENT');
    
    $is_greenhouse = false;
    foreach ($greenhouse_models as $model) {
        if (stripos($base_sku, $model) !== false) {
            $is_greenhouse = true;
            break;
        }
    }
    
    if (!$is_greenhouse) {
        return false;
    }
    
    // Don't add accessory for an accessory package
    if (stripos($base_sku, '-ACS') !== false || stripos($base_sku, 'ACCESSORY') !== false) {
        return false;
    }
    
    // Convert size format: 8X20 → 820, 12X32 → 1232
    // Pattern: Find format like -8X20 and convert to -820
    $base_sku = preg_replace('/-(\d+)X(\d+)/', '-$1$2', $base_sku);
    
    // Generate accessory SKU in uppercase
    return $base_sku . '-ACS';
}

// Add accessory package to cart by SKU
function add_accessory_by_sku($sku) {
    $product_id = wc_get_product_id_by_sku($sku);
    
    if (!$product_id) {
        wc_add_notice("Product SKU '{$sku}' not found", 'error');
        return false;
    }
    
    // Just try to add it - no validation, no flags
    $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
    
    if ($cart_item_key) {
        WC()->cart->cart_contents[$cart_item_key]['free_accessory'] = true;
        wc_add_notice("Added: {$sku}", 'success');
        return true;
    }
    
    wc_add_notice("Failed to add: {$sku}", 'error');
    return false;
}

// Set price to $0 for free accessory packages
add_action('woocommerce_before_calculate_totals', 'set_free_accessory_price', 20);

function set_free_accessory_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }
    
    // Check if qualifying coupon is applied
    $promo_codes = array('freeupgrade');
    $applied_coupons = array_map('strtolower', WC()->cart->get_applied_coupons());
    
    $has_promo = false;
    foreach ($applied_coupons as $coupon) {
        if (in_array($coupon, $promo_codes)) {
            $has_promo = true;
            break;
        }
    }
    
    if (!$has_promo) {
        return;
    }
    
    // Set price to 0 for accessory packages marked as free (case insensitive)
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $sku = $cart_item['data']->get_sku();
        
        // Check if this is an accessory package (case insensitive)
        if (stripos($sku, '-acs') !== false || isset($cart_item['free_accessory'])) {
            $cart_item['data']->set_price(0);
        }
    }
}

// Force quantity to 1 ONLY for free accessories (not greenhouses)
add_action('woocommerce_before_calculate_totals', 'enforce_free_accessory_quantity', 5);

function enforce_free_accessory_quantity($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        // ONLY enforce for items marked as free_accessory
        if (isset($cart_item['free_accessory']) && $cart_item['quantity'] != 1) {
            // Force quantity back to 1
            $cart->set_quantity($cart_item_key, 1, false);
        }
    }
}

// Lock quantity display for free accessories ONLY (not greenhouses)
add_filter('woocommerce_cart_item_quantity', 'lock_free_accessory_quantity', 10, 3);

function lock_free_accessory_quantity($product_quantity, $cart_item_key, $cart_item) {
    // ONLY lock quantity for free accessories, not for greenhouses
    if (isset($cart_item['free_accessory'])) {
        // Return quantity as plain text (not editable)
        return '<span style="color: #4CAF50; font-weight: 600;">1</span>';
    }
    
    // Return normal quantity input for all other products (including greenhouses)
    return $product_quantity;
}

// Add CSS to hide quantity controls ONLY for free accessories
add_action('wp_head', 'hide_free_accessory_quantity_controls');

function hide_free_accessory_quantity_controls() {
    if (is_cart() || is_checkout()) {
        ?>
        <style>
            /* ONLY target free accessory items - NOT greenhouses */
            .cart_item.free-accessory-item .quantity input[type="number"],
            .cart_item.free-accessory-item .quantity .qty,
            .cart_item.free-accessory-item .plus,
            .cart_item.free-accessory-item .minus,
            .cart_item.free-accessory-item .quantity button {
                display: none !important;
            }
            
            /* Style the locked quantity */
            .cart_item.free-accessory-item .quantity {
                pointer-events: none;
                user-select: none;
            }
            
            /* Highlight free accessory rows */
            .cart_item.free-accessory-item {
                background: #f0f9f4 !important;
            }
            
            /* Make sure greenhouses can still be removed - force show remove button */
            .cart_item:not(.free-accessory-item) .product-remove {
                display: table-cell !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
        </style>
        <?php
    }
}

// Add class ONLY to free accessory items (not greenhouses)
add_filter('woocommerce_cart_item_class', 'add_free_accessory_cart_class', 10, 3);

function add_free_accessory_cart_class($class, $cart_item, $cart_item_key) {
    // ONLY add class to free accessories
    if (isset($cart_item['free_accessory'])) {
        $class .= ' free-accessory-item';
    }
    // Greenhouses get normal classes
    return $class;
}

// Remove free accessory packages when coupon is removed
add_action('woocommerce_removed_coupon', 'remove_free_accessory_packages');

function remove_free_accessory_packages($coupon_code) {
    $promo_codes = array('FREEUPGRADE');
    
    if (!in_array(strtoupper($coupon_code), $promo_codes)) {
        return;
    }
    
    // Remove all free accessory packages
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['free_accessory'])) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }
    
    wc_add_notice('Free accessory packages removed.', 'notice');
}

// Update accessory packages when cart items change
add_action('woocommerce_cart_item_removed', 'update_accessories_on_cart_change', 10, 2);

function update_accessories_on_cart_change($cart_item_key, $cart) {
    // Check if a promo coupon is applied
    $promo_codes = array('freeupgrade', 'fallsale', 'premium2024');
    $applied_coupons = array_map('strtolower', WC()->cart->get_applied_coupons());
    
    $has_promo = false;
    foreach ($applied_coupons as $coupon) {
        if (in_array($coupon, $promo_codes)) {
            $has_promo = true;
            break;
        }
    }
    
    if (!$has_promo) {
        return;
    }
    
    // Get the removed item's SKU
    $removed_item = $cart->removed_cart_contents[$cart_item_key];
    $removed_sku = $removed_item['data']->get_sku();
    
    // If a greenhouse was removed, remove its accessory package
    if (!empty($removed_sku)) {
        $accessory_sku = get_matching_accessory_sku($removed_sku);
        
        if ($accessory_sku) {
            // Find and remove the matching accessory
            foreach (WC()->cart->get_cart() as $key => $item) {
                if ($item['data']->get_sku() === $accessory_sku && isset($item['free_accessory'])) {
                    WC()->cart->remove_cart_item($key);
                    break;
                }
            }
        }
    }
}

// Display FREE badge in cart
add_filter('woocommerce_cart_item_price', 'display_free_accessory_badge', 10, 3);

function display_free_accessory_badge($price, $cart_item, $cart_item_key) {
    if (isset($cart_item['free_accessory'])) {
        $original_price = $cart_item['data']->get_regular_price();
        return '<span style="color: #4CAF50; font-weight: bold; font-size: 1.1em;">FREE</span> <del style="opacity: 0.6;">$' . number_format($original_price, 2) . '</del>';
    }
    
    return $price;
}

// Show which accessory packages will be added
add_action('woocommerce_before_cart', 'show_available_accessory_promotion');

function show_available_accessory_promotion() {
    $promo_codes = array('freeupgrade', 'fallsale', 'premium2024');
    $applied_coupons = array_map('strtolower', WC()->cart->get_applied_coupons());
    
    $has_promo = false;
    foreach ($applied_coupons as $coupon) {
        if (in_array($coupon, $promo_codes)) {
            $has_promo = true;
            break;
        }
    }
    
    if ($has_promo) {
        // Show success message
        echo '<div class="woocommerce-info" style="background: #4CAF50; color: white; border-color: #388E3C;">';
        echo '🎁 <strong>Congratulations!</strong> You\'re getting FREE Premium Accessory Package(s) with your order! To edit/remove items from your cart please remove the coupon code first.';
        echo '</div>';
    } else {
        // Check if cart has greenhouses that qualify
        $has_greenhouse = false;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $sku = $cart_item['data']->get_sku();
            if (get_matching_accessory_sku($sku)) {
                $has_greenhouse = true;
                break;
            }
        }
       /* 
        if ($has_greenhouse) {
            echo '<div class="woocommerce-info">';
            echo '💡 <strong>Special Offer:</strong> Use code <strong>FREEUPGRADE</strong> to get a FREE Premium Accessory Package with your greenhouse!';
            echo '</div>';
        }
		*/
    }
}

// Prevent removal of free accessories only
add_filter('woocommerce_cart_item_remove_link', 'prevent_free_accessory_removal', 10, 2);

function prevent_free_accessory_removal($remove_link, $cart_item_key) {
    $cart_contents = WC()->cart->get_cart();
    
    if (!isset($cart_contents[$cart_item_key])) {
        return $remove_link;
    }
    
    $cart_item = $cart_contents[$cart_item_key];
    
    // ONLY prevent removal of free accessories (not greenhouses)
    if (isset($cart_item['free_accessory'])) {
        return '<span style="color: #4CAF50; font-size: 0.9em; font-weight: 600;">★ FREE GIFT</span>';
    }
    
    // Allow normal remove link for greenhouses and other products
    return $remove_link;
}

// Add cart item note for free accessories
add_filter('woocommerce_get_item_data', 'add_free_accessory_cart_note', 10, 2);

function add_free_accessory_cart_note($item_data, $cart_item) {
    if (isset($cart_item['free_accessory'])) {
        $item_data[] = array(
            'key'   => 'Promotion',
            'value' => '🎁 FREE with your greenhouse purchase!'
        );
    }
    
    return $item_data;
}

// Add helpful note on greenhouse products
add_filter('woocommerce_cart_item_name', 'add_greenhouse_removal_note', 10, 3);

function add_greenhouse_removal_note($product_name, $cart_item, $cart_item_key) {
    // Only for greenhouses when promo is active
    $promo_codes = array('freeupgrade', 'fallsale', 'premium2024');
    $applied_coupons = array_map('strtolower', WC()->cart->get_applied_coupons());
    
    $has_promo = false;
    foreach ($applied_coupons as $coupon) {
        if (in_array($coupon, $promo_codes)) {
            $has_promo = true;
            break;
        }
    }
    
    if (!$has_promo) {
        return $product_name;
    }
    
    // Check if this item has a matching accessory
    $sku = $cart_item['data']->get_sku();
    $accessory_sku = get_matching_accessory_sku($sku);
    
    if ($accessory_sku && !isset($cart_item['free_accessory'])) {
        // This is a greenhouse with a free accessory
        $product_name .= '<br><small style="color: #999; font-style: italic;">⚠️ Removing this will also remove its free accessory package</small>';
    }
    
    return $product_name;
}
add_filter('woocommerce_is_purchasable', 'force_acs_purchasable_during_promo', 999, 2);

function force_acs_purchasable_during_promo($is_purchasable, $product) {
    // If we're applying a coupon and it's an accessory, force purchasable
    if (doing_action('woocommerce_applied_coupon')) {
        $sku = $product->get_sku();
        if (!empty($sku) && stripos($sku, '-acs') !== false) {
            return true;
        }
    }
    
    return $is_purchasable;
}
// Exclude accessories from WooCommerce product queries
// Completely exclude accessory products from search results
add_action('pre_get_posts', 'completely_exclude_accessories_from_search');

function completely_exclude_accessories_from_search($query) {
    // Only on frontend main query
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // Only affect search queries
    if (!$query->is_search()) {
        return;
    }
    
    global $wpdb;
    
    // Get all accessory product IDs (cached)
    $cache_key = 'all_accessory_product_ids';
    $accessory_ids = wp_cache_get($cache_key);
    
    if ($accessory_ids === false) {
        $accessory_ids = $wpdb->get_col(
            "SELECT DISTINCT post_id 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_sku' 
            AND UPPER(meta_value) LIKE '%-ACS%'"
        );
        
        wp_cache_set($cache_key, $accessory_ids, '', 3600);
    }
    
    if (!empty($accessory_ids)) {
        // Get existing excluded IDs
        $existing_excluded = $query->get('post__not_in');
        
        if (!is_array($existing_excluded)) {
            $existing_excluded = array();
        }
        
        // Merge and exclude
        $all_excluded = array_merge($existing_excluded, $accessory_ids);
        $query->set('post__not_in', $all_excluded);
    }
}

// FRESH START - Enhanced Conversions
add_action('woocommerce_thankyou', 'grandio_push_enhanced_conversion', 5, 1);

function grandio_push_enhanced_conversion($order_id) {
    ?>
    <script>console.log('🚀 Grandio EC function called for order:', <?php echo json_encode($order_id); ?>);</script>
    <?php
    
    if (!$order_id) {
        echo '<script>console.log("❌ No order ID");</script>';
        return;
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        echo '<script>console.log("❌ Invalid order");</script>';
        return;
    }
    
    echo '<script>console.log("✅ Valid order, status:", "' . $order->get_status() . '");</script>';
    
    if (!in_array($order->get_status(), ['completed', 'processing'])) {
        echo '<script>console.log("❌ Wrong status");</script>';
        return;
    }
    
    // Don't check for duplicate flag - always run for testing
    
    // Get and hash data
    $email = strtolower(trim($order->get_billing_email()));
    $phone = preg_replace('/[^0-9]/', '', $order->get_billing_phone());
    if (strlen($phone) === 10) {
        $phone = '+1' . $phone;
    }
    $first_name = strtolower(trim($order->get_billing_first_name()));
    $last_name = strtolower(trim($order->get_billing_last_name()));
    
    ?>
    <script>
    console.log('📧 Raw email:', '<?php echo esc_js($email); ?>');
    console.log('📱 Normalized phone:', '<?php echo esc_js($phone); ?>');
    
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({
        'event': 'enhanced_conversion_data',
        'orderData': {
            'customer': {
                'billing': {
                    'email_hash': '<?php echo hash("sha256", $email); ?>',
                    'first_name_hash': '<?php echo hash("sha256", $first_name); ?>',
                    'last_name_hash': '<?php echo hash("sha256", $last_name); ?>',
                    'phone_hash': '<?php echo hash("sha256", $phone); ?>',
                    'street': '<?php echo esc_js($order->get_billing_address_1()); ?>',
                    'city': '<?php echo esc_js($order->get_billing_city()); ?>',
                    'region': '<?php echo esc_js($order->get_billing_state()); ?>',
                    'postcode': '<?php echo esc_js($order->get_billing_postcode()); ?>',
                    'country': '<?php echo esc_js($order->get_billing_country()); ?>'
                }
            }
        }
    });
    
    console.log('✅✅✅ ENHANCED CONVERSION DATA PUSHED ✅✅✅');
    console.log('Full dataLayer:', window.dataLayer);
    </script>
    <?php
}
// Validate when adding to cart
add_filter( 'woocommerce_add_to_cart_validation', 'limit_gift_cards_per_order_by_ids', 10, 3 );
function limit_gift_cards_per_order_by_ids( $passed, $product_id, $quantity ) {
    // Array of your gift card product IDs
    $gift_card_ids = array( 5112880, 5112878, 5112875, 5112839 ); 
    
    // Check if product is a gift card
    if ( in_array( $product_id, $gift_card_ids ) ) {
        $cart_gift_card_count = 0;
        
        // Count existing gift cards in cart
        foreach( WC()->cart->get_cart() as $cart_item ) {
            if ( in_array( $cart_item['product_id'], $gift_card_ids ) ) {
                $cart_gift_card_count += $cart_item['quantity'];
            }
        }
        
        // Limit to 2 gift cards per order
        $max_gift_cards = 2;
        
        if ( ( $cart_gift_card_count + $quantity ) > $max_gift_cards ) {
            wc_add_notice( 'To prevent fraud, we have limited our gift cards to 2 per customer. Please contact us if you have any questions.', 'error' );
            $passed = false;
        }
    }
    
    return $passed;
}
// Check cart totals on cart and checkout pages
add_action( 'woocommerce_check_cart_items', 'check_gift_card_limit_in_cart_by_ids' );
function check_gift_card_limit_in_cart_by_ids() {
    // Allow admins to bypass this check
    if ( current_user_can( 'manage_woocommerce' ) ) {
        return;
    }
    
    // Array of your gift card product IDs
    $gift_card_ids = array( 5112880, 5112878, 5112875, 5112839 ); 
    
    $cart_gift_card_count = 0;
    
    // Count all gift cards in cart
    foreach( WC()->cart->get_cart() as $cart_item ) {
        if ( in_array( $cart_item['product_id'], $gift_card_ids ) ) {
            $cart_gift_card_count += $cart_item['quantity'];
        }
    }
    
    $max_gift_cards = 2;
    
    if ( $cart_gift_card_count > $max_gift_cards ) {
        wc_add_notice( 'To prevent fraud, we have limited our gift cards to 2 per customer. Please update your cart.', 'error' );
    }
} 

// Limit gift card coupons to 2 per order - Comprehensive check
add_action( 'woocommerce_applied_coupon', 'check_gift_card_limit_after_apply', 10, 1 );
function check_gift_card_limit_after_apply( $coupon_code ) {
    // Allow admins to bypass this check
    if ( current_user_can( 'manage_woocommerce' ) ) {
        return;
    }
    
    // Count gift card coupons currently applied
    $applied_coupons = WC()->cart->get_applied_coupons();
    $gift_card_count = 0;
    
    foreach ( $applied_coupons as $applied_code ) {
        if ( strpos( strtolower( $applied_code ), 'g-' ) === 0 ) {
            $gift_card_count++;
        }
    }
    
    // If more than 2 gift cards are applied, remove the one just added
    if ( $gift_card_count > 2 ) {
        WC()->cart->remove_coupon( $coupon_code );
        wc_add_notice( 'To prevent fraud, we limit 2 gift cards per order. If you have more gift cards to use, please contact us at (866) 448-8231 and we\'ll help you process your order.', 'error' );
    }
}

// Prevent applying more than 2 gift cards at checkout
add_action( 'woocommerce_check_cart_items', 'check_gift_card_coupon_limit_at_checkout' );
function check_gift_card_coupon_limit_at_checkout() {
    // Allow admins to bypass this check
    if ( current_user_can( 'manage_woocommerce' ) ) {
        return;
    }
    
    $applied_coupons = WC()->cart->get_applied_coupons();
    $gift_card_count = 0;
    $gift_card_codes = array();
    
    foreach ( $applied_coupons as $applied_code ) {
        if ( strpos( strtolower( $applied_code ), 'g-' ) === 0 ) {
            $gift_card_count++;
            $gift_card_codes[] = $applied_code;
        }
    }
    
    // If more than 2 gift cards are applied, show error
    if ( $gift_card_count > 2 ) {
        // Remove extra gift cards (keep first 2)
        $codes_to_remove = array_slice( $gift_card_codes, 2 );
        foreach ( $codes_to_remove as $code ) {
            WC()->cart->remove_coupon( $code );
        }
        wc_add_notice( 'To prevent fraud, we limit 2 gift cards per order. If you have more gift cards to use, please contact us at (866) 448-8231 and we\'ll help you process your order.', 'error' );
    }
}
// Sale Banner - Black & Yellow Version with Redirect
function add_bf_banner() {
	 // Don't show banner on the holiday sale page itself
    if (is_page('holiday-sale')) {
        return;
    }
    ?>
    <div id="saleBanner" style="position: fixed; top: 0; left: 0; width: 100%; background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%); color: #FFD700; padding: 15px 20px; z-index: 99998; box-shadow: 0 2px 10px rgba(255,215,0,0.3); border-bottom: 3px solid #FFD700; display: none;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; position: relative;">
            <div style="text-align: center; flex: 1;">
                <span style="font-size: 22px; font-weight: 900; display: inline-block; margin-right: 15px; text-transform: uppercase; letter-spacing: 0.5px; word-spacing: 8px; text-shadow: 2px 2px 4px rgba(255,215,0,0.3);">
                    Holiday Savings Extended Through 12/31! 
                </span>
                <button onclick="window.location.href='/holiday-sale'" style="background: #FFD700; color: #000; border: 3px solid #FFD700; padding: 12px 30px; border-radius: 8px; font-weight: 900; cursor: pointer; font-size: 16px; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(255,215,0,0.4);" onmouseover="this.style.background='#FFC700'; this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(255,215,0,0.6)'" onmouseout="this.style.background='#FFD700'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(255,215,0,0.4)'">
                    SHOP DEAL NOW
                </button>
            </div>
            <button onclick="closeBanner()" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: rgba(255,215,0,0.2); border: 2px solid #FFD700; color: #FFD700; font-size: 28px; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0; font-weight: bold;" onmouseover="this.style.background='rgba(255,215,0,0.3)'" onmouseout="this.style.background='rgba(255,215,0,0.2)'">
                ×
            </button>
        </div>
    </div>
      <script>
        // Check if banner was previously closed
        if (!sessionStorage.getItem('bfBannerClosed')) {
            document.getElementById('saleBanner').style.display = 'block';
        }
        
        function closeBanner() {
            document.getElementById('saleBanner').style.display = 'none';
            // Remember that user closed the banner for this browser session
            sessionStorage.setItem('bfBannerClosed', 'true');
        }
    </script>
    <?php
}
add_action('wp_body_open', 'add_bf_banner');

/**
 * CTA Section with Klaviyo Integration
 * Collects emails and adds to klaviyo
 */

// Add shortcode for CTA section
add_shortcode('klaviyo_cta', 'klaviyo_cta_section');

// AJAX handlers for CTA email subscription
add_action('wp_ajax_subscribe_cta_email', 'handle_cta_email_subscription');
add_action('wp_ajax_nopriv_subscribe_cta_email', 'handle_cta_email_subscription');
function klaviyo_cta_section($atts) {
    // Parse attributes with defaults
    $atts = shortcode_atts([
        'title' => 'Perfect Greenhouse Gifts Dropping Every Week',
        'subtitle' => 'Sign up to recieve NEW Weekly Deals ',
        'button_text' => 'Subscribe Now',
        'bg_color' => '#2d7a4f',
        'accent_color' => '#1a5233'
    ], $atts);
    
    // Weekly deals configuration
    $weekly_deals = [
        [
            'start' => '2025-12-04',
            'end' => '2025-12-10',
            'week' => 'Week 1',
            'title' => 'The Gift of Fresh Air',
            'offer' => 'Buy any Louvre Window, get an Auto-Opener FREE ($79 value)',
            'code' => 'FRESHAIR'
        ],
        [
            'start' => '2025-12-11',
            'end' => '2025-12-17',
            'week' => 'Week 2',
            'title' => 'Mold Free & Bug Proof Panels',
            'offer' => '25% OFF All Moisture Control Kits',
            'code' => 'Valid: Dec 11 - 17, 2025. *Must be installed during greenhouse assembly'
        ],
        [
            'start' => '2025-12-18',
            'end' => '2025-12-24',
            'week' => 'Week 3',
            'title' => 'Dream Workspace',
            'offer' => '$15 OFF every Staging Table & Potting Bench',
            'code' => 'Valid: Dec 18 - 25, 2025'
        ],
        [
            'start' => '2025-12-25',
            'end' => '2025-12-31',
            'week' => 'Week 4',
            'title' => 'Build Stronger Together',
            'offer' => 'BOGO Frame-to-Base Brackets (All models except Summit)',
            'code' => 'BUILDSTRONG'
        ]
    ];
    
    // Find current deal
    $current_deal = null;
    $today = current_time('Y-m-d');
    
    foreach ($weekly_deals as $deal) {
        if ($today >= $deal['start'] && $today <= $deal['end']) {
            $current_deal = $deal;
            break;
        }
    }
    
    ob_start();
    ?>
    <style>
        .klaviyo-cta-section {
            background: linear-gradient(135deg, <?php echo esc_attr($atts['bg_color']); ?> 0%, <?php echo esc_attr($atts['accent_color']); ?> 100%);
            padding: 60px 20px;
            text-align: center;
            margin: 40px 0;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .klaviyo-cta-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .klaviyo-cta-section h2 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 15px 0;
            line-height: 1.2;
        }
        
        .klaviyo-cta-section p {
            color: rgba(255,255,255,0.95);
            font-size: 18px;
            margin: 0 0 30px 0;
            line-height: 1.5;
        }
        
        .current-deal-banner {
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 20px;
            margin: 0 0 30px 0;
            backdrop-filter: blur(10px);
        }
        
        .current-deal-banner .deal-week {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 8px 0;
        }
        
        .current-deal-banner .deal-title {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .current-deal-banner .deal-offer {
            color: rgba(255,255,255,0.95);
            font-size: 16px;
            margin: 0 0 12px 0;
            line-height: 1.4;
        }
        
        .current-deal-banner .deal-code-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .current-deal-banner .deal-code-label {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            font-weight: 600;
        }
        
        .current-deal-banner .deal-code {
            background: white;
            color: <?php echo esc_attr($atts['bg_color']); ?>;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            font-family: monospace;
        }
        
        .current-deal-banner .deal-dates {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            margin: 10px 0 0 0;
            font-style: italic;
        }
        
        .klaviyo-cta-form {
            display: flex;
            gap: 12px;
            max-width: 500px;
            margin: 0 auto;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .klaviyo-cta-form input[type="email"] {
            flex: 1;
            min-width: 280px;
            padding: 16px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .klaviyo-cta-form input[type="email"]::placeholder {
            color: #999;
        }
        
        .klaviyo-cta-form button[type="submit"] {
            padding: 16px 35px;
            background: white;
            color: <?php echo esc_attr($atts['bg_color']); ?>;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .klaviyo-cta-form button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .klaviyo-cta-form button[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .klaviyo-cta-message {
            margin-top: 15px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            display: none;
        }
        
        .klaviyo-cta-message.success {
            background: rgba(255,255,255,0.25);
            color: white;
        }
        
        .klaviyo-cta-message.error {
            background: rgba(255,59,48,0.3);
            color: white;
        }
        
        @media (max-width: 600px) {
            .klaviyo-cta-section {
                padding: 30px 20px;
            }
            
            .klaviyo-cta-section h2 {
                font-size: 26px;
            }
            
            .klaviyo-cta-section p {
                font-size: 16px;
            }
            
            .current-deal-banner .deal-title {
                font-size: 20px;
            }
            
            .current-deal-banner .deal-offer {
                font-size: 15px;
            }
            
            .klaviyo-cta-form {
                flex-direction: column;
            }
            
            .klaviyo-cta-form input[type="email"],
            .klaviyo-cta-form button[type="submit"] {
                width: 100%;
            }
        }
    </style>
    
    <div class="klaviyo-cta-section">
        <div class="klaviyo-cta-content">
            <h2><?php echo esc_html($atts['title']); ?></h2>
            <p><?php echo esc_html($atts['subtitle']); ?></p>
            
            <?php if ($current_deal): ?>
            <div class="current-deal-banner">
                <div class="deal-week"> <?php echo esc_html($current_deal['week']); ?> - NOW LIVE</div>
                <div class="deal-title"><?php echo esc_html($current_deal['title']); ?></div>
            <div class="deal-offer" style = display: none;><?php echo esc_html($current_deal['offer']); ?></div> 
                <!--<div class="deal-code-wrapper">
                    <span class="deal-code-label">USE CODE:</span>
                    <span class="deal-code"><?php echo esc_html($current_deal['code']); ?></span>
                </div> -->
                <div class="deal-dates">
                    <?php echo esc_html($current_deal['code']); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <form class="klaviyo-cta-form" data-cta-form>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Enter your email address" 
                    required 
                    data-cta-email
                >
                <button type="submit" data-cta-submit><?php echo esc_html($atts['button_text']); ?></button>
            </form>
            
            <div class="klaviyo-cta-message" data-cta-message></div>
        </div>
    </div>
    
    <script>
        (function() {
            const form = document.querySelector('[data-cta-form]');
            if (!form) return;
            
            const emailInput = form.querySelector('[data-cta-email]');
            const submitBtn = form.querySelector('[data-cta-submit]');
            const message = form.parentElement.querySelector('[data-cta-message]');
            const originalButtonText = submitBtn.textContent;
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                
                if (!email) {
                    showMessage('Please enter your email address', 'error');
                    return;
                }
                
                // Disable form while submitting
                submitBtn.disabled = true;
                submitBtn.textContent = 'Subscribing...';
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'subscribe_cta_email');
                formData.append('email', email);
                formData.append('nonce', '<?php echo wp_create_nonce('cta_subscribe_nonce'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('✓ Successfully subscribed! Check your email for exclusive offers.', 'success');
                        emailInput.value = '';
                    } else {
                        showMessage(data.data || 'Subscription failed. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Subscription error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalButtonText;
                });
            });
            
            function showMessage(text, type) {
                message.textContent = text;
                message.className = 'klaviyo-cta-message ' + type;
                message.style.display = 'block';
                
                setTimeout(() => {
                    message.style.display = 'none';
                }, 6000);
            }
        })();
    </script>
    <?php
    
    return ob_get_clean();
}



function handle_cta_email_subscription() {
    // Verify nonce ONLY if it's provided (for desktop)
    // Skip nonce check if not provided (for mobile HTML page)
    if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
        if (!wp_verify_nonce($_POST['nonce'], 'cta_subscribe_nonce')) {
            wp_send_json_error('Invalid security token');
            return;
        }
    }
    
    $email = sanitize_email($_POST['email'] ?? '');
    
    if (empty($email) || !is_email($email)) {
        wp_send_json_error('Invalid email address');
        return;
    }
    
    // Klaviyo Configuration
    $klaviyo_api_key = 'pk_14200bdef2c06e18463b3a1df4bea24ce9';
    $klaviyo_list_id = 'VNw3jZ';
    
    // Subscribe to Klaviyo using Bulk Import API
    $result = subscribe_to_klaviyo_cta($email, $klaviyo_api_key, $klaviyo_list_id);
    
    if ($result['success']) {
        wp_send_json_success('Subscribed successfully');
    } else {
        wp_send_json_error($result['message']);
    }
}


function subscribe_to_klaviyo_cta($email, $api_key, $list_id) {
    try {
        $profileAttributes = [
            'email' => $email,
            'properties' => [
                'Source' => 'CTA Section',
                'CTA Campaign' => 'Pre-Black Friday Sale',
                'Signed Up Via CTA' => true,
                'Sign Up Date' => date('Y-m-d H:i:s')
            ]
        ];
        
        $payload = [
            'data' => [
                'type' => 'profile-bulk-import-job',
                'attributes' => [
                    'profiles' => [
                        'data' => [
                            [
                                'type' => 'profile',
                                'attributes' => $profileAttributes
                            ]
                        ]
                    ]
                ],
                'relationships' => [
                    'lists' => [
                        'data' => [
                            [
                                'type' => 'list',
                                'id' => $list_id
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $url = 'https://a.klaviyo.com/api/profile-bulk-import-jobs/';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Klaviyo-API-Key ' . $api_key,
            'Content-Type: application/json',
            'revision: 2024-10-15'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        error_log("Klaviyo CTA Subscribe - HTTP: $httpCode - Response: $response");
        
        if (in_array($httpCode, [200, 201, 202])) {
            return ['success' => true, 'message' => 'Subscribed successfully'];
        } else {
            return ['success' => false, 'message' => "HTTP $httpCode: $response"];
        }
        
    } catch (Exception $e) {
        error_log("Klaviyo CTA Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
// Add coupon code as a separate line in totals
add_filter( 'wpo_wcpdf_woocommerce_totals', 'add_coupon_code_line_to_totals', 10, 3 );
function add_coupon_code_line_to_totals( $totals, $order, $document_type ) {
    
    // Get all coupons used in the order
    $coupons = $order->get_coupon_codes();
    
    if ( empty( $coupons ) ) {
        return $totals; // No coupons, return original totals
    }
    
    // Create new totals array with coupon code line inserted
    $new_totals = array();
    
    foreach ( $totals as $key => $total ) {
        // Add the current total line
        $new_totals[$key] = $total;
        
        // If this is a discount line, add coupon code line right after it
        if ( strpos( $key, 'discount' ) !== false || strpos( $key, 'coupon' ) !== false ) {
            
            // Format coupon codes
            $coupon_codes = implode( ', ', $coupons );
            
            // Add new line showing the coupon code(s)
            $new_totals['coupon_code_used'] = array(
                'label' => __( 'Coupon Code:', 'woocommerce-pdf-invoices-packing-slips' ),
                'value' => $coupon_codes
            );
        }
    }
    
    return $new_totals;
}

// Add custom column header
add_filter('manage_edit-shop_order_columns', 'add_coupon_column_header', 20);
function add_coupon_column_header($columns) {
    $new_columns = array();
    
    foreach ($columns as $column_name => $column_info) {
        $new_columns[$column_name] = $column_info;
        
        // Add coupon column after order total
        if ($column_name === 'order_total') {
            $new_columns['order_coupon'] = __('Coupon Used', 'woocommerce');
        }
    }
    
    return $new_columns;
}

// Add custom column content
add_action('manage_shop_order_posts_custom_column', 'add_coupon_column_content', 20, 2);
function add_coupon_column_content($column, $post_id) {
    if ($column === 'order_coupon') {
        $order = wc_get_order($post_id);
        
        if ($order) {
            $coupons = $order->get_coupon_codes();
            
            if (!empty($coupons)) {
                echo esc_html(implode(', ', $coupons));
            } else {
                echo '-';
            }
        }
    }
}

// Make the column sortable (optional)
add_filter('manage_edit-shop_order_sortable_columns', 'make_coupon_column_sortable');
function make_coupon_column_sortable($columns) {
    $columns['order_coupon'] = 'order_coupon';
    return $columns;
}

// Search WooCommerce orders by customer notes
add_filter('posts_search', 'search_customer_notes', 10, 2);
function search_customer_notes($search, $query) {
    global $wpdb;
    
    if (!is_admin() || !$query->is_main_query()) {
        return $search;
    }
    
    if (!isset($query->query['post_type']) || $query->query['post_type'] !== 'shop_order') {
        return $search;
    }
    
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    if (empty($search_term)) {
        return $search;
    }
    
    // Search in customer-provided notes (stored in order meta)
    $search .= " OR {$wpdb->posts}.ID IN (
        SELECT post_id FROM {$wpdb->postmeta}
        WHERE meta_key = '_customer_note'
        AND meta_value LIKE '%" . $wpdb->esc_like($search_term) . "%'
    )";
    
    return $search;
}
function render_star_rating($rating, $size = '18px', $show_average = false) {
    $filled_stars = round($rating);
    $empty_stars = 5 - $filled_stars;
    $html = '<span class="star-rating" style="font-size:' . esc_attr($size) . '; color:#000;">';
    $html .= str_repeat('★', $filled_stars) . str_repeat('☆', $empty_stars);
    if ($show_average) {
        $html .= ' <span style="font-size:' . ($size * 0.9) . ';">(' . esc_html($rating) . ' average)</span>';
    }
    $html .= '</span>';
    return $html;
}
