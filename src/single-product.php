<?php
/**
 * Default Single Product Template
 * Simple layout with description and reviews only
 */

defined('ABSPATH') || exit;

// Enqueue WooCommerce variation scripts BEFORE rendering
global $product;
if ($product && $product->is_type('variable')) {
    wp_enqueue_script('wc-add-to-cart-variation');
}

get_header();
?>

<style>
/* Simplified styles for non-greenhouse products */
.simple-product-page {
    max-width: 1400px;
    margin: 60px auto;
    padding: 0 20px;
}

.simple-product-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

/* TOP SECTION: Image Left | Title/Price Right */
.simple-product-top {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;
}

.simple-product-images {
    width: 100%;
}

.simple-product-images img {
    width: 100%;
    border-radius: 12px;
}

.simple-product-summary {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.product-title {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 20px 0;
}

.product-rating {
    margin-bottom: 15px;
}

.product-price {
    font-size: 32px;
    font-weight: 700;
    color: #27ae60;
    margin: 20px 0;
}

/* Add to Cart */
.simple-add-to-cart {
    margin: 30px 0;
}

/* For SIMPLE products - inline layout */
.simple-add-to-cart form.cart:not(.variations_form) {
    display: flex;
    align-items: center;
    gap: 15px;
}

.simple-add-to-cart form.cart:not(.variations_form) .quantity {
    flex: 0 0 auto;
}

.simple-add-to-cart form.cart:not(.variations_form) button.single_add_to_cart_button {
    flex: 1;
}

/* For VARIABLE products - stacked layout */
.simple-add-to-cart form.variations_form {
    display: block;
}

/* Variable Product Variations - Full Width */
.simple-add-to-cart .variations {
    margin-bottom: 20px;
    width: 100%;
    display: block;
}

.simple-add-to-cart .variations td {
    padding: 10px 0;
    border: none;
    display: block;
    width: 100%;
}

.simple-add-to-cart .variations label {
    font-weight: 600;
    color: #2c3e50;
    display: block;
    margin-bottom: 8px;
}

.simple-add-to-cart .variations select {
    width: 100%;
    padding: 12px 15px;
    font-size: 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.3s;
}

/* Quantity + Add to Cart Row for Variable Products */
.simple-add-to-cart form.variations_form .woocommerce-variation-add-to-cart {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
}

.simple-add-to-cart .quantity {
    flex: 0 0 auto;
}

.simple-add-to-cart .quantity input.qty {
    width: 80px;
    padding: 15px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
}

.simple-add-to-cart button.single_add_to_cart_button {
    flex: 1;
    padding: 15px 30px;
    font-size: 18px;
    font-weight: 700;
    background: #27ae60;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.simple-add-to-cart button.single_add_to_cart_button:hover {
    background: #229954;
    transform: translateY(-2px);
}

.simple-add-to-cart .stock {
    font-weight: 600;
}

.simple-add-to-cart .stock.in-stock {
    color: #27ae60;
}

.simple-add-to-cart .stock.out-of-stock {
    color: #e74c3c;
}

.product-meta {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

/* BOTTOM SECTION: Description (Full Width) */
.simple-product-bottom {
    width: 100%;
}

.simple-description-panel {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.simple-description-panel h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: #2c3e50;
}

/* Fits With Section */
.fits-with-section {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    margin-top: 40px;
}

.fits-with-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 30px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.fits-with-title svg {
    color: #3498db;
}

.fits-with-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.fits-with-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.fits-with-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #3498db;
}

.fits-with-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.fits-with-image {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: #f5f5f5;
}

.fits-with-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.fits-with-card:hover .fits-with-image img {
    transform: scale(1.05);
}

.fits-with-sale-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #e74c3c;
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.fits-with-content {
    padding: 20px;
}

.fits-with-product-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #2c3e50;
    min-height: 45px;
    line-height: 1.4;
}

.fits-with-price {
    font-size: 22px;
    font-weight: 700;
    color: #27ae60;
}

.fits-with-price del {
    color: #999;
    font-size: 16px;
    margin-right: 8px;
}

.fits-with-button {
    padding: 15px 20px;
    background: #f8f9fa;
    text-align: center;
    font-weight: 600;
    color: #3498db;
    border-top: 2px solid #e0e0e0;
    transition: all 0.3s;
}

.fits-with-card:hover .fits-with-button {
    background: #3498db;
    color: white;
}
/* Product Images with Gallery */
.simple-product-images {
    width: 100%;
    position: relative;
}

.simple-sale-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #e74c3c;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
    z-index: 10;
}

