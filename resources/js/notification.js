// Toast notification system
(function() {
    function getContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;display:flex;flex-direction:column;gap:8px;';
            document.body.appendChild(container);
        }
        return container;
    }

    window.showToast = function(message, type = 'info', duration = 3000) {
        const container = getContainer();
        const toast = document.createElement('div');

        const colors = {
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
            info: 'bg-blue-50 border-blue-200 text-blue-800'
        };

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        toast.className = `px-4 py-3 rounded-lg border shadow-lg transform translate-x-full transition-all duration-300 ${colors[type] || colors.info}`;
        toast.style.minWidth = '280px';
        toast.style.maxWidth = '400px';
        toast.innerHTML = `<div class="flex items-start gap-3">
            <span class="text-lg font-bold shrink-0">${icons[type] || icons.info}</span>
            <p class="text-sm flex-1">${message}</p>
            <button onclick="this.parentElement.remove()" class="text-xs opacity-50 hover:opacity-100 shrink-0">✕</button>
        </div>`;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
})();
