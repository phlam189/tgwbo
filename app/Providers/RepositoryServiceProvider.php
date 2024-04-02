<?php

namespace App\Providers;

use App\Repositories\Eloquent\AccountBalanceHistoryRepository;
use App\Repositories\Eloquent\BankRepository;
use App\Repositories\Eloquent\ClientAggregationRepository;
use App\Repositories\Eloquent\ClientRepository;
use App\Repositories\Eloquent\ContractorRepository;
use App\Repositories\Eloquent\IntroducerRepository;
use App\Repositories\Eloquent\InvoiceManagementRepository;
use App\Repositories\Interfaces\ClientAggregationRepositoryInterface;
use App\Repositories\Interfaces\InvoiceManagementRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Eloquent\AccountRepository;
use App\Repositories\Eloquent\ChargeHistoryRepository;
use App\Repositories\Eloquent\ClientContractDetailRepository;
use App\Repositories\Eloquent\ExpenseRepository;
use App\Repositories\Eloquent\InvoiceContructorRepository;
use App\Repositories\Eloquent\LogActionHistoryRepository;
use App\Repositories\Eloquent\LogTaskRepository;
use App\Repositories\Eloquent\TaskManagementRepository;
use App\Repositories\Interfaces\AccountBalanceHistoryRepositoryInterface;
use App\Repositories\Interfaces\BankRepositoryInterface;
use App\Repositories\Interfaces\ChargeHistoryRepositoryInterface;
use App\Repositories\Interfaces\ClientContractDetailRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\ContractorRepositoryInterface;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Repositories\Interfaces\IntroducerRepositoryInterface;
use App\Repositories\Interfaces\InvoiceContructorRepositoryInterface;
use App\Repositories\Interfaces\LogActionHistoryRepositoryInterface;
use App\Repositories\Interfaces\LogTaskRepositoryInterface;
use App\Repositories\Interfaces\TaskManagementRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            UserRepositoryInterface::class,
            UserRepository::class,
        );
        $this->app->singleton(
            BankRepositoryInterface::class,
            BankRepository::class,
        );
        $this->app->singleton(
            ClientRepositoryInterface::class,
            ClientRepository::class,
        );
        $this->app->singleton(
            IntroducerRepositoryInterface::class,
            IntroducerRepository::class,
        );
        $this->app->singleton(
            ContractorRepositoryInterface::class,
            ContractorRepository::class,
        );
        $this->app->singleton(
            AccountRepositoryInterface::class,
            AccountRepository::class,
        );
        $this->app->singleton(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class,
        );
        $this->app->singleton(
            ClientContractDetailRepositoryInterface::class,
            ClientContractDetailRepository::class,
        );
        $this->app->singleton(
            LogActionHistoryRepositoryInterface::class,
            LogActionHistoryRepository::class,
        );
        $this->app->singleton(
            ClientAggregationRepositoryInterface::class,
            ClientAggregationRepository::class,
        );
        $this->app->singleton(
            ChargeHistoryRepositoryInterface::class,
            ChargeHistoryRepository::class,
        );
        $this->app->singleton(
            TaskManagementRepositoryInterface::class,
            TaskManagementRepository::class,
        );

        $this->app->singleton(
            InvoiceManagementRepositoryInterface::class,
            InvoiceManagementRepository::class,
        );

        $this->app->singleton(
            AccountBalanceHistoryRepositoryInterface::class,
            AccountBalanceHistoryRepository::class,
        );

        $this->app->singleton(
            InvoiceContructorRepositoryInterface::class,
            InvoiceContructorRepository::class,
        );

        $this->app->singleton(
            LogTaskRepositoryInterface::class,
            LogTaskRepository::class,
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
