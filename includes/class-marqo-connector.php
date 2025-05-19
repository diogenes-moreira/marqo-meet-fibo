<?php
/**
 * Class for connecting with the Marqo API
 */
class Marqo_Connector {
    
    /**
     * Marqo API key
     */
    private $api_key;
    
    /**
     * Marqo API endpoint
     */
    private $endpoint;
    
    /**
     * Marqo index to use
     */
    private $index;
    
    /**
     * Constructor
     */
    public function __construct($api_key, $endpoint, $index) {
        $this->api_key = $api_key;
        $this->endpoint = rtrim($endpoint, '/');
        $this->index = $index;
    }
    
    /**
     * Perform search in Marqo
     * 
     * @param string $query Search query
     * @param int $limit Results limit
     * @param array $filters Additional filters
     * @return array Search results
     */
    public function search($query, $limit = 10, $filters = array()) {
        // Verify we have the necessary data
        if (empty($this->api_key) || empty($this->endpoint) || empty($this->index)) {
            return array(
                'success' => false,
                'message' => 'Incomplete Marqo configuration',
                'results' => array()
            );
        }
        
        // Build API URL
        $url = $this->endpoint . '/indexes/' . $this->index . '/search';
        
        // Prepare data for the request
        $data = array(
            'q' => $query,
            'limit' => $limit
        );
        
        // Add filters if they exist
        if (!empty($filters)) {
            $data['filter'] = $filters;
        }
        
        // Make API request
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key
            ),
            'body' => json_encode($data),
            'timeout' => 15
        ));
        
        // Check if there was an error in the request
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
                'results' => array()
            );
        }
        
        // Get response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Verify if the response is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => 'Error decoding Marqo response',
                'results' => array()
            );
        }
        
        // Process and return results
        $results = isset($data['hits']) ? $data['hits'] : array();
        
        return array(
            'success' => true,
            'message' => 'Search successful',
            'results' => $results
        );
    }
    
    /**
     * Test connection with Marqo
     * 
     * @return bool|string True if connection is successful, error message otherwise
     */
    public function test_connection() {
        // Verify we have the necessary data
        if (empty($this->api_key) || empty($this->endpoint)) {
            return 'Incomplete Marqo configuration';
        }
        
        // Try to make a simple request
        $url = $this->endpoint . '/indexes';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key
            ),
            'timeout' => 15
        ));
        
        // Check if there was an error in the request
        if (is_wp_error($response)) {
            return $response->get_error_message();
        }
        
        // Check response code
        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return 'Connection error. Code: ' . $code;
        }
        
        return true;
    }
}
