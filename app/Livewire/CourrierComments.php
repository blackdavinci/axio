<?php

namespace App\Livewire;

use App\Models\Courrier;
use App\Models\CourrierComment;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class CourrierComments extends Component
{
    use WithPagination, AuthorizesRequests;

    public $courrier;
    public $newComment = '';
    public $replyTo = null;
    public $replyContent = '';
    public $editingComment = null;
    public $editContent = '';
    public $showUsersList = false;
    public $mentionQuery = '';

    protected $listeners = [
        'commentAdded' => '$refresh',
        'commentUpdated' => '$refresh',
        'commentDeleted' => '$refresh',
    ];

    public function mount(Courrier $courrier)
    {
        $this->courrier = $courrier;
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:1|max:2000',
        ]);

        if (!$this->courrier->canUserComment(auth()->user())) {
            session()->flash('error', 'Vous n\'avez pas la permission de commenter.');
            return;
        }

        $this->courrier->addComment($this->newComment, auth()->user());

        $this->newComment = '';
        session()->flash('success', 'Commentaire ajouté avec succès.');
        $this->dispatch('commentAdded');
    }

    public function replyToComment($commentId)
    {
        $this->replyTo = $commentId;
        $this->replyContent = '';
    }

    public function cancelReply()
    {
        $this->replyTo = null;
        $this->replyContent = '';
    }

    public function submitReply()
    {
        $this->validate([
            'replyContent' => 'required|string|min:1|max:2000',
        ]);

        if (!$this->courrier->canUserComment(auth()->user())) {
            session()->flash('error', 'Vous n\'avez pas la permission de commenter.');
            return;
        }

        $this->courrier->addComment($this->replyContent, auth()->user(), $this->replyTo);

        $this->cancelReply();
        session()->flash('success', 'Réponse ajoutée avec succès.');
        $this->dispatch('commentAdded');
    }

    public function editComment($commentId)
    {
        $comment = CourrierComment::find($commentId);
        
        if (!$comment || !$comment->canBeEditedBy(auth()->user())) {
            session()->flash('error', 'Vous ne pouvez pas modifier ce commentaire.');
            return;
        }

        $this->editingComment = $commentId;
        $this->editContent = $comment->content;
    }

    public function cancelEdit()
    {
        $this->editingComment = null;
        $this->editContent = '';
    }

    public function updateComment()
    {
        $this->validate([
            'editContent' => 'required|string|min:1|max:2000',
        ]);

        $comment = CourrierComment::find($this->editingComment);
        
        if (!$comment || !$comment->canBeEditedBy(auth()->user())) {
            session()->flash('error', 'Vous ne pouvez pas modifier ce commentaire.');
            return;
        }

        $updated = $this->courrier->updateComment($comment, $this->editContent, auth()->user());

        if ($updated) {
            $this->cancelEdit();
            session()->flash('success', 'Commentaire modifié avec succès.');
            $this->dispatch('commentUpdated');
        } else {
            session()->flash('error', 'Erreur lors de la modification du commentaire.');
        }
    }

    public function deleteComment($commentId)
    {
        $comment = CourrierComment::find($commentId);
        
        if (!$comment || !$comment->canBeDeletedBy(auth()->user())) {
            session()->flash('error', 'Vous ne pouvez pas supprimer ce commentaire.');
            return;
        }

        $deleted = $this->courrier->deleteComment($comment, auth()->user());

        if ($deleted) {
            session()->flash('success', 'Commentaire supprimé avec succès.');
            $this->dispatch('commentDeleted');
        } else {
            session()->flash('error', 'Erreur lors de la suppression du commentaire.');
        }
    }

    public function searchMentions($query)
    {
        $this->mentionQuery = $query;
        $this->showUsersList = !empty($query);
    }

    public function addMention($username)
    {
        $this->newComment .= "@{$username} ";
        $this->showUsersList = false;
        $this->mentionQuery = '';
    }

    public function getMentionableUsersProperty()
    {
        if (empty($this->mentionQuery)) {
            return collect();
        }

        return User::where('name', 'like', "%{$this->mentionQuery}%")
            ->limit(10)
            ->get(['id', 'name']);
    }

    public function render()
    {
        $comments = $this->courrier->parentComments()
            ->with(['replies.user', 'user'])
            ->paginate(10);

        return view('livewire.courrier-comments', [
            'comments' => $comments,
            'mentionableUsers' => $this->mentionableUsers,
        ]);
    }
}
