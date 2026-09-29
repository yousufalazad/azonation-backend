<?php

namespace App\Http\Controllers\Org\History;

use App\Http\Concerns\ManagesOrgContent;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\HistoryFile;
use App\Models\HistoryImage;

/**
 * The organisation's history: how it started and what happened over the years.
 * The list, add, edit and delete logic is shared in ManagesOrgContent.
 */
class HistoryController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments, ManagesOrgContent;

    protected function contentConfig(): array
    {
        return [
            'model' => History::class,
            'body' => 'history',
            'date' => null,
            'dateRequired' => false,
            'active' => 'is_active',
            'files' => ['image' => HistoryImage::class, 'file' => HistoryFile::class],
            'foreignKey' => 'history_id',
            'folder' => 'org/history',
            'label' => 'History record',
        ];
    }
}
