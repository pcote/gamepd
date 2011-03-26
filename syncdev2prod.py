# suncdev2prod.py
# by Phil Cote
# Last Updated: November 9, 2010
# Purpose: A way to synchronize dev and production game and review tables.  
# Status: Adding support for a new exclusion feature to keep inappropriate titles out of prod.
# Still a WIP.

import gamedata
from gamedata import *
import pdb

# copy pasted from dbpopfromwebservice.
# TODO: Fix the code so I can just call the processReviews function from dbpopfromweb service.
def processReviews(  platform ):
	ps3Reviews = gameWS.getAllReviews( platform )
	for review in ps3Reviews:
		game = prodGameDB.getGameByTitle( review[  'game_title'] )
		if not revProd.reviewExists( review ) and game != None:
			revProd.addReview( game, review )
			print( "\nreview added for %s" % ( review['game_title'] ) )



def processGames():
	devList = devGameDB.getAllGames( "all" )

	for devGame in devList:
		print( "processing dev asin: %s" % (devGame['asin']) )

		prodGame = prodGameDB.getGameRecord( devGame["asin"] )
		if len( prodGame ) == 0:
			prodGameDB.addGame( devGame )
		elif devGame['lastUpdated'] > prodGame['lastUpdated']:
			prodGameDB.removeGame( prodGame['asin'] )
			prodGameDB.addGame( devGame )

def removeGames():
	# if a game doesn't exist in dev, it should not exist in production either.
	prodGames = prodGameDB.getAllGames( "all" )
	for prodGame in prodGames:
		devGame = devGameDB.getGameRecord( prodGame['asin'] )
		if len( devGame ) == 0:
			prodGameDB.removeGame( prodGame['asin'] )

	# a game whose asin is on the exclusion list should not be there in production.
	exclusions = devGameDB.getExclusions()
	for exclusion in exclusions:
		prodGame = prodGameDB.getGameRecord( exclusion['asin'] )
		if len( prodGame ) == 0:
			prodGameDB.removeGame( exclusion['asin'] )


devGameDB = GameDatabase( "dbconfig.cfg", "dev" )
prodGameDB = GameDatabase( "dbconfig.cfg", "prod" )
revDev = ReviewDatabase( "dbconfig.cfg", "dev" )
revProd = ReviewDatabase( "dbconfig.cfg", "prod" )
removeGames()
processGames() 
gameWS = ReviewWebService( "dbconfig.cfg", "stage" )
processReviews( "ps3" )
processReviews( "xbox360" )
processReviews( "wii" )
