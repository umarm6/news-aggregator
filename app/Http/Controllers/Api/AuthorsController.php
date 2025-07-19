<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Articles;
use Illuminate\Http\JsonResponse;

class AuthorsController extends Controller
{
    public function index(): JsonResponse
    {
        $authors = Articles::getCachedAuthors();

        return response()->json([
            'success' => true,
            'data' => $authors
        ]);
    }

}
