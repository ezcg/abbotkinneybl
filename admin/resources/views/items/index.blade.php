@extends('layouts.app')
@section('content')

<div class="container" id="tablegrid">

    <form action="{{ route('items.store') }}" method="post">

      {{ csrf_field() }}

      <h2 class="sectionTitle">Add</h2>

      <div class="sectionForm">
      <input type="text" size="30" name="title" placeholder="Title">
      <input type="text" size="60" name="description" placeholder="Description">
      <input class="btn btn-primary" type="submit" value="Add Item">
      </div>

    </form>

    <div style="clear:both;"> </div>

    <div style='float:left;margin-top:22px;'>

    <b>View:</b>

    <a href='/items'>
      @if ($view == 'activated' || empty($view)) <b> @endif
        Activated
      @if ($view == 'activated' || empty($view)) </b> @endif
    </a> ({!! $numActivated !!})

    <a href='/items?view=deactivated'>
      @if ($view == 'deactivated') <b> @endif
        Deactivated
      @if ($view == 'deactivated') </b> @endif
    </a> ({!! $numDeactivated !!})

    <a href='/items?view=uncategorized'>
      @if ($view == 'uncategorized') <b> @endif
        Uncategorized
      @if ($view == 'uncategorized') </b> @endif
      </a> ({!! $numUncategorized !!})

    </div>

    @include('layouts.partials.search', [
        'search' => $search,
        'catsArr' => $catsArr,
        'searchCatsId' => $searchCatsId,
        'includeCats' => 1
    ])

    <div style="clear:both;"></div>
    @if (count($itemsColl) == 0  && $view == 'uncategorized')
        <br><p>Not finding any uncategorized accounts. <a href='?'>View all accounts</a></p>
    @elseif (count($itemsColl) == 0  && $view == 'deactivated')
      <br><p>Not finding any deactivated accounts. <a href='?'>View all accounts</a></p>
    @elseif (count($itemsColl) ==0  && !empty($search))
      <br><p>Not finding anything with search '{!! $search !!}'
      @if (!empty($searchCatsId))
         under category '{!! $catsArr[$searchCatsId] !!}'
      @endif
      .
      <a href='?'>View all items</a></p>
    @else

        <div id='categoryTable'>
        @foreach( $itemsColl as $item )
            <form class='catRow' id="form_{{ $item->id }}" action="{{ route('items.update', $item) }}" method="post">
            <input type="hidden" name="on_page" value="{{$itemsColl->currentPage()}}">
            <input type="hidden" name="update_items_id" value="{{ $item->id }}">
            <input type="hidden" name="search_cats_id" value="{{ $searchCatsId }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="view" value="{{ $view }}">
            {{ csrf_field() }}
            <div class='tr'>
                <div class='td'>
                    <input type="text" size="30" name="title" value="{{ $item->title }}">
                    <input type="hidden" size="30" name="title_old" value="{{ $item->title }}">
                </div>
                <div class='td'>
                    <input type="text" size="60" name="description" value="{{ $item->description }}">
                </div>

                <div class='td'>
                    <button style='margin-left:4px;' class="submititemdetail btn btn-primary" name="edit">Submit</button>
                </div>
                <div class='td' style='margin-left:4px;margin-right:4px;'>
                  <button
                    name='deactivatedstatus'
                    value="{!! $item->deactivated !!}"
                    class='deactivatedstatusbtn btn @if($item->deactivated) btn-info @else btn-danger @endif'
                  >@if($item->deactivated)Reactivate @else Deactivate @endif
                  </button>
                </div>
                <div class='td'>
                    @php
                    $itemsId = !empty($item->id) ? $item->id : 0;
                    @endphp
                    <a class="confirm_delete_btn btn btn-danger"
                       href="/items/{!! $itemsId !!}?search={!! urlencode($search) !!}&page={!! $itemsColl->currentPage() !!}&cats_id={!! $searchCatsId !!}&view={!! $view !!}&sort={!! $sort !!}"
                    >Delete</a>

                </div>
                <div class='td'>
                  <span style='margin-left:10px;font-size:20px;' class="checkMark" id="items_id_{!! $item->id !!}">&#10004;</span>
                </div>

                @if ($googleSearchTerm)
                  @php
                    $searchTerm = str_replace("~searchterm~", $item->title, $googleSearchTerm);
                    $q = urlencode($searchTerm);
                  @endphp
                  <div style='float:right;margin-right:4px;'>
                    <a
                      class = "googleSearchLink"
                      target='_blank'
                      href=''
                    >google @php echo getOffsiteLinkIcon(); @endphp</a>&nbsp;
                  </div>
                @endif

            </div>
              <div style='clear:both;'></div>

            <div style='clear:both;'></div>
            <div class='tr'>
                <div class='td'  id='addCatsCheckboxes_{{ $item->id }}'>

                    @php
                        displayItemsCatsCkBoxes($itemsLevelCatsArr, '', $catsCollArr, $itemsCatsColl, $item->id);
                    @endphp

                </div>
                <br>

                @if ($usesContactInfo && substr($item->title, 0, 1) != "#")
                    <div class='td'><a href='/contactinfo?items_id={{$item->id}}&title={{ urlencode($item->title) }}'>Contact Info</a> &nbsp; &#183; &nbsp; </div>
                @endif
                @if ($usesHours && substr($item->title, 0, 1) != "#")
                  <div class='td'><a href='/hours/index?items_id={{$item->id}}&title={{ urlencode($item->title) }}'>Hours</a> </div>
                @endif
                <div style='clear:both;'></div>

                @if (substr($item->title, 0, 1) != "#")
                  <div class='td'> Social Media Accounts: <a href='/socialmediaaccounts/admin?items_id={{ $item->id
                  }}&title={{ urlencode($item->title) }}'>Edit</a></div>
                  <div class='td'> &#183; <a href='/socialmediaaccounts/create?items_id={{ $item->id }}&title={{ $item->title}}'>Add</a>  &#183; </div>
                @else
{{--                  <div class='td'>Hashtag accounts do not have dedicated Social Media Accounts</div>--}}
                @endif
                <div class='td'><a
                    href='/socialmedia?items_id={{ $item->id }}&title={{ urlencode($item->title) }}'
                  >Social Media</a></div>

                @foreach($item->smaArr as $sma)
                    <div class='td'> &nbsp; <b>&middot;</b> &nbsp; {!! getLinkToSite($sma, true, 20, 20) !!} {!! getLinkToSite($sma, 0) !!}</div>
                @endforeach

                @if (count($item->wikipediaArr) && substr($item->title, 0, 1) != "#")
                  <div class='td' style='margin-left:4px;'>
                     &#183; <a
                      href="{{ route('wikipedia.edit',  [$item->id,'title'=>$item->title] )}}"
                    >{!! getWikipediaIcon(20,20) !!} manual edit</a>
                      &#183; <a
                      class="wikipediasearch"
                      href="javascript:void(0);"
                      data-wikisearchresults_id="{!! $item->id !!}"
                    >auto update</a>
                  </div>
                @elseif ($usesWikipediaSearch && substr($item->title, 0, 1) != "#")
                  <div class='td' style='margin-left:4px;'>  &#183; {!! getWikipediaIcon(20,20) !!} <a
                      href="/wikipedia?items_id={{ $item->id }}&title={{ urlencode($item->title) }}"
                    >manaul add</a> &#183; <a
                      class="wikipediasearch"
                      href="javascript:void(0);"
                      data-wikisearchresults_id="{!! $item->id !!}"
                    >auto add</a>
                  </div>
                @endif

            </div>

              <div style='clear:both;'></div>

                <div class='wikisearchresultscont' id='wikisearchresultscont_{!! $item->id !!}'>
                  <div class='hide_wikisearchresults'><a
                      href='javascript:void(0);'
                      data-id='{!! $item->id !!}'
                      class='hide_wikisearchresults_X'
                  >X</a></div>
                  <div class='wikisearchresults' id='wikisearchresults_{!! $item->id !!}'></div>
                </div>

            <div style='clear:both;'></div>

          </form>

        @endforeach
        </div>
        <div style='clear:both;'></div>
        @if ($view != 'uncategorized')
        {!! $itemsColl->appends([
            'sort' => $sort,
            'search' => $search,
            'cats_id' => $searchCatsId,
            'view' => $view
            ])->render() !!}
        @else
          <p style='font-size:20px;margin:20px;text-align:center;'><a
              href='?view=uncategorized'><b>Reload next batch of uncategorized &#x21bb;</b></a></p>
        @endif

    @if ($usesWikipediaSearch)
      <div style='float:right;margin-top:20px;'><a
          id='auto-update'
          href='javascript:void(0);'

        >Auto-update wikipedia entries for all accounts listed on page.</a></div>
    @endif

    @endif

    <br><br><br>

