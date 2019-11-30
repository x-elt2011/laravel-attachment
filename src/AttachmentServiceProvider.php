<?php

namespace Xelt2011\Attachment;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;

class AttachmentServiceProvider extends \Illuminate\Support\ServiceProvider
{
	protected $defaultModel = Attachment::class;

    public function register()
    {
		$this->registerManager();
    }

    public function boot()
    {
		$this->loadMigrationsFrom(__DIR__.'/../migrations');

		$this->registerRouteModel();

		$this->registerRoutes();
    }

	protected function registerRouteModel()
	{
		Route::bind('DownloadableAttachment', function ($value) {
			return tap(
				app($this->defaultModel)->resolveRouteBinding($value),
				function ($attachment) {
					throw_unless($attachment instanceof DownloadInterface, new ModelNotFoundException);
				}
			);
		});
	}

	protected function registerRoutes()
	{
		Route::group([
			'middleware' => 'web',
		], function () {
			AttachmentController::registerRoutes();
		});
	}

	protected function registerManager()
	{
		$this->app->singleton(AttachmentManager::class, function ($app) {
			return tap(new AttachmentManager($app), function ($manager) {
				$manager->extend('attachment', function () {
					return new $this->defaultModel;
				});
			});
		});
	}
}
