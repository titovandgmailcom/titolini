<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ГЛАВНАЯ СТРАНИЦА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Райский уголок - Свежие продукты с доставкой';

require_once __DIR__ . '/includes/header.php';

// Получить популярные товары
$popular_products = getPopularProducts(12);
?>

<style>
/* ═══════════════════════════════════════════════════════════
   СТИЛИ ГЛАВНОЙ СТРАНИЦЫ
   ═══════════════════════════════════════════════════════════ */

/* БАЗОВЫЕ СТИЛИ ДЛЯ ВСЕХ ЭКРАНОВ */
.main-slider {
    position: relative;
    height: 500px;
    border-radius: 16px;
    overflow: hidden;
    margin: 30px 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.slider-slide {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
}

.slider-slide.active {
    opacity: 1;
    z-index: 1;
}

.slider-content {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
    padding: 0 80px;
    background-size: cover;
    background-position: center;
}

.slider-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
}

.slider-text {
    position: relative;
    max-width: 550px;
    color: white;
    z-index: 2;
}

.slider-title {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
    letter-spacing: -1px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.slider-description {
    font-size: 20px;
    margin-bottom: 30px;
    line-height: 1.5;
    text-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.slider-btn {
    padding: 18px 40px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
    text-decoration: none;
}

.slider-btn:hover {
    background: #5BAE49;
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(107, 191, 89, 0.4);
}

.slider-dots {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 12px;
    z-index: 10;
}

.slider-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.slider-dot:hover {
    background: rgba(255,255,255,0.8);
}

.slider-dot.active {
    background: white;
    width: 32px;
    border-radius: 6px;
}

.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.95);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #333;
    transition: all 0.3s ease;
    z-index: 10;
}

.slider-arrow:hover {
    background: white;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.slider-arrow.prev { left: 30px; }
.slider-arrow.next { right: 30px; }

.section-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark-green);
    margin-bottom: 30px;
    letter-spacing: -0.5px;
}

.categories-section,
.products-section {
    margin: 50px 0;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    text-decoration: none;
    color: var(--dark-text);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.2);
}

.category-icon {
    font-size: 64px;
    margin-bottom: 12px;
}

.category-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--dark-green);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
}

.product-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    text-decoration: none;
    color: var(--dark-text);
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.25);
    transform: translateY(-4px);
}

.product-image-container {
    position: relative;
    width: 100%;
    height: 220px;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 12px;
    background: #F5F5F5;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 6px 12px;
    background: var(--red-discount);
    color: white;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
}

.product-favorite {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 36px;
    height: 36px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    color: var(--gray-text);
    font-size: 16px;
}

