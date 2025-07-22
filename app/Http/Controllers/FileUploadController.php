<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToSetVisibility;

class FileUploadController extends Controller
{
    public function uploadFileToCloud(Request $request)
    {
        try {
            $uploadedFiles = [];
            if (!$request->hasFile('files')) {
                return response()->json([
                    'message' => 'No files were uploaded.',
                ], 400);
            }

            $files = $request->file('files');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                // Generate random string for filename
                $randomString = str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(12 / 52));
                $randomString = str_shuffle($randomString);
                $randomString = substr($randomString, 1, 12);

                $file_name = time() . '_' .
                    $randomString . '.' .
                    $file->getClientOriginalExtension();

                $storeFile = $file->storeAs("evidence", $file_name, "gcs");
                $disk = Storage::disk('gcs');
                $uploadedFiles[] = $disk->url($storeFile);
            }
        } catch (UnableToWriteFile | UnableToSetVisibility $e) {
            return response()->json([
                'data' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'data' => $uploadedFiles,
        ], 201);
    }
}