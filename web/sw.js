// var cacheName = 'snapdebrief-v1';

// self.addEventListener('install', function(event) {
//     event.waitUntil(caches.open(cacheName).then(function(cache) {
//         return cache.addAll([
//             '/en',
//             '/nl',
//             '/faq/nl',
//             '/faq/en',
//             '/disclaimer',
//             '/img/nl.png',
//             '/img/en.png',
//             '/img/uitleg-briefhulp-01b.png',
//             '/img/uitleg-briefhulp-03c.png',
//             '/img/proceed_vet.png',
//             '/img/amsterdam.png',
//             '/img/boot.png',
//             '/img/madi.png',
//             '/lib/image.min.js'
//         ]);
//     }));
// });

// self.addEventListener('fetch', function(event) {
//     event.respondWith(fetch(event.request).then(function(response) {
//         var clone = response.clone();
//         caches.open(cacheName).then(function(cache) {
//             cache.put(event.request, clone);
//         });
//         return response;
//     }).catch(function() {
//         return caches.match(event.request);
//     }));
// });