<h2 class='sectionTitle'>Manage Wikipedia Entries</h2>
<div style="clear:both;"></div>

<p>To deactivate or delete a Wikipedia entry, search for the associated <a href='/items'>Main Account</a>
  and click on the {!! getWikipediaIcon(20,20) !!} edit link for that account or browse below.</p>

<a href="/wikipedia?view=unassociated">
  @if ($view == "unassociated") <b> @endif
    Main Accounts with no associated Wikipedia entry
  @if ($view == "unassociated") </b> @endif
</a> ({{ $numUnassociated }})
&nbsp; &#183; &nbsp;

<a href="/wikipedia?view=deactivated">
  @if ($view == "deactivated") <b> @endif
    Main Accounts with deactivated Wikipedia entry
    @if ($view == "deactivated") </b> @endif
</a>  ({{ $numDeactivated }})

@if ( !$numUnassociated && $view == "unassociated")
  <p style='margin-top:10px;'>All Main Accounts associated with a Wikipedia entry.</p>
@elseif (!$wikipediaColl->count() && $view == "deactivated")
  <p style='margin-top:10px;'>All Wikipedia entries are active.</p>
@else

  <div style="clear:both;"></div>

  <p style='margin-top:5px;'>
  @if ($view == "unassociated")
    Browse Main Accounts below that are not associated with a Wikipedia entry.
  @else
    Browse Main Accounts below that are deactivated. Click to edit status.
  @endif
  </p>

  <style>
    .wrow:nth-child(even) {background: #ddd;padding:4px;width:600px;}
    .wrow:nth-child(odd) {background: #eee;padding:4px;width:600px;}
  </style>

  @foreach( $wikipediaColl as $obj )

    <div class="wrow">
      <div style='float:left;width:300px;'>{{$obj->title}}</div>
      <a href="/wikipedia?items_id={{ $obj->items_id }}&title={{ $obj->title }}&page={{ $page }}">
          <b>Add Wikipedia Entry</b>
      </a>
      -
      <a href="/items?items_id={{ $obj->items_id }}&title={{ $obj->title }}">
        <b>Main Account&raquo;</b>
      </a>
    </div>

  @endforeach

  {!! $wikipediaColl->appends(['view' => $view])->render() !!}

@endif