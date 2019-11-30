<?php

namespace Xelt2011\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Storage;
use Illuminate\Support\Str;

class AbstractAttachment extends Model implements Contracts\Attachment
{
	use \Illuminate\Database\Eloquent\SoftDeletes;

	const TYPE_ID = null;

	const STORAGE_DISK = 'public';

	const STORAGE_PATH = '/';

	protected $table = 'attachments';

	protected $attributes = [
		'is_referred' => 0,
		'extra_data' => '[]',
	];

	protected $fillable = [
		'type_id',
		'attachable_id',
		'attachable_type',
		'uuid',
		'group_id',
		'disk_id',
		'filepath',
		'filename',
		'filesize',
		'mimetype',
		'description',
		'token',
		'extra_data',
		'is_referred',
	];

	protected $casts = [
		'attachable_id' => 'int',
		'filesize' => 'int',
		'extra_data' => 'json',
		'is_referred' => 'int',
	];

	public static function getStorageDisk()
	{
		return static::STORAGE_DISK;
	}

	public static function getStoragePath()
	{
		return static::STORAGE_PATH;
	}

	public function getFileContent()
	{
		return Storage::disk($this->disk_id)
			->get($this->filepath);
	}

	public function getRouteKeyName()
	{
		return 'uuid';
	}

	public function attachable()
	{
		return $this->morphTo();
	}

	public function getUrlAttribute()
	{
		if (! $this->exists) {
			return null;
		}

		return Storage::disk($this->disk_id)->url($this->filepath);
	}

	public function getDownloadUrlAttribute()
	{
		return $this instanceof Contracts\Downloadable
			? route('attachments.download', $this)
			: null;
	}

	public function getIsImageAttribute()
	{
		return stripos($this->mimetype, 'image/') === 0;
	}

	public function scopeMimetype($builder, string $mimetype)
	{
		return $builder->where($builder->qualifyColumn('mimetype'), $mimetype);
	}

	public function scopeNotAttached($builder)
	{
		return $builder->whereNull($builder->qualifyColumn('attachable_id'));
	}

	public function scopeByToken($builder, string $token)
	{
		return $builder->where($builder->qualifyColumn('token'), $token);
	}

	public function scopeByGroup($builder, string $group)
	{
		return $builder->where($builder->qualifyColumn('group_id'), $group);
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$model->type_id = static::TYPE_ID;

			if (empty($model->uuid)) {
				$model->uuid = Str::uuid();
			}
		});

		static::saving(function ($model) {
			$dirty = $model->getDirty();

			if (array_key_exists('attachable_id', $dirty)) {
				$model->token = null;
			}
		});

		static::deleting(function ($model) {
			if ($model->isForceDeleting()) {
				return Storage::disk($model->disk_id)
					->delete($model->filepath);
			}
		});

		if (static::TYPE_ID) {
			static::addGlobalScope('type', function (Builder $builder) {
				$builder->where($builder->qualifyColumn('type_id'), static::TYPE_ID);
			});
		}
	}
}
