import React, { useState } from "react";
let helpersBase = require('../helpers/base');

export default function SelectRow({teamObj, setSelectedId, unsetSelectedId, getSelectedIdArr}) {

  let [visibility, setVisibility] = useState("hidden");
  let [checkmarkClass, setCheckmarkClass] = useState('checkmark');

  let selectedIdArr = getSelectedIdArr();

  function mngCheckmarkClass(e, id) {
    e.preventDefault();
    if (selectedIdArr.includes(id)) {
      setCheckmarkClass("checkmark");
      setVisibility("hidden");
      unsetSelectedId(id);
    } else {
      setCheckmarkClass("selectedCheckmark");
      setVisibility("visible");
      setSelectedId(id);
    }

  };

  const showCheckmark = (e) => {
    e.preventDefault();
    setVisibility("visible");
  };

  const hideCheckmark = (e, id) => {
    e.preventDefault();
    if (false === selectedIdArr.includes(id)) {
      setVisibility("hidden");
    }
  };

  // Issues with "Select All" displaying checkmarks
  // Can't get component to re-render after selected ids passed in and getting 'too many render' errors when
  // passing in ids as props, so dynamically naming the key and setting styles like this
  let selected = '';
  if (0 && selectedIdArr.length) {
    if (selectedIdArr.includes(teamObj.id)) {
      selected = 'selected_';
      visibility = 'visibile';
      checkmarkClass = 'selectedCheckmark';
    } else {
      visibility = 'hidden';
      checkmarkClass = 'checkmark';
    }
  }

  let checkmarkStyle = {visibility:visibility};

  return (

    <div key={selected + 'id_' + teamObj.id} className="teamRowCont"
         onMouseEnter = {e => showCheckmark(e)}
         onMouseLeave = {e => hideCheckmark(e, teamObj.id)}
         onClick = {(e) => mngCheckmarkClass(e, teamObj.id)}
    >
      <div style={checkmarkStyle} className={checkmarkClass}>&#10003;</div>
      <div dangerouslySetInnerHTML={{ __html: helpersBase.getThumbnailImage(teamObj)}} />
      <div className="indexTeamTitle" key={'j_' + teamObj.id}>{teamObj.title}</div>
      <div className="cb"></div>
    </div>

  );

}
