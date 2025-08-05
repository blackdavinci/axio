<?php

namespace App\Livewire;

use Coolsam\NestedComments\Livewire\CommentCard;
use Coolsam\NestedComments\Models\Comment;
use Filament\Notifications\Notification;

class CustomCommentCard extends CommentCard
{
    public bool $isEditing = false;
    public string $editContent = '';

    public function editComment()
    {
        // Vérifier les permissions
        if (!auth()->user()->can('update', $this->comment)) {
            Notification::make()
                ->title('Permission refusée')
                ->body('Vous ne pouvez pas modifier ce commentaire.')
                ->danger()
                ->send();
            return;
        }

        $this->isEditing = true;
        $this->editContent = $this->comment->body;
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->editContent = '';
    }

    public function saveEdit()
    {
        // Validation
        $this->validate([
            'editContent' => 'required|string|min:1|max:2000',
        ]);

        // Vérifier les permissions
        if (!auth()->user()->can('update', $this->comment)) {
            Notification::make()
                ->title('Permission refusée')
                ->body('Vous ne pouvez pas modifier ce commentaire.')
                ->danger()
                ->send();
            return;
        }

        try {
            // Mettre à jour le commentaire
            $this->comment->update([
                'body' => $this->editContent,
            ]);

            $this->isEditing = false;
            $this->editContent = '';

            Notification::make()
                ->title(__('nested-comments.comment_updated'))
                ->body('Votre commentaire a été modifié avec succès.')
                ->success()
                ->send();

            // Rafraîchir le composant
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de la modification.')
                ->danger()
                ->send();
        }
    }

    public function deleteComment()
    {
        // Vérifier les permissions
        if (!auth()->user()->can('delete', $this->comment)) {
            Notification::make()
                ->title('Permission refusée')
                ->body('Vous ne pouvez pas supprimer ce commentaire.')
                ->danger()
                ->send();
            return;
        }

        try {
            // Supprimer le commentaire
            $this->comment->delete();

            Notification::make()
                ->title(__('nested-comments.comment_deleted'))
                ->body('Le commentaire a été supprimé avec succès.')
                ->success()
                ->send();

            // Rafraîchir le composant parent
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('nested-comments.error_occurred'))
                ->body('Une erreur est survenue lors de la suppression.')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.custom-comment-card');
    }
}
