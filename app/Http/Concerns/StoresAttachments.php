<?php

namespace App\Http\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Photos and documents attached to a record (plans, reports...), stored on the public disk.
 * File tables have: <foreignKey>, file_path, file_name, mime_type, file_size, is_public, is_active.
 */
trait StoresAttachments
{
    // Validation rules for the "images[]" and "documents[]" fields
    protected function attachmentRules(): array
    {
        return [
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ];
    }

    /**
     * Save uploaded images[] and documents[] for $record.
     * $models = ['image' => ImageModel::class, 'file' => FileModel::class]
     */
    protected function saveAttachments(Request $request, Model $record, array $models, string $foreignKey, string $folder): void
    {
        foreach (['file' => 'documents', 'image' => 'images'] as $kind => $field) {
            foreach ((array) $request->file($field, []) as $upload) {
                $sub = $kind === 'image' ? 'image' : 'file';
                $path = $upload->storeAs("$folder/$sub", Carbon::now()->format('YmdHis') . '_' . $upload->getClientOriginalName(), 'public');
                $models[$kind]::create([
                    $foreignKey => $record->getKey(),
                    'file_path' => $path,
                    'file_name' => $upload->getClientOriginalName(),
                    'mime_type' => $upload->getClientMimeType(),
                    'file_size' => $upload->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
        }
    }

    // Add image_url / document_url to the record's images and documents
    protected function withAttachmentUrls(Model $record): Model
    {
        $record->images = $record->images->map(function ($image) {
            $image->image_url = $image->file_path ? url(Storage::url($image->file_path)) : null;
            return $image;
        });
        $record->documents = $record->documents->map(function ($document) {
            $document->document_url = $document->file_path ? url(Storage::url($document->file_path)) : null;
            return $document;
        });
        return $record;
    }

    // Delete the record's attachment rows and files
    protected function deleteAttachments(Model $record): void
    {
        $paths = $record->images()->pluck('file_path')->merge($record->documents()->pluck('file_path'))->filter();
        $record->images()->delete();
        $record->documents()->delete();
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