.product-favorite:hover,
.product-favorite.active {
    color: var(--red-discount);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-name {
    font-size: 15px;
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 8px;
    line-height: 1.4;
    min-height: 42px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-weight {
    font-size: 13px;
    color: #999;
    margin-bottom: 12px;
}

.product-price-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.product-price {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-green);
}

.product-old-price {
    font-size: 16px;
    color: #999;
    text-decoration: line-through;
}

.product-discount {
    padding: 2px 8px;
    background: #FFE5E5;
    color: var(--red-discount);
    border-radius: 4px;
    font-size: 13px;
    font-weight: 700;
}

.add-to-cart-btn {
    width: 100%;
    padding: 14px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
}

.add-to-cart-btn:hover {
    background: #5BAE49;
    transform: scale(1.02);
}

/* ═══════════════════════════════════════════════════════════
   АДАПТАЦИЯ ДЛЯ ПЛАНШЕТОВ (768px - 1024px)
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .slider-content {
        padding: 0 50px;
    }
    
    .slider-title {
        font-size: 40px;
    }
    
    .slider-description {
        font-size: 18px;
    }
    
    .categories-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ═══════════════════════════════════════════════════════════
   АДАПТАЦИЯ ДЛЯ МОБИЛЬНЫХ (до 768px)
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    /* СЛАЙДЕР */
    .main-slider {
        height: 320px;
        margin: 15px -20px;
        border-radius: 0;
    }
    
    .slider-content {
        padding: 0 20px;
    }
    
    .slider-title {
        font-size: 26px;
        margin-bottom: 12px;
    }
    
    .slider-description {
        font-size: 15px;
        margin-bottom: 18px;
        line-height: 1.4;
    }
    
    .slider-btn {
        padding: 12px 28px;
        font-size: 14px;
    }
    
    .slider-arrow {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    .slider-arrow.prev { left: 10px; }
    .slider-arrow.next { right: 10px; }
    
    .slider-dots {
        bottom: 15px;
        gap: 8px;
    }
    
    .slider-dot {
        width: 8px;
        height: 8px;
    }
    
    .slider-dot.active {
        width: 24px;
    }
    
    /* СЕКЦИИ */
    .categories-section,
    .products-section {
        margin: 30px 0;
    }
    
    .section-title {
        font-size: 22px;
        margin-bottom: 20px;
    }
    
    /* КАТЕГОРИИ - 3 КОЛОНКИ */
    .categories-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .category-card {
        padding: 16px 8px;
    }
    
    .category-icon {
        font-size: 48px;
        margin-bottom: 8px;
    }
    
    .category-name {
        font-size: 12px;
    }
    
    /* ТОВАРЫ - 2 КОЛОНКИ */
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .product-card {
        padding: 10px;
    }
    
    .product-image-container {
        height: 140px;
        margin-bottom: 8px;
    }
    
    .product-badge {
        top: 8px;
        left: 8px;
        padding: 4px 8px;
        font-size: 11px;
    }
    
    .product-favorite {
        top: 8px;
        right: 8px;
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
    
    .product-name {
        font-size: 13px;
        min-height: 36px;
    }
    
    .product-weight {
        font-size: 11px;
        margin-bottom: 8px;
    }
    
    .product-price-row {
        margin-bottom: 8px;
    }
    
    .product-price {
        font-size: 18px;
    }
    
    .product-old-price {
        font-size: 13px;
    }
    
    .product-discount {
        font-size: 11px;
        padding: 2px 6px;
    }
    
    .add-to-cart-btn {
        padding: 10px;
        font-size: 13px;
    }
}

/* ═══════════════════════════════════════════════════════════
   АДАПТАЦИЯ ДЛЯ МАЛЕНЬКИХ ТЕЛЕФОНОВ (до 480px)
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 480px) {
    .main-slider {
        height: 280px;
    }
    
    .slider-title {
        font-size: 22px;
    }
    
    .slider-description {
        font-size: 14px;
    }
    
    .slider-btn {
        padding: 10px 24px;
        font-size: 13px;
    }
    
    .section-title {
        font-size: 20px;
    }
    
    /* КАТЕГОРИИ - 2 КОЛОНКИ НА ОЧЕНЬ МАЛЕНЬКИХ */
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .category-card {
        padding: 12px 6px;
    }
    
    .category-icon {
        font-size: 40px;
    }
    
    .category-name {
        font-size: 11px;
    }
    
    /* ТОВАРЫ */
    .products-grid {
        gap: 8px;
    }
    
    .product-card {
        padding: 8px;
    }
    
    .product-image-container {
        height: 120px;
    }
    
    .product-name {
        font-size: 12px;
    }
    
    .product-price {
        font-size: 16px;
    }
}

/* ═══════════════════════════════════════════════════════════
   АДАПТАЦИЯ ДЛЯ ОЧЕНЬ МАЛЕНЬКИХ ЭКРАНОВ (до 360px)
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 360px) {
    .main-slider {
        height: 240px;
    }
    
    .slider-title {
        font-size: 20px;
    }
    
    .slider-description {
        font-size: 13px;
        margin-bottom: 15px;
    }
    
    .slider-arrow {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }
    
    .slider-arrow.prev { left: 8px; }
    .slider-arrow.next { right: 8px; }
    
    .section-title {
        font-size: 18px;
        margin-bottom: 15px;
    }
    
    .product-image-container {
        height: 110px;
    }
    
    .add-to-cart-btn {
        font-size: 12px;
        padding: 8px;
    }
}

/* ═══════════════════════════════════════════════════════════
   ИСПРАВЛЕНИЕ CONTAINER НА МОБИЛЬНЫХ
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .container {
        padding: 0 15px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 12px;
    }
}

/* ИСПРАВЛЕНИЕ БЕЛЫХ ОТСТУПОВ НА МОБИЛЬНЫХ */
@media (max-width: 768px) {
    /* Убрать белые отступы у слайдера */
    .main-slider {
        margin: 0 -15px 20px -15px;
        border-radius: 0;
    }
    
    /* Увеличить отступы у карточек категорий */
    .category-card {
        padding: 20px 10px;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    /* Секции с большими отступами */
    .categories-section,
    .products-section {
        margin: 25px 0;
    }
}

@media (max-width: 480px) {
    .main-slider {
        margin: 0 -12px 15px -12px;
    }
    
    .category-card {
        padding: 16px 8px;
        min-height: 120px;
    }
}

</style>

<!-- Слайдер -->
<div class="container">
    <div class="main-slider" id="mainSlider">
        <div class="slider-slide active" style="background: linear-gradient(135deg, #6BBF59 0%, #5BAE49 100%);">
            <div class="slider-content">
                <div class="slider-text">
                    <h1 class="slider-title">Свежие продукты каждый день!</h1>
                    <p class="slider-description">
                        Только качественные товары от проверенных поставщиков. 
                        Доставка в день заказа.
                    </p>
                    <a href="<?php echo SITE_URL; ?>/catalog.php" class="slider-btn">
                        Перейти в каталог
                    </a>
                </div>
            </div>
        </div>
        
        <div class="slider-slide" style="background: linear-gradient(135deg, #FF6B35 0%, #F44336 100%);">
            <div class="slider-content">
                <div class="slider-text">
                    <h1 class="slider-title">Программа лояльности!</h1>
                    <p class="slider-description">
                        Получайте до 7% кешбэка с каждой покупки. 
                        Копите бонусы и оплачивайте ими заказы.
                    </p>
                    <?php if ($is_logged_in): ?>
                    <a href="<?php echo SITE_URL; ?>/customer/loyalty.php" class="slider-btn">
                        Моя карта
                    </a>
                    <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="slider-btn">
                        Зарегистрироваться
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="slider-slide" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);">
            <div class="slider-content">
                <div class="slider-text">
                    <h1 class="slider-title">Колесо фортуны!</h1>
                    <p class="slider-description">
                        Крутите колесо каждый день и выигрывайте призы, бонусы и скидки!
                    </p>
                    <?php if ($is_logged_in): ?>
                    <a href="<?php echo SITE_URL; ?>/customer/wheel.php" class="slider-btn">
                        Крутить колесо
                    </a>
                    <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="slider-btn">
                        Войти
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <button class="slider-arrow prev" onclick="prevSlide()">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-arrow next" onclick="nextSlide()">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="slider-dots">
            <button class="slider-dot active" onclick="goToSlide(0)"></button>
            <button class="slider-dot" onclick="goToSlide(1)"></button>
            <button class="slider-dot" onclick="goToSlide(2)"></button>
        </div>
    </div>
</div>

<!-- Категории -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Категории товаров</h2>
        <div class="categories-grid">
            <?php 
            $category_icons = [
                'dairy' => '🥛',
                'meat' => '🥩',
                'bakery' => '🍞',
                'vegetables' => '🥗',
                'drinks' => '🥤',
                'ready-meals' => '🍱',
                'frozen' => '❄️',
                'groceries' => '🌾',
                'sweets' => '🍬',
                'baby-food' => '🍼'
            ];
            
            foreach ($categories as $category): 
                $icon = $category_icons[$category['slug']] ?? '📦';
            ?>
            <a href="<?php echo SITE_URL; ?>/catalog.php?category=<?php echo e($category['slug']); ?>" class="category-card">
                <div class="category-icon"><?php echo $icon; ?></div>
                <div class="category-name"><?php echo e($category['name']); ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Популярные товары -->
<section class="products-section">
    <div class="container">
        <h2 class="section-title">Популярные товары</h2>
        <div class="products-grid">
            <?php foreach ($popular_products as $product): 
                $discount = $product['old_price'] ? calculateDiscount($product['old_price'], $product['price']) : 0;
                $is_favorite = $is_logged_in ? isInFavorites($_SESSION['user_id'], $product['id']) : false;
            ?>
            <div class="product-card">
                <div class="product-image-container">
                    <?php if ($product['image_url']): ?>
                    <img src="<?php echo SITE_URL . '/assets/images/products/' . e($product['image_url']); ?>" 
                         alt="<?php echo e($product['name']); ?>" 
                         class="product-image">
                    <?php else: ?>
                    <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" 
                         alt="Нет изображения" 
                         class="product-image">
                    <?php endif; ?>
                    
                    <?php if ($discount > 0): ?>
                    <span class="product-badge">-<?php echo $discount; ?>%</span>
                    <?php endif; ?>
                    
                    <?php if ($is_logged_in): ?>
                    <button class="product-favorite <?php echo $is_favorite ? 'active' : ''; ?>" 
                            onclick="toggleFavorite(<?php echo $product['id']; ?>, this)"
                            data-product-id="<?php echo $product['id']; ?>">
                        <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $product['id']; ?>" class="product-name">
                        <?php echo e($product['name']); ?>
                    </a>
                    
                    <div class="product-weight">
                        <?php echo e($product['weight']); ?> <?php echo e($product['unit']); ?>
                    </div>
                    
                    <div class="product-price-row">
                        <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php if ($product['old_price']): ?>
                        <span class="product-old-price"><?php echo formatPrice($product['old_price']); ?></span>
                        <span class="product-discount">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                    </div>
                    
                    <button class="add-to-cart-btn" 
                            onclick="addToCart(<?php echo $product['id']; ?>, this)">
                        <i class="fas fa-shopping-cart"></i> В корзину
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo SITE_URL; ?>/catalog.php" class="btn-login" style="padding: 16px 48px; font-size: 16px;">
                Смотреть все товары
            </a>
        </div>
    </div>
</section>

<script>
// ═══════════════════════════════════════════════════════════
// СЛАЙДЕР
// ═══════════════════════════════════════════════════════════
let currentSlide = 0;
const slides = document.querySelectorAll('.slider-slide');
const dots = document.querySelectorAll('.slider-dot');
const totalSlides = slides.length;
let slideInterval;

function showSlide(index) {
    // Циклический переход
    if (index >= totalSlides) {
        currentSlide = 0;
    } else if (index < 0) {
        currentSlide = totalSlides - 1;
    } else {
        currentSlide = index;
    }
    
    // Убрать активный класс со всех слайдов
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    // Показать текущий слайд
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function nextSlide() {
    showSlide(currentSlide + 1);
    resetInterval();
}

function prevSlide() {
    showSlide(currentSlide - 1);
    resetInterval();
}

function goToSlide(index) {
    showSlide(index);
    resetInterval();
}

function resetInterval() {
    clearInterval(slideInterval);
    slideInterval = setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000);
}

// Автоматическое переключение каждые 5 секунд
slideInterval = setInterval(() => {
    showSlide(currentSlide + 1);
}, 5000);

// Управление с клавиатуры
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        prevSlide();
    } else if (e.key === 'ArrowRight') {
        nextSlide();
    }
});

