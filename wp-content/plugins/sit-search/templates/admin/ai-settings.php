<div class="wrap sit-search-admin">
    <div class="header">
        <h1>SIT AI Search Settings</h1>
        <p>Configure your OpenAI API Key for the AI-powered search functionality.</p>
    </div>

    <div class="content-wrapper" style="background:#fff; padding: 20px; border-radius: 5px; border: 1px solid #ccc; max-width: 800px; margin-top: 20px;">
        <form method="post" action="options.php">
            <?php settings_fields('sit_ai_search_options'); ?>
            <?php do_settings_sections('sit_ai_search_options'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" style="width: 200px;">
                        <label for="sit_openai_api_key">OpenAI API Key</label>
                    </th>
                    <td>
                        <input type="password" id="sit_openai_api_key" name="sit_openai_api_key" value="<?php echo esc_attr(get_option('sit_openai_api_key')); ?>" class="regular-text" style="width: 100%; max-width: 400px;" />
                        <p class="description">Enter your OpenAI API key starting with <code>sk-</code>. This key will be used to process search queries utilizing the gpt-4o-mini model.</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save API Key'); ?>
        </form>
    </div>
</div>