<script>

  @php
    echo "let googleSearchTerm = '" . $googleSearchTerm . "';";
  @endphp

  $(document).ready(function() {

    /* run through all the wikipedia links on the page and click them */
    $("#auto-update").click(function(e) {

      $(".wikipediasearch").each(function () {
        $(this).trigger('click');//for clicking element
      });
    });

    <!-- update deactivated status-->
    $(".deactivatedstatusbtn").click(function(e) {
      e.preventDefault();
      let form = $(this).parents('form:first');
      let itemsId =  $(form).find('[name=update_items_id]').val();
      let url = form.attr('action') + "deactivated";
      let btn = $(this);
      // set the opposite of whatever status it is set to
      let deactivate = $(this).val() == 1 ? 0 : 1;
      $.ajax({
        type: 'POST',
        url: url,
        data: {
          deactivate: deactivate
        },
        success: function (data) {
          if (deactivate) {
            btn.removeClass("btn-danger");
            btn.addClass("btn-info");
            btn.html("Reactivate");
            btn.val(1);
          } else {
            btn.removeClass("btn-info");
            btn.addClass("btn-danger");
            btn.html("Deactivate");
            btn.val(0);
          }
          let id = "#items_id_" + itemsId;
          $(id).show();
          $(id).fadeOut(2000);
        },
        error: function (errors) {
          console.log(errors);
          //alert("There was a problem: " + errors.responseJSON.message);
          $.fn.alertProblem("There was a problem: " + errors.responseJSON.message);
        }
      });

    });
    <!-- end deactivated status -->

    <!-- set query string for google search -->
    $(".googleSearchLink").click(function (e) {
      e.preventDefault();
      let form = $(this).parents('form:first');
      let searchTerm = $(form).find('[name=title]').val();
      searchTerm = googleSearchTerm.replace(/~searchterm~/, searchTerm);
      console.log("searchTerm", searchTerm);
      //https://www.google.com/search?q=nfl&tbs=qdr:y&tbo=1
      window.open("https://google.com/search?q=" + encodeURIComponent(searchTerm) + "&tbs=qdr:y&tbo=1");
    });
    <!-- end set query string for google search -->

    <!-- update item details -->
    $(".submititemdetail").click(function(e) {
      e.preventDefault();
      let form = $(this).parents('form:first');
      let url = form.attr('action');
      let title =  $(form).find('[name=title]').val();
      let itemsId =  $(form).find('[name=update_items_id]').val();
      let titleOld =  $(form).find('[name=title_old]').val();
      let description =  $(form).find('[name=description]').val();
      let errMsg = '';
      if (title.indexOf("~") !== -1) {
        errMsg = "Tilde characters ~ are not allowed in names.";
      } else if (!title) {
        errMsg = 'Name of main account you are submitting cannot be empty. ';
      }
      if (errMsg) {
        $.fn.alertProblem(errMsg);
      } else {
        $.ajax({
          type: 'POST',
          url: url,
          data: {
            title: title,
            title_old: titleOld,
            description: description
          },
          success: function (data) {
            let id = "#items_id_" + itemsId;
            $(form).find('[name=title_old]').val(title);
            $(id).show();
            $(id).fadeOut(2000);
          },
          error: function (errors) {
            console.log(errors);
            //alert("There was a problem: " + errors.responseJSON.message);
            let errMsg = '';
            for(let i in errors.responseJSON.errors) {
              errMsg+=errors.responseJSON.errors[i];
            }
            $.fn.alertProblem(errMsg);
          }
        });
      }
    });
    <!-- end update item details -->

    <!-- update cat ckboxes -->
    let itemsId = 0;
    let updateColumnUrl = "";
    let catsId = 0;
    let action = "";
    $('.catsIdCheckbox').click(function(e) {
      itemsId = $(this).data("items_id");
      updateColumnUrl = "/items/" + itemsId + "/updatecolumn";
      catsId = $(this).val();
      if ($(this).is(":checked")) {
          action = 'add';
      } else {
          action = 'delete';
      }
      console.log(itemsId, updateColumnUrl, catsId);
      update();
    });

    function update() {
      console.log("updateColumnUrl", updateColumnUrl);
      $.ajax({
        type: 'POST',
        url: updateColumnUrl,
        data: {
            cats_id: catsId,
            action: action
        },
        success: function (data) {
            console.log("updated",itemsId);
            let id = "#items_id_cats_id_ck_" + itemsId + "_" + catsId;
            console.log("id",id);
            $(id).show();
            $(id).fadeOut(1000);
        },
        error: function (errors) {
            console.log(errors);
        }
      });
    }

    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    });
</script>


</div>
@endsection