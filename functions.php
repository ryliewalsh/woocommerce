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
                '1.0.22' // Update version number when you modify CSS
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
// Rotating Sale Banner - Two Promotions (Free Heater + Financing)
function add_sale_banner() {
    ?>
    <div id="saleBanner" style="position: fixed; top: 0; left: 0; width: 100%; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 15px 20px; z-index: 99998; box-shadow: 0 2px 10px rgba(0,0,0,0.2); transition: background 0.5s ease;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; position: relative;">
            <div style="text-align: center; flex: 1; position: relative; min-height: 30px;">
                <!-- Promotion 1: Free Heater -->
                <div class="promo-slide" data-promo="1" style="position: absolute; width: 100%; left: 0; opacity: 1; transition: opacity 0.5s ease;">
                    <span style="font-size: 22px; font-weight: 900; display: inline-block; margin-right: 15px; text-transform: uppercase; letter-spacing: 0.5px; word-spacing: 8px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                        FREE Heater w/ Greenhouse Purchase $99 Value! Use Code: FREEHEAT at checkout
                    </span>
                </div>
                
                <!-- Promotion 2: Financing -->
                <div class="promo-slide" data-promo="2" style="position: absolute; width: 100%; left: 0; opacity: 0; transition: opacity 0.5s ease;">
                    <span style="font-size: 22px; font-weight: 900; display: inline-block; margin-right: 15px; text-transform: uppercase; letter-spacing: 0.5px; word-spacing: 8px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                        0% Financing Available! Low Monthly Payments with Synchrony
                    </span>
                    <a href="/financing-2" style="display: inline-block; background: white; color: #2e7d5a; padding: 8px 20px; border-radius: 25px; text-decoration: none; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" onmouseover="this.style.background='#f0f0f0'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='white'; this.style.transform='scale(1)';">
                        View Financing →
                    </a>
                </div>
            </div>
            
            <!-- Navigation Dots -->
            <div style="position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px;">
                <button onclick="goToSlide(1)" class="promo-dot" data-dot="1" style="width: 10px; height: 10px; border-radius: 50%; background: white; border: none; cursor: pointer; padding: 0; opacity: 1; transition: opacity 0.3s ease;"></button>
                <button onclick="goToSlide(2)" class="promo-dot" data-dot="2" style="width: 10px; height: 10px; border-radius: 50%; background: white; border: none; cursor: pointer; padding: 0; opacity: 0.5; transition: opacity 0.3s ease;"></button>
            </div>
            
            <button onclick="closeBanner()" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); border: none; color: white; font-size: 28px; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0; font-weight: bold;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ×
            </button>
        </div>
    </div>

    <script>
        let currentSlide = 1;
        let autoRotate;
        const totalSlides = 2;
        
        // Background colors for different promotions
        const backgrounds = {
            1: 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)', // Red - Free Heater
            2: 'linear-gradient(135deg, #2e7d5a 0%, #4a9b6e 100%)'  // Green - Financing
        };

        function showSlide(slideNum) {
            // Hide all slides
            document.querySelectorAll('.promo-slide').forEach(slide => {
                slide.style.opacity = '0';
            });
            
            // Show current slide
            const activeSlide = document.querySelector(`.promo-slide[data-promo="${slideNum}"]`);
            if (activeSlide) {
                activeSlide.style.opacity = '1';
            }
            
            // Update dots
            document.querySelectorAll('.promo-dot').forEach(dot => {
                dot.style.opacity = '0.5';
            });
            const activeDot = document.querySelector(`.promo-dot[data-dot="${slideNum}"]`);
            if (activeDot) {
                activeDot.style.opacity = '1';
            }
            
            // Change background color
            const banner = document.getElementById('saleBanner');
            if (banner && backgrounds[slideNum]) {
                banner.style.background = backgrounds[slideNum];
            }
            
            currentSlide = slideNum;
        }

        function nextSlide() {
            currentSlide = currentSlide >= totalSlides ? 1 : currentSlide + 1;
            showSlide(currentSlide);
        }

        function goToSlide(slideNum) {
            clearInterval(autoRotate);
            showSlide(slideNum);
            startAutoRotate();
        }

        function startAutoRotate() {
            autoRotate = setInterval(nextSlide, 6000); // Change every 6 seconds
        }

        function closeBanner() {
            const banner = document.getElementById('saleBanner');
            if (banner) {
                banner.style.display = 'none';
            }
            clearInterval(autoRotate);
            
            // Save state in session storage
            sessionStorage.setItem('saleBannerClosed', 'true');
        }

        // Initialize
        window.addEventListener('DOMContentLoaded', function() {
            // Check if banner was closed
            if (sessionStorage.getItem('saleBannerClosed') === 'true') {
                const banner = document.getElementById('saleBanner');
                if (banner) {
                    banner.style.display = 'none';
                }
                return;
            }
            
            // Start auto-rotation
            startAutoRotate();
            
            // Pause on hover
            const banner = document.getElementById('saleBanner');
            if (banner) {
                banner.addEventListener('mouseenter', function() {
                    clearInterval(autoRotate);
                });
                
                banner.addEventListener('mouseleave', function() {
                    startAutoRotate();
                });
            }
        });
    </script>

    <style>
        /* Push content down to account for banner */
        body {
            padding-top: 60px !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #saleBanner span {
                font-size: 16px !important;
                word-spacing: 4px !important;
            }
            
            #saleBanner {
                padding: 12px 15px !important;
            }
            
            body {
                padding-top: 55px !important;
            }
        }
        
        @media (max-width: 480px) {
            #saleBanner span {
                font-size: 14px !important;
                word-spacing: 2px !important;
            }
        }
    </style>
    <?php
}
add_action('wp_body_open', 'add_sale_banner');

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
//add_action('woocommerce_before_calculate_totals', 'set_free_accessory_price', 20);

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
//add_action('woocommerce_before_calculate_totals', 'enforce_free_accessory_quantity', 5);

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
//add_filter('woocommerce_cart_item_quantity', 'lock_free_accessory_quantity', 10, 3);

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
//add_action('wp_head', 'hide_free_accessory_quantity_controls');

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
//add_filter('woocommerce_cart_item_class', 'add_free_accessory_cart_class', 10, 3);

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
//add_action('woocommerce_cart_item_removed', 'update_accessories_on_cart_change', 10, 2);

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
//add_filter('woocommerce_cart_item_price', 'display_free_accessory_badge', 10, 3);

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
//add_filter('woocommerce_cart_item_remove_link', 'prevent_free_accessory_removal', 10, 2);

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
//add_filter('woocommerce_get_item_data', 'add_free_accessory_cart_note', 10, 2);

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
//add_filter('woocommerce_cart_item_name', 'add_greenhouse_removal_note', 10, 3);

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
//add_filter('woocommerce_is_purchasable', 'force_acs_purchasable_during_promo', 999, 2);

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
//add_action('pre_get_posts', 'completely_exclude_accessories_from_search');


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
//add_action('wp_body_open', 'add_bf_banner');

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
        'title' => 'LOWEST PRICE GUARANTEE - we price match!',
        'subtitle' => 'Sign up to recieve limited time offers ',
        'button_text' => 'Subscribe Now',
        'bg_color' => ' #4a7c59',
        'accent_color' => '#3d6a4a'
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

