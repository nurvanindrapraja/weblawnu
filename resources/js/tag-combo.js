// Shared tag combobox — a searchable multi-select for choosing tags on the
// news create/edit pages. Replaces the old plain checkbox list so it scales
// as the tag count grows: type to filter, click to pick, chips show what's
// selected. New tags can still be created inline via the "Tambah Tag baru"
// button (window.openTagModal / saveTag, defined on each page).
(function() {
    window.initTagCombo = function(root) {
        const input = root.querySelector('#tag-search');
        const dropdown = root.querySelector('#tag-dropdown');
        const list = root.querySelector('#tag-options');
        const empty = root.querySelector('#tag-empty');
        const chips = root.querySelector('#tag-chips');

        const raw = root.dataset.tags;
        console.debug('[tag-combo] init, raw data-tags =', raw);
        let allTags = [];
        if (raw) {
            try { allTags = JSON.parse(raw); }
            catch (err) { console.error('[tag-combo] JSON.parse failed:', err, 'raw =', raw); }
        }
        if (!Array.isArray(allTags)) allTags = [];
        const selected = new Map(); // id -> {id, name}

        function tagLabel(tag) {
            return String(tag.name).trim();
        }
        function norm(s) {
            return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function renderChips() {
            chips.innerHTML = '';
            selected.forEach((tag) => {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm';
                chip.appendChild(document.createTextNode(tag.name));

                const x = document.createElement('button');
                x.type = 'button';
                x.className = 'shrink-0 w-4 h-4 inline-flex items-center justify-center rounded-full text-emerald-500 hover:bg-emerald-100 hover:text-emerald-700';
                x.setAttribute('aria-label', 'Hapus tag ' + tag.name);
                x.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
                x.addEventListener('click', (e) => { e.stopPropagation(); removeTag(tag.id); });
                chip.appendChild(x);

                chips.appendChild(chip);
            });
        }

        function toggleTag(id) {
            if (selected.has(id)) {
                selected.delete(id);
            } else {
                const t = allTags.find((x) => String(x.id) === String(id));
                if (t) selected.set(t.id, { id: t.id, name: t.name });
            }
            renderChips();
            renderOptions(input.value);
        }

        function removeTag(id) {
            selected.delete(id);
            renderChips();
            renderOptions(input.value);
        }

        function renderOptions(q) {
            const query = norm(q);
            list.innerHTML = '';
            // When the user is actively searching, include already-selected
            // tags (dimmed) so they are "found" rather than hidden. When the
            // query is empty, only list the remaining unselected tags to keep
            // the default dropdown tidy.
            const pool = query
                ? allTags
                : allTags.filter((t) => !selected.has(t.id));
            const matches = query
                ? pool.filter((t) => norm(tagLabel(t)).includes(query))
                : pool;

            if (matches.length === 0) {
                empty.classList.remove('hidden');
                empty.textContent = query ? 'Tidak ada tag dengan nama “' + q + '”.' : 'Semua tag sudah dipilih.';
                return;
            }
            empty.classList.add('hidden');

            matches.forEach((t) => {
                const isSelected = selected.has(t.id);
                const row = document.createElement('button');
                row.type = 'button';
                row.className = 'w-full text-left px-3 py-2 text-sm flex items-center gap-2 ' +
                    (isSelected
                        ? 'text-gray-400 cursor-default'
                        : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700');
                row.innerHTML = '<span class="flex-1 truncate"></span>';
                row.querySelector('span').textContent = t.name;
                if (isSelected) {
                    const done = document.createElement('span');
                    done.className = 'shrink-0 text-[11px] font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5';
                    done.textContent = 'dipilih';
                    row.appendChild(done);
                } else {
                    row.addEventListener('click', () => {
                        toggleTag(t.id);
                        input.value = '';
                        input.focus();
                        renderOptions('');
                    });
                }
                list.appendChild(row);
            });
        }

        function openDropdown() { dropdown.classList.remove('hidden'); }
        function closeDropdown() { dropdown.classList.add('hidden'); }

        input.addEventListener('input', () => { renderOptions(input.value); openDropdown(); });
        input.addEventListener('focus', () => { renderOptions(input.value); openDropdown(); });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDropdown();
        });
        document.addEventListener('click', (e) => {
            // Only close when the click is outside the combo. Clicks inside the
            // input itself must NOT close it, otherwise the dropdown hides the
            // instant the user taps into the search box before typing.
            if (!root.contains(e.target) || e.target === input) return;
            if (e.target !== dropdown && !dropdown.contains(e.target)) closeDropdown();
        });

        // Expose for the inline "add new tag" handler on each page.
        root._tagCombo = {
            addTag(tag) {
                if (!tag || tag.id === undefined || tag.id === null) return;
                if (!selected.has(tag.id)) {
                    selected.set(tag.id, { id: tag.id, name: tag.name });
                    renderChips();
                }
                // Make sure the newly created tag can be searched/filtered later.
                if (!allTags.some((t) => String(t.id) === String(tag.id))) {
                    allTags.push({ id: tag.id, name: tag.name });
                }
                renderOptions(input.value);
            },
            count() { return selected.size; },
            eachTag(fn) { selected.forEach((t, id) => fn(id, t)); },
        };

        // Pre-select tags (edit pages pass the post's tag ids via data-selected).
        if (root.dataset.selected) {
            try {
                JSON.parse(root.dataset.selected).forEach((id) => {
                    const t = allTags.find((x) => String(x.id) === String(id));
                    if (t) selected.set(t.id, { id: t.id, name: t.name });
                });
            } catch (_) { /* ignore malformed data-selected */ }
        }

        renderChips();
        renderOptions('');
        closeDropdown();
    };
})();
