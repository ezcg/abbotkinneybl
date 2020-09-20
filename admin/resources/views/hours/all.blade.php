@extends('layouts.app')
@section('content')

    <link rel="stylesheet" type="text/css" href="/css/table.css">

    <div class="container" id="tablegrid">

      <p><b>Overview page for managing hours of operation. Helpful for seeing who is missing hours info.</b></p>

      <form>

        @php $numCols = 6; @endphp
        <table width='100%' cellpadding='4' cellspacing='0' style='border:1px solid black;'>
          <tr style='border-bottom:1px solid black;background-color:#f5f8fa'><td colspan={!! $numCols !!}>
          <label><b>View only Main Accounts missing hours:</b> <input
              id='hoursMissing'
              type='checkbox'
              name='hours_missing'
              value='1'
          @if ($missing)
              checked
          @endif
          ></label>
          </td></tr>

          <tr><td colspan='{!! $numCols !!}'><b>Exclude categories</b></tr>
          <tr>

        @php
        $count=0;
        foreach($catsArr as $catsId => $catsName) {
          $count++;
          echo "<td>";
          echo "<label>";
          echo "<input class='hoursExcludeCatsCkbk' type='checkbox' name='excludeCatsArr[]' value='$catsId'";
          if (is_array($excludeCatsArr) && in_array($catsId, $excludeCatsArr)) {
              echo " checked";
          }
          echo "> $catsName";
          echo "</label>";
          echo "</td>";
          if ($count % $numCols == 0) {
              echo "</tr>";
          }
        }
        $numColsRem = $numCols - ($count % $numCols);
        for($i = 0; $i < $numColsRem; $i++) {
            echo "<td> &nbsp; </td>";
        }
        if ($numColsRem) {
            echo "</tr>";
        }

        @endphp
        <tr><td align='center' colspan={!! $numCols !!}><button class="btn btn-primary">Submit</button></td></tr>
        </table>
      </form>

      <br>

      <table cellpadding='4' cellspacing='0' style='border:1px solid black;'>
      <tr style='border-bottom:1px solid black;background-color:#f5f8fa'>
          <td style='font-weight:bold;'>Main Account</td>
          <td align='center' style='font-weight:bold;'>View Social Media Accounts</td>
          <td align='center' style='font-weight:bold;'>Hours</td>
      </tr>
      @foreach($itemsArr as $itemsObj)
        <tr>
          <td><a href='/items?items_id={!! $itemsObj->id !!}'>{!! $itemsObj->title !!}</a></td>
          <td align='center'><a href='/socialmediaaccounts/admin?items_id={!! $itemsObj->id !!}&title={!! urlencode($itemsObj->title) !!}'
            >&raquo;</a></td>
          <td align='center'>
            @if ($itemsObj->hours)
              <a href='/hours/index?items_id={!! $itemsObj->id !!}&title={!! urlencode($itemsObj->title) !!}'>Edit</a>
            @else
              <a href='/hours/index?items_id={!! $itemsObj->id !!}&title={!! urlencode($itemsObj->title) !!}'>Create</a>
            @endif
          </td>
        </tr>
      @endforeach
      </table>
  </div>

  <br />
  <br />
  <br />
  <br />
  <br />
  <br />

@endsection