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

<script>
    // وقتی پست جدید از طریق broadcasting بیاد
    window.Echo.channel('posts')
        .listen('.post.created', (e) => {
            const list = document.getElementById('posts');
            const li = document.createElement('li');
            li.textContent = e.title + " - " + (e.body ?? '');
            list.prepend(li);
        });
</script>
</body>
</html>
