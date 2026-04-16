<div class="sit-ai-search-container sit-ai-mesh-container" style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: sans-serif; position: relative;">
    <div class="sit-ai-mesh-blob sit-ai-mesh-blob-1"></div>
    <div class="sit-ai-mesh-blob sit-ai-mesh-blob-2"></div>
    <form id="sit-ai-search-form" class="ai-search-box" style="position: relative; z-index: 2;">
        <svg style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; fill: #888; z-index:2;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 456.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/></svg>
        <input type="text" id="sit-ai-search-input" placeholder="Ask AI to find your dream program... (e.g. computer science, medicine, هندسة)" style="width: 100%; padding: 18px 120px 18px 50px; border: 2px solid #ddd; border-radius: 30px; font-size: 16px; outline: none; box-sizing: border-box;" autocomplete="off">
        <button type="submit" id="sit-ai-search-btn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #E20A17; color: #fff; border: none; padding: 12px 24px; border-radius: 25px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            <span class="btn-text">Search</span>
            <div class="sit-btn-dots">
                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            </div>
        </button>
    </form>
    
    <div id="sit-ai-loading" style="display:none; color: #E20A17; margin-top: 12px; font-size: 14px; font-weight: 500; text-align: center;">
        <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite; display: inline-block;"></span> AI is thinking... expanding your search with related fields
    </div>

    <div id="sit-ai-suggestions" style="display:none; margin-top: 12px; padding: 10px;">
        <!-- AI suggestion chips appear here -->
    </div>

    <style>
        .ai-suggestion-chip {
            display: inline-block;
            padding: 8px 16px;
            margin: 4px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            background: #fff;
        }
        .ai-suggestion-chip:hover {
            border-color: #E20A17;
            color: #E20A17;
            background: #fef7f7;
        }
        .ai-suggestion-all {
            background: #E20A17;
            color: #fff !important;
            border-color: #E20A17;
            font-weight: 600;
        }
        .ai-suggestion-all:hover {
            background: #c8090f;
            color: #fff !important;
        }
        #sit-ai-search-input:focus {
            border-color: #E20A17;
            box-shadow: 0 0 0 3px rgba(226, 10, 23, 0.1);
        }
    </style>
</div>
