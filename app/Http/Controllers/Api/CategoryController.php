<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articles;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Articles::getCachedCategories();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

}
