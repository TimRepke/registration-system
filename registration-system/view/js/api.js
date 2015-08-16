/**
 *
 * params will contain the attributes to send (as an object)
 *   -> will be defined later
 *
 * callback is called, once the request was sent to the server.
 * It'll transmit an object containing the
 *    -> state (0 = successful, 1 = error, 2 = ?)
 *    -> main message (string)
 *    -> errors (null, if non; array of strings with messages else)
 *
 * @param params
 * @param callback
 */
function api_send_signup(params, callback) {

    // TODO evaluate params and send them

    callback({
        state: 0,
        messages: 'Successful signup',
        errors: null
    })
}

function api_soft_protect(elementIds, regex) {
    for(var i = 0; i < elementIds.length; ++i) {
        $('#'+elementIds[i]).keyup(function(event) {
            if (!event.target.value.match(regex))
                event.target.style.backgroundColor="#f00";
            else
                event.target.style.backgroundColor="#fff";
        });
    }
}
