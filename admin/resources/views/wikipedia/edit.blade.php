@extends('layouts.app')

@section('content')

<div class="container" id="tablegrid">

<a href='/items?items_id={{$wikipediaArr['items_id']}}'>{{$wikipediaArr['title']}} Main Account</a>

<h2>Edit</h2>

<a href='{{ $wikipediaArr['url'] }}'>
    {{ $wikipediaArr['url'] }}
</a>

<form method='POST' action="{!! route('wikipedia.update', $wikipediaArr['items_id']) !!}">
  {{ csrf_field() }}
@include('wikipedia/partials/_form', ['wikipediaArr' => $wikipediaArr, 'action' => 'edit'])

</form>

  @include('wikipedia.partials.mngentries')

</div>

@endsection