// ========================================
// BACKEND: Apply 5% discount when greenhouse + accessories are in cart
// ========================================
add_action('woocommerce_before_calculate_totals', 'package_builder_greenhouse_discount', 20);

function package_builder_greenhouse_discount($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (did_action('woocommerce_before_calculate_totals') >= 2) return;
    
    $has_greenhouse = false;
    $accessories_items = array();
    
    // Check cart contents
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs'));
        
        // Check for greenhouse (adjust category slug if needed)
        if (in_array('element', $categories) || in_array('ascent', $categories) || in_array('elite', $categories) || in_array('summit', $categories)) {
            $has_greenhouse = true;
        }
        
        // Track accessories-upgrade items
        if (in_array('accessories-upgrade', $categories)) {
            $accessories_items[] = $cart_item_key;
        }
    }
    
    // Apply 5% discount to accessories if greenhouse is in cart
    if ($has_greenhouse && !empty($accessories_items)) {
        foreach ($accessories_items as $item_key) {
            $cart_item = $cart->cart_contents[$item_key];
            $original_price = $cart_item['data']->get_price();
            $discounted_price = $original_price * 0.95; // 5% off on top of the already sale price, had to force this way bc the sale price was set as regular 
            
            $cart_item['data']->set_price($discounted_price);
            
            // Store for display
            $cart->cart_contents[$item_key]['original_price'] = $original_price;
            $cart->cart_contents[$item_key]['bundle_discount'] = true;
        }
    }
}

