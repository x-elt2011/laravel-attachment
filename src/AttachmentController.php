<?php

namespace Xelt2011\Attachment;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use DB;
use Route;
use Storage;
use Exception;

class AttachmentController extends \Xelt2011\Routing\Controller
{
	public function create(CreateRequest $request)
	{
		$files = $attachments = [];
		$factory = app(AttachmentManager::class);

		DB::beginTransaction();
		try {
			foreach ($request->attachments as $attachment) {
				$file = $attachment['file'];
				$options = array_diff_key($attachment, array_flip(['file']));

				$attachments[] = $factory->make($file, $options);
			}

			DB::commit();
		} catch (Exception $ex) {
			DB::rollBack();

			abort(500, $ex->getMessage());
		}

		return response()
			->json([
				'attachments' => $attachments,
			]);
	}

	public function download(Request $request, Attachment $attachment)
	{
		try {

			return $attachment->download();
		} catch (Exception $ex) {
			return back();
		}
	}

	public static function registerRoutes()
	{
		Route::group([
			'as' => 'attachments.',
			'prefix' => 'attachments',
		], function () {
			Route::post('create', [static::class, 'create']);

			Route::get('download/{DownloadableAttachment}', [static::class, 'download']);
		});
	}
}
