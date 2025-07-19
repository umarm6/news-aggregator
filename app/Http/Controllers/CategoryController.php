<?php
namespace App\Http\Controllers;

use App\Models\Articles;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Cache::remember('categories', 3600, function () {
            return Articles::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category');
        });

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

}
