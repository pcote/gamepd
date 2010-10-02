# -*- coding: latin-1 -*-

#dbpopfromwebservice.py
# by Phil Cote
# Purpose: Migrate Playstation 3 data from amazon web services to mysql.
# the database being used is also used by php as a source for generating web pages
# Last Updated: September 14, 2010
# Status: Curenlty working on getting xbox 360 data into the system.

# NOTE: Still a consitency problem.  Hardware functions take browse nodes while
# the actual game functions take strings.

# TODO: Hardware will slip into the system on the "process software pass"
# and get eliminated from the game table during the two "process non games pass".
# For a go live scenario, I'll probably want this to be transactional so users don't 
# end up seeing a whole lot of hardware while an update of the database is in progress.

from amazonproduct import * # star import used so attribute exceptions can be handled.
import MySQLdb
import time
import datetime
import pdb
import gamedata
from gamedata import *


# Determine whether the game from the web service meets criteria for 
# being stored in the database.
def shouldAddGameToDatabase( game ):
		
		# confirm that the price is valid.
		if game['price'] == -1:
			return False
		
		# make sure it's not hardware.
		if getDBHardwareRecord( game['asin'] ) != None:
			return False
		
		# only stuff that's 49.99 or less should be added to the database. (applicable to xbox 360 and ps3 )
		if game['price'] >= 50:
			return False

		# since brand new list price is already 49.99, bargains for wii have to be less than 40 dollars.
		if game['platform'] == 'wii' and game['price'] >= 40:
			return False
		
		# if the asin already exists in the database, it doesn't need to be astedded again.
		dbRec = getDBGameRecord( game['asin'] )
		if len( dbRec ) > 0:
			return False
		
		# online game codes not allowed.  physical titles only
		titleString = game['gameTitle']

		if titleString.find( '[Online Game Code' ) > 0:
			return False
			
		return True
			

# Determine whether or not the price should be updated.
# TODO: This is only good for list prices.  Need something for "lowest price" updates too.			
def shouldUpdateListPrice( game ):
	PRICE_COL = 2
	
	# check for presence of a price in the object
	if game['price'] > -1:
		pass
	else:
		return False
	
	
	wsPrice = game['price']
	dbRec = getDBGameRecord( game['asin'] )
	
	# empty rec case
	if len(dbRec) == 0:
		return False
	# price in the database made obsolete by the web service price.
	if dbRec['price'] != wsPrice:
		return True
	return False

# Take a raw list of games (typically 10) and store them in the database.
# NOTE: At this pass, non games can end up in here.  ( controllers, consoles, ect )
# Removal of those is taken care of on a separate pass.
def storeGameData( wsGameList ):
	asin=""
	price=0
	gameTitle=""
	errorCount = 0
	
	for wsGame in wsGameList:
		try:
			asin = wsGame['asin']
			gameTitle = wsGame['gameTitle']
			price = wsGame['price']
			#print( "DEBUG: asin: " + asin + " title: " + gameTitle ) # keep for collecting samples to debug by			
			if shouldAddGameToDatabase( wsGame ):
				addGameToDatabase( wsGame )
				print( "added asin: " + asin + " title: " + gameTitle ) # keep for collecting samples to debug by
			elif shouldUpdateListPrice( wsGame ):
				updatePriceInDatabase( wsGame )
				print( "updated asin: " + asin + " title: " + gameTitle ) # keep for collecting samples to debug by

			refreshLowestPrice( wsGame )

			
		except( mysql.IntegrityError ):
			errorCount = errorCount + 1
			print( "integrity errors: " + str( errorCount ) )
			failfile.write( "\nasin number: %s causes an integrity violation and will not be added to the game table" % (asin) )
		except( UnicodeEncodeError ):
			failfile.write( "\nasin number: %s fails due to unicode encoding problem" %(asin) )
			
			

