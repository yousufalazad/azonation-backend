<?php

namespace App\Http\Controllers\Org\SuccessStory;

use App\Http\Concerns\ManagesOrgContent;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use App\Models\SuccessStoryFile;
use App\Models\SuccessStoryImage;

/**
 * Stories of what the organisation achieved and the people it helped.
 * The list, add, edit and delete logic is shared in ManagesOrgContent.
 */
class SuccessStoryController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments, ManagesOrgContent;

    protected function contentConfig(): array
    {
        return [
            'model' => SuccessStory::class,
            'body' => 'story',
            'date' => null,
            'dateRequired' => false,
            'active' => 'status',
            'files' => ['image' => SuccessStoryImage::class, 'file' => SuccessStoryFile::class],
            'foreignKey' => 'success_story_id',
            'folder' => 'org/success-story',
            'label' => 'Success story',
        ];
    }
}
