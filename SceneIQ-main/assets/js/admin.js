// SceneIQ Admin Panel JavaScript
class AdminPanel {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.loadDashboardData();
    }

    init() {
        console.log('🛠️ Admin Panel initialized');
        this.charts = {};
        this.realTimeUpdates = true;
        this.updateInterval = 30000; // 30 segundos
        this.setupRealTimeUpdates();
    }

    setupEventListeners() {
        // Bulk actions
        this.setupBulkActions();
        
        // Modal handlers
        this.setupModals();
        
        // Table interactions
        this.setupTableInteractions();
        
        // Quick actions
        this.setupQuickActions();
        
        // Search and filters
        this.setupSearchAndFilters();
    }

    // ================================
    // BULK ACTIONS SYSTEM
    // ================================
    setupBulkActions() {
        // Select all functionality
        const selectAllCheckboxes = document.querySelectorAll('#selectAll');
        selectAllCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.toggleSelectAll(e.target);
            });
        });

        // Individual checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.matches('.bulk-checkbox')) {
                this.updateBulkActionsVisibility();
            }
        });

        // Bulk action buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-bulk-action]')) {
                const action = e.target.dataset.bulkAction;
                this.executeBulkAction(action);
            }
        });
    }

    toggleSelectAll(selectAllCheckbox) {
        const targetCheckboxes = selectAllCheckbox.dataset.target || '.bulk-checkbox';
        const checkboxes = document.querySelectorAll(targetCheckboxes);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        this.updateBulkActionsVisibility();
    }

    updateBulkActionsVisibility() {
        const selectedItems = document.querySelectorAll('.bulk-checkbox:checked');
        const bulkActionsPanel = document.getElementById('bulkActionsPanel');
        const selectedCount = document.getElementById('selectedCount');
        
        if (bulkActionsPanel) {
            if (selectedItems.length > 0) {
                bulkActionsPanel.style.display = 'flex';
                if (selectedCount) {
                    selectedCount.textContent = selectedItems.length;
                }
            } else {
                bulkActionsPanel.style.display = 'none';
            }
        }
    }

    async executeBulkAction(action) {
        const selectedItems = Array.from(document.querySelectorAll('.bulk-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedItems.length === 0) {
            this.showNotification('No hay elementos seleccionados', 'warning');
            return;
        }

        const confirmation = await this.showConfirmDialog(
            `¿Estás seguro de que quieres ${action} ${selectedItems.length} elemento(s)?`
        );

        if (!confirmation) return;

        try {
            this.showLoading(`Ejecutando ${action}...`);
            
            const response = await fetch('api/bulk-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.sceneIQConfig?.csrfToken
                },
                body: JSON.stringify({
                    action: action,
                    items: selectedItems,
                    csrf_token: window.sceneIQConfig?.csrfToken
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(`${action} ejecutado exitosamente`, 'success');
                this.refreshCurrentView();
            } else {
                this.showNotification(result.message || 'Error en la operación', 'error');
            }
        } catch (error) {
            console.error('Bulk action error:', error);
            this.showNotification('Error en la conexión', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // ================================
    // MODAL SYSTEM
    // ================================
    setupModals() {
        // Close modal on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Modal close buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-close')) {
                const modal = e.target.closest('.modal');
                if (modal) this.closeModal(modal.id);
            }
        });
    }

    openModal(modalId, data = null) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (data) {
            this.populateModalData(modal, data);
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), textarea, select');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal.active');
        modals.forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    populateModalData(modal, data) {
        Object.keys(data).forEach(key => {
            const input = modal.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = Boolean(data[key]);
                } else {
                    input.value = data[key] || '';
                }
            }
        });
    }

    // ================================
    // TABLE INTERACTIONS
    // ================================
    setupTableInteractions() {
        // Sortable columns
        document.addEventListener('click', (e) => {
            if (e.target.matches('.sortable-header')) {
                this.sortTable(e.target);
            }
        });

        // Row actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action]')) {
                const action = e.target.dataset.action;
                const itemId = e.target.dataset.id;
                this.executeRowAction(action, itemId, e.target);
            }
        });

        // Quick edit inline
        document.addEventListener('dblclick', (e) => {
            if (e.target.matches('.editable-cell')) {
                this.enableInlineEdit(e.target);
            }
        });
    }

    sortTable(headerElement) {
        const table = headerElement.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const column = Array.from(headerElement.parentNode.children).indexOf(headerElement);
        const currentSort = headerElement.dataset.sort || 'asc';
        const newSort = currentSort === 'asc' ? 'desc' : 'asc';

        // Remove sort indicators from other headers
        table.querySelectorAll('.sortable-header').forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
            delete header.dataset.sort;
        });

        // Add sort indicator to current header
        headerElement.classList.add(`sort-${newSort}`);
        headerElement.dataset.sort = newSort;

        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.children[column].textContent.trim();
            const bValue = b.children[column].textContent.trim();
            
            // Try to parse as numbers
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return newSort === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // Sort as strings
            return newSort === 'asc' 
                ? aValue.localeCompare(bValue)
                : bValue.localeCompare(aValue);
        });

        // Reappend sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }

    async executeRowAction(action, itemId, element) {
        try {
            switch (action) {
                case 'edit':
                    await this.editItem(itemId, element);
                    break;
                case 'delete':
                    await this.deleteItem(itemId, element);
                    break;
                case 'toggle-status':
                    await this.toggleItemStatus(itemId, element);
                    break;
                case 'view':
                    this.viewItem(itemId);
                    break;
                default:
                    console.warn('Unknown action:', action);
            }
        } catch (error) {
            console.error('Row action error:', error);
            this.showNotification('Error ejecutando la acción', 'error');
        }
    }

    async deleteItem(itemId, element) {
        const confirmation = await this.showConfirmDialog(
            '¿Estás seguro de que quieres eliminar este elemento?'
        );
        
        if (!confirmation) return;

        try {
            const response = await fetch(`api/delete-item.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.sceneIQConfig?.csrfToken
                },
                body: JSON.stringify({
                    id: itemId,
                    csrf_token: window.sceneIQConfig?.csrfToken
                })
            });

            const result = await response.json();

            if (result.success) {
                // Remove row with animation
                const row = element.closest('tr');
                row.style.opacity = '0.5';
                row.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    row.remove();
                    this.updateTableStats();
                }, 300);

                this.showNotification('Elemento eliminado exitosamente', 'success');
            } else {
                this.showNotification(result.message || 'Error al eliminar', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification('Error en la conexión', 'error');
        }
    }

    enableInlineEdit(cell) {
        const originalValue = cell.textContent;
        const input = document.createElement('input');
        input.value = originalValue;
        input.className = 'inline-edit-input';
        
        cell.innerHTML = '';
        cell.appendChild(input);
        input.focus();
        input.select();

        const saveEdit = async () => {
            const newValue = input.value;
            if (newValue !== originalValue) {
                try {
                    // Save to server
                    const success = await this.saveInlineEdit(cell, newValue);
                    if (success) {
                        cell.textContent = newValue;
                        this.showNotification('Actualizado', 'success');
                    } else {
                        cell.textContent = originalValue;
                    }
                } catch (error) {
                    cell.textContent = originalValue;
                    this.showNotification('Error al guardar', 'error');
                }
            } else {
                cell.textContent = originalValue;
            }
        };

        input.addEventListener('blur', saveEdit);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                saveEdit();
            } else if (e.key === 'Escape') {
                cell.textContent = originalValue;
            }
        });
    }

    // ================================
    // QUICK ACTIONS
    // ================================
    setupQuickActions() {
        // Quick add buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-quick-action]')) {
                const action = e.target.dataset.quickAction;
                this.executeQuickAction(action);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'n':
                        e.preventDefault();
                        this.executeQuickAction('new');
                        break;
                    case 's':
                        e.preventDefault();
                        this.executeQuickAction('save');
                        break;
                    case 'f':
                        e.preventDefault();
                        this.focusSearch();
                        break;
                }
            }
        });
    }

    executeQuickAction(action) {
        switch (action) {
            case 'new':
                this.openModal('addItemModal');
                break;
            case 'export':
                this.exportData();
                break;
            case 'import':
                this.openModal('importModal');
                break;
            case 'backup':
                this.createBackup();
                break;
            case 'refresh':
                this.refreshCurrentView();
                break;
            default:
                console.warn('Unknown quick action:', action);
        }
    }

    // ================================
    // SEARCH AND FILTERS
    // ================================
    setupSearchAndFilters() {
        // Real-time search
        const searchInputs = document.querySelectorAll('.admin-search');
        searchInputs.forEach(input => {
            let searchTimeout;
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        });

        // Filter changes
        const filterSelects = document.querySelectorAll('.admin-filter');
        filterSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                this.applyFilters();
            });
        });

        // Date range filters
        const dateInputs = document.querySelectorAll('.date-filter');
        dateInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.applyFilters();
            });
        });
    }

    performSearch(query) {
        const rows = document.querySelectorAll('.searchable-row');
        const normalizedQuery = query.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isMatch = text.includes(normalizedQuery);
            
            row.style.display = isMatch ? '' : 'none';
            
            if (isMatch && query) {
                this.highlightSearchTerms(row, query);
            } else {
                this.removeHighlights(row);
            }
        });

        this.updateSearchResults(query);
    }

    highlightSearchTerms(row, query) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            if (cell.querySelector('.highlight')) return; // Already highlighted
            
            const text = cell.textContent;
            const regex = new RegExp(`(${query})`, 'gi');
            const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
            
            if (highlightedText !== text) {
                cell.innerHTML = highlightedText;
            }
        });
    }

    removeHighlights(row) {
        const highlights = row.querySelectorAll('.highlight');
        highlights.forEach(highlight => {
            highlight.outerHTML = highlight.textContent;
        });
    }

    // ================================
    // REAL-TIME UPDATES
    // ================================
    setupRealTimeUpdates() {
        if (this.realTimeUpdates) {
            setInterval(() => {
                this.updateDashboardStats();
            }, this.updateInterval);
        }
    }

    async updateDashboardStats() {
        try {
            const response = await fetch('api/dashboard-stats.php');
            const stats = await response.json();
            
            if (stats.success) {
                this.updateStatCards(stats.data);
                this.updateActivityFeed(stats.activity);
            }
        } catch (error) {
            console.error('Stats update error:', error);
        }
    }

    updateStatCards(stats) {
        Object.keys(stats).forEach(key => {
            const statElement = document.querySelector(`[data-stat="${key}"]`);
            if (statElement) {
                const currentValue = parseInt(statElement.textContent.replace(/,/g, ''));
                const newValue = stats[key];
                
                if (currentValue !== newValue) {
                    this.animateStatChange(statElement, currentValue, newValue);
                }
            }
        });
    }

    animateStatChange(element, from, to) {
        const duration = 1000;
        const startTime = Date.now();
        
        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.round(from + (to - from) * progress);
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    // ================================
    // UTILITY FUNCTIONS
    // ================================
    async showConfirmDialog(message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal active';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h3>Confirmar Acción</h3>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" onclick="this.closest('.modal').remove(); window.adminConfirmResolve(false);">Cancelar</button>
                        <button class="btn btn-danger" onclick="this.closest('.modal').remove(); window.adminConfirmResolve(true);">Confirmar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            window.adminConfirmResolve = (result) => {
                document.body.style.overflow = '';
                resolve(result);
                delete window.adminConfirmResolve;
            };
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `admin-notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${this.getNotificationIcon(type)}</span>
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        // Add styles if not present
        if (!document.querySelector('#admin-notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'admin-notification-styles';
            styles.textContent = `
                .admin-notification {
                    position: fixed;
                    top: 100px;
                    right: 20px;
                    z-index: 1001;
                    max-width: 400px;
                    background: var(--card-bg);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: var(--border-radius);
                    box-shadow: var(--shadow);
                    animation: slideInRight 0.3s ease;
                }
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: var(--spacing-sm);
                    padding: var(--spacing-md);
                }
                .notification-icon {
                    font-size: 1.2rem;
                    flex-shrink: 0;
                }
                .notification-message {
                    flex: 1;
                    color: var(--text-primary);
                }
                .notification-close {
                    background: none;
                    border: none;
                    color: var(--text-secondary);
                    cursor: pointer;
                    font-size: 1.2rem;
                    padding: 0.25rem;
                }
                .notification-success { border-left: 4px solid var(--success); }
                .notification-error { border-left: 4px solid var(--error); }
                .notification-warning { border-left: 4px solid var(--warning); }
                .notification-info { border-left: 4px solid var(--accent); }
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(styles);
        }
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || 'ℹ️';
    }

    showLoading(message = 'Cargando...') {
        const loader = document.createElement('div');
        loader.id = 'admin-loader';
        loader.className = 'admin-loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-spinner"></div>
                <div class="loader-message">${message}</div>
            </div>
        `;
        
        // Add loader styles if not present
        if (!document.querySelector('#admin-loader-styles')) {
            const styles = document.createElement('style');
            styles.id = 'admin-loader-styles';
            styles.textContent = `
                .admin-loader {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 2000;
                }
                .loader-content {
                    background: var(--card-bg);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: var(--border-radius);
                    padding: var(--spacing-xl);
                    text-align: center;
                    min-width: 200px;
                }
                .loader-spinner {
                    width: 40px;
                    height: 40px;
                    border: 4px solid rgba(255, 255, 255, 0.3);
                    border-top: 4px solid var(--accent);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto var(--spacing-md);
                }
                .loader-message {
                    color: var(--text-primary);
                    font-size: 1rem;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(styles);
        }
        
        document.body.appendChild(loader);
        document.body.style.overflow = 'hidden';
    }

    hideLoading() {
        const loader = document.getElementById('admin-loader');
        if (loader) {
            loader.remove();
            document.body.style.overflow = '';
        }
    }

    async exportData(format = 'csv') {
        try {
            this.showLoading('Preparando exportación...');
            
            const response = await fetch(`api/export.php?format=${format}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': window.sceneIQConfig?.csrfToken
                }
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `sceneiq-export-${new Date().toISOString().split('T')[0]}.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                this.showNotification('Exportación completada', 'success');
            } else {
                throw new Error('Export failed');
            }
        } catch (error) {
            console.error('Export error:', error);
            this.showNotification('Error en la exportación', 'error');
        } finally {
            this.hideLoading();
        }
    }

    refreshCurrentView() {
        window.location.reload();
    }

    focusSearch() {
        const searchInput = document.querySelector('.admin-search');
        if (searchInput) {
            searchInput.focus();
        }
    }

    loadDashboardData() {
        // Load initial dashboard data
        this.updateDashboardStats();
    }
}

// Initialize admin panel when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('/admin/')) {
        window.adminPanel = new AdminPanel();
    }
});

// Export for global access
window.AdminPanel = AdminPanel;