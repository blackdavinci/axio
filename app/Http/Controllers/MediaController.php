<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Afficher un fichier média (pour prévisualisation)
     */
    public function show(Media $media)
    {
        // Vérifier les permissions - seuls les utilisateurs authentifiés peuvent voir les médias
        if (!auth()->check()) {
            abort(403);
        }

        // Vérifier que l'utilisateur a accès au courrier associé
        $this->authorizeMediaAccess($media);

        $pathToFile = $media->getPath();
        
        if (!file_exists($pathToFile)) {
            abort(404);
        }

        return response()->file($pathToFile, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Télécharger un fichier média
     */
    public function download(Media $media)
    {
        // Vérifier les permissions
        if (!auth()->check()) {
            abort(403);
        }

        // Vérifier que l'utilisateur a accès au courrier associé
        $this->authorizeMediaAccess($media);

        $pathToFile = $media->getPath();
        
        if (!file_exists($pathToFile)) {
            abort(404);
        }

        return response()->download($pathToFile, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }

    /**
     * Vérifier l'accès au média en fonction du modèle associé
     */
    private function authorizeMediaAccess(Media $media)
    {
        $model = $media->model;
        
        // Si c'est un média de courrier, on vérifie l'accès au courrier
        if ($model instanceof \App\Models\Courrier) {
            // Pour l'instant, tous les utilisateurs authentifiés peuvent voir les courriers
            // Vous pouvez ajouter des vérifications plus spécifiques ici
            return true;
        }
        
        // Si c'est un média d'assignation, on vérifie l'accès à l'assignation
        if ($model instanceof \App\Models\CourrierAssignment) {
            // Vérifier que l'utilisateur peut voir l'assignation
            // (soit il est assigné, soit il a assigné, soit il a les permissions)
            $user = auth()->user();
            
            if ($model->user_id === $user->id || 
                $model->assigned_by_user_id === $user->id ||
                $user->can('view_any_courrier')) {
                return true;
            }
            
            abort(403, 'Vous n\'avez pas accès à ce fichier.');
        }
        
        return true;
    }
}
