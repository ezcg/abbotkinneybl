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
    }
  });

  const { totalItems, currentSlide } = carouselState;

  console.log("CustomButtonGroup categoryIndex", categoryIndex, "rowNum", rowNum, "depth", depth, "currentSlide", currentSlide, "slideNum", slideNum);

  let previousDepth = getPreviousDepth(categoryIndex);

  // keep track of slide number when in shallow mode so that we can return to it out of deep mode
  if (depth == 'shallow') {
    setCategoryShallowSlideNum(categoryIndex, currentSlide);
  }
  setCurrentDepth(categoryIndex, depth);
  useEffect(() => {
    // When coming out of 'deep' and returning to 'shallow', goto the slideNum passed in
    if (slideNum > 0 && depth == 'shallow' && previousDepth != 'shallow') {
      console.log("from deep and returning to shallow. categoryIndex", categoryIndex, "useEffect gotoSlide ", slideNum);
      goToSlide(slideNum);
    }
    // If entering 'deep' mode, jump to the second slide in deep mode
    if (currentSlide == 0 && depth == 'deep' && previousDepth == 'shallow') {
      console.log("entering deep mode first time");
      setTimeout(()=>{goToSlide(1)}, 700);
    }

  });

  // START Toggle which type of nav is visible here
  let navTopStyle = {display:'none'}
  let navInlineStyle = {display:'none'}
  let navMobileBottomStyle = {display:'none'}
  if (deviceType == 'mobile') {
    navMobileBottomStyle = {display:'block'}
  } else if (siteConfigObj.navBtnsOnTop == 0) {
    navInlineStyle = {display:'block'}
  } else if (siteConfigObj.navBtnsOnTop == 1) {
    navTopStyle = {display:'block'}
  }
  // END Toggle

  // nav btns inline START
  let prevBtnArrowInlineStyle = {opacity:'1'}
  let prevBtnInlineStyle = {cursor:'pointer'}
  if (currentSlide == 0) {
    prevBtnArrowInlineStyle = {opacity:'.2'}
    prevBtnInlineStyle = {cursor:'default'}

  }
  let reloadInlineStyle = {display:'block'}
  if (currentSlide == 0 || deviceType == 'mobile' || siteConfigObj.navBtnsOnTop) {
    reloadInlineStyle = {display:'none'}
  }

  // there's no itemsHeader when viewing single category page with a row for each player on a team
  let navBtnReloadInlineStyle = {}
  if (pageType == 'singleCategoryPage') {
    navInlineStyle.top = "57px";
    reloadInlineStyle.top = "57px";
    navBtnReloadInlineStyle.top = "40px";
  }
  if (currentSlide == 0) {
    if (pageType == 'singleCategoryPage') {
      navInlineStyle.borderTopRightRadius = "10px";
    }
    navInlineStyle.borderBottomRightRadius = "10px";
  }
  // nav btns inline END

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
  if (deviceType == 'mobile') {
    let index = 0;
    for(let obj of feedArr[categoryIndex]) {
      if (currentSlide == index - 1) {
        nextTitle = obj.title;
      }
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
        >&nbsp;{nextTitle} <span className="nextBtnArrowBottom">&raquo;</span>&nbsp;</button>
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
        >&nbsp;<span style={prevBtnArrowTopStyle} className="prevBtnArrowTop">&laquo;</span>&nbsp;</button>
        <button
          className='navBtnTop navBtnNextTop'
          onClick={() => next()}
        >&nbsp;<span className="nextBtnArrowTop">&raquo;</span>&nbsp;</button>
      </div>
      {/*  END top Nav buttons (above the row)*/}

      {/* START inline Nav buttons inside the row and a z-index above the content cards*/}
      <div style={navInlineStyle} className="custom-button-group-inline">

        <button
          className='navBtnInline navBtnNextInline'
          onClick={() => next()}
          ref={clickNextRef}
        >&nbsp;<span className="nextBtnArrowInline">&raquo;</span>&nbsp;</button>

        <button
          style={prevBtnInlineStyle}
          className="navBtnInline navBtnPrevInline"
          onClick={() => previous()}
          ref={clickPreviousRef}
        >&nbsp;<span style={prevBtnArrowInlineStyle} className="prevBtnArrowInline">&laquo;</span>&nbsp;</button>
      </div>

      <div  style={reloadInlineStyle} className="custom-button-group-reload-inline">
        <button
          style={navBtnReloadInlineStyle}
          className="navBtnInline navBtnReloadInline"
          onClick={() => goToSlide(0)}
        >&nbsp;<span className="reloadBtnArrowInline">&#x21bb;</span>&nbsp;
        </button>
      </div>

    </div>
  );
};