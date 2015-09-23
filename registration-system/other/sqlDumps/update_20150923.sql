
-- das attribut opentime setzt fest, ab wann die anmeldung möglich ist

ALTER TABLE `fahrten`
ADD `opentime` INT NOT NULL DEFAULT 0;