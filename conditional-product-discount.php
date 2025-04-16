<?php
/*
Plugin Name: Conditional Product Discount
Plugin URI: https://deepslog.com/
Description: Apply product-specific discounts in WooCommerce based on total product price (price × quantity). For products eligible for multiple rules, the highest discount is applied.
Version: 1.1.1
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.2
Author: Deepak Jangra
Author URI: https://profiles.wordpress.org/blackmario
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: conditional-product-discount
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

class Conditional_Product_Discount_Plugin {

    private $option_name = 'cpd_discount_rules';

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discounts'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function load_textdomain() {
        load_plugin_textdomain('conditional-product-discount', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script(
            'select2-js',
            plugin_dir_url(__FILE__) . 'dist/js/select2.min.js',
            ['jquery'],
            '4.1.0-rc.0',
            true
        );
    
        wp_enqueue_style(
            'select2-css',
            plugin_dir_url(__FILE__) . 'dist/css/select2.min.css',
            [],
            '4.1.0-rc.0'
        );
    
        wp_add_inline_script('select2-js', 'jQuery(document).ready(function($) {$(".cpd-select2").select2();});');
    }  

    public function create_admin_menu() {
        add_menu_page(
            esc_html__('Product Discounts', 'conditional-product-discount'),
            esc_html__('Product Discounts', 'conditional-product-discount'),
            'manage_options',
            'cpd-discount-rules',
            [$this, 'discount_rules_page'],
            'dashicons-money'
        );
    }

    public function register_settings() {
        register_setting('cpd_discount_group', $this->option_name, [
            'sanitize_callback' => ['Conditional_Product_Discount_Plugin', 'sanitize_rules']
        ]);
    }

    /**
     * Sanitize and re-index the discount rules.
     */
    public static function sanitize_rules($input) {
        $sanitized = [];
        // Rebuild the rules array to avoid issues with non-unique keys.
        foreach ($input as $rule) {
            $threshold = isset($rule['threshold']) ? max(0, floatval($rule['threshold'])) : 0;
            $discount  = isset($rule['discount']) ? max(0, min(floatval($rule['discount']), 100)) : 0;
            $products  = isset($rule['products']) && is_array($rule['products']) ? array_map('intval', $rule['products']) : [];
            $sanitized[] = [
                'threshold' => $threshold,
                'discount'  => $discount,
                'products'  => $products
            ];
        }
        return $sanitized;
    }

    public function discount_rules_page() {
        $rules    = get_option($this->option_name, []);
        $products = wc_get_products(['limit' => -1]);

        ob_start();
        foreach ($products as $product) {
            echo '<option value="' . esc_attr($product->get_id()) . '">' . esc_html($product->get_name()) . '</option>';
        }
        $escaped_options_html = ob_get_clean();
        ?>
        <script>
            const cpdProductOptionsHTML = <?php echo wp_json_encode($escaped_options_html); ?>;
        </script>
        <div class="wrap cpd-wrap">
            <h1><?php echo esc_html__('Conditional Product Discounts', 'conditional-product-discount'); ?></h1>
            <form method="post" action="options.php" onsubmit="return validateDiscountLimits();">
                <?php settings_fields('cpd_discount_group'); ?>
                <div id="cpd-rules-table">
                    <?php foreach ($rules as $index => $rule): ?>
                        <div class="cpd-rule-row">
                            <span class="cpd-remove-rule" onclick="this.parentNode.remove()">&times;</span>
							<?php /* translators: %d is the index */ ?>
                            <h2><?php echo esc_html(sprintf(__('Rule #%d', 'conditional-product-discount'), $index + 1)); ?></h2>
                            <div class="cpd-rule-row__inner">
                                <div>
                                    <label><?php echo esc_html__('Total Product Price Over:', 'conditional-product-discount'); ?></label>
                                    <input type="number" name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($index); ?>][threshold]" value="<?php echo esc_attr($rule['threshold']); ?>" step="0.01" />
                                </div>
                                <div>
                                    <label><?php echo esc_html__('Discount %:', 'conditional-product-discount'); ?></label>
                                    <input type="number" name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($index); ?>][discount]" value="<?php echo esc_attr($rule['discount']); ?>" step="0.01" max="100" />
                                </div>
                                <div class="cpd-rule-products">
                                    <label><?php echo esc_html__('Products:', 'conditional-product-discount'); ?></label>
                                    <select class="cpd-select2" name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($index); ?>][products][]" multiple>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected(!empty($rule['products']) && in_array($product->get_id(), $rule['products'], true)); ?>>
                                                <?php echo esc_html($product->get_name()); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" onclick="cpdAddRuleRow()"><?php echo esc_html__('Add Rule', 'conditional-product-discount'); ?></button></p>
                <?php submit_button(); ?>
            </form>
        </div>
        <script>
            function validateDiscountLimits() {
                const discountFields = document.querySelectorAll('input[name*="[discount]"]');
                for (const field of discountFields) {
                    const value = parseFloat(field.value);
                    if (value > 100) {
                        alert('<?php echo esc_js(__('Discount cannot exceed 100%. Please correct the values.', 'conditional-product-discount')); ?>');
                        return false;
                    }
                }
                return true;
            }

            function cpdAddRuleRow() {
                const table = document.getElementById('cpd-rules-table');
                // Use a unique index based on timestamp to avoid conflicts.
                const newIndex = 'new_' + Date.now();
                const div = document.createElement('div');
                div.classList.add('cpd-rule-row');
                div.innerHTML = `
                    <span class="cpd-remove-rule" onclick="this.parentNode.remove()">&times;</span>
                    <h2><?php echo esc_html__('Rule', 'conditional-product-discount'); ?> #New</h2>
                    <div class="cpd-rule-row__inner">
                        <div>
                            <label><?php echo esc_html__('Total Product Price Over:', 'conditional-product-discount'); ?></label>
                            <input type="number" name="cpd_discount_rules[${newIndex}][threshold]" step="0.01" />
                        </div>
                        <div>
                            <label><?php echo esc_html__('Discount %:', 'conditional-product-discount'); ?></label>
                            <input type="number" name="cpd_discount_rules[${newIndex}][discount]" step="0.01" max="100" />
                        </div>
                        <div class="cpd-rule-products">
                            <label><?php echo esc_html__('Products:', 'conditional-product-discount'); ?></label>
                            <select class="cpd-select2" name="cpd_discount_rules[${newIndex}][products][]" multiple>
                                ` + cpdProductOptionsHTML + `
                            </select>
                        </div>
                    </div>
                `;
                table.appendChild(div);
                jQuery('.cpd-select2').select2();
            }
        </script>
        <style>
            #cpd-rules-table {
                display: flex;
                gap: 22px;
                flex-wrap: wrap;
                max-width: 1600px;
            }
            .cpd-rule-row {
                position: relative;
                background-color: #fff;
                border-radius: 8px;
                padding: 14px;
                max-width: 800px;
            }
            .cpd-rule-row__inner {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
            }
            .cpd-rule-row__inner div {
                display: flex;
                align-items: start;
                flex-wrap: wrap;
                flex-direction: column;
                gap: 5px;
            }
            .cpd-rule-row__inner label {
                font-weight: 700;
            }
            .cpd-rule-row__inner input,
            .cpd-rule-row__inner .select2-selection,
            .cpd-rule-row__inner .select2-selection:focus {
                border: solid 1px #1e2327;
                border-radius: 8px !important;
                font-size: 14px;
                padding-left: 8px;
            }
            .cpd-remove-rule {
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
                font-size: 20px;
                color: #a00;
            }
        </style>
        <?php
    }

    public function apply_discounts($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;

        $rules = get_option($this->option_name, []);

        // Loop through each item in the cart.
        foreach ($cart->get_cart() as $item) {
            $product_id  = $item['product_id'];
            $total_price = $item['data']->get_price() * $item['quantity'];
            $max_discount_percent = 0;

            // Evaluate all discount rules applicable to the product.
            foreach ($rules as $rule) {
                // Skip the rule if it doesn't have products specified or the product isn't selected.
                if (empty($rule['products']) || !in_array($product_id, $rule['products'], true)) {
                    continue;
                }

                // Check if the total price qualifies for this rule.
                if ($total_price >= $rule['threshold']) {
                    $discount_percent = min(100, floatval($rule['discount']));
                    // Capture the highest discount percentage among applicable rules.
                    if ($discount_percent > $max_discount_percent) {
                        $max_discount_percent = $discount_percent;
                    }
                }
            }

            // If at least one discount rule applies, add a negative fee for the highest discount.
            if ($max_discount_percent > 0) {
                $discount = $total_price * ($max_discount_percent / 100);
				/* translators: %s is the item['data'] */
                $cart->add_fee(sprintf(__('Discount for %s', 'conditional-product-discount'), $item['data']->get_name()), -$discount);
            }
        }
    }
}

new Conditional_Product_Discount_Plugin();
