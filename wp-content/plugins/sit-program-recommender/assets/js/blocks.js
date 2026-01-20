/**
 * SIT Program Recommender Gutenberg Block
 */

(function() {
    'use strict';
    
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, ToggleControl, SelectControl, RangeControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el } = wp.element;
    
    registerBlockType('sit/program-recommender', {
        title: sitBlocks.strings.title,
        description: sitBlocks.strings.description,
        icon: 'welcome-learn-more',
        category: 'widgets',
        keywords: [
            __('program', 'sit-program-recommender'),
            __('recommender', 'sit-program-recommender'),
            __('quiz', 'sit-program-recommender'),
            __('assessment', 'sit-program-recommender')
        ],
        
        attributes: {
            theme: {
                type: 'string',
                default: 'default'
            },
            showFilters: {
                type: 'boolean',
                default: true
            },
            showSearch: {
                type: 'boolean',
                default: true
            },
            maxResults: {
                type: 'number',
                default: 10
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { theme, showFilters, showSearch, maxResults } = attributes;
            
            return [
                // Inspector Controls (Sidebar)
                el(InspectorControls, {},
                    el(PanelBody, {
                        title: __('Settings', 'sit-program-recommender'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: sitBlocks.strings.theme,
                            value: theme,
                            options: [
                                { label: __('Default', 'sit-program-recommender'), value: 'default' },
                                { label: __('Modern', 'sit-program-recommender'), value: 'modern' },
                                { label: __('Minimal', 'sit-program-recommender'), value: 'minimal' }
                            ],
                            onChange: function(value) {
                                setAttributes({ theme: value });
                            }
                        }),
                        
                        el(ToggleControl, {
                            label: sitBlocks.strings.showFilters,
                            checked: showFilters,
                            onChange: function(value) {
                                setAttributes({ showFilters: value });
                            }
                        }),
                        
                        el(ToggleControl, {
                            label: sitBlocks.strings.showSearch,
                            checked: showSearch,
                            onChange: function(value) {
                                setAttributes({ showSearch: value });
                            }
                        }),
                        
                        el(RangeControl, {
                            label: sitBlocks.strings.maxResults,
                            value: maxResults,
                            onChange: function(value) {
                                setAttributes({ maxResults: value });
                            },
                            min: 5,
                            max: 50,
                            step: 5
                        })
                    )
                ),
                
                // Block Preview
                el('div', {
                    className: 'sit-block-preview'
                },
                    el('div', {
                        className: 'sit-block-preview-header'
                    },
                        el('h3', {}, sitBlocks.strings.title),
                        el('p', {}, sitBlocks.strings.description)
                    ),
                    
                    el('div', {
                        className: 'sit-block-preview-content'
                    },
                        el('div', {
                            className: 'sit-preview-feature'
                        },
                            el('span', { className: 'dashicons dashicons-forms' }),
                            el('span', {}, __('Interactive Quiz', 'sit-program-recommender'))
                        ),
                        
                        el('div', {
                            className: 'sit-preview-feature'
                        },
                            el('span', { className: 'dashicons dashicons-analytics' }),
                            el('span', {}, __('Smart Recommendations', 'sit-program-recommender'))
                        ),
                        
                        showFilters && el('div', {
                            className: 'sit-preview-feature'
                        },
                            el('span', { className: 'dashicons dashicons-filter' }),
                            el('span', {}, __('Advanced Filters', 'sit-program-recommender'))
                        ),
                        
                        showSearch && el('div', {
                            className: 'sit-preview-feature'
                        },
                            el('span', { className: 'dashicons dashicons-search' }),
                            el('span', {}, __('Program Search', 'sit-program-recommender'))
                        )
                    ),
                    
                    el('div', {
                        className: 'sit-block-preview-settings'
                    },
                        el('small', {},
                            __('Theme: ', 'sit-program-recommender') + theme.charAt(0).toUpperCase() + theme.slice(1) +
                            ' | ' + __('Max Results: ', 'sit-program-recommender') + maxResults
                        )
                    )
                )
            ];
        },
        
        save: function() {
            // Return null because we render server-side
            return null;
        }
    });
    
})();
