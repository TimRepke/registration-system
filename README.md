registration-system
===================


in other/sqlDumps ist ein SQL dump, der auch die Datenbank anlegt.
Im root Ordner ist eine config.inc.php in der das Datenbankpasswort angepasst werden muss.

CHMOD Rechte müssen für zwei Datein angepasst werden:
- config_current_fahrt_id (auf rw-rw-rw-)
- passwd/users.txt (auf rw-rw-rw-)

Idealerweise config.inc.php auf gitignore setzen, damit Passwörter nicht überschrieben werden. Ggf aber im repository auf Änderungen prüfen bei Updates.
