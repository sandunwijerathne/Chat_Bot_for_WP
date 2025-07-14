<?php
add_action('rest_api_init', function () {
    register_rest_route('custom-chatgpt/v1', '/ask', [
        'methods' => 'POST',
        'callback' => 'custom_chatgpt_handler',
        'permission_callback' => '__return_true'
    ]);
});

function custom_chatgpt_handler($request) {
    $body = $request->get_json_params();
    $message = sanitize_text_field($body['message']);


    $siteurl = site_url();
    $pages = get_all_formatted_slugs();
    $content = "";
    $bot_name   = get_option('chatgpt_bot_name', 'TPT Bot');
    $system_msg = get_option('chatgpt_system_prompt', 'You are a helpful assistant for TPT Tours...');


    foreach ($pages as $slug) {
        $page = get_page_by_path($slug);
        if ($page) {
            $content .= strtoupper(str_replace(['-', '/'], ' ', $slug)) . ":\n";
            $content .= wp_strip_all_tags(apply_filters('the_content', $page->post_content)) . "\n\n";
        }
    }

    $api_key = get_option('chatgpt_api_key');
    if (!$api_key) return new WP_Error('missing_key', 'API key is not set', ['status' => 500]);

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [

                ['role' => 'system','content' => $system_msg . "\n\n" .$content],

                ['role' => 'system', 'content' => "You are a helpful assistant for TPT Tours in Sri Lanka the site".$siteurl.". Use the following content to answer questions. When you mention any webpage, tour, or resource, always include it as an HTML link using <a href=\"https://...\">Link Text</a>. Do not provide plain URLs or Markdown. Only respond using valid HTML for links, lists, and text formatting.\n\n.$content"],

                ['role' => 'user', 'content' => $message],
            ],
        ]),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) return new WP_Error('api_error', 'Failed to connect to OpenAI', ['status' => 500]);

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['choices'][0]['message']['content'] ?? 'No response from ChatGPT';
}
function get_all_formatted_slugs() {
    $formatted_slugs = array();
    
    // Get all published posts and pages
    $args = array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $formatted_slugs[] = "'" . get_post_field('post_name') . "'";
        }
    }
    
    wp_reset_postdata();
    
    // Format the output
    $output = '[' . implode(', ', $formatted_slugs) . ']';
    return $output;
}
