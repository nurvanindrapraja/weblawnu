// Global loading bar (top of page)
(function() {
    let bar = null;
    let progress = 0;
    let timer = null;

    function ensureBar() {
        if (bar) return bar;
        bar = document.createElement('div');
        bar.id = 'loading-bar';
        bar.style.cssText = `
            position: fixed; top: 0; left: 0; height: 3px; width: 0;
            background: linear-gradient(90deg, #10b981, #34d399);
            z-index: 9999; transition: width 0.3s ease;
            box-shadow: 0 0 10px rgba(16,185,129,0.5);
        `;
        document.body.prepend(bar);
        return bar;
    }

    window.startLoading = function() {
        const el = ensureBar();
        progress = 0;
        el.style.width = '0';
        el.style.display = 'block';

        if (timer) clearInterval(timer);
        timer = setInterval(() => {
            progress = Math.min(progress + Math.random() * 15, 90);
            el.style.width = progress + '%';
        }, 150);
    };

    window.stopLoading = function() {
        if (timer) { clearInterval(timer); timer = null; }
        const el = ensureBar();
        el.style.width = '100%';
        setTimeout(() => {
            el.style.display = 'none';
            progress = 0;
        }, 300);
    };
})();
