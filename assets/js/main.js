/**
 * ═══════════════════════════════════════════════════════════
 * MAIN.JS - Основные функции сайта
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

// ═══════════════════════════════════════════════════════════
// ИНИЦИАЛИЗАЦИЯ ПРИ ЗАГРУЗКЕ
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initMobileSearch();
    initScrollToTop();
    initFlashMessages();
    initNavigationActive();
    initMobileBottomNav();
});

// ═══════════════════════════════════════════════════════════
// ГАМБУРГЕР МЕНЮ
// ═══════════════════════════════════════════════════════════
function initMobileMenu() {
    const openBtn = document.getElementById('openMobileMenu');
    const closeBtn = document.getElementById('closeMobileMenu');
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            menu.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMobileMenu);
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMobileMenu);
    }
}

function closeMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (menu) menu.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
}

// Переключение dropdown в мобильном меню
function toggleMobileDropdown(element) {
    const parent = element.parentElement;
    const content = parent.querySelector('.mobile-menu-dropdown-content');
    const arrow = element.querySelector('.dropdown-arrow');
    
    parent.classList.toggle('active');
    
    if (parent.classList.contains('active')) {
        content.style.maxHeight = content.scrollHeight + 'px';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        content.style.maxHeight = '0';
        arrow.style.transform = 'rotate(0deg)';
    }
}

// ═══════════════════════════════════════════════════════════
// МОБИЛЬНЫЙ ПОИСК
// ═══════════════════════════════════════════════════════════
function initMobileSearch() {
    const openBtn = document.getElementById('openMobileSearch');
    const closeBtn = document.getElementById('closeMobileSearch');
    const searchScreen = document.getElementById('mobileSearchFullscreen');
    
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            searchScreen.classList.add('active');
            document.body.style.overflow = 'hidden';
            const searchInput = searchScreen.querySelector('.mobile-search-input');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 300);
            }
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            searchScreen.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
}

// ═══════════════════════════════════════════════════════════
// КНОПКА "НАВЕРХ"
// ═══════════════════════════════════════════════════════════
function initScrollToTop() {
    const scrollBtn = document.getElementById('scrollToTop');
    
    if (!scrollBtn) return;
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });
    
    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ═══════════════════════════════════════════════════════════
// FLASH СООБЩЕНИЯ
// ═══════════════════════════════════════════════════════════
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(function(flash) {
        // Автоматическое скрытие через 5 секунд
        setTimeout(function() {
            flash.style.animation = 'slideOut 0.5s ease forwards';
            setTimeout(function() {
                flash.remove();
            }, 500);
        }, 5000);
    });
}

// ═══════════════════════════════════════════════════════════
// АКТИВНАЯ СТРАНИЦА В НАВИГАЦИИ
// ═══════════════════════════════════════════════════════════
function initNavigationActive() {
    const currentPath = window.location.pathname;
    
    // Desktop навигация
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(function(link) {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
    
    // Мобильное нижнее меню
    const mobileNavItems = document.querySelectorAll('.mobile-bottom-nav-item');
    mobileNavItems.forEach(function(item) {
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href)) {
            item.classList.add('active');
        }
    });
}

// ═══════════════════════════════════════════════════════════
// МОБИЛЬНОЕ НИЖНЕЕ МЕНЮ
// ═══════════════════════════════════════════════════════════
function initMobileBottomNav() {
    const bottomNav = document.querySelector('.mobile-bottom-nav');
    if (!bottomNav) return;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        const bottomNavHeight = bottomNav.offsetHeight;
        mainContent.style.paddingBottom = bottomNavHeight + 'px';
    }
}

// ═══════════════════════════════════════════════════════════
// КОРЗИНА - ДОБАВЛЕНИЕ ТОВАРА
// ═══════════════════════════════════════════════════════════
function addToCart(productId, button) {
    // Анимация кнопки
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
    button.disabled = true;
    
    fetch('/api/cart.php', {
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
            button.innerHTML = '<i class="fas fa-check"></i> Добавлено!';
            button.style.background = '#4CAF50';
            
            // Обновить счетчик корзины
            updateCartCount();
            
            // Показать уведомление
            showNotification('Товар добавлен в корзину', 'success');
            
            // Вернуть кнопку в исходное состояние через 2 секунды
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 2000);
        } else {
            showNotification(data.error || 'Ошибка при добавлении товара', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка', 'error');
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
    
    fetch('/api/favorites.php', {
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
                showNotification('Удалено из избранного', 'info');
            } else {
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Добавлено в избранное', 'success');
            }
        } else {
            showNotification(data.error || 'Ошибка', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка', 'error');
    });
}

// ═══════════════════════════════════════════════════════════
// ОБНОВЛЕНИЕ СЧЕТЧИКА КОРЗИНЫ
// ═══════════════════════════════════════════════════════════
function updateCartCount() {
    fetch('/api/cart.php?action=count')
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

// ═══════════════════════════════════════════════════════════
// УВЕДОМЛЕНИЯ
// ═══════════════════════════════════════════════════════════
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = 'flash-message flash-' + type;
    
    const iconMap = {
        success: 'check-circle',
        error: 'exclamation-circle',
        info: 'info-circle'
    };
    
    notification.innerHTML = `
        <i class="fas fa-${iconMap[type]}"></i>
        <span>${message}</span>
        <button class="flash-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматическое удаление через 5 секунд
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.5s ease forwards';
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

// ═══════════════════════════════════════════════════════════
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ═══════════════════════════════════════════════════════════

// Форматирование цены
function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
}

// Debounce функция для оптимизации
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle функция для оптимизации
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ═══════════════════════════════════════════════════════════
// LAZY LOADING ИЗОБРАЖЕНИЙ
// ═══════════════════════════════════════════════════════════
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}

// ═══════════════════════════════════════════════════════════
// ЭКСПОРТ ФУНКЦИЙ ДЛЯ ИСПОЛЬЗОВАНИЯ В HTML
// ═══════════════════════════════════════════════════════════
window.addToCart = addToCart;
window.toggleFavorite = toggleFavorite;
window.updateCartCount = updateCartCount;
window.showNotification = showNotification;
window.toggleMobileDropdown = toggleMobileDropdown;