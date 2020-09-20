import React, { Component } from 'react';
let images = require('./images');
let googleMapIcon = images.googleMapIcon;
let helpersBase = require('../helpers/base');

class Info extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {

    if (this.props.displayInfo == 0) {
      return null;
    }

    let info = '';
    let lat = '';
    let lon = '';
    let day = '';
    let mapLink = '';
    let mapIconSrcTag = '';
    let mapIconLink = '';
    let itemObj = this.props.itemObj;
    let websiteDisplay = '';
    let mapBtnVisibility = { display: 'none' };

    switch (new Date().getDay()) {
      case 0:
        day = 'Sun';
        break;
      case 1:
        day = 'Mon';
        break;
      case 2:
        day = 'Tue';
        break;
      case 3:
        day = 'Wed';
        break;
      case 4:
        day = 'Thu';
        break;
      case 5:
        day = 'Fri';
        break;
      case 6:
        day = 'Sat';
    }

    if (itemObj.lat) {
      lat = itemObj.lat;
    }
    if (itemObj.lon) {
      lon = itemObj.lon;
    }
    if (itemObj.address) {
      itemObj.address = itemObj.address.replace(
        'Abbot Kinney Blvd',
        'Abbot Kinney Bl'
      );
      itemObj.address = itemObj.address.replace('Los Angeles', 'LA');
      info += itemObj.address;
      info += '<br />';
    }
    if (itemObj.hours) {
      var hours = '';
      for (var i in itemObj.hours) {
        if (itemObj.hours[i].includes(day)) {
          hours += ' ' + itemObj.hours[i] + '<br />';
        }
      }
      info = info + "<div class='hoursCont'>" + hours + '</div>';
    }
    if (itemObj.phone) {
      info += itemObj.phone;
      info += '<br />';
    }

   info+='<div class="linksOutCont">';
    if (itemObj.social_media_accounts_arr) {
      for(let site in itemObj.social_media_accounts_arr) {
        info+="<a class='platformIconInfo' href='" + itemObj.social_media_accounts_arr[site] + "' target='_blank'>";
        info+= helpersBase.getPlatformIconSrcWithSite(site, 'platformIconSrcInfo');
        info+= "</a>";
      }
      info+="<br />";
    }

    if (itemObj.website) {
      websiteDisplay = itemObj.website.replace('http://', '');
      websiteDisplay = websiteDisplay.replace('https://', '');
      websiteDisplay = websiteDisplay.replace('www.', '');
      info +=
        "<a class='websiteLinkOut' target='_blank' href='" +
        itemObj.website +
        "'>" +
        websiteDisplay +
        '</a>';
    }
    info+='</div>';

    if (lat) {
      mapBtnVisibility = { display: 'block' };
      if (
        /* if we're on iOS, open in Apple Maps */
        navigator.platform.indexOf('iPhone') != -1 ||
        navigator.platform.indexOf('iPod') != -1 ||
        navigator.platform.indexOf('iPad') != -1
      ) {
        mapLink =
          'maps://maps.google.com/maps?daddr=' +
          encodeURIComponent(lat + ',' + lon) +
          '&amp;ll=';
      } else {
        /* else use Google */
        mapLink =
          'https://maps.google.com/maps?q=' +
          encodeURIComponent(lat + ',' + lon);
        mapLink =
          'https://www.google.com/maps/place/' +
          encodeURIComponent(itemObj.address) +
          '/' +
          encodeURIComponent(lat + ',' + lon) +
          '/@' +
          encodeURIComponent(lat + ',' + lon);
        //mapLink = "https://www.google.com/maps/search/?api=1&map_action=map&query=" + encodeURIComponent(itemObj.lat + ',' + itemObj.lon);
        // https://www.google.com/maps/place/1329+Abbot+Kinney+Blvd,+Venice,+CA+90291/@33.9911109,-118.4691728,17z/data=!3m1!4b1!4m5!3m4!1s0x80c2babf642f8c79:0x9bae98ce4bf26899!8m2!3d33.9911109!4d-118.4669841
      }

      mapIconSrcTag = '<img  src="' + googleMapIcon + '">';
      mapIconLink = '<a target="_blank" href="' + mapLink + '">' + mapIconSrcTag + '</a>';
    }

    if (itemObj.history) {
      info += itemObj.history;
      if (itemObj.history_url) {
        let hostname = (new URL(itemObj.history_url)).hostname;
        let tmp = hostname.split(".");
        let domain = tmp[tmp.length - 2] + "." + tmp[tmp.length - 1];
        info+= ' <a target="_blank" className="historyUrl" href="' + itemObj.history_url + '">' + domain + '</a>';
      }
    }

    if (info === '') {
      return null;
    }

    return (
      <div>
        {/* hours, address, etc*/}
        <div
          className="itemTextCont infoCont"
          dangerouslySetInnerHTML={{ __html: info }}
        />
        <div className="mapBtnCont" style={mapBtnVisibility}>
          <div dangerouslySetInnerHTML={{ __html: mapIconLink }} />
        </div>
      </div>
    );
  }
}

export default Info;
