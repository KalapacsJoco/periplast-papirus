<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;

class Photo extends Model
{
    protected $fillable = ['photoable_type', 'photoable_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'uploaded_by'];

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return array<string, mixed> */
    public static function attributesFromUpload(UploadedFile $file, ?int $userId): array
    {
        // A Livewire ideiglenes fájlja a tároláskor törlődik, ezért a metaadatokat előbb olvassuk ki.
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        return [
            'disk' => 'local',
            'path' => $file->store('photos/'.now()->format('Y/m'), 'local'),
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'uploaded_by' => $userId,
        ];
    }

    /** The app's tab-scoped auth cannot protect plain links, so photos are served through signed URLs. */
    public function url(): string
    {
        return URL::signedRoute('photos.show', ['photo' => $this->id]);
    }
}
