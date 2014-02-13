=== Paragraph Commenting ===
Contributors: Chris Knowles
Tags: comments, ajax, annotations
Requires at least: 3.6
Tested up to: 3.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin brings paragraph commenting to virtually any WordPress theme.

It is built on top of Zane Matthew's Inline Ajax Comment plugin and his original readme is kept here for an explanation of his code.

To read about how I changed the original plugin to provide annotations go to : http://premium.wpmudev.org/how-to-have-comments-just-like-medium



=== Inline Ajax Comments ===
Contributors: ZaneMatthew
Donate link: http://zanematthew.com/
Tags: comments, ajax, inline
Requires at least: 3.6
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays a single line textarea for entering comments, users can press "enter/return", and comments are loaded and submitted via AJAX.

== Description ==

This plugin places a comment form similar to Facebook, only displaying a single line textarea, which submits when the user presses "enter/return" on their keyboard.

= Features =
* Single line textarea
* Auto expanding textarea
* AJAX submitted comments
* AJAX loaded comments
* Admin settings for additional styling


== Installation ==

1. Install the plugin via WordPress or download and upload the plugin to the `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= The Register link is not working? =
Make sure that "Anyone can register" is checked in "Settings --> General"

= Where is the "url", "email" field? =
Users can add their "url" or "email" by clicking the "more" link next to the textarea.

= Doesn't this prevent search engines from seeing comments? =
At the moment it does, [Google Ajax Crawling](https://developers.google.com/webmasters/ajax-crawling/docs/getting-started) will be added in a later version.

= Does this work with Custom Post Types? =
Yes

= Does this work on Pages? =
No

= Does this support paging? =
At the moment no, later versions might.

== Screenshots ==

1. As seen when user is logged in
2. Admin settings
3. As seen when user is not logged in

== Upgrade Notice ==

* Check settings

== Changelog ==

= 1.2.1 =
* Enhancement: Comment area now toggles from 1 line to multi-line
* Enhancement: The following `html` tags are now allowed `<a href="" title="">`, `<blockquote>`, `<code>`, `<em>`, `<strong>`
* Enhancement: Added additional styling for post authors
* Enhancement: User name links to their website
* Enhancement: You can now share direct links to comments
* Enhancement: Added setting to keep comment box open

= 1.2 =
* Comments now work on Pages (not just posts)
* Adding a setting to keep the 'more' section open

= 1.1 =
* Removing dynamic functions for older PHP support
* Removing dead code

= 0.1-alpha =
* Initial version
