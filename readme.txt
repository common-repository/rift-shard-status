=== Rift Shard Status ===
Tags: widget,rift,status,shard
Requires at least: 2.9.2
Tested up to: 3.5.2
Stable tag: 1.0.2

Shows the population, queue size and type (PvP, PvE, RP) of a shard (server) from the MMORPG Rift.

== Description ==
This addon shows the status of a server (shard) of the MMORPG Rift. It shows if the server is online, population (low/medium/high/full), queue size, language and so on.

I'm currently unsure how to "stlye" the information, for now most text is encapsulated with "spans" to make it possible to change the attributes via css. An example is included and can be deactivated in the settings. You can then copy the css into your own stylesheet and adapt it without the fear that they get overwritten the next update. If you create something cool, I would like to include it into this plugin, if it is not too site specific. Just contact me on the wordpress page.

If the plugin isn't available in your language, please help with the translation, check the FAQ for more information.

== Installation ==
Nothing fancy, just like any wordpress addon. After the installation make sure the cache directory (`wp-content/plugins/rift-shard-status/cache`) is writable by wordpress.

If you don't use the automatic installer in the wordpress backend, try the following:

1. Upload and unzip the plugin to the `/wp-content/plugins/` directory
1. Make sure the cache directory (`wp-content/plugins/rift-shard-status/cache`) is writable by wordpress.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Optionally configure the plugin in the settings tab


== Frequently Asked Questions ==
= Can I use this addon without the widget? =

Well, you can add something like the following in your themes code:
`<div class="riftss">
<?php
$instance = array();
$instance['shard'] = "yourShardName";
$instance['region'] = "eu"; // or "us"
$instance['cache_time'] = 300; // in seconds
$instance['show_last_update'] = true; // or false...
echo(rift_shard_status_html($instance));
?>
</div>`

= Rift Shard Status isn't available in my language. Can you add $LANG to the translations? =

Chances are very good that I don't speak $LANG well enough to translate the plugin myself. But you can help translating it into your language. Download the translation file from http://plugins.svn.wordpress.org/rift-shard-status/trunk/i18n/rift_shard_status.pot and translate the strings there. You can do this with a text editor or special tools.

You can find guides about translations all over google, for example: http://forums.lesterchan.net/index.php?topic=108.0

= I'm trying to use a translated version, but it always shows the wrong language =
If the plugin isn't translated into your language yet, it will always show English strings.

If you know the plugin is translated into your language, but it still only shows in English, check that your blog is set up for another language: http://codex.wordpress.org/Installing_WordPress_in_Your_Language


== Screenshots ==
1. The status of the shard I'm currently playing on, shown on my blog.

== Changelog ==
= 1.0.2 =
* fix for the new status URL that came with the new f2p site
= 1.0.1 =
* a release after over 18 month to update the shown compatibility version
= 1.0 =
* added the possibility to translate the plugin. If you want to translate the addon into your language, please take a look at i18n/rift_shard_status.pot and contact me with the results. Thanks!
* added German translation.
* fixed a small bug with the last update time.
= 0.9.2 =
* fixed an embarrassing bug that showed all servers as PvP/RP...
= 0.9.1 =
* brought a little bit of color into the plugin. Change the CSS to your liking.
= 0.9 =
* First public release
= 0.8 =
* Fidling around

== Restrictions ==
No restrictions so farm that I'm aware of. If you find out Rift Shard Status doesn't work with another addon, please contact me.
