<?php

namespace App\Http\Controllers\Org\Recognition;

use App\Http\Concerns\ManagesOrgContent;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use App\Http\Controllers\Controller;
use App\Models\Recognition;
use App\Models\RecognitionFile;
use App\Models\RecognitionImage;

/**
 * Awards and recognition the organisation received.
 * The list, add, edit and delete logic is shared in ManagesOrgContent.
 */
class RecognitionController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments, ManagesOrgContent;

    protected function contentConfig(): array
    {
        return [
            'model' => Recognition::class,
            'body' => 'description',
            'date' => 'recognition_date',
            'dateRequired' => true,
            'active' => 'is_active',
            'files' => ['image' => RecognitionImage::class, 'file' => RecognitionFile::class],
            'foreignKey' => 'recognition_id',
            'folder' => 'org/recognition',
            'label' => 'Recognition',
        ];
    }
}
