<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function destroy(Document $document, Request $request)
    {
        // Удаляем файл с диска (если существует)
        if ($document->path) {
            Storage::disk('public')->delete($document->path);
        }

        $document->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Документ удалён.');
    }
}