// Show discount in cart item price
add_filter('woocommerce_cart_item_price', 'show_package_bundle_discount', 10, 3);

function show_package_bundle_discount($price, $cart_item, $cart_item_key) {
    if (isset($cart_item['bundle_discount']) && isset($cart_item['original_price'])) {
        $original = wc_price($cart_item['original_price']);
        $new_price = wc_price($cart_item['data']->get_price());
        $savings = $cart_item['original_price'] - $cart_item['data']->get_price();
        
        return '<del style="opacity:0.6;">' . $original . '</del> 
                <ins style="text-decoration:none; color:#27ae60; font-weight:600;">' . $new_price . '</ins>
                <br><small style="color:#27ae60;"> Bundle discount: -' . wc_price($savings) . '</small>';
    }
    return $price;
}

// Show discount notice in cart
add_action('woocommerce_before_cart_table', 'show_package_discount_notice');

function show_package_discount_notice() {
    $has_greenhouse = false;
    $has_accessories = false;
    $total_savings = 0;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $categories = wp_get_post_terms($cart_item['product_id'], 'product_cat', array('fields' => 'slugs'));
        
       if (in_array('element', $categories) || in_array('ascent', $categories) || in_array('elite', $categories) || in_array('summit', $categories)) {
            $has_greenhouse = true;
        }
        
        if (in_array('accessories-upgrade', $categories)) {
            $has_accessories = true;
            if (isset($cart_item['original_price'])) {
                $savings = ($cart_item['original_price'] - $cart_item['data']->get_price()) * $cart_item['quantity'];
                $total_savings += $savings;
            }
        }
    }
    
    if ($has_greenhouse && $has_accessories && $total_savings > 0) {
        echo '<div class="woocommerce-message" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left:4px solid #4caf50; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
            <strong style="font-size: 18px; color: #2e7d32;">Bundle Discount Applied!</strong><br>
            <span style="font-size: 16px; color: #1b5e20;">You\'re saving <strong>' . wc_price($total_savings) . '</strong> (5% off accessories) with your greenhouse purchase!</span>
        </div>';
    }
}
// Handle AJAX add to cart for variable products
add_action('wp_ajax_add_anchor_to_cart', 'grandio_ajax_add_anchor_to_cart');
add_action('wp_ajax_nopriv_add_anchor_to_cart', 'grandio_ajax_add_anchor_to_cart');

function grandio_ajax_add_anchor_to_cart() {
    // Verify nonce
    check_ajax_referer('grandio_package_nonce', 'nonce');
    
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    
    if (!$variation_id) {
        wp_send_json_error(array(
            'message' => 'Invalid product ID'
        ));
        return;
    }
    
    // Get the variation product
    $variation = wc_get_product($variation_id);
    
    if (!$variation || !$variation->is_type('variation')) {
        wp_send_json_error(array(
            'message' => 'Invalid variation product'
        ));
        return;
    }
    
    // Get parent product ID
    $parent_id = $variation->get_parent_id();
    
    // Get variation attributes
    $variation_data = $variation->get_variation_attributes();
    
    // Add to cart
    $cart_item_key = WC()->cart->add_to_cart(
        $parent_id,
        $quantity,
        $variation_id,
        $variation_data
    );
    
    if ($cart_item_key) {
        // Success
        wp_send_json_success(array(
            'message' => 'Product added to cart',
            'cart_item_key' => $cart_item_key,
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_hash' => WC()->cart->get_cart_hash(),
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
        ));
    } else {
        // Failed
        wp_send_json_error(array(
            'message' => 'Could not add product to cart'
        ));
    }
}
/**
 * Automatically add free heater when a specific coupon is applied
 */
add_action('woocommerce_applied_coupon', 'add_free_heater_with_coupon');

