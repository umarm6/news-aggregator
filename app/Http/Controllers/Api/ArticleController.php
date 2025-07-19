<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $articles = Cache::remember('articles:all', 300, function () use ($request) {
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
