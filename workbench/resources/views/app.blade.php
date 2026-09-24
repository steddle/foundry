{{-- A page of a signed-in app under foundry:layouts.app.sidebar, as an imprint's own layout sets it. --}}
<foundry:layouts.app.sidebar title="Overview">
    <x-slot:sidebar>
        <foundry:app.sidebar :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']" home="/app">
            <x-slot:search>
                <foundry:app.sidebar.search kbd="⌘K" />
            </x-slot:search>

            <foundry:app.sidebar.item href="/app" icon="home">Overview</foundry:app.sidebar.item>
            <foundry:app.sidebar.item href="#" icon="inbox" count="12">Inbox</foundry:app.sidebar.item>
            <foundry:app.sidebar.item href="#" icon="building-office" count="4">Companies</foundry:app.sidebar.item>

            <foundry:app.sidebar.group heading="Board">
                <foundry:app.sidebar.item href="#">Q3 update</foundry:app.sidebar.item>
                <foundry:app.sidebar.item href="#">Minutes, 12 Sep</foundry:app.sidebar.item>
            </foundry:app.sidebar.group>

            <foundry:app.sidebar.group heading="Legal" :expanded="false">
                <foundry:app.sidebar.item href="#">Shareholders' agreement</foundry:app.sidebar.item>
            </foundry:app.sidebar.group>

            <x-slot:account>
                <flux:menu.item href="#" icon="cog-6-tooth">Settings</flux:menu.item>
            </x-slot:account>
        </foundry:app.sidebar>
    </x-slot:sidebar>

    <foundry:container class="flex flex-col gap-10 py-10">
        <foundry:page-head eyebrow="Overview" title="Good morning, Ada." lead="Four companies, twelve items waiting." />

        <foundry:rows>
            <foundry:record-row href="#" title="Northwind B.V." meta="Board meeting on 2 Oct" />
            <foundry:record-row href="#" title="Contoso Labs" meta="Q3 figures due Friday" />
        </foundry:rows>
    </foundry:container>
</foundry:layouts.app.sidebar>
