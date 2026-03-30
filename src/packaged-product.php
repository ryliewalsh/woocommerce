<?php
/**
 * Custom Single Product Template
 * SEO-Optimized with separate products and link-based size selector
 */

defined('ABSPATH') || exit;
get_header();
?>

<style>
/* === CUSTOM PRODUCT PAGE STYLES === */
.grandio-custom-product {
    max-width: 1400px;
    margin: 60px auto 150px;
    padding: 0 20px;
}

/* Desktop: Two-column layout */
@media (min-width: 1024px) {
    .grandio-product-container {
        display: flex;
        gap: 60px;
        align-items: flex-start;
    }
    
    /* LEFT: Sticky Images */
    .grandio-images-column {
        position: sticky;
        top: 100px;
        flex: 0 0 45%;
        max-width: 600px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    
    /* RIGHT: Scrolling Content */
    .grandio-content-column {
        flex: 1;
        min-width: 0;
    }
}

/* Product Images */
.grandio-product-images {
    position: relative;
}

.grandio-product-images img {
    width: 100%;
    height: auto;
    border-radius: 12px;
}

.grandio-sale-badge {
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

/* Product Summary */
.grandio-product-summary {
    background: #ffffff;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 40px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.grandio-product-title {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: #2c3e50;
    line-height: 1.2;
}

.grandio-product-price {
    font-size: 32px;
    font-weight: 700;
    color: #27ae60;
    margin: 20px 0;
}

.grandio-product-price del {
    color: #999;
    font-size: 24px;
    margin-right: 12px;
}

.grandio-product-description {
    font-size: 16px;
    line-height: 1.8;
    color: #555;
    margin: 20px 0;
}

.grandio-product-meta {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
    font-size: 14px;
    color: #777;
}

/* Size Selector */
.grandio-size-selector {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 30px;
    border-radius: 12px;
    margin: 30px 0;
    border: 2px solid #dee2e6;
}

.size-selector-title {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.size-selector-title svg {
    color: #3498db;
}

.size-options {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.size-option {
    background: white;
    border: 3px solid #dee2e6;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: block;
}

.size-option:hover {
    border-color: #3498db;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
    text-decoration: none;
}

.size-option.active {
    border-color: #27ae60;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
}

.size-option-inner {
    padding: 20px 15px;
    text-align: center;
}

.size-option-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #27ae60;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.size-option-name {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
}

.size-option-price {
    font-size: 18px;
    font-weight: 600;
    color: #27ae60;
}

.size-option-sale {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #e74c3c;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.size-selector-note {
    text-align: center;
    margin: 20px 0 0 0;
    padding: 15px;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 8px;
    color: #2c3e50;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.size-selector-note svg {
    color: #3498db;
    flex-shrink: 0;
}

/* Package Builder Section */
.grandio-package-builder {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 50px 40px;
    border-radius: 12px;
    margin-bottom: 40px;
}

.grandio-package-title {
    font-size: 32px;
    font-weight: 700;
    text-align: center;
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.grandio-package-subtitle {
    text-align: center;
    font-size: 16px;
    color: #666;
    margin: 0 0 40px 0;
}

.grandio-package-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

/* Product Card */
.grandio-product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 3px solid transparent;
    position: relative;
}

.grandio-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.grandio-product-card.selected {
    border-color: #27ae60;
    box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
}

.grandio-product-card.main-product {
    border-color: #3498db;
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
}

.grandio-product-card.main-product .product-checkbox-container {
    background: #3498db;
}

/* Checkbox Container */
.product-checkbox-container {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.95);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    z-index: 10;
    transition: all 0.3s;
}

.grandio-product-card:hover .product-checkbox-container {
    transform: scale(1.1);
}

.grandio-product-card.selected .product-checkbox-container {
    background: #27ae60;
}

.product-checkbox {
    width: 28px;
    height: 28px;
    cursor: pointer;
    accent-color: #27ae60;
}

.product-checkbox:disabled {
    cursor: default;
}

/* Product Image */
.product-card-image {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: #f5f5f5;
}

.product-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.grandio-product-card:hover .product-card-image img {
    transform: scale(1.05);
}

.product-sale-badge {
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

.product-main-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #3498db;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Product Content */
.product-card-content {
    padding: 25px;
}

.product-card-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 12px 0;
    color: #2c3e50;
    min-height: 50px;
}

.product-card-description {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
    min-height: 60px;
}

.product-card-price {
    font-size: 26px;
    font-weight: 700;
    color: #27ae60;
    margin: 15px 0 0 0;
}

.product-card-price del {
    color: #999;
    font-size: 18px;
    margin-right: 10px;
}

.product-savings {
    display: inline-block;
    background: #fff3cd;
    color: #856404;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 600;
    margin-left: 10px;
}

/* Sticky Tabs Navigation */
.grandio-sticky-tabs {
    position: sticky;
    top: 50px;
    background: white;
    z-index: 998;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    border-radius: 12px;
    overflow: hidden;
}

.grandio-sticky-tabs.stuck {
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.grandio-tabs-nav {
    display: flex;
    justify-content: center;
    gap: 0;
    margin: 0;
    padding: 0;
    list-style: none;
    background: #f8f9fa;
}

.grandio-tabs-nav li {
    flex: 1;
    margin: 0;
}

.grandio-tabs-nav a {
    display: block;
    padding: 20px 30px;
    text-align: center;
    color: #555;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    background: #f8f9fa;
}

.grandio-tabs-nav a:hover {
    background: #e9ecef;
    color: #2c3e50;
    text-decoration: none;
}

.grandio-tabs-nav a.active {
    background: white;
    color: #3498db;
    border-bottom-color: #3498db;
}

/* Product Tabs Content */
.grandio-product-tabs {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.grandio-tab-panel {
    display: none;
    line-height: 1.8;
}

.grandio-tab-panel.active {
    display: block;
}

.grandio-tab-panel h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: #2c3e50;
}

/* Sticky Checkout Bar */
.grandio-sticky-checkout {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
    z-index: 9999;
    transition: transform 0.3s ease;
    transform: translateY(0);
}

.grandio-sticky-checkout.hidden {
    transform: translateY(100%);
}

.sticky-checkout-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 30px;
}

/* Selected Items Summary */
.sticky-items-summary {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 20px;
}

.sticky-items-count {
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 8px;
    padding: 15px 20px;
    color: white;
}

.sticky-items-count-number {
    font-size: 32px;
    font-weight: 700;
    display: block;
    line-height: 1;
    margin-bottom: 5px;
}

.sticky-items-count-label {
    font-size: 13px;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sticky-items-list {
    flex: 1;
    color: white;
    font-size: 14px;
}

.sticky-items-list strong {
    display: block;
    font-size: 16px;
    margin-bottom: 5px;
}

/* Checkout Actions */
.sticky-checkout-actions {
    display: flex;
    align-items: center;
    gap: 25px;
}

.sticky-total {
    text-align: right;
}

.sticky-total-label {
    font-size: 14px;
    color: rgba(255,255,255,0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.sticky-total-amount {
    font-size: 36px;
    font-weight: 700;
    color: #27ae60;
    line-height: 1;
}

.sticky-quantity-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sticky-quantity-selector label {
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.sticky-qty-input {
    width: 70px;
    padding: 12px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 8px;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 18px;
    font-weight: 700;
    text-align: center;
}

.sticky-qty-input:focus {
    outline: none;
    border-color: #3498db;
    background: rgba(255,255,255,0.15);
}

.sticky-checkout-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    padding: 18px 45px;
    border: none;
    border-radius: 10px;
    font-size: 20px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sticky-checkout-btn:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(39, 174, 96, 0.6);
}

.sticky-checkout-btn:active {
    transform: translateY(-1px);
}

.sticky-checkout-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.sticky-checkout-btn svg {
    width: 24px;
    height: 24px;
}

/* Mobile Responsive */
@media (max-width: 1023px) {
    .grandio-custom-product {
        margin-bottom: 200px;
    }
    
    .grandio-product-container {
        flex-direction: column;
    }
    
    .grandio-images-column,
    .grandio-content-column {
        width: 100%;
        max-width: 100%;
        position: relative !important;
    }
    
    .grandio-product-summary {
        padding: 25px;
    }
    
    .grandio-product-title {
        font-size: 28px;
    }
    
    .grandio-package-grid {
        grid-template-columns: 1fr;
    }
    
    .grandio-tabs-nav {
        flex-direction: column;
    }
    
    .grandio-tabs-nav a {
        padding: 15px 20px;
    }
    
    .size-options {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .size-option-name {
        font-size: 20px;
    }
    
    .sticky-checkout-container {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
    }
    
    .sticky-items-summary {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    
    .sticky-items-count {
        text-align: center;
    }
    
    .sticky-items-list {
        text-align: center;
    }
    
    .sticky-checkout-actions {
        width: 100%;
        flex-direction: column;
        gap: 15px;
    }
    
    .sticky-total {
        text-align: center;
        width: 100%;
    }
    
    .sticky-quantity-selector {
        justify-content: center;
    }
    
    .sticky-checkout-btn {
        width: 100%;
        justify-content: center;
        padding: 16px 30px;
        font-size: 18px;
    }
}

/* Smooth scrollbar for sticky images */
.grandio-images-column::-webkit-scrollbar {
    width: 8px;
}

.grandio-images-column::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 1px;
}

.grandio-images-column::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 1px;
}

.grandio-images-column::-webkit-scrollbar-thumb:hover {
    
    background: #555;
}
/* === GALLERY SLIDER === */
.grandio-gallery-wrapper {
    position: relative;
    margin-top: 14px;
    overflow: hidden;
}

.grandio-gallery-track {
    display: flex;
    gap: 10px;
    overflow: hidden;
     
    scroll-behavior: smooth;
}

.grandio-gallery-image {
    flex: 0 0 calc(25% - 8px); /* 4 visible */
}

.grandio-gallery-image img {
    width: 100%;
    cursor: pointer;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: border 0.2s ease;
}

.grandio-gallery-image.active img {
    border-color: #3498db;
}

/* Arrows */
.gallery-arrow {
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
}

.gallery-arrow.left { left: -18px; }
.gallery-arrow.right { right: -18px; }

.gallery-arrow:hover {
    background: rgba(0,0,0,0.8);
}
/* Package Section Styling */
.package-section {
    margin-bottom: 40px;
}

.package-section-title {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 20px 0;
    padding: 15px 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    border-left: 4px solid #3498db;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.main-product-section .package-section-title {
    border-left-color: #3498db;
}

.essential-section .package-section-title {
    border-left-color: #e74c3c;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
}

.recommended-section .package-section-title {
    border-left-color: #3498db;
}

.section-badge {
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.essential-badge {
    background: #e74c3c;
    color: white;
}

.recommended-badge {
    background: #3498db;
    color: white;
}

/* Main Product Card Updates */
.grandio-product-card.main-product .product-selected-indicator {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #3498db;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.grandio-product-card.main-product .product-selected-indicator svg {
    width: 20px;
    height: 20px;
}

/* Compact Product List */
.compact-product-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.compact-product-card {
    background: white;
    border: 3px solid #e0e0e0;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
    cursor: pointer;
}

.compact-product-card:hover {
    border-color: #3498db;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    transform: translateY(-2px);
}

.compact-product-card.selected {
    border-color: #3498db;
    border-width: 3px;
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

/* Compact Card Main Row */
.compact-card-main {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    position: relative;
}

/* Selected Indicator */
.compact-selected-indicator {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 32px;
    height: 32px;
    background: #e0e0e0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    opacity: 0.3;
}

.compact-product-card.selected .compact-selected-indicator {
    background: #3498db;
    opacity: 1;
}

.compact-selected-indicator svg {
    color: white;
    width: 18px;
    height: 18px;
}

/* Thumbnail */
.compact-thumbnail {
    position: relative;
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: #f5f5f5;
    border: 2px solid #e0e0e0;
    transition: all 0.3s;
}

.compact-product-card.selected .compact-thumbnail {
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.compact-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.compact-sale-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #e74c3c;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

/* Product Info */
.compact-info {
    flex: 1;
    min-width: 0;
    margin-right: 50px; /* Space for indicator */
}

.compact-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.compact-product-card.selected .compact-title {
    color: #3498db;
}

.compact-price {
    font-size: 20px;
    font-weight: 700;
    color: #27ae60;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.compact-savings {
    font-size: 13px;
    background: #fff3cd;
    color: #856404;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
}

/* Expand Button */
.compact-expand-btn {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border: 2px solid #e0e0e0;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 5;
    position: relative;
}

.compact-expand-btn:hover {
    border-color: #3498db;
    background: #e3f2fd;
}

.compact-expand-btn svg {
    transition: transform 0.3s;
}

.compact-expand-btn.expanded svg {
    transform: rotate(180deg);
}

/* Expandable Description */
.compact-description {
    border-top: 2px solid #e0e0e0;
    background: #f8f9fa;
}

.compact-product-card.selected .compact-description {
    border-top-color: #3498db;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.compact-description-content {
    padding: 20px;
    color: #555;
    line-height: 1.6;
}

.compact-description-content p {
    margin: 0 0 10px 0;
}

.compact-description-content p:last-child {
    margin-bottom: 0;
}

/* Click Animation */
.compact-product-card:active {
    transform: scale(0.98);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .package-section-title {
        font-size: 18px;
        padding: 12px 15px;
    }
    
    .section-badge {
        font-size: 10px;
        padding: 3px 8px;
    }
    
    .compact-card-main {
        gap: 10px;
        padding: 15px;
    }
    
    .compact-thumbnail {
        width: 60px;
        height: 60px;
    }
    
    .compact-title {
        font-size: 16px;
    }
    
    .compact-price {
        font-size: 18px;
    }
    
    .compact-info {
        margin-right: 45px;
    }
    
    .compact-selected-indicator {
        width: 28px;
        height: 28px;
        top: 12px;
        right: 12px;
    }
    
    .compact-selected-indicator svg {
        width: 16px;
        height: 16px;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .grandio-gallery-image {
        flex: 0 0 calc(33.333% - 7px); /* 3 visible */
    }
}
/* Variable product cards are NOT clickable */
.compact-product-card.variable-product {
    cursor: default !important;
}

.compact-product-card.variable-product .compact-card-main {
    cursor: default !important;
    pointer-events: none; /* Disable clicking on the main card area */
}

/* But allow clicking/interacting with the dropdown */
.compact-product-card.variable-product .compact-description {
    pointer-events: auto;
}

.compact-product-card.variable-product .variation-select {
    cursor: pointer;
}

/* Remove hover effect from variable product cards */
.compact-product-card.variable-product:hover {
    transform: none;
    box-shadow: none;
}

/* Show different hover only on dropdown */
.compact-product-card.variable-product .variation-select:hover {
    border-color: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.2);
}

/* Selected state still shows blue border */
.compact-product-card.variable-product.selected {
    border-color: #3498db;
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

/* Always show dropdown - no display:none */
.compact-product-card.variable-product .compact-description {
    display: block !important;
    background: #f8f9fa;
    border-top: 2px solid #e0e0e0;
}

/* Variable Product Dropdown */
.variable-product-dropdown {
    padding: 20px 15px 15px 15px;
}

.variation-label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 14px;
}

.variation-label strong {
    color: #e74c3c;
}

.variation-select {
    width: 100%;
    padding: 12px 15px;
    font-size: 15px;
    border: 3px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    color: #2c3e50;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.variation-select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.variation-select option {
    padding: 10px;
    font-weight: 500;
}

.variation-select option:first-child {
    color: #999;
    font-style: italic;
}

/* Hide price for variable products */
.compact-product-card.variable-product .compact-price {
    display: none;
}


/* Mobile Responsive */
@media (max-width: 768px) {
    .variations-grid {
        grid-template-columns: 1fr;
    }
}

</style>

<div class="grandio-custom-product">
    <?php while (have_posts()) : the_post(); global $product; ?>
    
    <div class="grandio-product-container">

    <!-- LEFT COLUMN: Product Images (Sticky) -->
    <div class="grandio-images-column">
         <div class="grandio-product-images">

            <?php if ($product->is_on_sale()) : ?>
                <span class="grandio-sale-badge">Sale!</span>
            <?php endif; ?>

            <!-- MAIN IMAGE -->
            <div class="grandio-main-image">
                <?php
                if (has_post_thumbnail()) {
                    echo get_the_post_thumbnail(
                        $product->get_id(),
                        'full',
                        array(
                            'id' => 'grandio-main-img'
                        )
                    );
                } else {
                    echo wc_placeholder_img('full');
                }
                ?>
            </div>

            <!-- GALLERY IMAGES -->
<?php
$attachment_ids = $product->get_gallery_image_ids();
if ($attachment_ids) :
?>
<div class="grandio-gallery-wrapper">

    <button class="gallery-arrow left" aria-label="Previous images">&#10094;</button>

    <div class="grandio-gallery-track">
        <?php foreach ($attachment_ids as $attachment_id) : ?>
            <?php $full = wp_get_attachment_image_url($attachment_id, 'full'); ?>
            <div class="grandio-gallery-image">
                <img
                    src="<?php echo esc_url($full); ?>"
                    data-full="<?php echo esc_url($full); ?>"
                    loading="lazy"
                    alt=""
                >
            </div>
        <?php endforeach; ?>
    </div>

    <button class="gallery-arrow right" aria-label="Next images">&#10095;</button>

</div>
<?php endif; ?>


        </div>
     </div>
   


        
        <!-- RIGHT COLUMN: Product Content (Scrolls) -->
        <div class="grandio-content-column">
            
            <!-- Product Summary -->
            <div class="grandio-product-summary">
                <h1 class="grandio-product-title"><?php the_title(); ?></h1>
               
               
                     <?php if ($product->get_rating_count()) : ?>
    <a href="#reviews" class="grandio-product-rating scroll-to-reviews">
        <?php woocommerce_template_single_rating(); ?>
    </a>
<?php endif; ?>
                <div class="grandio-product-price">
                    <?php echo $product->get_price_html(); ?>
                </div>
                
                <!-- SIZE SELECTOR WITH LINKS -->
                <?php
             // Store the current product ID before any loops
$current_product_id = $product->get_id();

// Get all products in same category (greenhouse sizes)
$categories = wp_get_post_terms($current_product_id, 'product_cat', array('fields' => 'ids'));

if (!empty($categories)) :
    // Get ALL products in category (including current one)
    $all_size_products = new WP_Query(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $categories,
            )
        ),
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock',
            )
        )
    ));
    
    // Collect all products with their size order
    $products_sorted = array();
    
    while ($all_size_products->have_posts()) : 
        $all_size_products->the_post();
        $temp_product = wc_get_product(get_the_ID());
        
        // Get size order attribute
        $size_order = $temp_product->get_attribute('size-order');
        $size_order = $size_order ? intval($size_order) : 999; // Default to 999 if not set
        
        // Extract size display from product name
        preg_match('/(\d+\s*[x×]\s*\d+)/i', $temp_product->get_name(), $matches);
        $size_display = !empty($matches[1]) ? str_replace(' ', '', $matches[1]) : 'Standard';
        
        $products_sorted[] = array(
            'product' => $temp_product,
            'product_id' => $temp_product->get_id(),
            'size_order' => $size_order,
            'size_display' => $size_display,
            'is_current' => ($temp_product->get_id() == $current_product_id) // Compare to stored ID
        );
    endwhile;
    wp_reset_postdata();
    
    // Sort by size order attribute
    usort($products_sorted, function($a, $b) {
        return $a['size_order'] - $b['size_order'];
    });
    
    if (!empty($products_sorted)) :
?>
<div class="grandio-size-selector">
    <h3 class="size-selector-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="9" y1="21" x2="9" y2="9"></line>
        </svg>
        Available Sizes - Choose Yours
    </h3>
    
    <div class="size-options">
        <?php foreach ($products_sorted as $product_data) : 
            $size_product = $product_data['product'];
            $is_current = $product_data['is_current'];
            $active_class = $is_current ? 'active' : '';
        ?>
        
        <a href="<?php echo esc_url(get_permalink($product_data['product_id'])); ?>" 
           class="size-option <?php echo $active_class; ?>">
            <div class="size-option-inner">
               
                
                <div class="size-option-name">
                    <?php echo esc_html($product_data['size_display']); ?>
                </div>
                
                <div class="size-option-price">
                    <?php echo wc_price($size_product->get_price()); ?>
                </div>
                
                <?php if ($size_product->is_on_sale()) : ?>
                    <div class="size-option-sale">Sale!</div>
                <?php endif; ?>
            </div>
        </a>
        
        <?php endforeach; ?>
    </div>
    
    <p class="size-selector-note">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        Each size comes with size-specific accessories and options
    </p>
</div>
<?php 
    endif;
endif;
                ?>
                
                <div class="grandio-product-description">
                    <?php echo $product->get_short_description(); ?>
                </div>
                
                <!-- Product Meta -->
                <div class="grandio-product-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </div>
            </div>
            
        <!-- Package Builder Section -->
<div class="grandio-package-builder">
    <h2 class="grandio-package-title">Build Your Complete Package</h2>
    <p class="grandio-package-subtitle">Click items to add them to your package</p>
    
   <?php
$cross_sell_ids = $product->get_cross_sell_ids();

// Separate cross-sells into categories
$anchor_products = array();
$essential_products = array();
$recommended_products = array();

if (!empty($cross_sell_ids)) :
    foreach ($cross_sell_ids as $cross_sell_id) :
        $cross_product = wc_get_product($cross_sell_id);
        if (!$cross_product || !$cross_product->is_in_stock()) continue;
        
        // Get the product ID to check (parent if variation, otherwise self)
        $check_product_id = $cross_sell_id;
        
        // If this is a variation, get the parent product ID
        if ($cross_product->is_type('variation')) {
            $check_product_id = $cross_product->get_parent_id();
        }
        
        // Check product tags/categories to determine section
        $tags = wp_get_post_terms($check_product_id, 'product_tag', array('fields' => 'names'));
        $categories = wp_get_post_terms($check_product_id, 'product_cat', array('fields' => 'names'));
        
        $is_anchor = false;
        $is_essential = false;
        
        // Check combined tags and categories
        foreach (array_merge($tags, $categories) as $term) {
            if (stripos($term, 'anchor') !== false || 
                stripos($term, 'moisture') !== false || 
                stripos($term, 'installation') !== false) {
                $is_anchor = true;
                break;
            }
            if (stripos($term, 'essential') !== false || 
                stripos($term, 'required') !== false) {
                $is_essential = true;
                break;
            }
        }
        
        if ($is_anchor) {
            $anchor_products[] = $cross_product;
        } elseif ($is_essential) {
            $essential_products[] = $cross_product;
        } else {
            $recommended_products[] = $cross_product;
        }
    endforeach;
endif;
?>

<!-- MAIN PRODUCT SECTION -->
<div class="package-section main-product-section">
    <h3 class="package-section-title">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        Your Selected Greenhouse
    </h3>
    
    <div class="grandio-package-grid">
        <!-- Main Product Card -->
        <div class="grandio-product-card main-product selected" 
             data-product-id="<?php echo esc_attr($product->get_id()); ?>"
             data-product-name="<?php echo esc_attr($product->get_name()); ?>"
             data-product-price="<?php echo esc_attr($product->get_price()); ?>">
            
            <div class="product-selected-indicator">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Included</span>
            </div>
            
            <div class="product-card-image">
                <span class="product-main-badge">Main Product</span>
                <?php
                if (has_post_thumbnail()) {
                    echo get_the_post_thumbnail($product->get_id(), 'medium');
                } else {
                    echo wc_placeholder_img('medium');
                }
                ?>
            </div>
            
            <div class="product-card-content">
                <h3 class="product-card-title"><?php echo $product->get_name(); ?></h3>
                <div class="product-card-price">
                    <?php echo $product->get_price_html(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

// Function to render compact product card (simple or variable)
function render_compact_product_card($cross_product) {
    $product_id = $cross_product->get_id();
    $is_variable = $cross_product->is_type('variable');
    
    // Calculate savings
    $regular_price = $cross_product->get_regular_price();
    $sale_price = $cross_product->get_price();
    $savings = '';
    if ($cross_product->is_on_sale() && $regular_price > $sale_price) {
        $savings_amount = $regular_price - $sale_price;
        $savings = 'Save ' . wc_price($savings_amount);
    }
    ?>
    
    <div class="compact-product-card <?php echo $is_variable ? 'variable-product' : ''; ?>" 
         data-product-id="<?php echo esc_attr($product_id); ?>"
         data-product-name="<?php echo esc_attr($cross_product->get_name()); ?>"
         data-product-price="<?php echo esc_attr($cross_product->get_price()); ?>"
         data-is-variable="<?php echo $is_variable ? 'true' : 'false'; ?>">
        
        <div class="compact-card-main">
            <!-- Selected Indicator -->
            
            
            <!-- Thumbnail -->
            <div class="compact-thumbnail">
                <?php
                $image_id = $cross_product->get_image_id();
                if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'thumbnail', false, array('loading' => 'lazy'));
                } else {
                    echo wc_placeholder_img('thumbnail');
                }
                ?>
                <?php if ($cross_product->is_on_sale()) : ?>
                    <span class="compact-sale-badge">Sale</span>
                <?php endif; ?>
                <?php if ($is_variable) : ?>
                    <span class="compact-variable-badge">Options</span>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="compact-info">
                <h4 class="compact-title"><?php echo $cross_product->get_name(); ?></h4>
                <div class="compact-price">
                    <?php echo $cross_product->get_price_html(); ?>
                    <?php if ($savings) : ?>
                        <span class="compact-savings"><?php echo $savings; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Expand Button -->
            <?php if ($cross_product->get_description() || $is_variable) : ?>
            <button class="compact-expand-btn" type="button" aria-label="Show details">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Expandable Section -->
        <?php if ($cross_product->get_description() || $is_variable) : ?>
        <div class="compact-description" style="display: none;">
            <div class="compact-description-content">
                
                <!-- PRODUCT DESCRIPTION - ALWAYS SHOW IF EXISTS -->
                <?php if ($cross_product->get_description()) : ?>
                    <div class="product-description-text">
                        <?php echo wpautop($cross_product->get_description()); ?>
                    </div>
                <?php endif; ?>
                
             <!-- VARIABLE PRODUCT OPTIONS - ONLY FOR VARIABLE PRODUCTS -->
<!-- VARIABLE PRODUCT OPTIONS - DROPDOWN -->
<?php if ($is_variable) : ?>
    <div class="variable-product-dropdown">
        <label for="variation_select_<?php echo esc_attr($product_id); ?>" class="variation-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" style="vertical-align: middle; margin-right: 5px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <strong>Select Option:</strong>
        </label>
        
        <select 
            id="variation_select_<?php echo esc_attr($product_id); ?>" 
            name="variation_select_<?php echo esc_attr($product_id); ?>"
            class="variation-select"
            data-parent-id="<?php echo esc_attr($product_id); ?>"
            required>
            <option value="">-- Choose an option --</option>
            
            <?php
            $available_variations = $cross_product->get_available_variations();
            foreach ($available_variations as $variation_data) : 
                $variation = wc_get_product($variation_data['variation_id']);
                if (!$variation || !$variation->is_in_stock()) continue;
                
                // Get variation attributes for display
                $variation_attributes = array();
                foreach ($variation_data['attributes'] as $attr_name => $attr_value) {
                    $variation_attributes[] = $attr_value;
                }
                
                $variation_name = implode(' - ', $variation_attributes);
                $variation_price = $variation->get_price();
                $variation_regular_price = $variation->get_regular_price();
                $variation_on_sale = $variation->is_on_sale();
                
                // Build option label
                $option_label = $variation_name . ' - ' . wc_price($variation_price);
                if ($variation_on_sale) {
                    $option_label .= ' (Save ' . wc_price($variation_regular_price - $variation_price) . ')';
                }
            ?>
            
            <option 
                value="<?php echo esc_attr($variation->get_id()); ?>"
                data-price="<?php echo esc_attr($variation_price); ?>"
                data-name="<?php echo esc_attr($cross_product->get_name() . ' - ' . $variation_name); ?>">
                <?php echo esc_html($variation_name . ' - '); ?><?php echo wc_price($variation_price); ?><?php if ($variation_on_sale) echo ' (Save ' . wc_price($variation_regular_price - $variation_price) . ')'; ?>
            </option>
            
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

</div>
</div>
<?php endif; ?>
</div>

<?php
}

// Render sections using the function
?>

<?php if (!empty($anchor_products)) : ?>
<div class="package-section essential-section">
    <h3 class="package-section-title">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        Installation Add-Ons
        <span class="section-badge essential-badge">Must be added at installation</span>
    </h3>
    
    <div class="compact-product-list">
        <?php foreach ($anchor_products as $cross_product) : 
            render_compact_product_card($cross_product);
        endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($essential_products)) : ?>
<div class="package-section essential-section">
    <h3 class="package-section-title">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        Essential Add-Ons
        <span class="section-badge essential-badge">Highly Recommended</span>
    </h3>
    
    <div class="compact-product-list">
        <?php foreach ($essential_products as $cross_product) : 
            render_compact_product_card($cross_product);
        endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php if (!empty($recommended_products)) : ?>
<div class="package-section recommended-section">
    <h3 class="package-section-title">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3498db" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
        </svg>
        Recommended Add-Ons
        <span class="section-badge recommended-badge">Popular Choices</span>
    </h3>
    
    <div class="compact-product-list">
        <?php foreach ($recommended_products as $cross_product) : 
            render_compact_product_card($cross_product);
        endforeach; ?>
    </div>
</div>
<?php endif; ?>
            
<!-- Sticky Tabs Navigation -->
<div class="grandio-sticky-tabs" id="stickyTabsNav">
    <ul class="grandio-tabs-nav">
        <li><a href="#tab-description" class="tab-link active" data-tab="description">
            <svg width="20" height="20" style="display:inline-block;vertical-align:middle;margin-right:8px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Description
        </a></li>
        <li><a href="#tab-assembly" class="tab-link" data-tab="assembly">
            <svg width="20" height="20" style="display:inline-block;vertical-align:middle;margin-right:8px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            Additional Info
        </a></li>
        <li><a href="#tab-reviews" class="tab-link" data-tab="reviews">
            <svg width="20" height="20" style="display:inline-block;vertical-align:middle;margin-right:8px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            Reviews
        </a></li>
    </ul>
</div>

<!-- Product Tabs Content -->
<div class="grandio-product-tabs">
<?php
$tabs = apply_filters('woocommerce_product_tabs', array());

// Store the ACTUAL current product ID before any loops
$actual_current_product_id = $product->get_id();

foreach ($tabs as $key => $tab) :
    $panel_id = 'tab-' . $key;
    $active_class = ($key === 'description') ? 'active' : '';
?>
<div class="grandio-tab-panel <?php echo $active_class; ?>" id="<?php echo esc_attr($panel_id); ?>" role="tabpanel">
    
    <?php if ($key === 'reviews') : ?>
        <?php
        // Use the stored product ID
        $current_product_id = $actual_current_product_id;
        
        // Get product categories
        $product_cats = wp_get_post_terms($current_product_id, 'product_cat', array('fields' => 'ids'));
        
        // Get all products in same category
        $products_in_category = array($current_product_id);
        
        if (!empty($product_cats) && !is_wp_error($product_cats)) {
            $category_products = get_posts(array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'post_status' => 'publish',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $product_cats,
                    )
                )
            ));
            
            if (!empty($category_products)) {
                $products_in_category = $category_products;
            }
        }
        
        // Get all reviews from category products
        $all_category_reviews = get_comments(array(
            'post__in' => $products_in_category,
            'status' => 'approve',
            'type' => 'review',
            'orderby' => 'comment_date_gmt',
            'order' => 'DESC',
        ));
        
        // If no reviews with type 'review', try empty type
        if (empty($all_category_reviews)) {
            $all_category_reviews = get_comments(array(
                'post__in' => $products_in_category,
                'status' => 'approve',
                'type' => '',
                'orderby' => 'comment_date_gmt',
                'order' => 'DESC',
            ));
            
            // Filter to only include comments with ratings
            $all_category_reviews = array_filter($all_category_reviews, function($comment) {
                $rating = get_comment_meta($comment->comment_ID, 'rating', true);
                return !empty($rating);
            });
        }
        
        $review_count = count($all_category_reviews);
        
        // Get category name
        $cats = wp_get_post_terms($current_product_id, 'product_cat', array('fields' => 'names'));
        $category_name = !empty($cats) && !is_wp_error($cats) ? esc_html($cats[0]) : 'Greenhouse';
        
        // Build review page URL
        $review_page_url = home_url('/write-a-review/') . '?product_id=' . $current_product_id;
        ?>
        
        <?php if ($review_count > 0) : ?>
            <!-- HAS REVIEWS -->
            <div class="reviews-header-section" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
                <div class="shared-reviews-notice" style="flex: 1; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #2196f3; padding: 20px; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2196f3" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <div>
                            <strong style="font-size: 18px; display: block; margin-bottom: 5px; color: #2c3e50;">
                                <?php echo $review_count; ?> Customer <?php echo $review_count === 1 ? 'Review' : 'Reviews'; ?> from Our <?php echo $category_name; ?> Collection
                            </strong>
                            <p style="margin: 0; color: #555; line-height: 1.6;">
                                Reviews shown are from customers who purchased any size in this series. All our sizes share the same quality construction and features!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="reviews" class="woocommerce-Reviews">
                <div id="comments">
                    <h2 class="woocommerce-Reviews-title" style="font-size: 28px; margin: 0 0 25px 0; color: #2c3e50;">
                        <?php
                        // Calculate average rating
                        $total_rating = 0;
                        $rating_count = 0;
                        foreach ($all_category_reviews as $review) {
                            $rating = get_comment_meta($review->comment_ID, 'rating', true);
                            if ($rating) {
                                $total_rating += $rating;
                                $rating_count++;
                            }
                        }
                        
                        printf(
                            _n('%d review for this series', '%d reviews for this series', $review_count, 'woocommerce'),
                            $review_count
                        );
                        ?>
                    </h2>

                    <ol class="commentlist" style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($all_category_reviews as $comment) : 
                            $rating = get_comment_meta($comment->comment_ID, 'rating', true);
                            $product_reviewed = get_post($comment->comment_post_ID);
                        ?>
                        <li class="review" id="comment-<?php echo $comment->comment_ID; ?>" style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #e0e0e0;">
                            <div class="comment_container" style="display: flex; gap: 20px;">
                             
                                <div class="comment-text" style="flex: 1;">
                                    <!-- Product Name Badge at Top -->
                                    <?php if ($product_reviewed) : ?>
                                        <div style="margin-bottom: 12px;">
                                            <span style="display: inline-block; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); color: #1976d2; padding: 6px 14px; border-radius: 6px; font-size: 14px; font-weight: 600; border: 2px solid #90caf9;">
                                                 <?php echo esc_html($product_reviewed->post_title); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                   <?php if ( $rating ) : ?>
    <div style="margin-bottom:10px;">
        <?php echo wc_get_rating_html( $rating ); ?>
    </div>
