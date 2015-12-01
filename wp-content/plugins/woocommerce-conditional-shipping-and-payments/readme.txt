=== WooCommerce Conditional Shipping and Payments ===

Contributors: franticpsyx
Tags: woocommerce, conditional, checkout, restrictions, countries, gateways, shipping, methods, exclude, access
Requires at least: 3.8
Tested up to: 4.3
Stable tag: 1.1.8
WC requires at least: 2.2
WC tested up to: 2.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Use advanced rules to control the payment gateways, shipping methods and shipping countries/states available during checkout.

== Description ==

This powerful extension gives you full control over the countries/states, payment gateways and shipping methods available to your customers during checkout. Use its flexible set of conditions and create multiple rules to exclude payment and shipping options.

== Installation ==

1. Upload the plugin to the **/wp-content/plugins/** directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Changelog ==

= 1.1.8 =
 * Fix - Fatal error when attempting to get call 'get_available_payment_gateways()' from an admin page.

= 1.1.7 =
 * Fix - Show "Customer" condition in product-level restrictions.

= 1.1.6 =
 * Fix - Support for Flat Rate Boxes Shipping: Allow exclusions by method id.
 * Feature - Added new "Customer" condition to enable/disable restrictions by customer e-mail.

= 1.1.5 =
 * Fix - Minor admin styling fixes for WC 2.4.
 * Fix - WC 2.4 support: Enable deprecated add-on flat rate options in the Shipping settings panel.

= 1.1.4 =
 * Fix - WC 2.2 JS chosen compatibility.

= 1.1.3 =
 * Fix - Add support for payment gateway rules at the checkout->pay endpoint.
 * Fix - Shipping classes conditions when shipping class defined at variation level.
 * Fix - Duplicate shipping method checkout notices.
 * Feature - Added 'not in' Category and Shipping Class condition modifiers, which can be used, for example, to always exclude a payment gateway ** unless ** a product from the specified categories is present in the cart.

= 1.1.2 =
 * Fix - Support non-core Shipping Methods.

= 1.1.1 =
 * Feature - Support Table Rate Shipping rates in Shipping Method restrictions and conditions.
 * Feature - 'is not' modifier for the Billing Country and Shipping Country/State conditions.
 * Fix - Update checkout fields on State change.

= 1.1.0 =
 * Feature - Support add-on flat rates in the 'Shipping Method' condition.
 * Fix - Missing 'State / County' string in resolution messages under specific conditions.
 * Tweak - Updated conditions UI.
 * Dev - Refactored conditions API.

= 1.0.1 =
 * Fix - select2 localization in WC 2.3.6+.

= 1.0.0 =
 * Initial Release.

== Upgrade Notice ==

= 1.1.8 =
Fixed fatal error when attempting to get call 'get_available_payment_gateways()' from an admin page.
