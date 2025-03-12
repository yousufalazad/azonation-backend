<?php
namespace App\Http\Controllers\Org\Event;
use App\Http\Controllers\Controller;

use App\Models\EventDignitary;
use Illuminate\Http\Request;

class EventDignitaryController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(EventDignitary $eventDignitary) {}
    public function edit(EventDignitary $eventDignitary) {}
    public function update(Request $request, EventDignitary $eventDignitary) {}
    public function destroy(EventDignitary $eventDignitary) {}
}
