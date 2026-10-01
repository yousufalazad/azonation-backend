<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Simple organisation write-ups: a title, formatted text, optional date, privacy,
 * on/off and attachments (history, success stories, recognitions...).
 *
 * The controller using this trait also uses ResolvesCurrentOrg and StoresAttachments and defines:
 *   protected function contentConfig(): array
 *   [
 *     'model' => History::class,
 *     'body' => 'history',                 // formatted text column
 *     'date' => null | 'recognition_date', // optional date column
 *     'dateRequired' => false,
 *     'active' => 'is_active' | 'status',  // on/off column
 *     'files' => ['image' => HistoryImage::class, 'file' => HistoryFile::class],
 *     'foreignKey' => 'history_id',
 *     'folder' => 'org/history',
 *     'label' => 'History record',         // for messages
 *   ]
 */
trait ManagesOrgContent
{
    public function index()
    {
        $c = $this->contentConfig();
        $table = (new $c['model'])->getTable();
        $query = $this->owned($c['model'])
            ->select("$table.*", 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', "$table.privacy_setup_id", '=', 'privacy_setups.id')
            ->withCount(['images', 'documents']);
        if ($c['date']) {
            $query->orderByDesc("$table.{$c['date']}");
        }
        $records = $query->orderByDesc("$table.id")->get();
        return response()->json(['status' => true, 'data' => $records], 200);
    }

    public function show($id)
    {
        $c = $this->contentConfig();
        $table = (new $c['model'])->getTable();
        $record = $this->owned($c['model'])
            ->select("$table.*", 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', "$table.privacy_setup_id", '=', 'privacy_setups.id')
            ->where("$table.id", $id)
            ->first();
        if (!$record) {
            return response()->json(['status' => false, 'message' => "{$c['label']} not found"], 404);
        }
        return response()->json(['status' => true, 'data' => $this->withAttachmentUrls($record)], 200);
    }

    public function store(Request $request)
    {
        $c = $this->contentConfig();
        $validator = Validator::make($request->all(), $this->contentRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $record = DB::transaction(function () use ($request, $c) {
            $record = new $c['model']($this->contentValues($request));
            $record->user_id = $this->orgIdOrFail(); // always the current organisation
            $record->save();
            $this->saveAttachments($request, $record, $c['files'], $c['foreignKey'], $c['folder']);
            return $record;
        });
        return response()->json(['status' => true, 'message' => "{$c['label']} saved.", 'data' => $record], 201);
    }

    public function update(Request $request, $id)
    {
        $c = $this->contentConfig();
        $validator = Validator::make($request->all(), $this->contentRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $record = $this->owned($c['model'])->find($id);
        if (!$record) {
            return response()->json(['status' => false, 'message' => "{$c['label']} not found"], 404);
        }
        // The owner never changes (before, an admin saving a record made it theirs)
        DB::transaction(function () use ($request, $record, $c) {
            $record->update($this->contentValues($request));
            $this->saveAttachments($request, $record, $c['files'], $c['foreignKey'], $c['folder']);
        });
        return response()->json(['status' => true, 'message' => "{$c['label']} updated.", 'data' => $record], 200);
    }

    public function destroy($id)
    {
        $c = $this->contentConfig();
        $record = $this->owned($c['model'])->find($id);
        if (!$record) {
            return response()->json(['status' => false, 'message' => "{$c['label']} not found"], 404);
        }
        $this->deleteAttachments($record);
        $record->delete();
        return response()->json(['status' => true, 'message' => "{$c['label']} deleted."], 200);
    }

    private function contentRules(): array
    {
        $c = $this->contentConfig();
        $rules = [
            'title' => 'required|string|max:255',
            // Formatted text; the pages clean it before showing it
            $c['body'] => 'nullable|string|max:500000',
            'privacy_setup_id' => 'nullable|integer|exists:privacy_setups,id',
            $c['active'] => 'nullable|boolean',
        ];
        if ($c['date']) {
            $rules[$c['date']] = ($c['dateRequired'] ?? false) ? 'required|date' : 'nullable|date';
        }
        return $rules + $this->attachmentRules();
    }

    private function contentValues(Request $request): array
    {
        $c = $this->contentConfig();
        $values = [
            'title' => $request->title,
            $c['body'] => $request->input($c['body']),
            // Private unless chosen otherwise (some tables require a value)
            'privacy_setup_id' => $request->privacy_setup_id
                ?: (DB::table('privacy_setups')->where('name', 'Private')->value('id') ?? DB::table('privacy_setups')->orderBy('id')->value('id')),
            $c['active'] => $request->has($c['active']) ? $request->boolean($c['active']) : true,
        ];
        if ($c['date']) {
            $values[$c['date']] = $request->input($c['date']);
        }
        return $values;
    }
}
