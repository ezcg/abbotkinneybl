@extends('layouts.app')
@section('content')

@php
  if ($maxLevel == 1) {
    $rankArr[0] = "Order (1 is top)";
    for($i = 1; $i <= $catsPaginator->count(); $i++) {
        $rankArr[$i] = $i;
    }
  }
@endphp

<div class="container" id="tablegrid">

  <div id='loadingAnimation' style='display:none;' class="lds-spinner"><div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div></div>

  <!-- ADD CAT FORM-->
  <form action="{{ route('cats.store') }}" method="post">

    {{ csrf_field() }}

    <h2 class="sectionTitle">Add</h2>
    <div class="sectionForm">
    <input type="text" size="30" name="title" placeholder="Category Title" value="{{ old('title') }}">
    <input type="text" size="40" name="description" placeholder="Category Description (optional)" value="{{ old('description') }}">
    <!-- if site has only multi level categories, set text field to declare level -->
      @if ($maxLevel > 1)
        @php
          $levelArr = [];
          $levelArr[0] = 'Category Level';
          for($i = 1; $i <= $maxLevel; $i++) {
              $levelArr[$i] = 'Category Level ' . $i;
          }
        @endphp
        @php echo dropdown('level', $levelArr, 0); @endphp
      @endif
    <input class="btn btn-primary" type="submit" value="Add Category" style='margin-left:4px;'>
    </div>

  </form>

  <div style="clear:both;"> </div>

  <!-- EDIT CAT SECTION-->

  <form action="{{ route('cats.index') }}" method="get">
    <h2 class="sectionTitle">Edit</h2>
    <div class="sectionForm">
      <input type='text' name='search' size='20' placeholder='Search' value="{{ $search }}">
      <input type='submit' value='Search' class='btn btn-primary'>
    </div>
  </form>
  <div style='float:left;margin-top:26px;margin-left:20px;'>
    <a href='/cats/images'>Manage Category Thumbnail Images</a>

  </div>

  @if($usesHashtagCategories)
    <div style='float:left;margin-top:26px;margin-left:20px;'>
      <a href='/cats/hashtags'>Manage Hashtag Category</a>
    </div>
  @endif

  <div style='float:left;margin-top:26px;margin-left:20px;'>
    <a href='/cats/depth'>Manage Category Depth</a>
  </div>

    <div style="clear:both;"></div>

  @if (count($catsPaginator) ==0  && !empty($search))
    <br><p>Not finding anything with search '{!! $search !!}'. <a href='?'>View all categories</a></p>
  @endif

  <div class='catsHierarchyOverlay'>
    <div id='catsHierarchyOverlayToggle'>Toggle Category Hierarchy</div>
    <div style='clear:both;'></div>
    <div id="catsHierarchyCont">
      @php
        // Only display category hierarchy if developing locally or if dealing with multi-level categories, otherwise
        // it's not helpful
        if ($maxLevel > 1 || $env == 'local') {
          displayCats($parentChildFlattenedArr, $catsCollArr, $env);
        }
      @endphp
    </div>
  </div>

    @if (false && $maxLevel > 1 )
    <div style='padding:4px;font-weight:bold;cursor: not-allowed; '>
      <img style='float:left;margin:3px 2px;'
        src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAYAAAA71pVKAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kTtIw1AUhv++qErFwQ4iDhmqk0VREUetQhEqhFqhVQeTm76gSUOS4uIouBYcfCxWHVycdXVwFQTBB4iTo5Oii5R4blJoEeOFw/347/1/zj0X8DcqTDWD44CqWUY6mRCyuVUh/IogQlRj6JaYqc+JYgqe6+sePr7fxXmW970/V6+SNxngE4hnmW5YxBvE05uWznmfOMpKkkJ8TjxqUIPEj1yXXX7jXHTYzzOjRiY9TxwlFoodLHcwKxkq8RRxTFE1yvdnXVY4b3FWKzXW6pO/MJLXVpa5TjWEJBaxBBECZNRQRgUW4rRrpJhI03nCwz/o+EVyyeQqg5FjAVWokBw/+B/8nq1ZmJxwkyIJIPRi2x/DQHgXaNZt+/vYtpsnQOAZuNLa/moDmPkkvd7WYkdA3zZwcd3W5D3gcgcYeNIlQ3KkAJW/UADez+ibckD/LdCz5s6tdY7TByBDs0rdAAeHwEiRstc93t3VObd/77Tm9wMWfHKCeKRYdQAAAAZiS0dEABQADwAL8K0UiAAAAAlwSFlzAAAN1wAADdcBQiibeAAAAAd0SU1FB+QEBQQvLdOVaQIAAADtSURBVCjPldM9TsNQEATgD8WNqXIJXHAL6Ckp4xvEOQtcA4kUIJkKDpDKRSioSJnGoksBApq19LDsJEzz/nZHu7Pz+IsMJZ7Q4jvWOu4nafBJsi9wh3M84hk75LjAFV5xjTe9xC02sR/CGVYRV6SlNpH4g4Vx5EHQdC2U+Ay26giCIuJLIcYyeewIqj0E95Gnxbz3eIhgjjbDNFRNcRvrTe/cYYdpho8Qwj8I8shTRw9jGBJx2fWcqn2IoIq4L8zEvJqY3+kRBJt0zqnDVnsqKPCeOmzM2w94Sbx9Gd5eD3m7wyR6qQd+1az/q34BVDBG22qnrakAAAAASUVORK5CYII='
      >Once a child category is assigned to a parent, that child cannot be assigned to another parent.</div>
    @endif

    <!-- EDIT CATS FORM LIST-->

    @php $levelTitleDisplayedArr = []; @endphp

  @foreach( $catsPaginator as $catKey => $cat )

    @php
      if (!isset($levelTitleDisplayedArr[$cat->level]) && $maxLevel > 1) {
        echo "<p style='margin:15px;font-size:20px;background:#eee;padding:4px;'><b>Level " . $cat->level . " Categories</b></p>";
        $levelTitleDisplayedArr[$cat->level] = 1;
        echo "<div style='border:1px solid #cccccc;padding:10px;'>";
      }
    @endphp

    <form action="{{ route('cats.update', $cat) }}" method="post">
    <input type="hidden" name="on_page" value="{{$catsPaginator->currentPage()}}">
    <input type="hidden" name="cats_id" value="{{$cat->id}}">
    {{ csrf_field() }}


    <table style='width:100%;border:1px solid #ddd;'>

      @if (empty($headerRowSet))
        @php $headerRowSet = 1 @endphp

        <tr style='font-weight:bold;color:#000000;background-color:#eee;border-bottom:1px solid #ddd;'>
        <td>Title</td>
        <td>Description</td>
        <td>@if($maxLevel > 1)Level (1 is top) @else Order (1 is top) @endif </td>
        <td style='text-align:center;width:100px;'>Social Media</td>
        <td colspan='4'> &nbsp; </td>
        </tr>
      @endif

    <tr>
      <td>
        <input type="text" size="30" name="title" value="{{ $cat->title }}">
        <input type="hidden" size="30" name="title_old" value="{{ $cat->title }}">
      </td>
      <td>
        <input type="text" size="40" name="description" value="{{ $cat->description }}">
      </td>
      <td>
        @if ($maxLevel > 1)
          @php echo dropdown('level', $levelArr, $cat->level); @endphp
          <input type="hidden" name="old_level" value="{!! $cat->level !!}">
        @else
          @php echo dropdown('rank', $rankArr, $cat->rank); @endphp
        @endif
      </td>

      <td style='text-align:center;width:100px;'>
        <a style='font-weight:bold;font-size:20px;' href="/socialmedia?cats_id={!! $cat->id !!}">&raquo;</a>
      </td>

      <td>
        <button class="btn btn-primary" name="edit">Submit Edit</button>
      </td>
      <td>
        <button
          name='deactivatedstatus'
          value="{!! $cat->deactivated !!}"
          class='deactivatedstatusbtn btn @if($cat->deactivated == 1) btn-info @elseif($cat->deactivated == -1)btn-primary @else btn-danger @endif'
        >@if($cat->deactivated == -1)Activate &nbsp;&nbsp; &nbsp; @elseif($cat->deactivated == 1)Reactivate @else Deactivate @endif
        </button>
      </td>
      <td>
        <a
          class="confirm_delete_btn btn btn-danger"
          href="/cats/{!! $cat->id !!}&page={!! $catsPaginator->currentPage() !!}"
        >Delete</a>
      </td>
      <td style='width:30px;'>
        <span style='font-size:20px;' class="checkMark" id="cats_id_ck_img_{!! $cat->id !!}">&#10004;</span>
      </td>
    </tr>

    @if ($maxLevel > 1)

      <tr >
        <td colspan='7'>

          <table cellpadding=2 cellspacing=0 border=0>

          @php

          // child category checkboxes

          $count = 0;
          $numColsPerRow = 5;
          foreach($catsCollArr as $catsId => $catsTitle) {

            // don't display child cat that is not an immediate child of the parent being displayed
            if ($cat->level + 1 != $catsLevelArr[$catsId]) {
                continue;
            }

            if ($count == 0) {
                echo "<tr>";
            } elseif ($count % $numColsPerRow == 0) {
                echo "</tr><tr>";
            }
            echo "<td class='catsCkBoxTd' ";
            // don't display checkbox for category that is assigned to a different parent already
            if (isset($catsRelatedArr[$catsId]) && $catsRelatedArr[$catsId] != $cat->id && $catsRelatedArr[$catsId] !== 0) {
                //continue;
                echo "style='opacity: 0.5'";
            }
            echo ">";
            echo "<label style='margin-top:2px;'>";
            echo "<input type='checkbox' name='child_id' value='$catsId' ";
            echo "id='parent_id_cats_id_" . $cat->id . "_" . $catsId . "' class='catsRelCheckbox' ";
            echo "data-parent_id='" . $cat->id . "' data-name='parent_id' ";
            if (isset($catsRelatedArr[$catsId]) && $catsRelatedArr[$catsId] == $cat->id) {
              echo "checked";
            } else if (isset($catsRelatedArr[$catsId]) && $catsRelatedArr[$catsId] != $cat->id && $catsRelatedArr[$catsId] !== 0) {
                //continue;
                //echo "style='display:none;'";
                echo " disabled";
            }

            echo "> ";
            echo $catsTitle;
            echo "</label>";
            echo '<span class="checkMark" id="parent_id_cats_id_ck_' . $cat->id . '_' . $catsId . '">&#10004;</span>';

            if (isset($catsRelatedArr[$catsId])) {
                //echo $catsId . " " . $catsRelatedArr[$catsId];
            } else {
                //echo $catsId;
            }
            echo "</td>";
            $count++;
          }
          //if ($count % $numColsPerRow == 0) {
              echo "</tr>";
          //}
          @endphp

          </table>
        </td>
      </tr>

    @endif

    </table>
    </form>

    @php

      $nextCat = isset( $catsPaginator[$catKey + 1] ) ? $catsPaginator[$catKey + 1] : false;
      if ($nextCat && $nextCat->level != $cat->level && $maxLevel > 1) {
        echo "</div>";
      }
    @endphp


  @endforeach

  {!! $catsPaginator->appends(['sort' => $sort, 'search' => $search])->render() !!}

