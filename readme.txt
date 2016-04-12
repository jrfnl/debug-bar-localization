=== Debug Bar Localization ===
Contributors: jrf
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=995SSNDTCVBJG
Tags: debugbar, debug-bar, Debug Bar, Localization, Language, Translation, text domain, textdomain
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.0
Depends: Debug Bar
License: GPLv2

Debug Bar Localization adds a new panel to the Debug Bar which displays information on the locale for your install and the language files loaded.

== Description ==

Debug Bar Localization adds a new panel to the Debug Bar which displays information on the locale for your install and the language files loaded.

Additionally it will show you:

* The installed languages.
* Which text-domains were called from translation functions while no `load_..._textdomain()` call was found for that text-domain.
* For which text-domains `load_..._textdomain()` was called more than once.
* Which files WP tried to load to obtain a translation and whether this was successful.
* If successful: how many translated strings were found and when the translation was last updated.

= Important =

This plugin requires the [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugin to be installed and activated.

Also note that this plugin should be used solely for debugging and/or in a development environment and is not intended for use on a production site.

***********************************

If you like this plugin, please [rate and/or review](https://wordpress.org/support/view/plugin-reviews/debug-bar-localization) it. If you have ideas on how to make the plugin even better or if you have found any bugs, please report these in the [Support Forum](https://wordpress.org/support/plugin/debug-bar-localization) or in the [GitHub repository](https://github.com/jrfnl/debug-bar-localization/issues).



== Frequently Asked Questions ==

= Can it be used on live site ? =
This plugin is only meant to be used for development purposes, but shouldn't cause any issues if run on a production site.

= What is internationalization ? =
> Internationalization is the process of developing your plugin/theme so that it can easily be translated into other languages.

Ref: [Plugin Handbook](https://developer.wordpress.org/plugins/internationalization/) / [Theme Handbook](https://developer.wordpress.org/themes/functionality/internationalization/)

= What is localization ? =
> Localization describes the subsequent process of translating an internationalized plugin/theme.

Ref: [Plugin Handbook](https://developer.wordpress.org/plugins/internationalization/localization/) / [Theme Handbook](https://developer.wordpress.org/themes/functionality/localization/)

= How do I internationalize my theme/plugin ? =
For plugins, see the [Plugin Handbook on Internationalization](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/).

For themes, see the [Theme Handbook on Internationalization](https://developer.wordpress.org/themes/functionality/internationalization/).

= How do I localize my theme / plugin ? =
For plugins, see the [Plugin Handbook on Localization](https://developer.wordpress.org/plugins/internationalization/localization/).

For themes, see the [Theme Handbook on Localization](https://developer.wordpress.org/themes/functionality/localization/).

= Why is my `load_..._textdomain()` call not listed ? =

There are several potential reasons for this:

1. You might not be loading the translations correctly. See the above referenced handbook pages for more information.
2. You might be loading your translations selectively (lean loading), only on the pages they are needed. If so, make sure you are viewing such a page to see your translation listed.
3. You might be loading your translations too early or too late. Translations are best loaded on the `(admin_)init` hook. For more information see [this article](http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way).

In particular take note of the following:
If your textdomain is loaded before this plugin is loaded, _i.e. if you load your textdomain on the PHP file load from a must-use plugin_, _or_ if your textdomain is loaded very late, _i.e. after the admin bar has loaded_, this plugin can not pick up on the `load_..._textdomain()` call.


= Why won't the plugin activate ? =
Have you read what it says in the beautifully red bar at the top of your plugins page ? As it says there, the Debug Bar plugin needs to be active for this plugin to work. If the Debug Bar plugin is not active, this plugin will automatically de-activate itself.


== Changelog ==

= 1.0.1 (2016-xx-xx ) =
* Don't show warning about duplicate load calls on plugins page as that's caused by core, not by a plugin and the warning could be misleading.

= 1.0 (2016-01-13) =
* Initial release.


== Upgrade Notice ==

= 1.0 =
* Initial release.


== Installation ==

1. Install Debug Bar if not already installed (https://wordpress.org/plugins/debug-bar/).
1. Extract the .zip file for this plugin and upload its contents to the `/wp-content/plugins/` directory. Alternatively, you can install directly from the Plugin directory within your WordPress Install.
1. Activate the plugin through the "Plugins" menu in WordPress.

Don't use this plugin on a live site. This plugin is **only** intended to be used for development purposes.


== Screenshots ==
1. Debug Bar Localization displaying basic statistics.
1. Debug Bar Localization displaying textdomains without a 'load call'.
1. Debug Bar Localization displaying the load textdomain calls made.

