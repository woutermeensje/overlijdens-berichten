{{--
    Rich text editor (Quill 2), overgenomen uit inlog-omgeving.
    Gebruik: <textarea name="..." data-rich-text> of data-rich-text="basic" (zonder koppen/links).
    Optioneel: data-editor-height="240" (px of CSS-lengte), placeholder, required.
--}}
@once
    @push('head')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        <style>
            .rte {
                --rte-height: 260px;
                --rte-accent: #5a3a3a;
                --rte-accent-soft: #f3e9e2;
                --rte-icon: #6f6262;
                --rte-border: var(--color-base-300, #e5e0dc);
                border: 1px solid var(--rte-border);
                border-radius: 5px;
                background: #fff;
                overflow: hidden;
                transition: border-color .15s ease;
            }
            .rte:hover { border-color: #d6ccc6; }
            .rte:focus-within { border-color: var(--rte-accent); }
            .rte.is-invalid { border-color: #dc2626; }

            /* Toolbar */
            .rte .ql-toolbar.ql-snow {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 4px;
                border: 0;
                border-bottom: 1px solid var(--rte-border);
                padding: 6px 8px;
                background: #faf8f6;
                font-family: inherit;
            }
            .rte .ql-toolbar.ql-snow .ql-formats {
                display: inline-flex;
                align-items: center;
                gap: 2px;
                margin: 0;
                padding-right: 6px;
                margin-right: 2px;
                border-right: 1px solid var(--rte-border);
            }
            .rte .ql-toolbar.ql-snow .ql-formats:last-child { border-right: 0; padding-right: 0; }

            .rte .ql-toolbar.ql-snow button {
                width: 32px;
                height: 32px;
                padding: 7px;
                border-radius: 5px;
                transition: background-color .12s ease;
            }
            .rte .ql-toolbar.ql-snow button svg { width: 18px; height: 18px; }
            .rte .ql-toolbar.ql-snow button:hover,
            .rte .ql-toolbar.ql-snow button:focus-visible { background: #f1ebe7; outline: none; }
            .rte .ql-toolbar.ql-snow button.ql-active { background: var(--rte-accent-soft); }

            /* Iconkleuren: rustig grijs, accentkleur bij hover/actief i.p.v. Quill-blauw */
            .rte .ql-snow .ql-stroke { stroke: var(--rte-icon); stroke-width: 1.6; }
            .rte .ql-snow .ql-fill { fill: var(--rte-icon); }
            .rte .ql-snow .ql-picker { color: var(--rte-icon); }
            .rte .ql-snow.ql-toolbar button:hover .ql-stroke,
            .rte .ql-snow.ql-toolbar button.ql-active .ql-stroke,
            .rte .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke,
            .rte .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke { stroke: var(--rte-accent); }
            .rte .ql-snow.ql-toolbar button:hover .ql-fill,
            .rte .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: var(--rte-accent); }
            .rte .ql-snow.ql-toolbar .ql-picker-label:hover,
            .rte .ql-snow.ql-toolbar .ql-picker-label.ql-active,
            .rte .ql-snow.ql-toolbar .ql-picker-item:hover,
            .rte .ql-snow.ql-toolbar .ql-picker-item.ql-selected { color: var(--rte-accent); }

            /* Kopjes-dropdown */
            .rte .ql-snow .ql-picker.ql-header { width: 118px; height: 32px; font-size: 13px; font-weight: 500; }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-label {
                display: flex;
                align-items: center;
                padding: 0 8px;
                border: 0;
                border-radius: 5px;
            }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-label:hover { background: #f1ebe7; }
            .rte .ql-snow .ql-picker.ql-expanded .ql-picker-label { background: var(--rte-accent-soft); border: 0; }
            .rte .ql-snow .ql-picker.ql-expanded .ql-picker-options {
                margin-top: 4px;
                padding: 4px;
                border: 1px solid var(--rte-border);
                border-radius: 5px;
            }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-item { padding: 4px 8px; border-radius: 4px; }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-label::before,
            .rte .ql-snow .ql-picker.ql-header .ql-picker-item::before { content: 'Normale tekst'; }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-label[data-value='2']::before,
            .rte .ql-snow .ql-picker.ql-header .ql-picker-item[data-value='2']::before { content: 'Kop'; }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-label[data-value='3']::before,
            .rte .ql-snow .ql-picker.ql-header .ql-picker-item[data-value='3']::before { content: 'Subkop'; }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-item[data-value='2']::before { font-size: 1.15em; font-weight: 600; }
            .rte .ql-snow .ql-picker.ql-header .ql-picker-item[data-value='3']::before { font-size: 1.05em; font-weight: 600; }

            /* Schrijfvlak */
            .rte .ql-container.ql-snow { border: 0; height: auto; font-family: inherit; }
            .rte .ql-editor {
                min-height: var(--rte-height);
                max-height: max(var(--rte-height), 60vh);
                overflow-y: auto;
                overscroll-behavior: contain;
                padding: 14px 16px;
                font-size: 15px;
                line-height: 1.65;
                color: #2f2a2a;
            }
            .rte .ql-editor.ql-blank::before {
                left: 16px;
                right: 16px;
                font-style: normal;
                color: #a59a96;
            }
            .rte .ql-editor p + p { margin-top: .6em; }
            .rte .ql-editor h2 { font-size: 1.3em; font-weight: 600; margin: 1em 0 .4em; line-height: 1.3; }
            .rte .ql-editor h3 { font-size: 1.12em; font-weight: 600; margin: .9em 0 .35em; line-height: 1.3; }
            .rte .ql-editor h2:first-child,
            .rte .ql-editor h3:first-child { margin-top: 0; }
            .rte .ql-editor ol { margin: .6em 0; padding-left: .5em; }
            .rte .ql-editor li { margin: .2em 0; }
            .rte .ql-editor blockquote {
                margin: .8em 0;
                padding: .2em 0 .2em 14px;
                border-left: 3px solid #d9c7bc;
                color: #5b5050;
                font-style: italic;
            }
            .rte .ql-editor a { color: var(--rte-accent); text-decoration: underline; }

            /* Link-tooltip in het Nederlands en in huisstijl */
            .rte .ql-snow .ql-tooltip {
                z-index: 20;
                padding: 8px 12px;
                border: 1px solid var(--rte-border);
                border-radius: 5px;
                color: #2f2a2a;
                font-size: 13px;
            }
            .rte .ql-snow .ql-tooltip::before { content: 'Link:'; }
            .rte .ql-snow .ql-tooltip[data-mode='link']::before { content: 'URL:'; }
            .rte .ql-snow .ql-tooltip input[type='text'] {
                height: 30px;
                border: 1px solid var(--rte-border);
                border-radius: 5px;
                padding: 0 8px;
                font-size: 13px;
            }
            .rte .ql-snow .ql-tooltip input[type='text']:focus { outline: none; border-color: var(--rte-accent); }
            .rte .ql-snow .ql-tooltip a.ql-preview { color: var(--rte-accent); }
            .rte .ql-snow .ql-tooltip a.ql-action::after { content: 'Bewerken'; color: var(--rte-accent); }
            .rte .ql-snow .ql-tooltip a.ql-remove::before { content: 'Verwijderen'; color: #b42318; }
            .rte .ql-snow .ql-tooltip.ql-editing a.ql-action::after { content: 'Opslaan'; }

            .rte__source { display: none !important; }

            @media (max-width: 480px) {
                .rte .ql-toolbar.ql-snow { padding: 4px 6px; }
                .rte .ql-toolbar.ql-snow .ql-formats { padding-right: 4px; }
                .rte .ql-toolbar.ql-snow button { width: 30px; height: 30px; padding: 6px; }
            }
        </style>
        <script>
            (function () {
                var presets = {
                    full: {
                        toolbar: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'link'],
                            ['clean'],
                        ],
                        formats: ['header', 'bold', 'italic', 'underline', 'strike', 'list', 'blockquote', 'link'],
                    },
                    basic: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['clean'],
                        ],
                        formats: ['bold', 'italic', 'underline', 'list'],
                    },
                };

                var titles = {
                    bold: 'Vet', italic: 'Cursief', underline: 'Onderstrepen', strike: 'Doorhalen',
                    'list:ordered': 'Genummerde lijst', 'list:bullet': 'Opsommingslijst',
                    blockquote: 'Citaat', link: 'Link invoegen', clean: 'Opmaak wissen',
                };

                var instances = [];
                var formsBound = false;

                function looksLikeHtml(value) {
                    return /<\/?[a-z][\s\S]*>/i.test(value || '');
                }

                function stripHtml(value) {
                    return String(value || '').replace(/<[^>]*>/g, '').trim();
                }

                function sync(instance) {
                    var text = instance.quill.getText().trim();
                    // getSemanticHTML levert echte <ul>/<ol> (root.innerHTML geeft in Quill 2 altijd <ol data-list>).
                    var html = instance.quill.getSemanticHTML().replace(/&nbsp;/g, ' ');

                    instance.textarea.value = text ? html : '';
                }

                function labelToolbar(toolbar) {
                    toolbar.querySelectorAll('button').forEach(function (button) {
                        var format = (button.className.match(/ql-(\w+)/) || [])[1];
                        var key = button.value ? format + ':' + button.value : format;

                        if (titles[key]) {
                            button.setAttribute('title', titles[key]);
                            button.setAttribute('aria-label', titles[key]);
                        }
                    });
                }

                function bindFormValidation() {
                    if (formsBound) return;
                    formsBound = true;

                    document.addEventListener('submit', function (event) {
                        var form = event.target;
                        var firstInvalid = null;

                        instances.forEach(function (instance) {
                            if (!form.contains(instance.textarea)) return;

                            sync(instance);

                            var invalid = instance.required && stripHtml(instance.textarea.value) === '';
                            instance.wrapper.classList.toggle('is-invalid', invalid);
                            if (invalid && !firstInvalid) firstInvalid = instance;
                        });

                        if (firstInvalid) {
                            event.preventDefault();
                            firstInvalid.quill.focus();
                        }
                    }, true);
                }

                function init(textarea, index) {
                    if (typeof window.Quill === 'undefined' || textarea.dataset.richTextEnhanced === '1') return null;

                    var preset = presets[textarea.dataset.richText] || presets.full;
                    var wrapper = document.createElement('div');
                    wrapper.className = 'rte';

                    var editor = document.createElement('div');
                    editor.id = (textarea.id || 'rte_' + index) + '__editor';

                    var height = textarea.dataset.editorHeight;
                    if (height) {
                        wrapper.style.setProperty('--rte-height', /^\d+$/.test(height) ? height + 'px' : height);
                    }

                    textarea.parentNode.insertBefore(wrapper, textarea);
                    wrapper.appendChild(editor);
                    wrapper.appendChild(textarea);

                    var required = textarea.required;
                    textarea.required = false;
                    textarea.classList.add('rte__source');
                    textarea.dataset.richTextEnhanced = '1';

                    var quill = new window.Quill(editor, {
                        theme: 'snow',
                        placeholder: textarea.getAttribute('placeholder') || '',
                        formats: preset.formats,
                        modules: { toolbar: preset.toolbar },
                    });

                    labelToolbar(quill.getModule('toolbar').container);

                    var initialValue = textarea.value || '';
                    if (initialValue.trim() !== '') {
                        if (looksLikeHtml(initialValue)) {
                            quill.clipboard.dangerouslyPasteHTML(initialValue, 'silent');
                        } else {
                            quill.setText(initialValue, 'silent');
                        }
                    }

                    var instance = { textarea: textarea, quill: quill, wrapper: wrapper, required: required };

                    sync(instance);
                    quill.on('text-change', function () {
                        sync(instance);
                        if (instance.required && quill.getText().trim() !== '') {
                            wrapper.classList.remove('is-invalid');
                        }
                    });
                    instances.push(instance);
                    bindFormValidation();

                    return instance;
                }

                function initAll() {
                    document.querySelectorAll('textarea[data-rich-text]').forEach(init);
                }

                window.RichTextEditor = { init: init, initAll: initAll, instances: instances };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAll);
                } else {
                    initAll();
                }
            })();
        </script>
    @endpush
@endonce
