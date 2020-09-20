@extends('layouts.app')
@section('content')

<div class="container" id="tablegrid">

@if ($itemsId && $title)
<a href="/items?items_id={!! $itemsId !!}">{!! $title !!} Main Account</a>
  <br />
@endif

<h2 class='sectionTitle'>Add</h2>
@if (!empty($title))
  <div style='float:left;display:inline;margin-left:20px;margin-top:30px;'><a
      target='_blank'
      href="https://www.wikipedia.org/search-redirect.php?family=wikipedia&language=en&search={!! $title !!}&language=en&go=Go"
      >search wikipedia for '{!! $title !!}' &nbsp; @php echo getOffsiteLinkIcon(); @endphp</a></div>
@endif
<div style="clear:both;"></div>

@if (!empty($itemsId))
  @include('wikipedia.partials.create')
@else
  <p>To add a Wikipedia entry and associate it with a Main Account, search for the associated <a
      href="/items">Main Account</a> and click on the {!! getWikipediaIcon(20,20) !!} add link for that account.
    <br>
    <br>
    For an easy over view of Main Accounts with missing or deactivated Wikipedia entries, browse below.</p>
@endif

@include('wikipedia.partials.mngentries')

</div>
@endsection