.simple-main-image {
    margin-bottom: 15px;
}

.simple-main-image img {
    width: 100%;
    border-radius: 12px;
}

/* Gallery Thumbnails */
.simple-gallery-wrapper {
    position: relative;
    margin-top: 15px;
}

.simple-gallery-track {
    display: flex;
    gap: 10px;
    overflow: hidden;
    scroll-behavior: smooth;
}

.simple-gallery-image {
    flex: 0 0 calc(20% - 8px);
    cursor: pointer;
}

.simple-gallery-image img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: border 0.2s ease;
}

.simple-gallery-image.active img,
.simple-gallery-image:hover img {
    border-color: #3498db;
}

.simple-gallery-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.6);
    color: #fff;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 5;
    font-size: 20px;
}

.simple-gallery-arrow.left { left: -18px; }
.simple-gallery-arrow.right { right: -18px; }

.simple-gallery-arrow:hover {
    background: rgba(0,0,0,0.8);
}

@media (max-width: 768px) {
    .simple-gallery-image {
        flex: 0 0 calc(25% - 8px);
    }
}

@media (max-width: 1023px) {
    .simple-product-top {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .product-title {
        font-size: 28px;
    }
    
    .product-price {
        font-size: 24px;
    }
    
    .simple-product-summary {
        padding: 25px;
    }
    
    .simple-description-panel {
        padding: 25px;
    }
    
    .fits-with-section {
        padding: 25px;
    }
    
    .fits-with-title {
        font-size: 24px;
    }
    
    .fits-with-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
}

@media (max-width: 768px) {
    .simple-product-page {
        padding: 0 15px;
    }
    
    .fits-with-grid {
        grid-template-columns: 1fr;
    }
    
    .fits-with-image {
        height: 200px;
    }
}


/* Hide WooCommerce's duplicate variation price */
.simple-add-to-cart .woocommerce-variation-price {
    display: none !important;
}

/* Hide the default single variation wrapper price */
.simple-add-to-cart .woocommerce-variation.single_variation {
    display: none !important;
}
</style>

<div class="simple-product-page">
    <?php while (have_posts()) : the_post(); global $product; ?>
    
    <div class="simple-product-container">
        
        <!-- TOP SECTION: Image Left | Title/Price/Cart Right -->
        <div class="simple-product-top">
            
           <!-- LEFT: Product Images -->
<div class="simple-product-images">
    <?php if ($product->is_on_sale()) : ?>
        <span class="simple-sale-badge">Sale!</span>
    <?php endif; ?>

    <!-- Main Image -->
    <div class="simple-main-image">
        <?php
        if (has_post_thumbnail()) {
            echo get_the_post_thumbnail(
                $product->get_id(),
                'full',
                array('id' => 'simple-main-img')
            );
        } else {
            echo wc_placeholder_img('full');
        }
        ?>
    </div>

    <!-- Gallery Thumbnails -->
    <?php
    $attachment_ids = $product->get_gallery_image_ids();
    $main_image_id = $product->get_image_id();

    // Merge main image with gallery images
    $all_images = array();
    if ($main_image_id) {
        $all_images[] = $main_image_id;
    }
    if ($attachment_ids) {
        $all_images = array_merge($all_images, $attachment_ids);
    }

    if (count($all_images) > 1) : // Only show gallery if more than 1 image
    ?>
    <div class="simple-gallery-wrapper">
        <button class="simple-gallery-arrow left" aria-label="Previous">‹</button>
        
        <div class="simple-gallery-track">
            <?php foreach ($all_images as $index => $attachment_id) : ?>
                <?php 
                $full = wp_get_attachment_image_url($attachment_id, 'full');
                $thumb = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                $active_class = ($index === 0) ? 'active' : '';
                ?>
                <div class="simple-gallery-image <?php echo $active_class; ?>">
                    <img
                        src="<?php echo esc_url($thumb); ?>"
                        data-full="<?php echo esc_url($full); ?>"
                        alt="Product image <?php echo $index + 1; ?>"
                    >
                </div>
            <?php endforeach; ?>
        </div>
        
        <button class="simple-gallery-arrow right" aria-label="Next">›</button>
    </div>
    <?php endif; ?>
</div>
            
            <!-- RIGHT: Title, Price, Add to Cart -->
            <div class="simple-product-summary">
                <h1 class="product-title"><?php the_title(); ?></h1>
                
                <?php if ($product->get_rating_count()) : ?>
                    <div class="product-rating">
                        <?php woocommerce_template_single_rating(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="product-price" <?php if ($product->is_type('variable')) : ?>data-original-price="<?php echo esc_attr($product->get_price_html()); ?>"<?php endif; ?>>
                    <?php echo $product->get_price_html(); ?>
                </div>
                
                <!-- Add to Cart (Handles both simple and variable products) -->
                <div class="simple-add-to-cart">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
                
                <!-- Product Meta -->
                <div class="product-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </div>
            </div>
            
        </div>
        
        <!-- BOTTOM SECTION: Description (Full Width) -->
        <div class="simple-product-bottom">
            
            <div class="simple-description-panel">
                <h3>Description</h3>
                <?php the_content(); ?>
            </div>
            
            <!-- Fits With / Related Products Section -->
            <?php
            // Get related products or manually assigned products
            $related_products = wc_get_related_products($product->get_id(), 4);
            
            if (!empty($related_products)) :
            ?>
            <div class="fits-with-section">
                <h3 class="fits-with-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 16v-4"></path>
                        <path d="M12 8h.01"></path>
                    </svg>
                    Works Great With
                </h3>
                
                <div class="fits-with-grid">
                    <?php foreach ($related_products as $related_id) :
                        $related_product = wc_get_product($related_id);
                        if (!$related_product) continue;
                        
                        $related_image = wp_get_attachment_image_url($related_product->get_image_id(), 'medium');
                        $related_link = get_permalink($related_id);
                    ?>
                    
                    <div class="fits-with-card">
                        <a href="<?php echo esc_url($related_link); ?>" class="fits-with-link">
                            <div class="fits-with-image">
                                <?php if ($related_image) : ?>
                                    <img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr($related_product->get_name()); ?>">
                                <?php else : ?>
                                    <?php echo wc_placeholder_img('medium'); ?>
                                <?php endif; ?>
                                
                                <?php if ($related_product->is_on_sale()) : ?>
                                    <span class="fits-with-sale-badge">Sale</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="fits-with-content">
                                <h4 class="fits-with-product-title"><?php echo esc_html($related_product->get_name()); ?></h4>
                                <div class="fits-with-price">
                                    <?php echo $related_product->get_price_html(); ?>
                                </div>
                            </div>
                            
                            <div class="fits-with-button">
                                View Product →
                            </div>
                        </a>
                    </div>
                    
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
    </div>
    
    <?php endwhile; ?>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Variation script starting...');
    
    // Wait a moment for WooCommerce to initialize
    setTimeout(function() {
        var variationForm = $('form.variations_form');
        
        if (variationForm.length) {
            var priceContainer = $('.product-price');
            var originalPrice = priceContainer.attr('data-original-price');
            
            console.log('Variation form found');
            console.log('Original price:', originalPrice);
            
            // Listen for variation changes (WooCommerce triggers these automatically)
            variationForm.on('found_variation', function(event, variation) {
                console.log('Variation found:', variation);
                
                // Update price when variation is selected
                if (priceContainer.length && variation.price_html) {
                    priceContainer.html(variation.price_html);
                    console.log('Price updated to:', variation.price_html);
                }
            });
            
            // Reset price when variation is cleared
            variationForm.on('reset_data', function() {
                console.log('Variation reset');
                if (priceContainer.length && originalPrice) {
                    priceContainer.html(originalPrice);
                }
            });
            
            // Handle clear selection link
            $('.reset_variations').on('click', function() {
                console.log('Clear clicked');
                if (priceContainer.length && originalPrice) {
                    priceContainer.html(originalPrice);
                }
            });
            
            console.log('Variation handlers attached');
        } else {
            console.log('No variation form found');
        }
    }, 100);
    // Image Gallery
    var mainImg = $('#simple-main-img');
    var galleryThumbs = $('.simple-gallery-image');
    
    if (mainImg.length && galleryThumbs.length) {
        // Click thumbnail to change main image
        galleryThumbs.find('img').on('click', function() {
            var fullUrl = $(this).data('full');
            mainImg.attr('src', fullUrl);
            mainImg.removeAttr('srcset sizes');
            
            galleryThumbs.removeClass('active');
            $(this).closest('.simple-gallery-image').addClass('active');
        });
        
        // Arrow navigation
        var track = $('.simple-gallery-track');
        $('.simple-gallery-arrow.left').on('click', function() {
            track.scrollBy({ left: -200, behavior: 'smooth' });
        });
        $('.simple-gallery-arrow.right').on('click', function() {
            track.scrollBy({ left: 200, behavior: 'smooth' });
        });
    }
});
</script>

<?php get_footer(); ?>
