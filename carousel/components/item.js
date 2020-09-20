import React, { Component } from 'react';
import Info from '../components/info';
let helpersBase = require('../helpers/base');

console.log(React.version);

class Item extends React.Component {
  constructor(props) {
    super(props);
    this.state = {textOverflows:0, textOverflowShown:0, displayInfo:0, itemTextContStyle: {}, overflowBtnClass:'overflowBtnDownArrow'};
    this.toggleDisplayInfo = this.toggleDisplayInfo.bind(this);
    this.overflowDetectRef = React.createRef();
  }

  componentDidMount() {
    if (this.isOverflowing()) {
      this.setState({textOverflows:1});
    }
    //const styles = getComputedStyle(this.overflowDetectRef.current);
    //console.log("styles", styles.height);

  }

  isOverflowing() {

    // bug where scrollHeight will return incorrect height
    const overflows = this.overflowDetectRef.current.offsetHeight < this.overflowDetectRef.current.scrollHeight;
    if (this.overflowDetectRef.current.offsetHeight < this.overflowDetectRef.current.scrollHeight) {
      console.log("overflows", this.props.socialMediaObj.username,
        "offsetHeight:", this.overflowDetectRef.current.offsetHeight ,
        "scrollHeight", this.overflowDetectRef.current.scrollHeight);
    }
      //this.overflowDetectRef.current.offsetWidth < this.overflowDetectRef.current.scrollWidth;
    return overflows;
  }

  mngOverflow(e) {
    if (this.state.textOverflowShown == 0) {
      let style = {overflowY:'auto'};
      this.setState({itemTextContStyle:style, overflowBtnClass:'overflowBtnUpArrow', textOverflowShown:1});
      this.overflowDetectRef.current.scrollTo({top:this.overflowDetectRef.current.offsetHeight});
    } else {
      let style = {overflowY:'hidden'};
      this.setState({itemTextContStyle:style, overflowBtnClass:'overflowBtnDownArrow', textOverflowShown:0});
      this.overflowDetectRef.current.scrollTo({top:0});
    }
  }

  // display address, hours, etc or the social media
  toggleDisplayInfo(displayInfo) {
    if (displayInfo == 0) {
      this.setState({
        displayInfo: 1
      });
    } else {
      this.setState({
        displayInfo: 0
      });
    }
  }



