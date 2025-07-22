<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Repository;
use App\Models\Suite;
use App\Models\TestCase;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
  public function import(Request $request)
  {
    $user = $request->user();
    $createdBy = $user->email;

    $file = $request->file('file');

    if (!$file) {
      return response()->json(['error' => 'No file uploaded'], 400);
    }

    if ($file->getClientOriginalExtension() !== 'csv') {
      return response()->json(['error' => 'File must be a CSV'], 400);
    }

    // get squad nam from filename
    $filename = $file->getClientOriginalName();
    $filenameParts = explode('_', pathinfo($filename, PATHINFO_FILENAME));

    if (count($filenameParts) < 2) {
      return response()->json(['error' => 'Invalid filename format. Expected format: squad_suite[_subsuite]'], 400);
    }

    $squadName = str_replace('-', ' ', $filenameParts[0]);
    $suiteName = str_replace('-', ' ', $filenameParts[1]);

    // get repositoryId based on squad name
    $repository = Repository::where('title', $squadName)->first();

    if (!$repository) {
      return response()->json(['error' => 'Repository not found for squad: ' . $squadName . ' by ' . $createdBy], 404);
    }
    $repositoryId = $repository->id;
    $suite = new Suite();

    $suiteIdAvailable = $suite::where('repository_id', $repositoryId)->where('title', $suiteName)->first();
    $suiteId = !$suiteIdAvailable ? $this->createSuite($suiteName, $repositoryId) : $suiteIdAvailable->id;

    $subSuiteId = null;
    if (count($filenameParts) == 3) {
      $subSuiteName = str_replace('-', ' ', $filenameParts[2]);
      $subSuiteIdAvailable = $suite::where('repository_id', $repositoryId)
        ->where('title', $subSuiteName)
        ->first();
      $subSuiteId = !$subSuiteIdAvailable
        ? $this->createSubSuite($subSuiteName, $repositoryId, $suiteId)
        : $subSuiteIdAvailable->id;
    }

    if ($subSuiteId !== null) {
      $suiteId = $subSuiteId;
    }

    $handle = fopen($file->getPathname(), 'r');

    // Array to store all test cases
    $testCases = [];

    // Skip the header row
    fgetcsv($handle);

    while (($data = fgetcsv($handle)) !== false) {

      // Validate required fields
      print_r($data);
      if (empty($data[0]) || empty($data[1])) {
        return response()->json(['error' => 'Title and description are required fields'], 400);
      }

      if (empty($data[5]) || empty($data[6])) {
        return response()->json(['error' => 'Preconditions and scenarios are required fields'], 400);
      }

      // if (empty($data[8])) {
      //   return response()->json(['error' => 'Epic link is required fields'], 400);
      // }

      $preconditions = $data[5] ?? "";
      $scenarios = $data[6] ?? "";

      $dataPreconScenarios = "{\"preconditions\":\"<p>$preconditions</p>\",\"scenarios\":\"<p>$scenarios</p>\"}";
      $testCases[] = [
        'title' => $data[0],
        'description' => $data[1] ?? '',
        'labels' => !empty($data[2]) ? $data[2] : "None",
        'automated' => $data[3] ?? 0,
        'priority' => $data[4] ?? 2,
        'suite_id' => $suiteId,
        'data' => $dataPreconScenarios,
        'order' => 0,
        'regression' => $data[7] ?? 1,
        'epic_link' => !empty($data[8]) ? $data[8] : null,
        'linked_issue' => !empty($data[9]) ? $data[9] : null,
        'jira_key' => !empty($data[10]) ? $data[10] : null,
        'platform' => !empty($data[11]) ? $data[11] : '{"android":true,"ios":true,"web":false,"mweb":false}',
        'release_version' => !empty($data[12]) ? $data[12] : null,
        'severity' => !empty($data[13]) ? $data[13] : 'Moderate',
        'created_by' => $user->name,
        'updated_by' => $user->name,
        'created_at' => now(),
        'updated_at' => now(),
      ];

      // Insert in chunks of 1000 to avoid memory issues
      if (count($testCases) >= 1000) {
        TestCase::insert($testCases);
        $testCases = [];
      }
    }

    // Insert any remaining records
    if (!empty($testCases)) {
      TestCase::insert($testCases);
    }

    fclose($handle);

    return response()->json([
      'message' => 'Import completed successfully'
    ]);
  }

  public function createSuite(string $suiteName, string $repositoryId)
  {
    $suite = new Suite();
    $suite->repository_id = $repositoryId;
    $suite->parent_id = null;
    $suite->title = $suiteName;
    $suite->save();
    return $suite->id;
  }

  public function createSubSuite(string $subSuiteName, string $repositoryId, string $suiteId)
  {
    $suite = new Suite();
    $suite->repository_id = $repositoryId;
    $suite->parent_id = $suiteId;
    $suite->title = $subSuiteName;
    $suite->save();
    return $suite->id;
  }

  // Push data to log database
  public function pushToLogDatabase(string $action, string $feature, Request $request)
  {
    $user = Auth::user();
    $email = $user->email;

    $log = new Log();
    $log->user = $email;
    $log->action = $action;
    $log->feature = $feature;
    $log->request_data = json_encode($request->all());
    $log->created_at = now();
    $log->updated_at = now();
    $log->save();
  }

  /**
   * Helper method to properly escape CSV fields
   */
  private function str_putcsv($value) {
    // Convert null to empty string
    if ($value === null) {
        $value = '';
    }
    
    // Convert to string
    $value = (string)$value;
    
    // If value contains comma, newline or double quote, needs to be quoted
    if (preg_match('/[,"\r\n]/', $value)) {
        $value = '"' . str_replace('"', '""', $value) . '"';
    }
    return $value;
  }

  public function export(Request $request)
  {
    
    $project = Project::where('title', $request->input('project'))->first()->id;
    $repository = Repository::where('title', $request->input('squad'))->first()->id;
    $squadName = $request->input('squad');
    $suite = Suite::where('title', $request->input('feature'))->first()->id;
    $feature = $request->input('feature');
    $subFeature = $request->input('sub_feature') ?? '';
    $subSuite = null;
    if ($request->input('sub_feature') !== null && $request->input('sub_feature') !== '') {
      $subSuite = Suite::where('title', $request->input('sub_feature'))->first()->id;
    }

    $testCases = TestCase::where('suite_id', $suite)->get();

    // Initialize string buffer for CSV content
    $csvContent = '';
    
    // Create headers row
    $headers = [
        'ID',
        'Squad',
        'Feature',
        'Sub Feature',
        'Title',
        'Description',
        'Labels',
        'Automated',
        'Priority',
        'Preconditions',
        'Scenarios',
        'Regression',
        'Epic Link',
        'Jira Key',
        'Platform',
        'Release Version',
        'Severity',
        'Created By',
        'Updated By',
        'Created At',
        'Updated At'
    ];
    
    // Add headers to CSV
    $csvContent .= implode(',', array_map([$this, 'str_putcsv'], $headers)) . "\n";

    // Add data rows
    foreach ($testCases as $case) {
        $data = json_decode($case->data, true);
        $platform = json_decode($case->platform, true);
        
        $row = [
            $case->id,
            $squadName,
            $feature,
            $subFeature,
            $case->title,
            $case->description,
            $case->labels,
            $case->automated,
            $case->priority,
            strip_tags($data['preconditions'] ?? ''),
            strip_tags($data['scenarios'] ?? ''),
            $case->regression,
            $case->epic_link,
            $case->jira_key,
            json_encode($platform),
            $case->release_version,
            $case->severity,
            $case->created_by,
            $case->updated_by,
            $case->created_at,
            $case->updated_at
        ];
        
        $csvContent .= implode(',', array_map([$this, 'str_putcsv'], $row)) . "\n";
    }

    // Return response
    return response($csvContent)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="test_cases_export_' . date('Y-m-d_His') . '.csv"');
  }
}
