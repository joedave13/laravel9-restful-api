<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()->latest()->paginate(5);

        return new PostResource(true, 'Post list retrieved.', $posts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,svg', 'max:2048']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::query()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'image' => $image->hashName()
        ]);

        return new PostResource(true, 'Post created successfully.', $post);
    }

    public function show(Post $post)
    {
        return new PostResource(true, 'Post data found.', $post);
    }
}
