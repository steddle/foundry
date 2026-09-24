@props(['title', 'description'])

<foundry:layouts.site :$title :$description {{ $attributes }}>
    {{ $slot }}
</foundry:layouts.site>
