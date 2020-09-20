import React, { Component } from 'react';
import fetch from 'isomorphic-unfetch'
let {siteConfigObj} = require('../helpers/siteconstants');

class FooterLinks extends Component {

  constructor(props) {
    super(props);
  }

  render() {

    let arr = this.props.footerLinksArr;
    let factor = Object.values(arr).length > 0 ? Object.values(arr).length : 1;
    let width =  factor * 58;
    let content = '';
    if (siteConfigObj.SITE == 'nfl') {
        content+="<p align='center'><b>Site not affiliated with the NFL</b></p>";
    }
    content+= "<div class='footerLinkCont' style='width:" + width + "px;'>";
    for (let i = 0; i < arr.length; i++) {
        let link = arr[i].link;
        let name = arr[i].name;
        let imgsrc = arr[i].imgsrc;
        content += "<div class='footerLink'>";
        content += "<a href='" + link + "' ";
        if (name !== 'Homepage' && arr[i].open_link_in_new_window) {
            content += "target='_blank'";
        }
        content += ">";
        if (imgsrc) {
          content += "<img class='socialmediaFooterImageLink' src='" + imgsrc + "' data-toggle='tooltip' title='" + name + "'>";
        } else {
          content += "<span class='socialMediaFooterTextLink'>" + name + "</span>";
        }
        content += '</a>';
        content += '</div>';
    }
    content += "<div style='clear:both;'></div>";
    content += "</div>";
    content += '</div>';

    return (
      <div>
        <div
          dangerouslySetInnerHTML={{ __html: content }}
        />
      </div>
    );
  }
}

export default FooterLinks;
