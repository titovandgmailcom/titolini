</main>
    <!-- Main Content End -->
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>О компании</h3>
                    <p class="footer-description">
                        Райский уголок - это свежие и качественные продукты с доставкой на дом. 
                        Мы работаем только с проверенными поставщиками и гарантируем качество каждого товара.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="VK">
                            <i class="fab fa-vk"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Telegram">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Покупателям</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>/about.php">О компании</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/delivery.php">Доставка и оплата</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/return.php">Возврат товара</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/loyalty.php">Программа лояльности</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq.php">Частые вопросы</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Каталог</h3>
                    <ul class="footer-links">
                        <?php 
                        $footer_categories = array_slice($categories, 0, 6);
                        foreach ($footer_categories as $category): 
                        ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/catalog.php?category=<?php echo e($category['slug']); ?>">
                                <?php echo e($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Контакты</h3>
                    <ul class="footer-contacts">
                        <li>
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Горячая линия:</strong><br>
                                <a href="tel:+78005553535">+7 (800) 555-35-35</a>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong><br>
                                <a href="mailto:info@rayskiy-ugolok.ru">info@rayskiy-ugolok.ru</a>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Адрес:</strong><br>
                                г. Москва, ул. Ленина, д. 1
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Режим работы:</strong><br>
                                Ежедневно с 8:00 до 23:00
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-left">
                    <p>&copy; <?php echo date('Y'); ?> Райский уголок. Все права защищены.</p>
                    <div class="footer-legal">
                        <a href="<?php echo SITE_URL; ?>/privacy.php">Политика конфиденциальности</a>
                        <span class="separator">|</span>
                        <a href="<?php echo SITE_URL; ?>/terms.php">Пользовательское соглашение</a>
                    </div>
                </div>
                <div class="footer-bottom-right">
                    <div class="footer-payments">
                        <span>Принимаем к оплате:</span>
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-mir"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Кнопка "Наверх" -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="Наверх">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <script>
    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Гамбургер меню
        const openMobileMenu = document.getElementById('openMobileMenu');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        
        if (openMobileMenu) {
            openMobileMenu.addEventListener('click', function() {
                mobileMenu.classList.add('active');
                mobileMenuOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
        
        if (closeMobileMenu) {
            closeMobileMenu.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                mobileMenuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                mobileMenuOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Мобильный поиск
        const openMobileSearch = document.getElementById('openMobileSearch');
        const closeMobileSearch = document.getElementById('closeMobileSearch');
        const mobileSearchFullscreen = document.getElementById('mobileSearchFullscreen');
        
        if (openMobileSearch) {
            openMobileSearch.addEventListener('click', function() {
                mobileSearchFullscreen.classList.add('active');
                document.body.style.overflow = 'hidden';
                const searchInput = mobileSearchFullscreen.querySelector('.mobile-search-input');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 300);
                }
            });
        }
        
        if (closeMobileSearch) {
            closeMobileSearch.addEventListener('click', function() {
                mobileSearchFullscreen.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Кнопка "Наверх"
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        if (scrollToTopBtn) {
            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Автоскрытие flash сообщений
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(function(flash) {
            setTimeout(function() {
                flash.style.animation = 'slideOut 0.5s ease forwards';
                setTimeout(function() {
                    flash.remove();
                }, 500);
            }, 5000);
        });
        
        // Активация текущей страницы в навигации
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
        
        // Активация в нижнем меню
        const mobileNavItems = document.querySelectorAll('.mobile-bottom-nav-item');
        mobileNavItems.forEach(function(item) {
            if (item.getAttribute('href') === currentPath) {
                item.classList.add('active');
            }
        });
    });
    
    // Функция для переключения dropdown в мобильном меню
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
    </script>
</body>
</html>
<?php
// ВАЖНО: Отправить буфер вывода в конце
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>