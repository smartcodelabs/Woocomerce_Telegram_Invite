jQuery(document).ready(function($) {
    $('#tig-check-status').on('click', function(e) {
        e.preventDefault();
        $('#tig-bot-status').html('Loading...');

        $.ajax({
            type: 'POST',
            url: tig_ajax_object.ajax_url,
            data: {
                action: 'tig_check_bot_status',
                security: tig_ajax_object.nonce
            },
            success: function(response) {
                if(response.success) {
                    const status = response.data;
                    $('#tig-bot-status').html(`
                        <p><strong>Bot Name:</strong> ${status.bot_name}</p>
                        <p><strong>Channel Name:</strong> ${status.channel_name}</p>
                        <p><strong>Channel ID:</strong> ${status.channel_id}</p>
                    `);
                } else {
                    $('#tig-bot-status').html('Error retrieving bot status.');
                }
            },
            error: function() {
                $('#tig-bot-status').html('Error retrieving bot status.');
            }
        });
    });
});
