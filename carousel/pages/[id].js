import React, { Component, useRef, useEffect } from "react";
import fetch from 'isomorphic-unfetch'
import { withStyles } from "@material-ui/core/styles";
import Carousel from "react-multi-carousel";
import Item from '../components/item';
import CustomButtonGroup from '../components/custombuttongroup';
import FooterLinks from '../components/footerlinks';
let helpersBase = require('../helpers/base');
let {siteConfigObj} = require('../helpers/siteconstants');
import SubHeader from "../components/subheader";

class Category extends Component {

  constructor(props) {
    super(props);
    this.state = {
      categoryKeyArr:[],
    }
    this.previousFeedArr = [];
    this.toggleDepth = this.toggleDepth.bind(this);
    this.setCategoryShallowSlideNum = this.setCategoryShallowSlideNum.bind(this);
    this.setCurrentDepth = this.setCurrentDepth.bind(this);
    this.getPreviousDepth = this.getPreviousDepth.bind(this);
    this.categorySlideNumArr = [];
    this.categoryDepthArr = [];
  }

  setCurrentDepth(categoryIndex, depth) {
    this.categoryDepthArr[categoryIndex] = depth;
  }

  getPreviousDepth(categoryIndex) {
    if (typeof this.categoryDepthArr[categoryIndex] == 'undefined') {
      this.categoryDepthArr[categoryIndex] = 'shallow';
    }
    return this.categoryDepthArr[categoryIndex];
  }

  setCategoryShallowSlideNum(categoryIndex, slideNum) {
    this.categorySlideNumArr[categoryIndex] = slideNum;
  }

  getCategoryShallowSlideNum(categoryIndex) {
    if (typeof this.categorySlideNumArr[categoryIndex] == 'undefined') {
      this.categorySlideNumArr[categoryIndex] = 0;
    }
    return this.categorySlideNumArr[categoryIndex];
  }

  async toggleDepth(categoryIndex, itemsId, depth) {
    let categoryKeyArr = [];
    if (depth === 'shallow') {
      this.previousFeedArr[categoryIndex] = this.props.feedArr[categoryIndex];
      let tmpFeedArr = await helpersBase.getFeedArrWithItemsArr([itemsId], "deep");
      this.props.feedArr[categoryIndex] = tmpFeedArr[itemsId];
      categoryKeyArr = this.state.categoryKeyArr;
      categoryKeyArr[categoryIndex] = 'deep';
    } else {
      this.props.feedArr[categoryIndex] = this.previousFeedArr[categoryIndex];
      categoryKeyArr = this.state.categoryKeyArr;
      categoryKeyArr[categoryIndex] = 'shallow';
    }
    this.setState({
      categoryKeyArr: categoryKeyArr
    });
  }

