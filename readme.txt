=== VouchSafe CAPTCHA Replacement ===
Contributors: sharethink 
Donate link: http://vouchsafe.com
Tags: comments, spam
Requires at least: 3.1
Tested up to: 3.2
Stable tag: trunk


== Description ==

VouchSafe is an easy, effective replacement for comment and registration CAPTCHAs that's highly secure, and easier for your users to complete.

You can use VouchSafe to secure your comments and registration from spam. VouchSafe is easy to use, and very effective in stopping spammers. It's the first Human Interactive Proof that use an adaptive AI to create challenges that only humans can solve. You can learn more about VouchSafe at the [VouchSafe website](http://www.vouchsafe.com).

To solve a challenge, users either draw a line to join two objects that are related, or circle one object that doesn't belong in a group of related objects.

For visually or motor-impaired users, VouchSafe offers audio-based type-in challenges. VouchSafe is compatible with most smart phones and tablet computers.

VouchSafe requires users to register their domain to get an API key to activate their plugin. Your keyset allows you to customize the way VouchSafe will appear on each domain you manage. VouchSafe also offers support for SSL.

Full installation instructions with screenshots can be found in our [installation guide](http://www.vouchsafe.com/user-guide-for-wordpress).

To get an API key to activate VouchSafe on your domain, go to our [registration page](http://console.vouchsafe.com).

If you run into trouble [contact us](http://www.vouchsafe.com/contact) or you can check the posts on our [Facebook discussion page](http://www.facebook.com/vouchsafe.me?sk=app_2373072738).



== Installation ==

How to install and activate VouchSafe on your website:

1. Upload `vouchsafe_wp.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Register for an API key at http://console.vouchsafe.com
1. Activate the plugin.
1. Edit the plugin and enter your key values.
1. log into your acount at http://console.vouchsafe.com to configure the look and feel of VouchSafe on yourwebsite

OR

1. download `vouchsafe.zip` to your computer and use the `upload` button to install it in your plugins
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Register for an API key at http://console.vouchsafe.com
1. Activate the plugin.
1. Edit the plugin and enter your key values.
1. log into your acount at http://console.vouchsafe.com to configure the look and feel of VouchSafe on yourwebsite

Note: When filling in your site details at console.vouchsafe.com, type in the top level domain, (without the "www"). Don't select the "Global" option for your domain unless you're creating content to be syndicated on other domains.

The console server takes a few minutes to replicate changes to all server instances, so it may take 3-4 minutes for your keys to become active with the styles settings you chose.

You don't need to register your domain in order for this plugin to work. However, if you don't activate your plugin with an API key, you will be unable to customize the way it looks on your website.


== Frequently Asked Questions ==

= How do you guarantee the availability of your service? =

We have taken every precaution to guarantee that VouchSafe is available and responsive. VouchSafe is hosted on multiple, geographically distinct instances of the Amazon cloud service to provide the best possible speed and availability.

= Does VouchSafe work with SSL? =

Yes it does. Both console.vouchsafe.com, (your account managment console) and api.vouchsafe.com (the service itself) are available with 256 bit encryption.

= Why doesn't VouchSafe work with my contact form? =

Unlike Drupal and Joomla, WordPress doesn't have a core contact component, (it really ought to), so there's no way for us to inject our code into the particular plugin you may have installed on your website.  We'll be actively canvassing contact plugin developers to include the option to activate VouchSafe for their plugins.

= What about localization? =

We are currently working on integrating our localization with our cultural diversity localization engine, and we have not yet made our localization service publicly available. We'll be announcing availability within the next few months.

= Does VouchSafe require Flash or Silverlight? =

Absolutely not. If you watch the videos on our website, you'll see that VouchSafe is perfectly happy on the iPad, the iPhone, and a myriad of Android powered devices. VouchSafe is delivered as pure HTML5 or as an AJAX app where HTML5 is not available.

= I installed VouchSafe and I still got spam. What happened? =

The VouchSafe plugin is designed to integrate with the core WordPress comments and registration systems. There are some vulnerabilites particular to trackbacks and certain combinations of other plugins in WordPress.  You may need to deactivate trackbacks or secure them with another plugin. If you are using a third-party plugin to manage trackbacks, you may want to ask the developer if they have an update to integrate it with VouchSafe or another validation mechanism.

== Screenshots ==

1. Typical VouchSafe challenge shown (this is the default interface).

== Changelog ==

= 1.1.754 =
* VouchSafe plugin can now be initialized and work without entering key values.

= 1.1.733 =
* Revised plugin to use session data instead of the database to temporarily store form data while validating input

= 1.1.691 =
* Updated plugin to address issues with rejected spam triggering a "held for moderation" announcement.
* Improved documentation.

= 1.1.685 =
* FIxed bypass vulnerability

