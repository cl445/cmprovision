<?php
/*
 * Handling file upload with normal controller. As livewire still has some issues with large files
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Image;
use App\Jobs\ComputeSHA256;
use Exception;

class AddImageController extends Controller
{
    public function __construct()
    {
        $this->extendFileExtensionValidator();
    }

    public function store(Request $request)
    {
        @set_time_limit(86400);
        @ignore_user_abort(true);

        $validatedData = $this->validateFileUpload($request);

        $image = $this->saveFileUpload($validatedData['image']);

        /* Queue SHA256 calculation job */
        ComputeSHA256::dispatch($image);

        return $this->generateResponse($request, $image);
    }

    private function extendFileExtensionValidator()
    {
        Validator::extend('file_extension',
            function($attribute, $value, $parameters, $validator) {
                if (!$value instanceof UploadedFile) {
                    return false;
                }

                $extension = $value->getClientOriginalExtension();
                return $extension != '' && in_array($extension, $parameters);
            },
            "Only .gz, .bz2, .zst and .xz images are supported"
        );
    }

    private function validateFileUpload(Request $request)
    {
        return $request->validate([
            'image' => 'required|file|file_extension:gz,bz2,zst,xz',
        ]);
    }

    private function saveFileUpload($uploadedImage)
    {
        $image = new Image;
        $image->filename = $uploadedImage->getClientOriginalName();
        $image->filename_extension = $uploadedImage->getClientOriginalExtension();
        $image->sha256 = '';

        do {
            $image->filename_on_server = Str::random(40) . "." . $image->filename_extension;
        } while ( file_exists($image->imagepath()) );

        try {
            $uploadedImage->move(public_path("uploads"), $image->filename_on_server);
            $image->save();
        } catch (Exception $e) {
            Log::error('File upload error: '.$e->getMessage());
        }


        return $image;
    }

    private function generateResponse(Request $request, $image)
    {
        if ($request->wantsJson())
        {
            return $image;
        }
        else
        {
            session()->flash('message', 'Image added.');
            return redirect()->route('images');
        }
    }
}
