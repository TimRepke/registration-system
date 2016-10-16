-- Zeug in der Bachelor Datenbank sollte Keys aus der config nutzen!

-- Reiseart
-- "BUSBAHN" => "gemeinsam mit Bus/Bahn",
-- "RAD" => "gemeinsam mit Rad",
-- "AUTO" => "selbst mit Auto",
-- "INDIVIDUELL" => "Kamel/Individuell"

UPDATE `bachelor` SET `antyp` = 'BUSBAHN' WHERE `antyp` = 'gemeinsam mit Bus/Bahn';
UPDATE `bachelor` SET `antyp` = 'RAD' WHERE `antyp` = 'gemeinsam mit Rad';
UPDATE `bachelor` SET `antyp` = 'INDIVIDUELL' WHERE `antyp` = 'selbst mit Auto';
UPDATE `bachelor` SET `antyp` = 'INDIVIDUELL' WHERE `antyp` = 'Kamel/Individuell';

UPDATE `bachelor` SET `abtyp` = 'BUSBAHN' WHERE `abtyp` = 'gemeinsam mit Bus/Bahn';
UPDATE `bachelor` SET `abtyp` = 'RAD' WHERE `abtyp` = 'gemeinsam mit Rad';
UPDATE `bachelor` SET `abtyp` = 'INDIVIDUELL' WHERE `abtyp` = 'selbst mit Auto';
UPDATE `bachelor` SET `abtyp` = 'INDIVIDUELL' WHERE `abtyp` = 'Kamel/Individuell';

-- Essenswunsch
--"ALLES" => "Alles",
--"VEGE" => "Vegetarisch",
--"VEGA" => "Vegan"
UPDATE `bachelor` SET `essen` = 'ALLES' WHERE `essen` = 'Alles';
UPDATE `bachelor` SET `essen` = 'VEGE' WHERE `essen` = 'Vegetarisch';
UPDATE `bachelor` SET `essen` = 'VEGA' WHERE `essen` = 'Vegan';

-- Studityp
--"ERSTI" => "Ersti",
--"HOERS" => "Hoersti",
--"TUTTI" => "Tutor"
UPDATE `bachelor` SET `studityp` = 'ERSTI' WHERE `studityp` = 'Ersti';
UPDATE `bachelor` SET `studityp` = 'HOERS' WHERE `studityp` = 'Hoersti';
UPDATE `bachelor` SET `studityp` = 'TUTTI' WHERE `studityp` = 'Tutor';

UPDATE `bachelor` SET `studityp` = 'ERSTI' WHERE `studityp` = '0';
UPDATE `bachelor` SET `studityp` = 'HOERS' WHERE `studityp` = '1';
UPDATE `bachelor` SET `studityp` = 'TUTTI' WHERE `studityp` = '2';
