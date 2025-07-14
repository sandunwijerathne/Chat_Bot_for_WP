<?php
function render_chatgpt_in_footer() {
    wp_enqueue_style('chatgpt-style', CHATGPT_PLUGIN_URL . 'assets/chatgpt.css');
    wp_enqueue_script('chatgpt-script', CHATGPT_PLUGIN_URL . 'assets/chatgpt.js', ['jquery'], null, true);

    wp_localize_script('chatgpt-script', 'chatgptAjax', [
        'ajaxUrl' => site_url('/wp-json/custom-chatgpt/v1/ask'),
        'botName' => get_option('chatgpt_bot_name', 'AI Agent'),
    ]);

    $bot_logo = get_option('chatgpt_bot_logo');
    $bot_name = get_option('chatgpt_bot_name', 'Bot');
    ?>

    <!-- Chatbot Toggle Icon -->
    <div id="chatgpt-box-icon">
        <img src="<?php echo esc_url($bot_logo ?: plugins_url('robot.png', __FILE__)); ?>" alt="ChatBot Icon">
    </div>

    <!-- Full Chatbot UI -->
    <div id="chatgpt-box" style="display: none;">
        

        <?php if ($bot_name): ?>
            <div id="chatgpt-name"><?php echo esc_html($bot_name); ?></div>
		<hr>
        <?php endif; ?>

		<div id="chatgpt-suggestions">
    <div class="chatgpt-suggestion-buttons">
        <p class="chatgpt-suggest">Try asking: ‘What are the best tours in [destination]?’ or ‘Can you plan a 3-day itinerary for [location]?’</p>
        
    </div>
</div>
		
		
        <div id="chatgpt-history"></div>
		<div id="loading-msg"><?php echo esc_html($bot_name); ?> is typing...</div>
			<div id="chatgpt-input-wrapper">
				
        		<input type="text" id="chatgpt-input" placeholder="Ask your question...">
				<button id="chatgpt-send">Send</button>
			</div>
    </div>


    <?php
}
add_action('wp_footer', 'render_chatgpt_in_footer');
