import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.Echo.channel('posts')
    .listen('.post.created', (e) => {
        console.log("New post broadcasted:", e);
    });

Alpine.start();