<?php endif; ?>
                                    
                                    <p class="meta" style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                                        <strong class="woocommerce-review__author" style="color: #2c3e50; font-size: 16px;">
                                            <?php echo esc_html($comment->comment_author); ?>
                                        </strong>
                                        <span class="woocommerce-review__dash"> – </span>
                                        <time class="woocommerce-review__published-date" datetime="<?php echo get_comment_date('c', $comment); ?>">
                                            <?php echo get_comment_date('', $comment); ?>
                                        </time>
                                    </p>
                                    
                                    <div class="description" style="color: #555; line-height: 1.8;">
                                        <p style="margin: 0;"><?php echo wp_kses_post($comment->comment_content); ?></p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    
                    <!-- Write Review CTA at Bottom -->
                    <div style="text-align: center; margin-top: 40px; padding: 30px; background: #f8f9fa; border-radius: 8px;">
                        <p style="margin: 0 0 20px 0; font-size: 16px; color: #555;">
                            Have you used this product? Share your experience with other customers!
                        </p>
                        <a href="<?php echo esc_url($review_page_url); ?>" 
                           class="write-review-btn-footer" 
                           style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 14px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Write a Review
                        </a>
                    </div>
                </div>
            </div>
            
        <?php else : ?>
            <!-- NO REVIEWS -->
            <div class="no-reviews-notice" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #ff9800; padding: 40px; margin-bottom: 30px; border-radius: 12px; text-align: center;">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2" style="margin-bottom: 25px;">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <h3 style="font-size: 28px; font-weight: 700; color: #2c3e50; margin: 0 0 15px 0;">
                    No Reviews Yet for Our <?php echo $category_name; ?> Collection
                </h3>
                <p style="font-size: 18px; color: #555; margin: 0 0 30px 0; line-height: 1.8; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Be the first to share your experience with this product! Your review helps other customers make informed decisions.
                </p>
                <a href="<?php echo esc_url($review_page_url); ?>" 
                   class="write-review-btn-primary" 
                   style="display: inline-flex; align-items: center; gap: 12px; background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; padding: 18px 40px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Write the First Review
                </a>
            </div>
        <?php endif; ?>
    
    <?php else : ?>
        <!-- REGULAR TAB CONTENT -->
        <?php
        if (isset($tab['callback'])) {
            call_user_func($tab['callback'], $key, $tab);
        }
        ?>
    <?php endif; ?>
    
