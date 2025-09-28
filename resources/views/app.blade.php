@php
  $isLocal = app()->environment('local');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Aplicación</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  @unless($isLocal)
    @php
      $manifestPath = public_path('spa-build/manifest.json');
      $entry = null;
      if (file_exists($manifestPath)) {
          $manifest = json_decode(file_get_contents($manifestPath), true);
          foreach ($manifest as $v) {
              if (isset($v['isEntry']) && $v['isEntry']) { $entry = $v; break; }
          }
      }
    @endphp
    @if($entry && isset($entry['css']))
      @foreach($entry['css'] as $css)
        <link rel="stylesheet" href="{{ asset('spa-build/'.$css) }}">
      @endforeach
    @endif
  @endunless
</head>
<body>
  <div id="app"></div>

  @if($isLocal)
    <script type="module" src="http://localhost:5173/@@vite/client"></script>
    <script type="module" src="http://localhost:5173/src/main.ts"></script>
  @else
    @if($entry)
      <script type="module" src="{{ asset('spa-build/'.$entry['file']) }}"></script>
    @else
      <p style="color:red">Build de SPA no encontrado (spa-build/manifest.json).</p>
    @endif
  @endif
</body>
</html>
