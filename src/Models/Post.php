<?php

namespace App\Models;

class Post{

    public string $title = "";
    public string $slug = "";
    public string $technology = ""; 
    public string $date = ""; 
    public string $read_time_estimation = "";
    public string $author_name = ""; 
    public string $author_degree = "";
    public string $summary = "";
    public string $content = "";
    public string $conclusion = "";
    public string $tags = "";
    /* public $created_at; 
    public $updated_at; */


    // Maps recieved array to a known structure
    public function __construct(array $data = [])
    {

        $required = ['title', 'slug'];

        // Check if required properties exists and have some text atleast
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {

                throw new \InvalidArgumentException( "Missing required property: '$key'" );
            }elseif($data[$key] === '') {
                
                throw new \InvalidArgumentException( "Required property '$key' cannot be empty" );
            }
        }

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                
                if( $key == "content" ) {
                    $this->$key = json_encode($value);
                    continue;
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