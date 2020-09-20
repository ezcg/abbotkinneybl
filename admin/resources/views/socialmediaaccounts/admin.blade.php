@extends('layouts.app')
@section('content')

<div class="container" id="tablegrid">

@if ($itemsId)
  <div style='float:left;'>
  <a href="/items?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}">{!! $title !!} Main Account</a>
  &#183;
  <a href="/socialmediaaccounts/create?items_id={!! $itemsId !!}&title={!! urlencode($title) !!}">Add Social Media Account</a>
  </div>
@endif

@if (empty($itemsId) && $twitterListNamesStr)
  <div id='twitterImportCont'><a href='javascript:void(0);' id='twitterImport'>Twitter Import</a></div>
@elseif (empty($itemsId) && $twitterMain)
    <div id='twitterImportCont'><a href='javascript:void(0);' id='twitterImport'>Twitter Import</a></div>
@endif

@if (empty($itemsId))
  <div id='createMainAccountsCont'>  &#183; <a href='javascript:void(0);' id='createMainAccounts'>Create Main Accounts</a></div>
@endif

@if ($twitterListNamesStr)
  <div style='clear:both;'></div>
  <div id='twitterImportText'>Retrieve and save members of Twitter.com list(s) <b>{!! $twitterListNamesStr !!}</b> as a Social Media Account<br><a class="btn btn-primary" href='/twitter/getlistmembers?redirect=1'>Retrieve</a><br>

  </div>
@elseif ($twitterMain)
  <div style='clear:both;'></div>
  <div id='twitterImportText'>Retrieve and save members of <b>{!! $twitterMain !!}</b> main Twitter account as a Social Media Account <br> <a href='/twitter/getfriends?redirect=1' class="btn btn-primary" >Retrieve</a>
  </div>
@endif

@if (empty($itemsId) && $numUnassociated)
  <div style='clear:both;'></div>
  <div id='createMainAccountsText'>
  Create Main Accounts from all Social Media Accounts that are not already associated with a Main Account<br>
  <a class="btn btn-primary confirmClick" href='/socialmediaaccounts/assocall'>Create Main Accounts</a>.
  </div>
@elseif (empty($itemsId) && $numUnassociated == 0 && count($itemsArr))
  <div style='clear:both;'></div>
  <div id='createMainAccountsText'>
  All social media accounts are associated with a Main Account. Perhaps do Twitter Import first.
  </div>
@endif

<div style='clear:both;'></div>
<div style='float:left;margin-top:23px;'>
<a href='/socialmediaaccounts/admin?view=unassociated' @if ($view =='unassociated') class="selectedLink" @endif>
View unassociated
</a> ({!! $numUnassociated !!})
<a href='/socialmediaaccounts/admin?view=associated'  @if ($view =='associated' || empty($view)) class="selectedLink" @endif>
View associated
</a> ({!! $numAssociated !!})
<a href='/socialmediaaccounts/admin?view=deactivated'  @if ($view =='deactivated') class="selectedLink" @endif>
Deactivated
</a> ({!! $numDeactivated !!})
</div>

@include('layouts.partials.search', [
  'search' => $search,
  'catsArr' => [],
  'searchCatsId' => 0,
  'includeCats' => 0,
  'viewSort' => $view,
  'itemsId' => $itemsId
])

@if (count($smaColl) == 0 && !empty($search))
  <br><p>Nothing found for '{{$search}}'.</p>
@elseif (count($smaColl) == 0 && $view == 'unassociated')
  <br><p>Not finding any social media accounts that are not already associated with an item. View all
  <a href='?'>social media accounts</a>.</p>
@elseif (count($smaColl) == 0)
  <div style='clear:both;'></div>
  <br><p>No social media accounts found.</p>
