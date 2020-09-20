@extends('layouts.app')
@section('content')

<div class="container" id="tablegrid">

  <h3>Manage Category 'Hashtags'</h3>
  <div class='boxCont'>
  <p>The hashtags category enables you to search and publish hashtag content that is not associated with a specific
  social media account.</p>
  @if (!$usesHashtagCategories)
    <p>It does not look like you have a category named Hashtags. Go to <a href='/cats'>Category</a> page and add it to
    get started.</p>
  @else
    @if (!$hashtagItemsArr)
    <p>To start following hashtags, go to the <a href='/items'>Main Account</a> page and add the hashtag
      you wish to follow as a Main Account. The hashtag must start with the # symbol. Once you add the hashtag as
      a Main Account, assign it to the Hashtags category by clicking the Hashtags checkbox beneath it.</p>
    @else
      You have the following hashtags set as Main Accounts:
      <ul>
      @foreach($hashtagItemsArr as $key => $arr)
        <li><a href='/items?items_id={!! $arr['id'] !!}'>{!! $arr['title'] !!}</a></li>
      @endforeach
      </ul>
      <p>You can add text or usernames below that will cause the hashtag found to be ignored.</p>
      <p>For example, say you are tracking the hashtag #venicebeach. Twitter user @famousetourist
        posts "I love #venicebeach" and it gets retweeted a thousand times. To ignore all the retweets, enter
        '@famoustourist' below.</p>
      <p>
        Another example, to ignore all text that has 'I hate #venicebeach', simply enter
        'I hate #venicebeach' below. That will continue to get content that has #venicebeach in it, but not if it the full text has 'I hate #venicebeach' in it.
      </p>


      <form method='post' action='/cats/hashtagsignore'>
        {{ csrf_field() }}
      Ignore any found hashtag that has the following text in it:
        <input type='text' name='value' size='60'>
        <button class="btn btn-primary" name="edit">Add</button>
      </form>
      <br />

      @if ($hashtagIgnoreArr)
        <p>The following text will cause any hashtag match to be ignored:</p>
        <ul>
        @foreach($hashtagIgnoreArr as $key => $obj)
          <li>{!! $obj->value !!} <a
              class="confirm_delete_btn btn btn-danger"
              href='/cats/hashtagsignoredelete?value={!! urlencode($obj->value) !!}'
            >delete</a></li>
        @endforeach
        </ul>
      @endif

    @endif

  @endif

  </div>

</div>

@endsection