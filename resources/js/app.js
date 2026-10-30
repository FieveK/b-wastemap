import Alpine from 'alpinejs';
import './report-camera';
import './report-tracking';
import './admin-notifications';
import { addBanjarmasinBoundaries } from './map-boundaries';

window.Alpine = Alpine;

/**
 * Shared toggle/dropdown behaviour.
 * Used by the mobile nav toggle and the dropdown component.
 */
Alpine.data('dropdown', () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },
}));

/**
 * Modal behaviour (focus trap + open/close-by-name), previously written
 * inline in resources/views/components/modal.blade.php.
 *
 * All the real logic now lives here as plain, normally-compiled JS instead
 * of inside an HTML attribute string that Alpine has to interpret at
 * runtime with `new Function()` — that runtime interpretation is what a
 * strict `script-src` (without `unsafe-eval`) blocks.
 */
Alpine.data('modal', (name, show = false, focusOnOpen = false) => ({
    name,
    show,
    focusOnOpen,

    init() {
        this.$watch('show', (value) => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');

                if (this.focusOnOpen) {
                    setTimeout(() => this.firstFocusable()?.focus(), 100);
                }
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
    },

    focusables() {
        const selector =
            'a, button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])';

        return [...this.$el.querySelectorAll(selector)].filter(
            (el) => !el.hasAttribute('disabled')
        );
    },

    firstFocusable() {
        return this.focusables()[0];
    },

    lastFocusable() {
        return this.focusables().slice(-1)[0];
    },

    nextFocusableIndex() {
        return (
            (this.focusables().indexOf(document.activeElement) + 1) %
            (this.focusables().length + 1)
        );
    },

    prevFocusableIndex() {
        return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1;
    },

    nextFocusable() {
        return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable();
    },

    prevFocusable() {
        return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable();
    },

    handleOpen(detail) {
        if (detail === this.name) this.show = true;
    },

    handleClose(detail) {
        if (detail === this.name) this.show = false;
    },

    handleTab(event) {
        if (!event.shiftKey) this.nextFocusable()?.focus();
    },

    handleShiftTab() {
        this.prevFocusable()?.focus();
    },
}));

/**
 * "Saved." status message that auto-hides after 2 seconds
 * (profile + password update forms).
 */
Alpine.data('autoHideMessage', (delay = 2000) => ({
    show: true,

    init() {
        setTimeout(() => {
            this.show = false;
        }, delay);
    },
}));

Alpine.start();

const initializeMapBoundaries = () => {
    document.querySelectorAll('[data-bw-map-boundaries]').forEach((element) => {
        if (element.__bwMap) addBanjarmasinBoundaries(element.__bwMap);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMapBoundaries, { once: true });
} else {
    initializeMapBoundaries();
}
