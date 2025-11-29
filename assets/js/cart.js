/**
 * ═══════════════════════════════════════════════════════════
 * CART.JS - Функции корзины
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

const Cart = {
    apiUrl: window.location.origin + '/rayskiy-ugolok/api/cart.php',
    
    /**
     * Добавить товар в корзину
     */
    add: function(productId, quantity = 1, callback) {
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCount();
                if (callback) callback(data);
            }
            return data;
        })
        .catch(error => {
            console.error('Cart add error:', error);
            return { success: false, error: error.message };
        });
    },
    
    /**
     * Обновить количество товара
     */
    update: function(productId, quantity) {
        if (quantity < 1) {
            return this.remove(productId);
        }
        
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCount();
                this.updateTotal(data.total);
            }
            return data;
        })
        .catch(error => {
            console.error('Cart update error:', error);
            return { success: false, error: error.message };
        });
    },
    
    /**
     * Удалить товар из корзины
     */
    remove: function(productId) {
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCount();
                this.updateTotal(data.total);
                
                // Удалить элемент из DOM
                const itemElement = document.querySelector(`[data-product-id="${productId}"]`);
                if (itemElement) {
                    itemElement.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        itemElement.remove();
                        this.checkEmpty();
                    }, 300);
                }
            }
            return data;
        })
        .catch(error => {
            console.error('Cart remove error:', error);
            return { success: false, error: error.message };
        });
    },
    
    /**
     * Очистить корзину
     */
    clear: function() {
        if (!confirm('Очистить всю корзину?')) {
            return Promise.resolve({ success: false });
        }
        
        return fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateCount();
                location.reload();
            }
            return data;
        })
        .catch(error => {
            console.error('Cart clear error:', error);
            return { success: false, error: error.message };
        });
    },
    
    /**
     * Получить количество товаров
     */
    getCount: function() {
        return fetch(this.apiUrl + '?action=count')
            .then(response => response.json())
            .then(data => data.count || 0)
            .catch(error => {
                console.error('Cart count error:', error);
                return 0;
            });
    },
    
    /**
     * Получить содержимое корзины
     */
    getItems: function() {
        return fetch(this.apiUrl)
            .then(response => response.json())
            .then(data => data.items || [])
            .catch(error => {
                console.error('Cart items error:', error);
                return [];
            });
    },
    
    /**
     * Обновить счетчик корзины
     */
    updateCount: function() {
        this.getCount().then(count => {
            const counters = document.querySelectorAll('.cart-count, .mobile-nav-badge');
            counters.forEach(counter => {
                if (count > 0) {
                    counter.textContent = count;
                    counter.style.display = 'flex';
                } else {
                    counter.style.display = 'none';
                }
            });
        });
    },
    
    /**
     * Обновить итоговую сумму
     */
    updateTotal: function(total) {
        const totalElements = document.querySelectorAll('.cart-total, .summary-value.total');
        totalElements.forEach(element => {
            element.textContent = this.formatPrice(total);
        });
    },
    
    /**
     * Проверить пустую корзину
     */
    checkEmpty: function() {
        const cartItems = document.querySelectorAll('.cart-item');
        if (cartItems.length === 0) {
            location.reload();
        }
    },
    
    /**
     * Форматирование цены
     */
    formatPrice: function(price) {
        return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
    }
};

// ═══════════════════════════════════════════════════════════
// ФУНКЦИИ ДЛЯ СТРАНИЦЫ КОРЗИНЫ
// ═══════════════════════════════════════════════════════════

/**
 * Изменить количество товара
 */
function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) {
        removeItem(productId);
        return;
    }
    
    Cart.update(productId, newQuantity)
        .then(data => {
            if (data.success) {
                // Обновить отображение количества
                const qtyElement = document.querySelector(`[data-product-id="${productId}"] .quantity-value`);
                if (qtyElement) {
                    qtyElement.textContent = newQuantity;
                }
                
                // Обновить цену товара
                const itemTotal = document.querySelector(`[data-product-id="${productId}"] .item-price`);
                if (itemTotal && data.total) {
                    // Пересчитать на основе нового количества
                    location.reload(); // Проще перезагрузить страницу
                }
            } else {
                alert(data.error || 'Ошибка при обновлении количества');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка');
        });
}

/**
 * Удалить товар из корзины
 */
function removeItem(productId) {
    if (!confirm('Удалить товар из корзины?')) {
        return;
    }
    
    Cart.remove(productId)
        .then(data => {
            if (!data.success) {
                alert(data.error || 'Ошибка при удалении товара');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка');
        });
}

/**
 * Быстрое добавление в корзину (с кнопки)
 */
function quickAddToCart(productId, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    Cart.add(productId, 1)
        .then(data => {
            if (data.success) {
                button.innerHTML = '<i class="fas fa-check"></i> Добавлено';
                button.style.background = '#4CAF50';
                
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
        });
}

// ═══════════════════════════════════════════════════════════
// ИНИЦИАЛИЗАЦИЯ
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    // Обновить счетчик корзины при загрузке
    Cart.updateCount();
    
    // Анимация при загрузке страницы корзины
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 50);
    });
});

// ═══════════════════════════════════════════════════════════
// ЭКСПОРТ
// ═══════════════════════════════════════════════════════════
window.Cart = Cart;
window.updateQuantity = updateQuantity;
window.removeItem = removeItem;
window.quickAddToCart = quickAddToCart;