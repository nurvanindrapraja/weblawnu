// Global AJAX helper with loading bar integration
window.ajaxRequest = async function(url, options = {}) {
    // Start loading bar
    if (window.startLoading) window.startLoading();

    try {
        const res = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: 'Server error' }));
            throw new Error(err.message || 'Request failed');
        }

        return await res.json();
    } catch (error) {
        throw error;
    } finally {
        if (window.stopLoading) window.stopLoading();
    }
};
