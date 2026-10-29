<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Redaktur;
use App\Http\Controllers\Reporter;
use Illuminate\Support\Facades\Route;

// ============ AUTH ============
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Profile (self-service)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ============ FRONTEND (PUBLIC) ============
Route::get('/', [Frontend\HomeController::class, 'index'])->name('home');
Route::get('/berita', [Frontend\PostController::class, 'index'])->name('posts.index');
Route::get('/berita/{slug}', [Frontend\PostController::class, 'show'])->name('posts.show');
Route::get('/kategori/{slug}', [Frontend\CategoryController::class, 'show'])->name('categories.show');
// Old slug of the about page (renamed to tentang-lawnu).
Route::get('/halaman/tentang-lwp', fn () => redirect()->route('pages.show', ['slug' => 'tentang-lawnu']));
Route::get('/halaman/{slug}', [Frontend\PageController::class, 'show'])->name('pages.show');
Route::get('/pencarian', [Frontend\SearchController::class, 'index'])->name('search');
Route::get('/kontak', [Frontend\ContactController::class, 'index'])->name('contact');
Route::get('/kontak/status', [Frontend\ContactController::class, 'status'])->name('contact.status');

// Pendaftaran reporter/redaktur (diverifikasi admin)
Route::get('/daftar', [Auth\RegisterController::class, 'create'])->name('register');
Route::post('/daftar', [Auth\RegisterController::class, 'store'])->name('register.post');
Route::get('/daftar/berhasil', [Auth\RegisterController::class, 'success'])->name('register.success');
Route::get('/galeri', [Frontend\GalleryController::class, 'index'])->name('galleries.index');
Route::get('/galeri/{gallery}', [Frontend\GalleryController::class, 'show'])->name('galleries.show');

// Frontend API
Route::prefix('api/frontend')->group(function () {
    Route::post('/contact', [Frontend\ContactController::class, 'store'])->name('api.frontend.contact.store');
    Route::get('/search', [Frontend\SearchController::class, 'search'])->name('api.frontend.search');
    Route::get('/posts/{post}/rating', [Frontend\RatingController::class, 'show'])->name('api.frontend.rating.show');
    Route::post('/posts/{post}/rating', [Frontend\RatingController::class, 'store'])->name('api.frontend.rating.store');
});

