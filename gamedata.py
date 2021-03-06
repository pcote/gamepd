# -*- coding: iso-8859-1 -*-
# gamedata.py
# by Phil Cote
# An object oriented attempt to clean up the main logic by taking out all the getter and setter stuff for web services and the DB
# Ultimate goal is to make the code base a bit more test friendly.



"""
Game Data Module.
The main data handler which serves as a go to point for accessing 
web service data and for accessing the database.
"""
from amazonproduct import * # star import used so attribute exceptions can be handled.
import ConfigParser
import MySQLdb
import time
import datetime
import pdb
from pdb import set_trace
import decimal
import urllib
import string
import re
from xml.dom import minidom


# timestamp used for duration of script run.
# set to be global so both database and web service work with the same timestamp
ts = datetime.datetime.now() 



# db column indices
# associated with the GameDatabase class.
# set these constants to global out of laziness.
# of course, being annoyed with the whole usage of constants rather than associative arrays 
# is also a sort of irksome form of motivation i suppose.
ASIN_COL = 0
TITLE_COL = 1
PRICE_COL = 2

LAST_UPDATED = 3
OLD_PRICE = 4
ITEM_IMAGE = 5
ITEM_PAGE = 6
LOWEST_PRICE = 7
PLATFORM = 8
RELEASE_DATE = 9

REVIEW_ID = 0
REVIEW_SCORE = 1
ARTICLE_LINK = 2
REVIEW_ASIN = 3
REVIEW_CONTENT = 4


class WebServiceRecovery(object):
	"""Decorator that makes a few extra attempts to recover gracefully from a web service related error before failing.
	"""
		
	def __call__(self, fun, *args ):

		"""Wrapper for web service related exceptions
		"""
		def newfun( *args ):
			attemptsLimit = 5
			attempts = 0
			succeeded = False
			errorType = ""

			while not succeeded and attempts < attemptsLimit:
				if attempts > 0:
					print( "failure due to %s. Making attempt number: %d" % (errorType, attempts) )
				try:
					res = fun( *args )
					succeeded = True
					return res
				except AWSError:
					errorType = "AWSError"
					attempts = attempts + 1	
				except NoExactMatchesFound:
					attempts = attempts + 1	
					errorType = "NoExactMatchesFound"
				except IOError:
					errorType = "IOError"
					attempts = attempts + 1	

		
			raise Exception( "PROBLEM: %s" % errorType )

		return newfun 


class RecoverDBExceptions(object):
	"""
	Decorator that wraps database exceptions that happens when writing
	"""
	def __init__(self):
		pass

	def __call__(self, f ):
		def wrapperfunc(*args):
			gamerec = args[1]
			asin = gamerec['asin']

			try:
				self.res = f(*args)
		
			except( IntegrityError ):
				print( "integrity error: " )
				updateFile.write( "\nasin number: %s causes an integrity violation and will not be added to the game table" % (asin) )
			except( UnicodeEncodeError ):
				updateFile.write( "\nasin number: %s fails due to unicode encoding problem" % (asin) )
			
			return self.res	
		return wrapperfunc		

class ValidateAsin( object ):
	def __init__( self ):
		pass

	def __call__( self, f ):
		def wrapperfunc( *args ):
			pattern = "^[A-Z0-9]{10}$"
			asin = args[1]
			matchOb = re.match( pattern, asin )
			if matchOb:
				self.res = f( *args )
			else:
				raise Exception( "Invalid Asin: %s" % asin )
			self.res = f( *args )
			return self.res
		return wrapperfunc


class ValidatePlatform( object ):
	def __init__(self):
		pass

	def __call__( self, f ):
		def wrapperfunc( *args ):
			platform = args[1]
			if platform not in [ 'all', 'wii', 'ps3', 'xbox360' ]:
				raise Exception( "%s is an invalid platform" % platform )
			self.res = f( *args )
			return self.res
		return wrapperfunc



