<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Repository\Contracts\IApartmentsRepository','App\Repository\ApartmentsRepository');
        $this->app->bind('App\Repository\Contracts\IReservationsRepository','App\Repository\ReservationsRepository');
        $this->app->bind('App\Repository\Contracts\IUsersRepository','App\Repository\UsersRepository');
        $this->app->bind('App\Repository\Contracts\IReviewsRepository','App\Repository\ReviewsRepository');
    }
}