  render() {

    let { footerLinksArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr, classes } = this.props;
    const slidesToSlide = helpersBase.getSlidesToSlide(deviceType);
    const responsive = helpersBase.getResponsiveObj();
    let partialVisible = (deviceType == 'mobile' || siteConfigObj.partialVisible == 0) ? 0 : 1;
    let centerMode = (deviceType == 'mobile' || siteConfigObj.centerMode == 0) ? 0 : 1;
    let rowNum = 0;
    console.log("[id].js render pageType", pageType);

    return (

      <div style={{position: 'relative', paddingTop:'8px'}}>
        <SubHeader />
        {helpersBase.displayPageCategoryTitleHeader(categoryName, pageType)}

        {categoryNameArr.map((categoryName, index) => {

          let categoryIndex = categoryIndexArr[index];
          let categoryImage = categoryImageArr[index];
          let depth = typeof this.state.categoryKeyArr[categoryIndex] != 'undefined'
            ? this.state.categoryKeyArr[categoryIndex]
            : 'shallow';
          let categoryKey = depth + "_" + categoryIndex;
          rowNum = rowNum + 1;
          let slideNum = this.getCategoryShallowSlideNum(categoryIndex);
          let cardNum = 0;

          return <div key={'index_' + categoryKey} className={`rowCont ${classes.root}`}>
            <Carousel
              arrows={false}
              centerMode={centerMode}
              renderButtonGroupOutside={true}
              renderDotsOutside={false}
              showDots={false}
              sliderClass={false}
              customButtonGroup={<CustomButtonGroup
                pageType={pageType}
                deviceType={deviceType}
                categoryIndex={categoryIndex}
                feedArr={this.props.feedArr}
                rowNum={rowNum}
                depth={depth}
                setCategoryShallowSlideNum={this.setCategoryShallowSlideNum}
                setCurrentDepth={this.setCurrentDepth}
                getPreviousDepth={this.getPreviousDepth}
                slideNum={slideNum}
              />}
              responsive={responsive}
              ssr={true}
              draggable={false}
              swipeable={true}
              slidesToSlide={slidesToSlide}
              infinite={false}
              partialVisible={partialVisible}
              containerClass='container-padding'
              deviceType={deviceType}
              removeArrowOnDeviceType={['tablet', 'mobile']}
              minimumTouchDrag={20}
            >

            {this.props.feedArr[categoryIndex].map(content => {
              return <Item key={content.depth + "_" + content.items_id + "_" + "_" + rowNum + (++cardNum)}
                pageType={pageType}
                key={content.items_id}
                platformBtnLink={content.social_media.link}
                socialMediaObj={content.social_media}
                itemsId={content.items_id}
                itemObj={content}
                deviceType={this.props.deviceType}
                categoryIndex={categoryIndex}
                depth={content.depth}
                toggleDepth={this.toggleDepth}
              />
            })}

            </Carousel>

            {helpersBase.getCategoryTabForRow(categoryName, pageType, categoryImage)}

          </div>
        })}

        <div style={{margin:"20px auto",width:"250px",textAlign:"center"}}>
          Slider built using
          <br />
          <a
            href='https://github.com/YIZHUANG/react-multi-carousel'
            target='_blank'
          >Yizhuang react-multi-carousel</a>
        </div>

        <FooterLinks
          footerLinksArr = {footerLinksArr}
        />

      </div>

    )
  }
}

