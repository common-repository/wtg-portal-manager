=== Plugin Name ===
Contributors: WebTechGlobal
Donate link: http://www.webtechglobal.co.uk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: portal, portal manager, product portal, service portal, web portal, web portals
Requires at least: 3.8.0
Tested up to: 4.2.2
Stable tag: trunk

WTG Portal Manager assists us in managing related content for a product, service or event. 

== Description ==

Manage related content for a product or service in one place then offer it to visitors...in one place.
The job of this plugin is to pull related content of all types (even a WP menu) into one area for both visitors and administrators.
Everyone should have a better experience finding, updating, adding and removing information for the portals topic. That is goal number one. The
portal manager should streamline the creation of portals and guide the user to ensure no elements of the portal are missed. That is goal two.

What do you imaging the third goal should be in a plugin of this nature, let know in the plugins forum?

= Main Links = 
*   <a href="http://www.webtechglobal.co.uk/wtg-portal-manager/" title="WebTechGlobals Portal Manager">Plugins Portal</a>
*   <a href="https://trello.com/b/aqQwlxUU/easy-csv-importer" title="WebTechGlobals Portal Manager">Plugins Trello</a>
*   <a href="http://forum.webtechglobal.co.uk/viewforum.php?f=42" title="WebTechGlobal Forum for Portal Manager">Plugins Forum</a>
*   <a href="https://www.facebook.com/pages/WTG-Portal-Manager/360123887503176?ref=hl" title="WTG Task Manager Facebook Page">Plugins Facebook</a>
*   <a href="http://www.webtechglobal.co.uk/category/wordpress/wtg-portal-manager/" title="WebTechGlobal blog category for portal manager">Plugins Blog</a>
*   <a href="http://www.twitter.com/WebTechGlobal" title="WebTechGlobal Tweets">Plugins Twitter</a>
*   <a href="https://www.youtube.com/playlist?list=PLMYhfJnWwPWB_hCPD7hs-5dAa2O22nJFE" title="Official YouTube channel for the WTG Portal Manager">YouTube Playlist</a>

= Features List = 

1. Manage many portals.
1. Merge Twitter, phpBB and WP posts into a single "Updates" list.
1. Custom sidebar per portal.
1. Add custom menu to each portal.
1. High customization per portal is the goal, more to come soon!

= Work In Progress =
* Current Portal Selection menu on main page defaults to the first item.
* Too many configuration values are being stored in the settings array. Starting with sidebars move them to individual options.
* Serialize API credentials using key stored in plugin.
* Need a way to delete values in a form and reset it.
* Add functionality to the portal information in head of pages i.e. link to the portal, quick tools.
* Allow more than one Twitter account to be used on Updates list.
* Default values on Create Portal menus might be the cause of security warnings.
* Developer widget - will include portal ID and functionality for quickly changing portal. 
* Setup a Trello account for this project.

== Installation ==

Please install WTG Portal Manager from WordPress.org by going to Plugins --> Add New and searching "WTG Portal Manager". This is safer and quicker than any other methods.

== Frequently Asked Questions ==

= Why does portal does not display my chosen custom sidebar? =
Your theme may not support dynamic sidebars. The theme author may need to add the dynamic_sidebar() function
to the sidebar.php file in your themes folder. See more information here: 
http://codex.wordpress.org/Function_Reference/dynamic_sidebar

= Can I display multiple custom/dynamic sidebars in one portal? = 
Yes - it is possible but I may need to add support for your theme. It depends on the custom ID values being passed
to the dynamic_sidebar() function. Adding support for your theme will not take long at all so please drop a comment in the plugins
forum. 

= As a WebTechGlobal subscriber can I get higher priority support for this plugin? =
Yes - subscribers are put ahead of my Free Workflow and will not only result in a quicker response for support
but requests for new features are marked with higher priority.

= Can I hire you to customize the plugin for me? =
Yes - you can pay to improve the plugin to suit your needs. However many improvements will be done free.
Please post your requirements on the plugins forum first before sending me Paypal or Bitcoins. If your request is acceptable
within my plans it will always be added to the WTG Tasks Management plugin which is part of my workflow system. The tasks
priority can be increased based on your WebTechGlobal subscription status, donations or contributions you have made.

= Did WTG Portal Manager change one of my posts sidebars that I already set manually? =
I added this FAQ while creating the plugin because I'm allowing it to force a change to sidebar
that may have been done manually. Some users will want this but I'm ready to work with the users
who want something else. If you selected a special sidebar for your post/page manually on the Edit Post
screen. You may find that it is changed by WTG Portal Manager if you then assign that post or page to
a portal with a different main sidebar set. This scenario would indicate that you want/need the post to 
display widgets from two different sidebars. There may be a solution so please raise your situation on
the plugins forums.

== Screenshots ==

1. API accounts on a per portal basis but default ones for ease.
2. Working on one portal at a time allows all of the plugins views to help you focus on that portal. We active the portal we wish to edit.
3. A single form creates a new portal plus pages, a WP core menu, a WP core sidebar for displaying the menu and more to come.
4. Some common portal pages. Enter their existing ID or enter # to bulk create them.
5. Display forum content in the portal to encourage traffic to flow between portal and forum.

== Languages ==

Translators needed to help localize WTG Portal Manager.

== Upgrade Notice ==