// Свайп для мобильных устройств
let touchStartX = 0;
let touchEndX = 0;

const slider = document.getElementById('mainSlider');

slider.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
});

slider.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    if (touchEndX < touchStartX - 50) {
        nextSlide();
    }
    if (touchEndX > touchStartX + 50) {
        prevSlide();
    }
}

// ═══════════════════════════════════════════════════════════
// ДОБАВЛЕНИЕ В КОРЗИНУ
// ═══════════════════════════════════════════════════════════
function addToCart(productId, button) {
    <?php if (!$is_logged_in): ?>
    window.location.href = '<?php echo SITE_URL; ?>/login.php?redirect=' + encodeURIComponent(window.location.pathname);
    return;
    <?php endif; ?>
    
    // Анимация кнопки
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
    button.disabled = true;
    
    fetch('<?php echo SITE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Успешно добавлено
            button.innerHTML = '<i class="fas fa-check"></i> Добавлено!';
            button.style.background = '#4CAF50';
            
            // Обновить счетчик корзины
            updateCartCount();
            
            // Вернуть кнопку в исходное состояние через 2 секунды
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 2000);
        } else {
            alert(data.error || 'Ошибка при добавлении товара');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка. Попробуйте позже.');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// ═══════════════════════════════════════════════════════════
// ИЗБРАННОЕ
// ═══════════════════════════════════════════════════════════
function toggleFavorite(productId, button) {
    const icon = button.querySelector('i');
    const isActive = button.classList.contains('active');
    
    fetch('<?php echo SITE_URL; ?>/api/favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: isActive ? 'remove' : 'add',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isActive) {
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
            } else {
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
        } else {
            alert(data.error || 'Ошибка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка. Попробуйте позже.');
    });
}

// ═══════════════════════════════════════════════════════════
// ОБНОВЛЕНИЕ СЧЕТЧИКА КОРЗИНЫ
// ═══════════════════════════════════════════════════════════
function updateCartCount() {
    fetch('<?php echo SITE_URL; ?>/api/cart.php?action=count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCounts = document.querySelectorAll('.cart-count, .mobile-nav-badge');
                cartCounts.forEach(count => {
                    if (data.count > 0) {
                        count.textContent = data.count;
                        count.style.display = 'flex';
                    } else {
                        count.style.display = 'none';
                    }
                });
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>