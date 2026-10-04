<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class Media extends Model
{
    protected $guarded = [];

    protected $hidden = ['data'];

    /** Сжимает загруженное фото до 1200px WebP и сохраняет в базу. */
    public static function storeUpload(UploadedFile $file): self
    {
        $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $src) {
            throw new RuntimeException('Не удалось прочитать изображение.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, 1200 / max($w, $h));
        $img = imagecreatetruecolor(max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagecopyresampled($img, $src, 0, 0, 0, 0, imagesx($img), imagesy($img), $w, $h);

        ob_start();
        imagewebp($img, null, 80);
        $bytes = (string) ob_get_clean();

        return self::create([
            'name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'data' => base64_encode($bytes),
            'size' => strlen($bytes),
        ]);
    }

    public function path(): string
    {
        return '/media/'.$this->id.'.webp';
    }
}
