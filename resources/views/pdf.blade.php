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
        @foreach ($entry as $k => $d)
            @if ($loop->first)
                <h4>{{ $d }}</h4>
            @else
                @if (!empty($d))
                    <p><strong>{{ $k }}</strong></p>
                    <p>{!! $d !!}</p>
                @endif
            @endif
        @endforeach
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>