Category.getInitialProps = async function(context) {

  console.log("@@@@@@@@@@@@@@@@@@@@@@@@@");
  console.log("Category.getInitialProps");
  console.log("#########################");

  let feedArr = [], categoryNameArr = [], categoryIndexArr = [], categoryImageArr = [];
  let deviceType = helpersBase.getDeviceType(context);
  let categoryName = '';
  let catObj = {};
  let categoryId = 0;
  let pageType = '';
  let footerLinksArr = await helpersBase.getFooterLinksArr(1);

  if (context.query.id.indexOf("~~") !== -1) {
    // customized selection of individuals to view seperated by ~~ eg. colbert-late-show~~nbc-nightly

    pageType = 'selectedIndividualsPage';
    console.log("pageType", pageType);

    let itemsArr = [];
    let submittedItemsArr = context.query.id.split("~~");
    let r = await fetch(siteConfigObj.pathToJson + 'items_lookup.json');
    let itemsLookupArr = await r.json();
    for(let j in submittedItemsArr) {
      for(let itemsId in itemsLookupArr) {
          if (helpersBase.getPageIdFromStr(itemsLookupArr[itemsId]['title']) == submittedItemsArr[j]) {
          itemsArr.push(itemsId);
        }
      }
    }
    if (itemsArr.length == 0) {
      console.log("did not find any items matching submitted query.id " + context.query.id);
    }

    feedArr = await helpersBase.getFeedArrWithItemsArr(itemsArr);
    for(let itemsId in feedArr) {
      categoryNameArr.push(feedArr[itemsId][0]['title']);
      categoryIndexArr.push(itemsId);
      categoryImageArr.push(feedArr[itemsId][0]['avatar']);
    }

    //return {footerLinksArr, feedArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr};

  } else if (context.query.id.indexOf("~") !== -1) {
    // customized selection of teams to view seperated by ~ eg. ny-giants~ny-jets

    pageType = 'selectedTeamsPage';
    console.log("pageType", pageType);

    let submittedTeamArr = context.query.id.split("~");
    // get integer ids from team names submitted
    let r = await fetch(siteConfigObj.pathToJson + 'max_level_category.json');
    let teamArr = await r.json();

    for(let i in teamArr) {
      for(let j in submittedTeamArr) {
        if (!submittedTeamArr[j])continue;
        if (helpersBase.getPageIdFromStr(teamArr[i].title) == submittedTeamArr[j]) {
          categoryNameArr.push(teamArr[i]['title']);
          categoryIndexArr.push(teamArr[i]['id']);
          categoryImageArr.push(teamArr[i]['image']);
        }
      }
    }

    feedArr = await helpersBase.getFeedArrWithCategoryIndexArr(categoryIndexArr);

    //return {footerLinksArr, feedArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr};

  } else if (false !== (catObj = await helpersBase.getSingleCategory(context))) {

    // Display a row for each player with 20 content cards by that player alone

    pageType = 'singleCategoryPage';
    console.log("pageType", pageType);
    categoryName = catObj.categoryName;
    categoryId = catObj.categoryId;
    let awsUrlToJson = siteConfigObj.pathToJson + 'category_' + categoryId + '_max.json';
    let r = await fetch(awsUrlToJson);
    let responseJson = await r.json();
    let feedArr = [];
    for(let itemsId in responseJson) {
      feedArr[itemsId] = [];
      for (let key in responseJson[itemsId].social_media) {
        let tmpObj = helpersBase.getFeedObj(itemsId, key, responseJson);
        feedArr[itemsId].push(tmpObj);
      }
      let emptyObj = helpersBase.getEmptyFeedObj();
      feedArr[itemsId].push(emptyObj);
    }

    // order by rank
    let rankArr = [];
    let rankObj = {};
    for(let itemsId in feedArr) {
      rankObj = {'itemsId' : itemsId, 'rank': feedArr[itemsId][0]['rank']};
      rankArr.push(rankObj);
    }
    rankArr.sort(function(a, b){return a.rank - b.rank});
    // categoryNameArr is what holds the order of displayed rows, so set categoryNameArr by rank
    for(let i in rankArr) {
      let itemsId = rankArr[i]['itemsId'];
      categoryNameArr.push(feedArr[itemsId][0]['title']);
      categoryIndexArr.push(feedArr[itemsId][0]['items_id']);
      categoryImageArr.push(feedArr[itemsId][0]['avatar']);
    }

    // If this is not returned here and instead returned at the end of this method, an error due to feedArr not being
    // set occurs
    return {footerLinksArr, feedArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr};

  } else if (false !== (catObj = await helpersBase.getDivisionCategory(context))) {

    // Display a row for each team in the selected division with one content card from each main account that belongs
    // to the team in the team row

    pageType = 'divisionCategoryPage';
    console.log("pageType", pageType);

    let divisionCategoryName = catObj.categoryName;
    let divisionCategoryTeamArr = catObj.divisionCategoryTeamArr;

    for(let i in divisionCategoryTeamArr) {
      categoryNameArr.push(divisionCategoryTeamArr[i]['title']);
      categoryIndexArr.push(divisionCategoryTeamArr[i]['id']);
      categoryImageArr.push(divisionCategoryTeamArr[i]['image']);
    }

    feedArr = await helpersBase.getFeedArrWithCategoryIndexArr(categoryIndexArr);
    categoryName = divisionCategoryName;

    //return {footerLinksArr, feedArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr};

  }

  return {footerLinksArr, feedArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr};


};

const styles = () => ({
  root: {
    textAlign: "center"
  },
  title: {
    maxWidth: 300,
    margin: "auto",
    marginTop: 10
  }

});

export default withStyles(styles)(Category)
