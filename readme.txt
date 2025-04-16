=== Conditional Product Discount ===
Contributors: blackmario  
Tags: woocommerce, discount, product discount, conditional pricing, dynamic pricing  
Requires at least: 5.6  
Tested up to: 6.8
Requires PHP: 7.2  
Stable tag: 1.1.1
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Apply product-specific discounts in WooCommerce based on total product price (price × quantity).

== Description ==

Apply product-specific discounts in WooCommerce based on total product price (price × quantity).

### 🎯 Features:

- Set discount rules based on total price of individual products
- Define discount thresholds and percentages
- Choose specific products for each rule via searchable multi-select
- Responsive admin panel with Select2 UI
- Cap discount at 100% to prevent accidental giveaways
- Fully compatible with WooCommerce cart and checkout
- Internationalization ready

== Use Case Example ==

- If product total exceeds ₹800 → apply 10% off  
- If it exceeds ₹1200 → apply 20% off  
- If it exceeds ₹1500 → apply 25% off  

Each rule applies individually to qualifying products in the cart.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/conditional-product-discount` directory, or install the plugin through the WordPress admin panel.
2. Activate the plugin through the ‘Plugins’ menu.
3. Go to **Product Discounts** in the WordPress dashboard to set your rules.

== Frequently Asked Questions ==

= Can I apply discounts to categories or tags? =  
Not yet, but that feature is on the roadmap.

= Will it apply the highest matching discount? =  
No. It applies the **first matching rule** for each product.

= Does it work with other WooCommerce pricing plugins? =  
It should, but behavior may vary depending on how other plugins manipulate cart totals.

== Screenshots ==

1. Admin settings page for defining discount rules
2. Applied discounts in the WooCommerce cart

== Changelog ==

= 1.0.0 =
* Initial release – Create per-product discount rules based on total price thresholds

== Upgrade Notice ==

= 1.0.0 =
First release. Add powerful product-based discount logic to your WooCommerce store.

== Credits ==

Developed by [Deepak Jangra](https://profiles.wordpress.org/blackmario)

== License ==

This plugin is licensed under the GPLv2 or later.