function add_free_heater_with_coupon($coupon_code) {
    
    // Define your free heater product ID
    $free_heater_id = 5113433;
    
    // Define the coupon code that triggers the free heater
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    
    // Check if the applied coupon matches
    if (strtoupper($coupon_code) !== strtoupper($trigger_coupon)) {
        return;
    }
    
    // Check if there's a greenhouse in cart
    $has_greenhouse = false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $categories = wp_get_post_terms($cart_item['product_id'], 'product_cat', array('fields' => 'slugs'));
        
        if (in_array('element', $categories) || 
            in_array('ascent', $categories) || 
            in_array('elite', $categories) || 
            in_array('summit', $categories)) {
            $has_greenhouse = true;
            break;
        }
    }
    
    // Only add heater if greenhouse is in cart
    if (!$has_greenhouse) {
        wc_add_notice('This coupon can only be used with a greenhouse purchase.', 'error');
        WC()->cart->remove_coupon($coupon_code);
        return;
    }
    
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
        WC()->cart->add_to_cart($free_heater_id, 1, 0, array(), array(
            'free_gift' => true,
            'free_gift_with_coupon' => $coupon_code
        ));
        
        wc_add_notice('Free Heater added to your cart!', 'success');
    }
}

/**
 * Set heater price to $0 when coupon is applied AND greenhouse is in cart
 */
add_action('woocommerce_before_calculate_totals', 'set_free_heater_price_with_coupon', 10, 1);

function set_free_heater_price_with_coupon($cart) {
    
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }
    
    $free_heater_id = 5113433;
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    
    // Check if the coupon is applied
    $coupon_applied = WC()->cart->has_discount($trigger_coupon);
    
    if (!$coupon_applied) {
        return;
    }
    
    // Check if there's a greenhouse in cart
    $has_greenhouse = false;
    
    foreach ($cart->get_cart() as $cart_item) {
        $categories = wp_get_post_terms($cart_item['product_id'], 'product_cat', array('fields' => 'slugs'));
        
        if (in_array('element', $categories) || 
            in_array('ascent', $categories) || 
            in_array('elite', $categories) || 
            in_array('summit', $categories)) {
            $has_greenhouse = true;
            break;
        }
    }
    
    // Set heater to free if greenhouse is in cart and coupon is applied
    if ($has_greenhouse) {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $free_heater_id) {
                $cart_item['data']->set_price(0);
            }
        }
    }
}

/**
 * Display "FREE with Coupon" label in cart for the heater
 */
add_filter('woocommerce_cart_item_name', 'add_free_gift_coupon_label_to_cart', 10, 3);

function add_free_gift_coupon_label_to_cart($product_name, $cart_item, $cart_item_key) {
    
    $free_heater_id = 5113433;
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    
    if ($cart_item['product_id'] == $free_heater_id) {
        
        // Check if coupon is applied AND greenhouse is in cart
        $coupon_applied = WC()->cart->has_discount($trigger_coupon);
        $has_greenhouse = false;
        
        foreach (WC()->cart->get_cart() as $item) {
            $categories = wp_get_post_terms($item['product_id'], 'product_cat', array('fields' => 'slugs'));
            
            if (in_array('element', $categories) || 
                in_array('ascent', $categories) || 
                in_array('elite', $categories) || 
                in_array('summit', $categories)) {
                $has_greenhouse = true;
                break;
            }
        }
        
        if ($coupon_applied && $has_greenhouse) {
            $product_name .= ' <span class="free-gift-label" style="background: #d4773c; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; margin-left: 8px;">FREE $100 Value</span>';
        }
    }
    
    return $product_name;
}

/**
 * Remove heater from cart if coupon is removed
 */
add_action('woocommerce_removed_coupon', 'remove_free_heater_when_coupon_removed');

function remove_free_heater_when_coupon_removed($coupon_code) {
    
    $free_heater_id = 5113433;
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    
    // Check if the removed coupon is the trigger coupon
    if (strtoupper($coupon_code) !== strtoupper($trigger_coupon)) {
        return;
    }
    
    // Remove the heater from cart
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $free_heater_id) {
            WC()->cart->remove_cart_item($cart_item_key);
            wc_add_notice('Free heater removed from cart.', 'notice');
            break;
        }
    }
}

/**
 * MODIFIED: Only disable removal when BOTH coupon is applied AND greenhouse is in cart
 */