@else

  @if ($view == 'unassociated')
    <div style='padding:10px;text-align:center;float:left;'>A Main Account can have multiple social media accounts associated with it.
    Listed below are the social media accounts that are not associated yet with a Main Account.
    </div>
  @endif

  <table style='width:100%;border:1px solid #ddd;'>
  <tr style='border-bottom:1px solid #ddd;font-weight:bold;'>
  <td style='width:320px;'>Platform/Platform Username</td>
  <td>Main Account</td>
  <td align='center'>Social Media</td>
  <td align='center'>Edit Avatar</td>
  <td class="checkBoxColumn">Use Avatar</td>
  <td class="checkBoxColumn">Primary</td>
  <td class="checkBoxColumn">Is Active</td>
  <td> &nbsp; </td>
  </tr>

  @foreach($smaColl as $key => $sma)

    {{-- since the form for editing an avatar is not visible but still triggers the css odd/even row style, doing it this way--}}
    @php $rowClass="tdRowOdd" @endphp
    @if ($key % 2 == 0)
      @php $rowClass="tdRowEven" @endphp
    @endif

    <tr class="{!! $rowClass !!}">
    <td>
    @if(empty($sma->items_id))
      {!! getLinkToSite($sma, 1) !!} <a href='/items?search={{$sma->name}}'>{{$sma->name}}</a>
    @else
      {!! getLinkToSite($sma, 1) !!}  {!! getLinkToSite($sma, 0) !!}
    @endif
    </td>
    <td>

    @if (!empty($sma->items_id))
      <a href='/items?items_id={{$sma->items_id}}'>{{$sma->title}}</a>
    @else

      <form
      class="addAsMainAccountForm"
      action="{{ route('items.store') }}"
      method="post"
      data-name="{{$sma->name}}"
      data-username="{{$sma->username}}"
      >
      {{ csrf_field() }}
      {{-- Adding sma username as item title--}}
      <input type="hidden" name="title" value="{!! $sma->name !!}">
      <input type="hidden" name="sma_id" value="{!! $sma->sma_id !!}">
      <input type="hidden" name="search" value="{!! $search !!}">
      <input type="hidden" name="view" value="{!! $view !!}">
      <input type="hidden" name="page" value="{!! $smaColl->currentPage() !!}">
      <input class="btn btn-primary" type="submit" value="Add as Main Account">
      </form>

      {{--Drop down for associating item with sma--}}
      @if(count($itemsArr))
        <form class="sma_dd" id="{{$sma->username}}">
        <input class="sma_id" type="hidden" name="social_media_account_id" value="{{$sma->sma_id}}">
        <input class="sma_name" type="hidden" name="social_media_account_name" value="{{$sma->name}}">
        @php
        echo dropdown('items_id', $itemsArr, $selected = "", $class = "", $id = "");
        @endphp
        </form>
      @endif

    @endif

    </td>

    <td align='center'>

    @if (!empty($sma->items_id))
      <a href="/socialmedia/?items_id={{$sma->items_id}}&title={{$sma->title}}" style='font-size:20px;
      font-weight:bold;'>&raquo;
      </a>
      &nbsp;
    @endif

    </td>

    <td align='center'>

      <img
        class='smaAvatar'
        id="avatar_{{$sma->sma_id}}"
        src=@if ($sma->avatar)'{!! str_replace("http:", "", $sma->avatar) !!}' @else '{!! $awsPrimaryBucket !!}empty_gray_100x100.png' @endif
        style="cursor:pointer"
        data-sma_id='{{$sma->sma_id}}'
      >

    </td>

    <td>
    <div class="checkBoxColumnCell">
    <input
      id="use_avatar_{{$sma->sma_id}}"
      class='statusCheckbox'
      type='checkbox'
      name='use_avatar'
      value='1' {{ $sma->use_avatar ? 'checked' : '' }}
      data-sma_id='{{$sma->sma_id}}'
      data-name='use_avatar'
    >
    <span class="checkMark" id="use_avatar_ck_{{$sma->sma_id}}">&#10004;</span>
    </div>
    </td>

    <td class="checkBoxColumnCell">
    <div class="checkBoxColumnCell">
    <input
      id="is_primary_{{$sma->sma_id}}"
      class='statusCheckbox'
      type='checkbox'
      name='is_primary'
      value='1' {{ $sma->is_primary ? 'checked' : '' }}
      data-sma_id='{{$sma->sma_id}}' data-name='is_primary'
    >
    <span class="checkMark" id="is_primary_ck_{{$sma->sma_id}}">&#10004;</span>
    </div>
    </td>
    <td>
    <div class="checkBoxColumnCell">
    <input
      id="is_active_{{$sma->sma_id}}"
      class='statusCheckbox'
      type='checkbox'
      name='is_active'
      value='1' {{ $sma->is_active ? 'checked' : '' }}
      data-sma_id='{{$sma->sma_id}}' data-name='is_active'
    >
    <span class="checkMark" id="is_active_ck_{{$sma->sma_id}}">&#10004;</span>
    </div>
    </td>
    <td class='tdcenter'>
    <a
      class="confirm_delete_btn btn btn-danger"
      href="/socialmediaaccounts/{!! $sma->sma_id !!}?items_id={!! $itemsId !!}&title={!! $sma->title !!}&search={!! $search !!}&page={!! $smaColl->currentPage() !!}&view={!! $view !!}&redirect_to=admin"
    >Delete</a>
    </td>
    </tr>

    {{--Avatar form--}}
    <tr class="{!! $rowClass !!}">
      <td colspan='8'>
      <form
        id="avatar_form_{!! $sma->sma_id !!}"
        action="{{ route('socialmediaaccounts.update', $sma->sma_id) }}"
        class="smaAvatarForm"
        style='display:none;'

      >
        @if ($sma->avatar)
          <img
            style='float:left;;'
            class="smaAvatar"
            src='{!! str_replace("http:", "", $sma->avatar) !!}'
          >
        @else
          <div style='display:inline;float:left;margin-top:15px;'>Enter url to avatar:</div>
        @endif

        <input
          style='float:left;margin-top:15px;'
          type='text'
          name='avatar'
          value='{!! str_replace("http:", "", $sma->avatar) !!}'
          size='100'
        >
        <input type='hidden' name='sma_id' value='{!! $sma->sma_id !!}'>
        <input type="submit" value="Submit" class="avatarSubmit btn btn-primary" style="margin-left:2px;margin-top:12px;">
        <input type="submit" value="Cancel" class="avatarCancel btn btn-info" style="margin-top:12px;">

      </form>

      </td>
    </tr>

  @endforeach

  </table>

