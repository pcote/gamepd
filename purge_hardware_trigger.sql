create trigger PurgeHardware
after insert on game_hardware for each row
delete from games
where games.asin = NEW.asin