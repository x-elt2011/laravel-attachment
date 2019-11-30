<?php

namespace Xelt2011\Attachment;

use Illuminate\Http\UploadedFile;
use Str;
use Exception;

trait AttachableTrait
{
	/*
	 * @var array
	 */
	protected $recentlyCreatedAttachments = [];

	public function attachments()
	{
		return $this->morphMany(Attachment::class, 'attachable');
	}

	/*
	 * @param string $k
	 * @param \Xelt2011\Attachment\AttachmentInterface $attachment
	 * @return $this
	 */
	protected function setAttachmentByKey($k, AttachmentInterface $attachment)
	{
		$previousId = isset($this->attributes[$k]) ? $this->attributes[$k] : null;

		$this->attributes[$k] = $attachment->getKey();

		$this->recentlyCreatedAttachments[] = $attachment;

		if ($previousId && $previous = $attachment->find($previousId)) {
			$previous->delete();
		}

		return $this;
	}

	/*
	 * @param \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string $file
	 * @return \Xelt2011\Attachment\AttachmentInterface
	 * @throws \Exception
	 */
	public function attach($file, array $options = [])
	{
		return tap(app(AttachmentManager::class)
			->make($file, $options), function ($attachment) {
				$this->recentlyCreatedAttachments[] = $attachment;

				if ($this->exists) {
					$this->attachments()->saveMany([$attachment]);
				}
			});
	}

	/*
	 * @return $this
	 */
	public function saveAttachments(array $attachments)
	{
		if (empty($attachments)) {
			return $this;
		}

		$this->attachments()->saveMany($attachments);

		return $this;
	}

	/*
	 * @return $this
	 */
	public function saveAttachemntsByToken(string $token)
	{
		$attachments = Attachment::withoutGlobalScopes()->byToken($token)->get();
		
		return $this->saveAttachments($attachments);
	}

	/*
	 * @return $this
	 */
	public function gatherRecentlyCreatedAttachments()
	{
		return $this->saveAttachments($this->recentlyCreatedAttachments);
	}

	protected static function bootAttachableTrait()
	{
		static::saved(function ($model) {
			$model->gatherRecentlyCreatedAttachments();
		});

		static::deleted(function ($model) {
			$model->attachments->delete();
		});
	}
}
