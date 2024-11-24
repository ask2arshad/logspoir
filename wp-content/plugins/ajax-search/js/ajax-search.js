jQuery(document).ready(function ($) {
    $('#ajax-h2-search').on('input', function () {
        let query = $(this).val();
        if (query.length < 2) {
            $('#ajax-h2-results').html('');
            return;
        }

        $.ajax({
            url: ajax_search_params.ajax_url,
            method: 'POST',
            data: {
                action: 'h2_search',
                query: query
            },
            success: function (response) {
                $('#ajax-h2-results').html(response);
            },
            error: function () {
                $('#ajax-h2-results').html('<li>Error fetching results.</li>');
            }
        });
    });
});