Please update this plugin using your WordPress Installed Plugins screen. Click on Update Now under this plugins details when an update is ready.
This method is safer than using any other source for the files.

== Changelog ==
= 1.0.8 =  
* Feature Changes    
    * None
* Technical Changes
    * Bug fixed.
    
= 1.0.7 =  
* Feature Changes    
    * Developer menu for Admin Bar is now displayed on the frontside/public.
    * Ability to delete caches on a current view from the Developer menu.
* Technical Changes
    * class-globalui.php added and the class contains the developer menu which was moved from class-adminui.php.
 
= 1.0.6  =  
* Feature Changes    
    * Incorrect text replaced on Create New Portal Page form.
    * New page purposes added: information, steps, datatable, index and people.
    * Added link to notice for creation of new portal page.
* Technical Changes
    * A shortcode like [portalupdate timeline="WebTechGlobal"] now works better as the timeline value over-rides all others.
 
= 1.0.5 =  
* Feature Changes    
    * New tab added to Build section named Pages Table. Lists a portals pages and possible pages.
* Technical Changes
    * None
* Work In Progress
    * Current Portal Selection menu on main page defaults to the first item.
    * Too many configuration values are being stored in the settings array. Starting with sidebars move them to individual options.
    * Serialize API credentials using key stored in plugin.
    * Need a way to delete values in a form and reset it.
    * Add functionality to the portal information in head of pages i.e. link to the portal, quick tools.
    * Allow more than one Twitter account to be used on Updates list.
    * Default values on Create Portal menus might be the cause of security warnings.
    * Developer widget - will include portal ID and functionality for quickly changing portal. 
    * Setup a Trello account for this project.

= 1.0.4 =  
* Feature Changes    
    * None
* Technical Changes
    * User created sidebars are now stored in option "wtgportalmanager_sidebars".
* Work In Progress
    * Current Portal Selection menu on main page defaults to the first item.
    * Too many configuration values are being stored in the settings array. Starting with sidebars move them to individual options.
    * Serialize API credentials using key stored in plugin.
    * Need a way to delete values in a form and reset it.
    * Add functionality to the portal information in head of pages i.e. link to the portal, quick tools.
    * Allow more than one Twitter account to be used on Updates list.
    * Default values on Create Portal menus might be the cause of security warnings.
    * Developer widget - will include portal ID and functionality for quickly changing portal. 
    * Setup a Trello account for this project.

= 1.0.3 =  
* Feature Changes    
    * None
* Technical Changes
    * classes/class-adminui.php renamed to classes/class-adminui.php.
    * New classes/class-publicui.php added.
    * Bug fixed which was forcing default Twitter API credentials to be used.
* Work In Progress
    * Current Portal Selection menu on main page defaults to the first item.
    * Too many configuration values are being stored in the settings array. Starting with sidebars move them to individual options.
    * Serialize API credentials using key stored in plugin.
    * Need a way to delete values in a form and reset it.
    * Add functionality to the portal information in head of pages i.e. link to the portal, quick tools.
    * Allow more than one Twitter account to be used on Updates list.
    * Default values on Create Portal menus might be the cause of security warnings.
    * Developer widget - will include portal ID and functionality for quickly changing portal. 
    * Setup a Trello account for this project.

= 1.0.2 = 
* Feature Changes    
    * Twitter API option added to Global Switches form.
* Technical Changes
    * Fix for form submissions.
    * Transient cache keys were not unique per portal. Portal ID has been added.
    * Updates list shortcode no longer requires attributes.
    * Updates list shortcode will focus on using project/portal data for now.
* Work In Progress
    * Serialize API credentials using key stored in plugin.
    * Need a way to delete values in a form and reset it.
    * Allow more than one Twitter account to be used on Updates list.
    * Default values on Create Portal menus might be the cause of security warnings.
    * Developer widget - will include portal ID and functionality for quickly changing portal. 

== Donators ==
These donators have giving their permission to add their site to this list so that plugin authors can
request their support for their own project. Please do not request donations but instead visit their site,
show interest and tell them about your own plugin - you may get lucky. 

* <a href="" title="">Ryan Bayne from WebTechGlobal</a>

== Contributors: Translation ==
These contributors helped to localize WTG Tasks Manager by translating my endless dialog text.

* None Yet

== Contributors: Code ==
These contributers typed some PHP or HTML or CSS or JavaScript or Ajax for WTG Tasks Manager. Bunch of geeks really! 

* None Yet

== Contributors: Design ==
These contributors created graphics for the plugin and are good with Photoshop. The "fake celebrity pics" creators no doubt!

* None Yet

== Contributors: Video Tutorials ==
These contributors published videos on YouTube or another video streaming website. Please take interest in any ads that may appear while watching them!

* None Yet

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the WTG Portal Manager plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.

= When To Update = 

Browse the changes log and decide if you need any recent changes. There is nothing wrong with skipping versions if changes do not
help you - look for security related changes or new features that could really benefit you. If you do not see any you may want
to avoid updating. If you decide to apply the new version - do so after you have backedup your entire WordPress installation 
(files and data). Files only or data only is not a suitable backup. Every WordPress installation is different and creates a different
environment for WTG Task Manager - possibly an environment that triggers faults with the new version of this software. This is common
in software development and it is why we need to make preparations that allow reversal of major changes to our website.

