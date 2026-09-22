{{--
    Flux's toast group, persisted across wire:navigate so a toast outlives the
    page that raised it. Every document that shows a toast sets it once.

    @group Shell

    @example Before the page's scripts
    @code
    <foundry:toasts />
    @fluxScripts
--}}
@persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
@endpersist
