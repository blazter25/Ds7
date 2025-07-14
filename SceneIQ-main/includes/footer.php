</main>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SceneIQ</h3>
                    <p>La plataforma definitiva para descubrir tu próxima obsesión cinematográfica.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter">🐦</a>
                        <a href="#" aria-label="Instagram">📷</a>
                        <a href="#" aria-label="YouTube">📺</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Explorar</h4>
                    <ul>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/movies.php">Películas</a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/series.php">Series</a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/trending.php">Tendencias</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Comunidad</h4>
                    <ul>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/reviews.php">Reseñas</a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/help.php">Ayuda</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/privacy.php">Privacidad</a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/terms.php">Términos</a></li>
                        <li><a href="<?php echo $base_path ?? ''; ?>pages/contact.php">Contacto</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> SceneIQ. Todos los derechos reservados.</p>
                <p>Hecho con ❤️ para los amantes del cine y las series.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <?php if ($user ?? false): ?>
        <button class="fab" onclick="alert('Función próximamente')" title="Escribir Reseña">✏️</button>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script>
        // Funciones básicas ya incluidas en header
        console.log('🎬 SceneIQ cargado correctamente');
    </script>
</body>
</html>