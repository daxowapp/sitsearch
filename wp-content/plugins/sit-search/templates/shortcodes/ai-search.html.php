<div class="sit-ai-search-container" style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: sans-serif;">
    <div class="ai-search-box" style="position: relative; margin-bottom: 20px;">
        <svg style="position: absolute; left: 15px; top: 15px; width: 20px; height: 20px; fill: #888;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 456.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>
        <input type="text" id="sit-ai-search-input" placeholder="Ask AI to find your dream program... (e.g. computer science, medicine in turkish)" style="width: 100%; padding: 15px 15px 15px 45px; border: 2px solid #ddd; border-radius: 30px; font-size: 16px; outline: none;" autocomplete="off">
        <div id="sit-ai-loading" style="display:none; color: #E20A17; margin-top: 10px; font-size: 14px; font-weight: 500;">
            <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span> AI is thinking...
        </div>
    </div>
    
    <div id="sit-ai-results" class="ai-results-container" style="display: grid; gap: 15px;">
        <!-- Results will be dumped here by JS -->
    </div>

    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .ai-program-card {
            border: 1px solid #eaeaea; 
            padding: 20px; 
            border-radius: 10px; 
            transition: box-shadow 0.2s, transform 0.2s;
            background: #fff;
            text-decoration: none;
            color: #333;
            display: block;
        }
        .ai-program-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .ai-program-card h4 {
            margin: 0 0 10px; 
            color: #E20A17;
            font-size: 18px;
        }
        .ai-program-card p {
            margin: 5px 0; 
            font-size: 14px;
        }
    </style>
</div>
