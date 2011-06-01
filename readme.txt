=== BP Export Users ===
Contributors: holizz
Github link: https://github.com/dxw/bp-export-users
Tags: buddypress, export
Requires at least: 3.1.3
Tested up to: 3.1.3

Allows exporting of all user data from WordPress and BuddyPress to CSV.

== Description ==

It adds a menu item "Export Users" under the "Tools" menu.


== Installation ==

1. Unzip it where it goes.
2. Enable it.
3. It may well not export the fields you need it to export. You can change the fields to export that come from WordPress' `get_users()` by modifying the `bp_export_users_wp_fields` filter. And the fields that come from BuddyPress' `BP_XProfile_ProfileData::get_all_for_user()` may be changed with the `bp_export_users_bp_fields` filter.
