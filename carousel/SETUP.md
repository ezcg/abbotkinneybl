Init
- set up aws s3 bucket 
- set up route 53 if needed
- update helpers/siteconstants.js with config info
- add css import to pages/_app.js if need be

Development
   - hard code site in helpers/siteconstants.js and set config as needed
   - hard code customsite style sheet (if exists) to be included in _app.js. Create custom site style sheet if need be. 
    The styles will override existings styles unless convention of 'customsite.stylename' is specified in the elements.
   
Deployment to now.sh aka Vercel.com
    See https://vercel.com/docs/git-integrations







