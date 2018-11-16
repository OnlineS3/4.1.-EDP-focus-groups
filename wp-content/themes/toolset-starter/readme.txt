=== Reference site theme ===
1.4.2
- New header&footer default logo and image
- Simplified default Layouts
- Adjusted styling for new pagination in Views
- Adjusted styling for new forms in Views and CRED
- Custom CSS is minified now
- Updated WooCommerce products listing layout - fixed sorting
- Adjusted CSS to new WooCommerce styling
- Fixed problem with too wide .wp-caption
- Fixed problem with label margin in search widget
- Fixed that Toolset Starter cannot be registered as Toolset Base Theme


1.4.1
- Added default Layouts for WooCommerce archive and single product
- Minor changes in styles
- Minor changes in text domains - they are more consistent now

1.4
- Added step-by-step installer for downloading necessary plugins and default Views and Layouts
- Added a filter telling Toolset plugins that Toolset Starter is loading its own shiny Bootstrap
- Improved Layouts integration
- Updated Bootstrap to version 3.3.7
- Changed library used to generate color variations in PHP
- Fixed the issue with recent post slug added to body tag in archives
- Removed not working "menu_floating" option from Theme Customizer (Layouts handles that now)

1.3.9
- A warning message is always shown when Layouts plugin is enabled and no layout is assigned - no matter what is set in Layouts settings
- Fix for Color customizer for pagination
- Fix for menu on mobile
- Fix for fixed menu position
- Styling for dropdown in widget menu
- Fixed problem with PHP notice when Primary Color customization is disabled
- Default Sidebar is always defined - Layouts plugin allows to use Widget Areas now
- Fixed typo in header.php and header-layouts.php
- Improved WP gallery responsiveness
- Moved helper classes from theme.scss to style.scss
- Update for Language Switcher integration - it is not needed in WPML 3.6+


1.3.8
- Adjusted styling for archive pagination
- Adjusted styling for sorting links in sortable table header
- Small fixes for menu

1.3.7
- Fixes an issue with add_filter on render Layout
- Fixes enqueue for non existing style
- Fixes CSS in front-end without path

1.3.6
- Theme adjusted to updated Layouts plugin
-- Layout not assigned functionality now comes from Layouts plugin
-- Improved mechanism of assigning Layouts to pages
- Fixed problem with terms levels in CRED forms

1.3.5
- Cosmetic fixes
- Improvement on admin ajax url call for front-end usage

1.3.4
- Improvements for WPML language switcher
- Deleted !default from _variables.scss file
- Pagination styling adjusted to the new Views pagination policy
- Added missing .bg-gray-dark, fixed problem with text color on .bg-gray
- Fixed Views codemirror dependency issue
- Added Advanced setting to disable global Bootstrap .container wrapper from the theme

1.3.3
- Implemented changes in language switcher for the main menu only.
- Updated language selector for Bootstrap nav menus.

1.3.2
- Bug fixing

1.3.1
- Fixed problems with WPML language switcher
- Added some helper classes, e.g. border-primary

1.3
- Changed the way how styles are loaded - easier Child Theme creation
- Improved menu customization
- Improved Layouts Import/Export
- Bug fixing

1.2
- Updated Bootstrap to v3.3.5
- Moved styles for WooCommerce to separate file
- Moved Theme specific styles to separate file
- Added Custom CSS section in the Theme Customizer
- Added Advanced section in the Theme Customizer. Now you can:
-- Disable theme specific styles - leave only Bootstrap and basic WordPress styling e.g. for galleries
-- Disable styles regarding WooCommerce
-- Disable Primary Color setting
- Improved integration with Views
- Bugfixing

1.1
- Improved integration with Layouts - default layouts defined
- Improved styling for WooCommerce
- Added possiblity to use the theme with Views only
- Added WordPress Customizer
-- Primary Color
-- Header Background Image (when Layouts not activated)
-- Header Logos (when Layouts not activated)
-- Widget Areas in header and footer (when Layouts not activated)

1.0
- Theme release for Classifieds Reference Site
