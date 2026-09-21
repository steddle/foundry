<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $asset->name }}</title>
    @vite($stylesheet)
</head>
<body style="margin: 0; width: {{ $asset->width }}px; height: {{ $asset->height }}px; overflow: hidden;">
    {!! $asset->markup() !!}
</body>
</html>
