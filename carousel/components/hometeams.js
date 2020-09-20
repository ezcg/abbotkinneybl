import React from 'react'
let helpersBase = require('../helpers/base');

function getTitle(title) {
  let maxLength = 17
  if (title.length > maxLength) {
    title = title.substr(0, maxLength) + "...";
  }
  return title;
}

export default function HomeTeams ({teamsArr}) {

  // let str = '';
  // teamsArr.map((teamObj, j) => {
  //   str+=teamObj.title + ', ';
  // });
  // str = str.substr(0, str.length -2);
  // return str;

  return teamsArr.map((teamObj, j) => {
    return <div key={'j_'+j} className="indexTeamRowCont">
      <div dangerouslySetInnerHTML={{ __html: helpersBase.getThumbnailImage(teamObj)}} />
      <div className="indexTeamTitle" key={'j_' + j}>{getTitle(teamObj.title)}</div>
      <div className="cb"></div>
    </div>
  })
}


