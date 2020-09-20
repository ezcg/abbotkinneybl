@extends('layouts.app')
@section('content')

<div class="container" id="tablegrid">

<div style='float:left;'>
  <a href='{{ route('items.index', ["items_id" => $itemsId]) }}'><b>{{ $title }} Main Account</b></a>
</div>

<style>
  tr:nth-child(even) {background: #ddd}
  tr:nth-child(odd) {background: #eee}
</style>

<div style='clear:both;'></div><br>

<form method="POST" action="{{ route('hours.store', $id) }}">
{{ csrf_field() }}
<input type = "hidden" name="items_id" value="{!! $itemsId !!}">
<input type = "hidden" name="title' value = "{!! $title !!}">
<input type = "hidden" name="id" value= "{!! $id !!}">

<table align='center' style='border:1px solid black;'>
<tr style='font-weight:bold;border-bottom:1px solid black;background-color:#f5f8fa;'>
<td>Day</td>
<td>Start</td>
<td> &nbsp;</td>
<td> &nbsp; </td>
<td>End</td>
<td> &nbsp; </td>
<td width=50> &nbsp; &nbsp; &nbsp; </td>
</tr>

@php
echo "<tr>";
echo "<td>";
echo dayOfWeekDD('dayOfWeekArr', 'add', 'add');
echo "</td><td>";
echo twelveHourDD('startTimeHourArr', 'add', 'add');
echo " : ";
echo minuteDD('startTimeMinuteArr', 'add', 'add');
echo "</td><td>";
echo ampmRadio('startTimeAmpmArr', 'am', 'add');
echo "</td><td> &nbsp; </td><td>";
echo twelveHourDD('endTimeHourArr', 'add', 'add');
echo " : ";
echo minuteDD('endTimeMinuteArr', 'add', 'add');
echo "</td><td>";
echo ampmRadio('endTimeAmpmArr', 'pm', 'add');
echo "</td>";
echo "<td width=50>";
echo " &nbsp; ";
echo "</td>";
echo "</tr>";
@endphp

<tr>
<td colspan='7'>

<input type = 'radio' name='no_yelp_update' value = "1" @if ($noYelpUpdate == 1) checked="checked" @endif >
Do NOT overwrite this info with info automatically found on yelp.com
<br />
<input type = 'radio' name='no_yelp_update' value = "0" @if ($noYelpUpdate == 0) checked="checked" @endif >
Periodically and automatically overwrite this info with info found on yelp.com

</td>
</tr>
<tr>
<td align='center' colspan='7'>
<input id="add" class="btn btn-info" type="submit" value="Add">
</td>
</tr>
</table>
<br>
</form>

@if (!empty($hoursArr))

<form method="POST" action="{{ route('hours.update', $id) }}">

  {{ csrf_field() }}
  <input type='hidden' name='items_id' value="{!! $itemsId !!}">
  <input type='hidden' name='title' value="{!! $title !!}">
  <input type='hidden' name='id' value="{!! $id !!}">

  <table align='center' style='border:1px solid black;'>
  <tr style='font-weight:bold;border-bottom:1px solid black;background-color:#f5f8fa;'>
  <td>Day</td>
  <td>Start</td>
  <td> &nbsp;</td>
  <td> &nbsp; </td>
  <td>End</td>
  <td> &nbsp; </td>
  <td width=50> Delete </td>
  </tr>

  @php

  foreach($hoursArr as $dayOfWeek => $arr) {
    foreach($arr as $index => $row) {
      echo "<tr>";
      echo "<td>";
      echo dayOfWeekDD('dayOfWeekArr', $dayOfWeek, $index);
      echo "</td><td>";
      echo twelveHourDD('startTimeHourArr', $row['start_time_hour'], $index);
      echo " : ";
      echo minuteDD('startTimeMinuteArr', $row['start_time_minute'], $index);
      echo "</td><td>";
      echo ampmRadio('startTimeAmpmArr', $row['start_time_ampm'], $index);
      echo "</td><td> &nbsp; </td><td>";
      echo twelveHourDD('endTimeHourArr', $row['end_time_hour'], $index);
      echo " : ";
      echo minuteDD('endTimeMinuteArr', $row['end_time_minute'], $index);
      echo "</td><td>";
      echo ampmRadio('endTimeAmpmArr', $row['end_time_ampm'], $index);
      echo "</td>";
      echo "<td align='center'>";
      echo "<input type='checkbox' name='deleteArr[$index]' value='1'>";
      echo "</td>";
      echo "</tr>";
    }
  }

  @endphp

  <tr>
  <td align='left' colspan='7'>

  <input type = 'radio' name='no_yelp_update' value = "1" @if ($noYelpUpdate == 1) checked="checked" @endif >
  Do NOT overwrite this info with info automatically found on yelp.com
  <br />
  <input type = 'radio' name='no_yelp_update' value = "0" @if ($noYelpUpdate == 0) checked="checked" @endif >
  Periodically and automatically overwrite this info with info found on yelp.com

  </td>
  </tr>
  <tr>
  <td align='center' colspan='7'>

  <input id="edit" class="btn btn-info" type="submit" value="Submit Edit">

  </td>
  </tr>
  </table>
  <br>

@endif

</form>
</div>

<br><br>

<script>

  $(document).ready(function() {

    $("#edit").click(function(e) {

      let form = $(this).parents('form:first');
      let startTimeHourArr = [];
      let startTimeMinuteArr = [];
      let startTimeAmpmArr = [];
      let endTimeHourArr = [];
      let endTimeMinuteArr = [];
      let endTimeAmpmArr = [];
      let dayOfWeekArr = [];
      $(form).find('select[name^="dayOfWeekArr"]').each(function() {
        dayOfWeekArr.push($(this).find(":selected").val());
      });
      $(form).find('select[name^="startTimeHourArr"]').each(function() {
        startTimeHourArr.push($(this).find(":selected").val());
      });
      $(form).find('select[name^="startTimeMinuteArr"]').each(function() {
        startTimeMinuteArr.push($(this).find(":selected").val());
      });
      $(form).find('input[name^="startTimeAmpmArr"]:checked').each(function() {
        startTimeAmpmArr.push($(this).val());
      });
      $(form).find('select[name^="endTimeHourArr"]').each(function() {
        endTimeHourArr.push($(this).find(":selected").val());
      });
      $(form).find('select[name^="endTimeMinuteArr"]').each(function() {
        endTimeMinuteArr.push($(this).find(":selected").val());
      });
      $(form).find('input[name^="endTimeAmpmArr"]:checked').each(function() {
        endTimeAmpmArr.push($(this).val());
      });

      for(let i = 0; i < startTimeHourArr.length; i++) {
        let startTimeOrig = Number(startTimeHourArr[i]);

        let startTime = startTimeOrig;
        if (startTimeAmpmArr[i] == 'pm') {
          startTime = startTimeOrig + 12;
        }
        let endTimeOrig = Number(endTimeHourArr[i]);
        let endTime = endTimeOrig;
        if (endTimeAmpmArr[i] == 'pm') {
          endTime = endTimeOrig + 12;
        }

        if (startTime > endTime) {
          e.preventDefault();
          let errMsg = "Start hour must be earlier than end hour. ";
          errMsg+= "See: <br>";
          errMsg+= dayOfWeekArr[i] + " " + startTimeOrig + ":" + startTimeMinuteArr[i] + startTimeAmpmArr[i] + " ";
          errMsg+= endTimeOrig + ":" + endTimeMinuteArr[i] + endTimeAmpmArr[i];
          $.fn.alertProblem(errMsg);
        }
      }

    });

    $("#add").click(function (e) {

      let errMsg = '';
      if ($("#dayOfWeekArr_add").val() == 'add') {
        errMsg+= "You must select a day of week. <br>";
      }
      if ($("#startTimeHourArr_add").val() == 'add') {
        errMsg+= "You must select a start hour. <br>";
      }
      if ($("#startTimeMinuteArr_add").val() == 'add') {
        errMsg+= "You must select a start minute. <br>";
      }
      if ($("#endTimeHourArr_add").val() == 'add') {
        errMsg+= "You must select an end hour. <br>";
      }
      if ($("#endTimeMinuteArr_add").val() == 'add') {
        errMsg+= "You must select an end minute. <br>";
      }

      if (errMsg) {
        e.preventDefault();
        $.fn.alertProblem(errMsg);
      } else {
        let startHour = Number($("#startTimeHourArr_add").val());
        let endHour = Number($("#endTimeHourArr_add").val());
        let form = $(this).parents('form:first');
        if ($(form).find('input[class=startTimeAmpmArr]:checked').val() == 'pm') {
          startHour+=12;
        }
        if ($(form).find('input[class=endTimeAmpmArr]:checked').val() == 'pm') {
          endHour+=12;
        }
        if (startHour > endHour) {
          $.fn.alertProblem("Your start hour must be earlier than the end hour. ");
          e.preventDefault();
        }

      }


    });

  });

</script>

@endsection
