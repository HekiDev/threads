<?php

namespace App\Http\Controllers;

use App\Models\ThreadTopic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        $data = ThreadTopic::query()
            ->select('id', 'name')
            ->whereAny([
                'name',
            ], 'like', '%' . request('search') . '%')
            ->limit(10)
            ->get();

        return response()->json($data);
    }
}
