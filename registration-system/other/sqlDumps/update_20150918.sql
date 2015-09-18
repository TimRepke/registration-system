
-- this adds new attributes to a trip

ALTER TABLE `fahrten`
ADD `wikilink` VARCHAR(255) NOT NULL DEFAULT 'https://wiki.fachschaft.informatik.hu-berlin.de/wiki/Erstsemesterfahrt',
ADD `paydeadline` DATE NOT NULL,
ADD `payinfo` TEXT NOT NULL DEFAULT '';