## Eloquent 파일 첨부 라이브러리

Eloquent 모델에 파일을 첨부하고 관리 할 수 있는 라이브러리입니다.

## 설치

컴포저를 통해 패키지를 설치합니다.

```
$ composer require xelt2011/laravel-attachment
```

서비스 프로바이더를 등록합니다.

```php
'providers' => [
    // ...

    Xelt2011\Attachment\AttachmentServiceProvider::class,

    // ...
```

## 사용방법

```php
class Document extends Model
{
    use \Xelt2011\Attachment\AttachableTrait;

    // ...
}

$document = Document::first();

$document->attach($request->file('document'), [
  'path' => date('Y/m'),
  'filename' => '첨부 파일 #0001.pdf',
  'description' => '첨부 파일 설명',
]);
```

## 다운로드 가능한 첨부 파일

```php
class ImageGoods extends \Xelt2011\Attachment\AbstractAttachment
    implements \Xelt2011\Attachment\Contracts\Downloadable
{
    public function download()
    {
        return Storage::disk($this->disk_id)
            ->download($this->filepath, $this->filename);
    }
}
```

## 확장하기

```php
class Product extends Model
{
    use \Xelt2011\Attachment\AttachableTrait;

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'attachable');
    }

    // ...
}

class ProductImage extends \Xelt2011\Attachment\AbstractAttachment
{
    const TYPE_ID = 'product-image';

    const STORAGE_DISK = 'public';

    const STORAGE_PATH = 'path/to';

    protected $thumbnailImageDisk = 'public';

    protected $thumbnailImagePath = 'path/to'

    public function getThumbnailImageUrlAttribute()
    {
        return Storage::disk($this->thumbnailImageDisk)
          ->url(sprintf('%s/%s', $this->thumbnailImagePath, $this->filepath));
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            $model->append([
                'thumbnail_image_url',
            ]);
        });

        static::created(function ($model) {
            $content = $model->getFileContent();
            // 썸네일 이미지 생성
        });
    }
}

app(\Xelt2011\Attachment\AttachmentManager::class)
    ->extend('product-image', function () {
        return new ProductImage;
    });

$product = Product::first();
$product->attach($request->file('image'), [
    'type' => 'product-image',
    'group' => 'detail',
    'path' => date('Y/m'),
]);

$detailImages = $product->images()->byGroup('detail');
```
