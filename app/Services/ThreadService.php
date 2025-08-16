<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ThreadService
{
    public function storeAttachments($request, $thread)
    {
        $fileStorage = 'thread-attachments';
        if (is_array($request->attachments) && count($request->attachments)) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store($fileStorage);
                $url = Storage::url($path);
                $ext = strtolower($file->getClientOriginalExtension());
                $thread->attachments()->create([
                    'url' => $url,
                    'file_path' => $path,
                    'file_extension' => $file->getClientOriginalExtension(),
                    'file_name' => $file->getClientOriginalName(),
                    'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'media' : 'file',
                    'userable_id' => $thread->user_id,
                    'userable_type' => User::class,
                ]);
            }
        }
    }
}
