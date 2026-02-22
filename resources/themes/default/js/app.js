import '../../../js/bootstrap';
import EmblaCarousel from 'embla-carousel';

/**
 * Register Alpine.js data components via Livewire's bundled Alpine.
 * Livewire 4 bundles Alpine — do NOT import or start Alpine manually.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('heroCarousel', () => ({
        embla: null,
        current: 0,
        total: 0,
        autoplayTimer: null,

        init() {
            const viewport = this.$refs.viewport;
            if (!viewport) return;

            this.embla = EmblaCarousel(viewport, {
                loop: true,
                align: 'start',
                containScroll: 'trimSnaps',
            });

            this.total = this.embla.scrollSnapList().length;
            this.current = this.embla.selectedScrollSnap();

            this.embla.on('select', () => {
                this.current = this.embla.selectedScrollSnap();
            });

            this.startAutoplay();
        },

        prev() {
            this.embla?.scrollPrev();
            this.resetAutoplay();
        },

        next() {
            this.embla?.scrollNext();
            this.resetAutoplay();
        },

        goTo(index) {
            this.embla?.scrollTo(index);
            this.resetAutoplay();
        },

        startAutoplay() {
            this.autoplayTimer = setInterval(() => {
                this.embla?.scrollNext();
            }, 5000);
        },

        resetAutoplay() {
            clearInterval(this.autoplayTimer);
            this.startAutoplay();
        },

        destroy() {
            clearInterval(this.autoplayTimer);
            this.embla?.destroy();
        },
    }));

    window.Alpine.data('accessibilityMenu', () => ({
        open: false,
        fontSize: 'default',
        fontType: 'default',
        bgColor: 'default',
        contrast: 'default',

        init() {
            this.fontSize = localStorage.getItem('a11y-fontsize') || 'default';
            this.fontType = localStorage.getItem('a11y-fonttype') || 'default';
            this.bgColor = localStorage.getItem('a11y-bgcolor') || 'default';
            this.contrast = localStorage.getItem('a11y-contrast') || 'default';
            this.apply();
        },

        setFontSize(val) {
            this.fontSize = val;
            localStorage.setItem('a11y-fontsize', val);
            this.apply();
        },

        setFontType(val) {
            this.fontType = val;
            localStorage.setItem('a11y-fonttype', val);
            this.apply();
        },

        setBgColor(val) {
            this.bgColor = val;
            localStorage.setItem('a11y-bgcolor', val);
            this.apply();
        },

        setContrast(val) {
            this.contrast = val;
            localStorage.setItem('a11y-contrast', val);
            this.apply();
        },

        reset() {
            this.fontSize = 'default';
            this.fontType = 'default';
            this.bgColor = 'default';
            this.contrast = 'default';
            ['a11y-fontsize', 'a11y-fonttype', 'a11y-bgcolor', 'a11y-contrast']
                .forEach(k => localStorage.removeItem(k));
            this.apply();
        },

        apply() {
            const d = document.documentElement.dataset;
            d.a11yFontsize = this.fontSize;
            d.a11yFonttype = this.fontType;
            d.a11yBgcolor = this.bgColor;
            d.a11yContrast = this.contrast;
        },

        toggle() {
            this.open = !this.open;
        },
    }));
});
