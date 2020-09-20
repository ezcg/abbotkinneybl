@extends('layouts.app')
@section('content')

    <div class="container" id="tablegrid">

      {{ csrf_field() }}
      <a href='/items?items_id={{$itemsId}}'>{{$title}} Main Account</a>

      &#183;
      <a href="/socialmediaaccounts/admin?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}"
      >Edit {!! ($title) !!}Social Media Accounts</a>

      @if ($usesYelp)
        &#183; Search for '{!! $title !!}' @if($yelpFindLoc) in or around {!! $yelpFindLoc !!} @endif
        on <a target='_blank'
              href='http://www.yelp.com/search?find_desc={!! urlencode($title) !!}@if ($yelpFindLoc)&find_loc={!! urlencode($yelpFindLoc) !!}@endif'>yelp.com</a>
      @endif

      <br>

      <h2 class="sectionTitle">Add Social Media Account</h2>
      <div style='clear:both;'></div>

      <form class='addSMACont' action="{{ route('socialmediaaccounts.store') }}" method="POST">
      {{ csrf_field() }}
      Associate username or subreddit name:
        <input type='text' name='username' value="{{ $username }}">
        found on the social media platform
        <br>
        <br>
        <label>
          <input class='siteRadio' type='radio' name='site' value='instagram.com'
          @if ($site =='instagram.com') checked='checked' @endif
          > instagram
        </label>
        <br>
        @if ($usesYelp)
            <label>
              <input class='siteRadio'  type='radio' name='site' value='yelp.com'
              @if ($site =='yelp.com') checked='checked' @endif
              > yelp
            </label>
            <br>
        @endif
        @if ($usesTwitter)
            <label>
              <input class='siteRadio'  type='radio' name='site' value='twitter.com'
              @if ($site =='twitter.com') checked='checked' @endif
              > twitter
            </label>
            <br>
        @endif
        <label>
          <input class='siteRadio'  type='radio' name='site' value='reddit.com'
          @if ($site =='reddit.com') checked='checked' @endif
          > reddit
        </label>
        <br>
        <br>
        <b>with</b> Main Account <a href='/items?items_id={{$itemsId}}'>{{$title}}</a>

        <input type='hidden' name='items_id' value='{{$itemsId}}'>
        <input type='hidden' name='title' value='{{$title}}'>

        <br><br>
        <div id='avatar_url'>
        and set avatar to url: <input id='avatar_url' type='text' style='width:200px;' name='avatar'>
        </div>
        <div id='twitter_avatar_url_msg' style='display:none;'>
        The avatar is automatically retrieved from Twitter and you don't have to enter it here.
        </div>
        <br><br>
        <button class="btn btn-primary" name="add" style='float:left;margin-top:-4px;margin-left:5px;'>Associate</button>

        <div style='clear:both;'></div>
    </form>

    </div>
<script>

    $(document).ready(function() {

      $('.siteRadio').click(function() {
        if ($(this).val() == 'twitter.com') {
           $("#twitter_avatar_url_msg").show();
           $("#avatar_url").hide();
        } else {
          $("#twitter_avatar_url_msg").hide();
          $("#avatar_url").show();
        }

      });

    });

</script>

@endsection