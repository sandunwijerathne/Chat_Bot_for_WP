<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_options_page('ChatGPT Bot Settings', 'ChatGPT Bot', 'manage_options', 'chatgpt-bot', 'chatgpt_bot_settings_page');
});

add_action('admin_init', function () {
    register_setting('chatgpt_bot_options', 'chatgpt_api_key');
    register_setting('chatgpt_bot_options', 'chatgpt_bot_logo');
    register_setting('chatgpt_bot_options', 'chatgpt_bot_name');
    register_setting('chatgpt_bot_options', 'chatgpt_system_prompt');
});

function chatgpt_bot_settings_page() {
    wp_enqueue_media();
    $logo_url       = get_option('chatgpt_bot_logo');
    $default_prompt = 'You are a helpful assistant. Use the following site content to answer questions. When mentioning pages or resources, include them as HTML links using <a href="...">Link Text</a>. Use valid HTML for links, lists, and formatting. Do not use Markdown or plain URLs.';
    ?>
    <div class="wrap">
        <h1>ChatGPT Bot Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('chatgpt_bot_options');
            do_settings_sections('chatgpt_bot_options');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">OpenAI API Key</th>
                    <td>
                        <input type="password" name="chatgpt_api_key" value="<?php echo esc_attr(get_option('chatgpt_api_key')); ?>" style="width: 400px;" autocomplete="off" />
                        <p class="description">Paste your OpenAI API key here (e.g., sk-xxxx...).</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Chatbot Icon URL</th>
                    <td>
                        <?php if ($logo_url): ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="Chatbot Logo Preview" style="max-height: 50px; display:block; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="text" name="chatgpt_bot_logo" id="chatgpt_bot_logo" value="<?php echo esc_url($logo_url); ?>" style="width: 400px;" />
                        <br>
                        <button type="button" class="button" id="upload-logo-button">Upload Image</button>
                        <p class="description">Upload a 64x64 image or paste a direct image URL.</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Chatbot Display Name</th>
                    <td>
                        <input type="text" name="chatgpt_bot_name" value="<?php echo esc_attr(get_option('chatgpt_bot_name', 'AI Assistant')); ?>" style="width: 400px;" />
                        <p class="description">This name appears at the top of the chat box.</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">System Instruction</th>
                    <td>
                        <textarea name="chatgpt_system_prompt" rows="6" style="width: 100%;"><?php echo esc_textarea(get_option('chatgpt_system_prompt', $default_prompt)); ?></textarea>
                        <p class="description">Customize the chatbot's tone and behavior. Your site's published content is automatically appended.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr>
        <h2>Instructions</h2>
        <p>This plugin adds an AI-powered chatbot to your website using the OpenAI API. It automatically reads your published pages and posts to answer visitor questions.</p>

        <hr>
        <h3>Setup Guide</h3>
        <ol style="padding-left: 20px;">
            <li><strong>Enter Your OpenAI API Key:</strong> Paste your key from OpenAI. This is required to activate the chatbot.</li>
            <li><strong>Customize Appearance (Optional):</strong> Upload a logo or paste an image URL to use as the chat icon.</li>
            <li><strong>Set a System Instruction (Optional):</strong> Guide the chatbot's tone, persona, and behavior.</li>
        </ol>

        <hr>
        <h3>How It Works</h3>
        <ul style="list-style: disc; padding-left: 20px;">
            <li>The chatbot appears as a floating bubble on every page.</li>
            <li>It reads all your published posts and pages to provide relevant answers.</li>
            <li>Site content is cached for one hour. It refreshes automatically when you publish or update content.</li>
            <li>Requests are rate-limited to 20 per IP per hour to protect against abuse.</li>
        </ul>

        <hr>
        <h3>Notes</h3>
        <ul style="list-style: disc; padding-left: 20px;">
            <li>Ensure your OpenAI API usage limits match your expected traffic.</li>
            <li>The chatbot only reads text-based content (headings, paragraphs). Dynamic JS-rendered content is not included.</li>
            <li>Each visitor message is capped at 500 characters.</li>
        </ul>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            $('#upload-logo-button').on('click', function (e) {
                e.preventDefault();
                var image = wp.media({
                    title: 'Upload Logo',
                    multiple: false
                }).open()
                .on('select', function () {
                    var uploaded_image = image.state().get('selection').first();
                    $('#chatgpt_bot_logo').val(uploaded_image.toJSON().url);
                });
            });
        });
    </script>
    <?php
}
