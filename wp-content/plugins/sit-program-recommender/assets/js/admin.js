/**
 * SIT Program Recommender Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Admin object
    var SITAdmin = {
        
        // Configuration
        config: {
            ajaxUrl: sitAdmin.ajaxUrl,
            nonce: sitAdmin.nonce,
            strings: sitAdmin.strings
        },
        
        // Initialize admin functionality
        init: function() {
            this.bindEvents();
            this.initQuestionManager();
            this.initCollapsibles();
            this.initThemeSelector();
        },
        
        // Bind event handlers
        bindEvents: function() {
            var self = this;
            
            // OpenAI connection test
            $('#test-openai').on('click', function() {
                self.testOpenAIConnection();
            });
            
            // Export settings
            $('#export-settings').on('click', function() {
                self.exportSettings();
            });
            
            // Import settings
            $('#import-settings').on('click', function() {
                self.importSettings();
            });
            
            // Reset questions
            $('#reset-questions').on('click', function() {
                if (confirm(self.config.strings.confirm_reset)) {
                    self.resetQuestions();
                }
            });
            
            // Add question
            $('#add-question').on('click', function() {
                self.addQuestion();
            });
            
            // Remove question (delegated)
            $(document).on('click', '.remove-question', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to remove this question?')) {
                    $(this).closest('.question-item').remove();
                    self.updateQuestionNumbers();
                }
            });
            
            // Add option (delegated)
            $(document).on('click', '.add-option', function(e) {
                e.preventDefault();
                self.addOption($(this).closest('.question-item'));
            });
            
            // Remove option (delegated)
            $(document).on('click', '.remove-option', function(e) {
                e.preventDefault();
                $(this).closest('.option-item').remove();
            });
            
            // Theme selection
            $(document).on('click', '.theme-option', function() {
                $('.theme-option').removeClass('selected');
                $(this).addClass('selected');
                $('input[name="sit_recommender_display[theme]"]').val($(this).data('theme'));
            });
            
            // Collapsible sections
            $(document).on('click', '.sit-collapsible-header', function() {
                $(this).closest('.sit-collapsible').toggleClass('collapsed');
            });
            
            // Auto-save form data
            $('.sit-admin-form input, .sit-admin-form textarea, .sit-admin-form select').on('change', function() {
                self.autoSave($(this));
            });
        },
        
        // Initialize question manager
        initQuestionManager: function() {
            this.makeQuestionsSortable();
            this.updateQuestionNumbers();
        },
        
        // Make questions sortable
        makeQuestionsSortable: function() {
            if (typeof $.fn.sortable !== 'undefined') {
                $('#questions-container').sortable({
                    handle: '.question-handle',
                    placeholder: 'question-placeholder',
                    update: function() {
                        SITAdmin.updateQuestionNumbers();
                    }
                });
            }
        },
        
        // Update question numbers
        updateQuestionNumbers: function() {
            $('#questions-container .question-item').each(function(index) {
                $(this).find('h4').text('Question ' + (index + 1));
                $(this).attr('data-index', index);
                
                // Update input names
                $(this).find('input, textarea, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', name);
                    }
                });
            });
        },
        
        // Add new question
        addQuestion: function() {
            var questionCount = $('#questions-container .question-item').length;
            var questionHtml = this.getQuestionTemplate(questionCount);
            
            $('#questions-container').append(questionHtml);
            this.updateQuestionNumbers();
            
            // Scroll to new question
            var $newQuestion = $('#questions-container .question-item').last();
            $('html, body').animate({
                scrollTop: $newQuestion.offset().top - 100
            }, 500);
        },
        
        // Get question template HTML
        getQuestionTemplate: function(index) {
            return `
                <div class="question-item" data-index="${index}">
                    <div class="question-handle">⋮⋮</div>
                    <h4>Question ${index + 1}</h4>
                    <table class="form-table">
                        <tr>
                            <th>Question Text</th>
                            <td>
                                <textarea name="sit_recommender_questions[questions][${index}][question]" rows="2" cols="50" placeholder="Enter your question here..."></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>
                                <select name="sit_recommender_questions[questions][${index}][category]">
                                    <option value="interests">Interests</option>
                                    <option value="skills">Skills</option>
                                    <option value="career_goals">Career Goals</option>
                                    <option value="learning_style">Learning Style</option>
                                    <option value="problem_solving">Problem Solving</option>
                                    <option value="study_mode">Study Mode</option>
                                    <option value="industry_preference">Industry Preference</option>
                                    <option value="work_style">Work Style</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                <select name="sit_recommender_questions[questions][${index}][type]">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="scale">Scale (1-5)</option>
                                    <option value="ranking">Ranking</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Required</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="sit_recommender_questions[questions][${index}][required]" value="1">
                                    Required question
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>Weight</th>
                            <td>
                                <input type="number" name="sit_recommender_questions[questions][${index}][weight]" value="1.0" step="0.1" min="0" max="2">
                                <p class="description">Importance of this question (0-2)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="question-options">
                        <h5>Answer Options</h5>
                        <div class="options-container">
                            <!-- Options will be added here -->
                        </div>
                        <button type="button" class="button add-option">Add Option</button>
                    </div>
                    
                    <button type="button" class="button button-link-delete remove-question">Remove Question</button>
                </div>
            `;
        },
        
        // Add option to question
        addOption: function($question) {
            var questionIndex = $question.data('index');
            var optionCount = $question.find('.option-item').length;
            var optionId = String.fromCharCode(97 + optionCount); // a, b, c, etc.
            
            var optionHtml = `
                <div class="option-item">
                    <input type="hidden" name="sit_recommender_questions[questions][${questionIndex}][options][${optionCount}][id]" value="${optionId}">
                    <input type="text" name="sit_recommender_questions[questions][${questionIndex}][options][${optionCount}][text]" placeholder="Option text" style="flex: 1;">
                    <input type="text" name="sit_recommender_questions[questions][${questionIndex}][options][${optionCount}][vector]" placeholder="[0.5, 0.3, 0.8, ...]" class="vector-input" title="Vector values separated by commas">
                    <button type="button" class="button button-link-delete remove-option">Remove</button>
                </div>
            `;
            
            $question.find('.options-container').append(optionHtml);
        },
        
        // Test OpenAI connection
        testOpenAIConnection: function() {
            var $button = $('#test-openai');
            var $result = $('#openai-test-result');
            var apiKey = $('input[name="sit_recommender_openai[api_key]"]').val();
            
            if (!apiKey) {
                this.showMessage($result, 'error', 'Please enter an API key first.');
                return;
            }
            
            $button.addClass('testing').text(this.config.strings.testing);
            $result.hide().removeClass('success error');
            
            $.ajax({
                url: this.config.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'sit_test_openai',
                    nonce: this.config.nonce,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        SITAdmin.showMessage($result, 'success', response.data);
                    } else {
                        SITAdmin.showMessage($result, 'error', response.data);
                    }
                },
                error: function() {
                    SITAdmin.showMessage($result, 'error', 'Connection failed. Please try again.');
                },
                complete: function() {
                    $button.removeClass('testing').text('Test Connection');
                }
            });
        },
        
        // Export settings
        exportSettings: function() {
            var $button = $('#export-settings');
            
            $button.addClass('sit-loading');
            
            $.ajax({
                url: this.config.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'sit_export_settings',
                    nonce: this.config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create download
                        var dataStr = JSON.stringify(response.data, null, 2);
                        var dataBlob = new Blob([dataStr], {type: 'application/json'});
                        var url = URL.createObjectURL(dataBlob);
                        
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = 'sit-recommender-settings-' + new Date().toISOString().split('T')[0] + '.json';
                        link.click();
                        
                        URL.revokeObjectURL(url);
                        
                        SITAdmin.showNotice('Settings exported successfully!', 'success');
                    } else {
                        SITAdmin.showNotice('Export failed: ' + response.data, 'error');
                    }
                },
                error: function() {
                    SITAdmin.showNotice('Export failed. Please try again.', 'error');
                },
                complete: function() {
                    $button.removeClass('sit-loading');
                }
            });
        },
        
        // Import settings
        importSettings: function() {
            var fileInput = document.getElementById('import-file');
            var file = fileInput.files[0];
            
            if (!file) {
                this.showNotice('Please select a file to import.', 'error');
                return;
            }
            
            if (file.type !== 'application/json') {
                this.showNotice('Please select a valid JSON file.', 'error');
                return;
            }
            
            var reader = new FileReader();
            var $button = $('#import-settings');
            
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    
                    $button.addClass('sit-loading');
                    
                    $.ajax({
                        url: SITAdmin.config.ajaxUrl,
                        method: 'POST',
                        data: {
                            action: 'sit_import_settings',
                            nonce: SITAdmin.config.nonce,
                            settings: JSON.stringify(settings)
                        },
                        success: function(response) {
                            if (response.success) {
                                SITAdmin.showNotice(response.data, 'success');
                                // Reload page to show imported settings
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                SITAdmin.showNotice('Import failed: ' + response.data, 'error');
                            }
                        },
                        error: function() {
                            SITAdmin.showNotice('Import failed. Please try again.', 'error');
                        },
                        complete: function() {
                            $button.removeClass('sit-loading');
                        }
                    });
                    
                } catch (error) {
                    SITAdmin.showNotice('Invalid JSON file format.', 'error');
                }
            };
            
            reader.readAsText(file);
        },
        
        // Reset questions to default
        resetQuestions: function() {
            var $container = $('#questions-container');
            
            $container.addClass('sit-loading');
            
            // This would typically make an AJAX call to reset to defaults
            // For now, we'll just clear the container
            setTimeout(function() {
                $container.empty().removeClass('sit-loading');
                SITAdmin.showNotice('Questions reset to default. Please save to apply changes.', 'success');
            }, 1000);
        },
        
        // Initialize collapsible sections
        initCollapsibles: function() {
            $('.sit-collapsible').each(function() {
                var $this = $(this);
                var isCollapsed = $this.hasClass('collapsed');
                
                if (isCollapsed) {
                    $this.find('.sit-collapsible-content').hide();
                }
            });
        },
        
        // Initialize theme selector
        initThemeSelector: function() {
            var currentTheme = $('input[name="sit_recommender_display[theme]"]').val();
            $('.theme-option[data-theme="' + currentTheme + '"]').addClass('selected');
        },
        
        // Auto-save functionality
        autoSave: function($field) {
            var fieldName = $field.attr('name');
            var fieldValue = $field.val();
            
            // Simple auto-save indicator
            var $indicator = $field.siblings('.autosave-indicator');
            if ($indicator.length === 0) {
                $indicator = $('<span class="autosave-indicator">Saving...</span>');
                $field.after($indicator);
            }
            
            $indicator.show().text('Saving...');
            
            // Simulate auto-save (in real implementation, this would make an AJAX call)
            setTimeout(function() {
                $indicator.text('Saved').fadeOut(2000);
            }, 1000);
        },
        
        // Show message in specific container
        showMessage: function($container, type, message) {
            $container.removeClass('success error warning info')
                     .addClass(type)
                     .text(message)
                     .show();
        },
        
        // Show admin notice
        showNotice: function(message, type) {
            var noticeClass = 'notice-' + type;
            var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        // Validate form before submission
        validateForm: function($form) {
            var isValid = true;
            var errors = [];
            
            // Validate required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                if (!$field.val().trim()) {
                    isValid = false;
                    errors.push($field.closest('tr').find('th').text() + ' is required.');
                    $field.addClass('error');
                } else {
                    $field.removeClass('error');
                }
            });
            
            // Validate OpenAI API key format
            var $apiKey = $form.find('input[name="sit_recommender_openai[api_key]"]');
            if ($apiKey.length && $apiKey.val() && !$apiKey.val().startsWith('sk-')) {
                isValid = false;
                errors.push('OpenAI API key should start with "sk-"');
                $apiKey.addClass('error');
            }
            
            // Validate numeric fields
            $form.find('input[type="number"]').each(function() {
                var $field = $(this);
                var val = parseFloat($field.val());
                var min = parseFloat($field.attr('min'));
                var max = parseFloat($field.attr('max'));
                
                if (!isNaN(val)) {
                    if (!isNaN(min) && val < min) {
                        isValid = false;
                        errors.push($field.closest('tr').find('th').text() + ' must be at least ' + min);
                        $field.addClass('error');
                    } else if (!isNaN(max) && val > max) {
                        isValid = false;
                        errors.push($field.closest('tr').find('th').text() + ' must be at most ' + max);
                        $field.addClass('error');
                    } else {
                        $field.removeClass('error');
                    }
                }
            });
            
            if (!isValid) {
                var errorMessage = 'Please fix the following errors:\n\n' + errors.join('\n');
                alert(errorMessage);
            }
            
            return isValid;
        },
        
        // Initialize tooltips
        initTooltips: function() {
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[title]').tooltip({
                    position: { my: "left+15 center", at: "right center" },
                    tooltipClass: "sit-tooltip"
                });
            }
        },
        
        // Initialize color pickers
        initColorPickers: function() {
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.color-picker').wpColorPicker();
            }
        },
        
        // Initialize media uploader
        initMediaUploader: function() {
            $(document).on('click', '.upload-button', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $input = $button.siblings('input[type="text"]');
                
                var mediaUploader = wp.media({
                    title: 'Select Image',
                    button: {
                        text: 'Use This Image'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $input.val(attachment.url);
                });
                
                mediaUploader.open();
            });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        SITAdmin.init();
        
        // Form validation on submit
        $('form').on('submit', function(e) {
            if (!SITAdmin.validateForm($(this))) {
                e.preventDefault();
                return false;
            }
        });
        
        // Initialize additional components if available
        SITAdmin.initTooltips();
        SITAdmin.initColorPickers();
        
        if (typeof wp !== 'undefined' && wp.media) {
            SITAdmin.initMediaUploader();
        }
    });
    
    // Make SITAdmin globally available
    window.SITAdmin = SITAdmin;
    
})(jQuery);
