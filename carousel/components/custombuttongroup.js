import React, { useRef, useEffect,useLayoutEffect } from "react";
let {siteConfigObj} = require('../helpers/siteconstants');
let helpersBase = require('../helpers/base');

export default function CustomButtonGroup ({ next, previous, goToSlide, carouselState, pageType, deviceType, categoryIndex, feedArr, rowNum, depth, setCategoryShallowSlideNum, setCurrentDepth, getPreviousDepth, slideNum }) {

  // Have the first row slide cards to the right and then to the left to alert user to navigation
  const clickNextRef = React.useRef()
  const isMounted = React.useRef(false);
  const clickPreviousRef = React.useRef()
  useEffect(() => {
    let slideDemoDone = helpersBase.getCookie('slide_demo_done');
    if (rowNum === 1 && !isMounted.current && !slideDemoDone) {
      isMounted.current = true;
      setTimeout(()=>{clickNextRef.current.click()}, 700);
      setTimeout(()=>{clickPreviousRef.current.click()}, 1400);
      helpersBase.setCookie('slide_demo_done', 1, 1);
      //console.log("CustomButtonGroup demo slide should have happened");
    }
  });

  const { totalItems, currentSlide } = carouselState;

  //console.log("CustomButtonGroup categoryIndex", categoryIndex, "rowNum", rowNum, "depth", depth, "currentSlide", currentSlide, "slideNum", slideNum);

  let previousDepth = getPreviousDepth(categoryIndex);

  // keep track of slide number when in shallow mode so that we can return to it out of deep mode
  if (depth == 'shallow') {
    setCategoryShallowSlideNum(categoryIndex, currentSlide);
  }
  setCurrentDepth(categoryIndex, depth);
  useEffect(() => {
    // When coming out of 'deep' and returning to 'shallow', goto the slideNum passed
    if (depth == 'shallow' && previousDepth != 'shallow') {
      // "plus one" in order to jump to the next card as the card being returned to was already seen. mobile only
      // sees one card at a time
      if (deviceType == 'mobile') {
        slideNum = slideNum + 1;
      }
      //console.log("from deep and returning to shallow. categoryIndex", categoryIndex, "useEffect gotoSlide ", slideNum);
      setTimeout(()=>{goToSlide(slideNum)}, 700);
    }
    // If entering 'deep' mode, jump to the second slide in deep mode to make it apparent there are more slides as mobile
    // users only get 1 slide visible at a time.
    if (deviceType == 'mobile' && currentSlide == 0 && depth == 'deep' && previousDepth == 'shallow') {
      //console.log("entering deep mode first time");
      setTimeout(()=>{goToSlide(1)}, 700);
    }

  });

  // START Toggle which type of nav is visible here
  let navTopStyle = {display:'none'}
  let navMobileBottomStyle = {display:'none'}
  if (deviceType == 'mobile') {
    navMobileBottomStyle = {display:'block'}
  } else if (siteConfigObj.navBtnsOnTop == 1) {
    navTopStyle = {display:'block'}
  }
  // END Toggle

  // nav btns top START
  let navBtnReloadArrowTopStyle = {opacity:'1'}
  let navBtnReloadTopStyle = {cursor:'pointer'}
  let prevBtnTopStyle = {cursor:'pointer'}
  let prevBtnArrowTopStyle = {opacity:'1'}
  if (currentSlide == 0) {
    navBtnReloadArrowTopStyle = {opacity:'.2'}
    navBtnReloadTopStyle = {cursor:'default'}
    prevBtnTopStyle = {cursor:'default'}
    prevBtnArrowTopStyle = {opacity:'.2'}
  }
  // nav btns top END

  // Get name of next card content. Only for mobile
  let nextTitle = '';
  let nextBtnArrowBottomStyle = {display:'inline-block'};
  if (deviceType == 'mobile') {
    let index = 0;
    for(let obj of feedArr[categoryIndex]) {
      if (currentSlide == index - 1) {
        nextTitle = obj.title;
        if ((pageType == 'singleCategoryPage' || depth == 'deep') && !obj.isEnd) {
          nextTitle = 'More ' + nextTitle;
        } else if (!obj.isEnd) {
          nextTitle = 'Next: ' + nextTitle;
        }
      }
      // if the current slide is the Category End, hide the right arrow in the next button
      if (obj.isEnd && currentSlide == index) {
        nextBtnArrowBottomStyle = {display:'none'};
      }

      //console.log(feedArr[categoryIndex]);
      //console.log(nextTitle, obj.isEnd, depth);
      index++;

    }
  }


  return (

    /* NAV BUTTONS */
    <div>

      {/* START mobile nav next button below content card */}
      <div style={navMobileBottomStyle} className="custom-button-group-mobile-bottom">
        <button
          className='navBtnBottom navBtnNextBottom'
          onClick={() => next()}
        >&nbsp;{nextTitle} <span style={nextBtnArrowBottomStyle} className="nextBtnArrowBottom">&raquo;</span>&nbsp;</button>
      </div>
      {/* END mobile nav next button below content card */}

      {/* START top Nav buttons (above the row) */}
      <div style={navTopStyle} className="custom-button-group-top">
        <button
          style={navBtnReloadTopStyle}
          className="navBtnTop navBtnReloadTop"
          onClick={() => goToSlide(0)}
        >&nbsp;<span style={navBtnReloadArrowTopStyle} className="navBtnReloadArrowTop">&#x21bb;</span>&nbsp;
        </button>
        <button
          style={prevBtnTopStyle}
          className="navBtnTop navBtnPrevTop"
          onClick={() => previous()}
          ref={clickPreviousRef}
        >&nbsp;<span style={prevBtnArrowTopStyle} className="prevBtnArrowTop">&laquo;</span>&nbsp;</button>
        <button
          className='navBtnTop navBtnNextTop'
          onClick={() => next()}
          ref={clickNextRef}
        >&nbsp;<span className="nextBtnArrowTop">&raquo;</span>&nbsp;</button>
      </div>
      {/*  END top Nav buttons (above the row)*/}

    </div>
  );
};