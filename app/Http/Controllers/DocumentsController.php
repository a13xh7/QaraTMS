<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use App\Models\DocumentManager;
use App\Models\DecisionLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
        if (!auth()->user()->can('add_edit_documents')) {
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
        if (!auth()->user()->can('add_edit_documents')) {
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
        if (!auth()->user()->can('add_edit_documents')) {
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

        $document->save();

        return redirect()->route('project_documents_list_page', $document->project_id);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_documents')) {
            abort(403);
        }

        $document = Document::findOrFail($request->id);

        $document->title = $request->title;
        $document->parent_id = $request->parent_id;
        $document->content = $request->post('content');

        $document->save();

        return redirect()->route('document_show_page', [$document->project_id, $document->id]);
    }

    public function checkChildren($id)
    {
        if (!auth()->user()->can('delete_documents')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasChildren = Document::where('parent_id', $id)->exists();
        
        return response()->json([
            'hasChildren' => $hasChildren
        ]);
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('delete_documents')) {
            abort(403);
        }

        $document = Document::findOrFail($request->id);
        $project_id = $document->project_id;

        // Check for child documents
        $hasChildren = Document::where('parent_id', $request->id)->exists();
        
        if ($hasChildren) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete document with sub-documents. Please move or delete the sub-documents first.');
        }

        $document->delete();
        return redirect()
            ->route('project_documents_list_page', $project_id)
            ->with('success', 'Document deleted successfully.');
    }

    public function showCompliance()
    {
        $documents = DocumentManager::where('category', 'compliance')->get();
        $users = \App\Models\User::all();
        return view('docs.modern_documentation_page', [
            'pageTitle' => 'Compliance',
            'documents' => $documents,
            'selectedDocument' => $documents->first(),
            'users' => $users,
        ]);
    }

    public function showSopQa()
    {
        $documents = DocumentManager::where('category', 'sop_qa')->get();
        $users = \App\Models\User::all();
        return view('docs.modern_documentation_page', [
            'pageTitle' => 'SOP & QA Docs',
            'documents' => $documents,
            'selectedDocument' => $documents->first(),
            'users' => $users,
        ]);
    }

    public function showTestExceptions()
    {
        $documents = DocumentManager::where('category', 'test_exceptions')->get();
        $users = \App\Models\User::all();
        return view('docs.modern_documentation_page', [
            'pageTitle' => 'Test Exceptions',
            'documents' => $documents,
            'selectedDocument' => $documents->first(),
            'users' => $users,
        ]);
    }

    public function showAuditReadiness()
    {
        $documents = DocumentManager::where('category', 'audit_readiness')->get();
        $users = \App\Models\User::all();
        return view('docs.modern_documentation_page', [
            'pageTitle' => 'Audit Readiness',
            'documents' => $documents,
            'selectedDocument' => $documents->first(),
            'users' => $users,
        ]);
    }

    public function showKnowledgeTransfers()
    {
        $documents = DocumentManager::where('category', 'knowledge_transfers')->get();
        $users = \App\Models\User::all();
        return view('docs.modern_documentation_page', [
            'pageTitle' => 'Knowledge Transfers',
            'documents' => $documents,
            'selectedDocument' => $documents->first(),
            'users' => $users,
        ]);
    }

    public function showDecisionLogs(Request $request)
    {
        $query = DecisionLog::query();

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('decision_owner', 'like', "%{$searchTerm}%")
                  ->orWhereJsonContains('tags', $searchTerm);
            });
        }

        // Decision type filter
        if ($request->filled('decision_type')) {
            $query->where('decision_type', $request->input('decision_type'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('decision_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('decision_date', '<=', $request->input('end_date'));
        }

        $decisionLogs = $query->latest()->paginate(10);

        // Fetch status counts from DB
        $statusCounts = DecisionLog::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');
        // Fetch decision type counts from DB
        $typeCounts = DecisionLog::select('decision_type', \DB::raw('count(*) as total'))
            ->groupBy('decision_type')->pluck('total', 'decision_type');

        // Fetch all unique decision types for dropdowns
        $allTypes = config('decisionlog.types');

        return view('docs.decision_logs', [
            'pageTitle' => 'Decision Logs',
            'decisionLogs' => $decisionLogs,
            'statusCounts' => $statusCounts,
            'typeCounts' => $typeCounts,
            'allTypes' => $allTypes,
        ]);
    }

    public function storeDecisionLog(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'decision_type' => 'required|string',
                'decision_owner' => 'required|string',
                'involved_qa' => 'required|string',
                'decision_date' => 'required|date',
                'sprint_release' => 'nullable|string',
                'context' => 'required|string',
                'decision' => 'required|string',
                'impact_risk' => 'required|string',
                'status' => 'required|string',
                'tags' => 'nullable|string',
                'related_artifacts.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
            ]);

            // Process tags
            if ($request->filled('tags')) {
                $validatedData['tags'] = array_map('trim', explode(',', $request->tags));
            }

            // Handle file uploads
            if ($request->hasFile('related_artifacts')) {
                $artifacts = [];
                foreach ($request->file('related_artifacts') as $file) {
                    $path = $file->store('related_artifacts', 'public');
                    $artifacts[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ];
                }
                $validatedData['related_artifacts'] = $artifacts;
            }

            $decisionLog = DecisionLog::create($validatedData);

            return redirect()->route('documents.decision_logs')->with('success', 'Decision log created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating decision log: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create decision log. Please try again.']);
        }
    }

    public function editDecisionLog(DecisionLog $log)
    {
        return response()->json($log);
    }

    public function updateDecisionLog(Request $request, DecisionLog $log)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'decision_type' => 'required|string',
            'decision_owner' => 'required|string',
            'involved_qa' => 'required|string',
            'decision_date' => 'required|date',
            'sprint_release' => 'nullable|string',
            'context' => 'required|string',
            'decision' => 'required|string',
            'impact_risk' => 'required|string',
            'status' => 'required|string',
            'tags' => 'nullable|string',
            'related_artifacts.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
        ]);

        // Process tags
        if ($request->filled('tags')) {
            $validatedData['tags'] = array_map('trim', explode(',', $request->tags));
        }

        // Handle file uploads
        if ($request->hasFile('related_artifacts')) {
            $artifacts = $log->related_artifacts ?? [];
            foreach ($request->file('related_artifacts') as $file) {
                $path = $file->store('related_artifacts', 'public');
                $artifacts[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
            $validatedData['related_artifacts'] = $artifacts;
        }

        $log->update($validatedData);

        return redirect()->route('documents.decision_logs')->with('success', 'Decision log updated successfully.');
    }

    public function deleteDecisionLog(DecisionLog $log)
    {
        try {
            $log->delete();
            return redirect()->route('documents.decision_logs')->with('success', 'Decision log deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting decision log: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete decision log. Please try again.');
        }
    }

    public function exportDecisionLogPdf(DecisionLog $log)
    {
        $pdf = Pdf::loadView('docs.decision_log_pdf', ['log' => $log]);
        return $pdf->download('decision-log-' . $log->id . '.pdf');
    }

    public function exportBulkDecisionLogs(Request $request)
    {
        $request->validate([
            'ids' => 'required|string'
        ]);

        $ids = json_decode($request->ids, true);
        $logs = DecisionLog::whereIn('id', $ids)->get();

        if ($logs->isEmpty()) {
            return redirect()->back()->with('error', 'No decision logs found to export.');
        }

        $pdf = Pdf::loadView('docs.bulk_decision_logs_pdf', ['logs' => $logs]);
        return $pdf->download('decision-logs-bulk-' . now()->format('Y-m-d') . '.pdf');
    }

    public function bulkDeleteDecisionLogs(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:decision_logs,id'
        ]);

        try {
            $deletedCount = DecisionLog::whereIn('id', $request->ids)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} decision log(s)."
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting decision logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete decision logs. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a new document for the specified menu categories
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDocument(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string|in:compliance,sop_qa,test_exceptions,audit_readiness,knowledge_transfers',
                'content' => 'required|string',
                'tags' => 'nullable|string',
                'parent_id' => 'nullable|exists:documents_manager,id'
            ]);

            // Process tags if provided
            if ($request->filled('tags')) {
                $validatedData['tags'] = array_map('trim', explode(',', $request->tags));
            }

            // Set author_id to current user
            $validatedData['author_id'] = auth()->id();

            // Set position for new document (last in its level)
            $maxPosition = DocumentManager::where('parent_id', $request->parent_id)
                ->where('category', $request->category)
                ->max('position');
            $validatedData['position'] = ($maxPosition ?? -1) + 1;

            // Create the document
            $document = DocumentManager::create($validatedData);

            // Redirect to the category page with a success message
            return redirect()->route('documents.' . $validatedData['category'])
                ->with('success', 'Document created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            \Log::error('Error creating document: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create document. Please try again.']);
        }
    }

    /**
     * Delete a document
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDocument($id)
    {
        try {
            $document = DocumentManager::findOrFail($id);
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully!'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document. Please try again.'
            ], 500);
        }
    }

    /**
     * Get a document for viewing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocument($id)
    {
        try {
            $document = DocumentManager::with('reviewers')->findOrFail($id);

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'content' => $document->content,
                    'category' => $document->category,
                    'tags' => $document->tags,
                    'state' => $document->state,
                    'parent_id' => $document->parent_id,
                    'reviewers' => $document->reviewers->pluck('id')->toArray(),
                    'created_at' => $document->created_at->format('M d, Y \a\t g:i A'),
                    'updated_at' => $document->updated_at->format('M d, Y \a\t g:i A'),
                    'is_updated' => $document->updated_at != $document->created_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error fetching document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch document. Please try again.'
            ], 500);
        }
    }

    /**
     * Approve a document (single approval is enough)
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveDocument($id)
    {
        $document = DocumentManager::findOrFail($id);
        $user = auth()->user();

        $isReviewer = $document->reviewers->contains($user->id);
        $isAdmin = $user->role === 'Administrator' || $user->role === 'admin';

        if ($isReviewer || $isAdmin) {
            $document->state = 'approved';
            $document->last_edited_by_id = $user->id;
            $document->save();
            return back()->with('success', 'Document approved successfully!');
        } else {
            return back()->with('error', 'You are not authorized to approve this document.');
        }
    }

    /**
     * Update the tree structure of documents
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTree(Request $request)
    {
        try {
            $tree = $request->input('tree');
            
            if (!$tree || !is_array($tree)) {
                return response()->json(['success' => false, 'message' => 'Invalid tree structure']);
            }

            $this->updateTreeStructure($tree, null);

            return response()->json(['success' => true, 'message' => 'Tree structure updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating tree structure: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating tree structure']);
        }
    }

    /**
     * Recursively update the tree structure
     *
     * @param array $items
     * @param int|null $parentId
     */
    private function updateTreeStructure($items, $parentId)
    {
        foreach ($items as $index => $item) {
            $document = DocumentManager::find($item['id']);
            if ($document) {
                $document->parent_id = $parentId;
                $document->position = $index;
                $document->save();

                // Update children recursively
                if (isset($item['children']) && is_array($item['children'])) {
                    $this->updateTreeStructure($item['children'], $item['id']);
                }
            }
        }
    }

    /**
     * Get all descendant IDs of a document
     *
     * @param int $documentId
     * @return array
     */
    private function getAllDescendants($documentId)
    {
        $descendants = [];
        $children = DocumentManager::where('parent_id', $documentId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $descendants[] = $childId;
            $descendants = array_merge($descendants, $this->getAllDescendants($childId));
        }
        
        return $descendants;
    }

    /**
     * Get parent options for a document (excluding itself and descendants)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParentOptions($id)
    {
        try {
            $document = DocumentManager::findOrFail($id);
            $descendants = $this->getAllDescendants($id);
            $excludeIds = array_merge([$id], $descendants);

            // Get all documents in the same category, excluding the document and its descendants
            $availableParents = DocumentManager::where('category', $document->category)
                ->whereNotIn('id', $excludeIds)
                ->orderBy('title')
                ->get(['id', 'title', 'parent_id']);

            return response()->json([
                'success' => true,
                'options' => $availableParents
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error getting parent options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get parent options. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an existing document
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDocument(Request $request, $id)
    {
        try {
            $document = DocumentManager::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string|in:compliance,sop_qa,test_exceptions,audit_readiness,knowledge_transfers',
                'content' => 'required|string',
                'tags' => 'nullable|string',
                'state' => 'required|string|in:draft,approved',
                'reviewers' => 'nullable|array',
                'reviewers.*' => 'exists:users,id',
                'parent_id' => 'nullable|exists:documents_manager,id'
            ]);

            // Prevent circular references (document cannot be its own parent or descendant)
            if ($request->filled('parent_id')) {
                $parentId = $request->parent_id;
                
                // Check if trying to set itself as parent
                if ($parentId == $id) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['parent_id' => 'A document cannot be its own parent.']);
                }
                
                // Check if trying to set a descendant as parent (would create circular reference)
                $descendants = $this->getAllDescendants($id);
                if (in_array($parentId, $descendants)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['parent_id' => 'Cannot set a child document as parent (would create circular reference).']);
                }
            }

            // Process tags if provided
            if ($request->filled('tags')) {
                $validatedData['tags'] = array_map('trim', explode(',', $request->tags));
            }

            // Set last_edited_by_id to current user
            $validatedData['last_edited_by_id'] = auth()->id();

            // Update the document
            $document->update($validatedData);

            // Update reviewers if provided
            if ($request->has('reviewers')) {
                $document->reviewers()->sync($request->reviewers);
            } else {
                $document->reviewers()->detach();
            }

            // Redirect to the category page with a success message
            return redirect()->route('documents.' . $validatedData['category'])
                ->with('success', 'Document updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Document not found.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            \Log::error('Error updating document: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update document. Please try again.']);
        }
    }
}
