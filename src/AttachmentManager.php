<?php

namespace Xelt2011\Attachment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Storage;
use Str;
use Exception;

class AttachmentManager extends \Illuminate\Support\Manager
{
	public function getDefaultDriver()
	{
		return 'attachment';
	}

	/*
	 * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\File|string $file
	 * @return \Xelt2011\Attachment\AttachmentInterface
	 */
	public function make($file, array $options = [])
	{
		$type = $this->driver($options['type'] ?? null);

		$disk = $options['disk'] ?? $type::getStorageDisk();
		$path = $options['path'] ?? $type::getStoragePath();

		$attributes = array_filter(
			array_replace(
				array_fill_keys(array_flip($type->getFillable()), null),
				$options
			)
		);

		try {
			if (is_string($file)) {
				$file = new File($file);
			}

			$fpath = Storage::disk($disk)->putFile($path, $file, 'public');
			throw_if($fpath === false, new Exception(sprintf('failed to file write to Storage [%s]', $disk)));
			$fname = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();

			$attachment = $type->create(array_merge($attributes, [
				'disk_id' => $disk,
				'group_id' => $options['group'] ?? null,
				'filepath' => $fpath,
				'filename' => $attributes['filename'] ?? $fname,
				'filesize' => $file->getSize(),
				'mimetype' => $file->getMimeType(),
			]));
		} catch (Exception $ex) {
			if ($fpath) {
				Storage::disk($disk)->delete($fpath);
			}

			throw $ex;
		}

		return $attachment;
	}
}
