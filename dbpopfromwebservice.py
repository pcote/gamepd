# -*- coding: latin-1 -*-

#dbpopfromwebservice.py
# by Phil Cote
# Purpose: Migrate Playstation 3 data from amazon web services to mysql.
# the database being used is also used by php as a source for generating web pages
# Last Updated: October 7, 2010
# Status: Done with current round of updates.  Should now be complying with the object
# oriented version of gamedata module.



from amazonproduct import * # star import used so attribute exceptions can be handled.
import MySQLdb
from MySQLdb import IntegrityError

import time
import datetime
import pdb
import gamedata
import optparse
from optparse import OptionParser
from gamedata import *



def shouldAddGameToDatabase( game ):
	"""Determine whether the game from the web service meets criteria for 
	 being stored in the database."""		

	# the game should be released.
	if game['releaseDate'] == None:
		return False
	if game['releaseDate'] > datetime.datetime.now():
		return False

	# confirm that the price is valid.
	if game['price'] == -1:
		return False
	
	# make sure it's not hardware.
	if gameDB.getHardwareRecord( game['asin'] ) != None:
		return False
	
	# only stuff that's 49.99 or less should be added to the database. (applicable to xbox 360 and ps3 )
	if game['price'] >= 50:
		return False

	# since brand new list price is already 49.99, bargains for wii have to be less than 40 dollars.
	if game['platform'] == 'wii' and game['price'] >= 40:
		return False
	
	# if the asin already exists in the database, it doesn't need to be astedded again.
	dbRec = gameDB.getGameRecord( game['asin'] )
	if len( dbRec ) > 0:
		return False
	
	# online game codes and game downloads not allowed.  physical titles only
	titleString = game['gameTitle']
	if titleString.find( '[Online Game Code' ) > 0 or titleString.find( '[Game Download]' ):
		return False

	# games on the excluded list should not be added.
	if gameDB.isExcluded( game['asin'] ):
		return False
		
	return True
			

def shouldUpdateListPrice( game ):
	""" Determine whether or not the price should be updated.
	TODO: This is only good for list prices.  Need something for "lowest price" updates too."""	
	PRICE_COL = 2
	
	# check for presence of a price in the object
	if game['price'] > -1:
		pass
	else:
		return False
	
	
	wsPrice = game['price']
	dbRec = gameDB.getGameRecord( game['asin'] )
	
	# empty rec case
	if len(dbRec) == 0:
		return False
	# price in the database made obsolete by the web service price.
	if dbRec['price'] != wsPrice:
		return True
	return False


class RecoverWSExceptions:
	"""Manages common exception handling that comes from dealing with Web services
	and getting url connection errors(INCOMPLETE). 
	"""
	def __init__(self, numberAttempts ):
		self.numberAttempts = numberAttempts

	def __call__(self, f ):
		def wrapperfunc(*args):
			try:
				self.res = f(*args)
		
			except( IntegrityError ):
				print( "integrity error: " )
				updateFile.write( "\nasin number: %s causes an integrity violation and will not be added to the game table" )
			except( UnicodeEncodeError ):
				updateFile.write( "\nasin number: %s fails due to unicode encoding problem" )
			
			return self.res	
		return wrapperfunc		


def storeGameData( wsGameList ):
	""" Take a raw list of games (typically 10) and store them in the database.
	 NOTE: At this pass, non games can end up in here.  ( controllers, consoles, ect )
	 Removal of those is taken care of on a separate pass.
	 TODO: Updates of ANY game data should probably be done here.
	 However, updates to stuff other than list price should not affect the last updated field.  Only list price changes should affect that."""
	asin=""
	price=0
	gameTitle=""
	errorCount = 0

	#breakdown the web service game list to addable and updatable parts
	@RecoverWSExceptions(3)
	def breakdownList( gameList ):
		games2Add = filter( shouldAddGameToDatabase, gameList )
		games2Update = filter( shouldUpdateListPrice, gameList )
		return games2Add, games2Update

	gamesToAdd, gamesToUpdate = breakdownList( wsGameList )
	
	for game in gamesToAdd:
		gameDB.addGame( game )
		addMessage = "\nadded asin: " + game['asin'] + " title: " + game['gameTitle']
		print( addMessage ) # keep for collecting samples to debug by
		updateFile.write( addMessage )

	for game in gamesToUpdate:
		gameDB.updatePrice( game )
		updateMessage = "\nupdated asin: " + game['asin'] + " title: " + game['gameTitle'] + "\n"
		print( updateMessage ) 
		updateFile.write( updateMessage )




def storeNonGameData( hardwareList ):
	"""Stores non game data into it's own table in mysql.  
	Data stored sets will come from either hardware or controller categories in amazon."""

	for hwItem in hardwareList:
		try:
			if gameDB.getHardwareRecord( hwItem['asin'] ) == None:
				gameDB.addHardware( hwItem )
				updateFile.write( "\nhardware added.  asin: " + hwItem['asin'] + " item name: " + unicode( hwItem['item_name'] ) )
				
		except( IntegrityError ):
			print( "integrity error regarding item: " + hwItem['asin'] )
		except( UnicodeEncodeError ):
			print( "store non game data: UNICODE fail over item " + hwItem['asin'] )
		



