import Alpine from "alpinejs"
import collapse from "@alpinejs/collapse"
import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";

import "../css/toret-settings.css"

window.Alpine = Alpine
Alpine.plugin(collapse)

document.addEventListener("alpine:init", () => {

    Alpine.data("multi", () => ({
        init() {
            if (this.$el && !this.$el.classList.contains('tomselected')) {
                new TomSelect(this.$el, {
                    plugins: ['remove_button'],
                    dropdownParent: 'body'
                });
            }
        }
    }));

    Alpine.data("dynamicRow", () => ({
        init() {
            const selects = this.$el.querySelectorAll('select');

            selects.forEach(el => {
                if (el.classList.contains('tomselected')) return;

                new TomSelect(el, {
                    plugins: ['remove_button'],
                    dropdownParent: 'body',
                    create: false,
                    maxItems: el.multiple ? null : 1,
                    onInitialize: function() {
                        this.wrapper.style.display = 'flex';
                        this.wrapper.style.alignItems = 'center';
                    }
                });
            });
        }
    }));

    Alpine.data("toretSettings", (config) => ({
        activeTab: '',
        wideTabs: config.wideTabs || [],

        init() {
            const selects = document.querySelectorAll('.toret-plugin-settings select');

            selects.forEach(el => {
                if (el.classList.contains('tomselected')) return;

                if (el.style.display === 'none') return;

                if (el.hasAttribute('x-data') && el.getAttribute('x-data').includes('multi')) return;

                new TomSelect(el, {
                    plugins: ['remove_button'],
                    dropdownParent: 'body',
                    create: false,
                    maxItems: el.multiple ? null : 1
                });
            });

            const requestedTab = config.requestedTab;
            const validTabs = config.validTabs || [];
            const sessionKey = 'ToretYbox24LibrarySettingsActiveTab';

            let initialTab = config.firstTab;

            if (requestedTab && validTabs.includes(requestedTab)) {
                initialTab = requestedTab;
            }
            else {
                const storedTab = sessionStorage.getItem(sessionKey);
                if (storedTab && validTabs.includes(storedTab)) {
                    initialTab = storedTab;
                }
            }

            this.activeTab = initialTab;

            this.$watch('activeTab', (value) => {
                sessionStorage.setItem(sessionKey, value);

                const url = new URL(window.location);
                url.searchParams.set('tab', value);
                window.history.replaceState({}, '', url);
            });

            const scrollKey = 'ToretYbox24LibraryScrollTarget';
            const savedScrollId = sessionStorage.getItem(scrollKey);

            if (savedScrollId) {
                setTimeout(() => {
                    this.scrollToSub(savedScrollId);
                    sessionStorage.removeItem(scrollKey);
                }, 100);
            }
        },

        scrollToTop() {
            window.scrollTo({ top: 0, behavior: "smooth" });
        },

        scrollToSub(id) {
            this.$nextTick(() => {
                const el = document.getElementById(id);
                if (el) {
                    const yOffset = -50;
                    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({ top: y, behavior: "smooth" });
                }
            });
        },
        saveScrollTarget(id) {
            if(id) {
                sessionStorage.setItem('ToretYbox24LibraryScrollTarget', id);
            }
        }
    }));
});

Alpine.start()