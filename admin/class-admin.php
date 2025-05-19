<?php
/**
 * Admin class for Marqo Fibo Integration plugin
 */
class Marqo_Fibo_Admin {
    
    /**
     * Plugin settings
     */
    private $settings;
    
    /**
     * Constructor
     * 
     * @param array $settings Plugin settings
     */
    public function __construct($settings) {
        $this->settings = $settings;
    }
    
    /**
     * Initialize admin functionality
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings link on plugins page
        add_filter('plugin_action_links_marqo-fibo-integration/marqo-fibo-integration.php', 
            array($this, 'add_settings_link')
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Marqo Search Integration',
            'Marqo Search',
            'manage_options',
            'marqo-fibo-integration',
            array($this, 'display_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'marqo_fibo_settings',
            'marqo_fibo_settings',
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'marqo_fibo_general_section',
            'General Settings',
            array($this, 'general_section_callback'),
            'marqo_fibo_settings'
        );
        
        add_settings_field(
            'enabled',
            'Enable Integration',
            array($this, 'enabled_callback'),
            'marqo_fibo_settings',
            'marqo_fibo_general_section'
        );
        
        add_settings_field(
            'marqo_api_key',
            'Marqo API Key',
            array($this, 'api_key_callback'),
            'marqo_fibo_settings',
            'marqo_fibo_general_section'
        );
        
        add_settings_field(
            'marqo_endpoint',
            'Marqo Endpoint',
            array($this, 'endpoint_callback'),
            'marqo_fibo_settings',
            'marqo_fibo_general_section'
        );
        
        add_settings_field(
            'marqo_index',
            'Marqo Index',
            array($this, 'index_callback'),
            'marqo_fibo_settings',
            'marqo_fibo_general_section'
        );
        
        add_settings_field(
            'result_limit',
            'Results Limit',
            array($this, 'result_limit_callback'),
            'marqo_fibo_settings',
            'marqo_fibo_general_section'
        );
        
        add_settings_field(
            'cache_time',
            'Cache Time (seconds)',
            array($this, 'cache_time_callback'),
            'marqo_fibo_settings',
            'marqo_fibo_general_section'
        );
    }
    
    /**
     * Sanitize settings
     * 
     * @param array $input Input settings
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? (bool) $input['enabled'] : false;
        $sanitized['marqo_api_key'] = isset($input['marqo_api_key']) ? sanitize_text_field($input['marqo_api_key']) : '';
        $sanitized['marqo_endpoint'] = isset($input['marqo_endpoint']) ? esc_url_raw($input['marqo_endpoint']) : '';
        $sanitized['marqo_index'] = isset($input['marqo_index']) ? sanitize_text_field($input['marqo_index']) : '';
        $sanitized['result_limit'] = isset($input['result_limit']) ? absint($input['result_limit']) : 10;
        $sanitized['cache_time'] = isset($input['cache_time']) ? absint($input['cache_time']) : 3600;
        
        return $sanitized;
    }
    
    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>Configure the integration between Marqo Search and FiboSearch.</p>';
    }
    
    /**
     * Enabled field callback
     */
    public function enabled_callback() {
        $enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : false;
        ?>
        <input type="checkbox" name="marqo_fibo_settings[enabled]" value="1" <?php checked($enabled, true); ?> />
        <p class="description">Enable or disable the integration</p>
        <?php
    }
    
    /**
     * API key field callback
     */
    public function api_key_callback() {
        $api_key = isset($this->settings['marqo_api_key']) ? $this->settings['marqo_api_key'] : '';
        ?>
        <input type="password" name="marqo_fibo_settings[marqo_api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
        <p class="description">Your Marqo API key</p>
        <?php
    }
    
    /**
     * Endpoint field callback
     */
    public function endpoint_callback() {
        $endpoint = isset($this->settings['marqo_endpoint']) ? $this->settings['marqo_endpoint'] : '';
        ?>
        <input type="url" name="marqo_fibo_settings[marqo_endpoint]" value="<?php echo esc_attr($endpoint); ?>" class="regular-text" placeholder="https://api.marqo.ai" />
        <p class="description">Marqo API endpoint URL</p>
        <?php
    }
    
