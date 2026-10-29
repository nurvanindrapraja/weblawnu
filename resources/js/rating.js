// Star rating widget
document.addEventListener('DOMContentLoaded', function() {
    const ratingEl = document.getElementById('star-rating');
    if (!ratingEl) return;

    const postId = ratingEl.dataset.postId;
    const stars = ratingEl.querySelectorAll('.star-btn');
    let currentRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', async function() {
            currentRating = parseInt(this.dataset.value);

            // Update visual
            stars.forEach(s => {
                const val = parseInt(s.dataset.value);
                s.classList.toggle('text-yellow-400', val <= currentRating);
                s.classList.toggle('text-gray-300', val > currentRating);
            });

            // Submit rating
            try {
                const res = await fetch(`{{ route('frontend.rating.store', ':post') }}`.replace(':post', postId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (window.showToast) window.showToast(data.message, data.success ? 'success' : 'error');
            } catch(e) {
                if (window.showToast) window.showToast('Gagal mengirim rating.', 'error');
            }
        });
    });
});
