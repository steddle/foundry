@props(['title', 'lead' => null, 'as' => 'section'])

{{--
    One part of a signed-in page on a sheet lifted off it: its title, a
    sentence on it, and what it holds under them, a form, rows or a button.
    A settings page is a run of them in one column.

    @group Layout
    @prop title The part's heading, at heading-3.
    @prop lead A sentence on what it holds.
    @prop as The element: `section`, or `form` where the whole sheet submits.
    @slot slot What it holds.

    @example A setting and its save
    <foundry:panel title="Profile" lead="Used as the default sender on new agreements." as="form" class="max-w-3xl">
        <div class="divide-y divide-zinc-200 border-t border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            <foundry:field-row label="Name" for="panel-name">
                <flux:input id="panel-name" value="Ada Visser" />
            </foundry:field-row>
        </div>
        <foundry:actions>
            <foundry:button type="button">Save</foundry:button>
        </foundry:actions>
    </foundry:panel>

    @example Deleting an account
    <foundry:panel title="Delete account" lead="Your account and every connected assistant's access go at once." class="max-w-3xl items-start">
        <foundry:confirm-button label="Delete account" action="$el.dataset.done = 'yes'" />
    </foundry:panel>
--}}
<{{ $as }} {{ $attributes->class('flex flex-col gap-6 rounded-lg bg-zinc-25 dark:bg-zinc-800 p-5 shadow-paper sm:p-8') }}>
    <div class="flex flex-col gap-1">
        <foundry:heading size="3" level="2">{{ $title }}</foundry:heading>
        @if ($lead)
            <foundry:text variant="small" tone="muted">{{ $lead }}</foundry:text>
        @endif
    </div>

    {{ $slot }}
</{{ $as }}>
