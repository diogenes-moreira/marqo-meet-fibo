
/**
 * JavaScript for Marqo Fibo Integration
 * 
 * This file handles the frontend integration between Marqo and FiboSearch
 */

jQuery(document).ready(function($) {
    // Listen for FiboSearch events
    $(document).on('dgwt/wcas/search/started', function(event, data) {
        console.log('FiboSearch started', data);
    });
    
    $(document).on('dgwt/wcas/search/results', function(event, data) {
        console.log('FiboSearch results received', data);
        
        // Check if results are already enhanced by Marqo
        if (data.marqo_enhanced) {
            console.log('Results already enhanced by Marqo');
            return;
        }
        
        // Optionally perform additional Marqo search via AJAX
        // This is useful if you want to add more results or features beyond
        // what the server-side integration provides
        /*
        $.ajax({
            url: MarqoFiboData.ajax_url,
            type: 'POST',
            data: {
                action: 'marqo_fibo_search',
                nonce: MarqoFiboData.nonce,
                phrase: data.phrase
            },
            success: function(response) {
                if (response.success) {
                    console.log('Additional Marqo results', response.data);
                    // Process additional results here
                }
            }
        });
        */
    });
    
    // Add custom styling for Marqo results
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .dgwt-wcas-suggestion[data-marqo-source="true"] {
                background-color: rgba(0, 128, 0, 0.05);
            }
            .dgwt-wcas-suggestion[data-marqo-source="true"]:after {
                content: "Marqo";
                position: absolute;
                top: 5px;
                right: 5px;
                font-size: 9px;
                color: #999;
            }
        `)
        .appendTo('head');
});
