<?php
$css = <<<CSS

/* ==========================================================================
   HOMEPAGE PREMIUM REDESIGN - TRENDING AREAS & COUNTRIES
   ========================================================================== */

/* Trending Areas */
.sit-trending-areas {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0 2rem;
}

.sit-trending-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 1rem;
    padding: 1.75rem 1.25rem;
    text-decoration: none;
    color: var(--sit-text-dark);
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sit-trending-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(226, 10, 23, 0.1);
    border-color: rgba(226, 10, 23, 0.2);
}

.sit-trending-card:hover .sta-icon-wrapper {
    transform: scale(1.1);
    background: rgba(226, 10, 23, 0.05);
}

.sta-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: var(--sit-bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    padding: 12px;
}

.sta-icon-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.sta-content h3 {
    font-family: var(--sit-font-heading);
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
    color: var(--sit-text-dark);
}

.sta-content p {
    font-family: var(--sit-font-body);
    font-size: 0.85rem;
    color: var(--sit-text-muted);
    margin: 0;
}

.sta-arrow {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    color: var(--sit-primary);
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.sit-trending-card:hover .sta-arrow {
    opacity: 1;
    transform: translateX(0);
}

/* Countries Grid */
.sit-countries-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0 2rem;
}

.sit-country-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem;
    text-decoration: none;
    color: var(--sit-text-dark);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.sit-country-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(226, 10, 23, 0.1);
    border-color: rgba(226, 10, 23, 0.2);
}

.sc-flag-wrap {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 2px solid #fff;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.sit-country-card:hover .sc-flag-wrap {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(226, 10, 23, 0.2);
}

.sc-flag-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sc-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.sc-content h3 {
    font-family: var(--sit-font-heading);
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    color: var(--sit-text-dark);
}

.sc-count {
    font-family: var(--sit-font-body);
    font-size: 0.85rem;
    color: var(--sit-text-muted);
    margin-top: 0.25rem;
}

.sc-arrow {
    color: var(--sit-text-muted);
    opacity: 0.5;
    transition: all 0.3s ease;
}

.sit-country-card:hover .sc-arrow {
    color: var(--sit-primary);
    opacity: 1;
    transform: translateX(4px);
}

.sit-country-featured {
    background: linear-gradient(to right, rgba(226, 10, 23, 0.02), #fff);
    border-left: 3px solid var(--sit-primary);
}

/* ==========================================================================
   ENHANCED UNIVERSITY CAROUSEL OVERRIDES
   ========================================================================== */

.top-universities .university-box-wrapper {
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 0; /* Reset */
}

/* Let the sit-ui-card styling handle the container. We just layout internals. */
.top-universities .university-image {
    padding: 0 !important;
    margin: 0 !important;
    height: 200px;
}

.top-universities .university-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px 12px 0 0 !important;
}

.top-universities .university-content {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.top-universities .university-content h3 {
    font-family: var(--sit-font-heading) !important;
    font-size: 1.1rem !important;
    margin-top: 0 !important;
    margin-bottom: 0.5rem !important;
    line-height: 1.4 !important;
    max-height: 3em !important;
}

.top-universities .university-content .country {
    font-size: 0.85rem;
    color: var(--sit-text-muted);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.top-universities .university-content p:not(.country) {
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
}

/* Rewrite university attributes grid to look like tags */
.top-universities .university-attributes {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid var(--sit-border-color);
}

.top-universities .university-attributes .attribute {
    background: var(--sit-bg-light);
    border-radius: 6px;
    padding: 6px 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    width: calc(50% - 4px); /* 2 columns */
}

.top-universities .university-attributes .attribute img {
    width: 14px;
    height: 14px;
    display: none; /* Hide default icons, look cleaner as text tags */
}

.top-universities .university-attributes .attribute .attribute-content {
    display: flex;
    flex-direction: column;
}

.top-universities .university-attributes .attribute .attribute-content h4 {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: var(--sit-text-muted);
    letter-spacing: 0.5px;
    font-weight: 500;
    margin: 0 0 2px 0;
}

.top-universities .university-attributes .attribute .attribute-content p {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--sit-text-dark);
    margin: 0;
}

.top-universities .unilink {
    margin-top: 1.5rem !important;
    width: 100%;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-weight: 600;
    background: var(--sit-primary);
    transition: all 0.2s;
    font-size: 0.95rem;
}

.top-universities .unilink:hover {
    background: var(--sit-primary-dark);
}

CSS;

$file = '/Users/darwish/Dev/sitsearch/wp-content/plugins/sit-search/assets/css/sit-search.css';
file_put_contents($file, "\n" . $css, FILE_APPEND);
echo "CSS Appended successfully.";
?>
