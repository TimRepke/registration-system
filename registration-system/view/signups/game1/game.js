
/*
Small hints:
-------------


var FAPI = new FAPI();

later add data as they come in:
FAPI.data.setValue('mehl', 'bla@lala.de');

and send:
FAPI.submitSignup();


place global in a load_game() function (or so) and call it in game1/index.php as already implemented there.

to avoid broken paths, use as follows:
d3.xml(FAPI.resolvePath('graphics/map_castle.svg'), 'image/svg+xml', function (xml) {
*/