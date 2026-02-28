<?php

namespace App\Models;

class Post extends Model {

    public string $title = "";
    public string $slug = "";
    public string $technology = ""; 
    public string $date = ""; 
    public string $read_time_estimation = "";
    public string $author_name = ""; 
    public string $author_degree = "";
    public string $summary = "";
    public array|string $content = ""; //array when decoding, string when encoded (JSON)
    public string $conclusion = "";
    public string $tags = "";
    /* public $created_at; 
    public $updated_at; */

}

?>