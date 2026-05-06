const staticCacheName = 'precache-v1.0.1';

const precacheAssets = [
    '/assets/css/vendors/swiper-bundle.min.css',
    '/assets/css/vendors/bootstrap.min.css',
    '/assets/css/tabler-icons.min.css',
    '/assets/css/style.css',
    '/assets/css/custom.css',
    '/assets/js/vendors/bootstrap.bundle.min.js',
    '/assets/js/vendors/swiper-bundle.min.js',
    '/assets/js/vendors/custom-swiper.js',
    '/assets/js/vendors/jquery.min.js',
    '/assets/js/vendors/script.js',
    '/assets/js/vendors/custom.js',
];

self.addEventListener('install', function (event) {
    console.log('Service Worker is being installed');
    event.waitUntil(
        caches.open(staticCacheName).then(function (cache) {
            return cache.addAll(precacheAssets);
        })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== staticCacheName)
                .map(key => caches.delete(key))
            );
        })
    );
});

self.addEventListener('fetch', (event) => {});
