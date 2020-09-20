import React, { Component } from "react";
import fetch from 'isomorphic-unfetch'
import { withStyles } from "@material-ui/core/styles";
import FooterLinks from '../components/footerlinks';
import Link from 'next/link';
let helpersBase = require('../helpers/base');
let {siteConfigObj} = require('../helpers/siteconstants');
import SelectRow from '../components/selectrow.js'
import Spinner from '../components/spinner';
import SubHeader from "../components/subheader";

class Streams extends React.Component {

  constructor(props) {
    super(props);
    this.selectedIdArr = [];
    this.displayedCategoryTabArr = [];
    this.setSelectedId = this.setSelectedId.bind(this);
    this.unsetSelectedId = this.unsetSelectedId.bind(this);
    this.getSelectedIdArr = this.getSelectedIdArr.bind(this);
    this.state = {
      categoryRowsStyleDisplayArr: [],
      pathSegment: "",
      browseCategoryPathSegment: "",
      spinnerDisplayStyle:'none',
      browseAllSpinnerDisplayStyle:'none',
      submitPromptDisplayStyle:'none',
      submitFunctionalButtonDisplayStyle: 'none',
      submitDisabledButtonDisplayStyle: 'block',
      browseSelectedCatsFailPromptDisplayStyle: 'none',
      selectedAllCategoryArr: [],
      selectedIdArr: []
    }

  }

  browseAllClick() {
    this.setState({browseAllSpinnerDisplayStyle:'block'});
  }
  submitClick(e) {
    this.setState({
      spinnerDisplayStyle:'block',
      submitDisabledButtonDisplayStyle: 'none'
    });
  }
  submitClickFail(e) {
    e.preventDefault();
    this.setState({
      submitPromptDisplayStyle: 'block'
    });
  }
  submitCategoryClick(e) {
    this.setState({
      browseAllSpinnerDisplayStyle:'block'
    });
  }

  getSelectedIdArr() {
    return this.selectedIdArr;
  }
  setSelectedId(id) {
    if (!this.selectedIdArr.includes(id)) {
      this.selectedIdArr.push(id);
      this.setPathSegment();
    }
  }
  unsetSelectedId(id) {
    this.selectedIdArr = this.selectedIdArr.filter(selectedId => selectedId !== id);
    this.setPathSegment();
  }

  // Build the path part of the url.
  // 'teams' delimited with ~
  // 'individuals' delimited with ~~
  setPathSegment() {
    let delimiter = siteConfigObj.teams ? "~" : "~~";
    // the trailing double ~~ is needed in order to trigger the item lookup in [id].js
    let trimNumChars = siteConfigObj.teams ? 1 : 0;
    let pathSegment = '';
    let selectedIdArr = this.getSelectedIdArr();
    if (selectedIdArr.length > 0) {
      this.props.teamsArr.forEach(function(obj) {
        selectedIdArr.forEach(id => {
          if (id == obj.id) {
            pathSegment+= helpersBase.linkifyStr(obj.title) + delimiter;
          }
        });
      })
    }
    pathSegment = pathSegment.substring(0, pathSegment.length - trimNumChars);
    if (pathSegment) {
      this.setState({
        submitFunctionalButtonDisplayStyle: 'block',
        submitDisabledButtonDisplayStyle: 'none',
        pathSegment:pathSegment
      });
    }

  }

  // With category link
  displayCategoryTab(title, i) {
    if (!this.displayedCategoryTabArr[i]) {
      let catTitleEncoded = helpersBase.linkifyStr(title);
      this.displayedCategoryTabArr[i] = 1;
      return <div key={'c_' + i} style={{width:'100%',marginBottom:'2px'}}>
        <div className="cb"></div>
        <br />
        <Link href="/[id]" as={catTitleEncoded}><a title={title}>
          <div className='categoryTab'>{title}<span class="htmlArrow">&raquo;</span></div>
        </a>
        </Link>
        <div className="cb"></div>
      </div>
    }
  }

