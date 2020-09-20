@extends('layouts.app')
@section('content')
@php //dd($contactInfoArr); @endphp
<div class="container" id="tablegrid">

    <div style='float:left;'>
        <a href='{{ route('items.index', ["items_id" => $itemsId]) }}'>{{ $contactInfoArr['business'] }} Main Account</a>
    </div>
    @if($contactInfoArr['id'] > 0)
      <div style='float:right;'>
          <a
              class="delete_confirm_btn btn btn-danger"
              href='{{ route('contactinfo.delete', [$contactInfoArr['id']]) }}'
          >Delete</a>
      </div>
    @endif
    <br><br>

    @php

      if($contactInfoArr['id'] > 0) {
          $path = 'contactinfo.update';
          $id = $contactInfoArr['id'];
      } else {
          $path = 'contactinfo.store';
          $id = '';
      }

    @endphp

    <form method="POST" action="{{ route($path, $id) }}">
      {{ csrf_field() }}
      <input type='hidden' name = 'items_id' value ="{!! $contactInfoArr['items_id'] !!}">

    <div class="form-group">

      Business Name - Search
        <a
          href='https://google.com/search?q={!! urlencode($contactInfoArr['title']) !!}+{!! urlencode($location) !!}'
          target='_blank'
        >Google.com</a>
      <input type='text' name = 'business' value="{!! $contactInfoArr['business'] !!}" class = 'form-control'>

      Website
      <input type='text' name = 'website' value = "{!! $contactInfoArr['website'] !!}" class = 'form-control'>

      <hr>

      Yelp ID (optional)
      @if ($contactInfoArr['biz_id'])
        - <a href='https://www.yelp.com/biz/{!! $contactInfoArr['biz_id'] !!}' target='_blank'>Yelp Page</a>
      @endif
      - <a href='https://www.yelp-support.com/article/What-is-my-Yelp-Business-ID?l=en_US' target='_blank'>Info on Yelp ID</a>
      - <a href='https://www.yelp.com/search?find_desc={!! $contactInfoArr['title'] !!}&find_loc={!! $yelpFindLoc !!}' target='_blank'>Search Yelp</a>
      <input type='text' name = 'biz_id' value = "{!! $contactInfoArr['biz_id'] !!}" class = 'form-control'>
      <p>If the Business ID above is a valid yelp.com ID, an automatic periodic process can update this info with
            what is found on yelp.com for that Business ID.</p>
        <input type = 'radio' name='no_yelp_update' value = "1" @if ($contactInfoArr['no_yelp_update'] == 1) checked="checked" @endif >
        Do NOT automatically overwrite any info below with info found on yelp.com
        <br />
        <input type = 'radio' name='no_yelp_update' value = "0" @if ($contactInfoArr['no_yelp_update'] == 0) checked="checked" @endif >
        Periodically and automatically overwrite all info below with info found on yelp.com

        <hr>

        Street Address
        <input type='text' name = 'address' value = "{!! $contactInfoArr['address'] !!}" class = 'form-control'>

        City
        <input type='text' name = 'city' value = "{!! $contactInfoArr['city'] !!}"  class = 'form-control'>

        State
        <input type='text' name = 'state' value = "{!! $contactInfoArr['state'] !!}" class = 'form-control'>

        Postal Code (Zip)
        <input type='text' name = 'postal_code'  value = "{!! $contactInfoArr['postal_code'] !!}" class = 'form-control'>

        Phone
        <input type='text' name = 'phone_number'  value = "{!! $contactInfoArr['phone_number'] !!}" class = 'form-control'>

        Email
        <input type='text' name = 'email'  value = "{!! $contactInfoArr['email']  !!}" class = 'form-control'>

        Latitude *
        <input type='text' name = 'lat' value = "{!! $contactInfoArr['lat'] !!}" class = 'form-control'>

        Longitude *
        <input type='text' name = 'lon' value = "{!! $contactInfoArr['lon'] !!}" class = 'form-control'>

        * Latitude and Longitude generated automatically using Google maps with the given address. You can overwrite
        them above.
        <a href='javascript:void(0);' id='viewOnGoogleMapsLink'>View on Google Maps</a>

    </div>

    <input class="btn btn-info" type="submit" value="Submit">
    </form>

    <br><br>

</div>

<script>

  $(document).ready(function() {

    $("#viewOnGoogleMapsLink").click(function(e) {

      e.preventDefault();
      let form = $(this).parents('form:first');
      let business = $(form).find('[name=business]').val();
      let lat =  $(form).find('[name=lat]').val();
      let lon =  $(form).find('[name=lon]').val();
      let address =  $(form).find('[name=address]').val();
      let city =  $(form).find('[name=city]').val();
      let state =  $(form).find('[name=state]').val();
      let postal_code =  $(form).find('[name=postal_code]').val();

      if (!address || !city || !state || !postal_code) {
        $.fn.alertProblem("Enter address, city, state, postal code, latitude and longitude into the text fields in order to view on Google maps.");
        return;
      }

      let url = 'https://www.google.com/maps/place/';
      url+= encodeURIComponent(address + "," + city + "," + state + " " + postal_code);
      url+='/@' + lat + ',' + lon;
      window.open(url, "_blank");

    });

  });

</script>

@endsection