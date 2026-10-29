// Post preview modal: shows the public article look in an in-page iframe
// modal, so editors never leave the current page. The modal markup lives in
// resources/views/components/post-preview-modal.blade.php.
(function () {
    function el(id) {
        return document.getElementById(id);
    }

    function show() {
        el('post-preview-backdrop')?.classList.remove('hidden');
        const modal = el('post-preview-modal');
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
        el('post-preview-loading')?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Called from the iframe's onload attribute.
    window.postPreviewFrameLoaded = function () {
        el('post-preview-loading')?.classList.add('hidden');
    };

    /**
     * Open a GET-based preview (existing post) in the in-page modal.
     */
    window.openPostPreviewGet = function (url) {
        if (!el('post-preview-modal')) return;
        show();

        const tabLink = el('post-preview-open-tab');
        if (tabLink) {
            tabLink.href = url;
            tabLink.classList.remove('hidden');
        }

        // Cancel any pending form-driven navigation first.
        el('post-preview-form')?.reset();

        el('post-preview-frame').src = url;
    };

    /**
     * Open a POST-based preview (unsaved create-form data) in the in-page modal.
     *
     * @param {string} action Form action URL
     * @param {Object} fields Field name => value (array values become repeated `name[]` fields)
     */
    window.openPostPreviewPost = function (action, fields) {
        if (!el('post-preview-modal')) return;
        show();

        el('post-preview-open-tab')?.classList.add('hidden');

        const form = el('post-preview-form');
        while (form.firstChild) form.removeChild(form.firstChild);
        form.action = action;
        form.method = 'POST';

        Object.entries(fields).forEach(([name, value]) => {
            const values = Array.isArray(value) ? value : [value];
            values.forEach((v) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = Array.isArray(value) ? name + '[]' : name;
                input.value = v ?? '';
                form.appendChild(input);
            });
        });

        form.submit();
    };

    window.closePostPreview = function () {
        if (!el('post-preview-modal')) return;
        el('post-preview-backdrop')?.classList.add('hidden');
        const modal = el('post-preview-modal');
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        el('post-preview-frame').src = 'about:blank';
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') window.closePostPreview();
    });
})();
