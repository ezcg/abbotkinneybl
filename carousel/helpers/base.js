import fetch from "isomorphic-unfetch";
import MobileDetect from "mobile-detect";
import Link from "next/link";
let {siteConfigObj} = require('../helpers/siteconstants');
let images = require('../components/images');
let twitterPlatformIcon = images.twitterPlatformIcon;
let yelpPlatformIcon = images.yelpPlatformIcon;
let redditPlatformIcon = images.redditPlatformIcon;
let instagramPlatformIcon = images.instagramPlatformIcon;

export function getPlatformIconSrcWithSite(site, className) {
  let platformIconTag = '';
  let classStr = '';
  if (className) {
    classStr = 'class="' + className + '"';
  }
  if (site === 'twitter.com') {
    platformIconTag = '<img ' + classStr + ' src="' + twitterPlatformIcon + '"/>';
  } else if (site === 'yelp.com') {
    platformIconTag = '<img ' + classStr + ' src="' + yelpPlatformIcon + '" />';
  } else if (site === 'reddit.com') {
    platformIconTag = '<img ' + classStr + ' src="' + redditPlatformIcon + '" />';
  } else if (site === 'instagram.com') {
    platformIconTag = '<img ' + classStr + ' src="' + instagramPlatformIcon + '" />';
  }
  return platformIconTag;
}

export async function getFooterLinksArr(addHomepageLink) {
  console.log("siteConfigObj.pathToJson", siteConfigObj.pathToJson);
  let r = await fetch(siteConfigObj.pathToJson + 'footerlinks.json');
  let footerLinksArr = await r.json();

  // Add homepage icon link
  if (addHomepageLink) {
    let footerLinksObj = {
      id:0,
      name: "Homepage",
      link: "/",
      imgsrc: siteConfigObj.footerLinksHomepageIcon
    };
    footerLinksArr.push(footerLinksObj);
  }
  return footerLinksArr;

}

export function getDeviceType(context) {
  let userAgent;
  let deviceType;
  if (context && context.req) {
    userAgent = context.req.headers["user-agent"];
  } else {
    userAgent = navigator.userAgent;
  }
  const md = new MobileDetect(userAgent);
  if (md.tablet()) {
    deviceType = "tablet";
  } else if (md.mobile()) {
    deviceType = "mobile";
  } else {
    deviceType = "desktop";
  }
  return deviceType;
}

export async function getSingleCategory(context) {

  if (context.query.id == 'favicon.ico') {
    return false;
  }

  let r = await fetch(siteConfigObj.pathToJson + 'max_level_category.json');
  let categoryArr = await r.json();
  let categoryId = categoryArr.filter(categoryObj => {
    return (getPageIdFromStr(categoryObj.title) === context.query.id);
  }).map(categoryObj => categoryObj.id).pop();
  if (categoryId !== 0 && !categoryId) {
    console.log("helpers/base getSingleCategory did not find category id with " + context.query.id);
    return false
  }
  let categoryName = categoryArr.filter(categoryObj => {
    return (getPageIdFromStr(categoryObj.title) === context.query.id);
  }).map(categoryObj => categoryObj.title).pop();

  return {categoryId: categoryId, categoryName: categoryName};

}

export async function getDivisionCategory(context) {

  if (context.query.id == 'favicon.ico') {
    return false;
  }

  let r = await fetch(siteConfigObj.pathToJson + 'division_category.json');
  let divisionCategoryArr = await r.json();
  // 0 => {cat_id, cat_title, teams => {title, id}}
  let divisionCategoryId = divisionCategoryArr.filter(categoryObj => {
    return (getPageIdFromStr(categoryObj.cats_title) === context.query.id);
  }).map(categoryObj => categoryObj.cats_id).pop();
  if (divisionCategoryId !== 0 && !divisionCategoryId) {
    console.log("helpers/base getDivisionCategory did not find division category id with " + context.query.id);
    return false;
  }
  let divisionCategoryName = divisionCategoryArr.filter(categoryObj => {
    return (getPageIdFromStr(categoryObj.cats_title) === context.query.id);
  }).map(categoryObj => categoryObj.cats_title).pop();

  // Set the teams in the division
  let divisionCategoryTeamArr = [];
  for(let i in divisionCategoryArr) {
    if (divisionCategoryArr[i].cats_id == divisionCategoryId) {
      divisionCategoryTeamArr = divisionCategoryArr[i].teams;
      break;
    }
  }

  return {categoryId:divisionCategoryId, categoryName:divisionCategoryName, divisionCategoryTeamArr:divisionCategoryTeamArr};

}


