import React from "react";

export default function Spinner ({displayStyle, topStyle, leftStyle}) {

  if (!displayStyle) {
    displayStyle = 'block';
  }

  if (!topStyle) {
    topStyle = "0px";
  }

  if (!leftStyle) {
    leftStyle = "40%";
  }


  let style = {display:displayStyle, top:topStyle, left:leftStyle};

  return (
    <div style={style} className="lds-spinner">
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
      <div></div>
      <div></div>
    </div>
  )

}