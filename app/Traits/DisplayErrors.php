<?php

namespace App\Traits;

trait DisplayErrors {

    private array $validation;

    public function display(): void {
        if (count($this->validation) > 0) {
            foreach ($this->validation as $keyErrors => $errors) {
                foreach ($errors as $keyError => $message) {
                    response->finish(json->encode(response->error($message)));
                }
            }
        }
    }

}