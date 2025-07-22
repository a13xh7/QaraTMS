<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRunsAttachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{

    /**
     * Get the HTML content for the attachment modal.
     *
     * @param  int  $attachmentId The ID of the attachment.
     * @return \Illuminate\Http\Response
     */
    public function getModalContent($attachmentId)
    {
        $attachment = TestRunsAttachment::find($attachmentId);

        if (!$attachment) {
            return response('Attachment not found', 404);
        }

        $url = $attachment->public_url ?? null;
        $thumbnailUrl = $attachment->thumbnail_url ?? null;

        if (!$url) {
             return response('Attachment URL is invalid', 404);
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'mkv'];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        $isImage = in_array(strtolower($extension), $imageExtensions);
        $isVideo = in_array(strtolower($extension), $videoExtensions);

        $htmlContent = '';

        if ($isImage) {
            $htmlContent = "<img src='{$url}' alt='Attachment' class='img-fluid mx-auto d-block'>";
        } elseif ($isVideo) {
            $htmlContent = "<video src='{$url}' controls class='img-fluid mx-auto d-block'>";
            $htmlContent .= "Browser does not support the video tag.</video>";
        } else {
             $htmlContent = "<p>File type not supported for preview: <strong>{$extension}</strong></p><p><a href='{$url}' target='_blank'>Download File</a></p>";
        }
        
        return response($htmlContent, 200);
    }
} 
