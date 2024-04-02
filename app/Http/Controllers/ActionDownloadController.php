<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActionDownloadController extends Controller
{
    public function download(Request $request)
    {
        $fileName = $request->filename;

        $fileExt = \File::extension($fileName);

        $filePath = '';

        if ($fileExt == "pdf") {
            $filePath = config('filesystems.invoice') . '/';
        }
        elseif ($fileExt == "csv") {
            $filePath = config('filesystems.csv') . '/';;
        }

        $filePath = $filePath . $fileName;

        try {
            return Storage::disk('public')->download($filePath, $fileName);
        } catch (\Exception $e) {
            abort(404);
        }

    }
}