</div>

<br><br><br><br>

<script>

  $(document).ready(function() {

    <!-- update deactivated status-->
    $(".deactivatedstatusbtn").click(function(e) {
      $("#loadingAnimation").show();
      e.preventDefault();
      let form = $(this).parents('form:first');
      let catsId =  $(form).find('[name=cats_id]').val();
      let url = form.attr('action') + "deactivated";
      let btn = $(this);
      // set the opposite of whatever status it is set to
      let deactivate = $(this).val() == 1 || $(this).val() == -1 ? 0 : 1;
      $.ajax({
        type: 'POST',
        url: url,
        data: {
          deactivate: deactivate
        },
        success: function (data) {
          $("#loadingAnimation").hide();
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
          let id = "#cats_id_ck_img_" + catsId;
          $(id).show();
          $(id).fadeOut(2000);
          if (data.message) {
            $.fn.alertFYI(data.message);
          }
        },
        error: function (errors) {
          $("#loadingAnimation").hide();
          console.log(errors);
          $.fn.alertProblem("There was a problem: " + errors.responseJSON.error);

        }
      });

    });
    <!-- end deactivated status -->

    <!-- delete category -->
    <!-- just use jquery dialog and reload page -->
    $('.deletecat').click(function(e) {
      e.preventDefault();
      $("#loadingAnimation").show();
      let deleteUrl = $(this).attr('href');
      let form = $(this).parents('form:first');
      let r = confirm("Really permanently delete?");
      if (r) {
        $.ajax({
          type: 'GET',
          url: deleteUrl,
          success: function (data) {
            form.fadeOut(1000);
            form.parent().hide();
            $("#loadingAnimation").hide();
          },
          error: function (errors) {
            $("#loadingAnimation").hide();
            //alert(errors.responseJSON.error);
            $.fn.alertProblem(errors.responseJSON.error);

          }
        });
      }
    });
    <!-- end delete category -->

    <!-- update category ckboxes -->
    let childId = 0;
    let updateColumnUrl = "";
    let parentId = 0;
    let action = "";
    $('.catsRelCheckbox').click(function(e) {

      parentId = $(this).data("parent_id");
      updateUrl = "/cats/" + parentId + "/updaterelationship";
      childId = $(this).val();
      if ($(this).is(":checked")) {
        action = 'add';
      } else {
        action = 'delete';
      }
      updateCategoryCkBoxes();
    });

    function updateCategoryCkBoxes() {
      $("#loadingAnimation").show();
      $.ajax({
        type: 'POST',
        url: updateUrl,
        data: {
          child_id: childId,
          action: action
        },
        success: function (data) {
          let id = "#parent_id_cats_id_ck_" + parentId + "_" + childId;
          // remove category from beneath other parents if added
          if (action == 'add') {
            $("input[type=checkbox][value=" + childId + "]").parent().parent().css({ opacity: 0.5 });
            $("input[type=checkbox][value=" + childId + "]").prop("disabled", true);
            // keep the selected one visible
            $("#parent_id_cats_id_" + parentId + "_" + childId).removeAttr('disabled');
            $("#parent_id_cats_id_" + parentId + "_" + childId).parent().parent().css({ opacity: 1 });
          } else {
            //$("input[type=checkbox][value=" + childId + "]").parent().parent().show();
            $("input[type=checkbox][value=" + childId + "]").removeAttr('disabled');
            $("input[type=checkbox][value=" + childId + "]").parent().parent().css({ opacity: 1 });
            // keep the selected one visible
            //$("#parent_id_cats_id_" + parentId + "_" + childId).parent().show();
          }
          $(id).show();
          $(id).fadeOut(1000);
          $("#loadingAnimation").hide();
        },
        error: function (errors) {
          $("#loadingAnimation").hide();
          console.log("error");
          console.log(errors);
        }
      });
    }
    <!-- end update category checkboxes -->

    <!-- update cat details (not categories) -->
    let catsId = 0;
    let updateCategoryDetailsUrl = '';
    let title = '';
    let titleOld = '';
    let description = '';
    let level = 0;
    let levelOld = 0;
    let deactivated = 0;
    let rank = 0;
    $("button[name = 'edit']").click(function(e) {
      e.preventDefault();
      let form = $(this).parents('form:first');
      updateCategoryDetailsUrl = form.attr('action');
      title =  $(form).find('[name=title]').val();
      titleOld =  $(form).find('[name=title_old]').val();
      catsId =  $(form).find('[name=cats_id]').val();
      description =  $(form).find('[name=description]').val();
      level = $(form).find('[name=level]').find('option:selected').val();
      rank = $(form).find('[name=rank]').find('option:selected').val();
      levelOld =  $(form).find('[name=level_old]').val();
      let errMsg = '';
      if (title.indexOf("~") !== -1) {
        errMsg = "Tilde characters ~ are not allowed in names.";
      }
      if (errMsg) {
        $.fn.alertProblem(errMsg);
      } else {
        updateCategoryDetails();
      }
    });

    function updateCategoryDetails() {
      $("#loadingAnimation").show();
      console.log("posting to", updateCategoryDetailsUrl);
      $.ajax({
        type: 'POST',
        url: updateCategoryDetailsUrl,
        data: {
          level: level,
          level_old: levelOld,
          title: title,
          title_old: titleOld,
          description: description,
          rank:rank
        },
        success: function (data) {
          console.log("updated");
          $("#loadingAnimation").hide();
          // let id = "#cats_id_ck_img_" + catsId;
          // console.log("id",id);
          // $(id).show();
          // $(id).fadeOut(2000);
          //if (data.reload) {
            window.location.reload(true);
          //}
        },
        error: function (errors) {
          $("#loadingAnimation").hide();
          let errMsg = errors.responseJSON.message;
          let keysArr = Object.keys(errors.responseJSON.errors);
          keysArr.forEach(function(key) {
            let errArr = errors.responseJSON.errors[key];
            errArr.forEach(function(error) {
              errMsg+= error;
            });
          });

          $.fn.alertProblem(errMsg);

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


@endsection