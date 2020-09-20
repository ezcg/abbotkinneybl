@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css"  href='/css/adminsocialmedia.css'>

  <div class="container" id="tablegrid">

    @if ($itemsId)
      <a href="/items?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}">{!! $title !!} Main Account</a>
      @if (!$isHashtag)
        &#183;
        <a href="/socialmediaaccounts/admin?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}">{!! $title !!} Social Media Accounts</a>
      @endif
      <br />
    @endif

    @if ($isHashtag)
      Hashtags are a way of publishing content by anyone with the only association being the hashtag.
      It is hidden by default. Click 'Unhide' in the corresponding row below to make it public. Once you've unhidden
      all the hashtags you want and have no use for the rest, click on 'Delete Unpublished Hashtags'.
    @endif

      @include('layouts.partials.search', [
          'search' => $search,
          'catsArr' => $catsArr,
          'searchCatsId' => $searchCatsId,
          'includeCats' => 1
      ])

      @if ($hashtagCatsId)
        <div style='float:right;margin-top:20px;'>
        <a href='/socialmedia?view=publishedhashtags&cats_id={!! $hashtagCatsId !!}'>View Published Hashtags</a>
        <a
           class="confirm_delete_btn btn btn-danger"
           href='/socialmedia/deleteunpublishedhashtags?cats_id={!! $hashtagCatsId !!}'
        >Delete Unpublished Hashtags</a>
        </div>
        <div style='clear:both;'></div>
      @endif

      @if (count($smColl) == 0 && $search)
          <br><p>Not finding anything for text search '{!! $search !!}'.</p>
      @elseif (count($smColl) == 0 && $title)
          <br><p>Not finding any social media for '{!! $title !!}'.</p>
      @elseif (count($smColl) == 0)
          <div style='clear:both;'></div>
          <br><p>Not finding any social media.</p>
      @else

          <link rel="stylesheet" type="text/css" href="/css/table.css">

          <table style='width:100%;border:1px solid #ddd;'>
              <tr style='border-bottom:1px solid #ddd;background-color:#f5f8fa;'>
              <td style='width:220px;'>Platform/Username</td>
              <td style='width:200px;'>Main Account</td>

          <td><b>{!! $title !!} Media</b></td>
          <td> &nbsp; </td>
          <td><b> &nbsp; </b></td>
          </tr>
          @foreach($smColl as $sm)

              <tr style='height:120px;overflow:hidden;'>
                  <td width=250>
                      {!! getLinkToSite($sm, 1) !!}  {!! getLinkToSite($sm, 0) !!}
                  </td>
                  <td>
                    @if (!empty($sm->items_id))
                      <a href='/items?items_id={!! $sm->items_id !!}'>{!! $sm->title !!}</a>
                    @endif
                  </td>
              <td style='font-weight:bold;font-size:14px;vertical-align:top'>
                  {!! $sm->text !!}
                  <br />
                    {!! $sm->created_at !!}
              </td>
              <td>
                  @if ($sm->site == 'twitter.com')
                      <a href="https://twitter.com/{!! $sm->username !!}/status/{!! $sm->source_id !!}" target="_blank">twitter.com</a>
                  @elseif ($sm->site == "yelp.com")
                     <a href="{!! $sm->link !!}" target="_blank">yelp.com</a>
                  @elseif ($sm->site == "reddit.com")
                      <a href="{!! $sm->link !!}" target="_blank">reddit.com</a>
                  @endif
              </td>

              <td style='text-align:center;'>
                  @if ($sm->deleted == 0)
                      <a class="btn btn-danger"
                         href="/socialmedia/{!! $sm->id !!}?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}&search={!! urlencode($search) !!}&action=hide&page={!! $smColl->currentPage() !!}&cats_id={!! $searchCatsId !!}">Hide</a>
                  @else
                      <a class="btn btn-danger"
                         href="/socialmedia/{!! $sm->id !!}?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}&search={!! urlencode($search) !!}&action=unhide&page={!! $smColl->currentPage() !!}&cats_id={!! $searchCatsId !!}">Unhide</a>
                          <br>
                  <br>
                      <a class="confirm_delete_btn btn btn-danger"
                         href="/socialmedia/{!! $sm->id !!}?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}&search={!! urlencode($search) !!}&action=unhide&page={!! $smColl->currentPage() !!}&action=deleteforreal&cats_id={!! $searchCatsId !!}">Delete</a>
                  @endif
              </td>
              </tr>

          @endforeach

          </table>

      @endif

      <div style='clear:both;'></div>
      {!! $smColl->appends([
        'items_id' => $itemsId,
        'title' => $title,
        'sort' => $sort,
        'search' => $search,
        'cats_id' => $searchCatsId,
        'view' => $view
        ])->render() !!}

  </div>

@endsection
