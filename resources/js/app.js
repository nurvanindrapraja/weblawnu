import './ajax.js';
import './loading-bar.js';
import './notification.js';
import './confirm.js';
import './tag-combo.js';
import './post-preview.js';

// Alpine.js is loaded per-layout via the CDN script (defer + self-initializing),
// the same proven approach used by the public app layout. It powers the sidebar
// overlay, mobile menu, and user dropdown — no explicit Alpine.start() needed.

// Mobile menu toggle
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        document.getElementById('mobile-nav').classList.toggle('hidden');
    });
}

// TinyMCE is loaded via the CDN script in the admin layout (see admin.blade.php).
// We init it here on DOMContentLoaded, after the CDN script has self-initialized.
// The CDN build does NOT self-init editors (unlike the npm UMD build which fires
// an early init before the skin CSS is in the DOM — that was producing a
// toolbar-less editor). Here we get a clean single init.
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.tinymce === 'undefined' || typeof window.tinymce.init !== 'function') {
        console.warn('[app.js] TinyMCE global not found — CDN script may have failed to load.');
        return;
    }

    // Defensive: if anything else created an .mce-tinymce wrapper on our
    // textareas already, remove it so the init below starts clean.
    document.querySelectorAll('textarea.rich-editor').forEach((ta) => {
        const prev = ta.previousElementSibling;
        if (prev && prev.classList && prev.classList.contains('mce-tinymce')) {
            prev.remove();
        }
    });

    window.tinymce.init({
        selector: 'textarea.rich-editor',
        height: 420,
        menubar: false,
        branding: false,
        license_key: 'gpl',
        promotion: false,
        skin: 'oxide',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'table', 'help', 'preview',
        ],
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | blockquote code | alignleft aligncenter alignright | link image table | removeformat | fullscreen preview',
        content_style: 'body{font-family:system-ui,sans-serif;font-size:15px;line-height:1.6;color:#111827;padding:0 4px}',
    });
});