  mngCategoryRowsDisplay(index, action) {
    let categoryRowsStyleDisplayArr = this.state.categoryRowsStyleDisplayArr;
    if (action == 'show') {
      categoryRowsStyleDisplayArr[index] = 1;
    } else {
      delete categoryRowsStyleDisplayArr[index];
    }
    this.setState({categoryRowsStyleDisplayArr:categoryRowsStyleDisplayArr});
    return true;
  }

  displayStreamsCategoryTab(title, i) {

    if (!this.displayedCategoryTabArr[i]) {
      this.displayedCategoryTabArr[i] = 1;

      let toggleAction = 'show';
      if (typeof this.state.categoryRowsStyleDisplayArr[i] != 'undefined') {
        toggleAction = 'hide';
      }

      return <div key={'c_' + i} style={{width:'100%',marginBottom:'2px'}}>
        <div className="cb"></div>

        <div
          onClick={() => this.mngCategoryRowsDisplay(i, toggleAction)}
          className='categoryTab'
        >{title}</div>
        <div className="cb"></div>
      </div>
    }
    return true;
  }

  getRow(teamsArr, catsTitle, j, streams) {

    return teamsArr.map((teamObj, i) => {

      // Toggle for displaying/hiding all the items beneath a category
      let displayStyle = {display:'none'}
      if (typeof this.state.categoryRowsStyleDisplayArr[j] != 'undefined') {
        displayStyle = {display:'block'}
      } else if (siteConfigObj.streamsDefaultItemsDisplay == 1) {
        displayStyle = {display:'block'}
      }

      return <div key={'c_' + i} >
        {streams ? this.displayStreamsCategoryTab(catsTitle,j) : this.displayCategoryTab(catsTitle,j)}
        <div
          key = {'i_'+ i}
          className="teamsSelectRow"
          style={displayStyle}
        >
          <SelectRow
            teamObj = {teamObj}
            setSelectedId = {this.setSelectedId}
            unsetSelectedId = {this.unsetSelectedId}
            getSelectedIdArr = {this.getSelectedIdArr}
          />
          <div className="cb"></div>
        </div>
      </div>

    });
  }

  setCatCkBox(e) {
    let linkifiedCatsTitle = e.currentTarget.value;
    let browseCategoryPathSegment = this.state.browseCategoryPathSegment;
    if (e.currentTarget.checked) {
      browseCategoryPathSegment+=linkifiedCatsTitle;
    } else {
      browseCategoryPathSegment = browseCategoryPathSegment.replace(linkifiedCatsTitle, '');
    }
    this.setState({browseCategoryPathSegment: browseCategoryPathSegment});
  }

  changeBg(e) {
    e.target.style.background = "#05507a";
  }
  resetBg(e) {
    /// don't reset if it's been clicked/checked
    if (e.target.className != 'catsCkBoxChecked') {
      e.target.style.background = "#337ab7";
    }
  }

  displayBrowseSelectedCatsFailPrompt(e) {
    e.preventDefault();
    this.setState({browseSelectedCatsFailPromptDisplayStyle:'block'});
  }

