<?php
/**
 * ═══════════════════════════════════════════════════════════
 * СТРАНИЦА ТОВАРА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    redirect('/catalog.php');
}

$product = getProductById($product_id);

if (!$product) {
    redirect('/catalog.php');
}

$page_title = $product['name'] . ' - Райский уголок';
$discount = $product['old_price'] ? calculateDiscount($product['old_price'], $product['price']) : 0;
$is_favorite = $is_logged_in ? isInFavorites($_SESSION['user_id'], $product_id) : false;
$category = getCategoryById($product['category_id']);

// Похожие товары
$similar_products = getProductsByCategory($product['category_id'], 1, 4);
?>

<style>
.product-page {
    padding: 30px 0 60px;
}

.breadcrumbs {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
    font-size: 14px;
    color: var(--gray-text);
}

.breadcrumbs a {
    color: var(--gray-text);
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumbs a:hover {
    color: var(--primary-green);
}

.breadcrumbs span {
    color: var(--dark-text);
}

.product-main {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-bottom: 40px;
}

.product-gallery {
    position: relative;
}

.product-main-image {
    width: 100%;
    height: 500px;
    border-radius: 12px;
    overflow: hidden;
    background: #F5F5F5;
    margin-bottom: 15px;
}

.product-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.badge {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    color: white;
}

.badge-discount {
    background: var(--red-discount);
}

.badge-new {
    background: var(--primary-green);
}

.product-details {
    display: flex;
    flex-direction: column;
}

.product-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 15px;
    line-height: 1.3;
}

.product-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    font-size: 14px;
    color: var(--gray-text);
}

.product-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.product-price-section {
    background: var(--light-green-bg);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.price-row {
    display: flex;
    align-items: baseline;
    gap: 15px;
    margin-bottom: 10px;
}

.current-price {
    font-size: 42px;
    font-weight: 700;
    color: var(--primary-green);
}

.old-price {
    font-size: 24px;
    color: var(--gray-text);
    text-decoration: line-through;
}

.discount-badge {
    padding: 6px 14px;
    background: var(--red-discount);
    color: white;
    border-radius: 20px;
    font-size: 16px;
    font-weight: 700;
}

.savings {
    font-size: 14px;
    color: var(--gray-text);
}

.product-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    padding: 10px 20px;
}

.qty-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: var(--light-green-bg);
    color: var(--primary-green);
    font-size: 20px;
    font-weight: 700;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.qty-btn:hover {
    background: var(--primary-green);
    color: white;
}

.qty-value {
    font-size: 20px;
    font-weight: 600;
    min-width: 40px;
    text-align: center;
}

.add-to-cart-main {
    flex: 1;
    padding: 18px 30px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.add-to-cart-main:hover {
    background: #5BAE49;
    transform: scale(1.02);
}

.favorite-btn {
    width: 56px;
    height: 56px;
    background: white;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    font-size: 24px;
    color: var(--gray-text);
    cursor: pointer;
    transition: all 0.3s;
}

.favorite-btn:hover,
.favorite-btn.active {
    border-color: var(--red-discount);
    color: var(--red-discount);
}

.product-info-tabs {
    border-top: 2px solid var(--light-gray);
    padding-top: 30px;
}

.product-description {
    font-size: 15px;
    line-height: 1.8;
    color: var(--dark-text);
    margin-bottom: 25px;
}

.product-specs {
    display: grid;
    gap: 15px;
}

.spec-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid var(--light-gray);
}

.spec-label {
    flex: 0 0 200px;
    font-weight: 600;
    color: var(--dark-text);
}

.spec-value {
    flex: 1;
    color: var(--gray-text);
}

.similar-products {
    margin-top: 60px;
}

.section-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-green);
    margin-bottom: 30px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.products-grid .product-card {
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

.products-grid .product-card:hover {
    box-shadow: 0 6px 20px rgba(107, 191, 89, 0.25);
    transform: translateY(-4px);
}

.products-grid .product-image-container {
    position: relative;
    width: 100%;
    height: 180px;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 12px;
    background: #F5F5F5;
}

.products-grid .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.products-grid .product-badge {
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

.products-grid .product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.products-grid .product-name {
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
    text-decoration: none;
}

.products-grid .product-name:hover {
    color: var(--primary-green);
}

.products-grid .product-weight {
    font-size: 13px;
    color: #999;
    margin-bottom: 12px;
}

.products-grid .product-price-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.products-grid .product-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
}

/* Адаптивность */
@media (max-width: 1024px) {
    .product-main {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .product-page {
        padding: 20px 0 40px;
    }
    
    .product-main {
        padding: 20px;
    }
    
    .product-main-image {
        height: 350px;
    }
    
    .product-title {
        font-size: 24px;
    }
    
    .current-price {
        font-size: 32px;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
}
</style>

<div class="product-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-home"></i> Главная</a>
            <span>/</span>
            <a href="<?php echo SITE_URL; ?>/catalog.php">Каталог</a>
            <?php if ($category): ?>
            <span>/</span>
            <a href="<?php echo SITE_URL; ?>/catalog.php?category=<?php echo e($category['slug']); ?>">
                <?php echo e($category['name']); ?>
            </a>
            <?php endif; ?>
            <span>/</span>
            <span><?php echo e($product['name']); ?></span>
        </div>
        
        <!-- Основная информация о товаре -->
        <div class="product-main">
            <div class="product-gallery">
                <div class="product-main-image">
                    <?php if ($product['image_url']): ?>
                    <img src="<?php echo SITE_URL . '/assets/images/products/' . e($product['image_url']); ?>" 
                         alt="<?php echo e($product['name']); ?>">
                    <?php else: ?>
                    <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" alt="Нет изображения">
                    <?php endif; ?>
                </div>
                
                <div class="product-badges">
                    <?php if ($discount > 0): ?>
                    <span class="badge badge-discount">-<?php echo $discount; ?>%</span>
                    <?php endif; ?>
                    <?php if ($product['featured']): ?>
                    <span class="badge badge-new">Хит продаж</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-details">
                <h1 class="product-title"><?php echo e($product['name']); ?></h1>
                
                <div class="product-meta">
                    <div class="product-meta-item">
                        <i class="fas fa-weight"></i>
                        <?php echo e($product['weight']); ?> <?php echo e($product['unit']); ?>
                    </div>
                    <?php if ($product['in_stock'] > 0): ?>
                    <div class="product-meta-item">
                        <i class="fas fa-check-circle" style="color: var(--primary-green);"></i>
                        В наличии
                    </div>
                    <?php else: ?>
                    <div class="product-meta-item">
                        <i class="fas fa-times-circle" style="color: var(--red-discount);"></i>
                        Нет в наличии
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-price-section">
                    <div class="price-row">
                        <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php if ($product['old_price']): ?>
                        <span class="old-price"><?php echo formatPrice($product['old_price']); ?></span>
                        <span class="discount-badge">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($product['old_price']): ?>
                    <div class="savings">
                        Вы экономите: <?php echo formatPrice($product['old_price'] - $product['price']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($product['in_stock'] > 0): ?>
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" onclick="changeQty(-1)">−</button>
                        <span class="qty-value" id="qtyValue">1</span>
                        <button class="qty-btn" onclick="changeQty(1)">+</button>
                    </div>
                    
                    <button class="add-to-cart-main" onclick="addToCartProduct()">
                        <i class="fas fa-shopping-cart"></i> В корзину
                    </button>
                    
                    <?php if ($is_logged_in): ?>
                    <button class="favorite-btn <?php echo $is_favorite ? 'active' : ''; ?>" 
                            id="favoriteBtn"
                            onclick="toggleFavoriteProduct()">
                        <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div style="padding: 20px; background: #FFF3F3; border-radius: 8px; text-align: center; color: var(--red-discount); font-weight: 600;">
                    Товар временно отсутствует
                </div>
                <?php endif; ?>
                
                <div class="product-info-tabs">
                    <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 15px;">Описание</h3>
                    <div class="product-description">
                        <?php echo nl2br(e($product['description'])); ?>
                    </div>
                    
                    <h3 style="font-size: 20px; font-weight: 700; margin: 30px 0 15px;">Характеристики</h3>
                    <div class="product-specs">
                        <?php if ($product['manufacturer']): ?>
                        <div class="spec-row">
                            <div class="spec-label">Производитель:</div>
                            <div class="spec-value"><?php echo e($product['manufacturer']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($product['country']): ?>
                        <div class="spec-row">
                            <div class="spec-label">Страна производства:</div>
                            <div class="spec-value"><?php echo e($product['country']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="spec-row">
                            <div class="spec-label">Вес/объем:</div>
                            <div class="spec-value"><?php echo e($product['weight']); ?> <?php echo e($product['unit']); ?></div>
                        </div>
                        <?php if ($category): ?>
                        <div class="spec-row">
                            <div class="spec-label">Категория:</div>
                            <div class="spec-value">
                                <a href="<?php echo SITE_URL; ?>/catalog.php?category=<?php echo e($category['slug']); ?>">
                                    <?php echo e($category['name']); ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Похожие товары -->
        <?php if (!empty($similar_products) && count($similar_products) > 1): ?>
        <div class="similar-products">
            <h2 class="section-title">Похожие товары</h2>
            <div class="products-grid">
                <?php foreach ($similar_products as $similar): 
                    if ($similar['id'] == $product_id) continue;
                    $sim_discount = $similar['old_price'] ? calculateDiscount($similar['old_price'], $similar['price']) : 0;
                ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $similar['id']; ?>">
                            <?php if ($similar['image_url']): ?>
                            <img src="<?php echo SITE_URL . '/assets/images/products/' . e($similar['image_url']); ?>" 
                                 alt="<?php echo e($similar['name']); ?>" 
                                 class="product-image">
                            <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" alt="Нет изображения" class="product-image">
                            <?php endif; ?>
                        </a>
                        <?php if ($sim_discount > 0): ?>
                        <span class="product-badge">-<?php echo $sim_discount; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $similar['id']; ?>" class="product-name">
                            <?php echo e($similar['name']); ?>
                        </a>
                        <div class="product-weight"><?php echo e($similar['weight']); ?> <?php echo e($similar['unit']); ?></div>
                        <div class="product-price-row">
                            <span class="product-price"><?php echo formatPrice($similar['price']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let quantity = 1;

function changeQty(delta) {
    quantity = Math.max(1, quantity + delta);
    document.getElementById('qtyValue').textContent = quantity;
}

// Переименовал функцию, чтобы не было конфликта с main.js
function addToCartProduct() {
    <?php if (!$is_logged_in): ?>
    window.location.href = '<?php echo SITE_URL; ?>/login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    return;
    <?php endif; ?>
    
    const button = document.querySelector('.add-to-cart-main');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
    button.disabled = true;
    
    fetch('/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add',
            product_id: <?php echo $product_id; ?>,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i> Добавлено!';
            button.style.background = '#4CAF50';
            
            // Обновить счётчик корзины
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
            
            // Показать уведомление если функция доступна
            if (typeof showNotification === 'function') {
                showNotification('Товар добавлен в корзину', 'success');
            }
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 2000);
        } else {
            alert(data.error || 'Ошибка');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Переименовал функцию, чтобы не было конфликта с main.js
function toggleFavoriteProduct() {
    const button = document.getElementById('favoriteBtn');
    const icon = button.querySelector('i');
    const isActive = button.classList.contains('active');
    
    fetch('/api/favorites.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: isActive ? 'remove' : 'add',
            product_id: <?php echo $product_id; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isActive) {
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                if (typeof showNotification === 'function') {
                    showNotification('Удалено из избранного', 'info');
                }
            } else {
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                if (typeof showNotification === 'function') {
                    showNotification('Добавлено в избранное', 'success');
                }
            }
        } else {
            alert(data.error || 'Ошибка');
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>