export async function getFeedArrWithItemsArr(itemsArr, depth) {

  depth = typeof depth != 'undefined' ? depth : 'shallow';
  let feedArr = [];
  for(let i in itemsArr) {

    let itemsId = itemsArr[i];
    let url = siteConfigObj.pathToJson + 'items_id_' + itemsId + '.json';
    let r = await fetch(url);
    let responseJson = await r.json();

    feedArr[itemsId] = [];
    for (let key in responseJson.social_media) {
      let obj = {};
      obj[itemsId] = responseJson;
      let tmpObj = getFeedObj(itemsId, key, obj);
      tmpObj['depth'] = depth;
      feedArr[itemsId].push(tmpObj);
    }

    let emptyObj = getEmptyFeedObj(itemsId, depth);
    feedArr[itemsId].push(emptyObj);

  }

  return feedArr;

}

export async function getFeedArrWithCategoryIndexArr(categoryIndexArr) {

  let responseJson;
  let feedArr = [];
  for(let i in categoryIndexArr) {
    let categoryId = categoryIndexArr[i];
    let r = await fetch(siteConfigObj.pathToJson  + 'category_' +  categoryId + '.json');
    responseJson = await r.json();
    if (responseJson != 'undefined' && Object.keys(responseJson).length === 0) {
      console.log("NO JSON FOUND FOR categoryId " + categoryId);
      console.log(siteConfigObj.pathToJson + categoryId + '.json');
      console.log("categoryId " + categoryId + " should not have been activated");
    }
    feedArr[categoryId] = [];
    for (let itemsId in responseJson) {
      for (let key in responseJson[itemsId].social_media) {
        let tmpObj = getFeedObj(itemsId, key, responseJson);
        feedArr[categoryId].push(tmpObj);
      }
    }

    feedArr = getOrderedDivisionFeed(feedArr);

    let emptyObj = getEmptyFeedObj();
    feedArr[categoryId].push(emptyObj);

  }

  return feedArr;

}

export function getOrderedDivisionFeed(feedArr) {

  // Order the content cards within each category by rank.
  // index is set to itemsId so that it may be referenced in the rank object
  let tmpFeedArr = [];
  let orderedFeedArr = [];
  for(let categoryId in feedArr) {
    let rankArr = [];
    tmpFeedArr[categoryId] = [];
    for(let i in feedArr[categoryId]) {
      let itemsId = feedArr[categoryId][i]['items_id'];
      tmpFeedArr[categoryId][itemsId] = {...feedArr[categoryId][i]};
      let rankObj = {'itemsId' : itemsId, 'rank': feedArr[categoryId][i]['rank']};
      rankArr.push(rankObj);
    }
    rankArr.sort(function(a, b){return a.rank - b.rank});
    orderedFeedArr[categoryId] = [];
    for(let i in rankArr) {
      let itemsId = rankArr[i]['itemsId'];
      orderedFeedArr[categoryId].push(tmpFeedArr[categoryId][itemsId]);
    }
  }

  return orderedFeedArr;

}

// eg. 'AFC East' 'NY Giants'
export function displayPageCategoryTitleHeader(categoryName) {

  if (!categoryName) {
    return '';
  }
  return <div>
    <div className='categoryTitleCont'>{categoryName}</div>
    <div className='cb'></div>
  </div>

}

export function getResponsiveObj() {
  return {
    desktop: {
      breakpoint: { max: 3000, min: 700 },
      items: 3,
      partialVisibilityGutter: 0
    },
    tablet: {
      breakpoint: { max: 700, min: 464 },
      items: 1,
      partialVisibilityGutter: 0
    },
    mobile: {
      breakpoint: { max: 464, min: 0 },
      items: 1,
      minimumTouchDrag:20,
      partialVisibilityGutter: 0
    },
  };
}

export function getSlidesToSlide(deviceType) {
  let slidesToSlide = 3;
  if (deviceType === "mobile") {
    slidesToSlide = 1;
  } else if (deviceType === "tablet") {
    slidesToSlide = 1;
  }
  return slidesToSlide;
}

export function linkifyStr(str) {
  let tmp = getPageIdFromStr(str);
  return encodeURIComponent(tmp);
}

// Convert "NY Giants" to "ny-giants"
export function getPageIdFromStr(str) {
  if (!str) return '';
  str = str.toLowerCase().replace(/ /g, "-");
  return str;
}

export function getThumbnailImage(teamObj, page) {

  if (page == 'home' && siteConfigObj.useCategoryIconsOnHomepage == 0) {
    return '';
  }

  let image = '';
  if (teamObj && teamObj.image) {
    image = teamObj.image;
  } else if (teamObj && teamObj.avatar) {
    image = teamObj.avatar;
  }

  if (image) {
    return "<div class='indexCategoryThumbnailCont'>" +
      "<img class='indexCategoryThumbnailImg' src='" + image.replace("http:", "") + "'>" +
      "</div>";
  } else{
    return '';
  }

}

