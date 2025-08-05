<?php

namespace App\Policies;

use Coolsam\NestedComments\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tout le monde peut voir les commentaires
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return true; // Tout le monde peut voir un commentaire spécifique
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Tout utilisateur connecté peut créer des commentaires
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Un utilisateur peut modifier son propre commentaire ou si c'est un admin
        return $comment->user_id === $user->id || $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Un utilisateur peut supprimer son propre commentaire ou si c'est un admin
        return $comment->user_id === $user->id || $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $user->hasRole('super_admin'); // Seuls les admins peuvent restaurer
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->hasRole('super_admin'); // Seuls les admins peuvent supprimer définitivement
    }
}
