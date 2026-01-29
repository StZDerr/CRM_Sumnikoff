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

    /**
     * Скачать документ — отдаёт файл с оригинальным именем и корректным mime
     */
    public function download(Document $document)
    {
        $disk = Storage::disk('public');
        if (! $document->path || ! $disk->exists($document->path)) {
            abort(404);
        }

        $fullPath = $disk->path($document->path);

        $filename = $document->original_name ?? basename($document->path);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Улучшенное определение MIME по расширению (на случай, если загрузчик пометил docx как application/zip)
        $mimeMap = [
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        $mime = $document->mime ?? null;
        if (isset($mimeMap[$ext])) {
            $mime = $mimeMap[$ext];
        }

        return response()->download(
            $fullPath,
            $filename,
            ['Content-Type' => $mime ?? 'application/octet-stream']
        );
    }
}
