=== Simple Attribution ===
Contributors: section214
Donate link: http://pledgie.com/campaigns/17811
Tags: attribution, citation, cite, post, link
Requires at least: 3.0
Tested up to: 3.3.2
Stable tag: 1.1.2

A simple plugin to allow bloggers to add attribution to sourced posts.

== Description ==

Simple Attribution is just that... simple! It adds a meta box on post pages which allows bloggers to specify the name and url of the site a sourced article originated from. Assuming both these fields are filled out, it appends the attribution link to the bottom of the post.

Simple Attribution allows links to be generated in both text and image form, allowing you to customize it to the feel of your website. Natively, it includes 10 icons which can be used to identify the attribution link instead of the standard caption (which is editable through the options panel), and custom icons can be used as well.

Don't like where we put the link? You have the option to disable auto-attribution and put the link wherever you want it to display simply by adding &lt;?php $simple_attribution = new Simple_Attribution; $simple_attribution->display_attribution(); ?&gt; to your template!

== Installation ==

1. Unzip the downloaded 'simple-attribution.zip' file
2. Upload the 'simple-attribution' folder to '/wp-content/plugins' directory of your WordPress installation
3. Activate the plugin via the WordPress Plugins page

== Frequently Asked Questions ==

None yet

== Screenshots ==

1. The post meta box
2. The options panel

== Changelog ==

= Version 1.1.2 =
* Update support link

= Version 1.1.1 =
* Fix bug with WordPress installed in subdirectory not properly saving settings

= Version 1.1.0 =
* Moved to class-based structure
* Added proper I18N

= Version 1.0 =
* Initial release

== Todo ==

* Add support for custom taxonomies
