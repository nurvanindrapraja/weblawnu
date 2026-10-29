// Shared confirmation modal — replaces browser confirm()/prompt() dialogs
// with a styled Tailwind view. Mirrors the toast approach in notification.js:
// the markup is injected once on first use, then reused.
(function() {
    let overlay = null;
    let pendingResolve = null;
    let inputEnabled = false;

    function buildOverlay() {
        overlay = document.createElement('div');
        overlay.id = 'confirm-modal';
        overlay.className = 'fixed inset-0 z-[10001] items-center justify-center p-4 hidden';
        overlay.innerHTML = `
            <div id="confirm-backdrop" class="absolute inset-0 bg-black/40"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden" role="dialog" aria-modal="true">
                <div class="px-6 pt-6 pb-4">
                    <h3 id="confirm-title" class="text-base font-semibold text-gray-900"></h3>
                    <p id="confirm-message" class="mt-2 text-sm text-gray-600 leading-relaxed"></p>
                    <div id="confirm-input-wrap" class="mt-4 hidden">
                        <input type="text" id="confirm-input" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button type="button" id="confirm-cancel" class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Batal</button>
                    <button type="button" id="confirm-ok" class="px-4 py-2 rounded-lg text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition">OK</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);

        const close = (result) => {
            if (pendingResolve) pendingResolve(result);
            pendingResolve = null;
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        };
        overlay.querySelector('#confirm-ok').addEventListener('click', () => {
            close(inputEnabled ? overlay.querySelector('#confirm-input').value : true);
        });
        overlay.querySelector('#confirm-cancel').addEventListener('click', () => close(false));
        overlay.querySelector('#confirm-backdrop').addEventListener('click', () => close(false));
        document.addEventListener('keydown', (e) => {
            if (overlay.classList.contains('hidden')) return;
            if (e.key === 'Escape') close(false);
            if (e.key === 'Enter' && inputEnabled) {
                e.preventDefault();
                close(overlay.querySelector('#confirm-input').value);
            }
        });
    }

    /**
     * Show a confirmation modal and resolve when the user chooses.
     *
     * @param {string} message  Text shown in the modal body.
     * @param {object} [options]
     * @param {string} [options.title='Konfirmasi']
     * @param {string} [options.okLabel='OK']
     * @param {string} [options.cancelLabel='Batal']
     * @param {string} [options.okClass]    Tailwind classes for the OK button (e.g. red for delete)
     * @param {boolean} [options.input=false]  Show a text input (prompt-style).
     * @param {string}  [options.inputPlaceholder]
     * @returns {Promise<boolean|string>}  true on OK without input, the entered string with input, false on cancel/Escape.
     */
    window.confirmModal = function(message, options = {}) {
        if (!overlay) buildOverlay();

        overlay.querySelector('#confirm-title').textContent = options.title || 'Konfirmasi';
        overlay.querySelector('#confirm-message').textContent = message;

        const inputWrap = overlay.querySelector('#confirm-input-wrap');
        const input = overlay.querySelector('#confirm-input');
        inputEnabled = !!options.input;
        inputWrap.classList.toggle('hidden', !inputEnabled);
        if (inputEnabled) {
            input.placeholder = options.inputPlaceholder || '';
            input.value = '';
        }

        const okBtn = overlay.querySelector('#confirm-ok');
        okBtn.textContent = options.okLabel || 'OK';
        if (options.okClass) {
            okBtn.className = okBtn.className.replace(/bg-\S+ hover:bg-\S+/g, '').trim();
            okBtn.className = (okBtn.className + ' ' + options.okClass).trim();
        } else {
            okBtn.className = 'px-4 py-2 rounded-lg text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition';
        }
        overlay.querySelector('#confirm-cancel').textContent = options.cancelLabel || 'Batal';

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        if (inputEnabled) setTimeout(() => input.focus(), 50);

        return new Promise((resolve) => { pendingResolve = resolve; });
    };
})();
