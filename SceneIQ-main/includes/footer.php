</main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>SceneIQ</h4>
                <p>Descubre tu próxima obsesión cinematográfica</p>
            </div>
            
            <div class="footer-section">
                <h4>Enlaces</h4>
                <ul>
                    <li><a href="../index.php">Inicio</a></li>
                    <li><a href="../pages/movies.php">Películas</a></li>
                    <li><a href="../pages/series.php">Series</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Soporte</h4>
                <ul>
                    <li><a href="../pages/contact.php">Contacto</a></li>
                    <li><a href="../pages/help.php">Ayuda</a></li>
                    <li><a href="../pages/terms.php">Términos</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 SceneIQ. Todos los derechos reservados.</p>
        </div>
    </footer>

    <style>
        :root {
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --border-radius: 12px;
            --border-radius-small: 6px;
            --border-radius-large: 20px;
            --transition: all 0.3s ease;
            --accent: #ff6b6b;
            --success: #00d2ff;
            --error: #ff6b6b;
        }

        .footer {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: var(--spacing-xl);
            padding: var(--spacing-xl) 0 var(--spacing-md);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
        }

        .footer-section h4 {
            color: var(--text-primary);
            margin-bottom: var(--spacing-md);
            font-size: 1.1rem;
        }

        .footer-section p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: var(--spacing-sm);
        }

        .footer-section ul li a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-section ul li a:hover {
            color: var(--accent);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-md);
            text-align: center;
        }

        .footer-bottom p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        /* Estilos para botones */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-small);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--glass-bg);
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
        }
    </style>

</body>
</html>