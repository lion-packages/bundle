<?php

namespace App\Traits\Framework;

use LionSecurity\Validation;

trait DisplayErrors {

    private array $validation;

    public function validateRules(array $rules): void {
        $this->validation = Validation::validate((array) request, $rules)->data;
    }

    public function display(): void {
        if (count($this->validation) > 0) {
            foreach ($this->validation as $keyErrors => $errors) {
                foreach ($errors as $keyError => $message) {
                    response->finish(response->error($message));
                }
            }
        }
    }

}