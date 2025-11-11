<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    /**
     * Display a listing of blog posts
     */
    public function index(Request $request)
    {
        $logged_in = session('loggedIn') ? 1 : 0;
        
        $blog = Blog::published()
                   ->recent()
                   ->get();
     
        return view('blog', [
            "logged_in" => $logged_in, 
            'blog' => $blog
        ]);
    }

    /**
     * Display a specific blog post
     */
    public function show($topic_url)
    {
        $logged_in = session('loggedIn') ? 1 : 0;
        
        $post = Blog::where('topic_url', $topic_url)
                   ->where('published', true)
                   ->whereNull('hide')
                   ->firstOrFail();
        
        // Get related posts
        $relatedPosts = Blog::published()
                           ->where('id', '!=', $post->id)
                           ->recent(3)
                           ->get();
        
        return view('blog-single', [
            'logged_in' => $logged_in,
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }

    /**
     * Get recent blog posts for home page
     */
    public function getRecentPosts($limit = 4)
    {
        return Blog::published()
                  ->recent($limit)
                  ->get();
    }

    /**
     * Admin: Display all blog posts
     */
    public function adminIndex()
    {
        $posts = Blog::orderBy('date', 'desc')->paginate(10);
        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Admin: Show create form
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Admin: Store new blog post
     */
    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_photo' => 'nullable|url',
            'author' => 'required|string|max:100',
            'published' => 'boolean'
        ]);

        Blog::create($request->all());

        return redirect()->route('admin.blog.index')
                        ->with('success', 'Blog post created successfully!');
    }

    /**
     * Admin: Show edit form
     */
    public function edit($id)
    {
        $post = Blog::findOrFail($id);
        return view('admin.blog.edit', compact('post'));
    }

    /**
     * Admin: Update blog post
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_photo' => 'nullable|url',
            'author' => 'required|string|max:100',
            'published' => 'boolean'
        ]);

        $post = Blog::findOrFail($id);
        $post->update($request->all());

        return redirect()->route('admin.blog.index')
                        ->with('success', 'Blog post updated successfully!');
    }

    /**
     * Admin: Delete blog post
     */
    public function destroy($id)
    {
        $post = Blog::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.blog.index')
                        ->with('success', 'Blog post deleted successfully!');
    }
}
