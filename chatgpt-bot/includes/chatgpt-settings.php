<?php
// Admin settings menu
add_action('admin_menu', function () {
    add_options_page('ChatGPT Bot Settings', 'ChatGPT Bot', 'manage_options', 'chatgpt-bot', 'chatgpt_bot_settings_page');
});

// Register settings
add_action('admin_init', function () {
    register_setting('chatgpt_bot_options', 'chatgpt_api_key');
    register_setting('chatgpt_bot_options', 'chatgpt_bot_logo');
    register_setting('chatgpt_bot_options', 'chatgpt_bot_name');
    register_setting('chatgpt_bot_options', 'chatgpt_system_prompt');

});

// Admin page content
function chatgpt_bot_settings_page() {
    $logo_url = get_option('chatgpt_bot_logo');
    ?>
    <div class="wrap">
        <h1>ChatGPT Bot Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('chatgpt_bot_options');
            do_settings_sections('chatgpt_bot_options');
            ?>
            <table class="form-table">
                <!-- API Key Field -->
                <tr valign="top">
                    <th scope="row">OpenAI API Key</th>
                    <td>
                        <input type="text" name="chatgpt_api_key" value="<?php echo esc_attr(get_option('chatgpt_api_key')); ?>" style="width: 400px;" />
                        <p class="description">Paste your OpenAI API key here (e.g., sk-xxxx...).</p>
                    </td>
                </tr>

                <!-- Logo Field -->
                <tr valign="top">
                    <th scope="row">Chatbot Icon URL</th>
                    <td>
                        <?php if ($logo_url): ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="Chatbot Logo Preview" style="max-height: 50px; display:block; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="text" name="chatgpt_bot_logo" id="chatgpt_bot_logo" value="<?php echo esc_url($logo_url); ?>" style="width: 400px;" />
                        <br>
                        <button type="button" class="button" id="upload-logo-button">Upload Image</button>
                        <p class="description">Upload 64x64 size image or Paste a direct image URL or upload via Media Library.</p>
                    </td>
                </tr>
                <tr valign="top">
    <th scope="row">Chatbot Display Name</th>
    <td>
        <input type="text" name="chatgpt_bot_name" value="<?php echo esc_attr(get_option('chatgpt_bot_name', 'TPT Bot')); ?>" style="width: 400px;" />
        <p class="description">This name appears at the top of the chat box (e.g., TPT Bot, Travel Assistant, etc.).</p>
    </td>
</tr>
<tr valign="top">
    <th scope="row">System Instruction</th>
    <td>
        <textarea name="chatgpt_system_prompt" rows="6" style="width: 100%;"><?php echo esc_textarea(get_option('chatgpt_system_prompt', 'You are a helpful assistant for TPT Tours. Use the following content to answer questions. When you mention any webpage, tour, or resource, always include it as an HTML link using <a href=\"https://...\">Link Text</a>. Do not provide plain URLs or Markdown. Only respond using valid HTML for links, lists, and text formatting.')); ?></textarea>
        <p class="description">This message is sent to ChatGPT as the system instruction. You can customize tone, behavior, etc.</p>
    </td>
</tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <!-- Instructions -->
        <hr>
        <h2>Instructions</h2>

<p>This plugin allows you to add an AI-powered chatbot to your website using the OpenAI API. The chatbot automatically reads your page content and responds to visitor questions in real-time.</p>

<hr>

<h3>🔧 Setup Guide</h3>
<ol style="padding-left: 20px;">
    <li><strong>Enter Your OpenAI API Key:</strong><br>
        Go to the plugin settings and paste your API key from OpenAI. This is required to activate the chatbot functionality.</li>
    <li><strong>Customize the Appearance (Optional):</strong><br>
        You can upload a logo or paste an image URL to display above the chatbot's input field. This helps personalize the bot for your brand.</li>
</ol>

<hr>

<h3>💬 How the Chatbot Works</h3>
<ul style="list-style: disc; padding-left: 20px;">
    <li>The chatbot appears as a floating chat bubble on the bottom-left corner of every page.</li>
    <li>When clicked, it opens a chat window where users can type questions.</li>
    <li>The bot reads and analyzes the text content of each page to provide relevant, intelligent answers to user queries.</li>
    <li>No extra configuration is required – it auto-scans the visible text content on each page dynamically.</li>
</ul>

<hr>

<h3>✨ Features Overview</h3>
<ul style="list-style: disc; padding-left: 20px;">
    <li><strong>Easy Integration:</strong> Add the chatbot to any page or post using the shortcode <code>[chatgpt_bot]</code>.</li>
    <li><strong>Model Used:</strong> This plugin uses <code>gpt-3.5-turbo</code>, which is fast, affordable, and optimized for chat-based interactions using OpenAI's API.</li>
    <li><strong>OpenAI API Key:</strong> Simple admin panel setting to insert your key.</li>
    <li><strong>Content-Aware AI:</strong> Uses your website’s actual content to guide its answers.</li>
    <li><strong>Floating Chat Widget:</strong> Always accessible in the bottom-left corner of every page.</li>
    <li><strong>Custom Branding:</strong> Upload a logo or image above the input box.</li>
    <li><strong>Lightweight & Modular:</strong> Cleanly separated JavaScript and CSS files for better performance and maintainability.</li>
</ul>

<hr>

<h3>📌 Notes</h3>
<ul style="list-style: disc; padding-left: 20px;">
    <li>Make sure your OpenAI API usage limits support the expected traffic.</li>
    <li>The chatbot only analyzes text-based content (e.g., headings, paragraphs). It doesn’t parse dynamic JS-rendered content or external iframe text.</li>
    <li>For better responses, structure your content clearly with headings and proper formatting.</li>
</ul>
    </div>

    <!-- Media uploader JS -->
    <script>
        jQuery(document).ready(function($){
            $('#upload-logo-button').on('click', function(e){
                e.preventDefault();
                var image = wp.media({ 
                    title: 'Upload Logo',
                    multiple: false
                }).open()
                .on('select', function(){
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    $('#chatgpt_bot_logo').val(image_url);
                });
            });
        });
    </script>
    <?php
}
