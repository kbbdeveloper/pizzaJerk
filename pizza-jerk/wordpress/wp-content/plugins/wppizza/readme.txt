=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, pizzaria, pizzeria, restaurant menu, ecommerce, e-commerce, commerce, wordpress ecommerce, store, shop, sales, shopping, cart, order online, cash on delivery, multilingual, checkout, configurable, variable, widgets, shipping, tax, wpml
Requires at least: PHP 5.3+, MySql 5.5+, WP 3.3+, PHP Sessions 
Tested up to: 4.7
Stable tag: 2.16.11.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Restaurant Plugin (not only for Pizza). Maintain your Menu (sizes, prices, categories). Accept COD orders. Multisite, Multilingual, WPML compatible.



== Description ==

- **Conceived for Pizza Delivery Businesses, but flexible enough to serve any restaurant type.**

- Maintain your restaurant menu online and accept cash on delivery orders.

- Set categories, multiple prices per item and descriptions.

- Multilingual Frontend (just update labels in admin settings page and/or widget as required). WPML compatible.

- Multisite/Network enabled

- Keeps track of your online orders.

- 140+ currencies supported.

- Shortcode enabled. (see <a href='http://wordpress.org/extend/plugins/wppizza/faq/' >FAQ</a> for details)


**To see the plugin in action with different themes try it at <a href="https://www.wp-pizza.com/">www.wp-pizza.com</a>**

**if you wish to allow your customers to add additional ingredients to any given menu item, have a look at the premium <a href='https://www.wp-pizza.com/'>"WPPizza Add Ingredients"</a> extension**

**gateways available to process credit card payments  instead of just cash on delivery at <a href='https://www.wp-pizza.com/'>www.wp-pizza.com</a>** 


== Installation ==

**Install**

1. Download the plugin and upload the entire `wppizza` folder to the `/wp-content/plugins/` directory.  
Alternatively you can download and install WPPizza using the built in WordPress plugin installer.  
2. Activate the plugin through the 'Plugins' menu in WordPress.  
3. You will find all configuration and menu options in your administration sidebar  


**Things to do on first install**

for consistency this document has now moved to the following location :   
<a href='https://www.wp-pizza.com/topic/things-to-do-on-first-install/'>https://www.wp-pizza.com/topic/things-to-do-on-first-install/</a>  
** I strongly encourage you to read it **  


**Uninstall**

Please note:  
although all options, menu items and menu categories get deleted from the database along with the table that holds any orders you may have received, you will manually have to delete any additional pages (such as the order page for example) that have been created as i have no way of knowing if you are using this page elsewhere or have changed the content/name of it.  
the same goes for the 3 example icons that come with this plugin as you might have used them elsewhere.


== Screenshots ==

1. frontend example
2. administration - widget
3. administration - categories
4. administration - menu item
5. administration - order settings (one of many option screens)
  

== Other Notes ==

= Translations provided by: =