</div>
<?php endforeach; ?>
</div>
        </div>
    </div>
    
    <?php endwhile; ?>
</div>
<?php get_template_part('woocommerce/greenhouse-category-upsale'); ?>
<!-- Sticky Checkout Bar -->
<div class="grandio-sticky-checkout" id="stickyCheckout">
    <div class="sticky-checkout-container">
        
        <!-- Selected Items Summary -->
        <div class="sticky-items-summary">
            <div class="sticky-items-count">
                <span class="sticky-items-count-number" id="selectedItemsCount">1</span>
                <span class="sticky-items-count-label">Items Selected</span>
            </div>
            
            <div class="sticky-items-list" id="selectedItemsList">
                <strong>Your Package:</strong>
                <span id="itemsListText"><?php echo esc_html($product->get_name()); ?></span>
            </div>
        </div>
        
        <!-- Checkout Actions -->
        <div class="sticky-checkout-actions">
            <div class="sticky-total">
                <div class="sticky-total-label">Package Total</div>
                <div class="sticky-total-amount" id="packageTotal">
                    <?php echo wc_price($product->get_price()); ?>
                </div>
            </div>
            
            <div class="sticky-quantity-selector">
                <label for="packageQty">Qty:</label>
                <input type="number" id="packageQty" class="sticky-qty-input" value="1" min="1" step="1">
            </div>
            
            <button class="sticky-checkout-btn" id="checkoutBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                Checkout Now
            </button>
        </div>
        
    </div>
