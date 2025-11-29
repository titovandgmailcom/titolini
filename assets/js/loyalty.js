/**
 * ═══════════════════════════════════════════════════════════
 * LOYALTY.JS - Программа лояльности и колесо фортуны
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

const Loyalty = {
    apiUrl: window.location.origin + '/rayskiy-ugolok/api/loyalty.php',
    
    /**
     * Получить информацию о карте
     */
    getCard: function() {
        return fetch(this.apiUrl + '?action=get_card')
            .then(response => response.json())
            .catch(error => {
                console.error('Loyalty card error:', error);
                return null;
            });
    },
    
    /**
     * Получить историю транзакций
     */
    getTransactions: function(limit = 20) {
        return fetch(this.apiUrl + '?action=transactions&limit=' + limit)
            .then(response => response.json())
            .catch(error => {
                console.error('Transactions error:', error);
                return [];
            });
    },
    
    /**
     * Анимация карты при загрузке
     */
    animateCard: function() {
        const card = document.querySelector('.loyalty-card');
        if (!card) return;
        
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px) rotateX(10deg)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0) rotateX(0)';
        }, 100);
    },
    
    /**
     * Анимация прогресс бара
     */
    animateProgress: function() {
        const progressBar = document.querySelector('.progress-fill');
        if (!progressBar) return;
        
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0';
        
        setTimeout(() => {
            progressBar.style.width = targetWidth;
        }, 500);
    }
};

// ═══════════════════════════════════════════════════════════
// КОЛЕСО ФОРТУНЫ
// ═══════════════════════════════════════════════════════════

class WheelOfFortune {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        
        this.ctx = this.canvas.getContext('2d');
        this.isSpinning = false;
        this.rotation = 0;
        this.prizes = [];
        
        this.init();
    }
    
    init() {
        // Настройка размера canvas
        this.resize();
        window.addEventListener('resize', () => this.resize());
        
        // Загрузить призы
        this.loadPrizes();
    }
    
    resize() {
        const size = Math.min(this.canvas.parentElement.offsetWidth, 500);
        this.canvas.width = size;
        this.canvas.height = size;
        this.centerX = size / 2;
        this.centerY = size / 2;
        this.radius = size / 2 - 10;
        
        this.draw();
    }
    
    loadPrizes() {
        // Примерные призы (в реальности загружать с сервера)
        this.prizes = [
            { name: '50 бонусов', color: '#FF6B35', probability: 30 },
            { name: '100 бонусов', color: '#6BBF59', probability: 25 },
            { name: '200 бонусов', color: '#2196F3', probability: 15 },
            { name: 'Скидка 10%', color: '#FFD700', probability: 15 },
            { name: 'Скидка 20%', color: '#9C27B0', probability: 8 },
            { name: 'Бесплатная доставка', color: '#F44336', probability: 5 },
            { name: 'Подарок!', color: '#4CAF50', probability: 2 }
        ];
        
        this.draw();
    }
    
    draw() {
        if (!this.ctx) return;
        
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        const sliceAngle = (2 * Math.PI) / this.prizes.length;
        
        this.prizes.forEach((prize, index) => {
            const startAngle = this.rotation + index * sliceAngle;
            const endAngle = startAngle + sliceAngle;
            
            // Рисовать сектор
            this.ctx.beginPath();
            this.ctx.moveTo(this.centerX, this.centerY);
            this.ctx.arc(this.centerX, this.centerY, this.radius, startAngle, endAngle);
            this.ctx.fillStyle = prize.color;
            this.ctx.fill();
            this.ctx.strokeStyle = '#fff';
            this.ctx.lineWidth = 3;
            this.ctx.stroke();
            
            // Текст
            this.ctx.save();
            this.ctx.translate(this.centerX, this.centerY);
            this.ctx.rotate(startAngle + sliceAngle / 2);
            this.ctx.textAlign = 'center';
            this.ctx.fillStyle = '#fff';
            this.ctx.font = 'bold 14px Montserrat';
            this.ctx.fillText(prize.name, this.radius / 1.5, 0);
            this.ctx.restore();
        });
        
        // Центральная кнопка
        this.ctx.beginPath();
        this.ctx.arc(this.centerX, this.centerY, 40, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#fff';
        this.ctx.fill();
        this.ctx.strokeStyle = '#6BBF59';
        this.ctx.lineWidth = 4;
        this.ctx.stroke();
        
        this.ctx.fillStyle = '#6BBF59';
        this.ctx.font = 'bold 16px Montserrat';
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';
        this.ctx.fillText('SPIN', this.centerX, this.centerY);
    }
    
    spin() {
        if (this.isSpinning) return;
        
        this.isSpinning = true;
        
        // Случайный угол остановки
        const spins = 5 + Math.random() * 3; // 5-8 полных оборотов
        const extraRotation = Math.random() * Math.PI * 2;
        const totalRotation = spins * Math.PI * 2 + extraRotation;
        
        const duration = 4000; // 4 секунды
        const startTime = Date.now();
        const startRotation = this.rotation;
        
        const animate = () => {
            const now = Date.now();
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing функция (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            
            this.rotation = startRotation + totalRotation * easeOut;
            this.draw();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this.isSpinning = false;
                this.showPrize();
            }
        };
        
        animate();
    }
    
    showPrize() {
        // Определить выигрышный сектор
        const normalizedRotation = this.rotation % (2 * Math.PI);
        const sliceAngle = (2 * Math.PI) / this.prizes.length;
        const prizeIndex = Math.floor((2 * Math.PI - normalizedRotation) / sliceAngle) % this.prizes.length;
        const prize = this.prizes[prizeIndex];
        
        // Показать результат
        setTimeout(() => {
            alert(`Поздравляем! Вы выиграли: ${prize.name}`);
        }, 500);
    }
}