// ============ ADMIN ============
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/statistics', [Admin\DashboardController::class, 'statistics'])->name('dashboard.statistics');

        // Users
        Route::get('users/filter', [Admin\UserController::class, 'filter'])->name('users.filter');
        Route::resource('users', Admin\UserController::class)->except('show');
        Route::patch('users/{user}/status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [Admin\UserController::class, 'resetPassword'])->name('users.reset-password');

        // Roles
        Route::resource('roles', Admin\RoleController::class)->except('show');
        Route::put('roles/{role}/permissions', [Admin\RoleController::class, 'updatePermissions'])->name('roles.permissions');

        // Menus
        Route::get('menus', [Admin\MenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [Admin\MenuController::class, 'store'])->name('menus.store');
        Route::put('menus/{menu}', [Admin\MenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{menu}', [Admin\MenuController::class, 'destroy'])->name('menus.destroy');
        Route::patch('menus/{menu}/status', [Admin\MenuController::class, 'toggleStatus'])->name('menus.toggle-status');
        Route::patch('menus/reorder', [Admin\MenuController::class, 'reorder'])->name('menus.reorder');

        // Pages
        Route::resource('pages', Admin\PageController::class);
        Route::patch('pages/{page}/publish', [Admin\PageController::class, 'publish'])->name('pages.publish');
        Route::patch('pages/{page}/unpublish', [Admin\PageController::class, 'unpublish'])->name('pages.unpublish');

        // Galeri Kegiatan
        Route::get('galleries/filter', [Admin\GalleryController::class, 'filter'])->name('galleries.filter');
        Route::resource('galleries', Admin\GalleryController::class)->except('show');
        Route::patch('galleries/{gallery}/publish', [Admin\GalleryController::class, 'publish'])->name('galleries.publish');
        Route::patch('galleries/{gallery}/unpublish', [Admin\GalleryController::class, 'unpublish'])->name('galleries.unpublish');
        Route::patch('galleries/{gallery}/approve', [Admin\GalleryController::class, 'approve'])->name('galleries.approve');
        Route::patch('galleries/{gallery}/reject', [Admin\GalleryController::class, 'reject'])->name('galleries.reject');

        // Posts
        Route::get('posts/filter', [Admin\PostController::class, 'filter'])->name('posts.filter');
        Route::post('posts/preview', [Admin\PostController::class, 'previewDraft'])->name('posts.preview-draft');
        Route::resource('posts', Admin\PostController::class);
        Route::get('posts/{post}/preview', [Admin\PostController::class, 'preview'])->name('posts.preview');
        Route::patch('posts/{post}/submit', [Admin\PostController::class, 'submit'])->name('posts.submit');
        Route::patch('posts/{post}/approve', [Admin\PostController::class, 'approve'])->name('posts.approve');
        Route::patch('posts/{post}/reject', [Admin\PostController::class, 'reject'])->name('posts.reject');
        Route::patch('posts/{post}/publish', [Admin\PostController::class, 'publish'])->name('posts.publish');
        Route::patch('posts/{post}/unpublish', [Admin\PostController::class, 'unpublish'])->name('posts.unpublish');
        Route::patch('posts/{post}/archive', [Admin\PostController::class, 'archive'])->name('posts.archive');

        // Categories
        Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::patch('categories/{category}/status', [Admin\CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Tags
        Route::get('tags', [Admin\TagController::class, 'index'])->name('tags.index');
        Route::post('tags', [Admin\TagController::class, 'store'])->name('tags.store');
        Route::put('tags/{tag}', [Admin\TagController::class, 'update'])->name('tags.update');
        Route::delete('tags/{tag}', [Admin\TagController::class, 'destroy'])->name('tags.destroy');

        // Media
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::get('media/filter', [Admin\MediaController::class, 'filter'])->name('media.filter');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::get('media/{media}', [Admin\MediaController::class, 'show'])->name('media.show');
        Route::put('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');

        // Contact Messages
        Route::get('contact-messages', [Admin\ContactController::class, 'index'])->name('contacts.index');
        Route::get('contact-messages/{message}', [Admin\ContactController::class, 'show'])->name('contacts.show');
        Route::patch('contact-messages/{message}/status', [Admin\ContactController::class, 'updateStatus'])->name('contacts.update-status');
        Route::patch('contact-messages/{message}/reply', [Admin\ContactController::class, 'reply'])->name('contacts.reply');
        Route::delete('contact-messages/{message}', [Admin\ContactController::class, 'destroy'])->name('contacts.destroy');

        // Verifikasi pendaftaran reporter/redaktur
        Route::get('registrations', [Admin\RegistrationController::class, 'index'])->name('registrations.index');
        Route::post('registrations/{registration}/approve', [Admin\RegistrationController::class, 'approve'])->name('registrations.approve');
        Route::post('registrations/{registration}/reject', [Admin\RegistrationController::class, 'reject'])->name('registrations.reject');

        // Ratings
        Route::get('ratings', [Admin\RatingController::class, 'index'])->name('ratings.index');

        // Settings
        Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');

        // Activity Logs
        Route::get('activity-logs/filter', [Admin\ActivityLogController::class, 'filter'])->name('logs.filter');
        Route::get('activity-logs', [Admin\ActivityLogController::class, 'index'])->name('logs.index');
    });

// ============ REPORTER ============
Route::prefix('reporter')
    ->middleware(['auth', 'role:reporter,admin'])
    ->name('reporter.')
    ->group(function () {
        Route::get('/dashboard', [Reporter\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/statistics', [Reporter\DashboardController::class, 'statistics'])->name('dashboard.statistics');

        Route::get('posts', [Reporter\PostController::class, 'index'])->name('posts.index');
        Route::get('posts/filter', [Reporter\PostController::class, 'filter'])->name('posts.filter');
        Route::post('posts/preview', [Reporter\PostController::class, 'previewDraft'])->name('posts.preview-draft');
        Route::get('posts/create', [Reporter\PostController::class, 'create'])->name('posts.create');
        Route::post('posts', [Reporter\PostController::class, 'store'])->name('posts.store');

        // Galeri Kegiatan (miliknya sendiri; publish via review redaktur)
        Route::get('galleries', [Reporter\GalleryController::class, 'index'])->name('galleries.index');
        Route::get('galleries/filter', [Reporter\GalleryController::class, 'filter'])->name('galleries.filter');
        Route::get('galleries/create', [Reporter\GalleryController::class, 'create'])->name('galleries.create');
        Route::post('galleries', [Reporter\GalleryController::class, 'store'])->name('galleries.store');
        Route::get('galleries/{gallery}/edit', [Reporter\GalleryController::class, 'edit'])->name('galleries.edit');
        Route::put('galleries/{gallery}', [Reporter\GalleryController::class, 'update'])->name('galleries.update');
        Route::patch('galleries/{gallery}/submit', [Reporter\GalleryController::class, 'submit'])->name('galleries.submit');
        Route::delete('galleries/{gallery}', [Reporter\GalleryController::class, 'destroy'])->name('galleries.destroy');
        Route::get('posts/{post}', [Reporter\PostController::class, 'show'])->name('posts.show');
        Route::get('posts/{post}/edit', [Reporter\PostController::class, 'edit'])->name('posts.edit');
        Route::get('posts/{post}/preview', [Reporter\PostController::class, 'preview'])->name('posts.preview');
        Route::put('posts/{post}', [Reporter\PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post}', [Reporter\PostController::class, 'destroy'])->name('posts.destroy');
        Route::patch('posts/{post}/submit', [Reporter\PostController::class, 'submit'])->name('posts.submit');

        // Media library (baca + upload; hapus: admin, atau reporter
        // untuk media miliknya yang belum dipakai di berita published)
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::get('media/filter', [Admin\MediaController::class, 'filter'])->name('media.filter');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    });

// ============ REDAKTUR ============
Route::prefix('redaktur')
    ->middleware(['auth', 'role:redaktur,admin'])
    ->name('redaktur.')
    ->group(function () {
        Route::get('/dashboard', [Redaktur\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/statistics', [Redaktur\DashboardController::class, 'statistics'])->name('dashboard.statistics');

        Route::get('posts', [Redaktur\PostReviewController::class, 'index'])->name('posts.index');
        Route::get('posts/review', [Redaktur\PostReviewController::class, 'reviewList'])->name('posts.review');
        Route::get('posts/review/{post}', [Redaktur\PostReviewController::class, 'review'])->name('posts.review-detail');
        Route::patch('posts/{post}/start-review', [Redaktur\PostReviewController::class, 'startReview'])->name('posts.start-review');
        Route::patch('posts/{post}/approve', [Redaktur\PostReviewController::class, 'approve'])->name('posts.approve');
        Route::patch('posts/{post}/reject', [Redaktur\PostReviewController::class, 'reject'])->name('posts.reject');
        Route::patch('posts/{post}/publish', [Redaktur\PostReviewController::class, 'publish'])->name('posts.publish');
        Route::patch('posts/{post}/unpublish', [Redaktur\PostReviewController::class, 'unpublish'])->name('posts.unpublish');
        Route::get('posts/{post}/preview', [Redaktur\PostReviewController::class, 'preview'])->name('posts.preview');

        // Galeri Kegiatan (review)
        Route::get('galleries', [Redaktur\GalleryReviewController::class, 'index'])->name('galleries.index');
        Route::get('galleries/{gallery}', [Redaktur\GalleryReviewController::class, 'review'])->name('galleries.review');
        Route::patch('galleries/{gallery}/approve', [Redaktur\GalleryReviewController::class, 'approve'])->name('galleries.approve');
        Route::patch('galleries/{gallery}/reject', [Redaktur\GalleryReviewController::class, 'reject'])->name('galleries.reject');

        // Media library (baca + upload; hapus tetap khusus admin)
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::get('media/filter', [Admin\MediaController::class, 'filter'])->name('media.filter');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');

        // Pesan kontak (lihat + ubah status + balas; hapus tetap khusus admin)
        Route::get('contact-messages', [Admin\ContactController::class, 'index'])->name('contacts.index');
        Route::get('contact-messages/{message}', [Admin\ContactController::class, 'show'])->name('contacts.show');
        Route::patch('contact-messages/{message}/status', [Admin\ContactController::class, 'updateStatus'])->name('contacts.update-status');
        Route::patch('contact-messages/{message}/reply', [Admin\ContactController::class, 'reply'])->name('contacts.reply');
    });
