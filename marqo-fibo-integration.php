<?php
/**
 * Plugin Name: Marqo Search for Fibo Search
 * Description: Plugin that injects Marqo search results into Fibo Search
 * Version: 1.0.0
 * Author: diogenes.moreira
 * Text Domain: marqo-fibo-integration
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define constants
define('MARQO_FIBO_VERSION', '1.0.0');
define('MARQO_FIBO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MARQO_FIBO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class Marqo_Fibo_Integration {

    /**
     * Singleton instance of the class
     */
    private static $instance = null;

    /**
     * Plugin settings
     */
    private $settings;

    /**
     * Constructor
     */
    private function __construct() {
        $this->settings = get_option('marqo_fibo_settings', array(
            'marqo_api_key' => '',
            'marqo_endpoint' => '',
            'marqo_index' => '',
            'result_limit' => 10,
            'cache_time' => 3600,
            'enabled' => false
        ));

        // Initialize the plugin
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if Fibo Search is active
        if (!$this->is_fibo_search_active()) {
            add_action('admin_notices', array($this, 'fibo_search_missing_notice'));
            return;
        }

        // Load dependencies
        $this->load_dependencies();

        // Register admin hooks
        if (is_admin()) {
            $this->init_admin();
        }

        // Register public hooks
        $this->init_public();
    }

    /**
     * Check if Fibo Search is active
     */
    private function is_fibo_search_active() {
        return class_exists('DGWT_WC_Ajax_Search') || class_exists('DGWT_WCAS');
    }

    /**
     * Display notice if Fibo Search is not active
     */
    public function fibo_search_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Marqo Search for Fibo Search requires the FiboSearch plugin to be installed and activated.', 'marqo-fibo-integration'); ?></p>
        </div>
        <?php
    }

    /**
     * Load dependencies
     */
    private function load_dependencies() {
        // Include classes
        require_once MARQO_FIBO_PLUGIN_DIR . 'includes/class-marqo-connector.php';
        require_once MARQO_FIBO_PLUGIN_DIR . 'includes/class-search-interceptor.php';
        require_once MARQO_FIBO_PLUGIN_DIR . 'includes/class-cache-manager.php';
        
        if (is_admin()) {
            require_once MARQO_FIBO_PLUGIN_DIR . 'admin/class-admin.php';
        }
    }

    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        $admin = new Marqo_Fibo_Admin($this->settings);
        $admin->init();
    }

    /**
     * Initialize public functionality
     */
    private function init_public() {
        // Only proceed if the plugin is enabled
        if (!$this->settings['enabled']) {
            return;
        }

        // Initialize Marqo connector
        $marqo_connector = new Marqo_Connector(
            $this->settings['marqo_api_key'],
            $this->settings['marqo_endpoint'],
            $this->settings['marqo_index']
        );

        // Initialize cache manager
        $cache_manager = new Cache_Manager($this->settings['cache_time']);

        // Initialize search interceptor
        $search_interceptor = new Search_Interceptor(
            $marqo_connector,
            $cache_manager,
            $this->settings['result_limit']
        );
        $search_interceptor->init();
    }
}

// Initialize the plugin
function marqo_fibo_integration_init() {
    Marqo_Fibo_Integration::get_instance();
}
add_action('init', 'marqo_fibo_integration_init');

// Plugin activation
register_activation_hook(__FILE__, 'marqo_fibo_integration_activate');
function marqo_fibo_integration_activate() {
    // Create necessary directories
    if (!file_exists(MARQO_FIBO_PLUGIN_DIR . 'includes')) {
        mkdir(MARQO_FIBO_PLUGIN_DIR . 'includes');
    }
    if (!file_exists(MARQO_FIBO_PLUGIN_DIR . 'admin')) {
        mkdir(MARQO_FIBO_PLUGIN_DIR . 'admin');
    }
}

// Plugin deactivation
register_deactivation_hook(__FILE__, 'marqo_fibo_integration_deactivate');
function marqo_fibo_integration_deactivate() {
    // Clean up options if needed
}
