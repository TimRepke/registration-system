<?php

require_once __DIR__.'/Bachelor.php';
require_once __DIR__.'/Environment.php';

class Fahrt {

    const STATUS_IS_OPEN_NOT_FULL = 0;
    const STATUS_IS_OPEN_FULL = 1;
    const STATUS_IS_CLOSED = 2;

    public static $ALLOWED_FIELDS = ['fahrt_id', 'titel', 'ziel', 'von', 'bis', 'regopen', 'beschreibung', 'leiter', 'kontakt',
        'map_pin', 'max_bachelor', 'wikilink', 'paydeadline', 'payinfo', 'opentime'];

    private $environment;
    private $fid;
    private $data;

    function __construct($fid) {
        $this->environment = Environment::getEnv();
        $this->fid = $fid;
        $this->data = null;
    }

    private function getBachelorsSelector($params) {
        $conditions = [
            'fahrt_id' => $this->fid
        ];
        if (isset($params['waiting'])) {
            $conditions['transferred' . (($params['waiting']) ? '' : '[!]')] = null;
            $conditions['on_waitlist'] = ($params['waiting']) ? 1 : 0;
        }
        if (isset($params['essen']))
            $conditions['essen'] = $params['essen'];
        if (isset($params['studityp']))
            $conditions['studityp'] = $params['studityp'];
        if (isset($params['virgin']))
            $conditions['virgin'] = ($params['virgin']) ? 1 : 0;
        if (isset($params['backstepped']))
            $conditions['backstepped' . (($params['backstepped']) ? '[!]' : '')] = null;
        if (isset($params['paid']))
            $conditions['paid' . (($params['paid']) ? '[!]' : '')] = null;
        if (isset($params['repaid']))
            $conditions['repaid' . (($params['backstepped']) ? '[!]' : '')] = null;
        if (isset($params['public']))
            $conditions['public'] = ($params['public']) ? 1 : 0;

        $selector = [
            'table' => 'bachelor',
            'fields' => !isset($params['fields']) ? Bachelor::$ALLOWED_FIELDS : $params['fields'],
            'where' => ['AND' => $conditions]
        ];

        return $selector;
    }

    /**
     * This function returns participants of this fahrt.
     * Set parameters to filter, keep unset means 'dont care'.
     *
     * Form the param array as follows:
     *   - $fields (array[string]) columns you want to select
     *   - $waiting (bool) is on the waitlist and not transferred
     *   - $essen (str) has specific food preferences
     *   - $studityp (int) is of specific studityp
     *   - $virgin (bool) is <18 or not
     *   - $backstepped (bool) stepped back or not
     *   - $paid (bool) paid or not
     *   - $repaid (bool) got money back or not
     *   - $public (bool) publically visible
     *
     * @param array $params params for the selector
     *
     * @return medoo result
     */
    public function getBachelors($params) {
        $selector = $this->getBachelorsSelector($params);

        return $this->environment->database->select($selector['table'], $selector['fields'], $selector['where']);
    }

    public function getBachelor($bid) {
        return Bachelor::makeFromDB($this->fid, $bid);
    }

    public function getFahrtDetails() {
        if (!is_null($this->data) and !empty($this->data))
            return $this->data;

        $this->data = $this->environment->database->get('fahrten', Fahrt::$ALLOWED_FIELDS, ['fahrt_id' => $this->fid]);
        return $this->data;
    }

    public function get($field) {
        if (in_array($field, Fahrt::$ALLOWED_FIELDS))
            return $this->data[$field];
        else
            throw new Exception('Dieses Feld ist nicht vorhanden!');
    }

    public function getID() {
        return $this->fid;
    }

    public function getPossibleDates(){
        $details = $this->getFahrtDetails();
        $end = new DateTime($details['bis']);
        $period = new DatePeriod(
            new DateTime($details['von']),
            new DateInterval('P1D'),
            $end->modify( '+1 day' )
        );
        $ret = [];
        foreach($period as $d){
            array_push($ret, $d->format("d.m.Y"));
        }
        return $ret;
    }

    public function getNumMaxSpots() {
        return $this->getFahrtDetails()['max_bachelor'];
    }

    public function getNumTakenSpots() {
        $selector = $this->getBachelorsSelector(['backstepped' => false, 'waiting' => false]);
        return $this->environment->database->count('bachelor', $selector['where']);
    }

    public function getRegistrationState() {
        comm_verbose(3, 'checking if fid ' . $this->fid . ' is open');

        if (!$this->environment->database->has('fahrten', ['AND' => ['fahrt_id' => $this->fid, 'regopen' => 1]]))
            return Fahrt::STATUS_IS_CLOSED;

        $cnt = $this->getNumTakenSpots();
        $max = $this->getNumMaxSpots();

        if ($cnt < $max)
            return Fahrt::STATUS_IS_OPEN_NOT_FULL;

        return Fahrt::STATUS_IS_OPEN_FULL;
    }

    public function isRegistrationOpen() {
        $state = $this->getRegistrationState();
        return $state == Fahrt::STATUS_IS_OPEN_FULL or $state == Fahrt::STATUS_IS_OPEN_NOT_FULL;
    }
}