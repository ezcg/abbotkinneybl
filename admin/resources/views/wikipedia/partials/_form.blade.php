<div class="form-group">
  <table cellpadding='4' cellspacing='0'>

  <tr>
    <td>
      Url:
    </td>
    <td style='width:100%'>
      <input type='hidden' name='title' value="{!! $wikipediaArr['title'] !!}">
      <input type='hidden' name='view' value="{!! $view !!}">
      <input type='hidden' name='page' value="{!! $page !!}">

      <input
        type='text'
        name='url'
        value="{!! $wikipediaArr['url'] !!}"
        class = "wikipediaUrlField"
      >
      <br>Example: https://en.wikipedia.org/wiki/Main_Page
    </td>
  </tr>

  <tr>
    <td>
      Description:
    </td>
    <td style='width:100%'>
      <textarea
        name='description'
        rows='5'
        class = "wikipediaTextarea"
        id="wikisearchresults_{!! $wikipediaArr['items_id'] !!}"
      >{!! trim($wikipediaArr['description']) !!}</textarea>
    </td>
  </tr>

  </table>

  Manually entry. <button class="submititemdetail btn btn-primary" name="edit">Submit</button>

  <div class='td' style='float:right;'>To grab a brief summary from Wikipedia and overwrite any existing summary, click Auto Update.
    <a
      class="wikipediasearch btn btn-primary"
      href="javascript:void(0);"
      data-wikisearchresults_id="{!! $wikipediaArr['items_id'] !!}"
    >Auto Update</a>
  </div>

  @if ($action == 'edit' && $wikipediaArr['deactivated'] == 0)
    <a
      class="btn btn-danger"
      href="{{ route('wikipedia.deactivate', array($wikipediaArr['items_id'], "deactivated"=>1)) }}"
    >Deactivate</a>
  @elseif ($action == 'edit')
    <a
      class="btn btn-primary"
      href="{{ route('wikipedia.deactivate', array($wikipediaArr['items_id'], "deactivated"=>0)) }}"
    >Reactivate</a>
  @endif
  @if ($action == 'edit')
    <a
      class="confirm_delete_btn btn btn-danger"
      href="{{ route('wikipedia.delete', array($wikipediaArr['items_id'])) }}"
    >Delete</a>
  @endif

</div>

<input type='hidden' name = 'items_id' value="{!! $wikipediaArr['items_id'] !!}">