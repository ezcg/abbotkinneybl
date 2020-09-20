SITE = "abbotkinneybl";
console.log("SITE", SITE);
let siteConfigObj = {};
siteConfigObj.SITE = SITE;
siteConfigObj.pathToJson = '';
siteConfigObj.footerLinksHomepageIcon = ''
siteConfigObj.homepageIconSingleCategoryPage = '';
siteConfigObj.headSiteTitle = "";
siteConfigObj.siteTitle = "";// Main title of site eg. Abbot Kinney Bl
siteConfigObj.siteSubheaderTitle = ""; // Text beneath siteTitle and inside site title containing box
siteConfigObj.subheaderText = "";
siteConfigObj.subheaderText2 = "";
siteConfigObj.teams = 0;// when selecting custom view, 'teams' are selectable, not individuals
siteConfigObj.individuals = 0;// when selecting custom view, 'individuals' are selectable, not teams
siteConfigObj.streamsDefaultItemsDisplay = 0; // in /streams page, display items beneath category by default or not
siteConfigObj.enableSelectingByItems = 0;
siteConfigObj.mobileHomepageFullCategories = 0;
siteConfigObj.homepageFullCategories = 0;
siteConfigObj.useCategoryIconsOnHomepage = 0;
siteConfigObj.navBtnsOnTop = 0; // Don't have navBtns inside row but up top.
siteConfigObj.partialVisible = 1; // This is different from the "centerMode" prop, as it only shows the next items. For the centerMode, it shows both.
siteConfigObj.centerMode = 1; // Shows the next items and previous items partially.
siteConfigObj.homepageIsCustomSelect = 0; // if home page should be the custom select page
siteConfigObj.importCssFile = ''; // set style sheet to be included that overwrites style.css


if (SITE == 'abbotkinneybl') {

  siteConfigObj.pathToJson = 'https://s3.us-east-2.amazonaws.com/abbotkinneybl.ezcg.com/json/';
  siteConfigObj.footerLinksHomepageIcon = 'https://s3.us-east-2.amazonaws.com/ezcg.com/home-outline_black_50x50.png'
  siteConfigObj.homepageIconSingleCategoryPage = 'https://s3.us-east-2.amazonaws.com/ezcg.com/home-outline_grey_50x50.png';
  siteConfigObj.headSiteTitle = "Abbot Kinney Bl";
  siteConfigObj.siteTitle = "Abbot Kinney Bl";
  siteConfigObj.siteSubheaderTitle = "";
  siteConfigObj.subheaderText = "Social media streams from Abbot Kinney Boulevard in Venice Beach, CA.";
  siteConfigObj.subheaderText2 = "";
  siteConfigObj.headerIcon = '';
  siteConfigObj.individuals = 1;
  siteConfigObj.mobileHomepageFullCategories = 1;
  siteConfigObj.homepageFullCategories = 1;
  siteConfigObj.homepageIsCustomSelect = 1;
  siteConfigObj.navBtnsOnTop = 1;
  siteConfigObj.partialVisible = 0;
  siteConfigObj.centerMode = 0;
  siteConfigObj.googleAnalyticsId = '';

  exports.siteConfigObj = siteConfigObj;

} else {
  console.log("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
  console.log("SITE not set");
  console.log("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
}



