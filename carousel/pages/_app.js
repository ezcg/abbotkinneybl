/*
This is copy paste from the with-material-ui example.
*/
console.log(React.version);
import React from 'react'
import App from 'next/app'
import Head from 'next/head'
import { MuiThemeProvider } from '@material-ui/core/styles'
import CssBaseline from '@material-ui/core/CssBaseline'
import JssProvider from 'react-jss/lib/JssProvider'
import getPageContext from '../src/getPageContext'
let {siteConfigObj} = require('../helpers/siteconstants');
import ReactGA from "react-ga";
import Link from "next/link";
import 'react-slidedown/lib/slidedown.css'
let helpersBase = require('../helpers/base');

import '../css/main.css'
import("../css/extra.css");

if (typeof window !== "undefined") {
  let ignoreVisitor = helpersBase.getCookie("ignore_visitor");
  if (!ignoreVisitor) {
    ReactGA.initialize(siteConfigObj.googleAnalyticsId);
    ReactGA.pageview(window.location.pathname + window.location.search);
  }
}


import 'react-multi-carousel/lib/styles.css'


class MyApp extends App {
  constructor() {
    super()
    this.pageContext = getPageContext()
  }
  componentDidMount() {
    // Remove the server-side injected CSS.
    const jssStyles = document.querySelector('#jss-server-side');
    if (jssStyles && jssStyles.parentNode) {
      jssStyles.parentNode.removeChild(jssStyles);
    }
  }

  getHeaderLogo() {
    if (siteConfigObj.headerIcon) {
      return <Link href="/">
        <a className="headerTitle" title={siteConfigObj.siteTitle}>
          <img src={siteConfigObj.headerIcon} className="headerLogo" />
        </a>
      </Link>
    } else {
      return '';
    }
  }

  getSiteSubheaderTitle() {
    if (!siteConfigObj.siteSubheaderTitle) {
      return '';
    } else {
      return <div className='siteTitleSubHeader'>{siteConfigObj.siteSubheaderTitle}</div>
    }
  }

  render() {

    const { Component, pageProps } = this.props
    return (
      <>
        <Head>
          <title>{siteConfigObj.siteTitle}</title>
        </Head>
        <JssProvider
          registry={this.pageContext.sheetsRegistry}
          generateClassName={this.pageContext.generateClassName}
        >
          <MuiThemeProvider
            theme={this.pageContext.theme}
            sheetsManager={this.pageContext.sheetsManager}
          >
            <CssBaseline />
            {/* Pass pageContext to the _document though the renderPage enhancer
                to render collected styles on server-side. */}

            <h1 className="siteTitleCont">
              <div className="siteTitle">
                {this.getHeaderLogo()}
                <div className="linkTitleCont">
                <Link href="/">
                  <a className="headerTitle" title={siteConfigObj.siteTitle}>{siteConfigObj.siteTitle}</a>
                </Link>
                </div>
                {this.getSiteSubheaderTitle()}
                </div>
              <div className="clearBoth"></div>
            </h1>

            <Component pageContext={this.pageContext} {...pageProps} />
          </MuiThemeProvider>
        </JssProvider>
      </>
    )
  }
}

export default MyApp