// ═══════════════════════════════════════════════════════════
// ЭФФЕКТЫ И АНИМАЦИИ
// ═══════════════════════════════════════════════════════════

/**
 * Анимация счетчика бонусов
 */
function animateCounter(element, target, duration = 1000) {
    const start = 0;
    const startTime = Date.now();
    
    const animate = () => {
        const now = Date.now();
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(start + (target - start) * progress);
        element.textContent = current.toLocaleString('ru-RU');
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };
    
    animate();
}

/**
 * Эффект конфетти при получении бонусов
 */
function showConfetti() {
    // Простой эффект конфетти
    const colors = ['#6BBF59', '#FF6B35', '#FFD700', '#2196F3'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'fixed';
        confetti.style.width = '10px';
        confetti.style.height = '10px';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.left = Math.random() * window.innerWidth + 'px';
        confetti.style.top = '-10px';
        confetti.style.opacity = '1';
        confetti.style.pointerEvents = 'none';
        confetti.style.zIndex = '10000';
        confetti.style.borderRadius = '50%';
        
        document.body.appendChild(confetti);
        
        const duration = 2000 + Math.random() * 1000;
        const startTime = Date.now();
        const startX = parseFloat(confetti.style.left);
        const endX = startX + (Math.random() - 0.5) * 200;
        
        const animate = () => {
            const now = Date.now();
            const elapsed = now - startTime;
            const progress = elapsed / duration;
            
            if (progress < 1) {
                confetti.style.top = progress * window.innerHeight + 'px';
                confetti.style.left = startX + (endX - startX) * progress + 'px';
                confetti.style.opacity = 1 - progress;
                confetti.style.transform = `rotate(${progress * 360}deg)`;
                requestAnimationFrame(animate);
            } else {
                confetti.remove();
            }
        };
        
        animate();
    }
}

// ═══════════════════════════════════════════════════════════
// ИНИЦИАЛИЗАЦИЯ
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    // Анимация карты лояльности
    Loyalty.animateCard();
    
    // Анимация прогресс бара
    Loyalty.animateProgress();
    
    // Анимация счетчиков
    const balanceElement = document.querySelector('.card-balance-amount');
    if (balanceElement) {
        const balance = parseInt(balanceElement.textContent.replace(/\s/g, ''));
        animateCounter(balanceElement, balance, 1500);
    }
    
    // Инициализация колеса фортуны
    const wheelCanvas = document.getElementById('wheelCanvas');
    if (wheelCanvas) {
        window.wheel = new WheelOfFortune('wheelCanvas');
        
        // Клик по canvas для вращения
        wheelCanvas.addEventListener('click', function(e) {
            const rect = wheelCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const dx = x - window.wheel.centerX;
            const dy = y - window.wheel.centerY;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            // Проверить клик по центральной кнопке
            if (distance < 40) {
                window.wheel.spin();
            }
        });
    }
    
    // Анимация появления транзакций
    const transactions = document.querySelectorAll('.transaction-item');
    transactions.forEach((transaction, index) => {
        transaction.style.opacity = '0';
        transaction.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            transaction.style.transition = 'all 0.3s ease';
            transaction.style.opacity = '1';
            transaction.style.transform = 'translateX(0)';
        }, index * 50);
    });
});

// ═══════════════════════════════════════════════════════════
// ЭКСПОРТ
// ═══════════════════════════════════════════════════════════
window.Loyalty = Loyalty;
window.WheelOfFortune = WheelOfFortune;
window.animateCounter = animateCounter;
window.showConfetti = showConfetti;