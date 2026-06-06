<?php
defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('custom-chatgpt/v1', '/ask', [
        'methods'             => 'POST',
        'callback'            => 'custom_chatgpt_handler',
        'permission_callback' => 'custom_chatgpt_verify_nonce',
    ]);
});

function custom_chatgpt_verify_nonce($request) {
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_forbidden', 'Invalid or missing nonce.', ['status' => 403]);
    }
    return true;
}

function custom_chatgpt_handler($request) {
    // Rate limiting: 20 requests per IP per hour
    $ip       = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $rate_key = 'chatgpt_rate_' . md5($ip);
    $count    = (int) get_transient($rate_key);
    if ($count >= 20) {
        return new WP_Error('rate_limited', 'Too many requests. Please try again later.', ['status' => 429]);
    }
    set_transient($rate_key, $count + 1, HOUR_IN_SECONDS);

    $body = $request->get_json_params();
    if (empty($body['message'])) {
        return new WP_Error('missing_message', 'Message is required.', ['status' => 400]);
    }

    $message = sanitize_text_field($body['message']);
    if (strlen($message) > 500) {
        return new WP_Error('message_too_long', 'Message must be under 500 characters.', ['status' => 400]);
    }

    $api_key = get_option('chatgpt_api_key');
    if (!$api_key) {
        return new WP_Error('missing_key', 'API key is not configured.', ['status' => 500]);
    }

    $system_msg  = get_option('chatgpt_system_prompt', 'You are a helpful assistant. Use the following site content to answer questions. When mentioning pages or resources, include them as HTML links using <a href="...">Link Text</a>. Use valid HTML for links, lists, and formatting. Do not use Markdown or plain URLs.');
    $site_url    = site_url();
    $content     = custom_chatgpt_get_site_content();
    $full_system = $system_msg . "\n\nSite URL: " . $site_url . "\n\nSite Content:\n" . $content;

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => json_encode([
            'model'    => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $full_system],
                ['role' => 'user',   'content' => $message],
            ],
        ]),
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        error_log('ChatGPT Bot: OpenAI connection error - ' . $response->get_error_message());
        return new WP_Error('api_error', 'Failed to connect to OpenAI.', ['status' => 502]);
    }

    $decoded = json_decode(wp_remote_retrieve_body($response), true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['choices'][0]['message']['content'])) {
        error_log('ChatGPT Bot: Unexpected API response - ' . wp_remote_retrieve_body($response));
        return new WP_Error('api_response_error', 'Unexpected response from OpenAI.', ['status' => 502]);
    }

    $reply = $decoded['choices'][0]['message']['content'];

    // Allow only safe HTML tags in the response
    $allowed_tags = [
        'a'      => ['href' => [], 'target' => [], 'rel' => []],
        'b'      => [],
        'strong' => [],
        'i'      => [],
        'em'     => [],
        'ul'     => [],
        'ol'     => [],
        'li'     => [],
        'p'      => [],
        'br'     => [],
    ];

    return wp_kses($reply, $allowed_tags);
}

function custom_chatgpt_get_site_content() {
    $cache_key = 'chatgpt_site_content_v1';
    $cached    = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $posts = get_posts([
        'post_type'      => ['post', 'page'],
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    $content = '';
    foreach ($posts as $post) {
        $title   = strtoupper($post->post_title);
        $text    = wp_strip_all_tags(apply_filters('the_content', $post->post_content));
        $content .= $title . ":\n" . $text . "\n\n";
    }

    set_transient($cache_key, $content, HOUR_IN_SECONDS);

    return $content;
}

// Invalidate content cache whenever a post or page is saved
add_action('save_post', function () {
    delete_transient('chatgpt_site_content_v1');
});