</div>
<!-- JAVA SCRIPT on page updates --> 
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing all features...');
    
    // Get AJAX URL and nonce
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    const nonce = '<?php echo wp_create_nonce('grandio_package_nonce'); ?>';
    
    // ========================================
    // STICKY TABS
    // ========================================
    const stickyTabsNav = document.getElementById('stickyTabsNav');
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanels = document.querySelectorAll('.grandio-tab-panel');
    
    function activateTab(tabName) {
        tabLinks.forEach(l => l.classList.remove('active'));
        tabPanels.forEach(p => p.classList.remove('active'));
        
        const targetTabLink = document.querySelector('.tab-link[data-tab="' + tabName + '"]');
        const targetPanel = document.getElementById('tab-' + tabName);
        
        if (targetTabLink && targetPanel) {
            targetTabLink.classList.add('active');
            targetPanel.classList.add('active');
            
            setTimeout(() => {
                targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            activateTab(targetTab);
        });
    });
    
    if (stickyTabsNav) {
        const tabsObserver = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    stickyTabsNav.classList.remove('stuck');
                } else {
                    stickyTabsNav.classList.add('stuck');
                }
            },
            { threshold: 0, rootMargin: '-1px 0px 0px 0px' }
        );
        tabsObserver.observe(stickyTabsNav);
    }
    
    // ========================================
    // RATING LINK - SCROLL TO REVIEWS
    // ========================================
    const ratingLink = document.querySelector('.scroll-to-reviews');
    if (ratingLink) {
        ratingLink.addEventListener('click', function(e) {
            e.preventDefault();
            activateTab('reviews');
        });
    }
    
    // ========================================
    // CHECK URL HASH ON PAGE LOAD
    // ========================================
    function handleHashChange() {
        const hash = window.location.hash;
        if (hash) {
            let tabName = '';
            
            if (hash === '#reviews' || hash === '#tab-reviews') {
                tabName = 'reviews';
            } else if (hash === '#description' || hash === '#tab-description') {
                tabName = 'description';
            } else if (hash === '#assenb' || hash === '#tab-assembly') {
                tabName = 'qa';
            }
            
            if (tabName) {
                setTimeout(() => {
                    activateTab(tabName);
                }, 500);
            }
        }
    }
    
    handleHashChange();
    window.addEventListener('hashchange', handleHashChange);
    
    // ========================================
    // IMAGE GALLERY
    // ========================================
    const mainImg = document.getElementById('grandio-main-img');
    const wrapper = document.querySelector('.grandio-gallery-wrapper');
    const track = document.querySelector('.grandio-gallery-track');
    const leftArrow = document.querySelector('.gallery-arrow.left');
    const rightArrow = document.querySelector('.gallery-arrow.right');

    if (mainImg && track && wrapper) {
        const thumbs = track.querySelectorAll('.grandio-gallery-image');
        const visible = 4;
        let index = 0;

        if (thumbs.length > visible) {
            leftArrow.style.display = 'flex';
            rightArrow.style.display = 'flex';
        }

        function updateGallery() {
            const thumbWidth = thumbs[0].offsetWidth + 10;
            track.style.transform = `translateX(-${index * thumbWidth}px)`;
            leftArrow.style.opacity = index === 0 ? '0.3' : '1';
            rightArrow.style.opacity = index >= thumbs.length - visible ? '0.3' : '1';
        }

        rightArrow.addEventListener('click', () => {
            if (index < thumbs.length - visible) {
                index++;
                updateGallery();
            }
        });

        leftArrow.addEventListener('click', () => {
            if (index > 0) {
                index--;
                updateGallery();
            }
        });

        thumbs.forEach(thumb => {
            const img = thumb.querySelector('img');
            img.addEventListener('click', () => {
                const src = img.dataset.full;
                if (!src) return;
                mainImg.src = src;
                mainImg.removeAttribute('srcset');
                mainImg.removeAttribute('sizes');
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });

        updateGallery();
    }
    
    // ========================================
    // NOTIFICATION SYSTEM
    // ========================================
    function showNotification(message, type = 'info') {
        const existingNotif = document.querySelector('.grandio-notification');
        if (existingNotif) {
            existingNotif.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `grandio-notification grandio-notification-${type}`;
        notification.innerHTML = `
            <div class="grandio-notification-content">
                <span class="grandio-notification-icon">
                    ${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}
                </span>
                <span class="grandio-notification-message">${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    // ========================================
    // PACKAGE BUILDER
    // ========================================
    let selectedProducts = [];
    
    // Get main product data
    const mainProductCard = document.querySelector('.grandio-product-card.main-product');
    if (mainProductCard) {
        const mainProduct = {
            id: parseInt(mainProductCard.dataset.productId),
            name: mainProductCard.dataset.productName,
            price: parseFloat(mainProductCard.dataset.productPrice)
        };
        selectedProducts.push(mainProduct);
    }
    
    // Update summary function
    function updateSummary() {
        const selectedItemsCountEl = document.getElementById('selectedItemsCount');
        const itemsListTextEl = document.getElementById('itemsListText');
        const packageTotalEl = document.getElementById('packageTotal');
        
        if (!selectedItemsCountEl || !itemsListTextEl || !packageTotalEl) return;
        
        selectedItemsCountEl.textContent = selectedProducts.length;
        const itemNames = selectedProducts.map(p => p.name).join(', ');
        itemsListTextEl.textContent = itemNames;
        const total = selectedProducts.reduce((sum, p) => sum + p.price, 0);
        packageTotalEl.textContent = '$' + total.toFixed(2);
    }
    
    // ========================================
    // VARIATION DROPDOWN HANDLER - INSIDE DOMContentLoaded!
    // ========================================
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('variation-select')) {
            const select = e.target;
            const selectedOption = select.options[select.selectedIndex];
            const card = select.closest('.compact-product-card');
            const parentId = parseInt(select.dataset.parentId);
            
            if (!selectedOption.value) {
                // Remove from package
                card.dataset.hasVariationSelected = 'false';
                card.classList.remove('selected');
                
                selectedProducts = selectedProducts.filter(p => {
                    return parseInt(p.parentId) !== parentId;
                });
                
                updateSummary();
                showNotification('Removed from package', 'info');
                return;
            }
            
            const variationId = parseInt(selectedOption.value);
            const variationPrice = parseFloat(selectedOption.dataset.price);
            const variationName = selectedOption.dataset.name;
            
            console.log('=== DROPDOWN CHANGED ===');
            console.log('Parent ID:', parentId);
            console.log('New Variation ID:', variationId);
            console.log('Before filtering:', selectedProducts);
            
            // Remove ALL products with this parentId
            selectedProducts = selectedProducts.filter(p => {
                const keep = parseInt(p.parentId) !== parentId;
                console.log(`Product ${p.id} (parent: ${p.parentId}): ${keep ? 'KEEP' : 'REMOVE'}`);
                return keep;
            });
            
            console.log('After filtering:', selectedProducts);
            
            // Update card data
            card.dataset.productId = variationId;
            card.dataset.productPrice = variationPrice;
            card.dataset.productName = variationName;
            card.dataset.hasVariationSelected = 'true';
            
            // Add the NEW variation
            const newProduct = {
                id: variationId,
                name: variationName,
                price: variationPrice,
                parentId: parentId
            };
            
            selectedProducts.push(newProduct);
            
            console.log('After adding new:', selectedProducts);
            console.log('Total items:', selectedProducts.length);
            console.log('======================');
            
            // Mark card as selected
            card.classList.add('selected');
            
            showNotification('✓ ' + variationName, 'success');
            updateSummary();
        }
    });
    
    // ========================================
    // COMPACT CARD CLICK HANDLERS - INSIDE DOMContentLoaded!
    // ========================================
    const compactCards = document.querySelectorAll('.compact-product-card');
    
    compactCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Ignore if clicking expand button
            if (e.target.closest('.compact-expand-btn')) {
                return;
            }
            
            // Ignore if clicking dropdown area
            if (e.target.closest('.variation-select') || e.target.closest('.variable-product-dropdown')) {
                return;
            }
            
            // Check if variable product
            const isVariable = this.dataset.isVariable === 'true';
            
            if (isVariable) {
                // BLOCK clicks on variable products
                const dropdown = this.querySelector('.variation-select');
                
                if (dropdown) {
                    dropdown.focus();
                }
                
                showNotification('ℹ️ Use the dropdown to select this product', 'info');
                
                if (dropdown) {
                    dropdown.style.animation = 'pulse 0.5s';
                    setTimeout(() => {
                        dropdown.style.animation = '';
                    }, 500);
                }
                
                return;
            }
            
            // FOR NON-VARIABLE PRODUCTS: Toggle selection
            const productData = {
                id: parseInt(this.dataset.productId),
                name: this.dataset.productName,
                price: parseFloat(this.dataset.productPrice)
            };
            
            if (this.classList.contains('selected')) {
                // Deselect
                this.classList.remove('selected');
                selectedProducts = selectedProducts.filter(p => parseInt(p.id) !== productData.id);
                showNotification('Removed from package', 'info');
            } else {
                // Select
                this.classList.add('selected');
                
                if (!selectedProducts.find(p => parseInt(p.id) === productData.id)) {
                    selectedProducts.push(productData);
                    showNotification('✓ Added to package', 'success');
                }
            }
            
            updateSummary();
        });
    });
    
    // ========================================
    // EXPAND/COLLAPSE BUTTONS
    // ========================================
    const expandButtons = document.querySelectorAll('.compact-expand-btn');
    
    expandButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const card = this.closest('.compact-product-card');
            const description = card.querySelector('.compact-description');
            
            if (description) {
                if (description.style.display === 'none' || !description.style.display) {
                    description.style.display = 'block';
                    this.classList.add('expanded');
                } else {
                    description.style.display = 'none';
                    this.classList.remove('expanded');
                }
            }
        });
    });
    
    // ========================================
    // CHECKOUT BUTTON - AJAX
    // ========================================
    const checkoutBtn = document.getElementById('checkoutBtn');
    const packageQtyInput = document.getElementById('packageQty');
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const button = this;
            const qty = packageQtyInput ? parseInt(packageQtyInput.value) : 1;
            
            if (selectedProducts.length === 0) {
                showNotification('Please select at least one product', 'error');
                return;
            }
            
            // Disable button and show loading
            button.disabled = true;
            const originalHTML = button.innerHTML;
            button.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 6v6l4 2"></path>
                </svg>
                Adding to Cart...
            `;
            
            // Prepare data
            const formData = new FormData();
            formData.append('action', 'grandio_add_package_to_cart');
            formData.append('nonce', nonce);
            formData.append('products', JSON.stringify(selectedProducts));
            formData.append('quantity', qty);
            
            // Send AJAX request
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('AJAX Response:', data);
                
                if (data.success) {
                    button.innerHTML = `
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Success! Redirecting...
                    `;
                    
                    showNotification(
                        `${data.data.added_count} item(s) added to cart!`, 
                        'success'
                    );
                    
                    if (data.data.failed_count > 0) {
                        console.warn('Some products failed:', data.data.failed_products);
                    }
                    
                    setTimeout(() => {
                        window.location.href = data.data.checkout_url;
                    }, 1000);
                    
                } else {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                    
                    const errorMsg = data.data && data.data.message 
                        ? data.data.message 
                        : 'Failed to add products to cart';
                    
                    showNotification(errorMsg, 'error');
                    
                    console.error('Add to cart failed:', data);
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                button.innerHTML = originalHTML;
                button.disabled = false;
                showNotification('Something went wrong. Please try again.', 'error');
            });
        });
    }
    
    // ========================================
    // INITIALIZE
    // ========================================
    updateSummary();
    
    console.log('All features initialized successfully!');
    console.log('Initial selected products:', selectedProducts);
    
}); // END DOMContentLoaded
</script>

<?php get_footer(); ?>
