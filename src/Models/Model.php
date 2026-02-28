<?php

namespace App\Models;
 
/**
 * Data-Object base for all other 
 */
abstract class Model {

    // Child list determines which properties cannot be empty
    protected array $requiredFields = [];

    public function __construct(array $fields = []) {
        
        foreach ($fields as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
        
            } else {
                throw new \InvalidArgumentException(
                    "Property '$key' does not exist on " .
                    static::class // Child class name
                );
        
            }
        }

        $this->validate();
    }

    /**
     * Internal validator. Child classes can override this 
     * for custom logic, but should call parent::validate().
     */
    protected function validate(): void {
        foreach ($this->requiredFields as $field) {

            if ( !isset($this->$field) ) {
                throw new \InvalidArgumentException(
                    "Validation Error: Field '$field' is required."
                );

            }

            if(  $this->$field === '' ) {
                throw new \InvalidArgumentException(
                    "Validation Error: Field '$field' cant be empty."
                );                
            }

        }
    }
}