    /**
     * Index field callback
     */
    public function index_callback() {
        $index = isset($this->settings['marqo_index']) ? $this->settings['marqo_index'] : '';
        ?>
        <input type="text" name="marqo_fibo_settings[marqo_index]" value="<?php echo esc_attr($index); ?>" class="regular-text" />
        <p class="description">Marqo index name to search in</p>
        <?php
    }
    
    /**
     * Result limit field callback
     */
    public function result_limit_callback() {
        $limit = isset($this->settings['result_limit']) ? $this->settings['result_limit'] : 10;
        ?>
        <input type="number" name="marqo_fibo_settings[result_limit]" value="<?php echo esc_attr($limit); ?>" class="small-text" min="1" max="100" />
        <p class="description">Maximum number of results to fetch from Marqo</p>
        <?php
    }
    
    /**
     * Cache time field callback
     */
    public function cache_time_callback() {
        $cache_time = isset($this->settings['cache_time']) ? $this->settings['cache_time'] : 3600;
        ?>
        <input type="number" name="marqo_fibo_settings[cache_time]" value="<?php echo esc_attr($cache_time); ?>" class="small-text" min="0" />
        <p class="description">Time in seconds to cache search results (0 to disable caching)</p>
        <?php
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Test connection if requested
        $connection_status = '';
        if (isset($_POST['test_connection']) && check_admin_referer('marqo_fibo_test_connection')) {
            $connection_status = $this->test_connection();
        }
        
        // Clear cache if requested
        if (isset($_POST['clear_cache']) && check_admin_referer('marqo_fibo_clear_cache')) {
            $this->clear_cache();
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if ($connection_status): ?>
                <div class="notice <?php echo $connection_status['success'] ? 'notice-success' : 'notice-error'; ?> is-dismissible">
                    <p><?php echo esc_html($connection_status['message']); ?></p>
                </div>
            <?php endif; ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('marqo_fibo_settings');
                do_settings_sections('marqo_fibo_settings');
                submit_button('Save Settings');
                ?>
            </form>
            
            <div class="card">
                <h2>Connection Test</h2>
                <p>Test your connection to the Marqo API.</p>
                <form method="post">
                    <?php wp_nonce_field('marqo_fibo_test_connection'); ?>
                    <input type="submit" name="test_connection" class="button button-secondary" value="Test Connection" />
                </form>
            </div>
            
            <div class="card">
                <h2>Cache Management</h2>
                <p>Clear the search results cache.</p>
                <form method="post">
                    <?php wp_nonce_field('marqo_fibo_clear_cache'); ?>
                    <input type="submit" name="clear_cache" class="button button-secondary" value="Clear Cache" />
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add settings link to plugins page
     * 
     * @param array $links Existing links
     * @return array Modified links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=marqo-fibo-integration">' . __('Settings', 'marqo-fibo-integration') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Test connection to Marqo API
     * 
     * @return array Connection status
     */
    private function test_connection() {
        // Load Marqo connector
        if (!class_exists('Marqo_Connector')) {
            require_once MARQO_FIBO_PLUGIN_DIR . 'includes/class-marqo-connector.php';
        }
        
        // Create connector instance
        $connector = new Marqo_Connector(
            $this->settings['marqo_api_key'],
            $this->settings['marqo_endpoint'],
            $this->settings['marqo_index']
        );
        
        // Test connection
        $result = $connector->test_connection();
        
        if ($result === true) {
            return array(
                'success' => true,
                'message' => 'Connection successful! Your Marqo API is properly configured.'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $result
            );
        }
    }
    
    /**
     * Clear search results cache
     */
    private function clear_cache() {
        // Load cache manager
        if (!class_exists('Cache_Manager')) {
            require_once MARQO_FIBO_PLUGIN_DIR . 'includes/class-cache-manager.php';
        }
        
        // Create cache manager instance
        $cache_manager = new Cache_Manager($this->settings['cache_time']);
        
        // Flush cache
        $result = $cache_manager->flush();
        
        if ($result) {
            add_settings_error(
                'marqo_fibo_settings',
                'cache_cleared',
                'Cache cleared successfully.',
                'updated'
            );
        } else {
            add_settings_error(
                'marqo_fibo_settings',
                'cache_clear_failed',
                'Failed to clear cache.',
                'error'
            );
        }
    }
}
