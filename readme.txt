=== WPSN: Instant Social Network ===
Contributors: simon.goodchild
Donate link: https://wpsn.site/
Tags: social network, social media, community, groups, forum
Requires at least: 4.7
Tested up to: 6.6.1
Stable tag: 0.8.7
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Instantly and **easily** turn your website into a social network! Profile, Posts, Photos, Videos, Friends, Alerts - and more!

== Installation ==

Add this plugin via your WordPress admin page (Plugins -> Add New Plugin) and search for "WPSN".

Or visit the [WordPress Repository](https://wordpress.org/plugins/wpsn-instant-social-network/), download it, and upload the ZIP file to your site via your WordPress admin page (Plugins -> Add New Plugin -> Upload Plugin).

== Description ==

Instantly and **easily** turn your website into a social network! Profile, Posts, Photos, Videos, Friends, Alerts - and more!

* The emphasis is on a clean and simple interface for users.
* Powerful, yet easy for admins to customise with many options.

= WPSN is in early access (v0.x) =

Your patience and feedback is encouraged and appreciated to continuously improve WPSN.

= Features =

* Key principle of "Keep it Simple" (yet powerful)
* Profile page with your posts, replies - messages, images, and YouTube videos.
* Home activity stream to see all your friends activity.
* And yes, friends!
* Comes with a friendly Login and Register page.
* Live notifications as they happen.
* Loads of customisation options for admins - colours, design and more!
* Works with any WordPress theme - [check out the tips for your Theme](http://wpsn.site/themes/).
* Built "the WordPress" way.

Want reactions (like, love, etc), private chat, and member block? (available now)

How about groups, follow, forums and saved posts, and more? (coming soon)

Boost your social network with additional plugins via the [WPSN website](http://wpsn.site/).

**Did I mention a free theme for a clean instant social network?**

I am committed to ensuring WPSN works with popular WPSN Themes - [check out the tips for your Theme](http://wpsn.site/themes/).

You can also get a free WPSN Theme for a "social network in a box" from the [WPSN website](http://wpsn.site/).

= Want to try it out? =

Join [WPSN website](http://wpsn.site/) and see it in action, and have a go!

https://www.youtube.com/watch?v=xLkBcldELZE

== Frequently Asked Questions ==

= Is WPSN free? =

The core plugin, with all the features listed above is free. All updates will be free.

As mentioned above, additional plugins enhance your social network with extra features.

= Does this really work with any theme? =

That's the goal. All popular themes are tested, check out the [theme tips](http://wpsn.site/themes/). If it's commercial, I may need to work with you. If it's a weird one, I promise I'll do my best!

= I've got a suggestion or a bug - where do I go? =

Please use the [support forum](https://wordpress.org/support/plugin/wpsn-instant-social-network/) for WPSN.

== Screenshots ==

1. Profile Page
2. Activity Page with photos
3. User profile options
4. Loads of admin customisation options
5. Zoom in on photos
6. Post YouTube videos

== Changelog ==

= 0.8.7 =

* Added: Added support for WPSN Theme to show additional menu items when logged out

= 0.8.6 =

* Added: Markdown support for posts (bold, italic, code, list items)

= 0.8.5 =

* Added: webp added to allow image upload formats

= 0.8.4 =

* Added: Links for support on the WPSN Admin page
* Fixed: "Scroll to top" icon for Astra theme
* Fixed: Minor tweak to support the Kadence theme

= 0.8.3 =

* Fixed: Setting avatar and cover image for Astra theme

= 0.8.2 =

* Fixed: Menu transition for Astra theme
* Changed: Activity auto-load to manual so under user's control

= 0.8.1 =

* Added: Set space at top of WPSN content (useful if setting a background image with some themes, for example).

= 0.8 =

* Note: First public release on WordPress Repository.
* Added: Set Maximum width of WPSN content to pixels or percentage (previously just pixels).

= 0.7 =

* Changed: All usage of SERVER variables now sanitize early and escape late (with validation where possible).

= 0.6 =

* Note to WordPress Plugin Repository admins: Regarding Warnings on functions.php lines 554 and 601. The CSS is embedded in the <head> of the page as per recommended best practice.  $css is escaped.
* Note to WordPress Plugin Repository admins: To confirm that PNP ini_set for memory limit is temporarily set in two functions (wpsn_save_avatar() and wpsn_save_cover()), and then returned to the original value as per recommendation via email from plugins@wordpress.org.
* Changed: Additional sanitization and escaping to meet security requirements.

= 0.5 =

* Note to WordPress Plugin Repository admins: To confirm that the readme.txt file includes information on instructions under "Resources" for the minimized version of the croppie library, with links to obtain the uncompressed versions of the JS file and the CSS file.
* Changed: Additional sanitization and escaping to meet security requirements.
* Removed: .psd Photoshop files from img folder
* Tested: With WordPress version 6.6.1

= 0.4 =

* Added: WordPress smilies support for activity posts.
* Added: Set corner radius for design elements.
* Added: Security enhancements for WordPress repository.
* Added: Now includes options showing avatar in menu, show menu when logged out, and show menu labels, into core plugin (from WPSN theme).
* Changed: Improved readme.txt to include resources of Croppie and YouTube, with links to service and terms.
* Changed: Dynamic styles added to <head> of page via hooks.
* Changed: Memory limit for image cropping (ini_set) only temporarily changed for single purpose in function and then reset to what it was.
* Changed: Sanitization added to code where missing.
* Changed: Added wpsn_ prefix to add_dynamic_background_css and has_shortcode_in_any_page functions.
* Changed: Added wpsn_ prefix to $allowed_tags variable.
* Removed: Removed commented out lines loading Croppie remotely as not required.
* Removed: Removed return_bytes function as not used.

= 0.3 =
* Added: Can now paste an image into a post/reply from clipboard.
* Added: "Change" button on profile page (when hovering over header cover/avatar).
* Added: Automatically load more posts when "Load more" is in view.
* Added: "Load more" for replies.
* Added: Banned words (replaced with ***** or something you choose).
* Added: Post creation date stored in addition to modified date.
* Added: Live "count bubbles" for Alerts and Friends on Menu (if WPSN Them activated).
* Added: Set a page background image, with option for only on WPSN pages.
* Added: Set colors for boxes, text, input areas and so on.
* Added: Translations for English, French, German, Spanish and Hindi.
* Added: Hooks and filters to support WPSN Pro plugins.
* Changed: Sent and Received friends requests only show if there are any.
* Changed: Set maximum width for login and sign-up forms.
* Changed: Re-styled Friends/Search/Edit Profile/Alerts pages so "background friendly".
* Changed: Improvements for mobile display.
* Changed: Improved retrieval of posts.
* Changed: Minor styles changes to align font color and size, and button visibility.
* Fixed: Output on Home/Activity page.
* Note: Verified as WordPress multi-site compatible.

= 0.2 =
* Changed: Changes to adhere to WordPress Plugin checks.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 0.8 =
* Upgrade for latest security changes and features.
* Purge all cached files on your server after upgrading.

= 0.7 =
* Upgrade for latest security changes and features.

= 0.6 =
* Upgrade for latest security changes and features.

= 0.5 =
* Upgrade for latest security changes and features.

= 0.4 =
* Upgrade for latest security changes and features.

= 0.3 =
* Purge all cached files on your server after upgrading.

= 0.2 =
* Upgrade to ensure security and performance changes.

= 0.1 =
* Provided for testing and evaluation.

== Resources ==
* YouTube used as an external 3rd party service and is required to display videos activity posts embedded in HTML. https://www.youtube.com. Terms of Service at https://www.youtube.com/static?template=terms.
* Croppie code included, and required to crop uploaded images for avatar/profile images. Terms of Service, and Download, from https://foliotek.github.io/Croppie. Croppie was built by Foliotek for use in Foliotek Presentation. https://www.foliotek.com. Minified versions of the JS and CSS files are included in this plugin - you can get the full code version via their website.