@endif

<div style='clear:both;'></div>

@if ($view == 'unassociated' && count($smaColl) > 0)
  <p style='font-size:20px;margin-top:5px;margin-bottom:-20px;text-align:center;'><a
  href='?view=unassociated'><b>Reload next batch of unassociated &#x21bb;</b></a></p>
@endif

{!! $smaColl->appends(['sort' => $sort, 'search' => $search, 'view' => $view])->render() !!}

<br><br><br>
</div>

<script>

$(document).ready(function() {


  function submitMainAccountForm(response, nativeThis) {
    if (response == 'ok') {
      nativeThis.submit();
    }
  }

  $(".addAsMainAccountForm").submit(function(e) {
    e.preventDefault();
    let name = $(this).data("name");
    let username = $(this).data("username");
    let msg = 'This will create a Main Account named "' + name + '" and automatically associate that Main Account with this social media account "' + username + '". You can edit/rename the Main Account once added. Proceed?';
    // use native 'this' to avoid jquery event handler retriggering dialogue
    $.fn.alertConfirm(msg, submitMainAccountForm, this);
  });

  $(".smaAvatar").click(function(e) {
    let smaId = $(this).data('sma_id');
    if ($("#avatar_form_" + smaId).css("display") == 'none') {
      $("#avatar_form_" + smaId).slideDown();
    }else {
      $("#avatar_form_" + smaId).slideUp();
    }
  });

  $("#twitterImport").click(function(e) {
    if ($("#twitterImportText").css("display") == 'none') {
      $("#createMainAccountsText").hide();
      $("#twitterImportText").slideDown();
    }else {
      $("#createMainAccountsText").hide();
      $("#twitterImportText").slideUp();
    }
  });

  $("#createMainAccountsCont").click(function(e) {
    if ($("#createMainAccountsText").css("display") == 'none') {
      $("#twitterImportText").hide();
      $("#createMainAccountsText").slideDown();
    }else {
      $("#twitterImportText").hide();
      $("#createMainAccountsText").slideUp();
    }
  });

  $(".avatarCancel").click(function(e) {
    e.preventDefault();
    let form = $(this).parents('form:first');
    form.slideUp();
  });

  $(".avatarSubmit").click(function(e) {
    e.preventDefault();
    let form = $(this).parents('form:first');
    let avatar =  $(form).find('[name=avatar]').val();
    let smaId =  $(form).find('[name=sma_id]').val();
    let url = form.attr('action');
    $.ajax({
      type: 'POST',
      url: url,
      data: {
        avatar: avatar,
      },
      success: function (data) {
        form.hide();
        $("#avatar_" + smaId).attr('src', avatar);
      },
      error: function (errors) {
        let msg = errors.responseJSON.errors.avatar;
        $.fn.alertProblem(msg);
      }
    });

  });

  let updateId = 0;
  let updateColumnUrl = "";
  let value = 0;
  let name = "";
  $('.statusCheckbox').click(function(e) {
    updateId = $(this).data("sma_id");
    updateColumnUrl = "/socialmediaaccounts/" + updateId + "/updatecolumn";
    if ($(this).is(":checked")) {
      value = 1;
    } else {
      value = 0;
    }
    name = $(this).data("name");
    update();
  });

  function update() {
    $.ajax({
      type: 'POST',
      url: updateColumnUrl,
      data: {
        name: name,
        value: value,
      },
      success: function (data) {
        let id = "#" + name + "_ck_" + updateId;
        console.log("id",id);
        $(id).show();
        $(id).fadeOut(1000);
      },
      error: function (errors) {
        console.log("errorsV", errors);
        let msg = errors.responseJSON.errors;
        $.fn.alertProblem(msg);
      }
    });
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  /*
  Upon change of items assoc drop down, submit to server,
  Set link to item where where drop down was and add edit link
  repopulate all drop downs less the item that was just selected
  */
  var $itemsArr = [];
  @foreach($itemsArr as $id => $title)
  $itemsArr[{{$id}}] = '{{$title}}';
  @endforeach

  $('.sma_dd').click(function(e) {

    let itemsName = $(this).find(":selected").text();
    let itemsId = $(this).find('option:selected').val();
    if (itemsId != 0) {
      let smaId = $(this).parent().find(".sma_id").val();
      let editId = "#edit_" + smaId;
      let enclosingTD = $(this).parent();

      $.ajax({
        type: 'POST',
        url: '/socialmediaaccounts/' + smaId + '/assoc',
        data: {
          items_id: itemsId,
          redirect_disabled: 1
        },
        success: function (data) {
          let itemLink = '<a href="/items?items_id=' + itemsId + '">' + itemsName + '</a>';
          enclosingTD.html(itemLink);
          let editLink = '<a href="/socialmediaaccounts/edit?items_id=' + itemsId + '&title=' + encodeURI(itemsName) + '">edit</a>';
          $(editId).html(editLink);
        },
        error: function (errors) {
          console.log("2", errors);
          let msg = errors.responseJSON.errors;
          $.fn.alertProblem(msg);
        }

      });

    }
  });
});
</script>

@endsection