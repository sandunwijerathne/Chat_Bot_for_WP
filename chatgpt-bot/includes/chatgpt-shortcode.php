<?php
defined('ABSPATH') || exit;

function render_chatgpt_in_footer() {
    $bot_logo = get_option('chatgpt_bot_logo');
    $bot_name = get_option('chatgpt_bot_name', 'Bot');
    ?>

    <div id="chatgpt-box-icon">
        <img src="<?php echo esc_url($bot_logo ?: plugins_url('robot.png', __FILE__)); ?>" alt="ChatBot Icon">
    </div>

    <div id="chatgpt-box">
        <?php if ($bot_name): ?>
            <div id="chatgpt-name"><?php echo esc_html($bot_name); ?></div>
            <hr>
        <?php endif; ?>

        <div id="chatgpt-suggestions">
            <div class="chatgpt-suggestion-buttons">
                <p class="chatgpt-suggest">Try asking about our services, pricing, or hours.</p>
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