export function getDivisionThumbnailImage(image, width, height) {

  if (image) {

    return "<div class='categoryThumbnailCont'>" +
      "<img class='indexCategoryThumbnailImg' src='" + image + "'>" +
      "</div>";
  } else{
    return "<div class='categoryThumbnailCont'> &nbsp; </div>";;
  }

}
export function getCategoryTabForRow(categoryName, pageType, categoryImage) {

  if (!categoryName) return '';

  let categoryImageDiv = getDivisionThumbnailImage(categoryImage);

  if (pageType != 'singleCategoryPage' && pageType != 'selectedIndividualsPage') {
    let categoryNameAs = linkifyStr(categoryName);
    return <div className="categoryTabAndImageCont">
      <Link href="/[id]" as={categoryNameAs}>
        <a title={categoryName}>
          <div style={{float:"left"}} dangerouslySetInnerHTML={{ __html: categoryImageDiv}} />
        </a>
      </Link>
      <Link href="/[id]" as={categoryNameAs}>
        <a className="linkTitle" title={categoryName}>
          <div className='clickableCategoryTab'>{categoryName}<div className="htmlArrow">&raquo;</div></div>
        </a>
      </Link>
      <div className='cb'></div>
    </div>
  } else {
    return <div className="categoryTabAndImageCont">
      <div style={{float:"left"}} dangerouslySetInnerHTML={{ __html: categoryImageDiv}} />
      <div style={{cursor:"auto"}} className='categoryTab'>
        {categoryName}
      </div>
      <div className='cb'></div>
    </div>

  }

}

export function getFeedObj(itemsId, key, responseJson) {

  let tmpObj = {};
  // make sure avatars use no protocol eg. //www.domain.com not http://www.domain.com
  let avatar = responseJson[itemsId].avatar;
  avatar = avatar ? avatar.replace("http:", "") : "";
  tmpObj.avatar = avatar;
  tmpObj.description = responseJson[itemsId].description;
  tmpObj.items_id = parseInt(responseJson[itemsId].items_id);
  tmpObj.title = responseJson[itemsId].title;
  tmpObj.social_media =  responseJson[itemsId].social_media[key];
  tmpObj.social_media_accounts_arr = [];
  if (responseJson[itemsId].social_media_accounts_arr && Object.keys(responseJson[itemsId].social_media_accounts_arr).length) {
    tmpObj.social_media_accounts_arr = responseJson[itemsId].social_media_accounts_arr;
  }
  tmpObj.address = '';
  tmpObj.hours = '';
  tmpObj.phone = '';
  tmpObj.website = '';
  tmpObj.lat = '';
  tmpObj.lon = '';
  tmpObj.history = '';// likely wikipedia info
  tmpObj.rank = responseJson[itemsId].rank;
  if (responseJson[itemsId].address) {
    tmpObj.address = responseJson[itemsId].address;
  }
  if (responseJson[itemsId].hours) {
    tmpObj.hours = responseJson[itemsId].hours;
  }
  if (responseJson[itemsId].phone) {
    tmpObj.phone = responseJson[itemsId].phone;
  }
  if (responseJson[itemsId].website) {
    tmpObj.website = responseJson[itemsId].website;
  }
  if (responseJson[itemsId].lat) {
    tmpObj.lat = responseJson[itemsId].lat;
  }
  if (responseJson[itemsId].lon) {
    tmpObj.lon = responseJson[itemsId].lon;
  }
  if (responseJson[itemsId].history) {
    tmpObj.history = responseJson[itemsId].history;
  }
  if (responseJson[itemsId].history_url) {
    tmpObj.history_url = responseJson[itemsId].history_url;
  }
  tmpObj.depth = 'shallow';
  return tmpObj;
}

export function getEmptyFeedObj(itemsId, depth) {

  itemsId = typeof itemsId == 'undefined' ? 999999999 : itemsId;
  let emptyObj = {};
  emptyObj.isEnd = 1;
  emptyObj.items_id = itemsId;
  emptyObj.title = "Category End";
  emptyObj.depth = depth ? depth : "shallow";
  emptyObj.social_media = {};
  emptyObj.social_media['text'] = "<div class='endOfFeed'>" + emptyObj.title + "</div>";
  emptyObj.social_media['items_id'] = itemsId;
  return emptyObj;

}

export function getCookie(cname) {
  if (typeof document == 'undefined') {
    console.log("COOKIE FAIL trying to getCookie when document.cookie is not available");
    return false;
  }
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

export function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/;SameSite=None;Secure";
}
