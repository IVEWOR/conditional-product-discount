<?php
/*
Plugin Name: Conditional Product Discount
Plugin URI: https://deepslog.com/
Description: Easily configure product-specific discounts in WooCommerce based on the total product price (price × quantity). Define multiple discount rules with thresholds and assign them to specific products.
Version: 1.0.0
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.2
Author: Deepak Jangra
Author URI: https://profiles.wordpress.org/deepakjangra
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: conditional-product-discount
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

class Conditional_Product_Discount_Plugin {

    private $option_name = 'cpd_discount_rules';

    public function __construct() {
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discounts'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_style('cpd-admin-style', plugin_dir_url(__FILE__) . 'cpd-admin-style.css');
        wp_add_inline_script('select2-js', 'jQuery(document).ready(function($) {$(".cpd-select2").select2();});');
    }

    public function create_admin_menu() {
        add_menu_page(
            'Product Discounts',
            'Product Discounts',
            'manage_options',
            'cpd-discount-rules',
            [$this, 'discount_rules_page'],
            'dashicons-money'
        );
    }

    public function register_settings() {
        register_setting('cpd_discount_group', $this->option_name);
    }

    public function discount_rules_page() {
        $rules = get_option($this->option_name, []);
        $products = wc_get_products(['limit' => -1]);

        $options_html = '';
        foreach ($products as $product) {
            $options_html .= '<option value="' . esc_attr($product->get_id()) . '">' . esc_html($product->get_name()) . '</option>';
        }
        ?>
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
            .cpd-rule-row__inner div label {
                font-weight: 700;
            }
            .cpd-rule-row__inner input {
                font-size: 14px;
                padding-left: 8px;
            }
            .cpd-rule-row__inner input,
            .cpd-rule-row__inner .select2-selection,
            .cpd-rule-row__inner .select2-selection:focus {
                border: solid 1px #1e2327;
                border-radius: 8px !important;
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
        <div class="wrap cpd-wrap">
            <h1><?php _e('Conditional Product Discounts', 'conditional-product-discount'); ?></h1>
            <form method="post" action="options.php" onsubmit="return validateDiscountLimits();">
                <?php settings_fields('cpd_discount_group'); ?>
                <div id="cpd-rules-table">
                    <?php foreach ($rules as $index => $rule): ?>
                        <div class="cpd-rule-row">
                            <span class="cpd-remove-rule" onclick="this.parentNode.remove()">&times;</span>
                            <h2><?php echo sprintf(__('Rule #%d', 'conditional-product-discount'), $index + 1); ?></h2>
                            <div class="cpd-rule-row__inner">
                                <div>
                                    <label><?php _e('Total Product Price Over:', 'conditional-product-discount'); ?></label>
                                    <input type="number" name="<?php echo $this->option_name; ?>[<?php echo $index; ?>][threshold]" value="<?php echo esc_attr($rule['threshold']); ?>" step="0.01" />
                                </div>
                                <div>
                                    <label><?php _e('Discount %:', 'conditional-product-discount'); ?></label>
                                    <input type="number" name="<?php echo $this->option_name; ?>[<?php echo $index; ?>][discount]" value="<?php echo esc_attr($rule['discount']); ?>" step="0.01" max="100" />
                                </div>
                                <div class="cpd-rule-products">
                                    <label><?php _e('Products:', 'conditional-product-discount'); ?></label>
                                    <select class="cpd-select2" name="<?php echo $this->option_name; ?>[<?php echo $index; ?>][products][]" multiple>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo $product->get_id(); ?>" <?php echo (!empty($rule['products']) && in_array($product->get_id(), $rule['products'])) ? 'selected' : ''; ?>>
                                                <?php echo esc_html($product->get_name()); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" class="button button-secondary" onclick="cpdAddRuleRow()"><?php _e('Add Rule', 'conditional-product-discount'); ?></button></p>
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
                const rowCount = table.children.length;
                const div = document.createElement('div');
                div.classList.add('cpd-rule-row');
                div.innerHTML = `
                    <span class="cpd-remove-rule" onclick="this.parentNode.remove()">&times;</span>
                    <h2><?php _e('Rule', 'conditional-product-discount'); ?> #${rowCount + 1}</h2>
                    <div class="cpd-rule-row__inner">
                        <div>
                            <label><?php _e('Total Product Price Over:', 'conditional-product-discount'); ?></label>
                            <input type="number" name="cpd_discount_rules[${rowCount}][threshold]" step="0.01" />
                        </div>
                        <div>
                            <label><?php _e('Discount %:', 'conditional-product-discount'); ?></label>
                            <input type="number" name="cpd_discount_rules[${rowCount}][discount]" step="0.01" max="100" />
                        </div>
                        <div class="cpd-rule-products">
                            <label><?php _e('Products:', 'conditional-product-discount'); ?></label>
                            <select class="cpd-select2" name="cpd_discount_rules[${rowCount}][products][]" multiple>
                                <?php echo $options_html; ?>
                            </select>
                        </div>
                    </div>
                `;
                table.appendChild(div);
                jQuery('.cpd-select2').select2();
            }
        </script>
        <?php
    }

    public function apply_discounts($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;

        $rules = get_option($this->option_name, []);

        foreach ($cart->get_cart() as $item) {
            $product_id = $item['product_id'];
            $total_price = $item['data']->get_price() * $item['quantity'];

            foreach ($rules as $rule) {
                if (empty($rule['products']) || !is_array($rule['products']) || !in_array($product_id, $rule['products'])) continue;
                $discount_percent = floatval($rule['discount']);
                if ($discount_percent > 100) $discount_percent = 100;
                if ($total_price >= $rule['threshold']) {
                    $discount = $total_price * ($discount_percent / 100);
                    $cart->add_fee(sprintf(__('Discount for %s', 'conditional-product-discount'), $item['data']->get_name()), -$discount);
                    break;
                }
            }
        }
    }
}

new Conditional_Product_Discount_Plugin();