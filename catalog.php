<?php
/**
 * ═══════════════════════════════════════════════════════════
 * КАТАЛОГ ТОВАРОВ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Каталог товаров - Райский уголок';
require_once __DIR__ . '/includes/header.php';

// Параметры фильтрации
$category_slug = $_GET['category'] ?? null;
$search_query = $_GET['q'] ?? null;
$filter = $_GET['filter'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$products = [];
$category = null;
$page_heading = 'Каталог товаров';

// Фильтрация по категории
if ($category_slug) {
    $category = getCategoryBySlug($category_slug);
    if ($category) {
        $products = getProductsByCategory($category['id'], $page);
        $page_heading = $category['name'];
    }
}
// Поиск
elseif ($search_query) {
    $products = searchProducts($search_query, $page);
    $page_heading = 'Результаты поиска: ' . e($search_query);
}
// Акции
elseif ($filter === 'sale') {
    $stmt = $pdo->query("
        SELECT * FROM products 
        WHERE is_active = 1 AND in_stock > 0 AND old_price IS NOT NULL
        ORDER BY created_at DESC
        LIMIT " . PRODUCTS_PER_PAGE
    );
    $products = $stmt->fetchAll();
    $page_heading = 'Акции и скидки';
}
// Все товары
else {
    $products = getPopularProducts(PRODUCTS_PER_PAGE);
    $page_heading = 'Все товары';
}
?>

<style>
.catalog-page {
    padding: 30px 0 60px;
}

.catalog-header {
    margin-bottom: 30px;
}

.catalog-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark-green);
    margin-bottom: 10px;
}

.catalog-subtitle {
    color: var(--gray-text);
    font-size: 15px;
}

.catalog-filters {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.filter-btn {
    padding: 10px 20px;
    background: white;
    border: 2px solid var(--light-gray);
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    color: var(--dark-text);
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--primary-green);
    color: white;
    border-color: var(--primary-green);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.product-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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
    text-decoration: none;
}

.product-name:hover {
    color: var(--primary-green);
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
}

.add-to-cart-btn:hover {
    background: #5BAE49;
    transform: scale(1.02);
}

.no-products {
    text-align: center;
    padding: 80px 20px;
}

.no-products-icon {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 20px;
}

.no-products h3 {
    font-size: 24px;
    color: var(--dark-text);
    margin-bottom: 10px;
}

.no-products p {
    color: var(--gray-text);
    margin-bottom: 30px;
}

/* Адаптивность */
@media (max-width: 768px) {
    .catalog-title {
        font-size: 24px;
    }
    
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .product-card {
        padding: 12px;
    }
    
    .product-image-container {
        height: 160px;
    }
}

@media (max-width: 400px) {
    .products-grid {
        gap: 10px;
    }
}
</style>

<div class="catalog-page">
    <div class="container">
        <div class="catalog-header">
            <h1 class="catalog-title"><?php echo e($page_heading); ?></h1>
            <?php if (!empty($products)): ?>
            <p class="catalog-subtitle">Найдено товаров: <?php echo count($products); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Фильтры -->
        <div class="catalog-filters">
            <a href="<?php echo SITE_URL; ?>/catalog.php" 
               class="filter-btn <?php echo !$category_slug && !$filter ? 'active' : ''; ?>">
                Все товары
            </a>
            <a href="<?php echo SITE_URL; ?>/catalog.php?filter=sale" 
               class="filter-btn <?php echo $filter === 'sale' ? 'active' : ''; ?>">
                Акции
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?php echo SITE_URL; ?>/catalog.php?category=<?php echo e($cat['slug']); ?>" 
               class="filter-btn <?php echo $category_slug === $cat['slug'] ? 'active' : ''; ?>">
                <?php echo e($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($products)): ?>
        <!-- Нет товаров -->
        <div class="no-products">
            <div class="no-products-icon">📦</div>
            <h3>Товары не найдены</h3>
            <p>Попробуйте изменить параметры поиска или выбрать другую категорию</p>
            <a href="<?php echo SITE_URL; ?>/catalog.php" class="btn-login">
                Все товары
            </a>
        </div>
        
        <?php else: ?>
        <!-- Сетка товаров -->
        <div class="products-grid">
            <?php foreach ($products as $product): 
                $discount = $product['old_price'] ? calculateDiscount($product['old_price'], $product['price']) : 0;
                $is_favorite = $is_logged_in ? isInFavorites($_SESSION['user_id'], $product['id']) : false;
            ?>
            <div class="product-card">
                <div class="product-image-container">
                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $product['id']; ?>">
                        <?php if ($product['image_url']): ?>
                        <img src="<?php echo SITE_URL . '/assets/images/products/' . e($product['image_url']); ?>" 
                             alt="<?php echo e($product['name']); ?>" 
                             class="product-image">
                        <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" 
                             alt="Нет изображения" 
                             class="product-image">
                        <?php endif; ?>
                    </a>
                    
                    <?php if ($discount > 0): ?>
                    <span class="product-badge">-<?php echo $discount; ?>%</span>
                    <?php endif; ?>
                    
                    <?php if ($is_logged_in): ?>
                    <button class="product-favorite <?php echo $is_favorite ? 'active' : ''; ?>" 
                            onclick="toggleFavorite(<?php echo $product['id']; ?>, this)">
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
                    
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, this)">
                        <i class="fas fa-shopping-cart"></i> В корзину
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Добавление в корзину
function addToCart(productId, button) {
    <?php if (!$is_logged_in): ?>
    window.location.href = '<?php echo SITE_URL; ?>/login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
    return;
    <?php endif; ?>
    
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
    button.disabled = true;
    
    fetch('<?php echo SITE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i> Добавлено!';
            button.style.background = '#4CAF50';
            updateCartCount();
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
        alert('Произошла ошибка');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Избранное
function toggleFavorite(productId, button) {
    const icon = button.querySelector('i');
    const isActive = button.classList.contains('active');
    
    fetch('<?php echo SITE_URL; ?>/api/favorites.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
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
        alert('Произошла ошибка');
    });
}

// Обновление счетчика корзины
function updateCartCount() {
    fetch('<?php echo SITE_URL; ?>/api/cart.php?action=count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const counts = document.querySelectorAll('.cart-count, .mobile-nav-badge');
                counts.forEach(count => {
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