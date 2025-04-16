# Conditional Product Discount

**Contributors:** [Deepak Jangra](https://deepslog.com/)  
**Tags:** woocommerce, discount, product discount, conditional pricing, dynamic pricing  
**Requires at least:** 5.6  
**Tested up to:** 6.5  
**Requires PHP:** 7.2  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Apply automatic discounts to specific WooCommerce products based on the total product price (price × quantity). Set flexible discount rules for individual products.

---

## ✨ Features

- Set multiple rules based on total product price (product price × quantity)
- Define specific discounts per product using a multi-select dropdown
- Apply discounts per product line item—not the whole cart
- Responsive and clean admin UI
- Prevent discount abuse with 100% discount cap
- Built-in rule validation
- Select2-powered searchable product selection
- Fully compatible with WooCommerce cart calculations

---

## 📦 Use Case Example

- If a product’s total (price × quantity) exceeds ₹800 → apply 10% discount  
- If it exceeds ₹1200 → apply 20% discount  
- If it exceeds ₹1500 → apply 25% discount

These rules are evaluated individually per product—not on the total cart.

---

## 🛠 Installation

1. Download the plugin ZIP and upload it via **Plugins > Add New** or extract it into your `/wp-content/plugins/` directory.
2. Activate the plugin through the WordPress **Plugins** menu.
3. Go to **Product Discounts** in the WordPress admin panel.
4. Add your rules. Done.

---

## 📸 Screenshots

1. **Admin panel** with custom discount rules.
2. **WooCommerce cart** showing per-product applied discounts.

---

## ❓ FAQ

### Does this work with other discount plugins?

It should, but results may vary depending on how other plugins apply pricing logic.

### Can I apply discounts to categories or tags?

Not yet. Currently, you must select products individually. Category-based rules are on the roadmap.

### Will the plugin apply all matching rules?

Nope. The plugin stops at the **first matching rule** for each product. Plan your thresholds accordingly.

---

## 🚀 Changelog

### 1.0.0
- Initial release with product-based conditional discounts and UI rule manager.

---

## 🧑‍💻 Credits

Developed by [Deepak Jangra](https://deepslog.com/)

---

## 📜 License

This plugin is free software released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
