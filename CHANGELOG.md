# Minify HTML Cache

All notable changes to Minify HTML Cache plugin and original [WSCMin](http://lyncd.com/wpscmin) plugin.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.1](https://github.com/abuyoyo/minify-html-cache/releases/tag/1.1/)
Release Date: Jun 8, 2020

* Add `WyriHaximus\HtmlCompress` minifier library and use as default.
* Requires PHP >=7.4

## [1.0](https://github.com/abuyoyo/minify-html-cache/releases/tag/1.0/) - Initial Release
Release Date: Apr 20, 2020

* Major refactoring of WSCMin.
* Minify HTML Cache is installable as a regular WordPress plugin.
* Plugin works with WP-Optimize cache plugin (as well as WP-Super-Cache plugin).
* Use composer autoload to load minifier libraries.
* Add `voku/HtmlMin` minifier library and use as default.

---

## WPSCMin changelog

## 0.7
Release Date: 2015-09-28

### WPSCMin.php
* Version bump to 0.7
* Bug fix for rename of Minify `JSMin.php` to `JSMinPlus.php`

## 0.6
Release Date: 2015-09-24

### WPSCMin.php
* Version bump to 0.6
* Updated for compatibility with current version of `Minify 2.2.0`. This only necessitated separate include of `CSS/Compressor.php`

## 0.5
Release Date: 2011-01-20

### WPSCMin.php
* Version bump to 0.5, GPL, prepped to be bundled with WP Super Cache
* Filesystem path of Minify libs can now be set in `wp-cache-config.php`
* Added `<!--[minify_skip]-->` protected text `<!--[/minify_skip]-->` syntax
* No longer minifies dynamic page when known user detected by Super Cache
* Commented out compatibility code for Super Cache 0.9.9.5 and earlier

## 0.4
Release Date: 2010-08-11

### wp-super-cache.diff
* Eliminated, manual patching no longer required

### WPSCMin.php
* Version bump to 0.4
* Moved to `plugins/` subdir of `wp-super-cache`, now functions as a plugin of WP Super Cache
* Various minor changes to code structure



## 0.3
Release Date: 2009-03-15

### wp-super-cache.diff
* updated to diff with WP Super Cache 0.9.1

### WPSCMin.php
* Reverted to pre-PHP 5.2.3 callback syntax per [this thread](http://groups.google.com/group/minify/browse_thread/thread/3781cbaf19a9f770)

## 0.2
Release Date: 2009-02-23

### wp-super-cache.diff
* updated to diff with WP Super Cache 0.9

### WPSCMin.php
* WordPress config screen button style now WP 2.7 style
