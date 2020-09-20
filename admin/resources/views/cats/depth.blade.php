@extends('layouts.app')
@section('content')

  <div class="container" id="tablegrid">

    <h3>Manage Category Depth</h3>
    <div class='boxCont'>

      <p><b>
      @if(empty($depthObj) || $depthObj->value == 1)
        This site is currently configured to have a single level of categories, like a list.
      @else
        This site is currently configured to have {!! $depthObj->value !!} levels of categories.
      @endif
      </b></p>

      <p><b>Set Category Level</b></p>
      <form class="depthForm"  action='/cats/updatedepth' method='POST'>
        <input type='hidden' id='depthDb' name='depthDb' value='{!! $depthObj->value !!}'>
        {{ csrf_field() }}
        <select id='depth' name='depth'>
        @foreach($depthArr as $key => $depth)
          <option value='{!! $depth !!}'
          @if ($depth == $depthObj->value)
            selected
          @endif
          >{!! $depth !!}</option>
        @endforeach
        </select>
        <button class="btn btn-primary" name="edit">Submit</button>
      </form>
      <hr>
      <p><b>Multi Level Categories Example</b></p>
      <p>An example of multi level of categories can be found in the National Football League:<br><br>
      National Football Conference -> Division "NFC North" -> Team "Bears" -> Players "Corey Levin", "Adam Shaheen", etc<br>
      National Football Conference -> Division "NFC North" -> Team "Lions" -> Players "Reggie Ragland", "Taylor Decker", etc<br>
      American Football Conference -> Division "AFC East" -> Team "Patriots" -> Players "Ron Gronkowski", "Tom Brady", etc<br>
      ... and so on.<br>

      </p>

      <p><b>Single Level Categories Example</b></p>
      <p>An example of a single level of categories can be found in shoppings malls:<br><br>
      Dining -> gjelina, Felix, etc<br>
      Clothing -> Robert Graham, Principessa, etc<br>
      ... and so on.<br>
      <br>

      </p>


    </div>

  </div>

  <script>

    $(document).ready(function() {


      function submitDepthForm(response, nativeThis) {
        if (response == 'ok') {
          nativeThis.submit();
        }
      }

      $(".depthForm").submit(function(e) {
        e.preventDefault();
        let depth = $("#depth").val();
        let depthDb = $("#depthDb").val();
        let hasMultiLevelInCatsTable = @php echo $hasMultiLevelInCatsTable; @endphp;
        let msg = '';
        if (depth != depthDb && hasMultiLevelInCatsTable) {
          msg = 'Categories are already assigned levels and you are changing the range of levels. You will likely have to re-assign a level to a category on the Category page.';
        } else if (depth != depthDb){
          msg = 'You are changing the range of levels a category may be assigned. Update the categories\' levels on the Category page';
        } else {
          msg = 'No change in category level detected.';
        }
        // use native 'this' to avoid jquery event handler retriggering dialogue
        $.fn.alertConfirm(msg, submitDepthForm, this);
      });

    });

</script>

@endsection