class GameDatabase:
	"""Manages game software or hardware data that has to go in, or, or be updated in the database"""

	def __init__(self, configFileName, dbVer ):
		cp = ConfigParser.SafeConfigParser()
		cp.read( configFileName )

		hostString = cp.get( dbVer, "host" )
		pw = cp.get( dbVer, "password" )
		userString = cp.get( dbVer, "user" )
		dbString = cp.get( dbVer, "db" )

		mysql = MySQLdb
		self.mysql = mysql
		self.db = mysql.connect( host=hostString, passwd = pw, user=userString, db=dbString )
		self.csr = self.db.cursor()

	# TODO: Make sure to check this to make sure the review fields set to none doesn't 
	# break anything.  They don't belong there and they don't SEEM to be used.
	# Once deemed safe to do so, this can go away.
	def makeGameDic( self, resSet ):
		"""
		Utility used to turn a database result set into a list of 
		dictionary objects.  Mainly used internally to this library.
		"""
		gameRec = {}
		if resSet != None:
			gameRec = { 'asin':resSet[ASIN_COL], 'gameTitle':resSet[TITLE_COL], \
			'price':resSet[PRICE_COL], 'oldPrice':resSet[OLD_PRICE], \
			'itemImage':resSet[ITEM_IMAGE], 'itemPage':resSet[ITEM_PAGE], \
			'lowestPrice':resSet[LOWEST_PRICE], 'reviewScore':None, \
			'reviewLink':None, 'lastUpdated':resSet[LAST_UPDATED], 'platform':resSet[PLATFORM], 'releaseDate':resSet[RELEASE_DATE] }
		return gameRec

	@ValidateAsin()
	def getGameRecord( self, asinNum ):
		"""Pull a single game rec based on it's asin from the database."""
		gameRec = None
		query = "select * from games where asin = %s"
		resCount = self.csr.execute( query, ( asinNum ) )

		resSet = self.csr.fetchone()
		gameRec = self.makeGameDic( resSet )
		
		return gameRec

	@ValidatePlatform()
	def getAllGames( self, platform ):
		"""
		Gets a full list os ASINS.
		TODO: Not yet complete.  Do not use yet.
		"""

		query = "select * from games where platform = '" + platform + "'";
		if platform == 'all':
			query = "select * from games"

		resCount = self.csr.execute( query )
		resSet = self.csr.fetchall()
	
		gameList = list()
		for res in resSet:
			gameRec = self.makeGameDic( res )
			gameList.append( gameRec )

		return gameList

	@ValidateAsin()
	def getHardwareRecord( self, asinNum ):
		"""Pull a hardware rec based on the asin.
		Keyword arguments:
		asinNum -- The amazon asin identifier value.
		"""

		query = "select * from game_hardware where asin = %s"
		resCount = self.csr.execute( query, ( asinNum ) )
		resSet = self.csr.fetchone()
		return resSet



	def getGameByTitle( self, titleArg ):
		""" Pulls a game based on it's title.
		TODO: This could be a problem for games that happen to be on multiple platforms."""

		query = "select * from games where game_title = %s"
		resCount = self.csr.execute( query, titleArg )
		if resCount == 0:
			return None
		else:
			resSet = self.csr.fetchone()
			game = self.makeGameDic( resSet )
			return game	

	@RecoverDBExceptions()
	def addGame( self, game ):
		"""Adds a single game to the database."""
	
		insertQuery = """insert into games(asin,game_title,price,last_updated,old_price, item_image, item_page, lowest_price, platform, release_date ) values( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )"""
		asin = game['asin']
		title = game['gameTitle'].encode( 'latin1', 'xmlcharrefreplace' )
		price = game['price']
		itemImage = game['itemImage']
		itemPage = game['itemPage']
		lowestPrice = game['lowestPrice']
		platform = game['platform']
		releaseDate = game['releaseDate']
		self.csr.execute( insertQuery, ( asin, title, price, ts, price, itemImage, itemPage, lowestPrice, platform, releaseDate ) )

	@RecoverDBExceptions()
	def updatePrice( self, game ):
		"""Makes price changes to a specific game."""  

		updateQuery = """update games set price = %s, old_price = %s, last_updated = %s where asin = %s"""
		asin = game['asin']
		gameRec = self.getGameRecord( asin )
		newPrice = game['price']
		oldPrice = gameRec['price']
		self.csr.execute( updateQuery, ( newPrice, oldPrice, ts, asin ) )

	def addHardware( self, hwItem ):
		"""Adds a piece of hardware to the database."""


		query = """insert into game_hardware values( %s, %s )"""
		self.csr.execute( query, ( hwItem['asin'], hwItem['item_name'] ) )

		# removal any hardware that might be on the games table. (done to get around trigger permission problems on prod.)
		self.removeGame( hwItem['asin'] )
	
	@RecoverDBExceptions()
	def refreshLowestPrice( self, wsGame ):
		""" Ensures that the lowest price always ends up being the latest price from the web service

		NOTE: An update to the "last_updated" field does not need to happen for these cases."""
		query = "update games set lowest_price = %s where asin = %s"
		self.csr.execute( query, ( wsGame[ 'lowestPrice'], wsGame[ 'asin' ] ) )	

	@ValidateAsin()
	def removeGame( self, asin ):
		"""Deletes a game according to it's Amazon asin number"""
		query = "delete from games where asin = %s"
		self.csr.execute( query, ( asin ) )

	@ValidateAsin()
	def isExcluded( self, asin ):
		"""
		Check to see if the given asin is on the list of excluded records.
		"""
		query = "select * from game_exclusions where asin = %s"
		self.csr.execute( query, (asin ) )
		results = self.csr.fetchone()
		if results == None:
			return False
		return True

	def deleteExclusions(self):
		"""Deletes anything in the list of software games that have been marked as an exclusion"""
		query = "delete from games where asin in ( select asin from game_exclusions )"
		self.csr.execute( query )


	def getExclusions( self ):
		"""
		Gets a simple list of excluded data recordss. (asin and reason)
		"""
		ASIN = 0
		REASON = 1
		query = "select * from game_exclusions"
		self.csr.execute( query )
		resSet = self.csr.fetchall()
		exclusionList = list()
		for res in resSet:
			excludedItem = { 'asin': res[ ASIN ], 'reason': res[ REASON ] }
			exclusionList.append( excludedItem )
		return exclusionList


	def close(self):
		"""
		Closes the mysql connection for the game database.
		"""
		self.csr.close()





