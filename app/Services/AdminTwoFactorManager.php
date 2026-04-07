<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

final class AdminTwoFactorManager
{
    public function __construct(
        private readonly Google2FA $google2fa = new Google2FA,
    ) {}

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function qrCodeSvg(string $email, string $plainSecret): string
    {
        $issuer = (string) config('app.name', 'SwaedUAE');
        $url = $this->google2fa->getQRCodeUrl($issuer, $email, $plainSecret);
        $renderer = new ImageRenderer(
            new RendererStyle(192),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($url);
    }

    public function verify(string $plainSecret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        if ($code === '' || strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        return $this->google2fa->verifyKey($plainSecret, $code);
    }
}
