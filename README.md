# Quicklift

Contributors: kaynenh  
Tags: acquia_lift  
Requires at least: 4.7  
Tested up to: 4.95  
Stable tag: 4.7  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lift Personalization for Wordpress.

## Description

**Use with caution.**  
*This was built as a proof of concept so bugs surely exist.*

Based on work by John Money in [Acquia Lift CSV](https://github.com/johnmoney/acquia-lift-csv) and uses the [Content Hub SDK](https://github.com/acquia/content-hub-php).Does not support syndication. Creates a simple personalization custom post type to create content and adds necessary lift.js install script to the site.

## Installation

1. Download this repository and change the name from quicklift-master to quicklift
2. Run composer update
3. Place plugin in wp-content/plugins directory
4. Activate in Wordpress
5. Fill out credentials at Settings > Quicklift

## Frequently Asked Questions

### How do I use this?

1. Add a new personalization custom post type
2. Go to the front-end of the website
3. Activate Lift
4. Use personalization in Experience Builder

### What's next

1. Create personalizations in custom html widgets
2. Create slots through a custom widget
3. Automatic site id creation in settings form

## Changelog

### 0.1
* Initial release