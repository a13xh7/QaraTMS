<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mockery\Exception;

class FilesController extends Controller
{

    public static function saveImagesAndGetCleanCode($data)
    {
        $content = $data;
        $dom = new \DOMDocument();
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imageFiles = $dom->getElementsByTagName('img');

        foreach($imageFiles as $item => $image){
            $data = $image->getAttribute('src');

            try {
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $imgeData = base64_decode($data);
            } catch (\ErrorException $e) {
                continue;
            }

            $image_name= "/media/" . time().$item.'.png';
            $path = public_path() . $image_name;
            file_put_contents($path, $imgeData);

            $image->removeAttribute('src');
            $image->setAttribute('src', $image_name);
        }

        $content = $dom->saveHTML();

        return $content;
    }


    public function imgupload(Request $request)
    {
        if (isset($_FILES['file']['name'])) {

            if (!$_FILES['file']['error']) {
                $name = rand(100,1000).'_'.date('Ymd');
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $filename = $name.'.'.$ext;

                $destination = public_path() .'/media/'.$filename; //change this directory
                $location = $_FILES["file"]["tmp_name"];

                move_uploaded_file($location, $destination);

                return '/media/'.$filename;
            } else {
                echo 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
            }
        }

//        if ($request->hasFile('upload')) {
//            $originName = $request->file('upload')->getClientOriginalName();
//            $fileName = pathinfo($originName, PATHINFO_FILENAME);
//            $extension = $request->file('upload')->getClientOriginalExtension();
//            $fileName = $fileName . '_' . time() . '.' . $extension;
//
//            $request->file('upload')->move(public_path('media'), $fileName);
//
//            $url = asset('media/' . $fileName);
//            return response()->json(['fileName' => $fileName, 'uploaded'=> 1, 'url' => $url]);
//        }
    }
}
