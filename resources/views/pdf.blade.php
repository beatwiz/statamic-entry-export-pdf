<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>Export: {{ $collection->title() }}</title>
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($entries as $entry)
        @foreach ($entry as $e)
            @if ($loop->first)
                <h3>{{ $e['value'] }}</h3>
            @else
                @if (!empty($e))
                    <div class="label">{{ $e['name'] }}</div>
                    <div class="content">{!! $e['value'] !!}</div>
                @endif
            @endif
        @endforeach
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>
