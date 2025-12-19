<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="grandio-site-header">
    <div class="grandio-header-container">
        <!-- Logo -->
        <div class="grandio-logo">
            <?php
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="grandio-site-title">
                    <?php bloginfo('name'); ?>
                </a>
                <?php
            }
            ?>
        </div>
        
        <!-- Breadcrumbs -->
        <div class="grandio-breadcrumbs">
            <?php
            if (function_exists('woocommerce_breadcrumb')) {
                woocommerce_breadcrumb(array(
                    'delimiter'   => '<span class="breadcrumb-separator">/</span>',
                    'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="breadcrumb">',
                    'wrap_after'  => '</nav>',
                    'before'      => '<span class="breadcrumb-item">',
                    'after'       => '</span>',
                    'home'        => 'Home',
                ));
            }
            ?>
        </div>
    </div>
</header>

<style>
.grandio-site-header {
    background: white;
    border-bottom: 2px solid #e0e0e0;
    padding: 30px 0 20px 0;
}

.grandio-header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* Logo */
.grandio-logo {
    display: flex;
    justify-content: flex-start;
}

.grandio-logo img,
.grandio-logo .custom-logo {
    max-height: 70px;
    width: auto;
    display: block;
}

.grandio-site-title {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.3s;
}

.grandio-site-title:hover {
    color: #3498db;
}

/* Breadcrumbs */
.grandio-breadcrumbs {
    width: 100%;
}

.woocommerce-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 14px;
    color: #666;
}

.breadcrumb-item {
    color: #666;
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb-item a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb-item a:hover {
    color: #3498db;
}

.breadcrumb-separator {
    color: #ccc;
    margin: 0 4px;
}

/* Current page (last breadcrumb) */
.woocommerce-breadcrumb > span:last-child {
    color: #2c3e50;
    font-weight: 600;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .grandio-site-header {
        padding: 20px 0 15px 0;
    }
    
    .grandio-header-container {
        padding: 0 20px;
        gap: 20px;
    }
    
    .grandio-logo img {
        max-height: 50px;
    }
    
    .woocommerce-breadcrumb {
        font-size: 13px;
    }
}
</style>

<div id="content" class="site-content">
