<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('years_between', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) return false;
            $min = (int) $parameters[0];
            $max = (int) $parameters[1];
            $year = (int) $value;
            return $year >= $min && $year <= $max;
        });

        Validator::replacer('years_between', function ($message, $attribute, $rule, $parameters) {
            return "El campo {$attribute} debe ser un año entre {$parameters[0]} y {$parameters[1]}.";
        });
    }
}
