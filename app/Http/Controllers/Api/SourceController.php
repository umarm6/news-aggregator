<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articles;
use Illuminate\Http\JsonResponse;

class SourceController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = Articles::getCachedSources();

        return response()->json([
            'success' => true,
            'data' => $sources
        ]);
    }

}
