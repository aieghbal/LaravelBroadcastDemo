<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Events\PostCreated;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();
        return view('posts.index', compact('posts'));
    }
    public function store(Request $request)
    {
        $post = Post::create($request->only('title', 'body'));

        PostCreated::dispatch($post);

        return redirect()->back()->with('success', 'پست ساخته شد!');
    }
}
