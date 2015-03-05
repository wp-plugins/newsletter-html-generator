=== Newsletter HTML Generator ===
Contributors: kosteg
Donate link: https://www.facebook.com/ekosteg
Tags: newsletter, html, snippets, generator, mailchimp, getresponse, madmimi, campaignmonitor, responsive
Requires at least: 4.0
Tested up to: 4.1.1
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generates HTML-code of ready to send newsletter using title, teaser, image, etc from your post and templates you provide

== Description ==

Newsletter HTML Generator plugin extracts title, teaser (or excerpt), author name, featured image, permalink, shortlink, date from current post and generates full HTML-code of ready to send newsletter based on the templates you provide. You just copy and paste the final HTML-code in your favorite newsletter sending service like Mailchimp, GetResponse, Campaign Monitor, etc.

### How does it work?
The plugin makes custom post type "Email Templates".
1. First, you create as many templates as you need (for example "Mailchimp responsive", "Weekly newsletter", etc). You can get templates from newsletter service or design your own.
1. After that you open any of your regular posts in edit-mode, and choose any Email template you created before. The plugin instantly generates ready-to-send HTML-code using the post's title, teaser, etc.

The plugin doesn't send newsletter, doesn't create signup forms, doesn't segment your subscribers! It only generates ready to send HTML you can use with ANY Email service provider or Email marketing service.

== Installation ==

1. Upload the plugin's folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Does plugin send email-newsletters? =

No, it doesn't. It generates ready-to-send HTML-code you can use for any newsletter-sending software or service.

= Why should I use this plugin? =

Here is the example of context the plugin can help in.
You have a wordpress blog and run newsletter based on posts using some email service provider like Mailchimp.
Every time you create newsletter you have to manually copy and paste title, teaser, image, author name etc from your post to the newsletter service's editor.
To exclude this manual labor the plugin automatically generates ready-to-send HTML-code from your posts. You can copy it, then go to newsletter service provider, create blank newsletter from scratch and just paste generated HTML-code.

= What parts of my posts can be used in newsletters? =

Title, teaser (or if you don't have "read more" tag in post – the excerpt will be used), author name, featured image url, permalink, shortlink, publishing date.

== Screenshots ==

1. Example of the email template creating.
2. Example of generating newsletter HTML from blog post.

== Changelog ==

= 1.1.3 =
* Added {{{first10words}}} snippet. Useful for inserting to invisible first element, so google will use it as a snippet

= 1.1.2 =
* Fixing left contenteditable tags

= 1.1 =
* Fixing incorrect teaser generation
* Now you can add "contenteditable" atribute to some elements of your HTML-templates. After that you can edit your newsletter right in the preview and get final HTML code.

= 1.0 =
* Hurray
