<form method="POST" action="{!! route('wikipedia.store', 0) !!}">
  {{ csrf_field() }}
  @include('wikipedia/partials/_form', ['wikipediaArr' => $wikipediaArr, 'action' => 'create'])
</form>