  render() {

    const { footerLinksArr, divArr, deviceType } = this.props;

    this.displayedCategoryTabArr = [];
    let browseAllDisplay = 'none';
    if (siteConfigObj.individuals) {
      browseAllDisplay = 'block';
    }
    browseAllDisplay = {display:browseAllDisplay};

    let browseAllPathSegment = '';
    divArr.map((obj, i) => {
      browseAllPathSegment+=helpersBase.linkifyStr(obj.cats_title.trim()) + "~";
    });

    let spinnerLeftStyle='36%';
    let spinnerTopStyle="-40px";

    return (

      <div key='top' className="teamsCont">

        <div className="indexSubheader">
          <SubHeader />
        </div>

        <div className="browseAllCont">
          <Link href="/[id]" as={browseAllPathSegment}>
            <a
              onClick={() => this.browseAllClick()}
              style={browseAllDisplay}
              className="indexSubheaderBrowseAll"
              title="All at once..."
            >Browse all <div className="htmlArrowBrowseAll">&raquo;</div></a>
          </Link>
        </div>
        <div className='cb'></div>
        <div className="selectCategoriesGuideText">
          Or select categories and hit Submit:
          <div className="cb"></div>
        </div>

        <div style={{position:"relative", width:"100%"}}>
          <Spinner displayStyle={this.state.browseAllSpinnerDisplayStyle} topStyle={spinnerTopStyle} leftStyle={spinnerLeftStyle} />
        </div>

        <div>

          <div className="catsSelectCont">
            {divArr.map((divObj, j) => {
              let linkifiedCat = helpersBase.linkifyStr(divObj['cats_title']) + "~";
              return <div  key={j} className="catsCkBoxCont"><label
                onMouseEnter = {e => this.changeBg(e)}
                onMouseLeave = {e => this.resetBg(e)}
                htmlFor={linkifiedCat}
                className={this.state.browseCategoryPathSegment.includes(linkifiedCat) ? "catsCkBoxChecked" : "catsCkBox"}
              ><input
                style={{display:'none'}}
                id={linkifiedCat}
                onChange={(e) => this.setCatCkBox(e)}
                type='checkbox'
                name='browseCat'
                value={linkifiedCat}
                checked={this.state.browseCategoryPathSegment.includes(linkifiedCat) ? "checked" : ""}
              />{divObj['cats_title']}</label>
              </div>
            })}

            <div className="cb"></div>

          </div>

          <div className="cb"></div>

          <Link href="/[id]" as={this.state.browseCategoryPathSegment
            ? this.state.browseCategoryPathSegment.substring(0, this.state.browseCategoryPathSegment.length - 1)
            : ''
          }>
            <a
              onClick = {
                (e) => this.state.browseCategoryPathSegment
                  ? this.submitCategoryClick(e)
                  : this.displayBrowseSelectedCatsFailPrompt(e)
              }
              href={this.state.browseCategoryPathSegment}
              title="Select categories and click Submit"
            >
              <div className="catsSubmitBtn">

                <span style={{color:"#FFFFFF"}}>Submit</span><div className='htmlArrowSubmitBtn'>&raquo;</div>

              </div>
            </a>
          </Link>
          <div
            className="browseSelectedCatsFailPrompt"
            style={{display:this.state.browseSelectedCatsFailPromptDisplayStyle}}
          >No categories selected. Click the category buttons above first.</div>
          <div className="cb"></div>
          <br />

        </div>

        <div className="cb"></div>
        <FooterLinks
          footerLinksArr = {footerLinksArr}
        />

      </div>

    )
  }
}

Streams.getInitialProps = async function(context) {

  let jsonUrl = siteConfigObj.pathToJson + 'division_category.json';
  console.log("@@@@@@@@@@@@@@@@@@@@@@@@@");
  console.log("Streams.getInitialProps", jsonUrl);
  console.log("@@@@@@@@@@@@@@@@@@@@@@@@@");
  let deviceType = helpersBase.getDeviceType(context);

  let r = await fetch(jsonUrl);
  let divArr = await r.json();

  let teamsArr = [];
  for(let i in divArr) {
    for(let j in divArr[i]['teams']) {
      teamsArr.push(divArr[i]['teams'][j]);
    }
    divArr[i]['teams'].sort(function(a, b){
      if(a.title < b.title) { return -1; }
      if(a.title > b.title) { return 1; }
      return 0;
    });

  }

  let footerLinksArr = await helpersBase.getFooterLinksArr(1);

  return {footerLinksArr, divArr, teamsArr, deviceType};

};

const styles = () => ({

});

export default withStyles(styles)(Streams)
