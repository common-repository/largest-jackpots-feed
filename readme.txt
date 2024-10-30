=== Largest Jackpots Feed ===
Contributors: kuopassa
Tags: jackpots,slots,feed
Donate link: https://www.paypal.me/kuopassa
Requires at least: 5.1.0
Tested up to: 5.1.1
Requires PHP: 7.0
Stable tag: trunk

Place [largest_jackpots_feed] shortcodes in your WordPress site.

== Description ==
Create one or more shortcodes like:

[largest_jackpots_feed limit="5" offset="0" wraptag="ol"]

The shortcode can have currently the following attributes:

- limit
- offset
- wraptag

Please note that:

- Attributes "limit" and "offset" are integers, and "wraptag" is a string.
- The smallest value for "limit" is 1, and largest is 10.
- For "offset" the smallest value is 0, and largest is 9.
- "wraptag" can either be "ol" (ordered list), or "ul" (unordered list).
- Each jackpot is wrapped in a "li" tag.
- The HTML created by a shortcode does not contain any CSS.

== Installation ==
Install and enable.

== Changelog ==
First version, 0.1, was released 2nd April 2019.