@props(['heading', 'expanded' => true])

{{--
    Links in foundry:app.sidebar that fold under a heading, over
    flux:sidebar.group.

    @prop heading What the group is called, and the button that folds it.
    @prop expanded Whether it opens unfolded.
--}}
<flux:sidebar.group expandable :$heading :$expanded {{ $attributes->class('grid') }}>
    {{ $slot }}
</flux:sidebar.group>