add_filter('woocommerce_cart_item_remove_link', 'disable_free_heater_removal_coupon', 10, 2);

function disable_free_heater_removal_coupon($remove_link, $cart_item_key) {
    
    $free_heater_id = 5113433;
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    
    $cart_item = WC()->cart->get_cart()[$cart_item_key];
    
    if ($cart_item['product_id'] == $free_heater_id) {
        
        // Check if coupon is applied
        $coupon_applied = WC()->cart->has_discount($trigger_coupon);
        
        // Check if greenhouse is in cart
        $has_greenhouse = false;
        foreach (WC()->cart->get_cart() as $item) {
            $categories = wp_get_post_terms($item['product_id'], 'product_cat', array('fields' => 'slugs'));
            
            if (in_array('element', $categories) || 
                in_array('ascent', $categories) || 
                in_array('elite', $categories) || 
                in_array('summit', $categories)) {
                $has_greenhouse = true;
                break;
            }
        }
        
        // ONLY disable removal if BOTH coupon is applied AND greenhouse is in cart
        if ($coupon_applied && $has_greenhouse) {
            // Replace remove link with a message
            return '<span class="remove" style="color: #999; cursor: not-allowed;" title="Remove coupon to remove this item">✓</span>';
        }
    }
    
    return $remove_link;
}

/**
 * MODIFIED: Only lock quantity when BOTH coupon is applied AND greenhouse is in cart
 */
add_filter('woocommerce_cart_item_quantity', 'lock_free_heater_quantity_coupon', 10, 3);

function lock_free_heater_quantity_coupon($product_quantity, $cart_item_key, $cart_item) {
    
    $free_heater_id = 5113433;
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    
    if ($cart_item['product_id'] == $free_heater_id) {
        
        // Check if coupon is applied
        $coupon_applied = WC()->cart->has_discount($trigger_coupon);
        
        // Check if greenhouse is in cart
        $has_greenhouse = false;
        foreach (WC()->cart->get_cart() as $item) {
            $categories = wp_get_post_terms($item['product_id'], 'product_cat', array('fields' => 'slugs'));
            
            if (in_array('element', $categories) || 
                in_array('ascent', $categories) || 
                in_array('elite', $categories) || 
                in_array('summit', $categories)) {
                $has_greenhouse = true;
                break;
            }
        }
        
        // ONLY lock quantity if BOTH coupon is applied AND greenhouse is in cart
        if ($coupon_applied && $has_greenhouse) {
            // Display quantity as text instead of input
            return '<span class="quantity">1</span>';
        }
    }
    
    return $product_quantity;
}

/**
 * Add notice on cart/checkout if greenhouse is in cart but coupon not applied
 */
add_action('woocommerce_before_cart', 'show_free_heater_coupon_notice');
add_action('woocommerce_before_checkout_form', 'show_free_heater_coupon_notice');

function show_free_heater_coupon_notice() {
    
    $trigger_coupon = 'FREEHEAT'; // CHANGE THIS to your actual coupon code
    $free_heater_id = 5113433;
    
    // Check if coupon is already applied
    if (WC()->cart->has_discount($trigger_coupon)) {
        return;
    }
    
    // Check if heater is already in cart
    $heater_in_cart = false;
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $free_heater_id) {
            $heater_in_cart = true;
            break;
        }
    }
    
    if ($heater_in_cart) {
        return; // Don't show notice if heater already in cart
    }
    
    // Check if there's a greenhouse in cart
    $has_greenhouse = false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        $categories = wp_get_post_terms($cart_item['product_id'], 'product_cat', array('fields' => 'slugs'));
        
        if (in_array('element', $categories) || 
            in_array('ascent', $categories) || 
            in_array('elite', $categories) || 
            in_array('summit', $categories)) {
            $has_greenhouse = true;
            break;
        }
    }
    
    // Show notice if greenhouse in cart but coupon not applied
    if ($has_greenhouse) {
        wc_print_notice(
            ' <strong>Special Offer!</strong> Use coupon code <strong>' . $trigger_coupon . '</strong> to get a FREE Milkhouse Heater ($100 value) with your greenhouse!',
            'notice'
        );
    }
}
add_action('woocommerce_review_order_before_payment', 'grandio_synchrony_checkout_widget', 10);

