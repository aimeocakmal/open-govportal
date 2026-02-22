import '../../../js/bootstrap';
import Alpine from 'alpinejs';
import EmblaCarousel from 'embla-carousel';

window.Alpine = Alpine;

/**
 * Alpine.js data component for the hero banner carousel.
 * Uses Embla Carousel (vanilla) for smooth, accessible sliding.
 */
Alpine.data('heroCarousel', () => ({
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

Alpine.start();
