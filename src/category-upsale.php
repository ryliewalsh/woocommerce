<?php
/**
 * Category Showcase Section
 * Shows other greenhouse categories (not the current one)
 * To display on woocommerce add this in body: <?php get_template_part('woocommerce/greenhouse-category-upsale'); ?>
 */

defined('ABSPATH') || exit;

global $product;

// Get current product's categories
$current_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));

// Define main greenhouse categories
$main_categories = array(
    'element',
    'ascent',
    'summit',
    'elite',
);

// Or get all product categories dynamically
$all_categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'slug' => $main_categories,
));

// Filter out current category
$other_categories = array();
foreach ($all_categories as $category) {
    if (!in_array($category->term_id, $current_categories)) {
        $other_categories[] = $category;
    }
}

// Limit to 3 categories
$other_categories = array_slice($other_categories, 0, 3);

if (empty($other_categories)) {
    return; // Don't show if no other categories
}

// Define highlights for each category (customize these)
$category_highlights = array(
    'element-series' => array(
        'tagline' => 'Perfect for Beginners',
        'features' => array('Easy Assembly', 'Affordable', 'Durable Frame'),
        'icon' => '🌱'
    ),
    'ascent-series' => array(
        'tagline' => 'Step Up Your Growing',
        'features' => array('Enhanced Ventilation', 'More Space', 'Professional Grade'),
        'icon' => '🌿'
    ),
    'summit-series' => array(
        'tagline' => 'Premium Performance',
        'features' => array('Premium Materials', 'Advanced Features', 'Extended Warranty'),
        'icon' => '🏔️'
    ),
    'peak-series' => array(
        'tagline' => 'Ultimate Greenhouse',
        'features' => array('Commercial Quality', 'Maximum Capacity', 'Lifetime Support'),
        'icon' => '⛰️'
    ),
);
?>

<div class="grandio-category-showcase">
    <div class="category-showcase-header">
        <h2 class="showcase-title">Explore Other Greenhouse Collections</h2>
        <p class="showcase-subtitle">Find the perfect greenhouse for your growing needs</p>
    </div>
    
    <div class="category-showcase-grid">
        <?php foreach ($other_categories as $category) : 
            // Get category thumbnail
            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
            $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : wc_placeholder_img_src();
            
            // Get category highlights
            $highlights = isset($category_highlights[$category->slug]) ? $category_highlights[$category->slug] : array(
                'tagline' => 'Quality Greenhouses',
                'features' => array('Durable', 'Weather-Resistant', 'Easy Setup'),
                'icon' => '🏡'
            );
            
            // Get product count
            $category_link = get_term_link($category);
        ?>
        
        <div class="category-card">
            <a href="<?php echo esc_url($category_link); ?>" class="category-card-link">
                <!-- Category Image -->
                <div class="category-image" style="background-image: url('<?php echo esc_url($image_url); ?>');">
                    <div class="category-overlay">
                        <span class="category-icon"><?php echo $highlights['icon']; ?></span>
                    </div>
                </div>
                
                <!-- Category Content -->
                <div class="category-content">
                    <div class="category-header">
                        <h3 class="category-name"><?php echo esc_html($category->name); ?></h3>
                        <p class="category-tagline"><?php echo esc_html($highlights['tagline']); ?></p>
                    </div>
                    
                    <!-- Highlights -->
                    <ul class="category-highlights">
                        <?php foreach ($highlights['features'] as $feature) : ?>
                        <li class="highlight-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <?php echo esc_html($feature); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- CTA -->
                    <div class="category-cta">
                        <span class="cta-text">View Collection</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
        
        <?php endforeach; ?>
    </div>
</div>

<style>
.grandio-category-showcase {
    margin: 80px 0;
    padding: 60px 40px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.category-showcase-header {
    text-align: center;
    margin-bottom: 50px;
}

.showcase-title {
    font-size: 36px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 15px 0;
}

.showcase-subtitle {
    font-size: 18px;
    color: #666;
    margin: 0;
}

.category-showcase-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.category-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.category-card-link {
    text-decoration: none;
    display: block;
}

/* Category Image */
.category-image {
    position: relative;
    height: 280px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.category-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.4) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.category-card:hover .category-overlay {
    background: linear-gradient(180deg, rgba(52,152,219,0.3) 0%, rgba(52,152,219,0.6) 100%);
}

.category-icon {
    font-size: 64px;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    transition: transform 0.3s;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
}

/* Category Content */
.category-content {
    padding: 30px;
}

.category-header {
    margin-bottom: 20px;
}

.category-name {
    font-size: 26px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
    transition: color 0.3s;
}

.category-card:hover .category-name {
    color: #3498db;
}

.category-tagline {
    font-size: 15px;
    color: #7f8c8d;
    margin: 0;
    font-style: italic;
}

/* Highlights */
.category-highlights {
    list-style: none;
    padding: 0;
    margin: 0 0 25px 0;
}

.highlight-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    color: #555;
    margin-bottom: 12px;
}

.highlight-item svg {
    color: #27ae60;
    flex-shrink: 0;
}

/* CTA */
.category-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-radius: 8px;
    transition: all 0.3s;
}

.category-card:hover .category-cta {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.cta-text {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    transition: color 0.3s;
}

.category-card:hover .cta-text {
    color: white;
}

.category-cta svg {
    color: #3498db;
    transition: all 0.3s;
}

.category-card:hover .category-cta svg {
    color: white;
    transform: translateX(5px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .grandio-category-showcase {
        margin: 60px 0;
        padding: 40px 20px;
    }
    
    .showcase-title {
        font-size: 28px;
    }
    
    .category-showcase-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .category-image {
        height: 220px;
    }
    
    .category-name {
        font-size: 22px;
    }
}
</style>
