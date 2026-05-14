(function (window, document, $) {
    'use strict';

    const state = {
        loader: {
            currentButton: null,
            originalContent: null
        }
    };

    /**
     * Loader Module
     * Handles button state saving, spinner injection, and restoration.
     */
    const Loader = {
        initStyles: function () {
            if (!document.getElementById('dashicon-rotate-style')) {
                const style = document.createElement('style');
                style.id = 'dashicon-rotate-style';
                style.textContent = `
                    @keyframes rotate {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    .button-spinner.dashicons {
                        display: inline-block;
                        animation: rotate 1s linear infinite;
                        text-align: center;
                        flex-shrink: 0;
                    }
                `;
                document.head.appendChild(style);
            }
        },

        show: function (button) {
            if (state.loader.currentButton === button) return;

            state.loader.currentButton = button;
            state.loader.originalContent = button.innerHTML;

            const rect = button.getBoundingClientRect();
            button.style.height = rect.height + 'px';
            button.style.minWidth = rect.width + 'px';
            button.style.width = 'auto';
            button.style.whiteSpace = 'nowrap';

            const computedStyle = window.getComputedStyle(button);
            if (computedStyle.display !== 'flex') {
                button.style.display = 'inline-flex';
                button.style.justifyContent = 'center';
                button.style.alignItems = 'center';
                button.style.gap = '8px';
            }

            const spinner = document.createElement('span');
            spinner.className = 'button-spinner dashicons dashicons-update';

            const existingIcon = button.querySelector('.dashicons');
            if (existingIcon) {
                existingIcon.replaceWith(spinner);
            } else {
                button.prepend(spinner);
            }
        },

        restore: function () {
            const btn = state.loader.currentButton;
            if (btn && state.loader.originalContent !== null) {
                btn.innerHTML = state.loader.originalContent;

                btn.style.width = '';
                btn.style.minWidth = '';
                btn.style.height = '';
                btn.style.display = '';
                btn.style.justifyContent = '';
                btn.style.alignItems = '';
                btn.style.gap = '';
                btn.style.whiteSpace = '';
            }

            state.loader.currentButton = null;
            state.loader.originalContent = null;
        }
    };

    /**
     * Popup Module
     * Handles modal opening, closing, and global click events.
     */
    const Popup = {
        open: function (id) {
            const overlay = document.getElementById(id + '-overlay');
            if (overlay) overlay.style.display = 'flex';
            Loader.restore();
        },

        close: function (overlayElement) {
            if (overlayElement) overlayElement.style.display = 'none';
            Loader.restore();
        },

        handleGlobalClick: function (e) {
            const target = e.target;

            if (target.closest('.torlib-popup-close') || target.classList.contains('torlib-popup-close-footer')) {
                const overlay = target.closest('.torlib-popup-overlay');
                this.close(overlay);
                return;
            }

            if (target.classList.contains('torlib-popup-overlay')) {
                this.close(target);
                return;
            }

            const button = target.closest('[data-popup-target], .torlib-action-loader');

            if (button) {
                if (button.querySelector('.button-spinner')) {
                    e.preventDefault();
                    return;
                }

                const isPopupTrigger = button.hasAttribute('data-popup-target');
                const isLink = button.tagName === 'A';
                const href = button.getAttribute('href');
                const hasRealHref = isLink && href && href !== '#' && !href.startsWith('javascript');

                if (isPopupTrigger || !hasRealHref) {
                    e.preventDefault();
                }

                if (hasRealHref && !isPopupTrigger) {
                    Loader.show(button);
                    setTimeout(() => Loader.restore(), 3000);
                    return;
                }

                requestAnimationFrame(() => Loader.show(button));

                if (isPopupTrigger) {
                    const targetId = button.getAttribute('data-popup-target');
                    if (targetId) {
                        setTimeout(() => this.open(targetId), 150);
                    }
                }
            }
        }
    };

    /**
     * UI Interactions
     * Collapsible sections and clipboard actions.
     */
    const UI = {
        initCopyLink: function () {
            $(document).on("click", '.toret-copy-link', function (e) {
                const link = this.getAttribute('data-link');
                if (link && navigator.clipboard) {
                    navigator.clipboard.writeText(link);
                }
            });
        }
    };

    /**
     * Public API & Initialization
     */
    window.ToretLib = {
        showButtonLoader: Loader.show,
        restoreButton: Loader.restore,
        openPopup: Popup.open
    };

    $(function () {
        Loader.initStyles();
        UI.initCopyLink();

        document.addEventListener('click', Popup.handleGlobalClick.bind(Popup), true);
    });

})(window, document, jQuery);