* Italien:  Silvia Palandri  
* Hebrew:  Yair10 [&#1492;&#1500;&#1489;&#32;&#1489;&#1504;&#1497;&#1497;&#1514;&#32;&#1488;&#1514;&#1512;&#1497;&#1501;&#32;]  
* Dutch:  Jelmer  
* Spanish:  Andrew Kurtis at <a href="http://www.webhostinghub.com/">WebHostingHub</a>  
* German:  Franz Rufnak  

Many, many thanks guys and girls.  

Note: As the plugin gets updated over time and has some other strings and features added, the translations above (and future ones) will probably have a few strings not yet translated. If you wish, feel free to provide any of those missing and I will update the translations accordingly.  

If you want to contribute your own translation, feel free to send me your files and I will be more than happy to include them.  


= Demo Icons: =
please note that the icons used in the demo installation are <a href="http://www.iconarchive.com/show/desktop-buffet-icons-by-aha-soft.html">iconarchive.com</a> icons and not for commercial use.  
if you do wish to use any icon from this set commercially, please follow <a href="http://www.desktop-icon.com/stock-icons/desktop-buffet-icons.htm">this link</a> to purchase it.  


== Changelog ==

2.16.11.15  
* update: WP 4.7 - seemingly - changed some action hook priorities (or similar) which has affected some gateways. This update should fix this.  
12th December 2016  

2.16.11.14  
* fix: reverted  2.16.11.13 update as it broke a number of things 
4th November 2016  

2.16.11.13  
* tweak: improved compatibility with some 3rd party post cloning plugins  
4th November 2016  

2.16.11.12  
* fix: any possible entities javascript alerts were not decoded    
2nd November 2016  

2.16.11.11  
* fix: update to yesterdays 2.16.11.10 fix  
6th October 2016  

2.16.11.10  
* fix: minimum self pickup order value did not always get correctly applied with certain delivery settings     
5th October 2016  


2.16.11.9  
* fix: smtp sending was broken     
27th August 2016  

2.16.11.8  
* fix: additives were not mapped properly when displaying categories directly    
18th August 2016  

2.16.11.7  
* tweak: added missing gettext calls for reporting export labels     
* tweak: added wppizza_custom_report action hook to enable exporting of own customised report    
1st August 2016  

2.16.11.6  
* added: a number of action hooks to add to admin order history header if necessary     
* fix: KZT currency symbol was wrong  
* some minor - inconsequential - tweaks in a few functions  
25th July 2016  


2.16.11.5  
* added: option to set minimum order to be calculated after any discounts    
* added: value by order status in report export  
* tweak: increased z-index of minicart  
* fix: reporting export always exported all data even if range selected from dropdown  
21st July 2016  

2.16.11.4  
* fix: one of the css fields/declarations in email drag and drop templates did not get applied   
* tweak: added wppizza_filter_template_values to - add more parameters to template output if necessary  
29th May 2016  

2.16.11.3  
* tweak: optionally also display delivery note on orderpage and in emails (if delivery selected/applies and text was entered in the appropriate localization field - default value is empty !)   
22nd May 2016  

2.16.11.2  
* fix: orders that were placed while also creating a new account were not associated with that new account   
17th May 2016  

2.16.11.1  
* added: filters to allow other plugins to add additional parameters/variables to email/print template sections      
26th April 2016  

2.16.11  
* fix: WPML - under certain circumstances, categories might have been duplicated in slave language when using !all shortcode     
* fix: replaced deprecated function for WP 4.5 compatibility  
13th April 2016  

2.16.10  
* fix: erroneously set require_once when including phpmailer settings which - in conjunction with certain email template settings - might have caused (html) emails not be received by customer     
12th April 2016  

2.16.9  
* tweak: allow for "natural" rounding of tax fractions  
* tweak: add option to force WPML string translation registration if WPML was initially installed after WPPizza  
* fix: existing and saved templates did not allow adding of additional order form fields (since 2.16.6)  
8th April 2016  

2.16.8  
* fix: updating user profile data from order page for logged in users was broken (probably since 2.15)   
24th March 2016  

2.16.7  
* fix: some plaintext template filters were applied multiple times unnecessarily under certain circumstances   
10th March 2016  

2.16.6  
* fix: although correctly applied as saved, drag/drop (email|print) template sortorders were not reflected in admin screen  
* fix: tax not always displayed everywhere (though correctly applied) in non-english sites 
9th March 2016  


2.16.5  
* fix: could not delete (or add notes to) orders in child sites of a multisite install  
* fix: some template filter were possibily applied multiple times under certain circumstances  
* added: option for pickup to be selected as default  
* tweak: only show javascript alert (if enabled) when switching from default of pickup or delivery to opposite   
* tweak: added alert when popup blocking is enabled on admin printing 
1st March 2016  


2.16.4   
* fix: omissions in email summary data in non-english setups  
* tweak/added: advance notice for wppizza v3.0 (will display on next update)  
20th January 2016  

2.16.3   
* fix/tweak: sku metabox filter (if enabled) limited to running on post edit page only (might otherwsie interfere with other plugins elsewhere)   
* fix: deleting abandoned orders via cron or manually did not do anything due to script error     
15th December 2015  


2.16.2   
* added: ability to close shop right now without having to change opening times   
* fix: re-introduced a admin js function that was removed in 2.16 - 2.16.1 other plugins might rely on   
24th November 2015  

2.16.1   
* fix: unclosed tag affecting emails received at gmail/webmail account - introduced at 2.16  
21st November 2015  

2.16   
* added: optionally enable SKU's (wppizza->settings)  
* added: warning message in admin when using unsupported/outdated mysql version  
* added: option to set order page to be ssl/https  
* added: added filters for display of post (menu item) title and price labels in loops, emails , order page etc  
* added: added some more filters in various places for possible usage  
* fix : load admin-ajax.php as http if force_ssl_admin is set without the whole site being ssl  
* fix : possible orphaned templates/additional recipients eliminated  
* fix : category sorting - under certain circumstances - resulted in some categories not being listed in admin  
* fix : any order "from name" entities were not decoded in emails from header  
* tweak: minor css tweaks  
* tweak: some performance improvements in a few places  
* tweak: forcing plaintext templates to be sent as html when any recipients email address is gmail, outlook etc as these do not display pure plaintext emails correctly   
* tweak: made plaintext max linelength custom defineable constant  
18th November 2015  

2.15.0.20  
* tweak [WPML]: reduce number of WPML related queries     
* tweak: removing some unnecessary queries  
* tweak: optimising some queries   
21st October 2015  

2.15.0.19  
* tweak: adding sort flag and sorting by name to wppizza_sizes_available function   
* fix: elimination of some more possible php notices   
18th October 2015  


2.15.0.18  
tweak: made wp_new_user_notification backwards compatible for wp < 4.3.1  
11th October 2015  


2.15.0.17  
* tweak: some more css tweaks for order history   
9th October 2015  

2.15.0.16  
* tweak: a number of css tewaks in the order history screen to account for different OS   
9th October 2015  

2.15.0.15  
* fix:  wp_new_user_notification updated for wp 4.3.1 as it stopped sending the initial notification to new subscriber   
8th October 2015  

2.15.0.14  
* tweak:  a little bit more sanitisation and entity decoding in plaintext templates  
* tweak: better title category filtering when using install option 2 on non-english installations  
* fix: multisite -> order history -> parent site. not necessarily showing order in the right order if "check to have order history to use all orders of all child sites" is enabled  
7th October 2015  


2.15.0.13  
* tweak: stripped any possible html tags in menu item title, images/thumbnails title tag so as to not break loop layout if used  
* tweak: added "nocache" get variable to order page if caching (mainly for godaddy wordpress hosting, but might also stop other caching plugins to not cache the order page without explicitly excluding it )  
* tweak: updated tools->system info to account for mysql ports set in DB_HOST  
* added: ability to update/alter order table as appropriate for installations that had not yet updated to mysql 5.5+  
* fix: errors in css declarations of first additional print templates that were added for convenience on initial install / update with templates options (if you are updating and want to use a print template other than the broken one added by default, just add a new one and delete the broken one)  
5th October 2015  


2.15.0.12  
* added: a couple of simple helper methods to aid gateway development (comparing currencies/amounts)  
* tweak: upped tested with version  
* internal: some minor adjustments  
21st September 2015  


2.15.0.11  
* fix: sending html type emails was broken for old gateways in 2.15.0.10 (wrong variable name)  
* tweak: added propriatory iOS css declaration to enable item list scrolling in cart via touch (if necessary)  
18th September 2015  

  
2.15.0.10  
* added: some more strings to localization for editability    
* added: option to use smtp for wppizza related email sending (wppizza->settings)  
* added: some more classes on send order buttons for easier overlay/js gateway integration   
* tweak: removed tools->debug in favour of chceking wp debug settings  
* fix: old style order printing was broken  
14th September 2015  

2.15.0.9  
* fixed: formfields sort order was broken in 2.15.0.8    
8th September 2015  

2.15.0.8  
* fixed: Multisite orderhistory not updating order status on child sites  
7th September 2015  

2.15.0.7  
* added: some more info tools -> system info for debug purposes  
* fixed: some unclosed (self-closing) tags  
* fixed: some updates for external plugins/aaddons to more reliably filter and sort of order/confirmation page formfields  
5th September 2015  

2.15.0.6  
* added: $email_markup and $template_id as parameter to wppizza_phpmailer_smtp and wppizza_phpmailer_settings action    
* added: session test in tools -> system info  
* added: some more error output if mails fail (might help identifying some mail sending issues)  
* fix: some more possible php notices eliminated    
* fix: some more updates/fixes to still cater for old/legacy gateways  
2nd September 2015  

2.15.0.5  
* fix: some more possible phpnotices eliminated
* tweak/fix: eliminating @'s in cc and reply to *names* (phpmailer) to make some mailserver happy     
2nd September 2015  

2.15.0.4  
* fix: order history was sorted by id desc instead of order_date desc    
* tweak: no more customer name (doesn't belong there) in "from" email header name if "static from email" was set, but "static from name" was omitted  
* added: customer formfield values (customer name for example) can be added to subject line in emails instead  
* added: numerous filters for custom formtting of email subject line  
* added: selectively omit any attachments per email template  
* tweak: customer session variables now unescaped  
1st September 2015  


2.15.0.3  
* fix: error (introduced in 2.15) in the way "static from email" addresses were handled  
31th August 2015  

2.15.0.2  
* fix: order timestamps where not following WP timezone settings anymore  
31th August 2015  


2.15.0.1  
* tweak: css odd/even bg color in admin localization  
* tweak: minor layout cosmetics in plaintest template  
* fix: template preview was alwasy showing plaintext even if html  
* fix: templates were missing footer text (if set)  
30th August 2015  
 

2.15  

= IMPORTANT =

* THIS IS QUITE A BIG UPDATE.  
* although all efforts have been made for this update to be backwards compatible, please consider making a full backup of your current installation first (as always really) and do a test order after updating to make sure orders get still executed and emails send as expected.  
* as ever, if there are any problems, please let me know. thank you   


= ADDITIONS =

* general: email and print order template builder  
* general: price edit in quick edit  
* general: allow for discount when paying by COD  
* general: option [wppizza->tools] to disable sending of emails  
* general: added text option for opening hours if open 24 hours that day  
* general: XPF and CFP as currencies  
* admin order history: added a - hopefully more useful - popup with all details  
* reports: "worst" and "no" sellers in reporting screen  
* reports: ->export: accumulative number of times any item has been ordered within range of report dates  
* reports: ->export: sales value by payment type  
* filter: currency display filters (wppizza_filter_currency_display_left and wppizza_filter_currency_display_right depending on layout)  
* filter: price tier class (wppizza_filter_price_class) in loop templates  
* filter: surcharges (wppizza_filter_surcharges)  
* dev: basic skeleton gateway for developers (if using redirect/ hosted payment pages) in /add-ons  
* dev: allow for customisation of decimal places via constant WPPIZZA_DECIMALS  
* Multisite: added option to display blogname as well as category in cart/emails etc (wppizza->layout)  


= TWEAKS =

* various tweaks in reporting screen (adding categories if enabled etc)  
* some additional css for final confirmation page  
* only display DMARC notice on admin pages of wppizza  
* removed superflous mail() option to always use wp_mail() if plaintext  
* addded distinct classes to price tiers/prices  
* addded filters to order history query and results set  
* tip can also now be 0 if required  
* minor formatting tweaks in standard plaintext email template  
* wppizza-order.php template streamlined  
* log to file if updating of database with customer post variables fails for some reason  
* minified admin javascript  
* dev: WPPIZZA_CHARSET constant added  


= FIXES =

* minor install notices/errors when not installing default pages, categories and items.  
* possible rounding errors when hiding decimals  
* added missing categories display (if enabled) in final confirmation page  
* unable to enable/disable debug  (wppizza->tools)  
* taxrates and discounts were not shown (though correctly calculated) in non-english languages  
* changed collation of table to utf8mb4_unicode_ci to allow for 4byte characters  
* removed nonsensical nl2br in thankyou page markup  
* Multisite: blog_id did not always get restored correctly when displaying categories and sharing cart across multisite sub sites  
* Multisite: adding wppizza fields to user registration process did not work in multisite setups  


= REMOVED / DEPRECATED / CHANGED =

* REMOVED: templates/wppizza-phpmailer-settings.php removed. use provided action hooks in inc/phpmailer.php instead  
* REMOVED: templates/wppizza-order-email-subject.php in favour of filters-> wppizza_filter_email_subject_prefix, wppizza_filter_email_subject, wppizza_filter_email_suffix  
* DEPRECIATION NOTICE: wppizza_phpmailer_sent action hook will be removed (not documented anyway) in favour of wppizza_on_order_executed)  
* REMOVED/DEPRECATED: $emailPlaintext['db_items'] in plaiantext email template will now always store whats set if it's used for shop emails  


= UPDATES / INTERNALS =

* updated widget constructor for WP4.3 / php5  
* as per wp guidelines, locale name now hardcoded  
* order details stored more consistantly to enable easier/more reliable queries going forward   
* dropped irrelevant mail_construct column in wppizza_order table  
* minor changes/updates as well as removal of irrelevant variables in various templates  

28th August 2015  



2.12 - 2.14  
* intentionally skipped to indicate that 2.15 is quite a big update but not quite warranting a 3.0 version  

2.11   
* changelogs <= 2.11.x can be found in /changelogs  


== Frequently Asked Questions ==

= General Faq's =

for consistency and manageability the faq's have been moved to <a href='https://www.wp-pizza.com/forum/faqs/'>https://www.wp-pizza.com/forum/faqs/</a>

= Shortcodes = 

please see here <a href='https://www.wp-pizza.com/topic/wppizza-shortcodes/'>https://www.wp-pizza.com/topic/wppizza-shortcodes/</a>


= How can I submit a bug, ask for help or request a new feature? =

- leave a message on the <a href="http://wordpress.org/support/plugin/wppizza">wordpress forum</a> and I'll respond asap.  
- send an email to dev[at]wp-pizza.com with as much info as you can give me or 
- use the <a href="https://www.wp-pizza.com/contact/">"contact" form</a>, <a href="https://www.wp-pizza.com/forum/feature-requests/">"feature request" page </a> or <a href="http://www.wp-pizza.com/support/">support forum</a> on <a href="http://www.wp-pizza.com/">www.wp-pizza.com</a>


	
	>**additional premium add-ons can be found at <a href="https://www.wp-pizza.com/">www.wp-pizza.com</a>**  
	>



== Upgrade Notice ==

