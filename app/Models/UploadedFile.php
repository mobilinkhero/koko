<?php

namespace App\Models;

/**
 * @property int $id
 * @property string $type
 * @property string $path
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadedFile whereUrl($value)
 *
 * @mixin \Eloquent
 */
class UploadedFile extends BaseModel
{
    protected $fillable = [
        'type',
        'path',
        'url',

    ];
}
