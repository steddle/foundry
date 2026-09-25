@use('Illuminate\Support\Facades\Blade')

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"@if (config('imprint.palette')) data-palette="{{ config('imprint.palette') }}"@endif>
    <head>
        <foundry:head title="Example" description="One example of a component." />
    </head>
    <body class="isolate">
        {!! Blade::render($blade) !!}
        @fluxScripts
    </body>
</html>
