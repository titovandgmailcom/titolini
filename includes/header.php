<?php
/**
 * ═══════════════════════════════════════════════════════════
 * ШАПКА САЙТА
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

// ВАЖНО: Включить output buffering ПЕРВЫМ делом
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();

// Получить данные для шапки
$cart_count = $is_logged_in ? getCartCount($_SESSION['user_id']) : 0;
$favorites_count = $is_logged_in ? getFavoritesCount($_SESSION['user_id']) : 0;
$categories = getCategories();
$loyalty_card = $is_logged_in ? getLoyaltyCard($_SESSION['user_id']) : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Райский уголок - свежие и качественные продукты с доставкой">
    <title><?php echo $page_title ?? 'Райский уголок - Интернет-магазин продуктов'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Icons (Font Awesome CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Overlay для гамбургер меню -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    
    <!-- Гамбургер меню (мобильное) -->
    <nav class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <div class="mobile-menu-logo">
                <span class="logo-icon">🍃</span>
                <span class="logo-text">Райский уголок</span>
            </div>
            <button class="mobile-menu-close" id="closeMobileMenu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <?php if ($is_logged_in && $loyalty_card): ?>
        <div class="mobile-menu-profile">
            <div class="mobile-profile-avatar">
                <?php if ($current_user['avatar_url']): ?>
                    <img src="<?php echo e($current_user['avatar_url']); ?>" alt="Avatar">
                <?php else: ?>
                    <i class="fas fa-user-circle"></i>
                <?php endif; ?>
            </div>
            <div class="mobile-profile-info">
                <div class="mobile-profile-name">
                    <?php echo e($current_user['first_name'] . ' ' . $current_user['last_name']); ?>
                </div>
                <div class="mobile-profile-level">
                    <?php 
                    $level_settings = LOYALTY_LEVELS[$loyalty_card['current_level']];
                    echo e($level_settings['name']); 
                    ?>
                </div>
                <div class="mobile-profile-points">
                    <i class="fas fa-coins"></i> 
                    <?php echo number_format($loyalty_card['points_balance'], 0, '.', ' '); ?> бонусов
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/customer/dashboard.php" class="mobile-profile-btn">
                Личный кабинет
            </a>
        </div>
        <?php endif; ?>
        
        <div class="mobile-menu-list">
            <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-menu-item">
                <i class="fas fa-home"></i>
                <span>Главная</span>
            </a>
            
            <div class="mobile-menu-item mobile-menu-dropdown">
                <div class="mobile-menu-item-header" onclick="toggleMobileDropdown(this)">
                    <div>
                        <i class="fas fa-th-large"></i>
                        <span>Каталог товаров</span>
                    </div>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
                <div class="mobile-menu-dropdown-content">
                    <?php foreach ($categories as $category): ?>
                    <a href="<?php echo SITE_URL; ?>/catalog.php?category=<?php echo e($category['slug']); ?>" class="mobile-menu-subitem">
                        <?php echo e($category['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <a href="<?php echo SITE_URL; ?>/catalog.php?filter=sale" class="mobile-menu-item">
                <i class="fas fa-percent"></i>
                <span>Акции и скидки</span>
            </a>
            
            <?php if ($is_logged_in): ?>
            <a href="<?php echo SITE_URL; ?>/customer/loyalty.php" class="mobile-menu-item">
                <i class="fas fa-id-card"></i>
                <span>Программа лояльности</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/customer/wheel.php" class="mobile-menu-item">
                <i class="fas fa-dharmachakra"></i>
                <span>Колесо фортуны</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/customer/eco.php" class="mobile-menu-item">
                <i class="fas fa-leaf"></i>
                <span>Эко-программа</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/customer/orders.php" class="mobile-menu-item">
                <i class="fas fa-box"></i>
                <span>Мои заказы</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/customer/favorites.php" class="mobile-menu-item">
                <i class="fas fa-heart"></i>
                <span>Избранное</span>
                <?php if ($favorites_count > 0): ?>
                <span class="mobile-menu-badge"><?php echo $favorites_count; ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo SITE_URL; ?>/about.php" class="mobile-menu-item">
                <i class="fas fa-info-circle"></i>
                <span>О компании</span>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/contact.php" class="mobile-menu-item">
                <i class="fas fa-phone"></i>
                <span>Контакты</span>
            </a>
        </div>
        
        <div class="mobile-menu-footer">
            <?php if ($is_logged_in): ?>
            <a href="<?php echo SITE_URL; ?>/logout.php" class="mobile-menu-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Выход</span>
            </a>
            <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/login.php" class="mobile-menu-login-btn">
                Войти в аккаунт
            </a>
            <a href="<?php echo SITE_URL; ?>/register.php" class="mobile-menu-register-btn">
                Регистрация
            </a>
            <?php endif; ?>
        </div>
    </nav>
    
    <!-- Основная шапка -->
    <header class="site-header">
        <div class="container">
            <div class="header-top">
                <!-- Гамбургер меню (только на мобильных) -->
                <button class="hamburger-menu" id="openMobileMenu" aria-label="Открыть меню">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Логотип -->
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                    <span class="logo-icon">🍃</span>
                    <span class="logo-text">Райский уголок</span>
                </a>
                
                <!-- Поиск (desktop) -->
                <div class="header-search">
                    <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                        <input type="search" 
                               name="q" 
                               class="search-input" 
                               placeholder="Найти продукты..." 
                               value="<?php echo isset($_GET['q']) ? e($_GET['q']) : ''; ?>">
                        <button type="submit" class="search-button" aria-label="Поиск">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Иконка поиска (mobile) -->
                <button class="mobile-search-icon" id="openMobileSearch" aria-label="Поиск">
                    <i class="fas fa-search"></i>
                </button>
                
                <!-- Действия в шапке -->
                <div class="header-actions">
                    <?php if ($is_logged_in): ?>
                    <a href="<?php echo SITE_URL; ?>/customer/favorites.php" class="header-icon-link">
                        <div class="header-icon-wrapper">
                            <i class="fas fa-heart header-icon"></i>
                            <?php if ($favorites_count > 0): ?>
                            <span class="header-count"><?php echo $favorites_count; ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="header-icon-text">Избранное</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="header-icon-link">
                        <div class="header-icon-wrapper">
                            <i class="fas fa-shopping-cart header-icon"></i>
                            <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="header-icon-text">Корзина</span>
                    </a>
                    
                    <?php if ($is_logged_in): ?>
                    <a href="<?php echo SITE_URL; ?>/customer/dashboard.php" class="header-icon-link">
                        <i class="fas fa-user-circle header-icon"></i>
                        <span class="header-icon-text">Профиль</span>
                    </a>
                    <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="btn-login">
                        Войти
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Навигация (desktop) -->
            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="nav-link">Главная</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/catalog.php" class="nav-link">Каталог</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/catalog.php?filter=sale" class="nav-link">Акции</a></li>
                    <?php if ($is_logged_in): ?>
                    <li><a href="<?php echo SITE_URL; ?>/customer/loyalty.php" class="nav-link">Лояльность</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/customer/wheel.php" class="nav-link">Колесо удачи</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/customer/eco.php" class="nav-link">Эко-программа</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="nav-link">О нас</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="nav-link">Контакты</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Полноэкранный поиск (mobile) -->
    <div class="mobile-search-fullscreen" id="mobileSearchFullscreen">
        <div class="mobile-search-header">
            <button class="mobile-search-back" id="closeMobileSearch">
                <i class="fas fa-arrow-left"></i>
            </button>
            <form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="mobile-search-form">
                <input type="search" 
                       name="q" 
                       class="mobile-search-input" 
                       placeholder="Поиск продуктов..." 
                       autofocus>
            </form>
        </div>
        <div class="mobile-search-content">
            <div class="mobile-search-section">
                <h3>Популярные запросы</h3>
                <div class="mobile-search-tags">
                    <a href="<?php echo SITE_URL; ?>/search.php?q=молоко" class="search-tag">Молоко</a>
                    <a href="<?php echo SITE_URL; ?>/search.php?q=хлеб" class="search-tag">Хлеб</a>
                    <a href="<?php echo SITE_URL; ?>/search.php?q=курица" class="search-tag">Курица</a>
                    <a href="<?php echo SITE_URL; ?>/search.php?q=яблоки" class="search-tag">Яблоки</a>
                    <a href="<?php echo SITE_URL; ?>/search.php?q=сок" class="search-tag">Сок</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Нижнее меню (mobile) -->
    <nav class="mobile-bottom-nav">
        <a href="<?php echo SITE_URL; ?>/index.php" class="mobile-bottom-nav-item">
            <i class="fas fa-home"></i>
            <span>Главная</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/catalog.php" class="mobile-bottom-nav-item">
            <i class="fas fa-th-large"></i>
            <span>Каталог</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/cart.php" class="mobile-bottom-nav-item">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cart_count > 0): ?>
            <span class="mobile-nav-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
            <span>Корзина</span>
        </a>
        <?php if ($is_logged_in): ?>
        <a href="<?php echo SITE_URL; ?>/customer/favorites.php" class="mobile-bottom-nav-item">
            <i class="fas fa-heart"></i>
            <?php if ($favorites_count > 0): ?>
            <span class="mobile-nav-badge"><?php echo $favorites_count; ?></span>
            <?php endif; ?>
            <span>Избранное</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/customer/dashboard.php" class="mobile-bottom-nav-item">
            <i class="fas fa-user"></i>
            <span>Профиль</span>
        </a>
        <?php else: ?>
        <a href="<?php echo SITE_URL; ?>/login.php" class="mobile-bottom-nav-item">
            <i class="fas fa-user"></i>
            <span>Войти</span>
        </a>
        <?php endif; ?>
    </nav>
    
    <!-- Flash Messages -->
    <?php if (hasFlash('success')): ?>
    <div class="flash-message flash-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo e(getFlash('success')); ?></span>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <?php if (hasFlash('error')): ?>
    <div class="flash-message flash-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo e(getFlash('error')); ?></span>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <?php if (hasFlash('info')): ?>
    <div class="flash-message flash-info">
        <i class="fas fa-info-circle"></i>
        <span><?php echo e(getFlash('info')); ?></span>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Main Content Start -->
    <main class="main-content">