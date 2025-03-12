<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;
use App\Models\MeetingDignitary;
use Illuminate\Http\Request;

class MeetingDignitaryController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(MeetingDignitary $meetingDignitary) {}
    public function edit(MeetingDignitary $meetingDignitary) {}
    public function update(Request $request, MeetingDignitary $meetingDignitary) {}
    public function destroy(MeetingDignitary $meetingDignitary) {}
}
