<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $asset->name }}</title>
    @vite($stylesheet)
    {{-- Transparent under the site's own page ground, so a rounded icon keeps its corners. --}}
    <style>html, body { background: transparent !important; }</style>
</head>
<body style="margin: 0; width: {{ $asset->width }}px; height: {{ $asset->height }}px; overflow: hidden;">
    {!! $asset->markup() !!}
</body>
</html>
