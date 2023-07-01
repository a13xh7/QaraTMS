<?php

namespace App\Http\Controllers;

use App\Document;
use App\Project;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    /*****************************************
     *  PAGES
     *****************************************/

    public function index($project_id)  // list page
    {
        $project = Project::findOrFail($project_id);
        $documents = Document::where('project_id', $project->id)->tree()->get()->toTree();
        $selectedDocument = Document::first();

        return view('docs.list_page')
            ->with('project', $project)
            ->with('documents', $documents)
            ->with('selectedDocument', $selectedDocument);
    }

    public function create($project_id) // create page
    {
        if(!auth()->user()->can('add_edit_documents')) {
            abort(403);
        }

        $project = Project::findOrFail($project_id);
        $documents = Document::where('project_id', $project->id)->tree()->get()->toTree();

        return view('docs.create_page')
            ->with('project', $project)
            ->with('documents', $documents);
    }

    public function show($project_id, $document_id)
    {
        $project = Project::findOrFail($project_id);
        $documents = Document::where('project_id', $project->id)->tree()->get()->toTree();
        $selectedDocument = Document::findOrFail($document_id);

        return view('docs.list_page')
            ->with('project', $project)
            ->with('documents', $documents)
            ->with('selectedDocument', $selectedDocument);
    }

    public function edit($project_id, $document_id)
    {
        if(!auth()->user()->can('add_edit_documents')) {
            abort(403);
        }

        $project = Project::findOrFail($project_id);
        $documents = Document::where('project_id', $project->id)->tree()->get()->toTree();
        $selectedDocument = Document::findOrFail($document_id);

        return view('docs.edit_page')
            ->with('project', $project)
            ->with('documents', $documents)
            ->with('selectedDocument', $selectedDocument);
    }

    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        if(!auth()->user()->can('add_edit_documents')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required',
        ]);

        $document = new Document();

        $document->title = $request->title;
        $document->project_id = $request->project_id;
        $document->parent_id = $request->parent_id;
        $document->content = $request->get('content');
       // $document->content = FilesController::saveImagesAndGetCleanCode($request->get('content'));

        $document->save();

        return redirect()->route('project_documents_list_page', $document->project_id);
    }

    public function update(Request $request)
    {
        if(!auth()->user()->can('add_edit_documents')) {
            abort(403);
        }

        $document = Document::findOrFail($request->id);

        $document->title = $request->title;
        $document->parent_id = $request->parent_id;
        $document->content = $request->post('content');
       // $document->content = FilesController::saveImagesAndGetCleanCode($request->get('content'));

        $document->save();

        return redirect()->route('document_show_page', [$document->project_id, $document->id]);
    }

    public function destroy(Request $request)
    {
        if(!auth()->user()->can('delete_documents')) {
            abort(403);
        }

        $document = Document::findOrFail($request->id);
        $project_id = $document->project_id;

        $document->delete();
        return redirect()->route('project_documents_list_page', $project_id);
    }
}