# Stores non game data into it's own table in mysql.  
# Data stored sets will come from either hardware or controller categories in amazon.
def storeNonGameData( hardwareList ):

	for hwItem in hardwareList:
		try:
			if getDBHardwareRecord( hwItem['asin'] ) == None:
				addHardwareToDatabase( hwItem )
		except( mysql.IntegrityError ):
			print( "integrity error regarding item: " + hwItem['asin'] )
		except( UnicodeEncodeError ):
			print( "store non game data: UNICODE fail over item " + hwItem['asin'] )
		


# High level function for processing playstation 3 data in general.
# It pulls game data from the web service and loops through the recs
# to add to the database, update, or discard.
def processSoftwareData( platform ):
	pageCount = wsGetGamePageCount( platform )
	gameList = wsGetGames( platform, 1 )
	curPage = 1
	
	storeGameData( gameList )
	print( "\nfinished writing software item page: " + str( curPage ) + " of " + str(pageCount) )
	time.sleep(1)

	print( "collecting software data for the %s..." % ( platform ) )
	while curPage < pageCount:
		curPage = curPage + 1
		gameList = wsGetGames( platform, curPage )
		storeGameData( gameList )
		print( "\nfinished writing item page: " + str( curPage ) + " of " + str(pageCount)  )	


# High level function for processing non game data ( controllers and hardware come from separate browse nodes )
# It pulls hardware data from the web service and loops through the recs
# to add to the database.
def processNonGameData( bNode ):
	
	hardwareList = wsGetHardware(  bNode, 1 )
	storeNonGameData(  hardwareList )
	
	pageCount = wsGetHardwarePageCount( bNode )
	curPage = 1 # NOTE: may need to be changed for debugging purposes so we don't have to wait as long to get to the "odd" data cases.
	print( "\nfinished writing item page: " + unicode( curPage ) )
	time.sleep(1)
	print( "\n\ncollecting ps3 non game data: " + unicode( pageCount) + " pages" )
	
	while curPage < pageCount and curPage < gamedata.MAX_ALLOWABLE_PAGES:
		try:
			curPage = curPage + 1
			hardwareList = wsGetHardware( bNode, curPage )
			storeNonGameData( hardwareList )
			print( "\nfinished writing non game item page: " + str( curPage ) + " of " + str( pageCount ) )
		except( NoExactMatchesFound ):
			msg = "could not find an exact match for page " + str( curPage ) + " for VideoGames on browse node: " + bNode
			print( msg )
			failfile.write( "\n" + msg )


def processReviews(  platform ):
	ps3Reviews = gamedata.getAllReviews( platform )
	for review in ps3Reviews:

		game = dbGetGameByTitle( review[  'game_title'] )
		if not reviewInDatabase( review ) and game != None:
			addReviewToDatabase( game, review )
			print( "review added for %s" % ( review['game_title'] ) )



# connection to log file
failfile = open( "failfile.txt", "w" )

# set up of main interface to amazon api
AWS_KEY = cp.get( "db config", "aws_key" )
SECRET_KEY = cp.get( "db config", "secret_key" )
api = API(AWS_KEY, SECRET_KEY, 'us') 

# deal with the games.
failfile.write( "\nsoftware fails..." )
#processSoftwareData( "ps3" ) # technically includes hardware data.
#processSoftwareData( "xbox360" )
#processSoftwareData( "wii" )

# deal with the game hardware
failfile.write( "\nhardware fails..." )
#processNonGameData( gamedata.PS3_HARDWARE )
#processNonGameData( gamedata.XBOX360_HARDWARE )
#processNonGameData( gamedata.WII_HARDWARE )

# deal with the controllers ( controllers not platform specific as far as amazon's browse node hierarchy is concerned )
failfile.write( "\ncontroller fails..." )
processNonGameData( gamedata.GAME_CONTROLLERS )

failfile.write( "\nreview fails..." )
processReviews( "ps3" )
processReviews( "xbox360" )
processReviews( "wii" )
failfile.close()
db.close()