  render() {

    let overflowBtnStyle = {display:'none'};
    let overflowBtnClass = this.state.overflowBtnClass;
    if (this.state.textOverflows && this.state.displayInfo == 0) {
      overflowBtnStyle = {display:'block'};
    }

    let displayInfo = 0;
    if (this.state.displayInfo == 1) {
      displayInfo = 1;
    }

    let socialMediaObj = this.props.socialMediaObj;
    let createdAt = this.props.socialMediaObj.created_at;
    let itemObj = this.props.itemObj;
    let text = '';
    let avatar = '';
    // Links to main home page of all or just one social media account is contained in info, so all will have info
    // except for hashtags
    let hasInfo = 1;
    if (itemObj.isEnd || itemObj.title.charAt(0) == "#") {
      hasInfo = 0;
    }
    // if (
    //   itemObj.address ||
    //   itemObj.hours ||
    //   (itemObj.lat && itemObj.lon) ||
    //   itemObj.phone ||
    //   itemObj.website ||
    //   itemObj.history
    // ) {
    //   hasInfo = 1;
    // }

    text = socialMediaObj.text.replace('http:', '');
    if (itemObj.avatar) {
      avatar = '<img class="avatarImg" src="' + itemObj.avatar.replace('http:', '') + '"/>';
    }

    let platformIconTag = helpersBase.getPlatformIconSrcWithSite(socialMediaObj.site);

    let itemTextContStyle = this.state.itemTextContStyle;
    let itemBodyStyle = {};
    let platformBtnStyle = { display: 'block' };
    let infoBtnStyle = { display: 'none' };
    if (hasInfo) {
      infoBtnStyle = { display: 'block' };
      if (displayInfo == 1) {
        itemTextContStyle = {display: 'none'};
        platformBtnStyle = { display: 'none' };
        infoBtnStyle.backgroundColor = '#b9c1c6';
      }
    }

    // Don't display item header when displaying all of items cards (ie. not different items all in one row, it's
    // all one item in the row, so the header/category gets displayed once above the row in the page, not here)
    let itemsHeaderStyle = {};
    let goDeepStyle = {};
    if (this.props.pageType == 'singleCategoryPage') {
      itemsHeaderStyle = {display:'none'}
      goDeepStyle = {display:'none'}
    }
    // no going deep into 'Category End', but we want the up (return to shallow arrow) button available
    if (itemObj.isEnd && itemObj.depth == 'shallow') {
      goDeepStyle = {display:'none'}
    }
    // Only make username clickable if in multicategory mode. In single category mode, the username is in the category tab
    let itemHeaderUsernameStyle = {};
    if (this.props.pageType !== 'singleCategoryPage') {
      itemHeaderUsernameStyle = {cursor:"pointer"}
    }

    let isMobile = false;
    if (this.props.deviceType === 'mobile') {
      isMobile = true;
    }

    // Extract offsite link from reddit text and position them at the bottom
    // let offsiteLink = '';
    // let searchValue = '';
    // let match = '';
    // if (text.indexOf('www.reddit.com') !== -1 || text.indexOf('redd.it') !== -1) {
    //   searchValue = /<a class='offsiteLink'.*<\/a>/;
    //   match = text.match(searchValue);
    //   if (match && match[0]) {
    //     offsiteLink = match[0];
    //     text = text.replace(searchValue, '');
    //   }
    // }

    // make reddit links open in app - requires check if device is mobile, so doing it here
    if (text.indexOf('www.reddit.com') !== -1 && isMobile) {
      let searchValue = /www.reddit.com/;
      text = text.replace(searchValue, 'amp.reddit.com');
    }
    if (text.indexOf('redd.it') !== -1 && isMobile) {
      let searchValue = /redd.it/;
      text = text.replace(searchValue, 'amp.reddit.com');
    }

    if (0 && createdAt) {
      text+= " " + createdAt;
    }
    let depth = 'shallow';
    let depthBtnClassName='depthBtnDown';
    if (itemObj.depth == 'deep') {
      depthBtnClassName='depthBtnUp';
      depth = 'deep';
    }

    return (
      <div className="itemsCont">

        <div className="itemsHeader" style={itemsHeaderStyle}>
          <div onClick={() => this.toggleDisplayInfo(displayInfo)}
            className="itemAvatarCont"
            dangerouslySetInnerHTML={{ __html: avatar }}
          />
          <div onClick={() => this.toggleDisplayInfo(displayInfo)} style={itemHeaderUsernameStyle} className="itemHeaderUsername">
            {itemObj.title}
          </div>

          <div
            style={goDeepStyle}
            className="depthBtnCont"
            onClick={() => this.props.toggleDepth(this.props.categoryIndex, itemObj.items_id, depth )}
          >
            <div className={depthBtnClassName}></div>
            <div className="cb"></div>
          </div>

        </div>

        <div className="clearBoth" />
        <div style={itemBodyStyle} className="itemBody">
          <div
            ref={this.overflowDetectRef}
            style={itemTextContStyle}
            className="itemTextCont"
            dangerouslySetInnerHTML={{ __html: text }}
          />

          <div
            className="bottomBtnCont"
            style={this.state.bottomBtnContDisplay}
          >

            <div className="platformBtnCont" style={platformBtnStyle}>
              <a href={this.props.platformBtnLink} target="_blank">
                <div
                  className="platformIcon"
                  dangerouslySetInnerHTML={{ __html: platformIconTag }}
                />
              </a>
            </div>

            {/* component to display info; hours etc*/}
            <Info itemObj={itemObj} displayInfo={displayInfo} />

            {/* button to display info or not*/}
            <div
              onClick={() => this.toggleDisplayInfo(displayInfo)}
              className="infoBtnCont"
            >
              <button style={infoBtnStyle} className="infoBtn">
                info
              </button>
            </div>

            <div className='overflowBtnCont' onClick={(e) => this.mngOverflow(e)} style={overflowBtnStyle}>
              <div className={overflowBtnClass}>&raquo;</div>
            </div>

            {/*<span dangerouslySetInnerHTML={{ __html: offsiteLink }} />*/}


          </div>
        </div>
      </div>

    );
  }
}

export default Item;