def processSoftwareData( platform ):
	""" High level function for processing playstation 3 data in general.
 	It pulls game data from the web service and loops through the recs
 	to add to the database, update, or discard."""
	pageCount = gameWS.getGamePageCount( platform )
	gameList = gameWS.getGames( platform, 1 )
	curPage = 1
	
	storeGameData( gameList )
	print( "\nfinished  processing software item page: " + str( curPage ) + " of " + str(pageCount) )
	time.sleep(1)

	print( "collecting software data for the %s..." % ( platform ) )
	while curPage < pageCount:
		curPage = curPage + 1
		gameList = gameWS.getGames( platform, curPage )
		storeGameData( gameList )
		print( "\nfinished processing item page: " + str( curPage ) + " of " + str(pageCount)  )	



def processNonGameData( bNode ):
	""" High level function for processing non game data ( controllers and hardware come from separate browse nodes )
 	It pulls hardware data from the web service and loops through the recs
 	to add to the database."""

	hardwareList = gameWS.getHardware(  bNode, 1 )
	storeNonGameData(  hardwareList )
	pageCount = gameWS.getHardwarePageCount( bNode )
	curPage = 1 # NOTE: may need to be changed for debugging purposes so we don't have to wait as long to get to the "odd" data cases.
	print( "\nfinished processing item page: " + unicode( curPage ) )
	time.sleep(1)
	print( "\n\ncollecting non game data: " + unicode( pageCount) + " pages" )
	
	failCount = 0
	failLimit = 5

	while curPage < pageCount and curPage < gameWS.MAX_ALLOWABLE_PAGES:
		try:
			curPage = curPage + 1
			hardwareList = gameWS.getHardware( bNode, curPage )
			storeNonGameData( hardwareList )
			print( "\nfinished writing non game item page: " + str( curPage ) + " of " + str( pageCount ) )
		except( NoExactMatchesFound ):
			msg = "could not find an exact match for page " + str( curPage ) + " for VideoGames on browse node: " + str( bNode )
			print( msg )
			updateFile.write( "\n" + msg )
			failCount = failCount + 1
			if failCount >= failLimit:
				message = "no match limit reached for node: %s" % ( bNode )
				updateFile.write( message )
				print( message )
				return


def processReviews(  platform ):
	""" High level function for processing review data for a specified platform.
	It pulls data from the GameProp web service api and loops through the recs to 
	determine what needs to be added or updated to the database.
	"""
	gameReviews = revWS.getAllReviews( platform )
	for review in gameReviews:

		game = gameDB.getGameByTitle( review[  'game_title' ] )
		if not revDB.reviewExists( review ) and game != None:
			revDB.addReview( game, review )
			revMsg = "\nreview added for %s" % ( review['game_title'] ) 
			print( revMsg  )
			updateFile.write( revMsg )




if __name__ == '__main__':
	parser = OptionParser()
	dbVersion = "dev" # default to the locahost version
	parser.add_option( "-v" )
	(options, args ) = parser.parse_args()

	if options.v != None:
		dbVersion = options.v


	# connection to log file
	# TODO: "failfile" seems a little unprofessional at this stage of the game.  Change it.
	failfile = open( "failfile.txt", "w" )
	updateFile  = open( "updates.txt", "a" )
	logDateString = str( datetime.datetime.today() )
	updateFile.write("\n\nDatabase update on the %s db for date: %s \n" % ( logDateString, dbVersion ) )

	configFileName = "dbconfig.cfg"
	gameDB = GameDatabase(configFileName, dbVersion)
	gameWS = GameWebService(configFileName, dbVersion)
	



	# deal with the games and (unfortunately) the hardware that goes in with it.
	updateFile.write( "SOFTWARE UPDATES...\n" )
	updateFile.write( "\nSoftware updates for the ps3...\n" )
	processSoftwareData( "ps3" )

	updateFile.write( "\nSoftware updates for the xbox 360...\n" )
	processSoftwareData( "xbox360" )
	updateFile.write( "\nSoftware updates for the wii...\n" )
	processSoftwareData( "wii" )

	# deal with the game hardware.
	updateFile.write( "\nHardware Updates...\n" )
	updateFile.write( "\nHardware updates for the ps3...\n" )
	processNonGameData( gameWS.PS3_HARDWARE )
	updateFile.write( "\nHardware updates for the xbox 360...\n" )
	processNonGameData( gameWS.XBOX360_HARDWARE )
	updateFile.write( "\nHardware updates for the wii...\n" )
	processNonGameData( gameWS.WII_HARDWARE )

	# deal with the controllers ( controllers not platform specific as far as amazon's browse node hierarchy is concerned )
	updateFile.write( "\nGAME CONTROLLER UPDATES...\n" )
	processNonGameData( gameWS.GAME_CONTROLLERS )



	# TODO: Mystery bug causes the connection to die on prod unless reviews are run by themselves.
	# UPDATE: Noticed that the review related connections hang open an awful long time before they 
	# ever get used.  Simply moving the review connection init to where it actually gets used might work.
	revDB = ReviewDatabase(configFileName, dbVersion)
	revWS = ReviewWebService(configFileName, dbVersion)
	updateFile.write( "\nREVIEW UPDATES...\n" )
	updateFile.write( "\nReview updates for the ps3\n" )
	processReviews( "ps3" )
	updateFile.write( "\nReview updates for the xbox 360\n" )
	processReviews( "xbox360" )
	updateFile.write( "\nReview updates for the wii\n" )
	processReviews( "wii" )
	failfile.close()
	updateFile.close()
	gameDB.close()
	revDB.close()

