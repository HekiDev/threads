<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $data = Thread::query()
            ->with([
                'user:id,name',
                'topic:id,name',
            ])
            ->paginate(10)
            ->toResourceCollection();

        return Inertia::render('Dashboard', [
            'threads' => $data,
        ]);
    }

    public function show($username, $uuid)
    {
        $thread = Thread::query()
            ->with([
                'user:id,name',
                'topic:id,name',
            ])
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->toResource();

        $comments = Thread::query()
            ->with('user')
            ->limit(2)
            ->paginate(2)
            ->toResourceCollection();

        return Inertia::render('Thread/Show', [
            'post' => $thread,
            'comments' => $comments,
        ]);
    }
}
