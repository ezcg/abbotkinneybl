@extends('layouts.app')
@section('content')

<div class="container" id="tablegrid">

<p style='margin-bottom:10px;'><b>Manage the links that will appear at the bottom of the public facing pages.</b></p>

    <h2 class='sectionTitle'>@if ($id == 0) Add @else Edit &#183; <a href='/links'>Add</a> @endif</h2>
    <br> <br><br>
    <div class="addFooterLinkCont">

    Either "Name" or "Image Source" is required. Image source is a link to a thumbnail image.
    Name is the text of the hyperlink.

    @if($id == 0)
      <form method="POST" action="{!! route('links.store', $id) !!}">
    @else
      <form method="POST" action="{!! route('links.update', $id) !!}">
    @endif
    {{ csrf_field() }}
    <div class="form-group">
      <table cellpadding='4' cellspacing='0'>
        <tr>
          <td>
            Name:
          </td>
          <td>
            <input class = "linkNameTextField" type = 'text' name = 'name' value = "{{ old('name', $name ) }}">
            <input type = 'hidden' name = 'old_name' value = "{!! $name !!}">
            <br>Example: MyTwitterHandle
          </td>
        </tr>
        <tr>
          <td>
            Image Source:
          </td>
          <td>
            <input
              type = 'text'
              name = 'imgsrc'
              value="{{ old('imgsrc', $imgsrc) }}"
              class = "linkImgSrcTextField"
              id = "linkImgSrc"
            >
            <br>Example: https://pbs.twimg.com/profile_images/514428647696916480/BvRBAhtm_bigger.jpeg
          </td>
        </tr>
        <tr>
          <td>
            Link:
          </td>
          <td>
            <input
              type='text'
              name='link'
              value="{{ old('link', $link) }}"
              class = "linkHrefTextField"
              id = "linkHref"
            >
            <br>Example: https://twitter.com/pattonoswalt
          </td>
        </tr>
        <tr>
          <td>Link Click Action:
          </td>
          <td>
            <label for='new_window'>
              <input
                id='new_window'
                type='radio'
                name='open_link_in_new_window'
                value='1' @if($open_link_in_new_window) checked='checked' @endif
              >
              Link opens new window or tab</label><br>
            <label for='replace_window'>
              <input
                id='replace_window'
                type='radio'
                name='open_link_in_new_window'
                value='0' @if(!$open_link_in_new_window) checked='checked' @endif
              >
              Link replaces current page
            </label>
            <br>
          </td>
        </tr>

      </table>
      <input type="submit" value="Submit Link" class="btn btn-primary">
    </div>
    <input type='hidden' name='link_id' value="{!! $id !!}">
    </form>

<h2 class="sectionTitle">Edit</h2><br><br><br>

@if ( !$linksObj->count() )
  You have no links
@else
  <div class="editFooterLinkCont">
  <ul class='footerLinks' style='list-style-type:none;margin-left:-40px;'>
  @foreach( $linksObj as $obj )

      <li>

          <div class="footerLink" style='float:left;'>
          @if ($obj->imgsrc)
              <a href='{{$obj->link}}'>
                  <img
                    class='socialmediaFooterImageLink'
                    src='{{$obj->imgsrc}}'
                    data-toggle="tooltip"
                    title="{{$obj->name}}"
                  >
              </a>
          @else
              <a href='{{$obj->link}}'>
                  {{$obj->name}}
              </a>
          @endif
          </div>
          <div style='float:left;margin-top:5px;'>
          <a href='/links/{!! $obj->id !!}/edit' class = 'btn btn-info'>Edit</a>

          @if ($obj->deactivated == 0)
          <a
            class="btn btn-danger"
            href="/links/{!! $obj->id !!}/deactivate?deactivated=1"
          >Deactivate</a>
          @else
          <a
            class="btn btn-primary"
            href="/links/{!! $obj->id !!}/deactivate?deactivated=0"
          >Reactivate</a>
          @endif
          <a
              class="confirm_delete_btn btn btn-danger"
              href="/links/{!! $obj->id !!}"
          >Delete</a>
          </div>
          <br>
          <div style='clear:both;'></div>
          <br>
      </li>

  @endforeach

  </ul>
  </div>

@endif

    <div id="footerLinksPreviewCont" style='display:none'>
      <p align='center'>This is how the links will appear at the bottom of each page.</p>
      <div id="footerLinksPreview"></div>
    </div>

<script>

  $(document).ready(function() {

    let url = "{!! $footerLinksUrl !!}";

    $.ajax({
      type: 'GET',
      url: url,
      success: function (arr) {

        let factor = Object.values(arr).length > 0 ? Object.values(arr).length : 1;
        let width =  factor * 58;
        let content = "<div class='footerLinkCont' style='width:" + width + "px;'>";
        for (let i = 0; i < arr.length; i++) {
          let link = arr[i].link;
          let name = arr[i].name;
          let imgsrc = arr[i].imgsrc;
          content += "<div class='footerLinkPreview'>";
          content += "<a href='" + link + "' ";
          if (name !== 'Homepage') {
            content += "target='_blank'";
          }
          content += ">";
          if (imgsrc) {
            content += "<img class='socialmediaFooterImageLink' src='" + imgsrc + "' data-toggle='tooltip' title='" + name + "'>";
          } else {
            content += "<span class='socialMediaFooterTextLink'>" + name + "</span>";
          }
          content += '</a>';
          content += '</div>';
        }
        content += "<div style='clear:both;'></div>";
        content += "</div>";
        content += '</div>';
        $("#footerLinksPreview").html(content);
        $("#footerLinksPreviewCont").show();
      },
      error: function (errors) {
        console.log(errors);
        $.fn.alertProblem("There was a problem: " + errors.responseJSON.message);

      }
    });

  });

</script>

</div>
@endsection
