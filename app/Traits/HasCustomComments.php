<?php

namespace App\Traits;

use App\Models\CourrierComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomComments
{
    /**
     * Relation vers les commentaires
     */
    public function courrierComments(): MorphMany
    {
        return $this->morphMany(CourrierComment::class, 'commentable')
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir tous les commentaires parents (pas les réponses)
     */
    public function parentComments(): MorphMany
    {
        return $this->courrierComments()
            ->parents()
            ->withReplies();
    }

    /**
     * Obtenir le nombre total de commentaires
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->courrierComments()->count();
    }

    /**
     * Obtenir le nombre de commentaires parents uniquement
     */
    public function getParentCommentsCountAttribute(): int
    {
        return $this->courrierComments()->parents()->count();
    }

    /**
     * Ajouter un nouveau commentaire
     */
    public function addComment(string $content, User $user, ?int $parentId = null): CourrierComment
    {
        // Extraire les mentions du contenu
        $comment = new CourrierComment();
        $mentions = $comment->extractMentions($content);

        $comment = $this->courrierComments()->create([
            'content' => $content,
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'mentions' => $mentions,
        ]);

        // Notifier les utilisateurs mentionnés
        $comment->notifyMentionedUsers();

        return $comment->load('user');
    }

    /**
     * Modifier un commentaire existant
     */
    public function updateComment(CourrierComment $comment, string $content, User $user): bool
    {
        if (!$comment->canBeEditedBy($user)) {
            return false;
        }

        // Extraire les nouvelles mentions
        $mentions = $comment->extractMentions($content);

        $updated = $comment->update([
            'content' => $content,
            'mentions' => $mentions,
        ]);

        if ($updated) {
            $comment->markAsEdited();
            $comment->notifyMentionedUsers();
        }

        return $updated;
    }

    /**
     * Supprimer un commentaire
     */
    public function deleteComment(CourrierComment $comment, User $user): bool
    {
        if (!$comment->canBeDeletedBy($user)) {
            return false;
        }

        return $comment->delete();
    }

    /**
     * Obtenir les commentaires avec pagination
     */
    public function getCommentsPaginated(int $perPage = 10)
    {
        return $this->parentComments()
            ->paginate($perPage);
    }

    /**
     * Vérifier si l'utilisateur peut commenter
     */
    public function canUserComment(User $user): bool
    {
        // Ici vous pouvez ajouter votre logique de permissions
        // Par exemple, vérifier si l'utilisateur a le droit de commenter
        return true; // Par défaut, tous les utilisateurs peuvent commenter
    }

    /**
     * Obtenir les utilisateurs disponibles pour les mentions
     */
    public function getMentionableUsers(): \Illuminate\Database\Eloquent\Collection
    {
        // Retourner tous les utilisateurs actifs
        // Vous pouvez filtrer selon vos besoins
        return User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Rechercher dans les commentaires
     */
    public function searchComments(string $query)
    {
        return $this->courrierComments()
            ->where('content', 'like', "%{$query}%")
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}