class GameWebService:
	"""Reads data from Amazon web Services pertaining to games, hardware, or game controllers"""
	def __init__(self, configFileName, dbVer ):
		cp = ConfigParser.SafeConfigParser()
		cp.read( configFileName )

		# set up of main interface to amazon api
		AWS_KEY = cp.get( dbVer, "aws_key" )
		SECRET_KEY = cp.get( dbVer, "secret_key" )
		self.api = API(AWS_KEY, SECRET_KEY, 'us') 

		# important browse node ids
		self.PS3_HARDWARE = 14210671
		self.XBOX360_HARDWARE = 696756
		self.WII_HARDWARE = 14218821
		self.XBOX360_GAMES = 14220271
		self.PS3_GAMES = 14210861
		self.WII_GAMES = 14219011

		self.GAME_CONTROLLERS =  16229301

		self.MAX_ALLOWABLE_PAGES = 400 # amazon's 400 page web service query limit


	def _getPrice( self, node ):
		"""Pulls the list price from the item node and returns it.
		TODO: Find a way to fix the "ugly hack".  Also, there's a bit of a weird
		floating point result being spat out here.  Not a huge deal since number formatting

		on the PHP side takes care of it pretty well in most cases. (give or take a penny)"""

		price = -1
	
		if etree.tostring(node).find("<ListPrice>") > -1: # UGLY HACK
			priceString = node.ItemAttributes.ListPrice.Amount
			price = float( priceString ) / 100.0
	
		
		return price


	def _getLowestPrice( self, node ):
		"""get the lowest available price from the item node passed in here."""

		price = -1
		if etree.tostring(node).find( "<LowestNewPrice>" ) > -1:
			if etree.tostring(node).find("Too low to display" ) < 0:
				price = float( node.OfferSummary.LowestNewPrice.Amount )
				price = price / 100.0
		return price

	@ValidatePlatform()
	@WebServiceRecovery()
	def getGames( self, platform, pageNum ):
		"""pull games based on the platform. 
		 TODO: Make the platform parameter matter.  For right now, it's just ignoring it and
		 going straight to ps3 data.  Eventually, it's going to need to get wii and xbox 360 titles."""

		bNode = "14210861" # defaults to ps3 game node

		

		if platform == 'xbox360':
			bNode = self.XBOX360_GAMES
		elif platform == 'wii':
			bNode = self.WII_GAMES
	
		gameList = list()
	
		node = self.api.item_search( "VideoGames", BrowseNode=bNode, ResponseGroup="Small,ItemAttributes,Offers,Images", ItemPage=pageNum )
		
		for node in node.Items.Item:
			asin = unicode(node.ASIN)
			gameTitle = unicode(node.ItemAttributes.Title)
			price = self._getPrice(node)
			lowestPrice = self._getLowestPrice( node )
			itemPage = str( node.DetailPageURL )
			itemImage = "NoImage"
			releaseDate = "Date Unknown"			
			if hasattr( node, "MediumImage" ):
				itemImage = str( node.MediumImage.URL )

			if hasattr( node.ItemAttributes, "ReleaseDate" ):
				releaseDate = str( node.ItemAttributes.ReleaseDate )
				releaseDateArr = string.split( releaseDate, "-" )
				yr = int(releaseDateArr[0])
				mo = int(releaseDateArr[1])
				dy = int(releaseDateArr[2])
				releaseDate = datetime.datetime( yr, mo, dy, 0,0,0,0 ) 
			else:
				releaseDate = None
		
			gameRec = { "asin":asin, "gameTitle":gameTitle, "price":price, "itemPage":itemPage, \
				"itemImage":itemImage, "lowestPrice":lowestPrice, "platform":platform, "releaseDate":releaseDate }
			gameList.append( gameRec )
			
		return gameList

	
	@ValidateAsin()
	@WebServiceRecovery()
	def getSingleGame( self, asin, platform=None ):
		"""returns data on a single game. ( mainly used for debugging and testing special cases )"""

		node = self.api.item_lookup( asin, ResponseGroup="Small,ItemAttributes,Offers,Images" )
		item = node.Items.Item
		
		gameTitle = unicode( item.ItemAttributes.Title )
		price = self._getPrice( item )
		lowestPrice = self._getLowestPrice( item )
		itemPage = str( item.DetailPageURL )
		itemImage = "NoImage"
		if hasattr( item, "MediumImage" ):
			itemImage = str( item.MediumImage.URL )
		
		if hasattr( item.ItemAttributes, "ReleaseDate" ):
			releaseDate = str( item.ItemAttributes.ReleaseDate )
			releaseDateArr = string.split( releaseDate, "-" )
			yr = int(releaseDateArr[0])
			mo = int(releaseDateArr[1])
			dy = int(releaseDateArr[2])
			releaseDate = datetime.datetime( yr, mo, dy, 0,0,0,0 ) 
		else:
			releaseDate = None
		
		gameRec = { "asin":asin, "gameTitle":gameTitle, "price":price, "itemPage":itemPage, \
				"itemImage":itemImage, "lowestPrice":lowestPrice, "platform":platform, "releaseDate":releaseDate }
		if platform != None:
			gameRec['platform'] = platform
		return gameRec
				

	@WebServiceRecovery()
	def getGamePageCount( self, platform ):
		"""gets the number of pages available of data available for this platform.
		platform arg expects a string arg (example 'xbox360', NOT an amazon node identifier"""

		bNode = self.PS3_GAMES # ps3 game node default
		if platform == 'xbox360':
			bNode = self.XBOX360_GAMES
		elif platform == 'wii':
			bNode = self.WII_GAMES;

		node = self.api.item_search( "VideoGames", BrowseNode=bNode, ResponseGroup="Small" )
		pageCount = int( node.Items.TotalPages )
		return pageCount



	@WebServiceRecovery()
	def getHardware( self, browseNodeId, pageNum ):
		"""Pull a page of lexified xml web service data from amazon and return pertainent data
		as a dictionary list."""

		node = self.api.item_search( "VideoGames", BrowseNode=browseNodeId, ResponseGroup="Small", ItemPage=pageNum )
		hardwareList = list()
		

		for node in node.Items.Item:
			try:
				asin = unicode( node.ASIN )
				itemName = unicode( node.ItemAttributes.Title )
				hardRec = { "asin":asin, "item_name":itemName }
				hardwareList.append( hardRec )
			except( UnicodeEncodeError ):
				print( "Error while processing unicode in wsGetHardware" )
	
		return hardwareList

	@WebServiceRecovery()
	def getHardwarePageCount( self, browseNodeId ):
		"""Get the number of pages of data available for this hardware node."""

		node = self.api.item_search( "VideoGames", BrowseNode=browseNodeId, ResponseGroup="Small" )
		pageCount = int( node.Items.TotalPages )
		return pageCount






