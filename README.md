# Google Maps Store Locator Documentation

This is a wordpress plugin utilizing Google Maps to dynamically build searchable maps.  Currently only setup to search by Location Title.

## Setup

- Install the plugin and activate it.
- Add locations to the "Locations" tab in the sidebar.
- No need to lookup Latitude & Longitude, simply add your address and click save.  GMSL will automatically update your geolocation.

## Shortcodes

To display a google map on a page in WordPress, you can do either of the following:
- To display one location, write a shortcode using the location's ID.

  `[store_locator id="12"]`

- To display a group of locations, group them by Location Type and configure your shortcode like so:

  `[store_locator type="Location Type"]`
  
- To display search, configure your shortcode like the following:

  `[store_locator type="Location Type" search="true"]`

