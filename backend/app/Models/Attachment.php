<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *    schema="Attachment",
 *        @OA\Property(
 *            property="id",
 *            description="Attachment identifier",
 *            type="integer",
 *            nullable="false",
 *            example="1"
 *        ),
 *        @OA\Property(
 *            property="filename",
 *            description="Attachments filename",
 *            type="string",
 *            nullable="true",
 *            example="test-attachment.docx"
 *        ),
 *        @OA\Property(
 *            property="type",
 *            description="file type extension of the attachment",
 *            type="string",
 *            nullable="false",
 *            example="john.doe@todo.com"
 *        ),
 *    )
 * )
 */
class Attachment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }


}