class ReviewWebService:
	"""Grabs game review data from gamepro.  That is all"""	
	def __init__(self, configFileName, dbVer ):
		# note: technically, you don't need the configFileName argument.  there really isn't anything special about the args here.
		# it's just here to be consistant with all the other classes.
		cp = ConfigParser.SafeConfigParser()
		cp.read( configFileName )
		self.gameprokey=cp.get( dbVer, "gameprokey" )

	@ValidatePlatform()
	@WebServiceRecovery()
	def getAllReviews( self, platform ):
		"""Pulls review information for all games for a given platform."""

		baseURL = "http://api.gamepro.com/svc/content/get"
		argList = "platform=" + platform + "&genre=all&article_type=reviews&esrb=all&return_type=xml&max=1000&page=1&apiKey=" + self.gameprokey
		url = baseURL + "?" + argList
		sock = urllib.urlopen( url )
		rawData =  sock.read()
		domRoot = minidom.parseString( rawData )
		contentList = domRoot.getElementsByTagName( "content" )
		recordCount = 0
		reviewList = []

		for content in contentList:
			score = '-1' # default val in case no score node value exists for this review.
			recordCount = recordCount + 1
			reviewID = content.getElementsByTagName( "content_id" )[0].firstChild.nodeValue
			title = content.getElementsByTagName( "content_title" )[0].firstChild.nodeValue
			scoreNode = content.getElementsByTagName( "content_score" )[0].firstChild
			linkBackNode = content.getElementsByTagName( "link_back_url" )[0].firstChild
			reviewContent = content.getElementsByTagName( "content_body" )[0].firstChild.wholeText
			# review ids and titles can be depeneded on.  page links and scores not always.
			if scoreNode:
				score = scoreNode.nodeValue
				linkBackURL = linkBackNode.nodeValue
				reviewList.append( { "review_id":reviewID, "game_title":title, "review_score":score, \
						     "link_back_url":linkBackURL, "review_content":reviewContent } )

		return reviewList


	
