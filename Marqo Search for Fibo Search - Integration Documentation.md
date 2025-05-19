# Marqo Search for Fibo Search - Integration Documentation

## Overview

This WordPress plugin integrates Marqo Search with FiboSearch (formerly Ajax Search for WooCommerce) to enhance search results in WordPress sites. The plugin intercepts search queries from FiboSearch, enriches them with results from Marqo's vector search engine, and presents a unified set of results to users.

## Features

- Seamless integration between Marqo Search and FiboSearch
- Admin interface for configuration and testing
- Result caching for improved performance
- AJAX support for dynamic search results
- Custom styling for Marqo-sourced results

## Requirements

- WordPress 5.0+
- WooCommerce 4.0+
- FiboSearch (Ajax Search for WooCommerce) plugin
- Marqo Search API access (API key and endpoint)

## Installation

1. Upload the `marqo-fibo-integration` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Marqo Search to configure the plugin

## Configuration

### Marqo API Settings

- **API Key**: Your Marqo API key
- **Endpoint**: The URL of your Marqo API endpoint
- **Index**: The name of the Marqo index to search in

### Plugin Settings

- **Enable Integration**: Toggle to enable/disable the integration
- **Results Limit**: Maximum number of results to fetch from Marqo
- **Cache Time**: Duration (in seconds) to cache search results

## How It Works

1. When a user performs a search using FiboSearch, the plugin intercepts the search query
2. The query is sent to Marqo Search API to retrieve relevant results
3. Marqo results are processed and formatted to match FiboSearch's format
4. The results are merged with FiboSearch's original results
5. The combined results are presented to the user

## Technical Details

### Integration Points

The plugin uses the following WordPress hooks and filters to integrate with FiboSearch:

- `dgwt/wcas/search_results`: Filter to modify search results
- `wp_enqueue_scripts`: Action to add frontend JavaScript
- `dgwt/wcas/search/started` and `dgwt/wcas/search/results`: JavaScript events for frontend integration

### Caching

Search results are cached using WordPress transients to improve performance. The cache duration is configurable in the admin settings.

### Customization

Developers can customize the integration by:

1. Modifying the result processing in the `process_marqo_results` method
2. Adjusting the result merging logic in the `merge_results` method
3. Customizing the frontend styling in the JavaScript file

## Troubleshooting

### Common Issues

1. **No Marqo results appear**: Verify your API key and endpoint in the settings
2. **Connection errors**: Ensure your server can reach the Marqo API endpoint
3. **Slow search performance**: Increase the cache time in settings

### Testing Connection

Use the "Test Connection" button in the admin interface to verify your Marqo API configuration.

### Clearing Cache

If you make changes to your Marqo index or configuration, use the "Clear Cache" button to refresh the search results.

## Support

For support or feature requests, please contact the plugin developer.
