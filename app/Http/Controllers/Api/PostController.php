<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function update(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,svg', 'max:2048']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts/' . $post->image);

            $post->update([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
                'image' => $image->hashName()
            ]);
        } else {
            $post->update([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content
            ]);
        }

        return new PostResource(true, 'Post updated successfully,', $post);
    }

    public function destroy(Post $post)
    {
        Storage::delete('public/posts/' . $post->image);

        $post->delete();

        return new PostResource(true, 'Post deleted successfully.', null);
    }
}
