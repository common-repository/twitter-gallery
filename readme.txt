=== Plugin Name ===
Contributors: Tomas Vorobjov
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WFXMBAHD523VW
Tags: twitter, jQuery, ajax
Requires at least: 3.0
Tested up to: 3.03
Stable tag: 1.0

This plugin creates and maintains a twitter image gallery on a Wordpress page

== Description ==

The Twitter Gallery is a Wordpress plugin for aggregating images/photos 
posted to twitter. The gallery script searches the public twitter feed 
for photos and images sent by users from around the world. 

The initial release supports images posted to twitpic.com, yfrog.com,
img.ly, flic.kr, ow.ly/i and twitgoo.com

Everyone can have a photo included in the gallery by updating his/her 
twitter status with a message that includes (one or more) images uploaded
 to either of the 6 services mentioned above and the twitter hash tag 
 #<hash_tag> which is fully customizable through the administration dashboard.

**Requirements**

* PHP 5.2.0 or newer
* WordPress 3.0 or newer

== Installation ==

1. Upload the plugin files to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set the settings in "Twitter Gallery"
4. To create a gallery page simple go to add a new page and select
   'Twitter Gallery Page' from the templates dropdown. Set the title
   and publish an empty page.

== Frequently Asked Questions ==

= I changed my theme and the gallery stopped working
You only needs to deactive/active the plugin. Next version will be able
to handle theme changes automatically

== Screenshots ==
* none

== ChangeLog ==

= 1.0 =

* Re-introduced the 'manage photos' site
* Removed database
* Updated twitter authentication
* Updated admin panel (mostly added security)

* TODO:
  - css editor
  - change ajax loader (via upload or url)
  - support multiple galleries 
  - finish l10n  
  - add screenshots
  - handle theme changes without the need to deactive/active
  
= 0.9 - Pre-release =

This version is a pre-release and for testing purposes only. Settings,
such as the animated loader image, which are not configurable from the dashboard
in this version will be editable in the final release v1.0. 

== Upgrade Notice ==

= 1.0 =
* many updates, improvements, tweaks, wp admin security update, added twitter oauth, etc.

= 0.9 =
* The initial release of this plugin. .
  