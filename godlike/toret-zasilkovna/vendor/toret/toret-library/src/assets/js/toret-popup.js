document.addEventListener('DOMContentLoaded', function () {
    const ToretPopup = {
        activePopup: null,
        _boundHandleOverlayClick: null,
        _boundHandleEscapeKey: null,
        _boundHandleResize: null,
        actions: {},

        init: function () {
            this._boundHandleOverlayClick = this.handleOverlayClick.bind(this);
            this._boundHandleEscapeKey = this.handleEscapeKey.bind(this);
            this._boundHandleResize = this.handleResize.bind(this);

            const triggerButtons = document.querySelectorAll('.toret-popup-trigger-button');
            triggerButtons.forEach(button => {
                button.addEventListener('click', (event) => {
                    const targetButton = event.currentTarget;

                    const popupId = targetButton.dataset.popupId || 'my-default-popup';
                    const title = targetButton.dataset.popupTitle || 'Popup';
                    let content = targetButton.dataset.popupContent || '<p>No content provided.</p>';
                    const showHeader = targetButton.dataset.popupShowHeader !== 'false';
                    const showExternalCloseButton = targetButton.dataset.popupShowExternalClose === 'true';
                    const contentSourceId = targetButton.dataset.popupContentSourceId;
                    const size = targetButton.dataset.popupSize || 'medium';

                    if (contentSourceId) {
                        const sourceElement = document.querySelector(contentSourceId);
                        if (sourceElement) {
                            content = sourceElement.innerHTML;
                        } else {
                            console.warn('Popup content source element not found:', contentSourceId);
                        }
                    }

                    const footerButtons = [];
                    if (targetButton.dataset.button1Text) {
                        footerButtons.push({
                            text: targetButton.dataset.button1Text,
                            action: targetButton.dataset.button1Action || null,
                            className: targetButton.dataset.button1Class || '',
                            closeAfterAction: targetButton.dataset.button1Close !== 'false'
                        });
                    }
                    if (targetButton.dataset.button2Text) {
                        footerButtons.push({
                            text: targetButton.dataset.button2Text,
                            action: targetButton.dataset.button2Action || null,
                            className: targetButton.dataset.button2Class || '',
                            closeAfterAction: targetButton.dataset.button2Close !== 'false'
                        });
                    }
                    this.open(popupId, title, content, footerButtons, {
                        showHeader: showHeader,
                        showExternalCloseButton: showExternalCloseButton,
                        size: size
                    });
                });
            });
        },

        create: function (popupId, title, contentHTML, footerButtonsConfig, options = {}) {
            const defaultOptions = {
                showHeader: true,
                showExternalCloseButton: false,
                size: 'medium'
            };
            const finalOptions = { ...defaultOptions, ...options };

            if (document.getElementById(popupId + '-overlay')) {
                const oldPopupOverlay = document.getElementById(popupId + '-overlay');
                if (oldPopupOverlay) {
                    oldPopupOverlay.remove();
                }
            }

            const overlay = document.createElement('div');
            overlay.classList.add('toret-popup-overlay', 'toret-popup-popup-scope');
            overlay.id = popupId + '-overlay';

            const popup = document.createElement('div');
            popup.className = 'toret-popup-content';
            popup.id = popupId;
            popup.setAttribute('role', 'dialog');
            popup.setAttribute('aria-modal', 'true');

            if (finalOptions.size && ['small', 'medium', 'large', 'extra-large','fullscreen'].includes(finalOptions.size)) {
                popup.classList.add(`toret-popup-size-${finalOptions.size}`);
            } else {
                popup.classList.add(`toret-popup-size-medium`);
            }

            let headerElement, internalCloseButton, externalCloseButtonElement = null;

            if (finalOptions.showHeader) {
                popup.setAttribute('aria-labelledby', popupId + '-title');
                headerElement = document.createElement('div');
                headerElement.className = 'toret-popup-header';

                const titleElement = document.createElement('h2');
                titleElement.id = popupId + '-title';
                titleElement.className = 'toret-popup-title';
                titleElement.textContent = title;
                headerElement.appendChild(titleElement);

                internalCloseButton = document.createElement('button');
                internalCloseButton.className = 'toret-popup-close';
                internalCloseButton.innerHTML = '×';
                internalCloseButton.setAttribute('aria-label', 'Close popup');
                internalCloseButton.onclick = () => this.close();
                headerElement.appendChild(internalCloseButton);
                popup.appendChild(headerElement);
            } else {
                popup.classList.add('toret-popup-no-header');
                if (title) {
                    popup.setAttribute('aria-label', title);
                }

                if (finalOptions.showExternalCloseButton) {
                    externalCloseButtonElement = document.createElement('button');
                    externalCloseButtonElement.className = 'toret-popup-external-close-button toret-popup-popup-scope';
                    externalCloseButtonElement.innerHTML = '×';
                    externalCloseButtonElement.setAttribute('aria-label', 'Close popup');
                    externalCloseButtonElement.onclick = () => this.close();
                }
            }

            const body = document.createElement('div');
            body.className = 'toret-popup-body';
            body.innerHTML = contentHTML;
            popup.appendChild(body);

            if (footerButtonsConfig && footerButtonsConfig.length > 0) {
                const footer = document.createElement('div');
                footer.className = 'toret-popup-footer';
                footerButtonsConfig.forEach((btnConfig, index) => {
                    const button = document.createElement('button');
                    button.className = `toret-popup-footer-button toret-popup-footer-button-${index + 1} ${btnConfig.className || ''}`;
                    button.textContent = btnConfig.text;
                    if (btnConfig.action) {
                        button.addEventListener('click', (event) => {
                            if (typeof btnConfig.action === 'function') {
                                btnConfig.action(popupId, button, event);
                            } else if (typeof btnConfig.action === 'string') {
                                const actionParts = btnConfig.action.split('.');
                                let actionFn = window;
                                if (actionParts.length > 1 && actionParts[0] === 'ToretPopup') {
                                    actionFn = this;
                                    for (let i = 1; i < actionParts.length; i++) {
                                        actionFn = actionFn[actionParts[i]];
                                        if (typeof actionFn === 'undefined') break;
                                    }
                                } else {
                                    for (let i = 0; i < actionParts.length; i++) {
                                        actionFn = actionFn[actionParts[i]];
                                        if (typeof actionFn === 'undefined') break;
                                    }
                                }
                                if (typeof actionFn === 'function') {
                                    actionFn(popupId, button, event);
                                } else {
                                    console.warn(`Action function (string) "${btnConfig.action}" not found or not a function.`);
                                }
                            } else {
                                console.warn('Invalid action type for button:', btnConfig.text);
                            }
                            if (btnConfig.closeAfterAction !== false) {
                                this.close();
                            }
                        });
                    } else {
                        button.addEventListener('click', () => this.close());
                    }
                    footer.appendChild(button);
                });
                popup.appendChild(footer);
            }

            let popupContainerWrapper;
            if (externalCloseButtonElement) {
                overlay.appendChild(externalCloseButtonElement);
            }

            overlay.appendChild(popup);
            document.body.appendChild(overlay);

            this.activePopup = { overlay, popup };
            if (internalCloseButton) {
                this.activePopup.closeButton = internalCloseButton;
            }
            if (externalCloseButtonElement) {
                this.activePopup.externalCloseButton = externalCloseButtonElement;
                this.updateExternalCloseButtonPosition();
            }

            const elementToTrapFocusIn = popupContainerWrapper ? popupContainerWrapper : popup;
            this.trapFocus(elementToTrapFocusIn);
        },

        open: function (popupId, title, contentHTML, footerButtonsConfig, options = {}) {
            if (this.activePopup) {
                this.close();
            }
            this.create(popupId, title, contentHTML, footerButtonsConfig, options);

            if (this.activePopup && this.activePopup.overlay) {
                requestAnimationFrame(() => {
                    if (this.activePopup && this.activePopup.overlay) {
                        this.activePopup.overlay.classList.add('toret-popup-overlay--active');

                        if (this.activePopup.externalCloseButton) {
                            this.activePopup.externalCloseButton.focus();
                        } else if (this.activePopup.closeButton) {
                            this.activePopup.closeButton.focus();
                        } else {
                            const focusable = this.activePopup.popup.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                            if (focusable.length > 0) focusable[0].focus();
                        }
                    }
                });
                document.body.style.overflow = 'hidden';
                this.activePopup.overlay.addEventListener('click', this._boundHandleOverlayClick);
                document.addEventListener('keydown', this._boundHandleEscapeKey);
                if (this.activePopup.externalCloseButton) {
                    window.addEventListener('resize', this._boundHandleResize);
                }
            } else {
                console.error("Failed to create popup, activePopup.overlay is not available.");
            }
        },

        close: function () {
            if (!this.activePopup || !this.activePopup.overlay) {
                const strayOverlays = document.querySelectorAll('.toret-popup-overlay.toret-popup-popup-scope');
                strayOverlays.forEach(ov => ov.remove());
                document.body.style.overflow = '';
                window.removeEventListener('resize', this._boundHandleResize);
                return;
            }

            this.activePopup.overlay.classList.remove('toret-popup-overlay--active');
            document.body.style.overflow = '';

            this.activePopup.overlay.removeEventListener('click', this._boundHandleOverlayClick);
            document.removeEventListener('keydown', this._boundHandleEscapeKey);
            if (this.activePopup.externalCloseButton) {
                window.removeEventListener('resize', this._boundHandleResize);
            }

            const transitionDuration = 300;
            const overlayToRemove = this.activePopup.overlay;

            setTimeout(() => {
                if (overlayToRemove && overlayToRemove.parentNode) {
                    overlayToRemove.parentNode.removeChild(overlayToRemove);
                }
            }, transitionDuration);

            this.activePopup = null;
        },

        handleOverlayClick: function(event) {
            if (!this.activePopup || !this.activePopup.overlay) {
                return;
            }
            const clickedElement = event.target;
            const isOverlay = clickedElement === this.activePopup.overlay;
            const isExternalCloseButton = this.activePopup.externalCloseButton && clickedElement === this.activePopup.externalCloseButton;

            if (isOverlay && !isExternalCloseButton) {
                this.close();
            }
        },

        handleEscapeKey: function(event) {
            if (!this.activePopup) {
                return;
            }
            if (event.key === 'Escape') {
                this.close();
            }
        },

        updateExternalCloseButtonPosition: function() {
            if (this.activePopup && this.activePopup.externalCloseButton) {
                const buttonWidth = this.activePopup.externalCloseButton.offsetWidth || 32;
                const marginRight = 20;
                this.activePopup.externalCloseButton.style.left = (window.innerWidth - buttonWidth - marginRight) + 'px';
            }
        },

        handleResize: function() {
            this.updateExternalCloseButtonPosition();
        },

        trapFocus: function(element) {
            if (!element) return;
            const focusableElements = Array.from(element.querySelectorAll(
                'a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )).filter(el => el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0);

            if (focusableElements.length === 0) return;

            const firstFocusableElement = focusableElements[0];
            const lastFocusableElement = focusableElements[focusableElements.length - 1];

            element.addEventListener('keydown', (e) => {
                if (!this.activePopup || !element.contains(document.activeElement)) {
                    return;
                }
                if (e.key !== 'Tab') {
                    return;
                }

                if (e.shiftKey) {
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        e.preventDefault();
                    }
                }
            });
        }
    };

    ToretPopup.init();
    window.ToretPopup = ToretPopup;
});