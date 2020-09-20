import React
  from "react";
let {siteConfigObj} = require('../helpers/siteconstants');

export default function SubHeader () {

  let subheaderTextDisplay = 'none';
  if (siteConfigObj.subheaderText) {
    subheaderTextDisplay = 'block';
  }
  subheaderTextDisplay = {display:subheaderTextDisplay};
  let subheaderTextDisplay2 = 'none';
  if (siteConfigObj.subheaderText2) {
    subheaderTextDisplay2 = 'block';
  }
  subheaderTextDisplay2 = {display:subheaderTextDisplay2};

  return <div>
    <div style={subheaderTextDisplay} className="indexSubheaderText">{siteConfigObj.subheaderText}</div>
    <div style={subheaderTextDisplay2} className="indexSubheaderText2">{siteConfigObj.subheaderText2}</div>
    </div>

}


