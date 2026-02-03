<?php
if (!function_exists('heroUploadsBasePaths')) {
    function heroUploadsBasePaths(): array
    {
        return tenantUploadsPair('home/hero');
    }
}

if (!function_exists('ensureHeroUploadsDirectory')) {
    function ensureHeroUploadsDirectory(): array
    {
        return tenantEnsureUploadsDirectory('home/hero');
    }
}

if (!function_exists('saveHeroSlideImage')) {
    function saveHeroSlideImage(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar la imagen enviada.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('La carga de la imagen no es válida.');
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!$mime || !isset($allowedMimes[$mime])) {
            throw new RuntimeException('El archivo seleccionado debe ser una imagen JPG, PNG o WebP.');
        }

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                if (!function_exists('imagecreatefromwebp')) {
                    throw new RuntimeException('El servidor no soporta imágenes WebP.');
                }
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
            default:
                $image = false;
        }

        if (!$image) {
            throw new RuntimeException('No se pudo leer la imagen enviada.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 1600;
        $maxHeight = 900;

        $targetWidth = $width;
        $targetHeight = $height;

        if ($targetWidth > $maxWidth) {
            $targetHeight = (int) ceil($targetHeight * ($maxWidth / $targetWidth));
            $targetWidth = $maxWidth;
        }

        if ($targetHeight > $maxHeight) {
            $targetWidth = (int) ceil($targetWidth * ($maxHeight / $targetHeight));
            $targetHeight = $maxHeight;
        }

        if ($targetWidth !== $width || $targetHeight !== $height) {
            $optimized = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($optimized, false);
                imagesavealpha($optimized, true);
            }
            imagecopyresampled($optimized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            imagedestroy($image);
            $image = $optimized;
        }

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        [, $absoluteDir] = ensureHeroUploadsDirectory();

        try {
            $filename = 'hero-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $exception) {
            imagedestroy($image);
            throw new RuntimeException('No se pudo generar el nombre del archivo.');
        }

        $targetPath = $absoluteDir . '/' . $filename;

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($image, $targetPath, 82);
                break;
            case 'image/png':
                imagepng($image, $targetPath, 7);
                break;
            case 'image/webp':
                if (!function_exists('imagewebp')) {
                    imagedestroy($image);
                    throw new RuntimeException('El servidor no soporta optimizar imágenes WebP.');
                }
                imagewebp($image, $targetPath, 80);
                break;
        }

        imagedestroy($image);

        if (!file_exists($targetPath)) {
            throw new RuntimeException('No se pudo guardar la imagen optimizada.');
        }

        [$relativeDir] = heroUploadsBasePaths();
        return $relativeDir . '/' . $filename;
    }
}

if (!function_exists('deleteHeroSlideImage')) {
    function deleteHeroSlideImage(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $normalized = ltrim($relativePath, '/');
        if (!tenantUploadsIsWithin($normalized, 'home/hero')) {
            return;
        }

        $absolutePath = tenantUploadsAbsolutePath($normalized);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
