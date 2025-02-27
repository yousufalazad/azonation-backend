<?php
namespace App\Http\Controllers\Org\Project;
use App\Http\Controllers\Controller;

use App\Models\ProjectDignitary;
use Illuminate\Http\Request;

class ProjectDignitaryController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ProjectDignitary $projectDignitary) {}
    public function edit(ProjectDignitary $projectDignitary) {}
    public function update(Request $request, ProjectDignitary $projectDignitary) {}
    public function destroy(ProjectDignitary $projectDignitary) {}
}
