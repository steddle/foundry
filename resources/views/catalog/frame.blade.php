@use('Illuminate\Support\Facades\Blade')

{{-- One example on a page of its own, on the page's ground, for /components to frame at a viewport's width. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <foundry:head title="Example" description="One example of a component." />
    </head>
    <body class="isolate">
        {!! Blade::render($blade) !!}
        @fluxScripts
    </body>
</html>
