What It Was
===========
Gamepd was a project was an experimental videogame finder application aimed at finding recent price drops in titles.  The actual result site was named Uncle Squirrely.  It was my first attempt at trying to make money through affiliate sales.

Front End 
==========
- Had an title order setup based on latest price drops, cheapest games, alphabeticaloOrder, newest release date.
- Game review information based on information from gamepro.
- Chaos TV, which streams random youtube videos about a particular title when you click on the link.

Back End
========
- Python scripts read data from the Amazon Product Advertising API, found good game deals, and updates the database as needed. 
- Admin utilities removed titles that didn't need to be there. 

Lessons Learned
===============
<b>Content is Key.</b> Game Price Drop had very little in terms of content.  The content, by its nature, were all advertisements with links to random Youtube videos.  My guess is that this was why there was next to no search engine traffic.

<b>Technical progress doesn't mean a successful end product.</b> At the time, it was the furthest I had ever dove into Ajax, Javascript, and JQuery.  It used scheduled Python scripts to make calls to Amazon and used the info to revise the MySQL back end database the site depended on.  Throwing around fancy technology did not lead to business success.

<b>Solving your own problem might not lead to a profitable business.</b>  For startup ideas, Paul Graham suggests you build something you want to use to solve a problem you yourself have.  That much I did.  Money was tight and I wanted a simple tool for getting good game deals.  I used it and it helped me time my game purchases better.  So I succeeded in creating value for myself even if I never made money from it.  No regrets there.  