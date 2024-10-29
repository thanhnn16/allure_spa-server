<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        $query = $request->get('query', '');
        $type = $request->get('type', 'all'); // all, products, services
        $limit = $request->get('limit', 10);

        $results = $this->searchService->search($query, $type, $limit);

        return $this->respondWithJson($results);
    }
}
