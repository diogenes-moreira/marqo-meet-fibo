<?php
/**
 * Class for caching search results
 */
class Cache_Manager {
    
    /**
     * Cache expiration time in seconds
     */
    private $cache_time;
    
    /**
     * Cache group name
     */
    private $cache_group = 'marqo_fibo_cache';
    
    /**
     * Constructor
     * 
     * @param int $cache_time Cache expiration time in seconds
     */
    public function __construct($cache_time = 3600) {
        $this->cache_time = $cache_time;
    }
    
    /**
     * Get cached item
     * 
     * @param string $key Cache key
     * @return mixed|false Cached data or false if not found
     */
    public function get($key) {
        $key = $this->prepare_key($key);
        return get_transient($key);
    }
    
    /**
     * Set cache item
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @return bool True on success, false on failure
     */
    public function set($key, $data) {
        $key = $this->prepare_key($key);
        return set_transient($key, $data, $this->cache_time);
    }
    
    /**
     * Delete cached item
     * 
     * @param string $key Cache key
     * @return bool True on success, false on failure
     */
    public function delete($key) {
        $key = $this->prepare_key($key);
        return delete_transient($key);
    }
    
    /**
     * Flush all cache items for this plugin
     * 
     * @return bool True on success, false on failure
     */
    public function flush() {
        global $wpdb;
        
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                '_transient_' . $this->cache_group . '_%'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Prepare cache key
     * 
     * @param string $key Original key
     * @return string Prepared key
     */
    private function prepare_key($key) {
        // Sanitize key and add prefix
        $key = sanitize_key($key);
        return $this->cache_group . '_' . $key;
    }
}
