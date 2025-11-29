/**
 * ═══════════════════════════════════════════════════════════
 * SLIDER.JS - Слайдер главной страницы
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

class Slider {
    constructor(selector, options = {}) {
        this.slider = document.querySelector(selector);
        if (!this.slider) return;
        
        // Настройки по умолчанию
        this.options = {
            autoplay: true,
            autoplayDelay: 5000,
            loop: true,
            ...options
        };
        
        // Элементы
        this.slides = this.slider.querySelectorAll('.slider-slide');
        this.dots = this.slider.querySelectorAll('.slider-dot');
        this.prevBtn = this.slider.querySelector('.slider-arrow.prev');
        this.nextBtn = this.slider.querySelector('.slider-arrow.next');
        
        // Состояние
        this.currentSlide = 0;
        this.totalSlides = this.slides.length;
        this.interval = null;
        this.isAnimating = false;
        
        // Инициализация
        this.init();
    }
    
    init() {
        if (this.totalSlides === 0) return;
        
        // Показать первый слайд
        this.showSlide(0);
        
        // События кнопок
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prev());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.next());
        }
        
        // События точек
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goTo(index));
        });
        
        // Управление с клавиатуры
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
        });
        
        // Свайпы для мобильных
        this.initSwipe();
        
        // Пауза при наведении
        this.slider.addEventListener('mouseenter', () => this.pause());
        this.slider.addEventListener('mouseleave', () => this.resume());
        
        // Автоматическое переключение
        if (this.options.autoplay) {
            this.start();
        }
    }
    
    showSlide(index) {
        if (this.isAnimating) return;
        this.isAnimating = true;
        
        // Циклический переход
        if (this.options.loop) {
            if (index >= this.totalSlides) {
                index = 0;
            } else if (index < 0) {
                index = this.totalSlides - 1;
            }
        } else {
            index = Math.max(0, Math.min(index, this.totalSlides - 1));
        }
        
        this.currentSlide = index;
        
        // Убрать активный класс со всех слайдов и точек
        this.slides.forEach(slide => slide.classList.remove('active'));
        this.dots.forEach(dot => dot.classList.remove('active'));
        
        // Показать текущий слайд
        this.slides[this.currentSlide].classList.add('active');
        this.dots[this.currentSlide].classList.add('active');
        
        // Разрешить следующую анимацию
        setTimeout(() => {
            this.isAnimating = false;
        }, 300);
    }
    
    next() {
        this.showSlide(this.currentSlide + 1);
        this.restart();
    }
    
    prev() {
        this.showSlide(this.currentSlide - 1);
        this.restart();
    }
    
    goTo(index) {
        this.showSlide(index);
        this.restart();
    }
    
    start() {
        if (!this.options.autoplay) return;
        
        this.interval = setInterval(() => {
            this.next();
        }, this.options.autoplayDelay);
    }
    
    pause() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }
    
    resume() {
        if (this.options.autoplay && !this.interval) {
            this.start();
        }
    }
    
    restart() {
        this.pause();
        this.start();
    }
    
    initSwipe() {
        let touchStartX = 0;
        let touchEndX = 0;
        let touchStartY = 0;
        let touchEndY = 0;
        
        this.slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        this.slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            // Проверка, что это горизонтальный свайп
            if (Math.abs(deltaX) > Math.abs(deltaY)) {
                if (deltaX < -50) {
                    this.next();
                } else if (deltaX > 50) {
                    this.prev();
                }
            }
        }, { passive: true });
    }
    
    destroy() {
        this.pause();
        // Удалить все обработчики событий
        if (this.prevBtn) {
            this.prevBtn.removeEventListener('click', () => this.prev());
        }
        if (this.nextBtn) {
            this.nextBtn.removeEventListener('click', () => this.next());
        }
    }
}

// ═══════════════════════════════════════════════════════════
// АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    // Главный слайдер
    const mainSlider = new Slider('#mainSlider', {
        autoplay: true,
        autoplayDelay: 5000,
        loop: true
    });
    
    // Сохранить в window для доступа из HTML
    window.mainSlider = mainSlider;
});

// ═══════════════════════════════════════════════════════════
// ФУНКЦИИ ДЛЯ ИСПОЛЬЗОВАНИЯ В HTML (ОБРАТНАЯ СОВМЕСТИМОСТЬ)
// ═══════════════════════════════════════════════════════════
function nextSlide() {
    if (window.mainSlider) {
        window.mainSlider.next();
    }
}

function prevSlide() {
    if (window.mainSlider) {
        window.mainSlider.prev();
    }
}

function goToSlide(index) {
    if (window.mainSlider) {
        window.mainSlider.goTo(index);
    }
}

// Экспорт
window.Slider = Slider;
window.nextSlide = nextSlide;
window.prevSlide = prevSlide;
window.goToSlide = goToSlide;