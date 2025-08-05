@props([
    'record'
])
@if(isset($record))
    @if(app(\Coolsam\NestedComments\NestedComments::class)->classHasTrait($record, \Coolsam\NestedComments\Concerns\HasReactions::class))
        <livewire:nested-comments::reaction-panel :record="$record"/>
    @else
        <p>__('The current record is not configured for reactions. Please include the `HasReactions` trait to the model.')</p>
    @endif
@endif