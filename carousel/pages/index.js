import React, { Component } from "react";
import fetch from 'isomorphic-unfetch'
import { withStyles } from "@material-ui/core/styles";
let helpersBase = require('../helpers/base');
let {siteConfigObj} = require('../helpers/siteconstants');
import dynamic from 'next/dynamic'

class Index extends React.Component {

  render() {

    let {divArr, teamsArr, footerLinksArr, deviceType, feedArr, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr} = this.props;

    let DynamicComponent = '';
    let className = '';
    //if (siteConfigObj.homepageIsCustomSelect) {
    DynamicComponent = dynamic(() => import('../pages/streams'))
    // } else {
    //   className = 'indexCont';
    //   DynamicComponent = dynamic(() => import('../components/home'))
    // }

    return (

      <div key='top' className={className}>

        <DynamicComponent
          divArr={divArr}
          footerLinksArr={footerLinksArr}
          feedArr = {feedArr}
          categoryName = {categoryName}
          pageType = {pageType}
          categoryNameArr = {categoryNameArr}
          categoryIndexArr = {categoryIndexArr}
          categoryImageArr = {categoryImageArr}
          deviceType = {deviceType}
          teamsArr = {teamsArr}
        />

      </div>

    )
  }
}

Index.getInitialProps = async function(context) {

  console.log("@@@@@@@@@@@@@@@@@@@@@@@@@");
  console.log("Index.getInitialProps");
  console.log("#########################");

  let pageType = '', divArr = [], feedArr = [], categoryName = '', categoryNameArr = [], categoryIndexArr = [], categoryImageArr = [];
  let deviceType = helpersBase.getDeviceType(context);
  let footerLinksArr = await helpersBase.getFooterLinksArr(false);

  if (siteConfigObj.homepageIsCustomSelect) {

    console.log("homepageIsCustomSelect", siteConfigObj.pathToJson + 'division_category.json');

    let r = await fetch(siteConfigObj.pathToJson + 'division_category.json');
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

    return {divArr, teamsArr, footerLinksArr, deviceType};

  } else if (siteConfigObj.homepageFullCategories || (deviceType == 'mobile' && siteConfigObj.mobileHomepageFullCategories)) {
    // Sites like 'nfl' have too many categories and content and will create a bad mobile experience, so
    // this will offer a custom select button and browse by division categories

    // get top level categories and one content card per item
    console.log("getting " + siteConfigObj.pathToJson + 'max_level_category.json');
    let r = await fetch(siteConfigObj.pathToJson + 'max_level_category.json');
    let teamArr = await r.json();
    for(let i in teamArr) {
      categoryNameArr.push(teamArr[i]['title']);
      categoryIndexArr.push(teamArr[i]['id']);
      categoryImageArr.push(teamArr[i]['image']);
    }

    feedArr = await helpersBase.getFeedArrWithCategoryIndexArr(categoryIndexArr);
    pageType = 'selectedTeamsPage';
    categoryName = '';

  } else {

    console.log("getting " + siteConfigObj.pathToJson + 'division_category.json');
    let url = siteConfigObj.pathToJson + 'division_category.json';
    let r = await fetch(url);
    divArr = await r.json();
    pageType = 'index.js';

  }

  return {divArr, footerLinksArr, feedArr, deviceType, categoryName, pageType, categoryNameArr, categoryIndexArr, categoryImageArr};

};

const styles = () => ({

});

export default withStyles(styles)(Index)
