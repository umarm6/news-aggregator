<?php
// app/Http/Controllers/Api/ArticleController.php

namespace App\Http\Controllers;

use App\Http\Requests\ArticlesSearchRequest;
use App\Http\Resources\ArticlesResource;
use App\Models\Articles;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index(ArticlesSearchRequest $request): JsonResponse
    {
        $cacheKey = 'articles:' . md5(serialize($request->validated()));

        $articles = Cache::remember($cacheKey, 300, function () use ($request) {
             return $this->buildQuery($request)->paginate($request->get('per_page', 100));
        });

         return response()->json([
            'success' => true,
            'data' => ArticlesResource::collection($articles->items()),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'last_page' => $articles->lastPage(),
            ]
        ]);
    }

    public function show(Articles $article): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ArticlesResource($article)
        ]);
    }

    private function buildQuery(ArticlesSearchRequest $request)
    {

         $query = Articles::with('source')
            ->latest('published_at');

        if ($request->has('q')) {
            $query->search($request->q);
        }

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('source')) {
            $query->bySource($request->source);
        }

        if ($request->has('author')) {
            $query->byAuthor($request->author);
        }

        if ($request->has('from') || $request->has('from') &&  $request->has('to')) {
            $to = new Carbon($request->to);
            $toDate = $to->toDateString();
            $query->byDateRange($request->get('from'), $toDate);
        }

        return $query;

    }
}
