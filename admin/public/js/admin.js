$(document).ready(function() {
let count=0;
  <!-- handle wiki search -->
  $(".wikipediasearch").click(function(e) {

    // divId and wikisearchresults_id are 'items_id'
    let divId = $(this).data("wikisearchresults_id");
    let form = $(this).parents('form:first');
    let searchTerm =  $(form).find('[name=title]').val();
    // If on /widkipedia manual add page and the url field has a value, parse that url and use it as a search term
    // eg. https://en.wikipedia.org/wiki/Tim_Ryan_(Ohio_politician) - use "Tim_Ryan_(Ohio_politician)"
    let wikiUrl = $(".wikipediaUrlField").val();
    if (wikiUrl) {
      let arr = wikiUrl.split("/");
      searchTerm = arr.pop();
    }
    let itemsId =  $(form).find('[name=update_items_id]').val();
    if (!itemsId) {
      itemsId = divId;
    }
    let url = '/wikipedia/' + itemsId + '/searchwikipedia';

    $.ajax({
      type: 'GET',
      url: url,
      data: {
        search_term: searchTerm,
        id: itemsId
      },
      success: function (data) {

        if (!data) {
          data = 'No data returned from server.';
        } else {
          data = JSON.parse(data);
        }

        // if we're in the wikipedia section, in the edit form (and not the main account page)
        let onWikipediaFormPage = window.location.pathname.indexOf("wikipedia") !== -1;
        let onWikipediaEditFormPage = window.location.pathname.indexOf("edit") !== -1;
        if (onWikipediaFormPage && data.error_msg) {
          $.fn.alertProblem(data.error_msg);
        } else if (onWikipediaFormPage && !onWikipediaEditFormPage) {
        // we're on the add form page and successfully auto-added the wiki entry. Redirect to edit page
        // in case they want to deactivate or edit
        // /wikipedia/4187/edit?title=Barack%20Obama
          let url_string = window.location.href
          let urlObj = new URL(url_string);
          let page = urlObj.searchParams.get("page");
          let view = urlObj.searchParams.get("view");
          if (!view) {
            view = 'unassociated';
          }
          let url = "https://" + window.location.hostname + "/wikipedia/" + itemsId + "/edit?title=" + searchTerm;
          url+="&page=" + page + "&view=" + view;
          window.location = url

        } else if (onWikipediaFormPage && onWikipediaEditFormPage) {
        // we're on the edit form page
          if (data.url) {
            $(".wikipediaUrlField").val(data.url);
            let id = "#wikisearchresults_" + divId;
            $(id).html(data.text);
          }
        } else {

          let text = data.text;

          if (data.cats_id) {
            let catsCkboxId = "items_id_cats_id_" + itemsId + "_" + data.cats_id;
            if ($('#' + catsCkboxId).prop('checked') == false) {
              $('#' + catsCkboxId).click();
              text = '<b>Automatically set to ' + data.team + ' above.</b><br> ' + text;
            } else {
              text = '<b>Already set to ' + data.team + ' above.</b><br> ' + text;
            }
          }

          if (data.deactivate == 1 && $(form).find('[name=deactivatedstatus]').val() == 0) {
            $(form).find('[name=deactivatedstatus]').click();
          }

          let id = "#wikisearchresults_" + divId;
          let idCont = '#wikisearchresultscont_' + divId;
          $(id).html(text);
          $(idCont).show();

        }

      },
      error: function (errors) {
        $.fn.alertProblem("There was a problem: " + errors.responseJSON.message);
      }
    });
  });

  $(".hide_wikisearchresults_X").click(function(e) {
    e.preventDefault();
    let id = $(this).data('id');
    $("#wikisearchresultscont_" + id).hide();
  });
  <!--end wikisearch -->

  // START DIALOGUE MODALS
  // listens for click on hyperlink that has class confirm_delete_btn, confirms action and fires href if true
  // #delete-confirm and #confirm click text is set in footer of layouts/app.blade by id
  // post methods require rethink
  // $.fn.alertProblem and $.fn.alertConfrim - just pass in text to be displayed

  $(".confirm_delete_btn").click(function(e) {
    e.preventDefault();
    let btn = $(this);
    $("#delete-confirm").show();
    $( "#delete-confirm" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Delete": function() {
          window.open(btn.attr('href'), "_self");
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  });

  <!-- confirm link is to be clicked on-->
  $(".confirmClick").click(function(e) {
    e.preventDefault();
    let btn = $(this);
    $("#confirm-click").show();
    $( "#confirm-click" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Proceed": function() {
          window.open(btn.attr('href'), "_self");
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  });

  $.fn.alertProblem = function(text) {
    $("#alert-problem-text").html(text);
    $("#alert-problem").dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Ok": function () {
          $(this).dialog("close");
        }
      }
    });
  }

  $.fn.alertFYI = function(text) {
    $("#alert-fyi-text").html(text);
    $("#alert-fyi").dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Ok": function () {
          $(this).dialog("close");
        }
      }
    });
  }

  $.fn.alertConfirm = function(text, callback, nativeThis) {

    $("#alert-confirm-text").html(text);

    $("#alert-confirm").dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        Ok: function () {
          $(this).dialog("close");
          callback("ok", nativeThis);
        },
        Cancel: function() {
          $( this ).dialog( "close" );
          callback("cancel");
        }
      }
    });

  }
  // END DIALOGUE MODALS

  $("#catsHierarchyOverlayToggle").click(function() {
      $("#catsHierarchyCont").toggle();
  });

  $("#missing").click(function() {
      if ($(this).prop("checked") == true) {
          document.cookie = "missing=1";
      } else {
          document.cookie = "missing=0";
      }

  });

  $("#hoursMissing").click(function() {
    if ($(this).prop("checked") == true) {
      document.cookie = "hours_missing=1";
    } else {
      document.cookie = "hours_missing=0";
    }

  });

  $('.excludeCatsCkbk').click(function() {

      let exclude_cats_json_str = getCookie('exclude_cats_json_str');
      let excludeCatsObj = {};
      if (exclude_cats_json_str) {
          excludeCatsObj = JSON.parse(exclude_cats_json_str);
      }
      if ($(this).prop("checked") == true) {
          let catsId = $(this).val();
          excludeCatsObj[catsId] = catsId;

      } else {
          let catsId = $(this).val();
          delete excludeCatsObj[catsId];
      }

      exclude_cats_json_str = JSON.stringify(excludeCatsObj);
      document.cookie = "exclude_cats_json_str=" + exclude_cats_json_str;

  });


  $('.hoursExcludeCatsCkbk').click(function() {

    let hours_exclude_cats_json_str = getCookie('hours_exclude_cats_json_str');
    let hours_excludeCatsObj = {};
    if (hours_exclude_cats_json_str) {
      hours_excludeCatsObj = JSON.parse(hours_exclude_cats_json_str);
    }
    if ($(this).prop("checked") == true) {
      let catsId = $(this).val();
      hours_excludeCatsObj[catsId] = catsId;

    } else {
      let catsId = $(this).val();
      delete hours_excludeCatsObj[catsId];
    }

    hours_exclude_cats_json_str = JSON.stringify(hours_excludeCatsObj);
    document.cookie = "hours_exclude_cats_json_str=" + hours_exclude_cats_json_str;

  });

  function getCookie(cname) {
      var name = cname + "=";
      var decodedCookie = decodeURIComponent(document.cookie);
      var ca = decodedCookie.split(';');
      for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
              c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
              return c.substring(name.length, c.length);
          }
      }
      return "";
  }

  function setCookie(cname, cvalue, exdays) {
      var d = new Date();
      d.setTime(d.getTime() + (exdays*24*60*60*1000));
      var expires = "expires="+ d.toUTCString();
      document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

});
