import React from 'react'
import SelectRow
  from "./selectrow";

export default function Category ({title, teamsArr, displayedCategoryTabArr}) {
  return <div>{teamsArr.map((teamObj, i) => {
      return <div key={'i_'+ i} className="teamsSelectRow">
        <SelectRow
          teamObj={teamObj}
          setSelectedId={this.setSelectedId}
          unsetSelectedId={this.unsetSelectedId}
          getSelectedIdArr={this.getSelectedIdArr}
        />
        <div className="cb"></div>
      </div>

    })}
    </div>
}


