=== Project Force Field ===
Contributors: Faison
Tags: apache, brute force, brute force attack, brute force protection, modrewrite, mod_rewrite, security, prevent, protection, hacker, protect, login, wp-admin, attack protection, cloudflare, bruteforce, exhaustive key search, dictionary attack, ddos, ddos protection, denial-of-service, htaccess, wp-login.php, user enumeration, enumeration
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: 0.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Save your WordPress sites and servers from certain death during brute force attacks with Project Force Field by Orion Group!

== Description ==
[Faison Zutavern](http://faisonz.com/ "Faison Zutavern's Personal Blog"), [Jon Valcq](http://profiles.wordpress.org/jvalcq "Jon's WordPress.org Profile"), and [Emma Edgar](http://profiles.wordpress.org/emmaee "Emma's WordPress.org profile"), from [Orion Group LLC](http://www.orionweb.net/ "Milwaukee Area Web Developers"), bring superior Brute Force Attack protection to WordPress with their new plugin, *Project Force Field*. By tracking failed login attempts and taking advantage of Apache's mod_rewrite module, Project Force Field stops Brute Force Attacks from bogging down your sites and servers.

*Special thanks to [Chris Aykroid](http://www.aykcreative.com/ "Ayk Creative | Graphic Support for your Projects") for the plugin banner :D*

= Contributing =

If you would like to contribute or fork Project Force Field, we currently have a repo on Bitbucket. You can [find it here](https://bitbucket.org/oriongroup/project-force-field/)

= Features! =

* **Sends a 403 error code to anyone visiting /wp-login.php** - All brute force attacks we've seen target /wp-login.php. By responding with a 403 error, **your WordPress files aren't loaded, the Database isn't queried**, and the attacker doesn't figure out your password.
* **Changes the default login url** - While a so-called hacker is being deflected by your new Force Field, you will log in with ease at /wp-admin/. When you do that, WordPress will redirect you to the new, proper login url.
* **Automatically changes the login when a Brute Force Attack is detected** - When too many login failures occur within a minute, Project Force Field shifts polarity! The new login you previously used now responds with a 403 error, and a large random number is now used as your login url! After some time, the login will return back to normal.
* **Unlimited polarity shifts** - If a Brute Force Attacker gets smart and writes a script to check for the new login url, Project Force Field will continue to detect the attack and change the login.
* **Define the login yourself** - By defining `OGFF_LOGIN` in your wp-config.php, you can set the login to be *almost* anything you want.
* **Stops WordPress User Enumeration Exploit** - Many brute force attacks use the WordPress User Enumeration exploit to easily figure out valid usernames. We stop that to protect your site, and respond with a 403 to save your server.

= Future Features! =

* **Multisite Support** - It's not there yet, that's pretty lame, so I'm going to fix that before anything else!
* **Adjust the login failure threshold** - Currently, Project Force Field assumes a brute force is underway when there have been 30 login failures within a minute. This might not be ideal for large websites, so we want to let you increase that amount to 300 if needed.
* **Add optional email notification for brute force events** - If you want to know when your website is under attack, we want to let you know. In a near future version, we will let you add email addresses to be notified of brute force attacks, and any other important related events that we add in the future.
* **Add last resort .htaccess password lockdown** - If a so-called hacker writes a script that continues to learn the new login url, Project Force Field won't help much. In an upcoming version, we will check to see how many times the login url was changed, determine if the Brute Force Attack is smart, and lockdown the login with an .htaccess password.

== Installation ==
https://codex.wordpress.org/Managing_Plugins#Installing_Plugins

== Frequently Asked Questions ==
= Why does my browser say "Access forbidden!" or "Error 403" when I try to login at &lt;your site&gt;/wp-login.php =

Because that's what Project Force Field does. You need to login by going to `<your site>/wp-admin/`

= How do I change safe-entrance.php to something else? =

You can specify your own login by defining `OGFF_LOGIN` in your wp-config.php file (normally found in your WordPress directory). If you wanted to change your login to sneaky-entrance.php, add the following as its own line: `define( 'OGFF_LOGIN', 'sneaky-entrance.php' );` 
Do not use slashes `/`, do not specify a file that exists, and do not specify a directory that exists!

= Does Project Force Field cause issues with WordPress for iOS or ManageWP? =

WordPress for iOS still logs in with Project Force Field enabled. Adding your site to ManageWP will still work as long as you install the ManageWP Worker plugin beforehand.

= Does this plugin work on Nginx, IIS, or anything else not Apache? =

Nope. We use Apache, so adding support for any other server wouldn\'t be productive for us. If you, however, are a programmer and know how to make this feature for your server of choice, take what you want from this plugin, develop your version, and let me know so I can link to it :)

= Couldn't you handle this with the WordPress Rewrite API? =

We wanted to avoid running PHP and loading WordPress just to block a request to wp-login.php. We were experiencing over 100 requests a minute, that started to eat up server resources fast! By taking advantage of Apache's mod_rewrite module, we can block all requests to wp-login.php without loading WordPress. It's great that WordPress has a Rewrite API, but it just isn't the right solution for Project Force Field.

= How do I enable mod_rewrite? =

You can find instructions here: http://codex.wordpress.org/Using_Permalinks#Fixing_Permalink_Problems

= How do I give write access to my .htaccess file? =

You can find instructions here: http://codex.wordpress.org/Using_Permalinks#Fixing_Permalink_Problems

= When in the Dashboard, WordPress asks me to log back in, but when I try to login it says "Forbidden" =

When your session expires in WordPress, you can be prompted to log back in. If a brute force attack is detected after the login window pops-up, you will get this message. We're working on adding a script to update that window when the login url is changed, until then, you will just have type &lt;your domain name&gt;/wp-admin/ to log back in.

== Screenshots ==

1. Website protected in under 10 seconds!

== Changelog ==

= 0.6.1 =
* **Bugfix**: Delayed checking if permalinks are used until the action 'init'. This fixes warnings when updating or activating Project Force Field.

= 0.6.0 =
* **Enhancement**: Added protection from WordPress User Enumeration.
* **Enhancement**: Added code to handle upgrades to Project Force Field.

= 0.5.1 =
* **Bugfix**: Prefixed the variable `$new_login` in the file `project-force-field.php` with `ogff_` to avoid potential conflicts with other plugins, themes, or custom code.
* **Bugfix**: Added code to stop Project Force Field from trying to work on non-Apache servers and on multisites.
* **Enhancement**: Added warning on multisites regarding how Project Force Field doesn't currently work on multisites.

= 0.5.0 =
* Initial Release.

== Upgrade Notice ==
= 0.6.1 =
This version eliminates an error/warning that displays when updating and activating Project Force Field.

= 0.6.0 =
This version adds protection against WordPress User Enumeration, which hackers tend to use before attempting a brute force attack.

= 0.5.1 =
This version stops Project Force Field from running on sites that it can't work on, which includes non-Apache servers and multisites. Also adds a quick bugfix to avoid potential plugin/theme conflicts.

= 0.5.0 =
You shouldn't have a version before this, so you should update now!
