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
});
