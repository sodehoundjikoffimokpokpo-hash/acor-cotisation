<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $disk = Storage::disk('local');
    // On ne récupère que les fichiers qui finissent par .zip
    $files = collect($disk->allFiles())->filter(function ($file) {
        return str_ends_with($file, '.zip');
    });

    return view('admin.backups', compact('files'));
    }

    public function download($file)
    {
        // Assainir le nom de fichier (retirer toute tentative de remontée de répertoire)
        $file = basename($file);

        if (!str_ends_with($file, '.zip')) {
            abort(403, 'Format de fichier non autorisé.');
        }

        // Vérification de sécurité pour s'assurer que le fichier existe
        if (!Storage::disk('local')->exists($file)) {
            abort(404, 'Le fichier de sauvegarde est introuvable.');
        }

        return Storage::disk('local')->download($file);
    }
}
