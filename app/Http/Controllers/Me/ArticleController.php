<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\Me\Article\StoreRequest;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $articles = Article::with('category')->select([
            'category_id', 'title', 'slug', 'content_preview', 'content', 'featured_image',
        ])
        ->where('user_id', $userId)
        ->paginate();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Articles fetched successfully.',
            ],
            'data' => $articles,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $userId = auth()->id();
        $validated = $request->validated();

        $validated['slug'] = Str::of($validated['title'])->slug('-');
        $validated['content_preview'] = substr($validated['content'], 0, 218) . '...';

        if ($request->hasFile('featured_image'))
        {
            $validated['featured_image'] = $request->file('featured_image')->store('article/featured-image', 'public');
        }

        $createArticle = User::find($userId)->articles()->create($validated);

        if ($createArticle)
        {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Article created successfully.',
                ],
                'data' => [],
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 500,
                'status' => 'error',
                'message' => 'Error! Article failed to create.',
            ],
            'data' => [],
        ], 500);
    }

    public function show($id)
    {
        $article = Article::find($id);
        $userId = auth()->id();

        if($article)
        {
            if($article->user_id === $userId)
            {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Article created successfully.',
                    ],
                    'data' => $article,
                ]);
            }
    
            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Unauthorized.',
                ],
                'data' => [],
            ], 401);
        }

        return response()->json([
            'meta' => [
                'code' => 404,
                'status' => 'error',
                'message' => 'Article not found.',
            ],
            'data' => [],
        ], 404);

    }
}
