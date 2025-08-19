<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThreadRequest;
use App\Models\Thread;
use App\Models\ThreadTopic;
use App\Models\User;
use App\Services\ThreadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private ThreadService $threadService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'by_followers' => $request->boolean('by_followers', false),
            'by_following' => $request->boolean('by_following', false),
        ];

        $data = Thread::query()
            ->with([
                'user:id,name',
                'topic:id,name',
                'attachments:id,url,type,attachable_id,attachable_type',
            ])
            ->latest()
            ->paginate(10)
            ->toResourceCollection();

        return Inertia::render('Dashboard', [
            'threads' => Inertia::deepMerge($data),
            'currentPage' => $data->currentPage(),
            'lastPage' => $data->lastPage(),
            'filters' => $filters,
        ]);
    }

    public function show($username, $uuid)
    {
        $thread = Thread::query()
            ->with([
                'user:id,name',
                'topic:id,name',
                'attachments:id,url,type,attachable_id,attachable_type',
            ])
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->toResource();

        $comments = Thread::query()
            ->with(['user', 'attachments:id,url,type,attachable_id,attachable_type'])
            ->limit(2)
            ->paginate(2)
            ->toResourceCollection();

        return Inertia::render('Thread/Show', [
            'post' => $thread,
            'comments' => $comments,
        ]);
    }

    public function storeThread(ThreadRequest $request)
    {
        $topic = null;
        if ($request->topic) {
            $topic = ThreadTopic::firstOrCreate(['name' => $request->topic]);
        }

        $thread = Thread::create([
            'uuid' => Str::uuid(),
            'description' => $request->description,
            'thread_topic_id' => $topic?->id,
            'user_id' => auth()->user()->id,
        ]);

        $this->threadService->storeAttachments($request, $thread);

        return response()->json(['message' => 'Thread created successfully.']);
    }
}
