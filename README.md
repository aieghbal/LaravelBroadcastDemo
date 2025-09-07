# Laravel Broadcasting with Reverb (Realtime Post Updates)

این پروژه یک مثال ساده از **Laravel 12 Broadcasting** با استفاده از **Reverb** است. هدف: نمایش realtime پست‌ها در مرورگر بدون نیاز به رفرش صفحه.

## پیش‌نیازها

- PHP >= 8.1
- Laravel 12
- Node.js & npm

## نصب پروژه

```bash
composer create-project laravel/laravel laravel-broadcast-demo
cd laravel-broadcast-demo
npm install
```



## فعال‌سازی Reverb
```bash
php artisan install:broadcasting --reverb
```



## .env:
```bash
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=laravel
REVERB_APP_KEY=base64:someKey
REVERB_APP_SECRET=base64:someSecret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
```



## اجرای WebSocket Server
```bash
php artisan reverb:start
```



## مدل و Migration پست
```bash
php artisan make:model Post -m
```



## database/migrations/xxxx_create_posts_table.php:
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->timestamps();
});
```



## app/Models/Post.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'body'];
}
```



## Event Broadcast
```bash
php artisan make:event PostCreated
```



## app/Events/PostCreated.php:
```php
namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('posts');
    }

    public function broadcastAs(): string
    {
        return 'post.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->post->id,
            'title' => $this->post->title,
            'body' => $this->post->body,
        ];
    }
}
```



## Controller و Routes
```bash
php artisan make:controller PostController
```



## app/Http/Controllers/PostController.php:
```php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Events\PostCreated;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();
        return view('posts.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post = Post::create($validated);
        PostCreated::dispatch($post);

        return redirect()->back()->with('success', 'پست ساخته شد!');
    }
}
```



## Blade View
## resources/views/posts/index.blade.php:
```php
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>پست‌ها</title>
    @vite('resources/js/app.js')
</head>
<body>
    <h1>لیست پست‌ها</h1>

    <form action="{{ route('posts.store') }}" method="POST">
        @csrf
        <input type="text" name="title" placeholder="عنوان">
        <textarea name="body" placeholder="متن پست"></textarea>
        <button type="submit">ایجاد پست</button>
    </form>

    <ul id="posts">
        @foreach($posts as $post)
            <li>{{ $post->title }} - {{ $post->body }}</li>
        @endforeach
    </ul>
</body>
</html>
```



## JS اصلی
## resources/js/app.js:
```php
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Alpine from 'alpinejs';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws'],
});

console.log("Echo instance:", window.Echo);

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Alpine = Alpine;
Alpine.start();

setTimeout(() => {
    window.Echo.channel('posts')
        .listen('.post.created', (e) => {
            console.log("✅ New post broadcasted:", e);

            const list = document.getElementById('posts');
            if (list) {
                const li = document.createElement('li');
                li.textContent = e.title + " - " + e.body;
                list.prepend(li);
            }
        });
}, 2000);
```



## کامپایل JS
```bash
npm install
npm run dev
```


## 🔟 تست نهایی
- اجرای سرور لاراول
```bash
php artisan serve
```

- اجرای Reverb WebSocket Server
```bash
php artisan reverb:start
```


## باز کردن مرورگر
به آدرس: /posts

## ایجاد پست جدید
پس از ساخت پست، باید بدون رفرش صفحه در لیست نمایش داده شود
در کنسول مرورگر پیام broadcast چاپ شود