class ReviewDatabase:
	"""Handles reading and updating review data in the database."""

	def __init__(self, configFileName, dbVer ):
		cp = ConfigParser.SafeConfigParser()
		cp.read( configFileName )

		hostString = cp.get( dbVer, "host" )
		pw = cp.get( dbVer, "password" )
		userString = cp.get( dbVer, "user" )
		dbString = cp.get( dbVer, "db" )

		mysql = MySQLdb
		self.db = mysql.connect( host=hostString, passwd = pw, user=userString, db=dbString )
		self.csr = self.db.cursor()		


	def reviewExists( self, review ):
		"""boolean function to determine whether or not a review is currently in the database.
		review -- The review ID based on Gamepro's API"""	

		revID = str(review[ "review_id" ])
		query = "select * from game_reviews where review_id = %s"
		resSet = self.csr.execute( query, (revID ) )
		if self.csr.fetchone() == None:
			return False
		return True


	def addReview( self, game, review ):
		"""Puts a review into the database.

		game -- The game dictionary object.  Should have originated from the database. (I'm 90% sure of this)
		review -- review dictionary object.  Should have originated from the gamepro api.
		"""

		query = "insert into game_reviews values( %s, %s, %s, %s, %s )"
		reviewID = str(review['review_id'])
		reviewScore = str(review['review_score'])
		articleLink = str(review['link_back_url'])
		asin = game['asin']
		content = review['review_content']
		
		self.csr.execute( query, ( reviewID, reviewScore, articleLink, asin, content ) )

	def getReviews( self ):
		"""Pull a list of all the reviews from the database regardless of platform"""
		resList = list()
		query = "select * from game_reviews"
		self.csr.execute( query )
		
		resSet = self.csr.fetchall()	
		for res in resSet:
			newListItem = { 'reviewID': res[REVIEW_ID], 'score': res[REVIEW_SCORE], \
					'articleLink': res[ARTICLE_LINK], 'asin':res[REVIEW_ASIN], 'reviewContent':res[REVIEW_CONTENT] }
			resList.append( newListItem )

		return resList

	def close(self):
		"""
		Closes the mysql connection for the review database.
		"""
		self.csr.close()


if __name__ == "__main__":
	pass

