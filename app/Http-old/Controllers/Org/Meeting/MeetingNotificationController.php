<?php
namespace App\Http\Controllers\Org\Meeting;
use App\Http\Controllers\Controller;
use App\Models\MeetingNotification;
use Illuminate\Http\Request;

class MeetingNotificationController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(MeetingNotification $meetingNotification) {}
    public function edit(MeetingNotification $meetingNotification) {}
    public function update(Request $request, MeetingNotification $meetingNotification) {}
    public function destroy(MeetingNotification $meetingNotification) {}
}