function grandio_synchrony_checkout_widget() {
    // Check if WooCommerce cart exists
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }
    
    // Get cart total
    $cart_total = WC()->cart->total;
    
    // Only show if total is $500+
    if ($cart_total < 500) {
        return;
    }
    
    // Calculate payment based on price tiers
    if ($cart_total >= 2400) {
        $months = 36;
    } elseif ($cart_total >= 1200) {
        $months = 24;
    } else {
        $months = 18;
    }
    
    $annual_rate = 9.99;
    $monthly_rate = $annual_rate / 12 / 100;
    $monthly_payment = $cart_total * ($monthly_rate * pow(1 + $monthly_rate, $months)) / (pow(1 + $monthly_rate, $months) - 1);
    $monthly_payment = ceil($monthly_payment);
    
    ?>
    <div class="synchrony-checkout-widget" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196f3; border-radius: 12px; padding: 20px; margin: 20px 0 25px 0;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2196f3" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>
            <div>
                <h3 style="margin: 0; font-size: 20px; color: #2c3e50; font-weight: 700;">Flexible Payment Options Available</h3>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Pay as low as <strong style="color: #2196f3; font-size: 18px;">$<?php echo $monthly_payment; ?>/month</strong></p>
            </div>
        </div>
        
        <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50;">
            <p style="margin: 0; font-size: 14px; color: #555; line-height: 1.6;">
                To see all options continue by selecting Synchrony below and accepting terms and conditions
            </p>
        </div>
        
        <!-- Synchrony Data Layer -->
        <script>
        window._SFDDL = window._SFDDL || {
            page: {
                pageInfo: {
                    pageName: 'Checkout Page',
                    pageType: 'checkout'
                }
            },
            cart: {
                cartID: '<?php echo WC()->cart->get_cart_hash(); ?>',
                price: {
                    cartTotal: '<?php echo esc_js($cart_total); ?>'
                }
            }
        };
        </script>
        
        <!-- Synchrony Widget Container -->
        <div class="type-product sync-price" has-shadow="true" id="syf-promo-checkout" style="margin-top: 15px;"></div>
    </div>
    
    <style>
        @media (max-width: 768px) {
            .synchrony-checkout-widget {
                padding: 15px;
                margin: 15px 0 20px 0;
            }
            .synchrony-checkout-widget h3 {
                font-size: 18px;
            }
        }
    </style>
    <?php
}

// Add navigation buttons on checkout
add_action('woocommerce_before_checkout_form', 'add_checkout_navigation_buttons', 5);

function add_checkout_navigation_buttons() {
    ?>
    <div class="checkout-navigation-buttons" style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="nav-button back-to-cart" style="display: inline-flex; align-items: center; gap: 10px; background: #fff; color: #2c3e50; padding: 12px 24px; border: 2px solid #95a5a6; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: all 0.3s ease;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Cart
        </a>
        
    
    </div>
    
    <style>
        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .back-to-cart:hover {
            background: #95a5a6;
            color: white;
            border-color: #95a5a6;
        }
        
        .continue-shopping:hover {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
        }
        
        .continue-shopping:hover svg {
            stroke: white;
        }
        
        @media (max-width: 768px) {
            .checkout-navigation-buttons {
                flex-direction: column;
                margin-bottom: 20px;
            }
            .nav-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <?php
}
// Move Synchrony widget to cart totals section
add_action('wp_footer', 'move_synchrony_to_cart_totals');
function move_synchrony_to_cart_totals() {
    if (!is_cart()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var syfWidget = document.querySelector('.sync-price#syf-promo');
            var cartTotals = document.querySelector('.cart-collaterals .cart_totals');
            
          if (syfWidget && cartTotals) {
                // Move widget to top of cart totals
                cartTotals.insertBefore(syfWidget, cartTotals.firstChild);
                syfWidget.style.display = 'block';
                syfWidget.style.marginBottom = '1.5em';
                syfWidget.style.padding = '1em';
                syfWidget.style.background = '#f8f9fa';
                syfWidget.style.borderRadius = '4px';
                syfWidget.style.border = '1px solid #e0e0e0';
                syfWidget.style.width = '100%';
                syfWidget.style.boxSizing = 'border-box';
            }
        }, 1500);
    });
    </script>
    <?php
}
