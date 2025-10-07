<?php

namespace App\Models;

class Post{

    /* public $id; */
    public $title;
    public $slug;
    public $technology; 
    public $date; 
    public $read_time_estimation;
    public $author_name; 
    public $author_degree;
    public $summary;
    public $content;
    public $conclusion;
    public $tags;
    public $created_at; 
    public $updated_at;


    // Maps recieved array to a known structure
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                
                if( $key == "content" ) {
                    $this->$key = json_encode($value);
                    return;
                }
                
                $this->$key = $value;

            }else{
                throw new \InvalidArgumentException(
                    "Post model creation found an unexpected property: '$key'. " .
                    "Allowed properties: " . implode(', ', $this->getAllowedProperties())
                );
   
            }
        }
    }

    /**
     * Obtiene la lista de propiedades permitidas
     */
    private function getAllowedProperties(): array{
        return array_keys(get_object_vars($this));
        
    }

}


?>