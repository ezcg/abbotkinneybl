@extends('layouts.app')

@section('content')

  <div class="container" id="tablegrid">
    <h3>Manage Category Thumbnail Images</h3>

    @if (count($catsRows) == 0)
      <p>You can upload and associate a thumbnail image to a category once you have categories.</p>
    @else

      <p>Upload an image from your desktop or copy and paste the url to the image on the web. All images will be resized
      to 100x100. If no image is associated with a category, no attempt to display it to the public will be done.<br><a href="?sort=alpha">Display Alphabetically</a> - <a href="?">Display Hierarchy</a></p>

      @foreach($catsRows as $arr)
        <form action="/cats/{!! $arr['id'] !!}/images" method="post" style='margin-bottom:4px;'  enctype="multipart/form-data">
          <input type="hidden" name="id" value="{{$arr['id']}}">
          <input type="hidden" name="sort" value="{{$sort}}">
          {{ csrf_field() }}
          @if (!empty($arr['image']))
            <img src='{{ $arr['image'] }}' class='catThumbnail'>
          @else
            <div class='catThumbnail'></div>
          @endif
          <div class='catTitle' style='font-weight:bold;float:left;'>{{ $arr['title'] }} </div>
          <input type="file" name="imageupload" style='float:left;'/>

          or enter url: <input type='text' name='imageurl' size='40'>

          <button class="btn btn-primary" name="edit">Submit</button>

          <a
            class="confirm_delete_btn btn btn-danger"
            href="/cats/{!! $arr['id'] !!}/images?sort={!! $sort !!}"
          >Delete Image</a>

        </form>
        <div class='cb'></div>
        <br>
      @endforeach
  </form>

  </div>

  @endif

@endsection