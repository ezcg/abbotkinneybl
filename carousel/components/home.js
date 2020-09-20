import Link from "next/link";
import FooterLinks from "./footerlinks";
import SubHeader from "./subheader";
import React from "react";
let helpersBase = require('../helpers/base');
let {siteConfigObj} = require('../helpers/siteconstants');
import Spinner from '../components/spinner';
import HomeTeams from '../components/hometeams';

class Home extends React.Component {

  constructor(props) {
    super(props);
    let pathSegment = '';

    // This page is only for "division" level display of categories. eg. afc-east, afc-north, etc
    //if (siteConfigObj.individuals) {
      this.props.divArr.map((obj, i) => {
        pathSegment+=helpersBase.linkifyStr(obj.cats_title.trim()) + "~";
      });
    // } else {
    //   this.props.divArr.map((obj, i) => {
    //     for(let j in obj.teams) {
    //       pathSegment+=helpersBase.linkifyStr(obj.teams[j].title.trim()) + "~";
    //     }
    //   });
    // }
    pathSegment = pathSegment.substring(0, pathSegment.length - 1);
    this.state = {pathSegment:pathSegment, spinnerDisplayStyle:'none'}
  }

  changeBackground(e) {

    if (e.target.style.background == 'rgba(204, 204, 204, 0.5)') {
      e.target.style.background = '';
    } else {
      e.target.style.background = 'rgba(204, 204, 204, 0.5)';
    }
  }

  getTitle(title) {
    let maxLength = 17
    if (title.length > maxLength) {
      title = title.substr(0, maxLength) + "...";
    }
    return title;
  }

  browseAllClick() {
    this.setState({spinnerDisplayStyle:'block'});
  }

  render() {

    console.log("components/home.js");

    let btnStyleArr = [];
    // cant' browse by afc-east~afc-north~afc-south etc, but can browse by afc-east so no browse all if divisions displayed
    let browseAllDisplay = 'none';
    if (siteConfigObj.individuals) {
      browseAllDisplay = 'inline-block';
    }
    browseAllDisplay = {display:browseAllDisplay};

    return <div>

      <div className="indexSubheader">

      <SubHeader />

      <div className="subheaderNav">
        <Link href="/[id]" as={this.state.pathSegment}>
          <a
            onClick={() => this.browseAllClick()}
            style={browseAllDisplay}
            className="indexSubheaderBrowseAll"
            title="Browse all"
          >Browse all &raquo;</a>
        </Link>

        <Link href="/streams" as="streams">
          <a
            className="indexSubheaderSelectTeamsLink"
            title="streams"
          >Custom select &raquo;</a>
        </Link>
      </div>

      {/*Browse by categories below*/}
    </div>
    {this.props.divArr.map((obj, i) => {

      let catTitleEncoded = helpersBase.linkifyStr(obj.cats_title);

      let factor = obj.teams.length;
      // needs a pixel tweak for mobile
      let height = this.props.deviceType == 'mobile' ? 57 : 56;
      let btnHeight = height * factor + 12;
      btnStyleArr[i] = {height : btnHeight};

      return <div key={'i_' + i} className="indexDivisionCont">
        <div className='indexDivisionRow'>
          {helpersBase.getThumbnailImage(obj, 'home')}
          <Link href="/[id]" as={catTitleEncoded}>
            <a
              title={obj.cats_title}
            >
              <div className="indexDivisionTitle">{obj.cats_title}&raquo;</div>
            </a>
          </Link>
        </div>

        <div className="indexTeamCont">
          <HomeTeams teamsArr={obj.teams} />

          <Link href="/[id]" as={catTitleEncoded}>
            <a
              onMouseEnter={this.changeBackground}
              onMouseOut={this.changeBackground}
              onClick={() => this.browseAllClick()}
              className="gotoDivisionsBtnCont"
              title={obj.cats_title}
            >
              <div className="gotoDivisionsBtn" style={btnStyleArr[i]}>
                &raquo;
              </div>
            </a>
          </Link>
        </div>
      </div>

    })}

    <div className="cb"></div>
    <FooterLinks
      footerLinksArr = {this.props.footerLinksArr}
    />

    <Spinner displayStyle={this.state.spinnerDisplayStyle} topStyle={"10%"} leftStyle={"47%"} />

  </div>

  }

}

export default Home;