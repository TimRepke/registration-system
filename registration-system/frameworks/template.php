<?php

class template {
    private $template_base = "template/default.php";
    private $default_page  = "error";
    private $pages_base    = "template/pages/";
    private $state, $skey, $page;
    private $content = "";

    private function getSlug( $page ) {
        $page = strip_tags( $page );
        preg_match_all( "/([a-z0-9A-Z-_]+)/", $page, $matches );
        $matches = array_map( "ucfirst", $matches[0] );
        $slug = implode( "-", $matches );
        return $slug;
    }

    public function set_page($pg){
        $this->page = $pg;
    }

    public function set_content($cont){
        $this->content = $cont;
    }

    public function show(){
        $contentfile = (!isset($_REQUEST['page'])) ? $this->page : getSlug($_REQUEST['page']);

        if($this->content == ""){
            $content = @file_get_contents($this->pages_base."/".$contentfile.".txt");
            if(!$content)
                $content = @file_get_contents($this->pages_base."/".$this->default_page.".txt");
            $this->content = $content;
        }

        require($this->template_base);
    }

    private function displayMainContent(){
        echo $this->content;
    }

    private function display_status(){

    }
}