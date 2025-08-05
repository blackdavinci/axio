@if(isset($record))
    <livewire:nested-comments::comments :record="$record" />
@else
    <p>No Commentable record set.</p>
@endif