<?php
/**
 * Class for intercepting FiboSearch results and injecting Marqo results
 */
class Search_Interceptor {
    
    /**
     * Marqo connector instance
     */
    private $marqo_connector;
    
    /**
     * Cache manager instance
     */
    private $cache_manager;
    
    /**
     * Results limit
     */
    private $result_limit;
    
    /**
     * Constructor
     * 
     * @param Marqo_Connector $marqo_connector Marqo connector instance
     * @param Cache_Manager $cache_manager Cache manager instance
     * @param int $result_limit Results limit
     */
    public function __construct($marqo_connector, $cache_manager, $result_limit = 10) {
        $this->marqo_connector = $marqo_connector;
        $this->cache_manager = $cache_manager;
        $this->result_limit = $result_limit;
    }
    
    /**
     * Initialize hooks and filters
     */
    public function init() {
        // Hook into FiboSearch results
        add_filter('dgwt/wcas/search_results', array($this, 'inject_marqo_results'), 10, 2);
        
        // Add JavaScript to handle frontend integration
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add AJAX handler for direct Marqo searches
        add_action('wp_ajax_marqo_fibo_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_marqo_fibo_search', array($this, 'ajax_search'));
    }
    
    /**
     * Inject Marqo results into FiboSearch results
     * 
     * @param array $results Original FiboSearch results
     * @param string $phrase Search phrase
     * @return array Modified results
     */
    public function inject_marqo_results($results, $phrase) {
        // Skip if no phrase
        if (empty($phrase)) {
            return $results;
        }
        
        // Try to get cached results
        $cache_key = 'search_' . md5($phrase);
        $cached_results = $this->cache_manager->get($cache_key);
        
        if ($cached_results !== false) {
            return $this->merge_results($results, $cached_results, $phrase);
        }
        
        // Get results from Marqo
        $marqo_response = $this->marqo_connector->search($phrase, $this->result_limit);
        
        // If search failed, return original results
        if (!$marqo_response['success']) {
            error_log('Marqo search failed: ' . $marqo_response['message']);
            return $results;
        }
        
        // Process Marqo results
        $marqo_results = $this->process_marqo_results($marqo_response['results']);
        
        // Cache the processed results
        $this->cache_manager->set($cache_key, $marqo_results);
        
        // Merge and return results
        return $this->merge_results($results, $marqo_results, $phrase);
    }
    
    /**
     * Process raw Marqo results into a format compatible with FiboSearch
     * 
     * @param array $raw_results Raw results from Marqo
     * @return array Processed results
     */
    private function process_marqo_results($raw_results) {
        $processed_results = array();
        
        foreach ($raw_results as $result) {
            // Skip if no document
            if (!isset($result['_id']) || !isset($result['_source'])) {
                continue;
            }
            
            $source = $result['_source'];
            
            // Try to map Marqo fields to FiboSearch fields
            $processed_result = array(
                'id' => $result['_id'],
                'score' => isset($result['_score']) ? $result['_score'] : 0,
                'title' => isset($source['title']) ? $source['title'] : '',
                'description' => isset($source['description']) ? $source['description'] : '',
                'url' => isset($source['url']) ? $source['url'] : '',
                'image' => isset($source['image']) ? $source['image'] : '',
                'price' => isset($source['price']) ? $source['price'] : '',
                'sku' => isset($source['sku']) ? $source['sku'] : '',
                'source' => 'marqo', // Mark as coming from Marqo
                'raw' => $source // Keep raw data for custom processing
            );
            
            $processed_results[] = $processed_result;
        }
        
        return $processed_results;
    }
    
    /**
     * Merge FiboSearch results with Marqo results
     * 
     * @param array $fibo_results FiboSearch results
     * @param array $marqo_results Processed Marqo results
     * @param string $phrase Search phrase
     * @return array Merged results
     */
    private function merge_results($fibo_results, $marqo_results, $phrase) {
        // If no Marqo results, return original results
        if (empty($marqo_results)) {
            return $fibo_results;
        }
        
        // Get products section from FiboSearch results
        $products = isset($fibo_results['products']) ? $fibo_results['products'] : array();
        
        // Convert Marqo results to FiboSearch product format
        $marqo_products = array();
        foreach ($marqo_results as $result) {
            // Skip if already in FiboSearch results (by ID)
            $exists = false;
            foreach ($products as $product) {
                if (isset($product['ID']) && $product['ID'] == $result['id']) {
                    $exists = true;
                    break;
                }
            }
            
            if ($exists) {
                continue;
            }
            
            // Create product entry in FiboSearch format
            $product = array(
                'ID' => $result['id'],
                'post_title' => $result['title'],
                'post_content' => $result['description'],
                'price' => $result['price'],
                'sku' => $result['sku'],
                'thumbnail' => $result['image'],
                'url' => $result['url'],
                'marqo_data' => $result['raw'], // Add raw data for custom processing
                'marqo_source' => true // Mark as coming from Marqo
            );
            
            $marqo_products[] = $product;
        }
        
        // Merge products
        $merged_products = array_merge($products, $marqo_products);
        
        // Update products count
        $fibo_results['products'] = $merged_products;
        $fibo_results['total'] = count($merged_products);
        
        // Add Marqo info to results
        $fibo_results['marqo_enhanced'] = true;
        
        return $fibo_results;
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'marqo-fibo-integration',
            MARQO_FIBO_PLUGIN_URL . 'assets/js/marqo-fibo-integration.js',
            array('jquery'),
            MARQO_FIBO_VERSION,
            true
        );
        
        wp_localize_script('marqo-fibo-integration', 'MarqoFiboData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('marqo_fibo_search')
        ));
    }
    
    /**
     * AJAX handler for direct Marqo searches
     */
    public function ajax_search() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'marqo_fibo_search')) {
            wp_send_json_error('Invalid security token');
        }
        
        // Get search phrase
        $phrase = isset($_POST['phrase']) ? sanitize_text_field($_POST['phrase']) : '';
        
        if (empty($phrase)) {
            wp_send_json_error('Empty search phrase');
        }
        
        // Try to get cached results
        $cache_key = 'ajax_search_' . md5($phrase);
        $cached_results = $this->cache_manager->get($cache_key);
        
        if ($cached_results !== false) {
            wp_send_json_success($cached_results);
        }
        
        // Get results from Marqo
        $marqo_response = $this->marqo_connector->search($phrase, $this->result_limit);
        
        // If search failed, return error
        if (!$marqo_response['success']) {
            wp_send_json_error($marqo_response['message']);
        }
        
        // Process Marqo results
        $marqo_results = $this->process_marqo_results($marqo_response['results']);
        
        // Cache the processed results
        $this->cache_manager->set($cache_key, $marqo_results);
        
        // Return results
        wp_send_json_success($marqo_results);
    }
}
