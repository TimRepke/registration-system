<?php

class StorySignupMethod extends SignupMethod {
    public static function getName() {
        return "Story Mode";
    }

    public static function getAltText() {
        return "Kleine Bilderbuch Anmeldung.";
    }

    public static function getMetaInfo() {
        return [
            "version"      => '1.1',
            "date"         => '20.09.2014',
            "contributors" => ['Manuel Herrmann <fsfahrt@icetruck.de>']
        ];
    }

    public function getJSDependencies() {
        return ['story.js'];
    }

    public function getCSSDependencies() {
        return ['style.css'];
    }

    public function getAdditionalHeader() {
        return '';
    }

    public function showInlineHTML() {
        global $config_reisearten_o, $config_essen_o;
        $environment = Environment::getEnv();

        $dates = comm_get_possible_dates($environment->database, $environment->getSelectedTripId());
        foreach($dates as &$date)
            $date = '"'.$date.'"';

        echo '
        <div id="storyhead"></div>
		<div id="storycanvas">
			<div id="storybox"></div>
			<div id="story_umleitung" onclick="story.next(true)">&nbsp;</div>
			<script type="text/javascript">
				function comm_get_possible_dates() {
					return [' . implode(', ', $dates) . '];
				}
				function comm_get_food_types() {
					return [];
				}
				function config_get_travel_types() {
					return ' . $this->putTypesInObject($config_reisearten_o) . ';
				}
				function config_get_food_types() {
					return ' . $this->putTypesInObject($config_essen_o) . ';
				}
			</script>
		</div>';
    }

    private function putTypesInObject($obj) {
        $text = '{ ';
        $first = true;
        foreach ($obj as $key => $value) {
            if ($first)
                $first = false;
            else
                $text .= ', ';
            $text .= '"'.$key.'":"'.$value.'"';
        }
        $text .= ' }';
        return $text;
    }
}