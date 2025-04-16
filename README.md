=== Conditional Product Discount ===
Contributors: deepakjangra  
Tags: woocommerce, discount, product discount, conditional pricing, dynamic pricing  
Requires at least: 5.6  
Tested up to: 6.8
Requires PHP: 7.2  
Stable tag: 1.0.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Apply automatic discounts to specific WooCommerce products based on total product price (price × quantity). Easy rule management and flexible product targeting.

== Description ==

**Conditional Product Discount** is a lightweight WooCommerce extension that lets you apply percentage-based discounts on specific products if their total price (price × quantity) exceeds defined thresholds.

Perfect for creating volume-based promotions, bulk discounts, and custom pricing strategies without touching a single line of code.

### 🧠 Features:

- Set multiple discount rules based on total product price
- Apply discounts to individual products, not the entire cart
- Define rules for specific products using a searchable multi-select
- Prevent overly generous giveaways with a 100% cap
- Add and manage rules from a clean, responsive admin panel
- Select2-powered UI for fast product selection
- Works seamlessly with WooCommerce carts

### 🛠 Use Case Example:

- Buy more than ₹800 worth of a product → get 10% off  
- Over ₹1200 of a product → get 20% off  
- Over ₹1500 → get 25% off  

All discounts are applied automatically when the product meets the defined threshold.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/conditional-product-discount` directory, or install the plugin through the WordPress admin interface.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Product Discounts** in the admin menu to set up your rules.

== Frequently Asked Questions ==

= Does this work with other WooCommerce discount plugins? =  
It should, but compatibility may vary depending on how other plugins modify cart pricing.

= Can I apply discounts to product categories or tags? =  
Currently, the plugin supports specific products only. Support for categories and tags is planned.

= Will it apply the highest discount or the first matching rule? =  
The plugin stops at the first matching rule for each product.

== Screenshots ==

1. Admin interface to add and manage discount rules
2. WooCommerce cart showing applied discounts

== Changelog ==

= 1.0.0 =
* Initial release 🎉

== Upgrade Notice ==

= 1.0.0 =
First release! Set up your conditional product discounts with ease.

== Credits ==

Developed by [Deepak Jangra](https://deepslog.com/)

== License ==

This plugin is free software, licensed under the GNU General Public License v2 or later.
