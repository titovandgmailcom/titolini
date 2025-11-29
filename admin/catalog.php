<?php
/**
 * ═══════════════════════════════════════════════════════════
 * УПРАВЛЕНИЕ КАТАЛОГОМ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

$page_title = 'Управление каталогом - Райский уголок';
require_once __DIR__ . '/../includes/header.php';

// Проверка авторизации и роли
requireRole('admin');

// Получить все товары
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";

if ($search) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
}

if ($category_filter) {
    $query .= " AND p.category_id = :category";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);

if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
if ($category_filter) {
    $stmt->bindValue(':category', $category_filter);
}

$stmt->execute();
$products = $stmt->fetchAll();

// Получить категории для фильтра
$categories = getCategories();
?>

<style>
.catalog-page {
    padding: 30px 0 60px;
    background: #f5f5f5;
}

.page-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 10px;
}

.breadcrumb {
    display: flex;
    gap: 10px;
    font-size: 14px;
    color: var(--gray-text);
}

.breadcrumb a {
    color: var(--primary-green);
    text-decoration: none;
}

.filters-bar {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.search-input {
    flex: 1;
    min-width: 250px;
    padding: 12px 16px;
    border: 2px solid #E5E5E5;
    border-radius: 8px;
    font-size: 14px;
}

.filter-select {
    padding: 12px 16px;
    border: 2px solid #E5E5E5;
    border-radius: 8px;
    font-size: 14px;
    min-width: 200px;
}

.btn-add {
    padding: 12px 24px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-add:hover {
    background: #5BAE49;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s;
}

.product-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transform: translateY(-4px);
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #f5f5f5;
}

.product-body {
    padding: 20px;
}

.product-category {
    font-size: 12px;
    color: var(--primary-green);
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.product-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 10px;
}

.product-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 15px;
}

.product-stock {
    font-size: 13px;
    color: var(--gray-text);
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    gap: 10px;
}

.btn-edit, .btn-delete {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
}

.btn-edit {
    background: var(--light-green-bg);
    color: var(--primary-green);
}

.btn-delete {
    background: #FFE5E5;
    color: #E31E24;
}

.no-products {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.no-products i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .filters-bar {
        flex-direction: column;
    }
    
    .search-input, .filter-select {
        width: 100%;
    }
}
</style>

<div class="catalog-page">
    <div class="container">
        <div class="page-header">
            <h1>Управление каталогом</h1>
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Панель администратора</a>
                <span>/</span>
                <span>Каталог</span>
            </div>
        </div>
        
        <div class="filters-bar">
            <form method="GET" style="display: flex; gap: 15px; flex: 1; flex-wrap: wrap;">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Поиск товаров..."
                       value="<?php echo e($search); ?>">
                
                <select name="category" class="filter-select">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn-add">
                    <i class="fas fa-search"></i> Найти
                </button>
            </form>
            
            <a href="#" class="btn-add">
                <i class="fas fa-plus"></i> Добавить товар
            </a>
        </div>
        
        <?php if (empty($products)): ?>
        <div class="no-products">
            <i class="fas fa-box-open"></i>
            <h3>Товары не найдены</h3>
            <p>Попробуйте изменить параметры поиска</p>
        </div>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo SITE_URL . '/' . ($product['image_url'] ?? 'assets/images/no-image.png'); ?>" 
                     alt="<?php echo e($product['name']); ?>" 
                     class="product-image">
                
                <div class="product-body">
                    <div class="product-category"><?php echo e($product['category_name']); ?></div>
                    <h3 class="product-name"><?php echo e($product['name']); ?></h3>
                    <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                    <div class="product-stock">
                        <?php if ($product['in_stock'] > 0): ?>
                            В наличии: <?php echo $product['in_stock']; ?> шт.
                        <?php else: ?>
                            <span style="color: #E31E24;">Нет в наличии</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-actions">
                        <a href="#" class="btn-edit">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                        <button class="btn-delete" onclick="return confirm('Удалить товар?')">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>