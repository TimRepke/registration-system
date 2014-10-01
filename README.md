registration-system
===================


in other/sqlDumps/ ist ein SQL dump, der auch die Datenbank anlegt.

Anpassungen in der  config.inc.php:<br />
 - Datenbankzugang anpassen ($config_db)<br />
 - $config_baseurl anpassen

CHMOD Rechte müssen für zwei Datein angepasst werden:<br />
- config_current_fahrt_id (auf rw-rw-rw-)<br />
- passwd/users.txt (auf rw-rw-rw-)<br />

Idealerweise config.inc.php auf gitignore setzen, damit Passwörter nicht überschrieben werden. Ggf aber im repository auf Änderungen prüfen bei Updates.

Voraussetzungen:<br />
 - PHP 5.5.x (frühere Versionen gehen nicht, neuere nicht getestet)<br />
 - MySQL (andere Datenbanken ggf. möglich, aber nicht getestet!)

